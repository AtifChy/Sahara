import {
  clearError,
  setupPasswordToggles,
  validateEmailField,
  validatePasswordField,
} from "./auth.js";

(() => {
  const form = document.getElementById("login-form");
  if (!form) return;

  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");

  const emailError = document.getElementById("email-error");
  const passwordError = document.getElementById("password-error");

  // Initialize
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    validateEmail();
    validatePassword();
  });

  emailInput.addEventListener("blur", validateEmail);
  passwordInput.addEventListener("blur", validatePassword);

  emailInput.addEventListener("input", () => {
    clearError(emailError);
  });
  passwordInput.addEventListener("input", () => {
    clearError(passwordError);
  });

  setupPasswordToggles();

  // Validation functions
  function validateEmail() {
    return validateEmailField(emailInput, emailError);
  }

  function validatePassword() {
    return validatePasswordField(passwordInput, passwordError);
  }
})();
