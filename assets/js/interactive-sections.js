/**
 * Raghushree Theme — Interactive Sections Controller:
 * 1. Five-Slide Scroll-Pinned Visual Product Story (GSAP + ScrollTrigger, Snapping,
 *    Buttons, Keyboard, Mobile Swipe & Reduced-Motion Support)
 * 2. Four-Card 180° Click/Tap/Keyboard Flip Cards ("Useful detail, on the other side.")
 * 3. Single-Product Banner Smooth Fade-In / Fade-Out Image Slider
 */
(function () {
  'use strict';

  document.documentElement.classList.add('js-ready');

  var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReduced) {
    document.documentElement.classList.add('reduced-motion');
  }

  function getHeaderOffset() {
    var hdr = document.querySelector('.site-header');
    if (!hdr) return 0;
    var cs = window.getComputedStyle(hdr);
    if (cs.position === 'sticky' || cs.position === 'fixed') {
      return Math.round(hdr.getBoundingClientRect().height);
    }
    return 0;
  }

  /* ==========================================================================
     1. Five-Slide Scroll-Pinned Visual Product Story
     ========================================================================== */
  function initVisualProductStories() {
    var sections = Array.prototype.slice.call(document.querySelectorAll('[data-visual-story]'));
    if (!sections.length) return;

    var hasGSAP = Boolean(window.gsap && window.ScrollTrigger);
    if (hasGSAP) {
      window.gsap.registerPlugin(window.ScrollTrigger);
    }

    sections.forEach(function (section) {
      var viewport = section.querySelector('[data-vps-viewport]');
      var track = section.querySelector('[data-vps-track]');
      var slides = Array.prototype.slice.call(section.querySelectorAll('[data-vps-slide]'));
      var stepBtns = Array.prototype.slice.call(section.querySelectorAll('[data-vps-goto]'));
      var prevBtn = section.querySelector('[data-vps-prev]');
      var nextBtn = section.querySelector('[data-vps-next]');
      var progressBar = section.querySelector('[data-vps-progress]');

      if (!viewport || !track || slides.length < 2) return;

      var total = slides.length;
      var activeIdx = 0;
      var stInstance = null;
      var mm = null;

      function updateUI(idx, rawProgress) {
        var clamped = Math.max(0, Math.min(total - 1, idx));
        activeIdx = clamped;

        slides.forEach(function (sl, i) {
          sl.classList.toggle('is-active', i === clamped);
        });

        stepBtns.forEach(function (btn, i) {
          var isAct = i === clamped;
          btn.classList.toggle('is-active', isAct);
          btn.setAttribute('aria-selected', isAct ? 'true' : 'false');
        });

        if (progressBar) {
          var pct = typeof rawProgress === 'number'
            ? Math.max(20, Math.min(100, 20 + rawProgress * 80))
            : Math.round(((clamped + 1) / total) * 100);
          progressBar.style.width = pct.toFixed(1) + '%';
        }
      }

      function goToSlide(targetIdx) {
        var nextIdx = Math.max(0, Math.min(total - 1, targetIdx));

        // If Desktop GSAP ScrollTrigger pin is active, scroll window to exact snap point
        if (stInstance && stInstance.isActive !== undefined && window.innerWidth >= 960 && !prefersReduced) {
          var ratio = nextIdx / (total - 1);
          var targetScrollY = stInstance.start + ratio * (stInstance.end - stInstance.start);
          window.scrollTo({
            top: targetScrollY,
            behavior: 'smooth'
          });
          updateUI(nextIdx, ratio);
          return;
        }

        // Mobile / Tablet / Reduced-Motion Horizontal Track Scroll
        var slideWidth = viewport.clientWidth || track.clientWidth;
        track.scrollTo({
          left: nextIdx * slideWidth,
          behavior: prefersReduced ? 'auto' : 'smooth'
        });
        updateUI(nextIdx);
      }

      // Sync UI on Mobile / Reduced-Motion Native Horizontal Swipe
      var scrollTimer = null;
      track.addEventListener(
        'scroll',
        function () {
          if (stInstance && window.innerWidth >= 960 && !prefersReduced) return;
          clearTimeout(scrollTimer);
          scrollTimer = setTimeout(function () {
            var w = viewport.clientWidth || 1;
            var idx = Math.round(track.scrollLeft / w);
            updateUI(idx);
          }, 40);
        },
        { passive: true }
      );

      // Button Navigation
      stepBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var idx = Number(btn.getAttribute('data-vps-goto') || 0);
          goToSlide(idx);
        });
      });

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          goToSlide(activeIdx - 1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          goToSlide(activeIdx + 1);
        });
      }

      // Keyboard Left / Right Navigation
      section.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') {
          e.preventDefault();
          goToSlide(activeIdx + 1);
        } else if (e.key === 'ArrowLeft') {
          e.preventDefault();
          goToSlide(activeIdx - 1);
        }
      });

      // Desktop Scroll-Pinned Horizontal Movement (GSAP MatchMedia >= 960px & !prefersReduced)
      if (hasGSAP && !prefersReduced) {
        mm = window.gsap.matchMedia();
        mm.add('(min-width: 960px)', function () {
          section.classList.add('is-pin-ready');
          track.scrollLeft = 0;

          var movePct = -100 * ((total - 1) / total); // -80% for 5 slides (width: 500%)
          var tween = window.gsap.to(track, {
            xPercent: movePct,
            ease: 'none',
            paused: true
          });

          stInstance = window.ScrollTrigger.create({
            trigger: section,
            pin: viewport,
            start: function () {
              return 'top ' + getHeaderOffset() + 'px';
            },
            end: function () {
              return '+=' + Math.round(window.innerHeight * 2.5);
            },
            scrub: 0.55,
            anticipatePin: 1,
            invalidateOnRefresh: true,
            animation: tween,
            snap: {
              snapTo: 1 / (total - 1),
              duration: { min: 0.14, max: 0.32 },
              delay: 0.04,
              ease: 'power2.out',
              directional: false
            },
            onUpdate: function (self) {
              var idx = Math.round(self.progress * (total - 1));
              updateUI(idx, self.progress);
            }
          });

          return function () {
            section.classList.remove('is-pin-ready');
            window.gsap.set(track, { clearProps: 'transform' });
            stInstance = null;
          };
        });
      }
    });
  }

  /* ==========================================================================
     2. Four-Card 180° Interactive Flip Section (Click / Tap / Keyboard Only)
     ========================================================================== */
  function initFlipCards() {
    var cards = Array.prototype.slice.call(document.querySelectorAll('[data-flip-card]'));
    if (!cards.length) return;

    cards.forEach(function (card) {
      var front = card.querySelector('[data-flip-front]');
      var back = card.querySelector('[data-flip-back]');
      var openBtn = card.querySelector('[data-flip-open]');
      var closeBtn = card.querySelector('[data-flip-close]');
      var frontTriggers = Array.prototype.slice.call(card.querySelectorAll('[data-flip-trigger-surface]'));
      var backFocusables = Array.prototype.slice.call(card.querySelectorAll('[data-flip-back-focusable]'));

      function setFlipped(state, moveFocus) {
        card.classList.toggle('is-flipped', state);
        if (openBtn) {
          openBtn.setAttribute('aria-expanded', state ? 'true' : 'false');
          openBtn.tabIndex = state ? -1 : 0;
        }
        if (front) front.setAttribute('aria-hidden', state ? 'true' : 'false');
        if (back) back.setAttribute('aria-hidden', state ? 'false' : 'true');

        backFocusables.forEach(function (el) {
          el.tabIndex = state ? 0 : -1;
        });

        if (moveFocus) {
          if (state && closeBtn) {
            closeBtn.focus();
          } else if (!state && openBtn) {
            openBtn.focus();
          }
        }
      }

      if (openBtn) {
        openBtn.addEventListener('click', function (e) {
          e.preventDefault();
          setFlipped(true, true);
        });
      }

      frontTriggers.forEach(function (surface) {
        surface.addEventListener('click', function () {
          if (!card.classList.contains('is-flipped')) {
            setFlipped(true, false);
          }
        });
      });

      if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          setFlipped(false, true);
        });
      }

      card.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && card.classList.contains('is-flipped')) {
          e.preventDefault();
          setFlipped(false, true);
        }
      });
    });
  }

  /* ==========================================================================
     3. Single-Product Banner Smooth Fade-In / Fade-Out Image Slider
     ========================================================================== */
  function initBannerFadeSliders() {
    var sliders = Array.prototype.slice.call(document.querySelectorAll('[data-fade-slider]'));
    if (!sliders.length) return;

    sliders.forEach(function (slider) {
      var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-fade-slide]'));
      var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-fade-dot]'));
      var prevBtn = slider.querySelector('[data-fade-prev]');
      var nextBtn = slider.querySelector('[data-fade-next]');
      var titleEl = slider.querySelector('[data-fade-title]');
      var subEl = slider.querySelector('[data-fade-sub]');

      if (slides.length < 2) return;

      var current = 0;
      var timer = null;
      var paused = false;

      function showSlide(idx) {
        current = ((idx % slides.length) + slides.length) % slides.length;
        slides.forEach(function (sl, i) {
          var active = i === current;
          sl.classList.toggle('is-active', active);
          sl.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        dots.forEach(function (d, i) {
          var active = i === current;
          d.classList.toggle('is-active', active);
          d.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        var curSlide = slides[current];
        if (curSlide && titleEl) {
          titleEl.textContent = curSlide.getAttribute('data-caption-title') || '';
        }
        if (curSlide && subEl) {
          subEl.textContent = curSlide.getAttribute('data-caption-sub') || '';
        }
      }

      function startAuto() {
        if (prefersReduced) return;
        stopAuto();
        timer = setInterval(function () {
          if (!paused) {
            showSlide(current + 1);
          }
        }, 4200);
      }

      function stopAuto() {
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          showSlide(current - 1);
          startAuto();
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          showSlide(current + 1);
          startAuto();
        });
      }

      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          showSlide(Number(dot.getAttribute('data-fade-dot') || 0));
          startAuto();
        });
      });

      slider.addEventListener('mouseenter', function () {
        paused = true;
      });
      slider.addEventListener('mouseleave', function () {
        paused = false;
      });
      slider.addEventListener('focusin', function () {
        paused = true;
      });
      slider.addEventListener('focusout', function () {
        paused = false;
      });

      startAuto();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initVisualProductStories();
      initFlipCards();
      initBannerFadeSliders();
    });
  } else {
    initVisualProductStories();
    initFlipCards();
    initBannerFadeSliders();
  }
})();
