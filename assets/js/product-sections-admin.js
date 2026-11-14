(() => {
  function init(editor) {
    if (editor.dataset.ready) return; editor.dataset.ready = '1';
    editor.addEventListener('click', (event) => {
      const add = event.target.closest('[data-section-add]');
      const remove = event.target.closest('[data-section-remove]');
      if (remove && editor.contains(remove)) { const row = remove.closest('[data-section-row]'); const fieldset = row.closest('fieldset'); if (fieldset.querySelectorAll('[data-section-row]').length === 1) row.querySelectorAll('input').forEach((input) => { input.value = ''; }); else row.remove(); fieldset.querySelector('[data-section-add]').focus(); fieldset.dispatchEvent(new Event('change', { bubbles: true })); return; }
      if (!add || !editor.contains(add)) return;
      const fieldset = add.closest('fieldset'); const rows = fieldset.querySelector('[data-section-rows]'); const template = rows.querySelector('[data-section-row]');
      if (!template) return;
      const index = Number(add.dataset.nextIndex); if (!Number.isSafeInteger(index) || index >= 10000 || rows.children.length >= 50) return;
      add.dataset.nextIndex = String(index + 1);
      const row = template.cloneNode(true); row.querySelectorAll('input').forEach((input) => { input.value = ''; input.name = input.name.replace(/\]\[\d+\]\[/, `][${index}][`); }); rows.appendChild(row); row.querySelector('input').focus();
    });
  }
  function start() { document.querySelectorAll('.rg-section-editor').forEach(init); }
  function observe() { start(); if (document.body && window.MutationObserver) new MutationObserver(start).observe(document.body, { childList: true, subtree: true }); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', observe, { once: true }); else observe();
})();
