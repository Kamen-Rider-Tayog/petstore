/**
 * Utility Functions Module
 */

const Utils = {
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  throttle(func, limit) {
    let inThrottle;
    return function() {
      const args = arguments;
      const context = this;
      if (!inThrottle) {
        func.apply(context, args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  },

  formatCurrency(amount, currency = "PHP") {
    return new Intl.NumberFormat("en-PH", {
      style: "currency",
      currency: currency,
    }).format(amount);
  },

  showLoading() {
    const spinner = document.getElementById("loadingSpinner");
    if (spinner) spinner.style.display = "flex";
  },

  hideLoading() {
    const spinner = document.getElementById("loadingSpinner");
    if (spinner) spinner.style.display = "none";
  },

  async ajax(url, options = {}) {
    const defaultOptions = {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    };

    const config = { ...defaultOptions, ...options };

    try {
      this.showLoading();
      const response = await fetch(url, config);
      return await response.json();
    } catch (error) {
      console.error("AJAX Error:", error);
      throw error;
    } finally {
      this.hideLoading();
    }
  }
};

window.Utils = Utils;