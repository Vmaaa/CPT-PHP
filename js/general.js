window.toggleSubmenu = function (menuId) {
  const submenu = document.getElementById(menuId);
  const chevron = document.getElementById(menuId + "-chevron");
  if (!submenu) return;
  submenu.classList.toggle("expanded");
  if (chevron) {
    chevron.classList.toggle("fa-chevron-up");
    chevron.classList.toggle("fa-chevron-down");
  }
};

window.openModal = function (backdropId) {
  console.log("Opening modal:", backdropId);
  document.querySelectorAll(".modal-backdrop").forEach(
    (m) => (m.style.display = "none"),
  );
  const backdrop = document.getElementById(backdropId);
  if (!backdrop) {
    console.error("Modal backdrop not found:", backdropId);
    return;
  }

  backdrop.style.display = "flex";
  backdrop.setAttribute("aria-hidden", "false");
  document.body.style.overflow = "hidden";

  const firstFocusable = backdrop.querySelector(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
  );
  if (firstFocusable) {
    try {
      firstFocusable.focus({ preventScroll: true });
    } catch {}
  }
};

window.closeModal = function (backdropId) {
  const backdrop = document.getElementById(backdropId);
  if (!backdrop) return;

  backdrop.style.display = "none";
  backdrop.setAttribute("aria-hidden", "true");

  const anyOpen = Array.from(document.querySelectorAll(".modal-backdrop")).some(
    (bd) => bd.style.display !== "none",
  );
  if (!anyOpen) document.body.style.overflow = "";
};

function lockBackdrop(backdrop) {
  backdrop.setAttribute("role", "dialog");
  backdrop.setAttribute("aria-modal", "true");

  const swallowClick = (e) => {
    if (e.target === backdrop) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === "function") {
        e.stopImmediatePropagation();
      }
    }
  };

  backdrop.addEventListener("mousedown", swallowClick, true);
  backdrop.addEventListener("click", swallowClick, true);
  backdrop.addEventListener("touchstart", swallowClick, {
    capture: true,
    passive: false,
  });

  backdrop.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      e.preventDefault();
      e.stopPropagation();
    }
  });

  backdrop.querySelectorAll('.modal-close, [id^="cancel-"]').forEach((btn) => {
    btn.addEventListener("click", () => window.closeModal(backdrop.id));
  });
}

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
    lockBackdrop(backdrop);
  });
});

const logout_btn = document.getElementById("logout-btn");
if (logout_btn) {
  logout_btn.addEventListener("click", async function (e) {
    await CookieManager.logout();
  });
}
