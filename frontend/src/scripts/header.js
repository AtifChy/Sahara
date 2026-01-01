const scriptElement = document.currentScript;
const rootUrl = new URL("../..", scriptElement.src);

const navLinks = [
  { href: "index.html", label: "Home" },
  { href: "src/pages/shop.html", label: "Shop" },
  { href: "src/pages/about.html", label: "About" },
  { href: "src/pages/contact.html", label: "Contact" },
];

document.addEventListener("DOMContentLoaded", async () => {
  const container = document.getElementById("header");

  if (!container) return;

  const navMarkup = navLinks
    .map((item) => {
      const fullUrl = new URL(item.href, rootUrl);
      return `<a href="${fullUrl}">${item.label}</a>`;
    })
    .join("");

  container.innerHTML = `
      <header>
        <h1 class="logo">
          <a href="${new URL("index.html", rootUrl)}">Sahara</a>
        </h1>

        <nav>
          ${navMarkup}
          <button class="seller-btn">Seller</button>
        </nav>

        <div class="header-right">
          <button
            class="icon-btn"
            type="button"
            id="search-toggle"
            aria-hidden="false"
            aria-controls="search-form"
          >
            <span class="material-icons-outlined">search</span>
          </button>

          <form class="search-form" id="search-form" aria-hidden="true">
            <input
              id="search-input"
              type="search"
              placeholder="Search products..."
            />
          </form>

          <button class="icon-btn" type="button" id="shopping-cart">
            <span class="material-icons-outlined">shopping_cart</span>
          </button>
        </div>
      </header>
    `;

  const currrentPage = window.location.pathname.split("/").pop() ?? "";
  container.querySelectorAll("nav a[href]").forEach((link) => {
    const linkPage = link.getAttribute("href")?.split("/").pop() ?? "";
    if (linkPage === currrentPage) {
      link.setAttribute("aria-current", "page");
    } else {
      link.removeAttribute("aria-current");
    }
  });

  initSearch();
});
