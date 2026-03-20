/**
 * Scroll Module - Smooth Scrolling
 */
const ScrollModule = (function() {
  function initSmoothScrolling() {
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

  return { initSmoothScrolling };
})();

window.ScrollModule = ScrollModule;