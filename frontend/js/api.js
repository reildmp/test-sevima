/**
 * API Configuration and Helper Functions
 */

const API_BASE_URL = "http://localhost:8000/api";

// API Helper Functions
const API = {
  // Get token from localStorage
  getToken() {
    return localStorage.getItem("token");
  },

  // Get user data from localStorage
  getUser() {
    const user = localStorage.getItem("user");
    return user ? JSON.parse(user) : null;
  },

  // Save auth data
  saveAuth(data) {
    localStorage.setItem("token", data.token);
    localStorage.setItem("user", JSON.stringify(data));
  },

  // Clear auth data
  clearAuth() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
  },

  // Check if user is authenticated
  isAuthenticated() {
    return !!this.getToken();
  },

  // Make API request
  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    const token = this.getToken();

    const config = {
      ...options,
      headers: {
        ...options.headers,
      },
    };

    // Add authorization header if token exists
    if (token && !options.skipAuth) {
      config.headers["Authorization"] = `Bearer ${token}`;
    }

    // Add content-type for JSON requests
    if (options.body && !(options.body instanceof FormData)) {
      config.headers["Content-Type"] = "application/json";
      config.body = JSON.stringify(options.body);
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Something went wrong");
      }

      return data;
    } catch (error) {
      console.error("API Error:", error);
      throw error;
    }
  },

  // Auth endpoints
  async register(userData) {
    return this.request("/auth/register.php", {
      method: "POST",
      body: userData,
      skipAuth: true,
    });
  },

  async login(credentials) {
    return this.request("/auth/login.php", {
      method: "POST",
      body: credentials,
      skipAuth: true,
    });
  },

  // Post endpoints
  async getPosts(limit = 20, offset = 0) {
    return this.request(`/posts/list.php?limit=${limit}&offset=${offset}`, {
      method: "GET",
    });
  },

  async createPost(formData) {
    return this.request("/posts/create.php", {
      method: "POST",
      body: formData,
    });
  },

  async deletePost(postId) {
    return this.request("/posts/delete.php", {
      method: "DELETE",
      body: { id: postId },
    });
  },

  // Like endpoints
  async toggleLike(postId) {
    return this.request("/likes/toggle.php", {
      method: "POST",
      body: { post_id: postId },
    });
  },

  // Comment endpoints
  async getComments(postId) {
    return this.request(`/comments/list.php?post_id=${postId}`, {
      method: "GET",
    });
  },

  async createComment(postId, commentText) {
    return this.request("/comments/create.php", {
      method: "POST",
      body: {
        post_id: postId,
        comment_text: commentText,
      },
    });
  },

  async deleteComment(commentId) {
    return this.request("/comments/delete.php", {
      method: "DELETE",
      body: { id: commentId },
    });
  },
};

// Utility Functions
const Utils = {
  // Format time ago
  timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    const intervals = {
      year: 31536000,
      month: 2592000,
      week: 604800,
      day: 86400,
      hour: 3600,
      minute: 60,
    };

    for (const [unit, secondsInUnit] of Object.entries(intervals)) {
      const interval = Math.floor(seconds / secondsInUnit);
      if (interval >= 1) {
        return `${interval} ${unit}${interval > 1 ? "s" : ""} ago`;
      }
    }

    return "just now";
  },

  // Show toast notification
  showToast(message, type = "info") {
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: ${type === "error" ? "#ed4956" : type === "success" ? "#00c853" : "#667eea"};
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = "fadeOut 0.3s ease";
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  },

  // Validate email
  isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  },

  // Debounce function
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
};

// Export for use in other files
window.API = API;
window.Utils = Utils;
