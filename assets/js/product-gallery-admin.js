(() => {
  function init(editor) {
    if (editor.dataset.ready || !window.wp || !wp.media) return;
    const input = editor.querySelector('#raghushree_gallery_ids');
    const preview = editor.querySelector('[data-gallery-preview]');
    const add = editor.querySelector('[data-gallery-add]');
    const fallback = editor.querySelector('[data-gallery-fallback]');
    if (!input || !preview || !add || !fallback) return;
    let items;
    try { items = JSON.parse(editor.dataset.galleryItems || '[]'); } catch (_) { return; }
    if (!Array.isArray(items)) return;
    editor.dataset.ready = '1';
    fallback.hidden = true; add.hidden = false;
    function render(notify) {
      input.value = items.map((item) => item.id).join(', ');
      if (notify) input.dispatchEvent(new Event('change', { bubbles: true }));
      preview.replaceChildren();
      items.forEach((item, index) => {
        const tile = document.createElement('div'); tile.className = 'rg-gallery-item';
        if (item.url) { const image = document.createElement('img'); image.src = item.url; image.alt = ''; tile.appendChild(image); }
        else { const missing = document.createElement('p'); missing.textContent = 'Image unavailable'; tile.appendChild(missing); }
        const actions = document.createElement('div'); actions.className = 'rg-gallery-actions';
        [['left', 'Move earlier', index === 0], ['right', 'Move later', index === items.length - 1], ['remove', 'Remove', false]].forEach(([action, label, disabled]) => {
          const button = document.createElement('button'); button.type = 'button'; button.className = 'button'; button.textContent = label; button.disabled = disabled;
          button.setAttribute('aria-label', label + ' image ' + (index + 1));
          button.addEventListener('click', () => {
            if (action === 'remove') items.splice(index, 1);
            else { const next = index + (action === 'left' ? -1 : 1); [items[index], items[next]] = [items[next], items[index]]; }
            render(true);
            const target = preview.querySelectorAll('.rg-gallery-item')[Math.min(index, items.length - 1)];
            if (target) target.querySelector('button:not(:disabled)').focus(); else add.focus();
          });
          actions.appendChild(button);
        });
        tile.appendChild(actions); preview.appendChild(tile);
      });
    }
    add.addEventListener('click', () => {
      const frame = wp.media({ title: 'Choose product images', button: { text: 'Add images' }, library: { type: 'image' }, multiple: true });
      frame.on('select', () => {
        frame.state().get('selection').each((attachment) => {
          const image = attachment.toJSON(); const id = Number(image.id);
          if (!id || items.some((item) => Number(item.id) === id)) return;
          items.push({ id, url: image.sizes?.thumbnail?.url || image.url || '' });
        });
        render(true);
      });
      frame.open();
    });
    render();
  }
  function start() { document.querySelectorAll('.rg-gallery-editor').forEach(init); }
  function observe() { start(); if (document.body && window.MutationObserver) new MutationObserver(start).observe(document.body, { childList: true, subtree: true }); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', observe, { once: true }); else observe();
})();
