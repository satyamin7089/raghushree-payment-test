// Requires jsdom installed outside the repository and NODE_PATH set.
const { JSDOM } = require('jsdom'); const fs = require('fs'); const assert = require('assert');
const dom = new JSDOM(`<form class="product-quote" data-type="polymer" data-endpoint="/ajax" data-product="42" data-nonce="nonce"><select name="option"><option value="10kg">10 kg</option></select><input name="pincode" value="226001"><span data-purchase-price></span><p data-purchase-note></p><p class="product-quote__result"></p><button type="submit">Check</button></form>`, { runScripts: 'outside-only' });
const form = dom.window.document.querySelector('form'); form.dataset.packs = JSON.stringify({ '10kg': {price_paise: 10000} });
dom.window.fetch = async () => ({ json: async () => ({ success: false, data: { message: 'Please try again.', reference: 'SR-RATE-HTTP' } }) });
dom.window.eval(fs.readFileSync('assets/js/shipping-quote.js', 'utf8')); form.dispatchEvent(new dom.window.Event('submit', { bubbles: true, cancelable: true }));
setTimeout(() => { try { assert.match(form.querySelector('.product-quote__result').textContent, /Reference: SR-RATE-HTTP/); console.log('Shipping reference smoke tests passed.'); } catch (err) { console.error(err); process.exitCode = 1; } }, 0);
