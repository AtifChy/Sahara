import { CartModule } from "./modules/cart.js";

(() => {
  const cartItems = document.querySelectorAll(".cart-item");
  const clearCartBtn = document.querySelector(".btn-clear-cart");
  const checkoutBtn = document.querySelector(".btn-checkout");

  // Handle quantity decrease buttons
  document.querySelectorAll(".qty-decrease").forEach((btn) => {
    btn.addEventListener("click", () => {
      const productId = btn.dataset.productId;
      const input = document.querySelector(
        `.qty-input[data-product-id="${productId}"]`,
      );
      const currentQty = parseInt(input.value);

      if (currentQty > 1) {
        updateQuantity(productId, currentQty - 1);
      }
    });
  });

  // Handle quantity increase buttons
  document.querySelectorAll(".qty-increase").forEach((btn) => {
    btn.addEventListener("click", () => {
      const productId = btn.dataset.productId;
      const input = document.querySelector(
        `.qty-input[data-product-id="${productId}"]`,
      );
      const currentQty = parseInt(input.value);
      const maxQty = parseInt(input.max);

      if (currentQty < maxQty) {
        updateQuantity(productId, currentQty + 1);
      }
    });
  });

  // Handle direct input change
  document.querySelectorAll(".qty-input").forEach((input) => {
    input.addEventListener("change", () => {
      const productId = input.dataset.productId;
      let newQty = parseInt(input.value);
      const maxQty = parseInt(input.max);
      const minQty = parseInt(input.min) || 1;

      // Validate
      if (isNaN(newQty) || newQty < minQty) {
        newQty = minQty;
      }
      if (newQty > maxQty) {
        newQty = maxQty;
      }

      input.value = newQty;
      updateQuantity(productId, newQty);
    });
  });

  // Handle remove buttons
  document.querySelectorAll(".item-remove").forEach((btn) => {
    btn.addEventListener("click", () => {
      const productId = btn.dataset.productId;
      const item = btn.closest(".cart-item");

      if (confirm("Remove this item from cart?")) {
        // Fade out animation
        item.style.opacity = "0.5";
        btn.disabled = true;

        CartModule.removeItem(productId, () => {
          // Reload page to update totals
          window.location.reload();
        });
      }
    });
  });

  // Handle clear cart button
  if (clearCartBtn) {
    clearCartBtn.addEventListener("click", () => {
      if (confirm("Clear all items from cart?")) {
        CartModule.clearCart(() => {
          window.location.reload();
        });
      }
    });
  }

  // Handle checkout button
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", () => {
      window.location.href = "/checkout.php";
    });
  }

  function updateQuantity(productId, quantity) {
    CartModule.updateQuantity(
      productId,
      quantity,
      () => {
        window.location.reload();
      },
      (error) => {
        console.error("Failed to update quantity:", error);
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      },
    );
  }
})();
