/**
 * Raghushree — Product Detail Sticky On-Page Section Navigation
 */
(function () {
  'use strict';

  function initProductSubnav() {
    var nav = document.getElementById('product-section-nav');
    if (!nav) {
      return;
    }

    var links = Array.prototype.slice.call(nav.querySelectorAll('.product-subnav__link[href^="#"]'));
    if (!links.length) {
      return;
    }

    var sections = [];
    links.forEach(function (link) {
      var id = link.getAttribute('href').slice(1);
      var target = id ? document.getElementById(id) : null;
      if (target) {
        sections.push({ id: id, link: link, el: target });
        link.addEventListener('click', function () {
          setActiveLink(id);
        });
      }
    });

    if (!sections.length) {
      return;
    }

    function setActiveLink(activeId) {
      sections.forEach(function (item) {
        if (item.id === activeId) {
          item.link.classList.add('is-active');
          item.link.setAttribute('aria-current', 'true');
          // Keep active tab visible horizontally inside mobile scroll bar without vertical jump
          var list = nav.querySelector('.product-subnav__list');
          if (list && list.scrollWidth > list.clientWidth) {
            var targetLeft = item.link.offsetLeft - (list.clientWidth / 2) + (item.link.offsetWidth / 2);
            list.scrollTo({ left: Math.max(0, targetLeft), behavior: 'smooth' });
          }
        } else {
          item.link.classList.remove('is-active');
          item.link.removeAttribute('aria-current');
        }
      });
    }

    var ticking = false;
    function onScrollUpdate() {
      var headerH = document.querySelector('.site-header') ? document.querySelector('.site-header').offsetHeight : 84;
      var navH = nav.offsetHeight || 54;
      var offset = headerH + navH + 48;
      var currentId = sections[0].id;

      sections.forEach(function (item) {
        var rect = item.el.getBoundingClientRect();
        if (rect.top <= offset) {
          currentId = item.id;
        }
      });

      setActiveLink(currentId);
      ticking = false;
    }

    window.addEventListener(
      'scroll',
      function () {
        if (!ticking) {
          window.requestAnimationFrame(onScrollUpdate);
          ticking = true;
        }
      },
      { passive: true }
    );

    onScrollUpdate();
  }

  // Keep long introductions readable on short screens without nested scrollbars.
  function initDetailHeadings() {
    var headings = Array.prototype.slice.call(document.querySelectorAll('.product-detail-heading'));
    if (!headings.length) return;

    function update() {
      headings.forEach(function (heading) {
        var top = parseFloat(window.getComputedStyle(heading).top) || 0;
        var tooTall = heading.getBoundingClientRect().height + top + 20 > window.innerHeight;
        heading.classList.toggle('is-too-tall', tooTall);
      });
    }

    update();
    window.addEventListener('resize', update, { passive: true });
    if (window.ResizeObserver) {
      var observer = new ResizeObserver(update);
      headings.forEach(function (heading) { observer.observe(heading); });
    }
  }

  function init() {
    initProductSubnav();
    initDetailHeadings();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
