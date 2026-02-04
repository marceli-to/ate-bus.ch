/**
 * Reveal on Scroll
 * Adds fade-in + slide-up animation when elements enter viewport
 * Usage: Add data-reveal attribute to any element
 */

export function initReveal() {
  const elements = document.querySelectorAll('[data-reveal]');
  
  if (!elements.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px',
    }
  );

  elements.forEach((el) => {
    el.classList.add('reveal-ready');
    observer.observe(el);
  });
}
