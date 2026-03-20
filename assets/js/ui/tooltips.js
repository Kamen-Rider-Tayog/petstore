/**
 * Tooltips Module
 */

const TooltipModule = (function() {
  function init() {
    const tooltips = document.querySelectorAll("[data-tooltip]");
    tooltips.forEach((element) => {
      element.addEventListener("mouseenter", (e) => showTooltip(e, element));
      element.addEventListener("mouseleave", hideTooltip);
    });
  }

  function showTooltip(event, element) {
    const text = element.getAttribute("data-tooltip");
    if (!text) return;

    const tooltip = document.createElement("div");
    tooltip.className = "tooltip";
    tooltip.textContent = text;
    document.body.appendChild(tooltip);

    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px";
    tooltip.classList.add("visible");
  }

  function hideTooltip() {
    const tooltip = document.querySelector(".tooltip");
    if (tooltip) tooltip.remove();
  }

  return { init, showTooltip, hideTooltip };
})();

window.TooltipModule = TooltipModule;