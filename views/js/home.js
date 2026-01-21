import { ProductModule } from "./modules/product.js";

(() => {
  const productsGrid = document.getElementById("home-products-grid");

  if (!productsGrid || typeof ProductModule === "undefined") return;

  // Get popular products and render
  ProductModule.fetchProducts(
    { sort: "popular" },
    (products) => {
      const popularProducts = products.slice(0, 5);
      ProductModule.renderProducts(productsGrid, popularProducts);
    },
    (error) => {
      console.error("Error fetching popular products:", error);
    },
  );
})();
