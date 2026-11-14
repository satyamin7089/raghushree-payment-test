(() => {
  function init() {
    const container = document.querySelector('.raghushree-courier-pickers');
    if (!container) return;
    const product = container.querySelector('#sr-product-picker');
    const pack = container.querySelector('#sr-pack-picker');
    const productId = document.querySelector('#sr-product-id');
    const option = document.querySelector('#sr-option');
    if (!product || !pack || !productId || !option) return;
    container.hidden = false;
    product.addEventListener('change', () => {
      productId.value = product.value; option.value = ''; pack.replaceChildren();
      const initial = document.createElement('option'); initial.value = ''; initial.textContent = 'Select a pack'; pack.appendChild(initial);
      let options = [];
      try { options = JSON.parse(product.selectedOptions[0]?.dataset.packs || '[]'); } catch (_) { /* Keep manual entry available. */ }
      if (!Array.isArray(options)) return;
      options.forEach((item) => { const el = document.createElement('option'); el.value = item.key; el.textContent = item.label; pack.appendChild(el); });
    });
    pack.addEventListener('change', () => { option.value = pack.value; });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
