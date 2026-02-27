const ACCOUNT_API_URL = API_URL + "/account/";
const STUDENT_API_URL = API_URL + "/student/";

document.addEventListener("DOMContentLoaded", async () => {
  await refreshAccountData();
});

async function refreshAccountData() {
  const user = await fetchAccountData();
  if (user) renderAccountDetails(user);
}

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

    renderStudents(user.students || [], user.acco_id);
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

function renderStudents(students, acco_id) {

  const container = document.getElementById("students-container");
  const addStudentButton = document.getElementById("add-student-btn");

  addStudentButton.addEventListener("click", () => openStudentModal(null, acco_id));

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
      <div class="row">
        <h4>
          Estudiante ${index + 1}
        </h4>
        <button class="btn btn-info">
          <i class="fas fa-pencil"></i> Editar
        </button>
        <button class="btn btn-error" style="margin-left: 10px;">
          <i class="fas fa-trash"></i> Eliminar
        </button>
      </div>
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
        <label>CURP</label>
        <input type="text" class="form-control"
               value="${student.curp || ''}" readonly>
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

    //add event listener to edit button
    const editButton = wrapper.querySelector(".btn-info");
    //prevent default behavior of button
    editButton.addEventListener("click", (e) => e.preventDefault());
    editButton.addEventListener("click", () => openStudentModal(student, acco_id));

    const deleteButton = wrapper.querySelector(".btn-error");
    deleteButton.addEventListener("click", (e) => e.preventDefault());
    deleteButton.addEventListener("click", () => deleteStudent(student.id_student));
  });
}

function openStudentModal(student, acco_id) {
  const form = document.getElementById("student-info-form");
  form.reset();
  document.getElementById("student-acco-id").value = acco_id;
  if (student) {
  document.getElementById("student-id").value = student.id_student || "";
  document.getElementById("student-name").value = student.name || "";
  document.getElementById("student-school-id").value = student.school_id_number || "";
  document.getElementById("student-curp").value = student.curp || "";
  }
  openModal("student-modal");
}

function closeStudentModal() {
  closeModal("student-modal");
}

//event listener for form submission
document.getElementById("student-info-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  
  const studentId = document.getElementById("student-id").value;
  const accoId = document.getElementById("student-acco-id").value;
  const name = document.getElementById("student-name").value;
  const schoolId = document.getElementById("student-school-id").value;
  const curp = document.getElementById("student-curp").value;


  if(!name || !schoolId || !curp ) {
    SwalMessage({
      title: "Error",
      text: "Por favor completa todos los campos.",
      icon: "error",
    })
    return;
  }

  const method = studentId ? "PUT" : "POST";
  const formData = new FormData();
  formData.append("acco_id", accoId);
  formData.append("name", name);
  formData.append("school_id_number", schoolId);
  formData.append("curp", curp);
  if (studentId) formData.append("id_student", studentId);
  
  try {
  const response = await CookieManager.fetchWithAuth(`${STUDENT_API_URL}`, {
      method,
      body: formData,
      });
    
    if (!response.ok) {
      const errorData = await response.json();
      SwalMessage({
        title: "Error",
        text: errorData.error || "Ocurrió un error al guardar el estudiante.",
        icon: "error",
      });
      return;
    }
    
    await refreshAccountData();
    closeStudentModal();
    SwalMessage({
      title: "Éxito",
      text: `Estudiante ${studentId ? "actualizado" : "creado"} correctamente.`,
      icon: "success",
    });
  }
  catch (error) {
    SwalMessage({
      title: "Error",
      text: "Ocurrió un error al guardar el estudiante.",
      icon: "error",
    });
  }
}
);

async function deleteStudent(studentId) {
 const confirmResult = await SwalConfirm({
    title: "¿Eliminar estudiante?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });
  
  if (!confirmResult) return;
   
  const formData = new FormData();
  formData.append("id_student", studentId);
  try {
    const response = await CookieManager.fetchWithAuth(`${STUDENT_API_URL}`, {
      method: "DELETE",
      body: formData,
    });
  
    if (!response.ok) {
      const errorData = await response.json();
      SwalMessage({
        title: "Error",
        text: errorData.error || "Ocurrió un error al eliminar el estudiante.",
        icon: "error",
      });
      return;
    }
  }
  catch (error) {
    SwalMessage({
      title: "Error",
      text: "Ocurrió un error al eliminar el estudiante.",
      icon: "error",
    });
    return;
  }

  await refreshAccountData();
  SwalMessage({
    title: "Éxito",
    text: "Estudiante eliminado correctamente.",
    icon: "success",
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
