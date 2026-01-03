document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".newsletter-form");
  if (!form) return;

  const email = document.getElementById("email");
  const error = document.getElementById("email-error");

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    if (!email.value) {
      showError("Email is required.");
    } else if (!emailRegex.test(email.value)) {
      showError("Please enter a valid email address.");
    } else {
      clearError();
      console.log("Submitted email:", email.value);
    }
  });

  function showError(message) {
    error.textContent = "* " + message;
    error.style.display = "block";
  }

  function clearError() {
    error.textContent = "";
    error.style.display = "none";
  }
})();
