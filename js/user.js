const ACCOUNT_API_URL = API_URL + "/account/";
const PROFESSOR_API_URL = API_URL + "/professor/";
const modifiedUsers = new Set();

document.addEventListener("DOMContentLoaded", async () => {
  const users = await fetchAccountsData();
  renderUsers(users);
});

async function fetchAccountsData() {
  const queryParams = new URLSearchParams({
    acco_role_exclude: "student",
  });

  try {
    const response = await CookieManager.fetchWithAuth(
      `${ACCOUNT_API_URL}?${queryParams.toString()}`,
    );
    if (!response.ok) return [];
    const json = await response.json();
    return json.data || [];
  } catch {
    return [];
  }
}

function renderUsers(users) {
  const tbody = document.getElementById("users-table-body");
  tbody.innerHTML = "";

  users.forEach((user) => {
    const tr = document.createElement("tr");
    tr.dataset.id = user.acco_id;

    tr.innerHTML = `
      <td class="text-bold">${user.name}</td>
      <td>${user.acco_email}</td>
      <td>${user.academia || "-"}</td>
      <td>
        <select class="data-select role-select" data-id="${user.acco_id}">
          <option value="admin" ${
      user.acco_role === "admin" ? "selected" : ""
    }>Admin</option>
          <option value="professor" ${
      user.acco_role === "professor" ? "selected" : ""
    }>Profesor</option>
      </td>
      <td class="text-center">
        <input type="checkbox" class="data-checkbox status-checkbox" data-id="${user.acco_id}" ${
      user.is_advisor === 1 ? "checked" : ""
    }>
      </td>
      <td class="text-center">
        <input type="checkbox" class="data-checkbox advisor-checkbox" data-id="${user.acco_id}" ${
      user.is_president === 1 ? "checked" : ""
    }>
      </td>
      <td class="text-center">
        ${
      user.acco_status === 1
        ? `
        <button class="btn btn-success" data-id="${user.acco_id}" disabled><li class="fa fa-save"></li></button>
        `
        : `
        <button class="btn btn-success" disabled><li class="fa fa-save"></li></button>
        `
    }
        ${
      user.acco_status == 1
        ? `
        <button class="btn btn-error" data-id="${user.acco_id}"><li class="fa fa-trash"></li></button>
        `
        : `<button class="btn btn-success"><li class="fa fa-undo"></li></button>`
    }
      </td>
    `;

    tbody.appendChild(tr);
  });
}

document.addEventListener("change", (e) => {
  const id = e.target.dataset.id;
  if (!id) return;

  if (
    e.target.classList.contains("role-select") ||
    e.target.classList.contains("status-checkbox") ||
    e.target.classList.contains("advisor-checkbox")
  ) {
    modifiedUsers.add(id);
    toggleSaveButton(id, true);
  }
});

document.addEventListener("click", async (e) => {
  const id = e.target.dataset.id;
  if (!id) return;

  if (e.target.classList.contains("btn-success")) {
    const row = document.querySelector(`tr[data-id="${id}"]`);

    const payload = new FormData();
    payload.append("acco_id", id);
    payload.append("acco_role", row.querySelector(".role-select").value);
    payload.append(
      "is_advisor",
      row.querySelector(".advisor-checkbox").checked ? 1 : 0,
    );
    payload.append(
      "is_president",
      row.querySelector(".status-checkbox").checked ? 1 : 0,
    );
    payload.append("from_admin_panel", 1);

    const success = await updateUser(payload);
    if (success) {
      modifiedUsers.delete(id);
      toggleSaveButton(id, false);
    }
  }

  if (e.target.classList.contains("btn-error")) {
    const confirmed = await SwalConfirm(
      {
        title: "¿Deshabilitar usuario?",
        text:
          "Esta acción deshabilitará al usuario y no podrá acceder al sistema.",
        icon: "warning",
        confirmButtonText: "Sí, deshabilitar",
        cancelButtonText: "Cancelar",
      },
    );
    if (confirmed) {
      const payload = new FormData();
      payload.append("acco_id", id);
      const success = await deleteUser(payload);
      if (success) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        row.querySelector(".btn-error").outerHTML =
          `<button class="btn btn-success"><li class="fa fa-undo"></li></button>`;
      }
    }
  }
});

function toggleSaveButton(id, show) {
  const btn = document.querySelector(`.btn-success[data-id="${id}"]`);
  if (!btn) return;
  btn.disabled = !show;
}

async function updateUser(payload) {
  try {
    const responseProfessor = await CookieManager.fetchWithAuth(
      `${PROFESSOR_API_URL}`,
      {
        method: "PUT",
        body: payload,
      },
    );
    const responseAccount = await CookieManager.fetchWithAuth(
      `${ACCOUNT_API_URL}`,
      {
        method: "PUT",
        body: payload,
      },
    );

    if (!responseProfessor.ok || !responseAccount.ok) {
      const errorProfessor = await responseProfessor.json();
      const errorAccount = await responseAccount.json();
      SwalMessage({
        title: "Error",
        text: (errorProfessor.error || errorAccount.error ||
          "No se pudo actualizar el usuario."),
        icon: "error",
      });
      return false;
    }
    SwalMessage({
      title: "Éxito",
      text: "Usuario actualizado correctamente.",
      icon: "success",
    });
    return true;
  } catch {
    SwalMessage({
      title: "Error",
      text: "No se pudo actualizar el usuario.",
      icon: "error",
    });
    return false;
  }
}

async function deleteUser(payload) {
  try {
    const response = await CookieManager.fetchWithAuth(
      `${ACCOUNT_API_URL}`,
      { method: "DELETE", body: payload },
    );
    if (!response.ok) {
      const error = await response.json();
      SwalMessage({
        title: "Error",
        text: (error.error || "No se pudo deshabilitar el usuario."),
        icon: "error",
      });
      return false;
    }
    SwalMessage({
      title: "Éxito",
      text: "Usuario deshabilitado correctamente.",
      icon: "success",
    });
    return true;
  } catch {
    SwalMessage({
      title: "Error",
      text: "No se pudo deshabilitar el usuario.",
      icon: "error",
    });
    return false;
  }
}
