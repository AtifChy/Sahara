(() => {
  const scriptElement = document.currentScript;
  const rootUrl = new URL("../..", scriptElement.src);
  const headerUrl = new URL("src/components/header.html", rootUrl);

  document.addEventListener("DOMContentLoaded", async () => {
    const container = document.getElementById("header");

    if (!container) return;

    const res = await fetch(headerUrl);
    const html = await res.text();

    const doc = new DOMParser().parseFromString(html, "text/html");
    const template = doc.querySelector("#header-template");

    const fragment = template.content.cloneNode(true);
    fragment.querySelectorAll("a[href]").forEach((a) => {
      const orig = a.getAttribute("href").replace(/^\//, "");
      a.setAttribute("href", new URL(orig, rootUrl));
    });

    const currentFile = window.location.pathname.split("/").pop();
    fragment.querySelectorAll("nav a[href]").forEach((a) => {
      const targetFile = new URL(a.getAttribute("href")).pathname
        .split("/")
        .pop();
      if (targetFile === currentFile) {
        a.setAttribute("aria-current", "page");
      } else {
        a.removeAttribute("aria-current");
      }
    });

    container.appendChild(fragment);

    window.initSearch?.();
  });
})();
