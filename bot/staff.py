import os
from typing import Any, Optional
from html import escape
from pathlib import Path

import httpx
from dotenv import load_dotenv
from telegram import Update
from telegram.ext import Application, CommandHandler, ContextTypes, ConversationHandler, MessageHandler, filters

load_dotenv(Path(__file__).resolve().parents[1] / ".env")
load_dotenv()

WAITING_REPLY = 0
_origin = os.environ.get("HOC_API_URL", "http://127.0.0.1:8000").rstrip("/")
API_URL = _origin if _origin.endswith("/api") else f"{_origin}/api"
BOT_SECRET = os.environ.get("TELEGRAM_STAFF_BOT_SECRET", "change-me-staff")


def api_headers() -> dict[str, str]:
    return {
        "Accept": "application/json",
        "X-Webhook-Secret": BOT_SECRET,
    }


async def lookup_employee(telegram_id: int) -> Optional[dict[str, Any]]:
    async with httpx.AsyncClient(timeout=12) as client:
        response = await client.get(
            f"{API_URL}/bot/staff/me",
            headers=api_headers(),
            params={"telegram_user_id": str(telegram_id)},
        )
        if response.status_code == 404:
            return None
        response.raise_for_status()
        return response.json()["data"]


async def start(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    user = update.effective_user
    if user is None or update.message is None:
        return
    employee = await lookup_employee(user.id)
    if employee is None:
        await update.message.reply_text(
            "حسابك غير مسجّل كموظف.\n"
            "أضف آيدي تيليجرام الخاص بك في لوحة الموظفين ثم أعد /start."
        )
        return
    await update.message.reply_text(
        f"مرحباً {escape(employee['name'])}.\n"
        "ستصلك طلبات قسمك هنا.\n"
        "للرد على الزبون: /reply REQ-2026-000001 ثم اكتب الرسالة."
    )


async def reply_start(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    if update.message is None or update.effective_user is None:
        return ConversationHandler.END
    employee = await lookup_employee(update.effective_user.id)
    if employee is None:
        await update.message.reply_text("حسابك غير مسجّل كموظف.")
        return ConversationHandler.END
    parts = (update.message.text or "").split(maxsplit=1)
    if len(parts) < 2:
        await update.message.reply_text("استخدم: /reply REQ-2026-000001")
        return ConversationHandler.END
    context.user_data["request_number"] = parts[1].strip()
    await update.message.reply_text("اكتب الرسالة التي ستصل إلى بوت الزبون.")
    return WAITING_REPLY


async def capture_reply(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    user = update.effective_user
    if user is None or update.message is None or not update.message.text:
        return ConversationHandler.END

    async with httpx.AsyncClient(timeout=12) as client:
        response = await client.post(
            f"{API_URL}/bot/staff/reply",
            headers=api_headers(),
            json={
                "telegram_user_id": str(user.id),
                "request_number": context.user_data.get("request_number"),
                "text": update.message.text.strip(),
            },
        )
        if response.status_code >= 400:
            detail = response.json().get("message") if response.headers.get("content-type", "").startswith("application/json") else response.text
            await update.message.reply_text(f"تعذر إرسال الرسالة: {escape(str(detail))}")
            return ConversationHandler.END
        payload = response.json()["data"]

    await update.message.reply_text(
        f"وصلت الرسالة إلى الزبون عبر بوت العملاء.\nالطلب: {escape(payload['request_number'])}"
    )
    return ConversationHandler.END


async def cancel(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    if update.message:
        await update.message.reply_text("تم إلغاء الرد.")
    return ConversationHandler.END


def main() -> None:
    token = os.environ.get("TELEGRAM_STAFF_BOT_TOKEN", "")
    if not token:
        raise RuntimeError("TELEGRAM_STAFF_BOT_TOKEN is missing.")

    application = Application.builder().token(token).build()
    application.add_handler(CommandHandler("start", start))
    application.add_handler(
        ConversationHandler(
            entry_points=[CommandHandler("reply", reply_start)],
            states={
                WAITING_REPLY: [MessageHandler(filters.TEXT & ~filters.COMMAND, capture_reply)],
            },
            fallbacks=[CommandHandler("cancel", cancel)],
        )
    )
    application.run_polling(allowed_updates=Update.ALL_TYPES)


if __name__ == "__main__":
    main()
