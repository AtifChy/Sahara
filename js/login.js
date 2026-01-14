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
  attachEventListeners();
  setupPasswordToggles();

  function attachEventListeners() {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      validateEmail();
      validatePassword();
    });

    // focus change validation
    emailInput.addEventListener("blur", validateEmail);
    passwordInput.addEventListener("blur", validatePassword);

    // clear errors on input
    emailInput.addEventListener("input", () => {
      clearError(emailError);
    });
    passwordInput.addEventListener("input", () => {
      clearError(passwordError);
    });
  }

  function validateEmail() {
    validateEmailField(emailInput, emailError);
  }

  function validatePassword() {
    validatePasswordField(passwordInput, passwordError);
  }
})();
