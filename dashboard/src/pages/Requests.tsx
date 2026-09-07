import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { api, type ServiceRequest } from "../api";
import { copy, sources, statuses, type Locale } from "../i18n";

export function Requests({ t }: { locale: Locale; t: (c: { ar: string; en: string }) => string }) {
  const [items, setItems] = useState<ServiceRequest[]>([]);
  const [status, setStatus] = useState("");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    api
      .requests(status || undefined)
      .then((res) => setItems(res.data))
      .catch(() => setItems([]))
      .finally(() => setLoading(false));
  }, [status]);

  return (
    <>
      <header className="page-head">
        <div>
          <p className="eyebrow">{t(copy.brandMark)}</p>
          <h1 className="page-title">{t(copy.requests)}</h1>
          <p className="page-lede">{t(copy.requestsLede)}</p>
        </div>
      </header>
      <div className="toolbar">
        <label className="filter-label" htmlFor="request-status-filter">
          {t(copy.status)}
        </label>
        <select
          id="request-status-filter"
          className="field"
          value={status}
          onChange={(e) => setStatus(e.target.value)}
        >
          <option value="">{t(copy.all)}</option>
          {Object.entries(statuses).map(([key, label]) => (
            <option key={key} value={key}>
              {t(label)}
            </option>
          ))}
        </select>
      </div>
      <div className="table-wrap">
        <table>
          <thead>
            <tr>
              <th>{t(copy.number)}</th>
              <th>{t(copy.title)}</th>
              <th>{t(copy.client)}</th>
              <th>{t(copy.status)}</th>
              <th>{t(copy.source)}</th>
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
                    <Link className="table-link" to={`/requests/${item.id}`}>
                      {item.number}
                    </Link>
                  </td>
                  <td>{item.title}</td>
                  <td>{item.client?.name ?? "—"}</td>
                  <td>
                    <span className={`status status-${item.status}`}>
                      {t(statuses[item.status] ?? { ar: item.status, en: item.status })}
                    </span>
                  </td>
                  <td>
                    <span className={`source source-${item.source}`}>
                      {t(sources[item.source] ?? { ar: item.source, en: item.source })}
                    </span>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </>
  );
}
