import { showError, clearError, validatePasswordField } from "./auth.js";

(() => {
  const passwordInput = document.getElementById("new_password");
  const passwordError = document.getElementById("new-password-error");

  const confirmPasswordInput = document.getElementById("confirm_password");
  const confirmPasswordError = document.getElementById(
    "confirm-password-error",
  );

  passwordInput.addEventListener("blur", validatePassword);
  passwordInput.addEventListener("input", () => {
    validatePassword();
    if (confirmPasswordInput.value) {
      validateConfirmPassword();
    }
  });

  confirmPasswordInput.addEventListener("blur", validateConfirmPassword);
  confirmPasswordInput.addEventListener("input", validateConfirmPassword);

  function validatePassword() {
    return validatePasswordField(passwordInput, passwordError, 8, true);
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

  const pictureInput = document.getElementById("profile_picture");
  pictureInput.addEventListener("change", previewImage);

  function previewImage(event) {
    const file = event.target.files[0];
    const fileName = document.getElementById("fileName");
    const uploadBtn = document.getElementById("uploadBtn");

    if (file) {
      fileName.textContent = file.name;
      uploadBtn.disabled = false;

      const reader = new FileReader();
      reader.onload = function (e) {
        const avatarPreview = document.querySelector(".avatar-preview");
        const existingImg = document.getElementById("avatarImage");
        const existingIcon = document.getElementById("avatarIcon");

        if (existingImg) {
          existingImg.src = e.target.result;
        } else if (existingIcon) {
          existingIcon.remove();
          const img = document.createElement("img");
          img.id = "avatarImage";
          img.src = e.target.result;
          img.alt = "Profile Picture";
          avatarPreview.appendChild(img);
        }
      };
      reader.readAsDataURL(file);
    } else {
      fileName.textContent = "";
      uploadBtn.disabled = true;
    }
  }

  const passwordToggleSelector = document.querySelectorAll(".password-toggle");
  passwordToggleSelector.forEach((toggle) => {
    toggle.addEventListener("click", () => {
      const passwordInput = toggle.previousElementSibling;
      const icon = toggle.querySelector(".material-symbols-outlined");
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.textContent = "visibility_off";
      } else {
        passwordInput.type = "password";
        icon.textContent = "visibility";
      }
    });
  });
})();
