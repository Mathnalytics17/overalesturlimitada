(function () {
  function removeToast(toast) {
    if (!toast) return;
    toast.classList.add("admin-toast-hide");
    setTimeout(function () {
      toast.remove();
    }, 200);
  }

  function createToast(item) {
    const container = document.getElementById("admin-toast-container");
    if (!container) return;

    const toast = document.createElement("div");
    const type = item.type || "info";

    toast.className = "admin-toast admin-toast-" + type;

    const title = item.title ? `<div class="admin-toast-title">${escapeHtml(item.title)}</div>` : "";
    const message = item.message ? `<div class="admin-toast-message">${escapeHtml(item.message)}</div>` : "";

    toast.innerHTML = `
      <button type="button" class="admin-toast-close" aria-label="Cerrar">&times;</button>
      ${title}
      ${message}
    `;

    const closeBtn = toast.querySelector(".admin-toast-close");
    closeBtn.addEventListener("click", function () {
      removeToast(toast);
    });

    container.appendChild(toast);

    setTimeout(function () {
      removeToast(toast);
    }, 8000);
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  document.addEventListener("DOMContentLoaded", function () {
    const toasts = Array.isArray(window.ADMIN_FLASH_TOASTS) ? window.ADMIN_FLASH_TOASTS : [];
    toasts.forEach(createToast);
  });

  window.AdminToast = {
    show: createToast
  };
})();