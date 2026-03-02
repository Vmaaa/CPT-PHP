CLASS_API_URL = API_URL + "/class/";
CLASS_ASSIGNMENT_API_URL = API_URL + "/class/assigment/";
CLASS_ASSIGMENT_SUBMISSION_API_URL = CLASS_ASSIGNMENT_API_URL + "/submission/";
STUDENTS_API_URL = API_URL + "/student/";
CLASS_STUDENTS_API_URL = API_URL + "/class/student/";

document.addEventListener("DOMContentLoaded", async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const classId = urlParams.get("id_class");
  if (classId) {
    const classDetails = await loadSpecificClass(classId);
    await fetchAvailableStudents(classDetails.id_career);
  } else {
    SwalMessage({
      tile: "Error",
      text: "Sucedió un error al cargar la clase",
      icon: "error",
    });
    setTimeout(() => {
      window.location.href = BASE_URL + "/pages/classes.php";
    }, 2000);
  }
});

async function loadSpecificClass(classId) {
  const queryParams = new URLSearchParams({ id_class: classId });

  try {
    const response = await CookieManager.fetchWithAuth(
      `${CLASS_API_URL}?${queryParams.toString()}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      },
    );

    if (!response.ok) {
      throw new Error("Error al cargar las clases");
    }

    const result = await response.json();
    if (result.data.length === 0) {
      SwalMessage({
        tile: "Error",
        text: "Clase no encontrada",
        icon: "error",
      });
      setTimeout(() => {
        window.location.href = BASE_URL + "/pages/classes.php";
      }, 2000);
      return;
    }
    renderClassDetails(result.data[0]);
    return result.data[0];
  } catch (error) {
    console.error(error);
    document.getElementById("class-details-container").innerHTML =
      `<p class="error-text">Error al cargar la clase. Por favor, inténtelo de nuevo más tarde.</p>`;
  }
}
function renderClassInfo(classData) {
  const container = document.getElementById("class-info");

  container.innerHTML = `
    <h3>Información de la Clase</h3>
    <p><strong>Nombre:</strong> ${classData.name}</p>
    <p><strong>Carrera:</strong> ${classData.career}</p>
  `;
}
function renderProfessors(professors = []) {
  const container = document.getElementById("class-professors");

  if (professors.length === 0) {
    container.innerHTML =
      `<h3>Profesores</h3><p>No hay profesores asignados</p>`;
    return;
  }

  container.innerHTML = `
    <h3>Profesores (${professors.length})</h3> 
    <ul>
      ${
    professors
      .map(
        (p) => `
          <li>
            ${p.name}
          </li>
        `,
      )
      .join("")
  }
    </ul>
  `;
}

function renderStudents(students = [], classId, careerId) {
  const container = document.getElementById("class-students");

  const header = `
    <div class="name-with-action">
      <h3>Alumnos (${students.length})</h3>
      <button class="btn btn-primary" onclick="openEditClassStudentsModal(${classId}, ${careerId})">
        <i class="fas fa-edit"></i> Editar Alumnos
      </button>
    </div>
  `;

  if (!students.length) {
    container.innerHTML = `
      ${header}
      <p>No hay alumnos inscritos</p>
    `;
    return;
  }

  //group students by acco_id
  const studentsByAccount = groupStudentsByAccount(students);

  //alternate bold for each account group
  const studentsList = Object.values(studentsByAccount)
    .map((group) => {
      let groupHTML = "<h4>Cuenta: " + group[0].acco_id + "</h4>";
      groupHTML += group
        .map(
          (s) => `
            <li>
              <span class="name">${s.name}</span>
              <span class="name-subtitle">${s.school_id_number}</span>
            </li>
          `,
        )
        .join("");
      return groupHTML
    })
    .join("");

  container.innerHTML = `
    ${header}
    <ul>
      ${studentsList}
    </ul>
  `;
}
function renderAssignments(assignments = [], classId) {
  const container = document.getElementById("class-assignments");

  const header = `
    <div class="name-with-action">
      <h3>Actividades</h3>
      <button class="btn btn-primary" onclick="openNewAssignmentModal(${classId})">
        <i class="fas fa-plus"></i> Nueva Actividad
      </button>
    </div>
  `;

  if (!assignments.length) {
    container.innerHTML = `
      ${header}
      <p>No hay actividades creadas</p>
    `;
    return;
  }

  const assignmentsList = assignments
    .map(
      (a) => `
        <li class="assignment-item" onclick="selectAssignment(${a.id_assigment}, this)">
          ${a.title}
        </li>
      `,
    )
    .join("");

  container.innerHTML = `
    ${header}
    <ul>
      ${assignmentsList}
    </ul>
  `;
  // Guardamos para referencia
  window.__classAssignments = assignments;
}

function selectAssignment(idAssignment, el) {
  document
    .querySelectorAll(".assignment-item")
    .forEach((item) => item.classList.remove("active"));

  el.classList.add("active");

  const assignment = window.__classAssignments.find(
    (a) => a.id_assigment === idAssignment,
  );

  if (!assignment) return;

  renderAssignmentDetail(assignment);
}

function renderAssignmentDetail(assignment) {
  const container = document.getElementById("assignment-detail");
  container.classList.remove("assignment-empty");
  container.classList.add("assignment-detail");
  container.innerHTML = "";

  // Name with action
  const nameWithAction = document.createElement("div");
  nameWithAction.className = "name-with-action";

  const title = document.createElement("h3");
  title.textContent = assignment.title;
  nameWithAction.appendChild(title);

  const editButton = document.createElement("button");
  editButton.className = "btn btn-primary";
  if (!assignment.can_be_edited) editButton.disabled = true;
  editButton.innerHTML = '<i class="fas fa-edit"></i> Editar Actividad';
  editButton.addEventListener("click", () => {
    openEditAssignmentModal(assignment);
  });
  nameWithAction.appendChild(editButton);

  container.appendChild(nameWithAction);

  // Assignment detail grid
  const detailGrid = document.createElement("div");
  detailGrid.className = "assignment-detail-grid";

  // Left column: assignment info
  const infoCol = document.createElement("div");
  infoCol.className = "assignment-info";
  infoCol.innerHTML = `
    <h3><strong>Datos de la actividad</strong></h3>
    <p><strong>Descripción:</strong> ${assignment.description || "Sin descripción"}</p>
    <p><strong>Fecha de límite de entrega:</strong> ${assignment.due_date}</p>
    <h4>Entregados</h4>
  `;

  const deliveredList = document.createElement("ul");
  deliveredList.className = "delivered-list";

  if (assignment.submissions?.length) {
    assignment.submissions.forEach((s) => {
      const firstStudent = s.students[0];
      const firstName = firstStudent.name
        ? `(${firstStudent.school_id_number}) ${firstStudent.name}`
        : firstStudent.school_id_number;
      const extraCount = s.students.length > 1 ? ` y ${s.students.length - 1} más` : "";

      const li = document.createElement("li");
      li.setAttribute("data-assigment-submission-id", s.assigment_submission_id);
      li.innerHTML = `Cuenta: ${s.acco_id} <br> ${firstName}${extraCount}`;
      li.style.cursor = "pointer";
      li.addEventListener("click", () => {
        openViewSubmissionModal(s);
      });
      deliveredList.appendChild(li);
    });
  } else {
    const li = document.createElement("li");
    li.textContent = "Nadie ha entregado aún";
    deliveredList.appendChild(li);
  }

  infoCol.appendChild(deliveredList);

  // Right column: assignment preview
  const previewCol = document.createElement("div");
  previewCol.className = "assignment-preview";
  const previewTitle = document.createElement("h3");
  previewTitle.innerHTML = "<strong>Documento de la asignación</strong>";
  previewCol.appendChild(previewTitle);

  if (assignment.file_url) {
    const iframe = document.createElement("iframe");
    iframe.src = assignment.file_url;
    iframe.loading = "lazy";
    previewCol.appendChild(iframe);
  } else {
    const p = document.createElement("p");
    p.textContent = "No hay archivo asociado";
    previewCol.appendChild(p);
  }

  detailGrid.appendChild(infoCol);
  detailGrid.appendChild(previewCol);

  container.appendChild(detailGrid);
}


function renderClassDetails(classData) {
  renderClassInfo(classData);
  renderProfessors(classData.professors);
  renderStudents(classData.students, classData.id_class, classData.id_career);
  renderAssignments(classData.assigments, classData.id_class);
}

function openViewSubmissionModal(submission) {

  document.getElementById("view-submission-title").textContent =
    "Entrega de asignación";

  document.getElementById("view-submission-id").value = submission.id_assigment_submission;

  document.getElementById("view-submission-date").textContent =
    submission.submitted_at || "";

  document.getElementById("view-submission-rate-date").textContent =
    submission.graded_at ? submission.graded_at : "Sin calificar";

  document.getElementById("view-submission-grade").value =
    submission.grade ?? "Sin calificar";

  document.getElementById("view-submission-feedback").value =
    submission.feedback ?? "Sin retroalimentación";

  // Archiv
  if (submission.file_url) {
    //load iframe with file
    document.getElementById("submission-document").src = submission.file_url;
  }


  // Estudiantes
  const studentsDiv = document.getElementById("submission-students");
  studentsDiv.innerHTML = "";

  submission.students.forEach((student, index) => {

    const wrapper = document.createElement("div");

    wrapper.innerHTML = `
      <div class="form-group">
        <label>Estudiante ${index+ 1}</label>
        <p>(${student.school_id_number}) ${student.name}</p>
      </div>
    `;

    studentsDiv.appendChild(wrapper);
  });
  openModal("modal-view-submission");
}

function closeViewSubmissionModal() {
  closeModal("modal-view-submission");
}


function openNewAssignmentModal(classId) {
  const assignmentFeedback = "Puedes subir un archivo PDF de hasta 5MB como parte de la asignación, esto es opcional. Si decides subir un archivo, asegúrate de que esté en formato PDF y no exceda el límite de tamaño para evitar problemas al guardar la asignación.";
  document.getElementById("assignment-modal-title").textContent =
    "Nueva asignación";

  document.getElementById("assignment-id").value = "";
  document.getElementById("assignment-class-id").value = classId;
  document.getElementById("assignment-title").value = "";
  document.getElementById("assignment-description").value = "";
  document.getElementById("assignment-due-date").value = "";
  document.getElementById("assignment-file").value = "";
  document.getElementById("delete-assignment-file-container").style.display =
    "none";
  document.getElementById("assignment-feedback").innerText = assignmentFeedback;
  // Reset checkbox
  document.getElementById("delete-assignment-file").checked = false;

  window.openModal("modal-assignment");
}


function openEditAssignmentModal(assignment) {
  const assigmentFeedback = assignment.file_url ?
  "Ya subiste un archivo para esta asignación, si subes un nuevo archivo se reemplazará el anterior. Si quieres eliminar el archivo sin subir uno nuevo, marca la casilla de eliminar archivo y guarda los cambios"
  : "No has subido un archivo para esta asignación, puedes subir uno nuevo o dejarlo sin archivo";
  if (!assignment.can_be_edited) {
    SwalMessage({
      tile: "Error",
      text: "La asignación no puede ser editada, ya que no la creaste tú",
      icon: "error",
    });
    return;
  }
  document.getElementById("assignment-modal-title").textContent =
    "Editar asignación";

  document.getElementById("assignment-id").value = assignment.id_assigment;

  document.getElementById("assignment-class-id").value = assignment.id_class;

  document.getElementById("assignment-title").value = assignment.title;

  document.getElementById("assignment-description").value =
    assignment.description || "";

  document.getElementById("assignment-due-date").value = assignment.due_date
    .replace(" ", "T");

  document.getElementById("assignment-file").value = "";

  document.getElementById("delete-assignment-file-container").style.display =
    assignment.file_url ? "block" : "none";

  // Reset checkbox
  document.getElementById("delete-assignment-file").checked = false;

  document.getElementById("assignment-feedback").innerText = assigmentFeedback;

  window.openModal("modal-assignment");
}

async function saveRateSubmission() {
  const grade = document.getElementById("view-submission-grade").value;
  const feedback = document.getElementById("view-submission-feedback").value;
  const submissionId = document.getElementById("view-submission-id").value;
  
  const formData = new FormData();
  formData.append("grade", grade);
  formData.append("feedback", feedback);
  formData.append("id_assigment_submission", submissionId);
  
  const response = await CookieManager.fetchWithAuth(
    CLASS_ASSIGMENT_SUBMISSION_API_URL,
    {
      method: "PUT",
      body: formData,
    },
  );

  if (!response.ok) {
    SwalMessage({
      tile: "Error",
      text: "Sucedió un error al calificar la entrega",
      icon: "error",
    });
    return;
  }
  
  SwalMessage({
    tile: "Éxito",
    text: "Entrega calificada correctamente",
    icon: "success",
  });
  closeViewSubmissionModal();
  //reload class details
  const urlParams = new URLSearchParams(window.location.search);
  const classIdParam = urlParams.get("id_class");
  if (classIdParam) {
    await loadSpecificClass(classIdParam);
    document.getElementById("assignment-detail").innerHTML =
      `<p class="assignment-empty">Seleccione una asignación para ver los detalles</p>`;

  }

}

async function submitAssignment() {
  const id = document.getElementById("assignment-id").value;
  let editing = false;
  if (id) {
    editing = true;
  }
  const fileInput = document.getElementById("assignment-file");
  const file = fileInput.files[0];
  const deleteCurrentFileCheckbox = document.getElementById(
    "delete-assignment-file",
  );

  // Validación archivo
  if (file) {
    if (file.type !== "application/pdf") {
      SwalMessage({
        tile: "Error",
        text: "El archivo debe ser un PDF",
        icon: "error",
      });
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      SwalMessage({
        tile: "Error",
        text: "El archivo no debe superar los 5MB",
        icon: "error",
      });
      return;
    }
  }

  const formData = new FormData();
  formData.append(
    "id_class",
    document.getElementById("assignment-class-id").value,
  );
  formData.append(
    "title",
    document.getElementById("assignment-title").value,
  );
  formData.append(
    "description",
    document.getElementById("assignment-description").value,
  );

  // Due date in format YYYY-MM-DD HH:MM:SS
  const dueDateInput = document.getElementById("assignment-due-date").value;
  const dueDate = new Date(dueDateInput);
  const dueDateFormatted = dueDate
    .toISOString()
    .slice(0, 19)
    .replace("T", " ");
  formData.append("due_date", dueDateFormatted);

  if (editing) {
    formData.append("id_assigment", id);
  }

  if (file) {
    formData.append("file", file);
  }

  if (editing && deleteCurrentFileCheckbox.checked) {
    formData.delete("file");
    formData.append("remove_url", "1");
  }

  const response = await CookieManager.fetchWithAuth(
    CLASS_ASSIGNMENT_API_URL,
    {
      method: editing ? "PUT" : "POST",
      body: formData,
    },
  );

  if (!response.ok) {
    SwalMessage({
      tile: "Error",
      text: "Sucedio un error al guardar la asignación",
      icon: "error",
    });
    return;
  }

  SwalMessage({
    tile: "Éxito",
    text: "Asignación" + (editing ? " editada" : " creada") + " correctamente",
    icon: "success",
  });
  window.closeModal("modal-assignment");
  //reload page
  const urlParams = new URLSearchParams(window.location.search);
  const classId = urlParams.get("id_class");
  if (classId) {
    await loadSpecificClass(classId);
    // Clear assignment detail
    if (editing) {
      document.getElementById("assignment-detail").innerHTML =
        `<p class="assignment-empty">Seleccione una asignación para ver los detalles</p>`;
    }
  }
}

async function fetchAvailableStudents(params) {
  const queryParams = new URLSearchParams(params);
  const response = await CookieManager.fetchWithAuth(
    `${STUDENTS_API_URL}?${queryParams.toString()}`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    },
  );

  if (!response.ok) {
    SwalsMessage({
      tile: "Error",
      text: "Error al cargar los alumnos disponibles",
      icon: "error",
    });
    return;
  }

  const result = await response.json();
  return result.data;
}

async function fetchClassStudents(classId) {
  const queryParams = new URLSearchParams({ id_class: classId });
  const response = await CookieManager.fetchWithAuth(
    `${CLASS_STUDENTS_API_URL}?${queryParams.toString()}`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    },
  );

  if (!response.ok) {
    SwalsMessage({
      tile: "Error",
      text: "Error al cargar los alumnos de la clase",
      icon: "error",
    });
    return;
  }

  const result = await response.json();
  return result.data;
}

async function openEditClassStudentsModal(classId, careerId) {
  document.getElementById("edit-students-class-id").value = classId;
  //load current students into the select
  const currentSelect = document.getElementById("current-students");
  currentSelect.innerHTML = "";
  const classStudents = await fetchClassStudents(classId);
  // group students by acco_id
  const classStudentsGrouped = groupStudentsByAccount(classStudents);

  //handle option groups by acco_id, each group will be and option group with the acco_id as label, and the students as options
  Object.entries(classStudentsGrouped).forEach(([accoId, students]) => {

    const optgroup = document.createElement("optgroup");
    optgroup.label = `Cuenta ${accoId}`;

    const option = document.createElement("option");
    option.value = accoId;

    const firstStudent = students[0];
    const extraCount = students.length - 1;

    if (extraCount > 0) {
      option.textContent =
        `(${firstStudent.school_id_number}) ${firstStudent.name} y ${extraCount} más...`;
    } else {
      option.textContent = `(${firstStudent.school_id_number}) ${firstStudent.name}`;
    }

    // Tooltip completo
    option.title = students
      .map(s => `(${s.school_id_number}) ${s.name}`)
      .join("\n");

    option.selected = true; // Marcar como seleccionado por ser estudiante actual de la clase

    optgroup.appendChild(option);
    currentSelect.appendChild(optgroup);

  });

  //load avaliable students into the select
  const select = document.getElementById("available-students");
  select.innerHTML = "";
  const avaliableStudents = await fetchAvailableStudents({
    id_career: careerId,
    "without_class": "1",
  });
  const avaliableStudentsGrouped = groupStudentsByAccount(avaliableStudents);
  Object.entries(avaliableStudentsGrouped).forEach(([accoId, students]) => {
  
    const optgroup = document.createElement("optgroup");
    optgroup.label = `Cuenta ${accoId}`;

    const option = document.createElement("option");
    option.value = accoId;

    const firstStudent = students[0];
    const extraCount = students.length - 1;

    if (extraCount > 0) {
      option.textContent =
        `(${firstStudent.school_id_number}) ${firstStudent.name} y ${extraCount} más...`;
  
    } else {
      option.textContent = `(${firstStudent.school_id_number}) ${firstStudent.name}`;
    }

    // Tooltip completo
    option.title = students
      .map(s => `(${s.school_id_number}) ${s.name}`)
      .join("\n");

    optgroup.appendChild(option);
    select.appendChild(optgroup);

  });

  window.openModal("modal-edit-students");
}

async function saveClassStudents() {
  const classId = document.getElementById("edit-students-class-id").value;
  const currentSelect = document.getElementById("current-students");
  const currentStudentIds = Array.from(currentSelect.options).map(
    (option) => option.selected ? option.value : null,
  );
  const availableSelect = document.getElementById("available-students");
  const availableStudentIds = Array.from(availableSelect.options).map(
    (option) => option.selected ? option.value : null,
  );
  const allStudentsIds = currentStudentIds.concat(availableStudentIds);
  const selectedStudentIds = allStudentsIds.filter((id) => id !== null);
  const selectedStudentInformation = Array.from(currentSelect.options)
    .concat(Array.from(availableSelect.options))
    .filter((option) => option.selected)
    .map((option) => ({
      name: option.value
    }));

  const confirmed = await SwalConfirm({
    title: "Confirmar cambios",
    text: "Las siguientes cuentas de estudiantes serán asignadas a la clase: " +
      selectedStudentInformation
        .map((s) => s.name)
        .join(", "),
    icon: "question",
    confirmButtonText: "Confirmar",
    cancelButtonText: "Cancelar",
  });

  if (!confirmed) {
    return;
  }

  const formData = new FormData();
  formData.append("id_class", classId);
  selectedStudentIds.forEach((id) => {
    formData.append("account_ids[]", id);
  });

  const response = await CookieManager.fetchWithAuth(
    CLASS_STUDENTS_API_URL,
    {
      method: "PUT",
      body: formData,
    },
  );

  if (!response.ok) {
    SwalMessage({
      tile: "Error",
      text: "Sucedió un error al guardar los alumnos de la clase",
      icon: "error",
    });
    return;
  }

  SwalMessage({
    tile: "Éxito",
    text: "Alumnos de la clase actualizados correctamente",
    icon: "success",
  });
  window.closeModal("modal-edit-students");
  //reload class details
  const urlParams = new URLSearchParams(window.location.search);
  const classIdParam = urlParams.get("id_class");
  if (classIdParam) {
    await loadSpecificClass(classIdParam);
  }
}
