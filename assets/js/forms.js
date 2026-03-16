/**
 * Form Validation JavaScript
 * Handles client-side validation for all forms with real-time feedback
 */

class FormValidator {
  constructor(formElement) {
    this.form = formElement;
    this.init();
  }

  init() {
    this.bindEvents();
    this.setupRealTimeValidation();
  }

  bindEvents() {
    if (this.form) {
      this.form.addEventListener("submit", (e) => this.handleSubmit(e));
    }
  }

  setupRealTimeValidation() {
    const inputs = this.form.querySelectorAll("input, textarea, select");
    inputs.forEach((input) => {
      input.addEventListener("blur", () => this.validateField(input));
      input.addEventListener("input", () => this.clearFieldError(input));

      // Special handling for password fields
      if (input.type === "password") {
        input.addEventListener("input", () =>
          this.checkPasswordStrength(input),
        );
      }

      // Special handling for email fields
      if (input.type === "email") {
        input.addEventListener("input", () =>
          this.validateEmailRealtime(input),
        );
      }
    });
  }

  handleSubmit(e) {
    const isValid = this.validateForm();

    if (!isValid) {
      e.preventDefault();
      this.showFormError("Please correct the errors below and try again.");
    } else {
      this.showFormSuccess("Form submitted successfully!");
    }
  }

  validateForm() {
    let isValid = true;
    const inputs = this.form.querySelectorAll("input, textarea, select");

    inputs.forEach((input) => {
      if (!this.validateField(input)) {
        isValid = false;
      }
    });

    return isValid;
  }

  validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name || field.id;
    let isValid = true;
    let errorMessage = "";

    // Clear previous errors
    this.clearFieldError(field);

    // Required field validation
    if (field.hasAttribute("required") && !value) {
      isValid = false;
      errorMessage = `${this.getFieldLabel(field)} is required.`;
    }

    // Email validation
    if (field.type === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        errorMessage = "Please enter a valid email address.";
      }
    }

    // Password validation
    if (field.type === "password" && value) {
      if (value.length < 8) {
        isValid = false;
        errorMessage = "Password must be at least 8 characters long.";
      } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
        isValid = false;
        errorMessage =
          "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
      }
    }

    // Confirm password validation
    if (field.name === "confirm_password") {
      const passwordField = this.form.querySelector('input[name="password"]');
      if (passwordField && value !== passwordField.value) {
        isValid = false;
        errorMessage = "Passwords do not match.";
      }
    }

    // Phone validation
    if (field.type === "tel" && value) {
      const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
      if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ""))) {
        isValid = false;
        errorMessage = "Please enter a valid phone number.";
      }
    }

    // URL validation
    if (field.type === "url" && value) {
      try {
        new URL(value);
      } catch {
        isValid = false;
        errorMessage = "Please enter a valid URL.";
      }
    }

    // Minimum length validation
    if (field.hasAttribute("minlength") && value) {
      const minLength = parseInt(field.getAttribute("minlength"));
      if (value.length < minLength) {
        isValid = false;
        errorMessage = `${this.getFieldLabel(field)} must be at least ${minLength} characters long.`;
      }
    }

    // Maximum length validation
    if (field.hasAttribute("maxlength") && value) {
      const maxLength = parseInt(field.getAttribute("maxlength"));
      if (value.length > maxLength) {
        isValid = false;
        errorMessage = `${this.getFieldLabel(field)} must not exceed ${maxLength} characters.`;
      }
    }

    // Numeric validation
    if (field.type === "number" && value) {
      const numValue = parseFloat(value);
      if (isNaN(numValue)) {
        isValid = false;
        errorMessage = "Please enter a valid number.";
      }

      if (field.hasAttribute("min")) {
        const min = parseFloat(field.getAttribute("min"));
        if (numValue < min) {
          isValid = false;
          errorMessage = `${this.getFieldLabel(field)} must be at least ${min}.`;
        }
      }

      if (field.hasAttribute("max")) {
        const max = parseFloat(field.getAttribute("max"));
        if (numValue > max) {
          isValid = false;
          errorMessage = `${this.getFieldLabel(field)} must not exceed ${max}.`;
        }
      }
    }

    // Custom validation for specific fields
    if (field.name === "username" && value) {
      if (value.length < 3) {
        isValid = false;
        errorMessage = "Username must be at least 3 characters long.";
      } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
        isValid = false;
        errorMessage =
          "Username can only contain letters, numbers, and underscores.";
      }
    }

    // Credit card validation
    if (field.name === "card_number" && value) {
      const cardRegex = /^[0-9]{13,19}$/;
      if (!cardRegex.test(value.replace(/\s/g, ""))) {
        isValid = false;
        errorMessage = "Please enter a valid credit card number.";
      }
    }

    // CVV validation
    if (field.name === "cvv" && value) {
      if (!/^[0-9]{3,4}$/.test(value)) {
        isValid = false;
        errorMessage = "Please enter a valid CVV (3-4 digits).";
      }
    }

    // Expiry date validation
    if (field.name === "expiry_date" && value) {
      const expiryRegex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
      if (!expiryRegex.test(value)) {
        isValid = false;
        errorMessage = "Please enter a valid expiry date (MM/YY).";
      } else {
        const [month, year] = value.split("/");
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear() % 100;
        const currentMonth = currentDate.getMonth() + 1;

        if (
          parseInt(year) < currentYear ||
          (parseInt(year) === currentYear && parseInt(month) < currentMonth)
        ) {
          isValid = false;
          errorMessage = "Card has expired.";
        }
      }
    }

    if (!isValid) {
      this.showFieldError(field, errorMessage);
    }

    return isValid;
  }

  validateEmailRealtime(field) {
    const value = field.value.trim();
    if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      this.showFieldError(field, "Please enter a valid email address.");
    } else {
      this.clearFieldError(field);
    }
  }

  checkPasswordStrength(field) {
    const value = field.value;
    const strengthIndicator =
      field.parentElement.querySelector(".password-strength");

    if (!strengthIndicator) return;

    let strength = 0;
    let feedback = [];

    if (value.length >= 8) strength++;
    else feedback.push("At least 8 characters");

    if (/[a-z]/.test(value)) strength++;
    else feedback.push("Lowercase letter");

    if (/[A-Z]/.test(value)) strength++;
    else feedback.push("Uppercase letter");

    if (/\d/.test(value)) strength++;
    else feedback.push("Number");

    if (/[^a-zA-Z\d]/.test(value)) strength++;
    else feedback.push("Special character");

    const strengthClasses = ["weak", "fair", "good", "strong"];
    const strengthTexts = ["Weak", "Fair", "Good", "Strong"];

    strengthIndicator.className =
      "password-strength " + strengthClasses[Math.min(strength - 1, 3)];
    strengthIndicator.textContent = strengthTexts[Math.min(strength - 1, 3)];

    if (strength < 4) {
      strengthIndicator.title = "Missing: " + feedback.join(", ");
    } else {
      strengthIndicator.title = "Password strength is good!";
    }
  }

  getFieldLabel(field) {
    // Try to find label by 'for' attribute
    const label = this.form.querySelector(`label[for="${field.id}"]`);
    if (label) {
      return label.textContent.replace("*", "").trim();
    }

    // Try to find label by name
    const labels = this.form.querySelectorAll("label");
    for (let label of labels) {
      if (
        label.htmlFor === field.id ||
        label.getAttribute("for") === field.name
      ) {
        return label.textContent.replace("*", "").trim();
      }
    }

    // Fallback to field name or placeholder
    return field.placeholder || field.name || "This field";
  }

  showFieldError(field, message) {
    field.classList.add("error");

    let errorElement = field.parentElement.querySelector(".field-error");
    if (!errorElement) {
      errorElement = document.createElement("div");
      errorElement.className = "field-error";
      field.parentElement.appendChild(errorElement);
    }
    errorElement.textContent = message;
  }

  clearFieldError(field) {
    field.classList.remove("error");
    const errorElement = field.parentElement.querySelector(".field-error");
    if (errorElement) {
      errorElement.remove();
    }
  }

  showFormError(message) {
    this.showFormMessage(message, "error");
  }

  showFormSuccess(message) {
    this.showFormMessage(message, "success");
  }

  showFormMessage(message, type) {
    let messageElement = this.form.querySelector(".form-message");
    if (!messageElement) {
      messageElement = document.createElement("div");
      messageElement.className = "form-message";
      this.form.insertBefore(messageElement, this.form.firstChild);
    }

    messageElement.className = `form-message ${type}`;
    messageElement.textContent = message;

    // Auto-hide success messages after 5 seconds
    if (type === "success") {
      setTimeout(() => {
        if (messageElement) {
          messageElement.remove();
        }
      }, 5000);
    }
  }
}

// Password confirmation matching
class PasswordMatcher {
  constructor(passwordField, confirmField) {
    this.passwordField = passwordField;
    this.confirmField = confirmField;
    this.init();
  }

  init() {
    this.confirmField.addEventListener("input", () => this.checkMatch());
    this.passwordField.addEventListener("input", () => this.checkMatch());
  }

  checkMatch() {
    const password = this.passwordField.value;
    const confirm = this.confirmField.value;

    if (confirm && password !== confirm) {
      this.showMismatch();
    } else {
      this.clearMismatch();
    }
  }

  showMismatch() {
    this.confirmField.classList.add("error");
    let errorElement =
      this.confirmField.parentElement.querySelector(".field-error");
    if (!errorElement) {
      errorElement = document.createElement("div");
      errorElement.className = "field-error";
      this.confirmField.parentElement.appendChild(errorElement);
    }
    errorElement.textContent = "Passwords do not match.";
  }

  clearMismatch() {
    this.confirmField.classList.remove("error");
    const errorElement =
      this.confirmField.parentElement.querySelector(".field-error");
    if (errorElement) {
      errorElement.remove();
    }
  }
}

// Initialize form validation for all forms
document.addEventListener("DOMContentLoaded", function () {
  // Initialize validation for all forms
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    new FormValidator(form);
  });

  // Initialize password matching for registration forms
  const passwordFields = document.querySelectorAll('input[name="password"]');
  const confirmFields = document.querySelectorAll(
    'input[name="confirm_password"]',
  );

  passwordFields.forEach((passwordField, index) => {
    if (confirmFields[index]) {
      new PasswordMatcher(passwordField, confirmFields[index]);
    }
  });

  // Add password strength indicators
  const passwordInputs = document.querySelectorAll('input[type="password"]');
  passwordInputs.forEach((input) => {
    if (!input.name.includes("confirm")) {
      const strengthIndicator = document.createElement("div");
      strengthIndicator.className = "password-strength";
      input.parentElement.appendChild(strengthIndicator);
    }
  });
});

// Export for potential use in other scripts
window.FormValidator = FormValidator;
window.PasswordMatcher = PasswordMatcher;
