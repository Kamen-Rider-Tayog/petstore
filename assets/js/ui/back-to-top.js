/**
 * Back to Top Button Module
 */

const BackToTopModule = (function() {
  let button = null;

  function init() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', createButton);
    } else {
      createButton();
    }
    bindEvents();
  }

  function createButton() {
    // Check if button already exists
    if (document.querySelector('.back-to-top')) {
      button = document.querySelector('.back-to-top');
      return;
    }
    
    button = document.createElement("button");
    button.className = "back-to-top";
    button.setAttribute("aria-label", "Back to top");
    button.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 12L12 8M12 8L8 12M12 8V16M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`;
    
    // Ensure body exists before appending
    if (document.body) {
      document.body.appendChild(button);
    } else {
      // Fallback: wait for body
      document.addEventListener('DOMContentLoaded', function() {
        document.body.appendChild(button);
      });
    }
  }

  function bindEvents() {
    window.addEventListener("scroll", toggleVisibility);
  }

  function toggleVisibility() {
    if (!button) {
      button = document.querySelector('.back-to-top');
      if (!button) return;
    }
    
    if (window.pageYOffset > 300) {
      button.classList.add("visible");
    } else {
      button.classList.remove("visible");
    }
  }

  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  // Add click listener
  document.addEventListener('click', function(e) {
    if (e.target.closest('.back-to-top')) {
      e.preventDefault();
      scrollToTop();
    }
  });

  return { init };
})();

window.BackToTopModule = BackToTopModule;