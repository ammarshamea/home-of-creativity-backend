import { useEffect, useState, type FormEvent } from "react";
import { Link, useParams } from "react-router-dom";
import { api, type ServiceRequest } from "../api";
import { copy, sources, statuses, type Locale } from "../i18n";

export function RequestDetail({ t }: { locale: Locale; t: (c: { ar: string; en: string }) => string }) {
  const { id } = useParams();
  const [item, setItem] = useState<ServiceRequest | null>(null);
  const [status, setStatus] = useState("");
  const [error, setError] = useState("");

  useEffect(() => {
    if (!id) return;
    api.request(id).then((res) => {
      setItem(res.data);
      setStatus(res.data.status);
    });
  }, [id]);

  async function saveStatus(next: string) {
    if (!item) return;
    setError("");
    try {
      const res = await api.updateStatus(item.id, next);
      setItem(res.data);
      setStatus(res.data.status);
    } catch (err) {
      const message = err instanceof Error ? err.message : "";
      setError(message.includes("quotation") ? t(copy.paymentBlocked) : t(copy.saveFailed));
    }
  }

  async function onSave(event: FormEvent) {
    event.preventDefault();
    await saveStatus(status);
  }

  if (!item) return <p className="muted">{t(copy.empty)}</p>;

  const canConfirmPayment = item.status === "quotation_sent";
  const quotationPending = ["draft", "submitted", "ai_analyzing"].includes(item.status);

  return (
    <div className="detail">
      <Link className="back-link" to="/requests">
        {t(copy.back)}
      </Link>
      <header className="page-head">
        <div>
          <p className="eyebrow">{item.client?.name ?? t(copy.client)}</p>
          <h1 className="page-title">{item.number}</h1>
          <p className="page-lede">{item.title}</p>
        </div>
        <p className={`status status-${item.status}`} data-testid="request-status">
          {t(statuses[item.status] ?? { ar: item.status, en: item.status })}
        </p>
      </header>
      <section className="card detail-card">
        <p>{item.description}</p>
        <dl className="meta-grid">
          <div>
            <dt>{t(copy.client)}</dt>
            <dd>{item.client?.name ?? "—"}</dd>
          </div>
          <div>
            <dt>{t(copy.source)}</dt>
            <dd>{t(sources[item.source] ?? { ar: item.source, en: item.source })}</dd>
          </div>
          <div>
            <dt>{t(copy.telegram)}</dt>
            <dd dir="ltr">{item.client?.telegram_user_id ?? "—"}</dd>
          </div>
          <div>
            <dt>{t(copy.odooPartner)}</dt>
            <dd dir="ltr">{item.client?.odoo_partner_id ?? "—"}</dd>
          </div>
          <div>
            <dt>{t(copy.odooQuote)}</dt>
            <dd dir="ltr">{item.odoo_quotation_id ?? "—"}</dd>
          </div>
          <div>
            <dt>{t(copy.odooInvoice)}</dt>
            <dd dir="ltr">{item.odoo_invoice_id ?? "—"}</dd>
          </div>
        </dl>
        {item.briefs?.length ? (
          <div className="briefs">
            <h3>{t(copy.clickupTasks)}</h3>
            <ul>
              {item.briefs.map((brief) => (
                <li key={`${brief.department}-${brief.clickup_task_id ?? "none"}`}>
                  <strong>{brief.department}</strong>
                  {brief.clickup_task_id ? <span dir="ltr"> · {brief.clickup_task_id}</span> : null}
                </li>
              ))}
            </ul>
          </div>
        ) : null}
      </section>
      <form className="toolbar" onSubmit={onSave}>
        <select className="field" value={status} onChange={(e) => setStatus(e.target.value)} aria-label={t(copy.status)}>
          {Object.entries(statuses)
            .filter(([key]) => key !== "payment_confirmed" || canConfirmPayment || item.status === "payment_confirmed")
            .map(([key, label]) => (
              <option key={key} value={key}>
                {t(label)}
              </option>
            ))}
        </select>
        <button className="btn btn-primary" type="submit">
          {t(copy.save)}
        </button>
        {canConfirmPayment ? (
          <button className="btn btn-teal" type="button" onClick={() => void saveStatus("payment_confirmed")}>
            {t(copy.markPaid)}
          </button>
        ) : null}
      </form>
      {quotationPending ? <p className="muted">{t(copy.paymentBlocked)}</p> : null}
      {error ? <p className="error">{error}</p> : null}
    </div>
  );
}
