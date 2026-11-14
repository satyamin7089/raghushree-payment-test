(() => {
  document.querySelectorAll('.product-quote').forEach((form) => {
    const result = form.querySelector('.product-quote__result');
    const button = form.querySelector('button[type="submit"]');
    const price = form.querySelector('[data-purchase-price]');
    const note = form.querySelector('[data-purchase-note]');
    const quantity = form.querySelector('[name="packet_quantity"]');
    const sample = form.querySelector('[data-sample]');
    let packs = {};
    try { packs = JSON.parse(form.dataset.packs || '{}'); } catch (_) { packs = {}; }
    const money = (paise) => '₹' + (paise / 100).toFixed(2);
    let revision = 0;
    function updateSelection() {
      if (quantity) {
        const n = Math.max(1, Math.min(999, parseInt(quantity.value, 10) || 1));
        quantity.value = n;
        form.elements.option.value = sample && sample.checked ? 'sample' : 'packet_' + n;
      }
      const pack = packs[form.elements.option.value];
      if (price) price.textContent = pack ? money(pack.price_paise) : 'Contact us';
      if (note) note.textContent = !pack ? 'This packet quantity is not configured for online purchase. Please contact us.' : (form.dataset.type === 'pipe' ? 'Over 30 kg? Contact us for a custom quote.' : '30 kg or more? Contact us for a custom quote.');
      return !!pack;
    }
    const minus = form.querySelector('[data-qty-minus]');
    const plus = form.querySelector('[data-qty-plus]');
    if (minus) minus.addEventListener('click', () => { quantity.value = Math.max(1, (parseInt(quantity.value, 10) || 1) - 1); quantity.dispatchEvent(new Event('input', { bubbles: true })); });
    if (plus) plus.addEventListener('click', () => { quantity.value = Math.min(999, (parseInt(quantity.value, 10) || 1) + 1); quantity.dispatchEvent(new Event('input', { bubbles: true })); });
    updateSelection();
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (!updateSelection()) { result.textContent = 'Please contact us for this quantity.'; return; }
      if (!form.reportValidity()) return;
      const requestRevision = ++revision;
      form.dispatchEvent(new CustomEvent('raghushree:quote-invalid'));
      button.disabled = true;
      result.textContent = 'Checking shipping…';
      const body = new URLSearchParams({ action: 'raghushree_shipping_quote', nonce: form.dataset.nonce, product_id: form.dataset.product, option: form.elements.option.value, pincode: form.elements.pincode.value });
      try {
        const response = await fetch(form.dataset.endpoint, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
        const data = await response.json();
        if (requestRevision !== revision) return;
        if (!data.success) { const message = data.data?.message || 'Shipping rates are temporarily unavailable.'; const ref = data.data?.reference; throw new Error(message + (typeof ref === 'string' && /^SR-[A-Z-]+$/.test(ref) ? ' Reference: ' + ref + '.' : '')); }
        const quote = data.data;
        result.textContent = `Estimated total ${money(quote.total_paise)} including GST and shipping${quote.courier_name ? ' (' + quote.courier_name + ')' : ''}.${quote.checkout_token ? ' Continue below.' : ' Checkout is not currently available.'}`;
        form.dispatchEvent(new CustomEvent('raghushree:quote', { detail: quote }));
      } catch (error) {
        if (requestRevision === revision) result.textContent = error.message || 'Shipping rates are temporarily unavailable.';
      } finally { button.disabled = false; }
    });
    function invalidate() { revision++; updateSelection(); result.textContent = ''; form.dispatchEvent(new CustomEvent('raghushree:quote-invalid')); }
    form.addEventListener('input', invalidate);
    form.addEventListener('change', invalidate);
  });
})();
