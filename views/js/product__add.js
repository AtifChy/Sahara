import { clearError, showError } from "./auth.js";

(() => {
  const form = document.getElementById("product-form");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    const isTitleValid = validateTitle();
    const isPriceValid = validatePrice();
    const isCategoryValid = validateCategory();
    const isStockValid = validateStock();

    if (!isTitleValid || !isPriceValid || !isCategoryValid || !isStockValid) {
      e.preventDefault();
      if (!isTitleValid) {
        titleInput.focus();
      } else if (!isPriceValid) {
        priceInput.focus();
      } else if (!isCategoryValid) {
        categorySelect.focus();
      } else {
        stockInput.focus();
      }
    }
  });

  const imageInput = document.getElementById("image-input");
  const imagePreview = document.getElementById("image-preview");
  const imageUploadArea = document.getElementById("image-upload-area");

  imageUploadArea.addEventListener("click", () => {
    imageInput.click();
  });

  imageInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
      previewImage(file);
    }
  });

  function previewImage(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.classList.add("uploaded");
      imagePreview.innerHTML = `
        <img src="${e.target.result}" alt="">
        <p>
          <span class="material-symbols-outlined">check_circle</span>
          Image selected: ${file.name}
        </p>
        <small>Click to change the image</small>
      `;
    };
    reader.readAsDataURL(file);
  }

  const titleInput = document.getElementById("title");
  const titleError = document.getElementById("title-error");

  titleInput.addEventListener("input", validateTitle);
  titleInput.addEventListener("blur", validateTitle);

  function validateTitle() {
    const input = titleInput.value.trim();

    if (!input) {
      showError(titleError, "Title is required");
      return false;
    }

    if (input.length < 3 || input.length > 100) {
      showError(titleError, "Title must be between 3 and 100 characters");
      return false;
    }

    clearError(titleError);
    return true;
  }

  const priceInput = document.getElementById("price");
  const priceError = document.getElementById("price-error");

  priceInput.addEventListener("input", validatePrice);
  priceInput.addEventListener("blur", validatePrice);

  function validatePrice() {
    const value = priceInput.value.trim();

    if (!value) {
      showError(priceError, "Price is required");
      return false;
    }

    const price = parseFloat(value);
    if (isNaN(price) || price <= 0) {
      showError(priceError, "Price must be a positive number");
      return false;
    }

    clearError(priceError);
    return true;
  }

  const categorySelect = document.getElementById("category");
  const categoryError = document.getElementById("category-error");

  categorySelect.addEventListener("change", validateCategory);
  categorySelect.addEventListener("blur", validateCategory);

  function validateCategory() {
    const value = categorySelect.value;
    if (!value) {
      showError(categoryError, "Please select a category");
      return false;
    }
    clearError(categoryError);
    return true;
  }

  const stockInput = document.getElementById("stock");
  const stockError = document.getElementById("stock-error");

  stockInput.addEventListener("input", validateStock);
  stockInput.addEventListener("blur", validateStock);

  function validateStock() {
    const value = stockInput.value.trim();
    if (!value) {
      showError(stockError, "Stock quantity is required");
      return false;
    }
    const stock = parseInt(value);
    if (isNaN(stock) || stock < 0) {
      showError(stockError, "Stock must be a non-negative integer");
      return false;
    }
    clearError(stockError);
    return true;
  }
})();
