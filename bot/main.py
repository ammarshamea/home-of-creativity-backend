import os
from html import escape
from pathlib import Path

import httpx
from dotenv import load_dotenv
from telegram import Update
from telegram.ext import Application, CommandHandler, ContextTypes, ConversationHandler, MessageHandler, filters

load_dotenv(Path(__file__).resolve().parents[1] / ".env")
load_dotenv()

WAITING_TITLE, WAITING_BODY = range(2)
_origin = os.environ.get("HOC_API_URL", "http://127.0.0.1:8000").rstrip("/")
API_URL = _origin if _origin.endswith("/api") else f"{_origin}/api"
BOT_SECRET = os.environ.get("TELEGRAM_BOT_SECRET", "")


def api_headers() -> dict[str, str]:
    return {
        "Accept": "application/json",
        "X-Webhook-Secret": BOT_SECRET,
    }


async def link_client(telegram_id: int, name: str) -> dict:
    async with httpx.AsyncClient(timeout=12) as client:
        response = await client.post(
            f"{API_URL}/bot/telegram/link",
            headers=api_headers(),
            json={
                "telegram_user_id": str(telegram_id),
                "name": name,
                "locale": "ar",
            },
        )
        response.raise_for_status()
        return response.json()


async def start(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    user = update.effective_user
    if user is None or update.message is None:
        return
    await link_client(user.id, user.full_name)
    await update.message.reply_text(
        "تم إنشاء حسابك في Home of Creativity.\n"
        "استخدم /new لإرسال طلب جديد."
    )


async def new_request(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    if update.message is None:
        return ConversationHandler.END
    await update.message.reply_text("ما عنوان الطلب؟")
    return WAITING_TITLE


async def capture_title(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    if update.message is None or not update.message.text:
        return WAITING_TITLE
    context.user_data["title"] = update.message.text.strip()
    await update.message.reply_text("صف المطلوب باختصار.")
    return WAITING_BODY


async def capture_body(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    user = update.effective_user
    if user is None or update.message is None or not update.message.text:
        return ConversationHandler.END

    async with httpx.AsyncClient(timeout=12) as client:
        response = await client.post(
            f"{API_URL}/bot/telegram/requests",
            headers=api_headers(),
            json={
                "telegram_user_id": str(user.id),
                "title": context.user_data.get("title", "طلب جديد"),
                "description": update.message.text.strip(),
            },
        )
        response.raise_for_status()
        payload = response.json()["data"]

    await update.message.reply_text(
        f"تم تسجيل الطلب {escape(payload['number'])}.\nالحالة: {payload['status']}"
    )
    return ConversationHandler.END


async def cancel(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    if update.message:
        await update.message.reply_text("تم إلغاء الطلب.")
    return ConversationHandler.END


def main() -> None:
    token = os.environ.get("TELEGRAM_BOT_TOKEN", "")
    if not token:
        raise RuntimeError("TELEGRAM_BOT_TOKEN is missing.")

    application = Application.builder().token(token).build()
    application.add_handler(CommandHandler("start", start))
    application.add_handler(
        ConversationHandler(
            entry_points=[CommandHandler("new", new_request)],
            states={
                WAITING_TITLE: [MessageHandler(filters.TEXT & ~filters.COMMAND, capture_title)],
                WAITING_BODY: [MessageHandler(filters.TEXT & ~filters.COMMAND, capture_body)],
            },
            fallbacks=[CommandHandler("cancel", cancel)],
        )
    )
    application.run_polling(allowed_updates=Update.ALL_TYPES)


if __name__ == "__main__":
    main()
