(() => {
  const accountBtn = document.getElementById("account-btn");
  const accountMenu = document.getElementById("account-menu");

  if (!accountBtn || !accountMenu) return;

  let isOpen = false;

  const toggleMenu = (open) => {
    isOpen = open;
    accountBtn.setAttribute("aria-expanded", String(open));
    accountMenu.setAttribute("aria-hidden", String(!open));
  };

  accountBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    toggleMenu(!isOpen);
  });

  document.addEventListener("click", (e) => {
    if (
      isOpen &&
      !accountMenu.contains(e.target) &&
      !accountBtn.contains(e.target)
    ) {
      toggleMenu(false);
    }
  });

  accountMenu.addEventListener("click", (e) => {
    e.stopPropagation();
  });
})();
