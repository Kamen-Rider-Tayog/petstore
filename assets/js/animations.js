/**
 * Animation JavaScript
 * Handles loading states, scroll animations, and smooth transitions
 */

class LoadingManager {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.showPageLoading();
  }

  bindEvents() {
    // Handle form submissions
    const forms = document.querySelectorAll("form");
    forms.forEach((form) => {
      form.addEventListener("submit", (e) => this.handleFormSubmit(e, form));
    });

    // Handle AJAX requests
    this.interceptAjaxRequests();

    // Handle page navigation
    const links = document.querySelectorAll("a[href]");
    links.forEach((link) => {
      link.addEventListener("click", (e) => this.handleLinkClick(e, link));
    });
  }

  showPageLoading() {
    // Add loading class to body
    document.body.classList.add("loading");

    // Remove loading after page loads
    window.addEventListener("load", () => {
      setTimeout(() => {
        document.body.classList.remove("loading");
        document.body.classList.add("loaded");
      }, 300);
    });
  }

  handleFormSubmit(e, form) {
    // Don't prevent default - let the form submit
    // But show loading state
    this.showFormLoading(form);
  }

  showFormLoading(form) {
    const submitBtn = form.querySelector(
      'button[type="submit"], input[type="submit"]',
    );
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add("loading");

      const originalText = submitBtn.textContent || submitBtn.value;
      submitBtn.setAttribute("data-original-text", originalText);

      if (submitBtn.tagName === "BUTTON") {
        submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
      } else {
        submitBtn.value = "Processing...";
      }
    }
  }

  hideFormLoading(form) {
    const submitBtn = form.querySelector(
      'button[type="submit"], input[type="submit"]',
    );
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.classList.remove("loading");

      const originalText = submitBtn.getAttribute("data-original-text");
      if (originalText) {
        if (submitBtn.tagName === "BUTTON") {
          submitBtn.textContent = originalText;
        } else {
          submitBtn.value = originalText;
        }
        submitBtn.removeAttribute("data-original-text");
      }
    }
  }

  handleLinkClick(e, link) {
    const href = link.getAttribute("href");

    // Only handle internal links
    if (
      href &&
      !href.startsWith("http") &&
      !href.startsWith("mailto:") &&
      !href.startsWith("tel:")
    ) {
      // Don't handle anchor links or external links
      if (!href.startsWith("#") && !link.hasAttribute("target")) {
        e.preventDefault();
        this.showPageTransition(link);
      }
    }
  }

  showPageTransition(link) {
    document.body.classList.add("page-transitioning");

    setTimeout(() => {
      window.location.href = link.href;
    }, 300);
  }

  interceptAjaxRequests() {
    // Store original XMLHttpRequest
    const originalXHR = window.XMLHttpRequest;

    window.XMLHttpRequest = function () {
      const xhr = new originalXHR();
      const originalSend = xhr.send;

      xhr.send = function () {
        // Show loading for AJAX requests
        document.body.classList.add("ajax-loading");

        xhr.addEventListener("loadend", () => {
          document.body.classList.remove("ajax-loading");
        });

        return originalSend.apply(this, arguments);
      };

      return xhr;
    };
  }
}

class ScrollAnimations {
  constructor() {
    this.animatedElements = [];
    this.init();
  }

  init() {
    this.bindEvents();
    this.setupIntersectionObserver();
    this.handleInitialLoad();
  }

  bindEvents() {
    window.addEventListener("scroll", () => this.handleScroll());
    window.addEventListener("resize", () => this.handleResize());
  }

  setupIntersectionObserver() {
    const options = {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    };

    this.observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          this.animateElement(entry.target);
        }
      });
    }, options);

    // Observe elements with animation classes
    const animatedElements = document.querySelectorAll(
      ".animate-on-scroll, .fade-in, .slide-up, .slide-left, .slide-right, .scale-in",
    );
    animatedElements.forEach((element) => {
      this.observer.observe(element);
    });
  }

  animateElement(element) {
    if (element.classList.contains("animated")) return;

    element.classList.add("animated");

    // Add specific animation classes based on data attributes or existing classes
    if (element.classList.contains("fade-in")) {
      element.style.animation = "fadeIn 0.6s ease-out forwards";
    } else if (element.classList.contains("slide-up")) {
      element.style.animation = "slideUp 0.6s ease-out forwards";
    } else if (element.classList.contains("slide-left")) {
      element.style.animation = "slideLeft 0.6s ease-out forwards";
    } else if (element.classList.contains("slide-right")) {
      element.style.animation = "slideRight 0.6s ease-out forwards";
    } else if (element.classList.contains("scale-in")) {
      element.style.animation = "scaleIn 0.6s ease-out forwards";
    }

    // Handle animation delay
    const delay = element.dataset.animationDelay || 0;
    if (delay > 0) {
      element.style.animationDelay = `${delay}ms`;
    }
  }

  handleScroll() {
    this.updateScrollProgress();
    this.handleParallaxElements();
  }

  updateScrollProgress() {
    const scrollTop = window.pageYOffset;
    const docHeight =
      document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = (scrollTop / docHeight) * 100;

    // Update progress bar if exists
    const progressBar = document.querySelector(".scroll-progress");
    if (progressBar) {
      progressBar.style.width = `${scrollPercent}%`;
    }
  }

  handleParallaxElements() {
    const parallaxElements = document.querySelectorAll(".parallax");
    const scrollTop = window.pageYOffset;

    parallaxElements.forEach((element) => {
      const speed = element.dataset.parallaxSpeed || 0.5;
      const yPos = -(scrollTop * speed);
      element.style.transform = `translateY(${yPos}px)`;
    });
  }

  handleResize() {
    // Recalculate positions on resize
    this.handleScroll();
  }

  handleInitialLoad() {
    // Animate elements that are already in viewport on load
    setTimeout(() => {
      const elements = document.querySelectorAll(
        ".animate-on-scroll, .fade-in, .slide-up, .slide-left, .slide-right, .scale-in",
      );
      elements.forEach((element) => {
        const rect = element.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
          this.animateElement(element);
        }
      });
    }, 100);
  }
}

class HoverEffects {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    // Add hover effects to interactive elements
    const interactiveElements = document.querySelectorAll(
      ".card, .btn, .nav-link, .product-item, .pet-item",
    );
    interactiveElements.forEach((element) => {
      element.addEventListener("mouseenter", () =>
        this.handleMouseEnter(element),
      );
      element.addEventListener("mouseleave", () =>
        this.handleMouseLeave(element),
      );
    });
  }

  handleMouseEnter(element) {
    element.classList.add("hover-active");

    // Add ripple effect for buttons
    if (element.classList.contains("btn")) {
      this.createRippleEffect(element);
    }
  }

  handleMouseLeave(element) {
    element.classList.remove("hover-active");
  }

  createRippleEffect(button) {
    const ripple = document.createElement("span");
    ripple.className = "ripple-effect";

    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = `${size}px`;
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;

    button.appendChild(ripple);

    setTimeout(() => {
      ripple.remove();
    }, 600);
  }
}

class NotificationManager {
  constructor() {
    this.init();
  }

  init() {
    this.container = this.createContainer();
  }

  createContainer() {
    let container = document.querySelector(".notification-container");
    if (!container) {
      container = document.createElement("div");
      container.className = "notification-container";
      document.body.appendChild(container);
    }
    return container;
  }

  show(message, type = "info", duration = 5000) {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" aria-label="Close notification">&times;</button>
            </div>
        `;

    this.container.appendChild(notification);

    // Animate in
    setTimeout(() => notification.classList.add("show"), 10);

    // Auto-hide
    if (duration > 0) {
      setTimeout(() => this.hide(notification), duration);
    }

    // Handle close button
    const closeBtn = notification.querySelector(".notification-close");
    closeBtn.addEventListener("click", () => this.hide(notification));

    return notification;
  }

  hide(notification) {
    notification.classList.remove("show");
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }

  success(message, duration) {
    return this.show(message, "success", duration);
  }

  error(message, duration) {
    return this.show(message, "error", duration);
  }

  warning(message, duration) {
    return this.show(message, "warning", duration);
  }

  info(message, duration) {
    return this.show(message, "info", duration);
  }
}

// Initialize all animation components when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  new LoadingManager();
  new ScrollAnimations();
  new SmoothScroll();
  new HoverEffects();

  // Initialize notification manager globally
  window.notifications = new NotificationManager();
});

// Export for potential use in other scripts
window.LoadingManager = LoadingManager;
window.ScrollAnimations = ScrollAnimations;
window.SmoothScroll = SmoothScroll;
window.HoverEffects = HoverEffects;
window.NotificationManager = NotificationManager;
