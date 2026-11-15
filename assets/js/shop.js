/**
 * Raghushree — Consumer Shop Sections Controller
 *
 * 1. Centre-focused product coverflow: the active slide is sharp and centred,
 *    the neighbouring slides are positioned behind it and blurred by CSS.
 * 2. Controls: previous/next arrows, dot tabs, keyboard arrows, touch swipe and
 *    click-a-neighbour-to-centre. The centre "+" link opens the product page.
 *
 * Progressive enhancement only: without JavaScript the first slide stays
 * centred and the link to its product page keeps working.
 */
(function () {
  'use strict';

  function initCoverflows() {
    var sections = Array.prototype.slice.call(document.querySelectorAll('[data-shopflow]'));

    sections.forEach(function (section) {
      var viewport = section.querySelector('[data-shopflow-viewport]');
      var slides = Array.prototype.slice.call(section.querySelectorAll('[data-shopflow-slide]'));
      var dots = Array.prototype.slice.call(section.querySelectorAll('[data-shopflow-dot]'));
      var prevBtn = section.querySelector('[data-shopflow-prev]');
      var nextBtn = section.querySelector('[data-shopflow-next]');

      if (!viewport || slides.length < 2) return;

      var total = slides.length;
      var activeIndex = 0;
      var touchStartX = null;
      var touchStartY = null;

      function setSlideState(slide, isActive) {
        slide.classList.toggle('is-active', isActive);
        slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        var link = slide.querySelector('a');
        if (link) {
          if (isActive) {
            link.removeAttribute('tabindex');
          } else {
            link.setAttribute('tabindex', '-1');
          }
        }
      }

      // Circular distance so the slider never runs out of slides on either side:
      // the last product always sits to the left of the first one and vice versa.
      function circularOffset(index) {
        var raw = (index - activeIndex + total) % total;
        return raw > total / 2 ? raw - total : raw;
      }

      function layout() {
        slides.forEach(function (slide, index) {
          var offset = circularOffset(index);
          var bounded = Math.max(-4, Math.min(4, offset));
          slide.setAttribute('data-offset', String(bounded));
          setSlideState(slide, 0 === offset);
        });

        dots.forEach(function (dot, index) {
          var isActive = index === activeIndex;
          dot.classList.toggle('is-active', isActive);
          dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
      }

      function goTo(index) {
        var next = ((index % total) + total) % total;
        if (next === activeIndex) return;
        activeIndex = next;
        layout();
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          goTo(activeIndex - 1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          goTo(activeIndex + 1);
        });
      }

      dots.forEach(function (dot, index) {
        dot.addEventListener('click', function () {
          goTo(index);
        });
      });

      slides.forEach(function (slide, index) {
        var link = slide.querySelector('a');
        if (!link) return;
        link.addEventListener('click', function (event) {
          if (index === activeIndex) return;
          // A blurred neighbour is a navigation target for the slider, not a page.
          event.preventDefault();
          goTo(index);
        });
      });

      section.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
          event.preventDefault();
          goTo(activeIndex - 1);
        } else if (event.key === 'ArrowRight') {
          event.preventDefault();
          goTo(activeIndex + 1);
        }
      });

      viewport.addEventListener('touchstart', function (event) {
        var touch = event.changedTouches[0];
        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
      }, { passive: true });

      viewport.addEventListener('touchend', function (event) {
        if (touchStartX === null) return;
        var touch = event.changedTouches[0];
        var deltaX = touch.clientX - touchStartX;
        var deltaY = touch.clientY - touchStartY;
        touchStartX = null;
        touchStartY = null;
        if (Math.abs(deltaX) < 40 || Math.abs(deltaX) < Math.abs(deltaY)) return;
        goTo(deltaX < 0 ? activeIndex + 1 : activeIndex - 1);
      }, { passive: true });

      layout();
      section.setAttribute('data-shopflow-ready', 'true');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCoverflows);
  } else {
    initCoverflows();
  }
})();
