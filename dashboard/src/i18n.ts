export type Locale = "ar" | "en";
export type Copy = { ar: string; en: string };

const STORAGE = "hoc-dash-locale";

export function readLocale(): Locale {
  const stored = localStorage.getItem(STORAGE);
  return stored === "en" || stored === "ar" ? stored : "ar";
}

export function applyLocale(locale: Locale) {
  localStorage.setItem(STORAGE, locale);
  document.documentElement.lang = locale;
  document.documentElement.dir = locale === "ar" ? "rtl" : "ltr";
}

export const copy = {
  brand: { ar: "لوحة HOC", en: "HOC Dashboard" },
  brandMark: { ar: "إدارة الطلبات الحية", en: "Live request operations" },
  overview: { ar: "نظرة عامة", en: "Overview" },
  overviewLede: { ar: "ملخص الحسابات القادمة من تيليجرام ومسار كل طلب.", en: "A snapshot of Telegram accounts and every request stage." },
  requests: { ar: "الطلبات", en: "Requests" },
  requestsLede: { ar: "تابع الطلبات الواردة من البوت وحدّث حالتها.", en: "Follow bot requests and move their status." },
  clients: { ar: "العملاء", en: "Clients" },
  clientsLede: { ar: "الحسابات تُنشأ فقط عبر بوت تيليجرام.", en: "Accounts are created only through the Telegram bot." },
  employees: { ar: "الموظفون", en: "Employees" },
  employeesLede: { ar: "سجل الفريق، آيدي تيليجرام وClickUp، والمهنة لإيصال الطلبات.", en: "Team records, Telegram and ClickUp IDs, and the role used to route requests." },
  addEmployee: { ar: "إضافة موظف", en: "Add employee" },
  editEmployee: { ar: "تعديل", en: "Edit" },
  saveEmployee: { ar: "حفظ الموظف", en: "Save employee" },
  deleteEmployee: { ar: "حذف", en: "Delete" },
  employeeName: { ar: "الاسم", en: "Name" },
  employeeCode: { ar: "الرقم", en: "Staff number" },
  clickupId: { ar: "آيدي ClickUp", en: "ClickUp ID" },
  profession: { ar: "المهنة", en: "Role" },
  notes: { ar: "ملاحظات", en: "Notes" },
  active: { ar: "نشط", en: "Active" },
  inactive: { ar: "متوقف", en: "Inactive" },
  actions: { ar: "إجراءات", en: "Actions" },
  cancel: { ar: "إلغاء", en: "Cancel" },
  openStaffBot: { ar: "فتح بوت الموظفين", en: "Open staff bot" },
  login: { ar: "دخول الفريق", en: "Staff login" },
  loginLede: { ar: "هذه الصفحة لفريق العمل فقط.", en: "This page is for the studio team only." },
  signupTitle: { ar: "إنشاء حساب", en: "Create an account" },
  signupLede: { ar: "لا يوجد تسجيل بالبريد. افتح البوت ثم اضغط Start.", en: "There is no email signup. Open the bot and press Start." },
  signupCta: { ar: "فتح بوت تيليجرام", en: "Open Telegram bot" },
  signupStep1: { ar: "افتح البوت من الزر أدناه.", en: "Open the bot from the button below." },
  signupStep2: { ar: "اضغط Start لإنشاء حسابك.", en: "Press Start to create your account." },
  signupStep3: { ar: "أرسل طلبك بأمر /new.", en: "Send a request with /new." },
  recent: { ar: "آخر الطلبات", en: "Latest requests" },
  back: { ar: "العودة للطلبات", en: "Back to requests" },
  staffChip: { ar: "فريق العمل", en: "Studio staff" },
  email: { ar: "البريد", en: "Email" },
  password: { ar: "كلمة المرور", en: "Password" },
  submit: { ar: "دخول", en: "Sign in" },
  logout: { ar: "خروج", en: "Log out" },
  language: { ar: "English", en: "العربية" },
  clientsCount: { ar: "العملاء", en: "Clients" },
  requestsCount: { ar: "الطلبات", en: "Requests" },
  number: { ar: "الرقم", en: "Number" },
  title: { ar: "العنوان", en: "Title" },
  status: { ar: "الحالة", en: "Status" },
  source: { ar: "المصدر", en: "Source" },
  client: { ar: "العميل", en: "Client" },
  all: { ar: "الكل", en: "All" },
  save: { ar: "حفظ الحالة", en: "Save status" },
  markPaid: { ar: "تأكيد الدفع", en: "Mark as paid" },
  paymentBlocked: { ar: "لا يمكن تأكيد الدفع قبل إرسال عرض السعر.", en: "Payment cannot be confirmed before a quotation is sent." },
  saveFailed: { ar: "تعذر حفظ الحالة.", en: "Could not save the status." },
  description: { ar: "الوصف", en: "Description" },
  phone: { ar: "الهاتف", en: "Phone" },
  telegram: { ar: "تيليجرام", en: "Telegram" },
  odooPartner: { ar: "عميل Odoo", en: "Odoo partner" },
  odooQuote: { ar: "عرض Odoo", en: "Odoo quotation" },
  odooInvoice: { ar: "فاتورة Odoo", en: "Odoo invoice" },
  clickupTasks: { ar: "مهام ClickUp", en: "ClickUp tasks" },
  empty: { ar: "لا توجد بيانات بعد.", en: "No records yet." },
  loading: { ar: "جارٍ التحميل…", en: "Loading…" },
  forbidden: { ar: "هذا الحساب ليس حساب فريق.", en: "This account is not a staff user." },
  failed: { ar: "تعذر تسجيل الدخول.", en: "Could not sign in." },
} satisfies Record<string, Copy>;

export const statuses: Record<string, Copy> = {
  draft: { ar: "مسودة", en: "Draft" },
  submitted: { ar: "مُرسل", en: "Submitted" },
  ai_analyzing: { ar: "تحليل الذكاء", en: "AI analyzing" },
  quotation_sent: { ar: "عرض سعر", en: "Quotation sent" },
  payment_confirmed: { ar: "مدفوع", en: "Paid" },
  in_progress: { ar: "قيد التنفيذ", en: "In progress" },
  ready_for_review: { ar: "جاهز للمراجعة", en: "Ready for review" },
  revision_in_progress: { ar: "تعديل", en: "Revision" },
  approved: { ar: "معتمد", en: "Approved" },
  completed: { ar: "مكتمل", en: "Completed" },
  cancelled: { ar: "ملغى", en: "Cancelled" },
};

export const sources: Record<string, Copy> = {
  telegram: { ar: "تيليجرام", en: "Telegram" },
  website: { ar: "الموقع", en: "Website" },
};

export const professions: Record<string, Copy> = {
  sales: { ar: "مبيعات", en: "Sales" },
  branding: { ar: "هوية بصرية", en: "Branding" },
  "3d_visualization": { ar: "تصوير ثلاثي", en: "3D visualization" },
  media: { ar: "إعلام", en: "Media" },
  web: { ar: "ويب", en: "Web" },
  print: { ar: "طباعة", en: "Print" },
  creative_direction: { ar: "إخراج إبداعي", en: "Creative direction" },
};
