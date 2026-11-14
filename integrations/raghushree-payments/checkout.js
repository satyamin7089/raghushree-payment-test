(() => {
  const quoteForm = document.querySelector('.product-quote');
  const form = document.querySelector('.product-checkout');
  if (!quoteForm || !form) return;
  const status = form.querySelector('.product-checkout__status');
  const button = form.querySelector('button[type="submit"]');
  const section = form.closest('.checkout-section');
  let quoteToken = '';
  let started = false;
  let finished = false;
  quoteForm.addEventListener('raghushree:quote', (event) => {
    if (started) return;
    quoteToken = event.detail.checkout_token || '';
    form.hidden = !quoteToken;
    if (section) section.hidden = !quoteToken;
    status.textContent = '';
    if (quoteToken) {
      const quote = event.detail;
      const money = (paise) => '₹' + (Number(paise) / 100).toFixed(2);
      ['product_paise', 'product_gst_paise', 'rate_paise', 'shipping_gst_paise', 'total_paise'].forEach((key) => {
        const field = form.querySelector(`[data-summary="${key}"]`);
        if (field) field.textContent = money(quote[key]);
      });
      form.querySelector('[data-summary="product"]').textContent = `${quote.product_name} — ${quote.option_name}`;
      form.querySelector('[data-summary="gst_label"]').textContent = `Product GST (${quote.product_gst_bps / 100}%)`;
      form.querySelector('[data-summary="courier"]').textContent = quote.courier_name ? `Lowest available courier: ${quote.courier_name}` : 'Lowest available courier';
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
  quoteForm.addEventListener('raghushree:quote-invalid', () => {
    if (!started) { quoteToken = ''; form.hidden = true; if (section) section.hidden = true; }
  });
  async function post(action, fields) {
    const body = new URLSearchParams({ action, nonce: quoteForm.dataset.nonce, ...fields });
    const response = await fetch(quoteForm.dataset.endpoint, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
    const data = await response.json();
    if (!data.success) throw new Error(data.data?.message || 'An error occurred. Please contact us.');
    return data.data;
  }
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!quoteToken || started || !form.reportValidity()) return;
    if (typeof window.Razorpay !== 'function') { status.textContent = 'Razorpay Checkout could not load. Please try again later.'; return; }
    button.disabled = true;
    status.textContent = 'Preparing your order…';
    let order;
    try {
      order = await post('rgpay_start_checkout', { ...Object.fromEntries(new FormData(form)), quote_token: quoteToken });
      started = true;
      quoteForm.querySelectorAll('input, select, button').forEach((element) => { element.disabled = true; });
    } catch (error) {
      quoteToken = '';
      button.disabled = false;
      status.textContent = error.message + ' Please check shipping again before retrying.';
      return;
    }
    const checkout = new window.Razorpay({
      key: order.key, order_id: order.razorpay_order_id, amount: order.amount, currency: 'INR',
      name: 'Raghushree', description: order.description, prefill: order.prefill,
      handler: async (response) => {
        finished = true;
        button.disabled = true;
        status.textContent = 'Verifying payment…';
        try {
          const result = await post('rgpay_verify_checkout', { internal_order_id: String(order.internal_order_id), receipt_token: order.receipt_token, razorpay_payment_id: response.razorpay_payment_id, razorpay_order_id: response.razorpay_order_id, razorpay_signature: response.razorpay_signature });
          status.textContent = result.message + ' Order #' + order.internal_order_id;
        } catch (error) { status.textContent = 'Payment confirmation is pending. Do not pay again. Contact us with order #' + order.internal_order_id + ' and your payment reference.'; }
      },
      modal: { ondismiss: () => { if (finished) return; button.disabled = false; button.textContent = 'Resume payment'; status.textContent = 'Checkout closed. Resume the same order, or contact us if payment was already made. Order #' + order.internal_order_id; } },
    });
    checkout.on('payment.failed', () => { status.textContent = 'Payment failed or is pending. Check your payment status before retrying. Order #' + order.internal_order_id; });
    // Reopen the same Razorpay order, never create another on a retry.
    form.onsubmit = (e) => { e.preventDefault(); if (!finished) checkout.open(); };
    status.textContent = 'Opening secure payment…';
    checkout.open();
  });
})();
