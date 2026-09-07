const API_URL = import.meta.env.VITE_API_URL ?? "http://127.0.0.1:8000/api";
const TOKEN_KEY = "hoc-staff-token";

export function getToken() {
  return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string | null) {
  if (token) localStorage.setItem(TOKEN_KEY, token);
  else localStorage.removeItem(TOKEN_KEY);
}

export type User = {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
};

export type Client = {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
  telegram_user_id: string | null;
  odoo_partner_id?: string | null;
  requests_count?: number;
};

export type Brief = {
  department: string;
  brief: string | null;
  clickup_task_id: string | null;
};

export type ServiceRequest = {
  id: number;
  number: string;
  title: string;
  description: string;
  status: string;
  source: string;
  odoo_quotation_id?: string | null;
  odoo_invoice_id?: string | null;
  briefs?: Brief[];
  client?: Client;
  created_at: string | null;
};

export type Employee = {
  id: number;
  code: string;
  name: string;
  phone: string | null;
  email: string | null;
  telegram_user_id: string | null;
  clickup_user_id: string | null;
  profession: string;
  notes: string | null;
  is_active: boolean;
};

type Envelope<T> = { data: T; message?: string };

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const headers = new Headers(init?.headers);
  headers.set("Accept", "application/json");
  if (init?.body) headers.set("Content-Type", "application/json");
  const token = getToken();
  if (token) headers.set("Authorization", `Bearer ${token}`);

  const response = await fetch(`${API_URL}${path}`, { ...init, headers });
  if (response.status === 401) {
    setToken(null);
  }
  if (!response.ok) {
    const body = await response.json().catch(() => ({}));
    throw new Error(body.message ?? `HTTP ${response.status}`);
  }
  return response.json() as Promise<T>;
}

export const api = {
  login(email: string, password: string) {
    return request<Envelope<{ token: string; user: User }>>("/auth/login", {
      method: "POST",
      body: JSON.stringify({ email, password }),
    });
  },
  me() {
    return request<Envelope<User>>("/auth/me");
  },
  logout() {
    return request<Envelope<null>>("/auth/logout", { method: "POST" });
  },
  overview() {
    return request<Envelope<{ clients: number; requests: number; by_status: Record<string, number> }>>(
      "/admin/overview",
    );
  },
  requests(status?: string) {
    const query = status ? `?status=${encodeURIComponent(status)}` : "";
    return request<{ data: ServiceRequest[] }>(`/admin/requests${query}`);
  },
  request(id: string) {
    return request<Envelope<ServiceRequest>>(`/admin/requests/${id}`);
  },
  updateStatus(id: number, status: string) {
    return request<Envelope<ServiceRequest>>(`/admin/requests/${id}`, {
      method: "PATCH",
      body: JSON.stringify({ status }),
    });
  },
  clients() {
    return request<{ data: Client[] }>("/admin/clients");
  },
  employees() {
    return request<{ data: Employee[] }>("/admin/employees");
  },
  createEmployee(payload: Partial<Employee> & { name: string; profession: string }) {
    return request<Envelope<Employee>>("/admin/employees", {
      method: "POST",
      body: JSON.stringify(payload),
    });
  },
  updateEmployee(id: number, payload: Partial<Employee>) {
    return request<Envelope<Employee>>(`/admin/employees/${id}`, {
      method: "PUT",
      body: JSON.stringify(payload),
    });
  },
  deleteEmployee(id: number) {
    return request<Envelope<null>>(`/admin/employees/${id}`, { method: "DELETE" });
  },
};
