/**
 * Authentication Page Logic
 */

// Check if already logged in
if (API.isAuthenticated()) {
  window.location.href = "feed.html";
}

// Register Form Handler
const registerForm = document.getElementById("registerForm");
if (registerForm) {
  registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = {
      username: document.getElementById("username").value.trim(),
      email: document.getElementById("email").value.trim(),
      password: document.getElementById("password").value,
      full_name: document.getElementById("fullName").value.trim(),
      bio: document.getElementById("bio")?.value.trim() || "",
    };

    // Validation
    if (
      !formData.username ||
      !formData.email ||
      !formData.password ||
      !formData.full_name
    ) {
      Utils.showToast("Please fill in all required fields", "error");
      return;
    }

    if (!Utils.isValidEmail(formData.email)) {
      Utils.showToast("Please enter a valid email address", "error");
      return;
    }

    if (formData.password.length < 6) {
      Utils.showToast("Password must be at least 6 characters", "error");
      return;
    }

    const submitBtn = registerForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = "Creating account...";

    try {
      const response = await API.register(formData);

      if (response.success) {
        API.saveAuth(response.data);
        Utils.showToast("Account created successfully!", "success");

        setTimeout(() => {
          window.location.href = "feed.html";
        }, 1000);
      }
    } catch (error) {
      Utils.showToast(error.message || "Registration failed", "error");
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });
}

// Login Form Handler
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = {
      username: document.getElementById("username").value.trim(),
      password: document.getElementById("password").value,
    };

    // Validation
    if (!formData.username || !formData.password) {
      Utils.showToast("Please fill in all fields", "error");
      return;
    }

    const submitBtn = loginForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = "Logging in...";

    try {
      const response = await API.login(formData);

      if (response.success) {
        API.saveAuth(response.data);
        Utils.showToast("Login successful!", "success");

        setTimeout(() => {
          window.location.href = "feed.html";
        }, 1000);
      }
    } catch (error) {
      Utils.showToast(error.message || "Login failed", "error");
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });
}
