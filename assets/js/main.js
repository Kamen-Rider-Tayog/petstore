/**
 * Main JavaScript File
 * Global functions and utilities for the Ria Pet Store website
 */

class PetStoreApp {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.initTooltips();
    this.initBackToTop();
    this.updateCartCount();
    this.initModals();
  }

  bindEvents() {
    // Global event listeners
    document.addEventListener("DOMContentLoaded", () => {
      this.onPageLoad();
    });

    // Handle AJAX errors globally
    window.addEventListener("unhandledrejection", (event) => {
      console.error("Unhandled promise rejection:", event.reason);
      this.showNotification("An error occurred. Please try again.", "error");
    });
  }

  onPageLoad() {
    // Initialize page-specific features
    this.initFormValidation();
    this.initImageLazyLoading();
    this.initSmoothScrolling();
  }

  // ===== TOOLTIPS =====
  initTooltips() {
    const tooltips = document.querySelectorAll("[data-tooltip]");
    tooltips.forEach((element) => {
      element.addEventListener("mouseenter", (e) =>
        this.showTooltip(e, element),
      );
      element.addEventListener("mouseleave", () => this.hideTooltip());
    });
  }

  showTooltip(event, element) {
    const text = element.getAttribute("data-tooltip");
    if (!text) return;

    const tooltip = document.createElement("div");
    tooltip.className = "tooltip";
    tooltip.textContent = text;
    document.body.appendChild(tooltip);

    const rect = element.getBoundingClientRect();
    tooltip.style.left =
      rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px";

    tooltip.classList.add("visible");
  }

  hideTooltip() {
    const tooltip = document.querySelector(".tooltip");
    if (tooltip) {
      tooltip.remove();
    }
  }

  // ===== MODAL WINDOWS =====
  initModals() {
    // Close modal when clicking outside
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("modal-overlay")) {
        this.closeModal();
      }
    });

    // Close modal with escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        this.closeModal();
      }
    });
  }

  showModal(content, options = {}) {
    // Remove existing modal
    this.closeModal();

    const modal = document.createElement("div");
    modal.className = "modal-overlay";
    modal.innerHTML = `
            <div class="modal-content ${options.size || ""}">
                ${options.showClose !== false ? '<button class="modal-close">&times;</button>' : ""}
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        `;

    document.body.appendChild(modal);

    // Add close event listeners
    const closeBtn = modal.querySelector(".modal-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", () => this.closeModal());
    }

    // Focus management
    modal.setAttribute("tabindex", "-1");
    modal.focus();

    // Animate in
    setTimeout(() => modal.classList.add("active"), 10);
  }

  closeModal() {
    const modal = document.querySelector(".modal-overlay");
    if (modal) {
      modal.classList.remove("active");
      setTimeout(() => modal.remove(), 300);
    }
  }

  // ===== BACK TO TOP BUTTON =====
  initBackToTop() {
    const button = document.createElement("button");
    button.className = "back-to-top";
    button.setAttribute("aria-label", "Back to top");
    
    // SVG icon directly in JS
    button.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 12L12 8M12 8L8 12M12 8V16M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`;
    
    document.body.appendChild(button);

    window.addEventListener("scroll", () => {
      if (window.pageYOffset > 300) {
        button.classList.add("visible");
      } else {
        button.classList.remove("visible");
      }
    });

    button.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }

  // ===== CART FUNCTIONALITY =====
  async updateCartCount() {
    try {
      const response = await fetch("../backend/api/cart_count.php");
      const data = await response.json();

      const cartCounts = document.querySelectorAll(".cart-count");
      cartCounts.forEach((count) => {
        count.textContent = data.count || 0;
      });
    } catch (error) {
      console.error("Error updating cart count:", error);
    }
  }

  // ===== CURRENCY FORMATTING =====
  formatCurrency(amount, currency = "PHP") {
    const formatter = new Intl.NumberFormat("en-PH", {
      style: "currency",
      currency: currency,
    });
    return formatter.format(amount);
  }

  // ===== NOTIFICATIONS =====
  showNotification(message, type = "info", duration = 5000) {
    // Remove existing notifications
    const existing = document.querySelectorAll(".notification");
    existing.forEach((notif) => notif.remove());

    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => notification.classList.add("active"), 10);

    // Auto remove
    if (duration > 0) {
      setTimeout(() => this.removeNotification(notification), duration);
    }

    // Close button
    const closeBtn = notification.querySelector(".notification-close");
    closeBtn.addEventListener("click", () =>
      this.removeNotification(notification),
    );
  }

  removeNotification(notification) {
    notification.classList.remove("active");
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }

  // ===== FORM VALIDATION =====
  initFormValidation() {
    const forms = document.querySelectorAll("form[data-validate]");
    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        if (!this.validateForm(form)) {
          e.preventDefault();
        }
      });
    });
  }

  validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll("input, textarea, select");

    inputs.forEach((input) => {
      if (!this.validateField(input)) {
        isValid = false;
      }
    });

    return isValid;
  }

  validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = "";

    // Required validation
    if (field.hasAttribute("required") && !value) {
      isValid = false;
      message = "This field is required";
    }

    // Email validation
    if (field.type === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        message = "Please enter a valid email address";
      }
    }

    // Show/hide error
    this.setFieldError(field, message);

    return isValid;
  }

  setFieldError(field, message) {
    // Remove existing error
    const existing = field.parentNode.querySelector(".field-error");
    if (existing) existing.remove();

    if (message) {
      const error = document.createElement("div");
      error.className = "field-error";
      error.textContent = message;
      field.parentNode.appendChild(error);
      field.classList.add("error");
    } else {
      field.classList.remove("error");
    }
  }

  // ===== IMAGE LAZY LOADING =====
  initImageLazyLoading() {
    const images = document.querySelectorAll("img[data-src]");
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.remove("lazy");
          observer.unobserve(img);
        }
      });
    });

    images.forEach((img) => imageObserver.observe(img));
  }

  // ===== SMOOTH SCROLLING =====
  initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach((link) => {
      link.addEventListener("click", (e) => {
        const target = document.querySelector(link.getAttribute("href"));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      });
    });
  }

  // ===== UTILITY FUNCTIONS =====
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  throttle(func, limit) {
    let inThrottle;
    return function () {
      const args = arguments;
      const context = this;
      if (!inThrottle) {
        func.apply(context, args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  }

  // ===== AJAX HELPER =====
  async ajax(url, options = {}) {
    const defaultOptions = {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    };

    const config = { ...defaultOptions, ...options };

    try {
      this.showLoading();
      const response = await fetch(url, config);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("AJAX Error:", error);
      throw error;
    } finally {
      this.hideLoading();
    }
  }

  showLoading() {
    const spinner = document.getElementById("loadingSpinner");
    if (spinner) {
      spinner.style.display = "flex";
    }
  }

  hideLoading() {
    const spinner = document.getElementById("loadingSpinner");
    if (spinner) {
      spinner.style.display = "none";
    }
  }
}

// Initialize the app
const petStoreApp = new PetStoreApp();

// Make functions globally available
window.showModal = (content, options) =>
  petStoreApp.showModal(content, options);
window.closeModal = () => petStoreApp.closeModal();
window.showNotification = (message, type, duration) =>
  petStoreApp.showNotification(message, type, duration);
window.formatCurrency = (amount, currency) =>
  petStoreApp.formatCurrency(amount, currency);
window.updateCartCount = () => petStoreApp.updateCartCount();
