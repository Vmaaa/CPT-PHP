const ACCOUNT_API_URL = API_URL + "/account/";

document.addEventListener("DOMContentLoaded", async () => {
  const user = await fetchAccountData();
  if (user) {
    renderAccountDetails(user);
  }
});

async function fetchAccountData() {
  try {
    const response = await CookieManager.fetchWithAuth(
      `${ACCOUNT_API_URL}`,
    );
    if (!response.ok) return null;
    const json = await response.json();
    return json.data?.[0] || null;
  } catch {
    return null;
  }
}

function renderAccountDetails(user) {
  // Campos comunes
  document.getElementById("name").value = user.name || "";
  document.getElementById("email").value = user.acco_email || "";
  document.getElementById("role").value = user.acco_role || "";
  document.getElementById("status").value = user.acco_status == 1
    ? "Activo"
    : "Inactivo";
  document.getElementById("curp").value = user.curp || "";

  const isStudent = user.acco_role === "student";
  const isTeacher = user.acco_role === "professor" || user.id_professor;

  // Mostrar/ocultar campos según el tipo de usuario
  const teacherFields = document.getElementById("teacher-fields");
  const studentFields = document.getElementById("student-fields");

  if (isTeacher) {
    teacherFields.style.display = "block";
    studentFields.style.display = "none";

    document.getElementById("academia").value = user.academia || "";
    document.getElementById("level_of_education").value =
      user.level_of_education || "";
    document.getElementById("is_president").value = user.is_president == 1
      ? "Sí"
      : "No";
    document.getElementById("is_advisor").value = user.is_advisor == 1
      ? "Sí"
      : "No";
  } else if (isStudent) {
    studentFields.style.display = "block";
    teacherFields.style.display = "none";

    document.getElementById("school_id_number").value = user.school_id_number ||
      "";
    document.getElementById("id_career").value = user.id_career || "";
    document.getElementById("id_class").value = user.id_class || "-";
  }
}
