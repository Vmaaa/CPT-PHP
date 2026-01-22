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
    const response = await CookieManager.fetchWithAuth(CLASS_API_URL, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });

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
  const maxDisplayProfessors = 2;
  const maxDisplayStudents = 3;

  const professorsHTML = cls.professors.length
    ? cls.professors.slice(0, maxDisplayProfessors).map((p) => `
        <li>
          <span class="name">${p.name}</span>
          <span class="name-subtitle">${p.academia}</span>
        </li>
      `).join("") + (cls.professors.length > maxDisplayProfessors
      ? `<li class="more-info">y ${
        cls.professors.length - maxDisplayProfessors
      } más...</li>`
      : "")
    : `<li class="no-data-list">Sin profesores asignados</li>`;

  const studentsHTML = cls.students.length
    ? cls.students.slice(0, maxDisplayStudents).map((s) => `
        <li>
          <span class="name">${s.name}</span>
          <span class="name-subtitle">${s.school_id_number}</span>
        </li>
      `).join("") + (cls.students.length > maxDisplayStudents
      ? `<li class="more-info">y ${
        cls.students.length - maxDisplayStudents
      } más...</li>`
      : "")
    : `<li class="no-data-list">Sin estudiantes inscritos</li>`;

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
      <h4>Estudiantes</h4>
      <ul class="list">
        ${studentsHTML}
      </ul>
    </div>

    <div class="class-card-footer">
      <button class="btn btn-info" onclick="viewClassDetails(${cls.id_class})">
        Ver más información y asignaciones
      </button>
    </div>
  `;

  return card;
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString("es-MX", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

function viewClassDetails(idClass) {
  window.location.href = `class_details.php?id_class=${idClass}`;
}
