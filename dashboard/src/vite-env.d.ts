/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL: string;
  readonly VITE_TELEGRAM_BOT: string;
  readonly VITE_TELEGRAM_STAFF_BOT: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
