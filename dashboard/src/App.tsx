import { NavLink, Navigate, Outlet, Route, Routes, useLocation } from "react-router-dom";
import { useEffect, useState, type ReactNode } from "react";
import { AuthProvider, useAuth } from "./auth";
import { applyLocale, copy, readLocale, type Copy, type Locale } from "./i18n";
import { Clients } from "./pages/Clients";
import { Employees } from "./pages/Employees";
import { Login } from "./pages/Login";
import { Overview } from "./pages/Overview";
import { RequestDetail } from "./pages/RequestDetail";
import { Requests } from "./pages/Requests";

function tFactory(locale: Locale) {
  return (entry: Copy) => entry[locale];
}

function Shell({ locale, setLocale }: { locale: Locale; setLocale: (next: Locale) => void }) {
  const { user, logout } = useAuth();
  const location = useLocation();
  const t = tFactory(locale);
  const [navOpen, setNavOpen] = useState(false);

  useEffect(() => {
    setNavOpen(false);
  }, [location.pathname]);

  useEffect(() => {
    document.body.style.overflow = navOpen ? "hidden" : "";
    return () => {
      document.body.style.overflow = "";
    };
  }, [navOpen]);

  useEffect(() => {
    if (!navOpen) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setNavOpen(false);
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [navOpen]);

  if (!user?.is_admin) return <Navigate to="/staff" replace />;

  return (
    <div className={navOpen ? "app-shell nav-open" : "app-shell"}>
      <header className="mobile-bar">
        <p className="brand">
          HOME <span>of</span> CREATIVITY
        </p>
        <button
          type="button"
          className="nav-toggle"
          aria-expanded={navOpen}
          aria-controls="dash-nav"
          aria-label={navOpen ? t(copy.closeMenu) : t(copy.menu)}
          onClick={() => setNavOpen((open) => !open)}
        >
          <span aria-hidden="true" className={navOpen ? "nav-toggle-bars is-open" : "nav-toggle-bars"}>
            <span />
            <span />
          </span>
        </button>
      </header>
      <button
        type="button"
        className="nav-backdrop"
        tabIndex={navOpen ? 0 : -1}
        aria-hidden={!navOpen}
        aria-label={t(copy.closeMenu)}
        onClick={() => setNavOpen(false)}
      />
      <aside id="dash-nav" className={navOpen ? "sidebar is-open" : "sidebar"}>
        <div>
          <p className="brand">
            HOME <span>of</span> CREATIVITY
          </p>
          <p className="brand-mark">{t(copy.brandMark)}</p>
        </div>
        <nav className="nav-links" aria-label={t(copy.menu)}>
          <NavLink to="/" end>
            {t(copy.overview)}
          </NavLink>
          <NavLink to="/requests">{t(copy.requests)}</NavLink>
          <NavLink to="/employees">{t(copy.employees)}</NavLink>
          <NavLink to="/clients">{t(copy.clients)}</NavLink>
        </nav>
        <div className="sidebar-foot">
          <div className="staff-chip">
            <span className="staff-dot" aria-hidden="true" />
            <span>
              <strong>{user.name}</strong>
              <small>{t(copy.staffChip)}</small>
            </span>
          </div>
          <button type="button" className="btn btn-ghost" onClick={() => setLocale(locale === "ar" ? "en" : "ar")}>
            {t(copy.language)}
          </button>
          <button type="button" className="btn btn-ghost" onClick={() => void logout()}>
            {t(copy.logout)}
          </button>
        </div>
      </aside>
      <main className="main">
        <Outlet />
      </main>
    </div>
  );
}

function Guarded({ children }: { children: ReactNode }) {
  const { user, ready } = useAuth();
  if (!ready) return <p className="muted" style={{ padding: "2rem" }}>{copy.loading[readLocale()]}</p>;
  if (!user?.is_admin) return <Navigate to="/staff" replace />;
  return children;
}

export function App() {
  const [locale, setLocaleState] = useState<Locale>(() => {
    const next = readLocale();
    applyLocale(next);
    return next;
  });
  const t = tFactory(locale);

  function setLocale(next: Locale) {
    applyLocale(next);
    setLocaleState(next);
  }

  return (
    <AuthProvider>
      <Routes>
        <Route path="/staff" element={<Login locale={locale} t={t} setLocale={setLocale} />} />
        <Route path="/login" element={<Navigate to="/staff" replace />} />
        <Route
          element={
            <Guarded>
              <Shell locale={locale} setLocale={setLocale} />
            </Guarded>
          }
        >
          <Route path="/" element={<Overview locale={locale} t={t} />} />
          <Route path="/requests" element={<Requests locale={locale} t={t} />} />
          <Route path="/requests/:id" element={<RequestDetail locale={locale} t={t} />} />
          <Route path="/employees" element={<Employees locale={locale} t={t} />} />
          <Route path="/clients" element={<Clients locale={locale} t={t} />} />
        </Route>
      </Routes>
    </AuthProvider>
  );
}
