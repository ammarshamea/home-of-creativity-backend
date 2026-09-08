import type { PageMeta } from "../api";
import { copy, type Copy } from "../i18n";

type Props = {
  meta: PageMeta | null;
  disabled?: boolean;
  onPage: (page: number) => void;
  t: (entry: Copy) => string;
};

function fill(template: string, vars: Record<string, string | number>) {
  return Object.entries(vars).reduce(
    (text, [key, value]) => text.replaceAll(`{${key}}`, String(value)),
    template,
  );
}

export function Pagination({ meta, disabled, onPage, t }: Props) {
  if (!meta || meta.total === 0) return null;

  const page = meta.current_page;
  const lastPage = meta.last_page;

  return (
    <nav className="pager" aria-label={t(copy.pagination)}>
      <p className="pager-meta">
        {fill(t(copy.showingRange), {
          from: meta.from ?? 0,
          to: meta.to ?? 0,
          total: meta.total,
        })}
      </p>
      {lastPage > 1 && (
        <div className="pager-controls">
          <button
            type="button"
            className="btn"
            disabled={disabled || page <= 1}
            onClick={() => onPage(page - 1)}
          >
            {t(copy.previous)}
          </button>
          <p className="pager-page" aria-live="polite">
            {fill(t(copy.pageOf), { page, pages: lastPage })}
          </p>
          <button
            type="button"
            className="btn"
            disabled={disabled || page >= lastPage}
            onClick={() => onPage(page + 1)}
          >
            {t(copy.next)}
          </button>
        </div>
      )}
    </nav>
  );
}
