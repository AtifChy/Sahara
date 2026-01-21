import { clearError, validateEmailField } from "./auth.js";

(() => {
  const form = document.querySelector(".newsletter-form");
  if (!form) return;

  const emailInput = document.getElementById("email");
  const emailError = document.getElementById("email-error");

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    validateEmailField(emailInput, emailError);
  });

  emailInput.addEventListener("blur", () => {
    validateEmailField(emailInput, emailError);
  });

  emailInput.addEventListener("input", () => {
    clearError(emailError);
  });
})();
