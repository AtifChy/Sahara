import { initSearch } from "./search.js";

function getHeaderUrl() {
  return "/partials/header.html";
}

async function injectHeader() {
  const mount = document.getElementById("header");
  if (!mount) return;

  try {
    const res = await fetch(getHeaderUrl());
    if (!res.ok) throw new Error("Failed to fetch header: " + res.statusText);

    const html = await res.text();
    mount.innerHTML = html;

    highlightActiveLink(mount);

    initSearch();
  } catch (err) {
    console.error(err);
  }
}

function highlightActiveLink(scope) {
  const currentPath = window.location.pathname;
  const links = scope.querySelectorAll("nav a[href]");
  links.forEach((link) => {
    const linkPath = new URL(link.href).pathname;
    if (linkPath === currentPath) {
      link.setAttribute("aria-current", "page");
    }
  });
}

// document.addEventListener("DOMContentLoaded", injectHeader);
injectHeader();
