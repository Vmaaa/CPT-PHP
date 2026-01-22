const CLASS_API_URL = API_URL + "/class/";
const CLASS_PROFESSOR_API_URL = API_URL + "/class/professor/";
const CAREER_API_URL = API_URL + "/career/";
const PROFESSOR_API_URL = API_URL + "/professor/";

let currentClasses = [];
let careers = [];
document.addEventListener("DOMContentLoaded", async () => {
  const btnNewClass = document.getElementById("btn-new-class");
  careers = await loadCareers();
  currentClasses = await loadClasses();

  btnNewClass.addEventListener("click", async () => {
    await loadCareers(document.getElementById("new-class-career"));
    await loadProfessors(
      document.getElementById("new-class-professors"),
    );
    openModal("modal-new-class");
  });
});

async function loadProfessors(selectElement, selectedIds = []) {
  try {
    const response = await CookieManager.fetchWithAuth(PROFESSOR_API_URL, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (!response.ok) {
      throw new Error("Error al cargar los profesores");
    }

    const result = await response.json();
    const professors = result.data || [];
    if (!selectElement) return professors;
    selectElement.innerHTML = "";
    professors.forEach((prof) => {
      const option = document.createElement("option");
      option.value = prof.id_professor;
      option.textContent = `${prof.name} (${prof.academia})`;
      if (selectedIds.includes(prof.id_professor)) {
        option.selected = true;
      }
      selectElement.appendChild(option);
    });
    return professors;
  } catch (error) {
    console.error(error);
    return [];
  }
}

async function loadCareers(selectElement, selectedId = null) {
  try {
    const response = await CookieManager.fetchWithAuth(CAREER_API_URL, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (!response.ok) {
      throw new Error("Error al cargar las carreras");
    }

    const result = await response.json();
    const careers = result.data || [];
    if (!selectElement) return careers;
    selectElement.innerHTML = "";
    careers.forEach((career) => {
      const option = document.createElement("option");
      option.value = career.id_career;
      option.textContent = career.career;
      if (career.id_career === selectedId) {
        option.selected = true;
      }
      selectElement.appendChild(option);
    });

    return careers;
  } catch (error) {
    console.error(error);
    return [];
  }
}

async function loadClasses() {
  try {
    const queryParams = new URLSearchParams({
      "from_admin_panel": "1",
    });
    const response = await CookieManager.fetchWithAuth(
      `${CLASS_API_URL}?${queryParams.toString()}`,
      {
        headers: {
          "Content-Type": "application/json",
        },
      },
    );

    if (!response.ok) {
      throw new Error("Error al cargar las clases");
    }

    const result = await response.json();
    renderClasses(result.data || []);
    return result.data || [];
  } catch (error) {
    console.error(error);
    document.getElementById("classes-container").innerHTML =
      `<p class="error-text">No se pudieron cargar las clases</p>`;
  }
}

async function renderClasses(classes) {
  const container = document.getElementById("classes-container");
  container.innerHTML = "";

  if (classes.length === 0) {
    container.innerHTML = `<p class="empty-text">No hay clases registradas</p>`;
    return;
  }

  await classes.forEach(async (cls) => {
    container.appendChild(await createClassCard(cls));
  });
}

function getCareerNameById(careers, idCareer) {
  const career = careers.find((c) => c.id_career === idCareer);
  return career ? career.career : "Desconocida";
}

async function createClassCard(cls) {
  const card = document.createElement("div");
  card.className = "class-card";

  const professorsHTML = cls.professors.length
    ? cls.professors.map((p) => `
        <li>
          <span class="name">${p.name}</span>
          <span class="name-subtitle">${p.academia}</span>
        </li>
      `).join("")
    : `<li class="no-data-list">Sin profesores asignados</li>`;
  card.innerHTML = `
    <div class="class-card-header">
      <h3>${cls.name}</h3>
      <span class="career">Carrera : ${
    getCareerNameById(careers, cls.id_career)
  }</span>
    </div>

    <div class="class-card-body">
      <p class="created">Creada: ${formatDate(cls.created_at)}</p>

      <h4>Profesores</h4>
      <ul class="list">
        ${professorsHTML}
      </ul>
    </div>

    <div class="class-card-footer">
      <button class="btn btn-edit" onclick="editClass(${cls.id_class})">
        Editar
      </button>
      <button class="btn btn-error" onclick="deleteClass(${cls.id_class})" disabled>
        Eliminar
      </button>
    </div>
  `;

  return card;
}
async function createClass() {
  const nameInput = document.getElementById("new-class-name");
  const idCareerInput = document.getElementById("new-class-career");
  const profSelect = document.getElementById("new-class-professors");

  const name = nameInput.value;
  const idCareer = idCareerInput.value;

  if (!name || !idCareer) {
    SwalMessage({
      tile: "Error",
      text: "Por favor, complete todos los campos obligatorios.",
      icon: "error",
    });
    return;
  }

  const form = new FormData();
  form.append("name", name);
  form.append("id_career", idCareer);

  const res = await CookieManager.fetchWithAuth(CLASS_API_URL, {
    method: "POST",
    body: form,
  });

  const result = await res.json();
  if (!res.ok) {
    SwalMessage({
      title: "Error",
      text: result.error || "No se pudo crear la clase.",
      icon: "error",
    });
    return;
  }

  const idClass = result.id_class;

  const profForm = new FormData();
  profForm.append("id_class", idClass);

  [...profSelect.selectedOptions].forEach((o) =>
    profForm.append("id_professor[]", o.value)
  );

  await CookieManager.fetchWithAuth(CLASS_PROFESSOR_API_URL, {
    method: "POST",
    body: profForm,
  });

  closeModal("modal-new-class");
  nameInput.value = "";
  idCareerInput.value = "";
  profSelect.innerHTML = "";
  SwalMessage({
    title: "Éxito",
    text: "Clase creada correctamente.",
    icon: "success",
  });
  currentClasses = await loadClasses();
}

async function editClass(idClass) {
  const cls = currentClasses.find((c) => c.id_class === idClass);
  if (!cls) return;

  document.getElementById("edit-class-id").value = cls.id_class;
  document.getElementById("edit-class-name").value = cls.name;

  await loadCareers(
    document.getElementById("edit-class-career"),
    cls.id_career,
  );

  await loadProfessors(
    document.getElementById("edit-class-professors"),
    cls.professors.map((p) => p.id_professor),
  );

  openModal("modal-edit-class");
}

async function updateClass() {
  const idClass = document.getElementById("edit-class-id").value;
  const name = document.getElementById("edit-class-name").value;
  const profSelect = document.getElementById("edit-class-professors");

  const form = new FormData();
  form.append("id_class", idClass);
  form.append("name", name);

  const res_c = await CookieManager.fetchWithAuth(CLASS_API_URL, {
    method: "PUT",
    body: form,
  });
  if (!res_c.ok) {
    const result_c = await res_c.json();
    SwalMessage({
      title: "Error",
      text: result_c.error || "No se pudo actualizar la clase.",
      icon: "error",
    });
    return;
  }

  const profForm = new FormData();
  profForm.append("id_class", idClass);

  [...profSelect.selectedOptions].forEach((o) =>
    profForm.append("id_professor[]", o.value)
  );

  const res_cp = await CookieManager.fetchWithAuth(CLASS_PROFESSOR_API_URL, {
    method: "PUT",
    body: profForm,
  });

  if (!res_cp.ok) {
    const result_cp = await res_cp.json();
    SwalMessage({
      title: "Error",
      text: result_cp.error ||
        "No se pudo actualizar los profesores de la clase.",
      icon: "error",
    });
    return;
  }

  closeModal("modal-edit-class");
  SwalMessage({
    title: "Éxito",
    text: "Clase actualizada correctamente.",
    icon: "success",
  });
  currentClasses = await loadClasses();
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString("es-MX", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}
