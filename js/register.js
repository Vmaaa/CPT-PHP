CAREER_API = API_URL + "/career/";
REGISTER_API = API_URL + "/account/register/";

document.addEventListener("DOMContentLoaded", function () {
  const emailInput = document.getElementById("email");
  const namesInput = document.getElementById("nombres");
  const lastNamesInput = document.getElementById("apellidos");
  const userCURP = document.getElementById("curp");
  const userTypeText = document.getElementById("user-type-text");

  // student fields inputs
  const studentFields = document.getElementById("student-fields");
  const studentIdInput = document.getElementById("boleta");
  const careerInput = document.getElementById("carrera");
  const carreraSelect = document.getElementById("carrera");

  // teacher fields inputs
  const teacherFields = document.getElementById("teacher-fields");
  const academiaInput = document.getElementById("academia");
  const levelOfEducationInput = document.getElementById("nivel");

  const registerForm = document.getElementById("register-form");
  const registerButton = document.getElementById("register-btn");

  registerForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    registerButton.disabled = true;
    registerButton.textContent = "Registrando...";

    const formData = new FormData();
    //append common fields
    formData.append("acco_email", emailInput.value.trim());
    formData.append(
      "acco_name",
      namesInput.value.trim() + " " + lastNamesInput.value.trim(),
    );
    formData.append("curp", userCURP.value.trim());
    isStudent = emailInput.value.trim().endsWith("@alumno.ipn.mx");
    isAdmin = emailInput.value.trim() === "admin@cpt.ipn.mx";

    //append specific fields
    if (isStudent) {
      //student
      formData.append("school_id_number", studentIdInput.value.trim()); //student
      formData.append("id_career", careerInput.value);
    } else {
      //admin if is admin@cpt_ipn.mx else teacher
      if (isAdmin) {
        const system_secret = await SwalInput(
          {
            title: "Verificación de administrador",
            inputLabel: "Ingresa la clave secreta del sistema:",
            inputType: "password",
            confirmButtonText: "Verificar",
            cancelButtonText: "Cancelar",
          },
        );
        if (system_secret === null) {
          SwalMessage(
            {
              title: "Registro cancelado",
              text: "No se proporcionó la clave secreta del sistema.",
              icon: "error",
            },
          );
          registerButton.disabled = false;
          registerButton.textContent = "Registrarse";
          return;
        }
        formData.append("system_secret", system_secret);
      }
      //teacher
      formData.append("academia", academiaInput.value.trim());
      formData.append("level_of_education", levelOfEducationInput.value.trim());
    }
    console.log(
      "Submitting form data:",
      Object.fromEntries(formData.entries()),
    );

    const response = await CookieManager.fetchWithAuth(
      REGISTER_API,
      {
        method: "POST",
        body: formData,
      },
    );
    if (response.ok) {
      if (!isAdmin) {
        SwalMessage(
          {
            text: "Registro exitoso. Serás redirigido a la página de inicio.",
            icon: "success",
            title: "Registro exitoso",
          },
        );
        // redirect to home after a short delay
        setTimeout(() => {
          window.location.href = BASE_URL + "/index.php";
        }, 2000);
      } else {
        const jsonResponse = await response.json();
        const plain_password = jsonResponse.plain_password;
        //show password in a confirm dialog for wait beofre redirect
        const confirmed = await SwalConfirm({
          title: "Registro de administrador exitoso",
          text: "La contraseña generada para el administrador es: " +
            plain_password,
          icon: "success",
          confirmButtonText: "Copiar al portapapeles y salir",
          showCancelButton: false,
        });
        if (confirmed) {
          //copy to clipboard
          await navigator.clipboard.writeText(plain_password);
          window.location.href = BASE_URL + "/index.php";
        }
      }
    } else {
      const errorData = await response.json();
      SwalMessage(
        {
          title: "Error de registro",
          text: errorData.error || "Ocurrió un error durante el registro.",
          icon: "error",
        },
      );
      registerButton.disabled = false;
      registerButton.textContent = "Registrarse";
    }
  });

  function updateUserType() {
    const email = emailInput.value.trim();
    if (email.endsWith("@alumno.ipn.mx")) {
      userTypeText.textContent = "Estás registrando una cuenta de ESTUDIANTE.";
      studentFields.style.display = "";
      //students fieals as required
      studentIdInput.required = true;
      careerInput.required = true;

      teacherFields.style.display = "none";
      //teacher fields not required
      academiaInput.required = false;
      levelOfEducationInput.required = false;

      fetchCarreras();
    } else if (email.length > 0) {
      userTypeText.textContent = "Estás registrando una cuenta de PROFESOR.";
      studentFields.style.display = "none";
      //students fieals not required
      studentIdInput.required = false;
      careerInput.required = false;

      teacherFields.style.display = "";
      //teacher fields as required
      academiaInput.required = true;
      levelOfEducationInput.required = true;
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
