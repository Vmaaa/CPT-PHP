const ACCOUNT_API_URL = API_URL + "/account/";

document.addEventListener("DOMContentLoaded", async () => {
  const user = await fetchAccountData();
  if (user) renderAccountDetails(user);
});

async function fetchAccountData() {
  try {
    const response = await CookieManager.fetchWithAuth(`${ACCOUNT_API_URL}`);
    if (!response.ok) return null;
    const json = await response.json();
    return json.data?.[0] || null;
  } catch {
    return null;
  }
}


function renderAccountDetails(user) {

  document.getElementById("email").value = user.acco_email || "";
  document.getElementById("status").value =
    user.acco_status == 1 ? "Activo" : "Inactivo";

  const role = user.acco_role;

  const teacherFields = document.getElementById("teacher-fields");
  const studentFields = document.getElementById("student-fields");
  const adminBanner = document.getElementById("admin-banner");
  const accountCard = document.getElementById("account-card");

  teacherFields.style.display = "none";
  studentFields.style.display = "none";
  adminBanner.style.display = "none";
  accountCard.style.border = "";

  // ================= STUDENT =================
  if (role === "student") {

    document.getElementById("role").value = "Cuenta Estudiante";
    studentFields.style.display = "block";

    renderStudents(user.students || []);
  }

  // ================= PROFESSOR =================
  if (role === "professor") {

    document.getElementById("role").value = "Profesor";
    teacherFields.style.display = "block";

    loadAcademicData(user);
  }

  // ================= ADMIN =================
  if (role === "admin") {

    document.getElementById("role").value = "Administrador";
    teacherFields.style.display = "block";
    adminBanner.style.display = "block";
    accountCard.style.border = "2px solid var(--primary)";

    loadAcademicData(user);
  }
}

function renderStudents(students) {

  const container = document.getElementById("students-container");
  container.innerHTML = "";

  if (!students.length) {
    container.innerHTML = "<p>No hay estudiantes asociados.</p>";
    return;
  }

  students.forEach((student, index) => {

    const wrapper = document.createElement("div");
    wrapper.style.marginBottom = "25px";
    wrapper.style.padding = "15px";
    wrapper.style.border = "1px solid #eee";
    wrapper.style.borderRadius = "6px";

    wrapper.innerHTML = `
      <h4 style="margin-bottom:15px;">
        Estudiante ${index + 1}
      </h4>

      <div class="form-group">
        <label>Nombre</label>
        <input type="text" class="form-control"
               value="${student.name || ''}" readonly>
      </div>

      <div class="form-group">
        <label>Matrícula</label>
        <input type="text" class="form-control"
               value="${student.school_id_number || ''}" readonly>
      </div>

      <div class="form-group">
        <label>Carrera</label>
        <input type="text" class="form-control"
               value="${student.id_career ? student.id_career + ' - ' + student.career : '-'}" readonly>
      </div>

      <div class="form-group">
        <label>Clase</label>
        <input type="text" class="form-control"
               value="${student.id_class ? student.id_class + ' - ' + student.class_name : '-'}" readonly>
      </div>
    `;

    container.appendChild(wrapper);
  });
}

function loadAcademicData(user) {

  document.getElementById("academia").value =
    user.academia || "";

  document.getElementById("level_of_education").value =
    user.level_of_education || "";

  const presidentBadge = document.getElementById("badge-president");
  const advisorBadge = document.getElementById("badge-advisor");

  presidentBadge.textContent = "Presidente de Academia";
  advisorBadge.textContent = "Asesor";

  if (user.is_president == 1) {
    presidentBadge.classList.add("badge-success");
  }
    if (user.is_advisor == 1) {
    advisorBadge.classList.add("badge-success");
  }
}
