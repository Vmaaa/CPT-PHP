const CALENDAR_API_URL = API_URL + "/calendar/";
const CAREER_API_URL = API_URL + "/career/";

document.addEventListener("DOMContentLoaded", async () => {
  await loadStageTypes();
  await loadCareers();
  await loadCalendarStages();

  document.getElementById("btn-new-stage").addEventListener("click", () => {
    openStageModal();
  });

  document.getElementById("stage-form").addEventListener("submit", saveStage);
});

/* ========================
   LOADERS
======================== */
async function fetchStages() {
  const response = await CookieManager.fetchWithAuth(
    CALENDAR_API_URL + "stage/",
  );
  const data = await response.json();
  if (!response.ok) {
    SwalMessage({
      title: "Error",
      text: data.error || "Hubo un error al obtener las etapas disponibles.",
      icon: "error",
    });
  }
  return data.data;
}

async function loadStageTypes() {
  const types = await fetchStages();
  const select = document.getElementById("stage_type");
  select.innerHTML = "";

  Object.entries(types).forEach(([key, label]) => {
    const opt = document.createElement("option");
    opt.value = key;
    opt.textContent = label;
    select.appendChild(opt);
  });
}

async function loadCareers() {
  const res = await CookieManager.fetchWithAuth(CAREER_API_URL);
  const res_json = await res.json();
  const data = res_json.data;

  const select = document.getElementById("id_career");
  select.innerHTML = "";

  data.forEach((c) => {
    const opt = document.createElement("option");
    opt.value = c.id_career;
    opt.textContent = c.career;
    select.appendChild(opt);
  });
}

async function loadCalendarStages() {
  const res = await CookieManager.fetchWithAuth(CALENDAR_API_URL);
  const res_json = await res.json();
  const data = res_json.data || [];

  const container = document.getElementById("stages-container");
  container.innerHTML = "";

  data.forEach((stage) => {
    container.appendChild(createStageCard(stage));
  });
}

/* ========================
   UI
======================== */

function createStageCard(stage) {
  const div = document.createElement("div");
  div.className = "stage-card";

  div.innerHTML = `
    <div class="stage-title">${stage.humanized_stage}</div>
    <div class="stage-info"><b>Carrera:</b> ${stage.career}</div>
    <div class="stage-info"><b>Inicio:</b> ${stage.start_date}</div>
    <div class="stage-info"><b>Fin:</b> ${stage.end_date}</div>
    <div class="stage-info"><b>Año:</b> ${stage.year}</div>
    <div class="stage-info"><b>Semestre:</b> ${
    stage.first_half ? "Semestre I" : "Semestre II"
  }</div>
    <div class="stage-actions">
      <button class="btn btn-error" onclick='deleteStage(${stage.id_calendar_events})'>
        Eliminar
      </button>
      <button class="btn btn-primary" onclick='editStage(${
    JSON.stringify(stage)
  })'>
        Editar
      </button>
    </div>
  `;
  return div;
}

/* ========================
   MODAL
======================== */
function openStageModal() {
  document.getElementById("stage-form").reset();
  document.getElementById("id_calendar_events").value = "";
  document.getElementById("stage-modal-title").textContent = "Nueva etapa";
  document.getElementById("stage-modal-backdrop").style.display = "flex";
}

function editStage(stage) {
  document.getElementById("stage-modal-title").textContent = "Editar etapa";

  document.getElementById("id_calendar_events").value = stage.id_calendar_events;
  document.getElementById("stage_type").value = stage.stage;
  document.getElementById("stage_type").disabled = true;
  document.getElementById("id_career").value = stage.id_career;
  document.getElementById("id_career").disabled = true;
  document.getElementById("start_date").value = stage.start_date.split(" ")[0];
  document.getElementById("end_date").value = stage.end_date.split(" ")[0];
  document.getElementById("first_half").value = stage.first_half;
  document.getElementById("year").value = stage.year;
  document.getElementById("stage-modal-backdrop").style.display = "flex";
  console.log(stage);
}

function closeStageModal() {
  document.getElementById("stage-form").reset();
  document.getElementById("stage-modal-backdrop").style.display = "none";
}

/* ========================
   SAVE
======================== */

async function saveStage(e) {
  e.preventDefault();

  const payload = {
    id_calendar_events: document.getElementById("id_calendar_events").value || null,
    stage: document.getElementById("stage_type").value,
    id_career: document.getElementById("id_career").value,
    start_date: formatDateForDatabase(document.getElementById("start_date").value),
    end_date: formatDateForDatabase(document.getElementById("end_date").value),
    year: document.getElementById("year").value,
    first_half: document.getElementById("first_half").value
  };

  const formData = new FormData();
  Object.entries(payload).forEach(([key, value]) => {
    if (value !== null) {
      formData.append(key, value);
    }
  });


  const method = payload.id_calendar_events ? "PUT" : "POST";

  const res = await CookieManager.fetchWithAuth(CALENDAR_API_URL, {
    method,
    body: formData,
  });

  const data = await res.json();

  if (!res.ok) {
    SwalMessage({
      title: "Error",
      text: data.error || "No se pudo guardar la etapa",
      icon: "error",
    });
    return;
  }

  if (payload.id_calendar_events) {
    SwalMessage({
      title: "Éxito",
      text: "Etapa actualizada correctamente",
      icon: "success",
    });
  }
    else {
    SwalMessage({
      title: "Éxito",
      text: "Etapa creada correctamente",
      icon: "success",
    });
  }

  closeStageModal();
  await loadCalendarStages();
}

async function deleteStage(id_calendar_events) {
  const confirm = await SwalConfirm({
    title: "¿Eliminar etapa?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });
  
  if (!confirm) return;

  const formData = new FormData();
  formData.append("id_calendar_events", id_calendar_events);

  const res = await CookieManager.fetchWithAuth(CALENDAR_API_URL, {
    method: "DELETE",
    body: formData,
  });
  
  const data = await res.json();

  if (!res.ok) {
    SwalMessage({
      title: "Error",
      text: data.error || "No se pudo eliminar la etapa",
      icon: "error",
    });
    return;
  }

  SwalMessage({
    title: "Éxito",
    text: "Etapa eliminada correctamente",
    icon: "success",
  });
  
  await loadCalendarStages();
}

