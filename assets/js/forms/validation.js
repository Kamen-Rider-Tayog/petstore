/**
 * Form Validation Module
 */

const FormValidationModule = (function() {
  function init() {
    initForms();
  }

  function initForms() {
    const forms = document.querySelectorAll("form[data-validate]");
    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        if (!validateForm(form)) e.preventDefault();
      });
    });
  }

  function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll("input, textarea, select");
    inputs.forEach((input) => {
      if (!validateField(input)) isValid = false;
    });
    return isValid;
  }

  function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = "";

    if (field.hasAttribute("required") && !value) {
      isValid = false;
      message = "This field is required";
    }

    if (field.type === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        message = "Please enter a valid email address";
      }
    }

    setFieldError(field, message);
    return isValid;
  }

  function setFieldError(field, message) {
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

  return { init, initForms, validateForm, validateField };
})();

window.FormValidationModule = FormValidationModule;