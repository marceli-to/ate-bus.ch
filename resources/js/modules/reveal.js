/**
 * Reveal on Scroll using ScrollReveal
 * Usage: Add data-reveal attribute to any element
 * Use data-reveal="fade" for fade-only animation
 */

import ScrollReveal from 'scrollreveal';

export function initReveal() {
  // Default: fade + slide up
  ScrollReveal().reveal('[data-reveal]:not([data-reveal="fade"])', {
    distance: '100px',
    origin: 'bottom',
    duration: 600,
    easing: 'ease-out',
    viewFactor: 0.1,
  });

  // Fade only
  ScrollReveal().reveal('[data-reveal="fade"]', {
    distance: '0',
    duration: 600,
    delay: 300,
    easing: 'ease-out',
    viewFactor: 0.1,
  });
}
