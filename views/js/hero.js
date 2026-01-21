(() => {
  const slides = [
    {
      offer: "Limited Time Offer 30% off",
      title: "Experience Pure Sound - Your Perfect Headphones Awaits!",
      image: "hero-image.jpg",
      button: "Buy now",
    },
    {
      offer: "Exclusive Deal - 20% off",
      title: "Upgrade Your Listening Experience!",
      image: "hero-image2.jpg",
      button: "Shop now",
    },
    {
      offer: "Special Discount - 15% off",
      title: "Enhance Your Audio Experience!",
      image: "hero-image3.jpg",
      button: "Explore now",
    },
  ];

  const slider = document.querySelector(".hero-slider");
  const dots = Array.from(document.querySelectorAll(".dots .dot"));

  if (!slider || dots.length === 0) return;

  slides.forEach((slide) => {
    const content = document.createElement("div");
    content.className = "hero-content";

    content.innerHTML = `
      <div class="hero-text">
        <div class="offer">${slide.offer}</div>
        <h1 id="hero-title">${slide.title}</h1>
      </div>
      <div class="hero-buttons">
        <button class="buy-btn">${slide.button}</button>
        <a href="#" class="more">
          Find more
          <span class="material-symbols-outlined">arrow_forward</span>
        </a>
      </div>
    `;

    slider.appendChild(content);
  });

  let current = 0;

  const setSlider = (index) => {
    current = (index + slides.length) % slides.length;
    slider.style.transform = `translateX(-${current * 100}%)`;

    dots.forEach((dot, i) => {
      dot.classList.toggle("active", i === current);
    });
  };

  slider.addEventListener("wheel", (e) => {
    e.preventDefault();
    if (e.deltaY > 0) {
      setSlider(current + 1);
    } else {
      setSlider(current - 1);
    }
  });

  dots.forEach((dot, i) => {
    dot.addEventListener("click", () => setSlider(i));
  });

  setSlider(current);
})();
