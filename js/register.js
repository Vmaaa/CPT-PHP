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

  function fetchCarreras() {
    carreraSelect.innerHTML = '<option value="">Cargando carreras...</option>';
    fetch("/api/carreras")
      .then((res) => res.json())
      .then((data) => {
        carreraSelect.innerHTML =
          '<option value="">Selecciona carrera</option>';
        data.forEach((carrera) => {
          const option = document.createElement("option");
          option.value = carrera.id;
          option.textContent = carrera.nombre;
          carreraSelect.appendChild(option);
        });
      })
      .catch(() => {
        carreraSelect.innerHTML =
          '<option value="">No se pudieron cargar las carreras</option>';
      });
  }

  emailInput.addEventListener("blur", updateUserType);
  updateUserType();
});
