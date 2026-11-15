/**
 * Raghushree — Homepage Banner Carousel
 *
 * Two stacked banners rotate every 12 seconds. Controls are the previous/next
 * arrow buttons only (no dots). The rotation pauses while the pointer or the
 * keyboard focus is inside the banner, restarts after a manual change, respects
 * prefers-reduced-motion, and leaves the first banner visible without JavaScript.
 */
(function () {
  'use strict';

  var DELAY = 12000;

  function initHeroCarousel() {
    var hero = document.querySelector('[data-home-hero]');
    if (!hero) return;

    var slides = Array.prototype.slice.call(hero.querySelectorAll('[data-home-hero-slide]'));
    if (slides.length < 2) return;

    var prevBtn = hero.querySelector('[data-home-hero-prev]');
    var nextBtn = hero.querySelector('[data-home-hero-next]');
    var total = slides.length;
    var index = 0;
    var timer = null;
    var paused = false;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    hero.classList.add('home-hero--enhanced');

    function show(next) {
      index = ((next % total) + total) % total;

      slides.forEach(function (slide, slideIndex) {
        var isActive = slideIndex === index;
        slide.classList.toggle('is-active', isActive);
        slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        if ('inert' in slide) {
          slide.inert = !isActive;
        }
      });
    }

    function stop() {
      if (timer !== null) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function start() {
      stop();
      if (reduceMotion) return;
      timer = window.setInterval(function () {
        if (!paused && !document.hidden) {
          show(index + 1);
        }
      }, DELAY);
    }

    function go(next) {
      show(next);
      start();
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        go(index + 1);
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        go(index - 1);
      });
    }

    hero.addEventListener('keydown', function (event) {
      if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
      if (event.target === prevBtn || event.target === nextBtn) return;
      event.preventDefault();
      go(event.key === 'ArrowRight' ? index + 1 : index - 1);
    });

    hero.addEventListener('mouseenter', function () {
      paused = true;
    });

    hero.addEventListener('mouseleave', function () {
      paused = false;
    });

    hero.addEventListener('focusin', function () {
      paused = true;
    });

    hero.addEventListener('focusout', function (event) {
      if (!hero.contains(event.relatedTarget)) {
        paused = false;
      }
    });

    show(0);
    start();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeroCarousel);
  } else {
    initHeroCarousel();
  }
})();
