/**
 * Modal Windows Module
 */

const ModalModule = (function() {
  let activeModal = null;

  function init() {
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("modal-overlay")) closeModal();
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeModal();
    });
  }

  function showModal(content, options = {}) {
    closeModal();

    const modal = document.createElement("div");
    modal.className = "modal-overlay";
    modal.innerHTML = `
      <div class="modal-content ${options.size || ""}">
        ${options.showClose !== false ? '<button class="modal-close">&times;</button>' : ""}
        <div class="modal-body">${content}</div>
      </div>
    `;

    document.body.appendChild(modal);
    activeModal = modal;

    const closeBtn = modal.querySelector(".modal-close");
    if (closeBtn) closeBtn.addEventListener("click", closeModal);

    modal.setAttribute("tabindex", "-1");
    modal.focus();
    setTimeout(() => modal.classList.add("active"), 10);
  }

  function closeModal() {
    if (activeModal) {
      activeModal.classList.remove("active");
      setTimeout(() => {
        if (activeModal && activeModal.parentNode) activeModal.remove();
        activeModal = null;
      }, 300);
    }
  }

  return { init, showModal, closeModal };
})();

window.ModalModule = ModalModule;