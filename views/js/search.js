(() => {
  const header = document.querySelector("header");
  const toggleBtn = document.getElementById("search-toggle");
  const searchForm = document.getElementById("search-form");
  const searchInput = document.getElementById("search-input");

  if (!header || !toggleBtn || !searchForm || !searchInput) return;

  const params = new URLSearchParams(window.location.search);
  const qParam = (params.get("q") || "").trim();
  const stored = localStorage.getItem("searchOpen");

  let isOpen =
    stored === "true" ||
    !!qParam ||
    searchForm.getAttribute("aria-hidden") === "false";

  function applyState() {
    header.classList.toggle("search-active", isOpen);
    searchForm.setAttribute("aria-hidden", String(!isOpen));
    // toggleBtn.setAttribute("aria-hidden", String(isOpen));
    if (isOpen) {
      setTimeout(() => searchInput.focus(), 200);
    }
  }

  applyState();

  if (qParam && searchInput) {
    searchInput.value = qParam;
  }

  const toggleSearch = (open) => {
    if (isOpen === open) return;
    isOpen = open;
    localStorage.setItem("searchOpen", String(isOpen));
    applyState();
  };

  toggleBtn.addEventListener("click", () => toggleSearch(true));

  document.addEventListener("click", (event) => {
    if (
      !searchForm.contains(event.target) &&
      !toggleBtn.contains(event.target) &&
      !qParam
    ) {
      toggleSearch(false);
      localStorage.setItem("searchOpen", "false");
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isOpen) {
      if (searchInput.value) {
        searchInput.value = "";
        window.dispatchEvent(new CustomEvent("searchCleared"));
      } else {
        toggleSearch(false);
        searchInput.blur();
      }
    }
  });
})();

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("search-form");
  const input = document.getElementById("search-input");
  if (!form || !input) return;

  form.addEventListener("submit", (ev) => {
    ev.preventDefault();
    const target = "/shop.php";
    const query = input.value.trim();

    if (!query) {
      localStorage.setItem("searchOpen", "true");
      window.location.href = target;
      return;
    }

    localStorage.setItem("searchOpen", "true");
    window.location.href = `${target}?q=${encodeURIComponent(query)}`;
  });
});
