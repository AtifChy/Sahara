export function showError(errorElement, message) {
  if (errorElement) {
    errorElement.textContent = message;
    errorElement.parentElement?.classList.add("has-error");
  }
}

export function clearError(errorElement) {
  if (errorElement) {
    errorElement.textContent = "";
    errorElement.parentElement?.classList.remove("has-error");
  }
}

export function calculatePasswordStrength(password) {
  let strength = 0;

  // Length
  if (password.length >= 8) strength += 1;
  if (password.length >= 12) strength += 1;

  // Character variety
  if (/[a-z]/.test(password)) strength += 1;
  if (/[A-Z]/.test(password)) strength += 1;
  if (/[0-9]/.test(password)) strength += 1;
  if (/[^A-Za-z0-9]/.test(password)) strength += 1; // Special chars

  return Math.min(strength, 4); // Max 4
}

export function updatePasswordStrengthUI(strengthBar, strengthText, strength) {
  const strengthLevels = ["weak", "fair", "good", "strong"];
  const strengthLabels = ["Weak", "Fair", "Good", "Strong"];
  const strengthWidths = ["25%", "50%", "75%", "100%"];

  if (!strengthBar || !strengthText) return;

  if (strength === 0) {
    strengthBar.style.width = "0%";
    strengthText.textContent = "Password strength";
    strengthBar.className = "strength-fill";
    return;
  }

  const level = strengthLevels[strength - 1];
  strengthBar.style.width = strengthWidths[strength - 1];
  strengthBar.className = "strength-fill strength-" + level;
  strengthText.textContent = strengthLabels[strength - 1];
}

export function validateEmailField(input, errorElement) {
  const value = input.value.trim();

  if (value.length === 0) {
    showError(errorElement, "Email is required");
    return false;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(value)) {
    showError(errorElement, "Invalid email address");
    return false;
  }

  clearError(errorElement);
  return true;
}

export function validatePasswordField(
  input,
  errorElement,
  minLength = 8,
  requireStrength = false,
) {
  const value = input.value;

  if (value.length === 0) {
    showError(errorElement, "Password is required");
    return false;
  }

  if (value.length < minLength) {
    showError(
      errorElement,
      `Password must be at least ${minLength} characters`,
    );
    return false;
  }

  if (requireStrength) {
    if (!/[A-Za-z]/.test(value)) {
      showError(errorElement, "Password must contain at least one letter");
      return false;
    }
    if (!/[0-9]/.test(value)) {
      showError(errorElement, "Password must contain at least one number");
      return false;
    }
  }

  clearError(errorElement);
  return true;
}

export function validatePhoneField(input, errorElement, required = true) {
  const value = input.value.trim();

  if (value.length === 0) {
    if (required) {
      showError(errorElement, "Phone number is required");
      return false;
    }
    clearError(errorElement);
    return true;
  }

  const digits = phone.replace(/\D/g, "");
  if (digits.length < 10 || digits.length > 15) {
    showError(errorElement, "Invalid phone number (10-15 digits)");
    return false;
  }

  clearError(errorElement);
  return true;
}

export function validateNameField(
  input,
  errorElement,
  minLength = 2,
  maxLength = 100,
) {
  const value = input.value.trim();

  if (value.length === 0) {
    showError(errorElement, "Name is required");
    return false;
  }

  if (value.length < minLength || value.length > maxLength) {
    showError(
      errorElement,
      `Name must be ${minLength}-${maxLength} characters`,
    );
    return false;
  }

  clearError(errorElement);
  return true;
}

export function validateGenderSelect(select, errorElement) {
  const value = select.value;

  if (value === "") {
    showError(errorElement, "Please select your gender");
    return false;
  }

  const genderOptions = ["male", "female", "other"];
  if (!genderOptions.includes(value)) {
    showError(errorElement, "Invalid gender selection");
    return false;
  }

  clearError(errorElement);
  return true;
}

export function validateTermsCheckbox(checkbox, errorElement) {
  if (!checkbox.checked) {
    showError(errorElement, "You must agree to the terms");
    return false;
  }
  clearError(errorElement);
  return true;
}

export function setupPasswordToggles() {
  const toggleButtons = document.querySelectorAll(".toggle-password");

  toggleButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();
      const targetId = button.getAttribute("data-target");
      const targetInput = document.getElementById(targetId);
      const icon = button.querySelector(".material-symbols-outlined");

      if (!targetInput || !icon) return;

      if (targetInput.type === "password") {
        targetInput.type = "text";
        icon.textContent = "visibility_off";
      } else {
        targetInput.type = "password";
        icon.textContent = "visibility";
      }
    });
  });
}
