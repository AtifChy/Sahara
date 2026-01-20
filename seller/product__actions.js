function viewProduct(id) {
  window.location.href = `/seller/product__view.php?id=${id}`;
}

function editProduct(id) {
  window.location.href = `/seller/product__edit.php?id=${id}`;
}

function deleteProduct(id, title) {
  const $confirm = confirm(
    `Are you sure you want to delete the product "${title}"? This action cannot be undone.`,
  );
  if (!$confirm) return;

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "/seller/product__delete.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      const data = JSON.parse(xhr.responseText);
      if (data.success) {
        alert(data.message);
        window.location.href = "/seller.php?page=products&status=deleted";
      } else {
        alert(`Error: ${data.message}`);
      }
    }
  };

  xhr.onerror = function () {
    alert("Request failed. Please try again.");
  };

  xhr.send(`id=${encodeURIComponent(id)}`);
}
