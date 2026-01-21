import { CartModule } from "./cart.js";

function fetchProducts(filters = {}, onSuccess, onError) {
  const xhr = new XMLHttpRequest();

  const params = new URLSearchParams();
  if (filters.category) params.append("category", filters.category);
  if (filters.sort) params.append("sort", filters.sort);
  if (filters.priceRange) params.append("priceRange", filters.priceRange);
  if (filters.search) params.append("search", filters.search);

  const queryString = params.toString();
  const url = `/controllers/shop/ProductsController.php${queryString ? "?" + queryString : ""}`;

  xhr.open("GET", url, true);

  xhr.onload = function () {
    if (xhr.status === 200) {
      try {
        const data = JSON.parse(xhr.responseText);
        if (data.success) {
          onSuccess(data.products);
        } else {
          onError(new Error(data.message || "Failed to fetch products"));
        }
      } catch (e) {
        onError(new Error("Invalid response format"));
      }
    } else {
      onError(new Error("Failed to fetch products. Status: " + xhr.status));
    }
  };

  xhr.onerror = function () {
    onError(new Error("Network error occurred"));
  };

  xhr.send();
}

function getImagePath(imageName) {
  if (imageName && imageName.startsWith("/")) {
    return imageName;
  }
}

function renderStars(rating) {
  const max = 5;
  const fullStars = Math.floor(rating);
  const halfStars = rating % 1 >= 0.5;
  const stars = [];

  for (let i = 0; i < max; i++) {
    let state = "empty";
    if (i < fullStars) {
      state = "full";
    } else if (i === fullStars && halfStars) {
      state = "half";
    }
    stars.push(`<span class="star ${state}">★</span>`);
  }

  return stars.join("");
}

// Create a product card element
function createProductCard(product) {
  const card = document.createElement("div");
  card.className = "product-card";

  const newBadge = product.isNew ? '<span class="new-badge">New</span>' : "";
  const starsHtml = renderStars(product.rating);
  let imagePath = getImagePath(product.image);
  if (!imagePath) {
    imagePath = "/views/assets/product_placeholder.svg";
  }

  card.innerHTML = `
      ${newBadge}
      <button class="wishlist" aria-label="Add to wishlist">
        <span class="material-symbols-outlined">favorite_border</span>
      </button>

      <div class="product-image">
        <img src="${imagePath}" alt="${product.title}" />
      </div>

      <h3>${product.title}</h3>
      <p class="desc">${product.description}</p>

      <div class="product-rating">
        <div class="rating-stars" aria-hidden="true">
          ${starsHtml}
        </div>
        <span class="rating-value">${product.rating.toFixed(1)}</span>
      </div>

      <div class="product-info">
        <span class="price">৳${product.price.toFixed(2)}</span>
        <button class="add-to-cart" aria-label="Add to cart">Add to cart</button>
      </div>
    `;

  // Card click to view product details
  card.addEventListener("click", (e) => {
    if (e.target.closest(".wishlist") || e.target.closest(".add-to-cart")) {
      return;
    }
    window.location.href = `/product_details.php?id=${product.id}`;
  });

  // Wishlist toggle
  const wishlistBtn = card.querySelector(".wishlist");
  wishlistBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    wishlistBtn.classList.toggle("active");
  });

  // Add to cart
  const addToCartBtn = card.querySelector(".add-to-cart");
  addToCartBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    CartModule.addItem(product.id, 1);
  });

  return card;
}

function renderProducts(container, productsToRender) {
  if (!container) return;

  container.innerHTML = "";

  productsToRender.forEach((product) => {
    const card = createProductCard(product);
    container.appendChild(card);
  });
}

export const ProductModule = {
  fetchProducts,
  getImagePath,
  renderStars,
  createProductCard,
  renderProducts,
};
