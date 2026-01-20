import { Product } from "./product.js";

(() => {
  const categoryBtns = document.querySelectorAll(".category-btn");
  const sortSelect = document.getElementById("sort-select");
  const priceSelect = document.getElementById("price-select");
  const productsGrid = document.getElementById("products-grid");
  const noProducts = document.getElementById("no-products");

  let currentCategory = "all";
  let currentSort = "newest";
  let currentPriceRange = "all";

  if (!productsGrid || !categoryBtns.length || typeof Product === "undefined")
    return;

  // read search query param
  const params = new URLSearchParams(window.location.search);
  let initialQuery = (params.get("q") || "").trim();

  const input = document.getElementById("search-input");

  input.addEventListener("input", (e) => {
    initialQuery = e.target.value.trim();
    filterAndRender();
  });

  window.addEventListener("searchCleared", () => {
    initialQuery = "";
    input.value = "";
    filterAndRender();
  });

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
    // Build filters object for API
    const filters = {
      category: currentCategory !== "all" ? currentCategory : undefined,
      sort: currentSort,
      priceRange: currentPriceRange !== "all" ? currentPriceRange : undefined,
      search: initialQuery || undefined,
    };

    // Fetch products from API
    Product.fetchProducts(
      filters,
      (products) => {
        renderFilteredProducts(products);
      },
      (err) => {
        console.error("Error fetching products:", err);
      },
    );
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
      const card = Product.createProductCard(product);
      productsGrid.appendChild(card);
    });
  }
})();
