/**
 * Core App Class
 * Main application initializer
 */

class PetStoreApp {
  constructor() {
    this.init();
  }

  init() {
    this.loadModules();
  }

  loadModules() {
    // Initialize all modules in order
    if (window.TooltipModule) window.TooltipModule.init();
    if (window.ModalModule) window.ModalModule.init();
    if (window.BackToTopModule) window.BackToTopModule.init();
    if (window.NotificationModule) window.NotificationModule.init();
    if (window.FormValidationModule) window.FormValidationModule.init();
    if (window.CartModule) window.CartModule.updateCartCount();
    if (window.DropdownModule) window.DropdownModule.init();
    
    // Page load events
    document.addEventListener("DOMContentLoaded", () => {
      this.onPageLoad();
    });

    // Global error handling
    window.addEventListener("unhandledrejection", (event) => {
      console.error("Unhandled promise rejection:", event.reason);
      if (window.NotificationModule) {
        window.NotificationModule.show("An error occurred. Please try again.", "error");
      }
    });
  }

  onPageLoad() {
    if (window.FormValidationModule) window.FormValidationModule.initForms();
    if (window.ImageModule) window.ImageModule.initLazyLoading();
    if (window.ScrollModule) window.ScrollModule.initSmoothScrolling();
    if (window.CartModule) window.CartModule.renderMiniCart();
    if (window.CartModule) window.CartModule.attachAddToCartHandlers();
  }
}

// Initialize the app
const petStoreApp = new PetStoreApp();

// Make utility functions globally available (for backward compatibility)
window.showModal = (content, options) => window.ModalModule?.showModal(content, options);
window.closeModal = () => window.ModalModule?.closeModal();
window.showNotification = (message, type, duration) => window.NotificationModule?.show(message, type, duration);
window.formatCurrency = (amount, currency) => window.Utils?.formatCurrency(amount, currency);
window.updateCartCount = () => window.CartModule?.updateCartCount();
window.addToCart = (productId, quantity) => window.CartModule?.addToCart(productId, quantity);
window.removeItem = (cartId) => window.CartModule?.removeItem(cartId);
window.renderMiniCart = () => window.CartModule?.renderMiniCart();