export const ProductModule = (() => {
  const products = [
    {
      id: 1,
      title: "Apple Pro 2nd Gen",
      description: "Apple Airpods Pro (2nd Gen) with Magsafe",
      price: 399.99,
      category: "electronics",
      image: "apple_earphone_image.png",
      rating: 4.8,
      isNew: true,
    },
    {
      id: 2,
      title: "Premium Wireless Headphones",
      description: "High-quality sound with noise cancellation",
      price: 249.99,
      category: "electronics",
      image: "apple_earphone_image.png",
      rating: 4.6,
      isNew: false,
    },
    {
      id: 3,
      title: "Classic Cotton T-Shirt",
      description: "Comfortable and durable everyday wear",
      price: 29.99,
      category: "fashion",
      image: "apple_earphone_image.png",
      rating: 4.4,
      isNew: false,
    },
    {
      id: 4,
      title: "Leather Crossbody Bag",
      description: "Stylish and spacious leather messenger bag",
      price: 159.99,
      category: "fashion",
      image: "apple_earphone_image.png",
      rating: 4.7,
      isNew: true,
    },
    {
      id: 5,
      title: "Stainless Steel Watch",
      description: "Elegant timepiece with premium materials",
      price: 299.99,
      category: "accessories",
      image: "apple_earphone_image.png",
      rating: 4.9,
      isNew: false,
    },
    {
      id: 6,
      title: "Designer Sunglasses",
      description: "UV protection with fashionable frame",
      price: 189.99,
      category: "accessories",
      image: "apple_earphone_image.png",
      rating: 4.5,
      isNew: true,
    },
    {
      id: 7,
      title: "Smart Home Speaker",
      description: "Voice-controlled speaker with premium sound",
      price: 129.99,
      category: "electronics",
      image: "apple_earphone_image.png",
      rating: 4.3,
      isNew: false,
    },
    {
      id: 8,
      title: "Portable Phone Charger",
      description: "Fast charging power bank for all devices",
      price: 49.99,
      category: "accessories",
      image: "apple_earphone_image.png",
      rating: 4.6,
      isNew: false,
    },
    {
      id: 9,
      title: "Organic Bedding Set",
      description: "Soft and eco-friendly bed sheets collection",
      price: 89.99,
      category: "home",
      image: "apple_earphone_image.png",
      rating: 4.7,
      isNew: true,
    },
    {
      id: 10,
      title: "Smart LED Lighting Kit",
      description: "Customizable RGB lights with app control",
      price: 79.99,
      category: "home",
      image: "apple_earphone_image.png",
      rating: 4.5,
      isNew: false,
    },
    {
      id: 11,
      title: "Graphic Hoodie",
      description: "Trendy streetwear with high-quality print",
      price: 69.99,
      category: "fashion",
      image: "apple_earphone_image.png",
      rating: 4.4,
      isNew: false,
    },
    {
      id: 12,
      title: "Minimalist Desk Lamp",
      description: "Modern lamp with adjustable brightness",
      price: 59.99,
      category: "home",
      image: "apple_earphone_image.png",
      rating: 4.2,
      isNew: false,
    },
  ];

  function getImagePath(imageName) {
    const path = window.location.pathname;
    const isInSubdir = path.includes("/pages/");
    return isInSubdir ? `../assets/${imageName}` : `assets/${imageName}`;
  }

  function getAllProducts() {
    return products;
  }

  function getProductsByCategory(category) {
    return products.filter((p) => p.category === category);
  }

  function getPopularProducts(limit = 3) {
    return products.filter((p) => p.isNew).slice(0, limit);
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
    const imagePath = getImagePath(product.image);

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
        <span class="price">$${product.price.toFixed(2)}</span>
        <button class="add-to-cart" aria-label="Add to cart">Add to cart</button>
      </div>
    `;

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
      console.log(`Added "${product.title}" to cart`);
      // TODO: Implement actual cart functionality
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

  function searchProducts(query) {
    const lowerQuery = query.toLowerCase();
    return products.filter(
      (p) =>
        p.title.toLowerCase().includes(lowerQuery) ||
        p.description.toLowerCase().includes(lowerQuery),
    );
  }

  function getProductById(id) {
    return products.find((p) => p.id === id) || null;
  }

  return {
    getAllProducts,
    getProductsByCategory,
    getPopularProducts,
    createProductCard,
    renderProducts,
    renderStars,
    searchProducts,
    getProductById,
    getImagePath,
  };
})();
