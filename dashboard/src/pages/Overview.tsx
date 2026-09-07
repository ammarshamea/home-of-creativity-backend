import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { api, type ServiceRequest } from "../api";
import { copy, statuses, type Locale } from "../i18n";

export function Overview({ t }: { locale: Locale; t: (c: { ar: string; en: string }) => string }) {
  const [data, setData] = useState<{ clients: number; requests: number; by_status: Record<string, number> } | null>(null);
  const [recent, setRecent] = useState<ServiceRequest[]>([]);

  useEffect(() => {
    api.overview().then((res) => setData(res.data)).catch(() => setData(null));
    api.requests().then((res) => setRecent(res.data.slice(0, 6))).catch(() => setRecent([]));
  }, []);

  if (!data) return <p className="muted">{t(copy.loading)}</p>;

  return (
    <>
      <header className="page-head">
        <div>
          <p className="eyebrow">{t(copy.brandMark)}</p>
          <h1 className="page-title">{t(copy.overview)}</h1>
          <p className="page-lede">{t(copy.overviewLede)}</p>
        </div>
      </header>
      <div className="cards">
        <article className="card card-accent">
          <span className="muted">{t(copy.clientsCount)}</span>
          <strong>{data.clients}</strong>
        </article>
        <article className="card card-accent">
          <span className="muted">{t(copy.requestsCount)}</span>
          <strong>{data.requests}</strong>
        </article>
        {Object.entries(data.by_status).map(([status, total]) => (
          <article className="card" key={status}>
            <span className="muted">{t(statuses[status] ?? { ar: status, en: status })}</span>
            <strong>{total}</strong>
          </article>
        ))}
      </div>
      <section className="panel recent-panel">
        <div className="panel-head">
          <h2>{t(copy.recent)}</h2>
          <Link className="btn" to="/requests">
            {t(copy.requests)}
          </Link>
        </div>
        <div className="table-wrap table-flush">
          <table>
            <thead>
              <tr>
                <th>{t(copy.number)}</th>
                <th>{t(copy.title)}</th>
                <th>{t(copy.status)}</th>
              </tr>
            </thead>
            <tbody>
              {recent.length === 0 ? (
                <tr>
                  <td colSpan={3}>{t(copy.empty)}</td>
                </tr>
              ) : (
                recent.map((item) => (
                  <tr key={item.id}>
                    <td>
                      <Link to={`/requests/${item.id}`}>{item.number}</Link>
                    </td>
                    <td>{item.title}</td>
                    <td>
                      <span className={`status status-${item.status}`}>
                        {t(statuses[item.status] ?? { ar: item.status, en: item.status })}
                      </span>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </section>
    </>
  );
}
