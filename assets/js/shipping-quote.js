(() => {
  document.querySelectorAll('.product-quote').forEach((form) => {
    const result = form.querySelector('.product-quote__result');
    const button = form.querySelector('button');
    let revision = 0;
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const requestRevision = ++revision;
      button.disabled = true;
      result.textContent = 'Checking shipping…';
      const body = new URLSearchParams({ action: 'raghushree_shipping_quote', nonce: form.dataset.nonce, product_id: form.dataset.product, option: form.elements.option.value, pincode: form.elements.pincode.value });
      try {
        const response = await fetch(form.dataset.endpoint, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
        const data = await response.json();
        if (requestRevision !== revision) return;
        if (!data.success) throw new Error(data.data?.message || 'Shipping rates are temporarily unavailable.');
        const money = (paise) => '₹' + (paise / 100).toFixed(2);
        const quote = data.data;
        result.textContent = `${quote.product_name} — ${quote.option_name}: ${money(quote.product_paise)} + estimated shipping ${money(quote.rate_paise)} = ${money(quote.product_paise + quote.rate_paise)}${quote.courier_name ? ' (' + quote.courier_name + ')' : ''}.${quote.checkout_token ? ' Continue below.' : ' Checkout is not currently available.'}`;
        form.dispatchEvent(new CustomEvent('raghushree:quote', { detail: quote }));
      } catch (error) {
        if (requestRevision === revision) result.textContent = error.message || 'Shipping rates are temporarily unavailable.';
      } finally { button.disabled = false; }
    });
    form.addEventListener('input', () => { revision++; result.textContent = ''; form.dispatchEvent(new CustomEvent('raghushree:quote-invalid')); });
    form.addEventListener('change', () => { revision++; result.textContent = ''; form.dispatchEvent(new CustomEvent('raghushree:quote-invalid')); });
  });
})();
