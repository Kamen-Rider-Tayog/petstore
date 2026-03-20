/**
 * Image Module - Lazy Loading
 */
const ImageModule = (function() {
  function initLazyLoading() {
    const images = document.querySelectorAll("img[data-src]");
    if (!images.length) return;

    const imageObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.remove("lazy");
          imageObserver.unobserve(img);
        }
      });
    });

    images.forEach((img) => imageObserver.observe(img));
  }

  return { initLazyLoading };
})();

window.ImageModule = ImageModule;