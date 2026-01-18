CAREER_API = API_URL + "/career/";

document.addEventListener("DOMContentLoaded", function () {
  const emailInput = document.getElementById("email");
  const userTypeText = document.getElementById("user-type-text");
  const studentFields = document.getElementById("student-fields");
  const teacherFields = document.getElementById("teacher-fields");
  const carreraSelect = document.getElementById("carrera");

  function updateUserType() {
    const email = emailInput.value.trim();
    if (email.endsWith("@alumno.ipn.mx")) {
      userTypeText.textContent = "Estás registrando una cuenta de ESTUDIANTE.";
      studentFields.style.display = "";
      teacherFields.style.display = "none";
      fetchCarreras();
    } else if (email.length > 0) {
      userTypeText.textContent = "Estás registrando una cuenta de PROFESOR.";
      studentFields.style.display = "none";
      teacherFields.style.display = "";
    } else {
      userTypeText.textContent = "";
      studentFields.style.display = "none";
      teacherFields.style.display = "none";
    }
  }

  async function fetchCarreras() {
    carreraSelect.innerHTML = '<option value="">Cargando carreras...</option>';
    const response = await CookieManager.fetchWithAuth(CAREER_API);
    if (response.ok) {
      const response_json = await response.json();
      const carreras = response_json.data;

      carreraSelect.innerHTML =
        '<option value="">Selecciona una carrera</option>';
      carreras.forEach((carrera) => {
        const option = document.createElement("option");
        option.value = carrera.id_career;
        option.textContent = carrera.career;
        carreraSelect.appendChild(option);
      });
    } else {
      carreraSelect.innerHTML =
        '<option value="">Error al cargar carreras</option>';
    }
  }

  emailInput.addEventListener("blur", updateUserType);
  updateUserType();
});
