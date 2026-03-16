/**
 * Mobile Navigation JavaScript
 * Handles mobile menu toggle, dropdown menus, and responsive navigation
 */

class MobileNavigation {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.handleWindowResize();
  }

  bindEvents() {
    // Mobile menu toggle
    const mobileToggle = document.querySelector(".mobile-nav-toggle");
    if (mobileToggle) {
      mobileToggle.addEventListener("click", () => this.toggleMobileMenu());
    }

    // Close mobile menu when clicking outside
    document.addEventListener("click", (e) => {
      const mobileNav = document.querySelector(".mobile-nav");
      const mobileToggle = document.querySelector(".mobile-nav-toggle");

      if (mobileNav && mobileNav.classList.contains("active")) {
        if (!mobileNav.contains(e.target) && !mobileToggle.contains(e.target)) {
          this.closeMobileMenu();
        }
      }
    });

    // Handle mobile dropdown menus
    const dropdownToggles = document.querySelectorAll(
      ".mobile-nav .dropdown-toggle",
    );
    dropdownToggles.forEach((toggle) => {
      toggle.addEventListener("click", (e) => {
        e.preventDefault();
        this.toggleDropdown(toggle);
      });
    });

    // Handle window resize
    window.addEventListener("resize", () => this.handleWindowResize());

    // Handle escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        this.closeMobileMenu();
      }
    });
  }

  toggleMobileMenu() {
    const mobileNav = document.querySelector(".mobile-nav");
    const body = document.body;

    if (mobileNav) {
      mobileNav.classList.toggle("active");
      body.classList.toggle("mobile-menu-open");

      // Update toggle button
      const toggle = document.querySelector(".mobile-nav-toggle");
      if (toggle) {
        const isActive = mobileNav.classList.contains("active");
        toggle.setAttribute("aria-expanded", isActive);
        toggle.innerHTML = isActive
          ? '<i class="icon-close"></i>'
          : '<i class="icon-menu"></i>';
      }
    }
  }

  closeMobileMenu() {
    const mobileNav = document.querySelector(".mobile-nav");
    const body = document.body;

    if (mobileNav) {
      mobileNav.classList.remove("active");
      body.classList.remove("mobile-menu-open");

      // Update toggle button
      const toggle = document.querySelector(".mobile-nav-toggle");
      if (toggle) {
        toggle.setAttribute("aria-expanded", "false");
        toggle.innerHTML = '<i class="icon-menu"></i>';
      }
    }
  }

  toggleDropdown(toggle) {
    const dropdown = toggle.parentElement;
    const isActive = dropdown.classList.contains("active");

    // Close other dropdowns
    const allDropdowns = document.querySelectorAll(".mobile-nav .dropdown");
    allDropdowns.forEach((d) => d.classList.remove("active"));

    // Toggle current dropdown
    if (!isActive) {
      dropdown.classList.add("active");
    }

    // Update aria-expanded
    toggle.setAttribute("aria-expanded", !isActive);
  }

  handleWindowResize() {
    const mobileNav = document.querySelector(".mobile-nav");
    const windowWidth = window.innerWidth;

    // Close mobile menu on desktop
    if (
      windowWidth > 768 &&
      mobileNav &&
      mobileNav.classList.contains("active")
    ) {
      this.closeMobileMenu();
    }

    // Reset dropdown states on resize
    const dropdowns = document.querySelectorAll(".mobile-nav .dropdown");
    dropdowns.forEach((dropdown) => {
      dropdown.classList.remove("active");
      const toggle = dropdown.querySelector(".dropdown-toggle");
      if (toggle) {
        toggle.setAttribute("aria-expanded", "false");
      }
    });
  }
}

// Admin sidebar toggle for mobile
class AdminSidebar {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    const sidebarToggle = document.querySelector(".admin-sidebar-toggle");
    if (sidebarToggle) {
      sidebarToggle.addEventListener("click", () => this.toggleSidebar());
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", (e) => {
      const sidebar = document.querySelector(".admin-sidebar");
      const toggle = document.querySelector(".admin-sidebar-toggle");

      if (sidebar && sidebar.classList.contains("show")) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
          this.closeSidebar();
        }
      }
    });

    // Handle window resize
    window.addEventListener("resize", () => this.handleWindowResize());
  }

  toggleSidebar() {
    const sidebar = document.querySelector(".admin-sidebar");
    const overlay = document.querySelector(".admin-sidebar-overlay");

    if (sidebar) {
      sidebar.classList.toggle("show");

      if (overlay) {
        overlay.classList.toggle("show");
      }
    }
  }

  closeSidebar() {
    const sidebar = document.querySelector(".admin-sidebar");
    const overlay = document.querySelector(".admin-sidebar-overlay");

    if (sidebar) {
      sidebar.classList.remove("show");

      if (overlay) {
        overlay.classList.remove("show");
      }
    }
  }

  handleWindowResize() {
    const windowWidth = window.innerWidth;
    const sidebar = document.querySelector(".admin-sidebar");

    // Auto-close sidebar on larger screens
    if (windowWidth > 768 && sidebar && sidebar.classList.contains("show")) {
      this.closeSidebar();
    }
  }
}

// Filter sidebar toggle for mobile
class FilterSidebar {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    const filterToggle = document.querySelector(".filter-toggle");
    if (filterToggle) {
      filterToggle.addEventListener("click", () => this.toggleFilterSidebar());
    }

    // Close filter sidebar when clicking outside
    document.addEventListener("click", (e) => {
      const sidebar = document.querySelector(".filter-sidebar");
      const toggle = document.querySelector(".filter-toggle");

      if (sidebar && sidebar.classList.contains("show")) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
          this.closeFilterSidebar();
        }
      }
    });

    // Handle window resize
    window.addEventListener("resize", () => this.handleWindowResize());
  }

  toggleFilterSidebar() {
    const sidebar = document.querySelector(".filter-sidebar");

    if (sidebar) {
      sidebar.classList.toggle("show");
    }
  }

  closeFilterSidebar() {
    const sidebar = document.querySelector(".filter-sidebar");

    if (sidebar) {
      sidebar.classList.remove("show");
    }
  }

  handleWindowResize() {
    const windowWidth = window.innerWidth;
    const sidebar = document.querySelector(".filter-sidebar");

    // Auto-close filter sidebar on larger screens
    if (windowWidth > 768 && sidebar && sidebar.classList.contains("show")) {
      this.closeFilterSidebar();
    }
  }
}

// Smooth scrolling for anchor links
class SmoothScroll {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        const targetId = link.getAttribute("href");
        const targetElement = document.querySelector(targetId);

        if (targetElement) {
          e.preventDefault();
          this.scrollToElement(targetElement);
        }
      });
    });
  }

  scrollToElement(element) {
    const offsetTop = element.offsetTop - 80; // Account for fixed header
    window.scrollTo({
      top: offsetTop,
      behavior: "smooth",
    });
  }
}

// Back to top button
class BackToTop {
  constructor() {
    this.button = null;
    this.init();
  }

  init() {
    this.createButton();
    this.bindEvents();
  }

  createButton() {
    this.button = document.createElement("button");
    this.button.className = "back-to-top";
    this.button.innerHTML = '<i class="icon-arrow-up"></i>';
    this.button.setAttribute("aria-label", "Back to top");
    document.body.appendChild(this.button);
  }

  bindEvents() {
    if (this.button) {
      this.button.addEventListener("click", () => this.scrollToTop());

      window.addEventListener("scroll", () => this.toggleVisibility());
    }
  }

  scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  }

  toggleVisibility() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop > 300) {
      this.button.classList.add("visible");
    } else {
      this.button.classList.remove("visible");
    }
  }
}

// Initialize all navigation components when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  new MobileNavigation();
  new AdminSidebar();
  new FilterSidebar();
  new SmoothScroll();
  new BackToTop();
});

// Export for potential use in other scripts
window.MobileNavigation = MobileNavigation;
window.AdminSidebar = AdminSidebar;
window.FilterSidebar = FilterSidebar;
window.SmoothScroll = SmoothScroll;
window.BackToTop = BackToTop;
