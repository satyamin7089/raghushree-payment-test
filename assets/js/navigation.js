/**
 * Raghushree — Accessible Global Header, Mega Menu & Mobile Navigation
 */
(function () {
  'use strict';

  function initNavigation() {
    var header = document.querySelector('.site-header');
    var megaTrigger = document.querySelector('[data-mega-trigger]');
    var megaMenu = document.getElementById('products-mega-menu');
    var megaWrapper = document.querySelector('[data-mega-wrapper]');
    var mobileToggle = document.querySelector('[data-mobile-toggle]');
    var mobileDrawer = document.getElementById('mobile-nav-drawer');
    var mobileProductsBtn = document.querySelector('[data-mobile-products-toggle]');
    var mobileProductsSub = document.getElementById('mobile-products-sublist');
    var closeTimer = null;

    // 1. Sticky Header Scroll Elevation
    if (header) {
      var onScroll = function () {
        if (window.scrollY > 12) {
          header.classList.add('is-scrolled');
        } else {
          header.classList.remove('is-scrolled');
        }
      };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    }

    var lastHoverOpenTime = 0;

    // 2. Desktop Product Mega Menu (Click, Touch, Hover Intent, Keyboard & Escape)
    function openMegaMenu(fromHover) {
      if (!megaTrigger || !megaMenu) {
        return;
      }
      var wasClosed = megaTrigger.getAttribute('aria-expanded') !== 'true';
      window.clearTimeout(closeTimer);
      megaTrigger.setAttribute('aria-expanded', 'true');
      megaMenu.classList.add('is-open');
      megaMenu.setAttribute('aria-hidden', 'false');
      if (fromHover && wasClosed) {
        lastHoverOpenTime = Date.now();
      }
    }

    function closeMegaMenu(returnFocus) {
      if (!megaTrigger || !megaMenu) {
        return;
      }
      window.clearTimeout(closeTimer);
      lastHoverOpenTime = 0;
      megaTrigger.setAttribute('aria-expanded', 'false');
      megaMenu.classList.remove('is-open');
      megaMenu.setAttribute('aria-hidden', 'true');
      if (returnFocus) {
        megaTrigger.focus();
      }
    }

    if (megaTrigger && megaMenu) {
      megaTrigger.addEventListener('click', function (e) {
        e.preventDefault();
        var expanded = megaTrigger.getAttribute('aria-expanded') === 'true';
        var justOpenedByHover = expanded && (Date.now() - lastHoverOpenTime < 450);

        if (justOpenedByHover) {
          lastHoverOpenTime = 0;
          openMegaMenu(false);
        } else if (expanded) {
          closeMegaMenu(false);
        } else {
          openMegaMenu(false);
        }
      });

      // Fine-pointer hover intent on desktop only (touch always uses click)
      var bindHover = function (el) {
        if (!el) {
          return;
        }
        el.addEventListener('pointerenter', function (e) {
          if (e.pointerType === 'mouse' && window.innerWidth >= 900) {
            openMegaMenu(true);
          }
        });
        el.addEventListener('pointerleave', function (e) {
          if (e.pointerType === 'mouse' && window.innerWidth >= 900) {
            closeTimer = window.setTimeout(function () {
              closeMegaMenu(false);
            }, 180);
          }
        });
      };

      bindHover(megaWrapper);
      bindHover(megaMenu);

      // Close when keyboard focus leaves trigger + mega menu
      document.addEventListener('focusin', function (e) {
        if (megaMenu.classList.contains('is-open')) {
          var insideTrigger = megaWrapper && megaWrapper.contains(e.target);
          var insideMenu = megaMenu.contains(e.target);
          if (!insideTrigger && !insideMenu) {
            closeMegaMenu(false);
          }
        }
      });

      // Close on outside click
      document.addEventListener('click', function (e) {
        if (!megaMenu.classList.contains('is-open')) {
          return;
        }
        var insideTrigger = megaWrapper && megaWrapper.contains(e.target);
        var insideMenu = megaMenu.contains(e.target);
        if (!insideTrigger && !insideMenu) {
          closeMegaMenu(false);
        }
      });
    }

    // 3. Mobile Hamburger Drawer & Touch Submenu Accordion
    function closeMobileDrawer(returnFocus) {
      if (!mobileToggle || !mobileDrawer) {
        return;
      }
      mobileToggle.setAttribute('aria-expanded', 'false');
      mobileToggle.setAttribute('aria-label', 'Open main menu');
      mobileDrawer.setAttribute('hidden', '');
      if (returnFocus) {
        mobileToggle.focus();
      }
    }

    if (mobileToggle && mobileDrawer) {
      mobileToggle.addEventListener('click', function () {
        var expanded = mobileToggle.getAttribute('aria-expanded') === 'true';
        if (expanded) {
          closeMobileDrawer(false);
        } else {
          mobileToggle.setAttribute('aria-expanded', 'true');
          mobileToggle.setAttribute('aria-label', 'Close main menu');
          mobileDrawer.removeAttribute('hidden');
        }
      });
    }

    if (mobileProductsBtn && mobileProductsSub) {
      mobileProductsBtn.addEventListener('click', function (e) {
        e.preventDefault();
        var expanded = mobileProductsBtn.getAttribute('aria-expanded') === 'true';
        mobileProductsBtn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        if (expanded) {
          mobileProductsSub.setAttribute('hidden', '');
        } else {
          mobileProductsSub.removeAttribute('hidden');
        }
      });
    }

    // Escape key closes active mega menu or mobile drawer
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (megaMenu && megaMenu.classList.contains('is-open')) {
          closeMegaMenu(true);
        }
        if (mobileDrawer && !mobileDrawer.hasAttribute('hidden')) {
          closeMobileDrawer(true);
        }
      }
    });

    // Reset mobile drawer state when resizing to desktop
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 900 && mobileDrawer && !mobileDrawer.hasAttribute('hidden')) {
        closeMobileDrawer(false);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavigation);
  } else {
    initNavigation();
  }
})();
