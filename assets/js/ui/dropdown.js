/**
 * Dropdown Module
 */
const DropdownModule = (function() {
  function init() {
    initCustomDropdowns();
  }

  function initCustomDropdowns() {
    const dropdowns = document.querySelectorAll('.custom-dropdown');
    
    dropdowns.forEach(dropdown => {
      const selected = dropdown.querySelector('.selected');
      const options = dropdown.querySelector('.options');
      const optionItems = dropdown.querySelectorAll('.option');
      
      selected.addEventListener('click', (e) => {
        e.stopPropagation();
        closeAllDropdowns();
        dropdown.classList.toggle('active');
        options.classList.toggle('show');
        selected.classList.toggle('open');
      });
      
      optionItems.forEach(option => {
        option.addEventListener('click', (e) => {
          e.stopPropagation();
          const value = option.dataset.value;
          const text = option.textContent;
          
          selected.innerHTML = `${text} ${getChevronIcon()}`;
          selected.dataset.value = value;
          
          optionItems.forEach(opt => opt.classList.remove('selected'));
          option.classList.add('selected');
          
          dropdown.classList.remove('active');
          options.classList.remove('show');
          selected.classList.remove('open');
          
          document.dispatchEvent(new CustomEvent('dropdown-change', { 
            detail: { value, text } 
          }));
        });
      });
    });
    
    document.addEventListener('click', closeAllDropdowns);
  }

  function closeAllDropdowns() {
    document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
      dropdown.classList.remove('active');
      const options = dropdown.querySelector('.options');
      const selected = dropdown.querySelector('.selected');
      if (options) options.classList.remove('show');
      if (selected) selected.classList.remove('open');
    });
  }

  function getChevronIcon() {
    return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>';
  }

  return { init };
})();

window.DropdownModule = DropdownModule;

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
  DropdownModule.init();
});