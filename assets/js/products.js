/**
 * Raghushree — Products Archive Dynamic Search & Taxonomy Filtering
 * Enhances server-side GET filtering with instant accessible UI updates.
 */
(function () {
  'use strict';

  function initProductCatalogFilters() {
    var form = document.querySelector('[data-products-filter-form]');
    var cards = Array.prototype.slice.call(document.querySelectorAll('[data-product-card]'));
    var pills = Array.prototype.slice.call(document.querySelectorAll('[data-category-pill]'));
    var countEl = document.querySelector('[data-products-count]');
    var emptyEl = document.querySelector('[data-products-empty]');
    var resetBtns = document.querySelectorAll('[data-products-reset]');

    if (!form || !cards.length) {
      return;
    }

    var searchInput = form.querySelector('[name="product_q"]');
    var catSelect = form.querySelector('[name="polymer_cat"]');
    var gradeSelect = form.querySelector('[name="material_grade"]');
    var formSelect = form.querySelector('[name="material_form"]');

    function applyFilters(syncUrl) {
      var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
      var cat = catSelect ? catSelect.value : '';
      var grade = gradeSelect ? gradeSelect.value : '';
      var matForm = formSelect ? formSelect.value : '';
      var visibleCount = 0;

      cards.forEach(function (card) {
        var cardText = (card.getAttribute('data-search-text') || '').toLowerCase();
        var cardCats = (card.getAttribute('data-categories') || '').split(',');
        var cardGrades = (card.getAttribute('data-grades') || '').split(',');
        var cardForms = (card.getAttribute('data-forms') || '').split(',');

        var matchQuery = !query || cardText.indexOf(query) !== -1;
        var matchCat = !cat || cardCats.indexOf(cat) !== -1;
        var matchGrade = !grade || cardGrades.indexOf(grade) !== -1;
        var matchForm = !matForm || cardForms.indexOf(matForm) !== -1;

        if (matchQuery && matchCat && matchGrade && matchForm) {
          card.removeAttribute('hidden');
          visibleCount += 1;
        } else {
          card.setAttribute('hidden', '');
        }
      });

      pills.forEach(function (pill) {
        var pillCat = pill.getAttribute('data-category-pill') || '';
        if (pillCat === cat) {
          pill.classList.add('is-active');
          pill.setAttribute('aria-pressed', 'true');
        } else {
          pill.classList.remove('is-active');
          pill.setAttribute('aria-pressed', 'false');
        }
      });

      if (countEl) {
        countEl.textContent = visibleCount + (visibleCount === 1 ? ' polymer record shown' : ' polymer records shown');
      }

      if (emptyEl) {
        if (visibleCount === 0) {
          emptyEl.removeAttribute('hidden');
        } else {
          emptyEl.setAttribute('hidden', '');
        }
      }

      if (syncUrl && window.history && window.history.replaceState) {
        var params = new URLSearchParams();
        if (query) {
          params.set('product_q', searchInput.value.trim());
        }
        if (cat) {
          params.set('polymer_cat', cat);
        }
        if (grade) {
          params.set('material_grade', grade);
        }
        if (matForm) {
          params.set('material_form', matForm);
        }
        var qs = params.toString();
        var nextUrl = window.location.pathname + (qs ? '?' + qs : '');
        window.history.replaceState(null, '', nextUrl);
      }
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      applyFilters(true);
    });

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        applyFilters(true);
      });
    }

    [catSelect, gradeSelect, formSelect].forEach(function (sel) {
      if (sel) {
        sel.addEventListener('change', function () {
          applyFilters(true);
        });
      }
    });

    pills.forEach(function (pill) {
      pill.addEventListener('click', function (e) {
        e.preventDefault();
        var targetCat = pill.getAttribute('data-category-pill') || '';
        if (catSelect) {
          catSelect.value = targetCat;
        }
        applyFilters(true);
      });
    });

    resetBtns.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (searchInput) {
          searchInput.value = '';
        }
        if (catSelect) {
          catSelect.value = '';
        }
        if (gradeSelect) {
          gradeSelect.value = '';
        }
        if (formSelect) {
          formSelect.value = '';
        }
        applyFilters(true);
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductCatalogFilters);
  } else {
    initProductCatalogFilters();
  }
})();
