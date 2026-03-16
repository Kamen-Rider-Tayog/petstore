// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');

    if (contactForm) {
        // Real-time validation
        const inputs = contactForm.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });

        // AJAX form submission
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form before submission
            if (!validateForm(this)) {
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Sending...';

            // Prepare form data
            const formData = new FormData(this);

            // Send AJAX request
            fetch('contact_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                // Show message
                showFormMessage(data.message, data.success ? 'success' : 'error');

                // Clear form on success
                if (data.success) {
                    contactForm.reset();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                showFormMessage('An error occurred. Please try again later.', 'error');
            });
        });
    }

    // Smooth scroll to form when clicking FAQ CTA
    const faqLink = document.querySelector('.faq-cta a[href="#contact-form"]');
    if (faqLink) {
        faqLink.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('contact-form');
            if (form) {
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Focus on name field
                const nameField = form.querySelector('#name');
                if (nameField) nameField.focus();
            }
        });
    }
});

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input, textarea, select');

    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });

    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Clear previous errors
    clearFieldError(field);

    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required.';
    }

    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        }
    }

    // Minimum length validation
    if (field.hasAttribute('minlength') && value) {
        const minLength = parseInt(field.getAttribute('minlength'));
        if (value.length < minLength) {
            isValid = false;
            errorMessage = `Minimum ${minLength} characters required.`;
        }
    }

    if (!isValid) {
        showFieldError(field, errorMessage);
    }

    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('error');

    let errorElement = field.parentElement.querySelector('.field-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        field.parentElement.appendChild(errorElement);
    }
    errorElement.textContent = message;
}

function clearFieldError(field) {
    field.classList.remove('error');
    const errorElement = field.parentElement.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

function showFormMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.form-message');
    existingMessages.forEach(msg => msg.remove());

    const messageElement = document.createElement('div');
    messageElement.className = `form-message ${type}`;
    messageElement.innerHTML = message;

    const form = document.getElementById('contact-form');
    form.insertBefore(messageElement, form.firstChild);

    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.parentNode.removeChild(messageElement);
            }
        }, 5000);
    }

    // Scroll to message
    messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
