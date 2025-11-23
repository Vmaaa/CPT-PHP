function getFechaHoy() {
  const d = new Date();
  return {
    desde: new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0),
    hasta: new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23, 59, 59),
  };
}

function getFechaSemana() {
  const d = new Date();
  const monday = new Date(
    d.setDate(d.getDate() - (d.getDay() === 0 ? 6 : d.getDay() - 1)),
  );
  return {
    desde: new Date(
      monday.getFullYear(),
      monday.getMonth(),
      monday.getDate(),
      0,
      0,
      0,
    ),
    hasta: new Date(),
  };
}

function getFechaMes() {
  const d = new Date();
  return {
    desde: new Date(d.getFullYear(), d.getMonth(), 1, 0, 0, 0),
    hasta: new Date(),
  };
}

function safeStamp() {
  return new Date().toISOString().slice(0,19).replace(/[T:]/g,"-").replace(/\s+/g,"_");
}

// (opcional, ya que lo usas seguido)
function toDateInput(d) {
  return d.toISOString().slice(0, 10);
}

/* ============================================================
      UTILS
  ============================================================ */
const getResultadoBadge = (r) => ({
  VENTA: { class: "badge-success", text: "Venta" },
  INCOMPLETA: { class: "badge-error", text: "Incompleta" },
  NO_INTERESADO: { class: "badge-warning", text: "No Interesado" },
}[r] || { class: "badge-info", text: r });

const truncate = (t, l) => !t ? "-" : t.length > l ? t.slice(0, l) + "..." : t;

function formatDateTimeFromLocal(ts) {
  if (!ts) return "-";
  const s = (typeof ts === "string" && !ts.includes("T")) ? ts.replace(" ", "T") : ts;
  const d = new Date(s);
  if (isNaN(d.getTime())) return "-";
  return d.toLocaleString("es-ES", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatDateOnly(ts) {
  if (!ts) return "-";
  const s =
    ts instanceof Date ? ts.toISOString() :
    (typeof ts === "number" ? new Date(ts).toISOString() :
    (typeof ts === "string" && !ts.includes("T") ? ts.replace(" ", "T") : ts));
  const d = new Date(s);
  return isNaN(d.getTime()) ? "-" : d.toLocaleDateString("es-ES", {
    day: "2-digit", month: "short", year: "numeric",
  });
}

function formatCurrency(v) {
  return (Number(v) || 0).toLocaleString("es-MX", {
    style: "currency",
    currency: "MXN",
  });
}
