import { useEffect, useState } from "react";
import { api, type Client } from "../api";
import { copy, type Locale } from "../i18n";

const TELEGRAM_BOT = import.meta.env.VITE_TELEGRAM_BOT ?? "pro_design_perfect_bot";

export function Clients({ t }: { locale: Locale; t: (c: { ar: string; en: string }) => string }) {
  const [items, setItems] = useState<Client[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .clients()
      .then((res) => setItems(res.data))
      .catch(() => setItems([]))
      .finally(() => setLoading(false));
  }, []);

  return (
    <>
      <header className="page-head">
        <div>
          <p className="eyebrow">{t(copy.brandMark)}</p>
          <h1 className="page-title">{t(copy.clients)}</h1>
          <p className="page-lede">{t(copy.clientsLede)}</p>
        </div>
        <a className="btn btn-telegram" href={`https://t.me/${TELEGRAM_BOT}`} target="_blank" rel="noreferrer">
          {t(copy.signupCta)}
        </a>
      </header>
      <div className="table-wrap">
        <table>
          <thead>
            <tr>
              <th>{t(copy.client)}</th>
              <th>{t(copy.email)}</th>
              <th>{t(copy.phone)}</th>
              <th>{t(copy.telegram)}</th>
              <th>{t(copy.requestsCount)}</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td colSpan={5}>{t(copy.loading)}</td>
              </tr>
            ) : items.length === 0 ? (
              <tr>
                <td colSpan={5}>{t(copy.empty)}</td>
              </tr>
            ) : (
              items.map((item) => (
                <tr key={item.id}>
                  <td>
                    <strong className="client-name">{item.name}</strong>
                  </td>
                  <td dir="ltr">{item.email ?? "—"}</td>
                  <td dir="ltr">{item.phone ?? "—"}</td>
                  <td dir="ltr">
                    {item.telegram_user_id ? (
                      <span className="source source-telegram">{item.telegram_user_id}</span>
                    ) : (
                      "—"
                    )}
                  </td>
                  <td>{item.requests_count ?? 0}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </>
  );
}
