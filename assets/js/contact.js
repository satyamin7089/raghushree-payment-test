/**
 * Raghushree — Contact & Industrial RFQ Form Progressive Validation
 */
(function () {
  'use strict';

  function initContactForm() {
    var form = document.getElementById('raghushree-enquiry-form');
    var notice = document.querySelector('.form-notice');

    if (notice) {
      notice.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    if (!form) {
      return;
    }

    form.setAttribute('novalidate', 'novalidate');

    var clearFieldError = function (input) {
      var wrapper = input.closest('.form-field');
      if (!wrapper) {
        return;
      }
      wrapper.classList.remove('form-field--error');
      input.removeAttribute('aria-invalid');
      if (input.id && input.getAttribute('aria-describedby') === 'error-' + input.id) {
        input.removeAttribute('aria-describedby');
      }
      var err = wrapper.querySelector('.form-error[data-client-error]');
      if (err) {
        err.remove();
      }
    };

    var setFieldError = function (input, message) {
      var wrapper = input.closest('.form-field');
      if (!wrapper) {
        return;
      }
      clearFieldError(input);
      wrapper.classList.add('form-field--error');
      input.setAttribute('aria-invalid', 'true');
      var err = document.createElement('span');
      err.className = 'form-error';
      if (input.id) {
        err.id = 'error-' + input.id;
        input.setAttribute('aria-describedby', err.id);
      }
      err.setAttribute('data-client-error', 'true');
      err.setAttribute('role', 'alert');
      err.textContent = message;
      wrapper.appendChild(err);
    };

    form.querySelectorAll('input, select, textarea').forEach(function (field) {
      field.addEventListener('input', function () {
        clearFieldError(field);
      });
      field.addEventListener('change', function () {
        clearFieldError(field);
      });
    });

    form.addEventListener('submit', function (e) {
      var firstInvalid = null;
      var requiredFields = form.querySelectorAll('[required]');

      requiredFields.forEach(function (field) {
        var val = (field.value || '').trim();
        if (!val) {
          setFieldError(field, 'Please complete this required field.');
          if (!firstInvalid) {
            firstInvalid = field;
          }
          return;
        }

        if (field.type === 'email') {
          var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
          if (!emailOk) {
            setFieldError(field, 'Please enter a valid business email address.');
            if (!firstInvalid) {
              firstInvalid = field;
            }
          }
        }
      });

      if (firstInvalid) {
        e.preventDefault();
        firstInvalid.focus();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initContactForm);
  } else {
    initContactForm();
  }
})();
