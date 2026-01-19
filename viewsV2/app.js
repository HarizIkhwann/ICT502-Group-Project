// Global configuration
const CONFIG = {
  API_BASE: "http://localhost/ICT502%20Group%20Project/api",
  NOTIFICATION_DURATION: 3000,
};

// Utility functions
const Utils = {
  // Show notification
  showNotification(message, type = "success") {
    const notification = document.getElementById("notification");
    if (!notification) return;

    notification.textContent = message;
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-[70] transition-all ${
      type === "success" ? "bg-green-500 text-white" : "bg-red-500 text-white"
    }`;
    notification.classList.remove("hidden");
    setTimeout(
      () => notification.classList.add("hidden"),
      CONFIG.NOTIFICATION_DURATION,
    );
  },

  // Format date
  formatDate(dateString) {
    if (!dateString) return "N/A";
    return dateString.split("T")[0];
  },

  // Format currency
  formatCurrency(amount) {
    return (
      "$" +
      parseFloat(amount || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
      })
    );
  },

  // Toggle sidebar
  toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebar-overlay");
    sidebar?.classList.toggle("-translate-x-full");
    overlay?.classList.toggle("hidden");
  },

  // Load sidebar
  async loadSidebar() {
    try {
      const response = await fetch("sidebar.html");
      const html = await response.text();
      const container = document.getElementById("sidebar-container");
      if (container) container.innerHTML = html;
    } catch (error) {
      console.error("Failed to load sidebar:", error);
    }
  },

  // API call wrapper
  async apiCall(endpoint, options = {}) {
    try {
      const url = `${CONFIG.API_BASE}${endpoint}`;
      const response = await fetch(url, {
        headers: {
          "Content-Type": "application/json",
          ...options.headers,
        },
        ...options,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      return result;
    } catch (error) {
      console.error("API call failed:", error);
      throw error;
    }
  },
};

// Make Utils available globally
window.Utils = Utils;
window.CONFIG = CONFIG;
window.toggleSidebar = Utils.toggleSidebar;

// Auto-load sidebar on page load
document.addEventListener("DOMContentLoaded", () => {
  Utils.loadSidebar();
});
