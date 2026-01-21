import {
  calculatePasswordStrength,
  clearError,
  setupPasswordToggles,
  showError,
  updatePasswordStrengthUI,
  validateEmailField,
  validateGenderSelect,
  validateNameField,
  validatePasswordField,
  validatePhoneField,
  validateTermsCheckbox,
} from "./auth.js";

(() => {
  const form = document.getElementById("signup-form");
  if (!form) return;

  const nameInput = document.getElementById("name");
  const emailInput = document.getElementById("email");
  const phoneInput = document.getElementById("phone");
  const genderSelect = document.getElementById("gender");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");
  const termsCheckbox = document.getElementById("terms");

  const nameError = document.getElementById("name-error");
  const emailError = document.getElementById("email-error");
  const phoneError = document.getElementById("phone-error");
  const genderError = document.getElementById("gender-error");
  const passwordError = document.getElementById("password-error");
  const confirmPasswordError = document.getElementById(
    "confirm-password-error",
  );
  const termsError = document.getElementById("terms-error");

  const strengthBar = document.querySelector(".strength-fill");
  const strengthText = document.querySelector(".strength-text");

  // Password Toggle Setup
  setupPasswordToggles();

  // Event Listener
  form.addEventListener("submit", (e) => {
    const isNameValid = validateName();
    const isEmailValid = validateEmail();
    const isPhoneValid = validatePhone();
    const isGenderValid = validateGender();
    const isPasswordValid = validatePassword();
    const isConfirmPasswordValid = validateConfirmPassword();
    const isTermsValid = validateTerms();

    const isFormValid =
      isNameValid &&
      isEmailValid &&
      isPhoneValid &&
      isGenderValid &&
      isPasswordValid &&
      isConfirmPasswordValid &&
      isTermsValid;

    if (!isFormValid) {
      e.preventDefault();

      if (!isNameValid) nameInput.focus();
      else if (!isEmailValid) emailInput.focus();
      else if (!isPhoneValid) phoneInput.focus();
      else if (!isPasswordValid) passwordInput.focus();
      else if (!isConfirmPasswordValid) confirmPasswordInput.focus();
    }
  });

  nameInput.addEventListener("blur", validateName);
  nameInput.addEventListener("input", validateName);

  emailInput.addEventListener("blur", validateEmail);
  emailInput.addEventListener("input", validateEmail);

  phoneInput.addEventListener("blur", validatePhone);
  phoneInput.addEventListener("input", validatePhone);

  genderSelect.addEventListener("blur", validateGender);
  genderSelect.addEventListener("change", validateGender);

  passwordInput.addEventListener("blur", validatePassword);
  passwordInput.addEventListener("input", () => {
    validatePassword();
    if (confirmPasswordInput.value) {
      validateConfirmPassword();
    }
  });

  confirmPasswordInput.addEventListener("blur", validateConfirmPassword);
  confirmPasswordInput.addEventListener("input", validateConfirmPassword);

  termsCheckbox.addEventListener("change", validateTerms);

  // Validation functions
  function validateName() {
    return validateNameField(nameInput, nameError);
  }

  function validateEmail() {
    return validateEmailField(emailInput, emailError);
  }

  function validatePhone() {
    return validatePhoneField(phoneInput, phoneError);
  }

  function validateGender() {
    return validateGenderSelect(genderSelect, genderError);
  }

  function validatePassword() {
    const isValid = validatePasswordField(
      passwordInput,
      passwordError,
      8,
      true,
    );

    const strength = calculatePasswordStrength(passwordInput.value);
    updatePasswordStrengthUI(strengthBar, strengthText, strength);

    return isValid;
  }

  function validateConfirmPassword() {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (confirmPassword.length === 0) {
      showError(confirmPasswordError, "Please confirm your password");
      return false;
    } else if (password !== confirmPassword) {
      showError(confirmPasswordError, "Password do not match");
      return false;
    }

    clearError(confirmPasswordError);
    return true;
  }

  function validateTerms() {
    return validateTermsCheckbox(termsCheckbox, termsError);
  }
})();
