initSearch = () => {
  const toggleBtn = document.getElementById("search-toggle");
  const searchForm = document.getElementById("search-form");
  const searchInput = document.getElementById("search-input");

  if (!toggleBtn || !searchForm) return;

  let isOpen = searchForm.getAttribute("aria-hidden") === "false";

  const toggleSearch = (open) => {
    if (isOpen === open) return;

    isOpen = open;
    searchForm.setAttribute("aria-hidden", String(!open));
    toggleBtn.setAttribute("aria-hidden", String(open));

    if (open) {
      requestAnimationFrame(() => searchInput?.focus());
    }
  };

  toggleBtn.addEventListener("click", () => toggleSearch(true));

  document.addEventListener("click", (event) => {
    if (
      !searchForm.contains(event.target) &&
      !toggleBtn.contains(event.target)
    ) {
      toggleSearch(false);
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isOpen) {
      toggleSearch(false);
    }
  });
};
