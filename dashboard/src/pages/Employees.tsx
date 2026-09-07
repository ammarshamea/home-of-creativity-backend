import { useEffect, useState, type FormEvent } from "react";
import { api, type Employee } from "../api";
import { copy, professions, type Locale } from "../i18n";

const emptyForm = {
  name: "",
  code: "",
  phone: "",
  email: "",
  telegram_user_id: "",
  clickup_user_id: "",
  profession: "sales",
  notes: "",
  is_active: true,
};

export function Employees({ t }: { locale: Locale; t: (c: { ar: string; en: string }) => string }) {
  const [items, setItems] = useState<Employee[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [editingId, setEditingId] = useState<number | null>(null);
  const [form, setForm] = useState(emptyForm);
  const staffBot = import.meta.env.VITE_TELEGRAM_STAFF_BOT as string | undefined;

  function load() {
    setLoading(true);
    api
      .employees()
      .then((res) => setItems(res.data))
      .catch(() => setItems([]))
      .finally(() => setLoading(false));
  }

  useEffect(() => {
    load();
  }, []);

  function startEdit(item: Employee) {
    setEditingId(item.id);
    setForm({
      name: item.name,
      code: item.code,
      phone: item.phone ?? "",
      email: item.email ?? "",
      telegram_user_id: item.telegram_user_id ?? "",
      clickup_user_id: item.clickup_user_id ?? "",
      profession: item.profession,
      notes: item.notes ?? "",
      is_active: item.is_active,
    });
    setError("");
  }

  function resetForm() {
    setEditingId(null);
    setForm(emptyForm);
    setError("");
  }

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    setError("");
    const payload = {
      name: form.name.trim(),
      code: form.code.trim() || undefined,
      phone: form.phone.trim() || null,
      email: form.email.trim() || null,
      telegram_user_id: form.telegram_user_id.trim() || null,
      clickup_user_id: form.clickup_user_id.trim() || null,
      profession: form.profession,
      notes: form.notes.trim() || null,
      is_active: form.is_active,
    };
    try {
      if (editingId) {
        await api.updateEmployee(editingId, payload);
      } else {
        await api.createEmployee(payload);
      }
      resetForm();
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : t(copy.saveFailed));
    }
  }

  async function remove(id: number) {
    setError("");
    try {
      await api.deleteEmployee(id);
      if (editingId === id) resetForm();
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : t(copy.saveFailed));
    }
  }

  return (
    <>
      <header className="page-head">
        <div>
          <p className="eyebrow">{t(copy.brandMark)}</p>
          <h1 className="page-title">{t(copy.employees)}</h1>
          <p className="page-lede">{t(copy.employeesLede)}</p>
        </div>
        {staffBot ? (
          <a className="btn btn-telegram" href={`https://t.me/${staffBot}`} target="_blank" rel="noreferrer">
            {t(copy.openStaffBot)}
          </a>
        ) : null}
      </header>

      <form className="card employee-form" onSubmit={onSubmit}>
        <h2 className="form-title">{editingId ? t(copy.editEmployee) : t(copy.addEmployee)}</h2>
        <div className="form-grid">
          <label>
            {t(copy.employeeName)}
            <input className="field" required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
          </label>
          <label>
            {t(copy.employeeCode)}
            <input className="field" dir="ltr" value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} placeholder="EMP-0001" />
          </label>
          <label>
            {t(copy.phone)}
            <input className="field" dir="ltr" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
          </label>
          <label>
            {t(copy.email)}
            <input className="field" dir="ltr" type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
          </label>
          <label>
            {t(copy.telegram)}
            <input className="field" dir="ltr" value={form.telegram_user_id} onChange={(e) => setForm({ ...form, telegram_user_id: e.target.value })} />
          </label>
          <label>
            {t(copy.clickupId)}
            <input className="field" dir="ltr" value={form.clickup_user_id} onChange={(e) => setForm({ ...form, clickup_user_id: e.target.value })} />
          </label>
          <label>
            {t(copy.profession)}
            <select className="field" value={form.profession} onChange={(e) => setForm({ ...form, profession: e.target.value })}>
              {Object.entries(professions).map(([key, label]) => (
                <option key={key} value={key}>
                  {t(label)}
                </option>
              ))}
            </select>
          </label>
          <label className="check-row">
            <input type="checkbox" checked={form.is_active} onChange={(e) => setForm({ ...form, is_active: e.target.checked })} />
            {t(copy.active)}
          </label>
        </div>
        <label>
          {t(copy.notes)}
          <textarea className="field field-area" rows={3} value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} />
        </label>
        <div className="toolbar">
          <button className="btn btn-primary" type="submit">
            {editingId ? t(copy.saveEmployee) : t(copy.addEmployee)}
          </button>
          {editingId ? (
            <button className="btn" type="button" onClick={resetForm}>
              {t(copy.cancel)}
            </button>
          ) : null}
        </div>
        {error ? <p className="error">{error}</p> : null}
      </form>

      <div className="table-wrap">
        <table>
          <thead>
            <tr>
              <th>{t(copy.employeeCode)}</th>
              <th>{t(copy.employeeName)}</th>
              <th>{t(copy.phone)}</th>
              <th>{t(copy.telegram)}</th>
              <th>{t(copy.clickupId)}</th>
              <th>{t(copy.profession)}</th>
              <th>{t(copy.active)}</th>
              <th>{t(copy.actions)}</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td colSpan={8}>{t(copy.loading)}</td>
              </tr>
            ) : items.length === 0 ? (
              <tr>
                <td colSpan={8}>{t(copy.empty)}</td>
              </tr>
            ) : (
              items.map((item) => (
                <tr key={item.id}>
                  <td dir="ltr">{item.code}</td>
                  <td>
                    <strong className="client-name">{item.name}</strong>
                  </td>
                  <td dir="ltr">{item.phone ?? "—"}</td>
                  <td dir="ltr">{item.telegram_user_id ?? "—"}</td>
                  <td dir="ltr">{item.clickup_user_id ?? "—"}</td>
                  <td>{t(professions[item.profession] ?? { ar: item.profession, en: item.profession })}</td>
                  <td>{item.is_active ? t(copy.active) : t(copy.inactive)}</td>
                  <td>
                    <div className="row-actions">
                      <button className="btn" type="button" onClick={() => startEdit(item)}>
                        {t(copy.editEmployee)}
                      </button>
                      <button className="btn" type="button" onClick={() => void remove(item.id)}>
                        {t(copy.deleteEmployee)}
                      </button>
                    </div>
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
