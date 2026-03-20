/**
 * Notifications Module
 */

const NotificationModule = (function() {
  let timeoutId = null;

  function show(message, type = "info", duration = 5000) {
    // Remove existing notifications
    document.querySelectorAll(".notification").forEach((notif) => notif.remove());

    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <span class="notification-message">${message}</span>
        <button class="notification-close">&times;</button>
      </div>
    `;

    document.body.appendChild(notification);
    setTimeout(() => notification.classList.add("active"), 10);

    if (duration > 0) {
      if (timeoutId) clearTimeout(timeoutId);
      timeoutId = setTimeout(() => remove(notification), duration);
    }

    const closeBtn = notification.querySelector(".notification-close");
    closeBtn.addEventListener("click", () => remove(notification));
  }

  function remove(notification) {
    notification.classList.remove("active");
    setTimeout(() => {
      if (notification.parentNode) notification.remove();
    }, 300);
  }

  return { show };
})();

window.NotificationModule = NotificationModule;