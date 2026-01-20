import { Product } from "./product.js";

(() => {
  // DOM element
  const productsGrid = document.getElementById("home-products-grid");

  // Early return if elements don't exist
  if (!productsGrid || typeof Product === "undefined") return;

  // Get popular products and render
  Product.fetchProducts(
    { sort: "popular" },
    (products) => {
      const popularProducts = products.slice(0, 5);
      Product.renderProducts(productsGrid, popularProducts);
    },
    (error) => {
      console.error("Error fetching popular products:", error);
    },
  );
})();
