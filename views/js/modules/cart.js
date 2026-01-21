const API_ENDPOINT = "/controllers/shop/CartController.php";
let cartCount = 0;

function init() {
  loadCartCount(function (count) {
    cartCount = count;
    updateCartBadge();
  });

  window.addEventListener("cartUpdated", handleCartUpdate);
}

function loadCartCount(callback) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", API_ENDPOINT, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    const count = parseInt(xhr.responseText) || 0;
    if (callback) {
      callback(count);
    }
  };

  xhr.onerror = function () {
    console.error("Failed to load cart count");
    if (callback) callback(0);
  };

  xhr.send("action=count");
}

function addItem(productId, quantity, onSuccess, onError) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", API_ENDPOINT, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      const responseText = xhr.responseText.trim();

      if (responseText === "STOCK_EXCEEDED") {
        showNotification("error", "Out of Stock", "Not enough stock available");
        if (onError) onError(new Error("Stock exceeded"));
        return;
      }

      const count = parseInt(responseText) || 0;
      cartCount = count;
      updateCartBadge();
      showNotification(
        "success",
        "Added to cart",
        "Item successfully added to your cart",
      );
      dispatchCartEvent();
      if (onSuccess) onSuccess({ count: count });
    } else {
      showNotification("error", "Error", "Failed to add item to cart");
      if (onError) onError(new Error("HTTP error: " + xhr.status));
    }
  };

  xhr.onerror = function () {
    console.error("Failed to add to cart");
    showNotification("error", "Error", "Failed to add item to cart");
    if (onError) onError(new Error("Network error"));
  };

  const params = "action=add&product_id=" + productId + "&quantity=" + quantity;
  xhr.send(params);
}

function updateQuantity(productId, quantity, onSuccess, onError) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", API_ENDPOINT, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      const responseText = xhr.responseText.trim();

      // Check for stock exceeded error
      if (responseText === "STOCK_EXCEEDED") {
        showNotification("error", "Out of Stock", "Not enough stock available");
        if (onError) onError(new Error("Stock exceeded"));
        return;
      }

      const count = parseInt(responseText) || 0;
      cartCount = count;
      updateCartBadge();
      showNotification("success", "Updated", "Cart quantity updated");
      dispatchCartEvent();
      if (onSuccess) onSuccess({ count: count });
    } else {
      showNotification("error", "Error", "Failed to update quantity");
      if (onError) onError(new Error("HTTP error: " + xhr.status));
    }
  };

  xhr.onerror = function () {
    console.error("Failed to update quantity");
    showNotification("error", "Error", "Failed to update quantity");
    if (onError) onError(new Error("Network error"));
  };

  const params =
    "action=update&product_id=" + productId + "&quantity=" + quantity;
  xhr.send(params);
}

/**
 * Remove item from cart
 */
function removeItem(productId, onSuccess, onError) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", API_ENDPOINT, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      const count = parseInt(xhr.responseText) || 0;
      cartCount = count;
      updateCartBadge();
      showNotification("success", "Removed", "Item removed from cart");
      dispatchCartEvent();
      if (onSuccess) onSuccess({ count: count });
    } else {
      showNotification("error", "Error", "Failed to remove item");
      if (onError) onError(new Error("HTTP error: " + xhr.status));
    }
  };

  xhr.onerror = function () {
    console.error("Failed to remove item");
    showNotification("error", "Error", "Failed to remove item");
    if (onError) onError(new Error("Network error"));
  };

  const params = "action=remove&product_id=" + productId;
  xhr.send(params);
}

function clearCart(onSuccess, onError) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", API_ENDPOINT, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      cartCount = 0;
      updateCartBadge();
      showNotification(
        "success",
        "Cart cleared",
        "All items removed from cart",
      );
      dispatchCartEvent();
      if (onSuccess) onSuccess();
    } else {
      showNotification("error", "Error", "Failed to clear cart");
      if (onError) onError(new Error("HTTP error: " + xhr.status));
    }
  };

  xhr.onerror = function () {
    console.error("Failed to clear cart");
    showNotification("error", "Error", "Failed to clear cart");
    if (onError) onError(new Error("Network error"));
  };

  xhr.send("action=clear");
}

function getItems() {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", API_ENDPOINT, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    const responseText = xhr.responseText.trim();
    const items = [];

    if (responseText && responseText !== "0") {
      const parts = responseText.split(",");
      for (let i = 0; i < parts.length; i++) {
        const itemParts = parts[i].split(":");
        if (itemParts.length === 2) {
          items.push({
            id: parseInt(itemParts[0]),
            quantity: parseInt(itemParts[1]),
          });
        }
      }
    }
  };

  xhr.send("action=get");
}

function getCount() {
  return cartCount;
}

// UI functions
function updateCartBadge() {
  const badge = document.getElementById("cart-badge");
  if (!badge) {
    console.warn("Cart badge element not found");
    return;
  }

  const count = getCount();
  console.log("Updating cart badge:", { count: count });

  if (count > 0) {
    badge.textContent = count;
    badge.style.display = "flex";
  } else {
    badge.style.display = "none";
  }
}

function showNotification(type, title, message) {
  let container = document.querySelector(".notification-container");
  if (!container) {
    container = document.createElement("div");
    container.className = "notification-container";
    document.body.appendChild(container);
  }

  const notification = document.createElement("div");
  notification.className = "notification notification-" + type;

  const icons = {
    success: "check_circle",
    error: "error",
    info: "info",
    warning: "warning",
  };

  notification.innerHTML = `
    <span class="material-symbols-outlined">${icons[type] || "info"}</span>
    <div class="notification-content">
      <strong>${title}</strong>
      <p>${message}</p>
    </div>`;

  container.appendChild(notification);

  setTimeout(function () {
    notification.classList.add("show");
  }, 10);

  setTimeout(function () {
    notification.classList.remove("show");
    setTimeout(function () {
      notification.remove();

      if (container.children.length === 0) {
        container.remove();
      }
    }, 300);
  }, 3000);
}

// Event handlers
function handleCartUpdate() {
  loadCartCount((count) => {
    cartCount = count;
    updateCartBadge();
  });
}

function dispatchCartEvent() {
  const event = new CustomEvent("cartUpdated", {
    detail: { count: cartCount },
  });
  window.dispatchEvent(event);
}

export const CartModule = {
  init,
  addItem,
  updateQuantity,
  removeItem,
  clearCart,
  getItems,
  getCount,
  updateCartBadge,
};

document.addEventListener("DOMContentLoaded", function () {
  init();
});
