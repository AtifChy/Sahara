function getFooterUrl() {
  return "/partials/footer.html";
}

async function injectFooter() {
  const mount = document.getElementById("footer");
  if (!mount) return;

  try {
    const res = await fetch(getFooterUrl());
    if (!res.ok) throw new Error("Failed to fetch footer: " + res.statusText);

    const html = await res.text();
    mount.innerHTML = html;
  } catch (err) {
    console.error(err);
  }
}

// document.addEventListener("DOMContentLoaded", injectFooter);
injectFooter();
