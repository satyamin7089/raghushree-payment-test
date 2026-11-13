/**
 * Raghushree — Global UI Interactions (FAQ Accordions & Subtle Scroll Reveal)
 */
(function () {
  'use strict';

  var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 1. Accessible FAQ Accordion
  function initFaqAccordions() {
    var triggers = document.querySelectorAll('.faq-trigger[aria-controls]');
    if (!triggers.length) {
      return;
    }

    triggers.forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        var isExpanded = trigger.getAttribute('aria-expanded') === 'true';
        var panelId = trigger.getAttribute('aria-controls');
        var panel = panelId ? document.getElementById(panelId) : null;
        var item = trigger.closest('.faq-item');

        if (!panel) {
          return;
        }

        trigger.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
        if (isExpanded) {
          panel.setAttribute('hidden', '');
          if (item) {
            item.classList.remove('is-open');
          }
        } else {
          panel.removeAttribute('hidden');
          if (item) {
            item.classList.add('is-open');
          }
        }
      });
    });
  }

  // 2. Subtle Progressive Scroll Reveal
  function initScrollReveal() {
    var revealEls = document.querySelectorAll('.reveal');
    if (!revealEls.length) {
      return;
    }

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
      revealEls.forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    document.documentElement.classList.add('js-ready');

    var observer = new IntersectionObserver(
      function (entries, obs) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            obs.unobserve(entry.target);
          }
        });
      },
      {
        rootMargin: '0px 0px -6% 0px',
        threshold: 0.08
      }
    );

    revealEls.forEach(function (el) {
      observer.observe(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initFaqAccordions();
      initScrollReveal();
    });
  } else {
    initFaqAccordions();
    initScrollReveal();
  }
})();
