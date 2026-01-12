import { ProductModule } from "./modules/product.js";

(() => {
  // DOM element
  const productsGrid = document.getElementById("home-products-grid");

  // Early return if elements don't exist
  if (!productsGrid || typeof ProductModule === "undefined") return;

  // Get popular products and render
  const popularProducts = ProductModule.getPopularProducts(3);
  ProductModule.renderProducts(productsGrid, popularProducts);
})();
