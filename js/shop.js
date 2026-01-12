import { ProductModule } from "./modules/product.js";

(() => {
  const categoryBtns = document.querySelectorAll(".category-btn");
  const sortSelect = document.getElementById("sort-select");
  const priceSelect = document.getElementById("price-select");
  const productsGrid = document.getElementById("products-grid");
  const noProducts = document.getElementById("no-products");

  // State
  let currentCategory = "all";
  let currentSort = "newest";
  let currentPriceRange = "all";

  // read query param
  const params = new URLSearchParams(window.location.search);
  const initialQuery = (params.get("q") || "").trim();

  // Early return if elements don't exist
  if (
    !productsGrid ||
    !categoryBtns.length ||
    typeof ProductModule === "undefined"
  )
    return;

  // Initialize
  filterAndRender();
  attachEventListeners();

  function attachEventListeners() {
    // Category filter
    categoryBtns.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        categoryBtns.forEach((b) => b.classList.remove("active"));
        e.target.classList.add("active");
        currentCategory = e.target.dataset.category;
        filterAndRender();
      });
    });

    // Sort filter
    if (sortSelect) {
      sortSelect.addEventListener("change", (e) => {
        currentSort = e.target.value;
        filterAndRender();
      });
    }

    // Price filter
    if (priceSelect) {
      priceSelect.addEventListener("change", (e) => {
        currentPriceRange = e.target.value;
        filterAndRender();
      });
    }
  }

  function filterAndRender() {
    let filtered = ProductModule.getAllProducts();

    if (initialQuery) {
      filtered = ProductModule.searchProducts(initialQuery);
      const input = document.getElementById("search-input");
      if (input) input.value = initialQuery;
      localStorage.setItem("searchOpen", "true");
    }

    // Apply category filter
    if (currentCategory !== "all") {
      filtered = filtered.filter((p) => p.category === currentCategory);
    }

    // Apply price filter
    if (currentPriceRange !== "all") {
      filtered = filtered.filter((p) => {
        const price = p.price;
        switch (currentPriceRange) {
          case "0-100":
            return price < 100;
          case "100-500":
            return price >= 100 && price < 500;
          case "500-1000":
            return price >= 500 && price < 1000;
          case "1000":
            return price >= 1000;
          default:
            return true;
        }
      });
    }

    // Apply sorting
    filtered.sort((a, b) => {
      switch (currentSort) {
        case "price-low":
          return a.price - b.price;
        case "price-high":
          return b.price - a.price;
        case "popular":
          return b.rating - a.rating;
        case "rating":
          return b.rating - a.rating;
        case "newest":
        default:
          return b.isNew - a.isNew || b.id - a.id;
      }
    });

    renderFilteredProducts(filtered);
  }

  function renderFilteredProducts(productsToRender) {
    if (productsToRender.length === 0) {
      productsGrid.style.display = "none";
      noProducts.style.display = "block";
      return;
    }

    productsGrid.style.display = "grid";
    noProducts.style.display = "none";
    productsGrid.innerHTML = "";

    productsToRender.forEach((product) => {
      const card = ProductModule.createProductCard(product);
      productsGrid.appendChild(card);
    });
  }
})();
