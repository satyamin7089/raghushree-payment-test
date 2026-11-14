// Requires jsdom; install outside the repository and set NODE_PATH.
const { JSDOM } = require('jsdom'); const fs = require('fs'); const assert = require('assert');
const dom = new JSDOM(`<form><div class="raghushree-courier-pickers" hidden><select id="sr-product-picker"><option value="">Select</option><option value="42">Test product</option></select><select id="sr-pack-picker"></select></div><input id="sr-product-id"><input id="sr-option"></form>`, { runScripts: 'outside-only' });
const doc = dom.window.document; const picker = doc.querySelector('#sr-product-picker'); picker.options[1].dataset.packs = JSON.stringify([{ key: '5kg', label: '5 kg' }, { key: '10kg', label: '10 kg' }]);
dom.window.eval(fs.readFileSync('assets/js/courier-test-admin.js', 'utf8')); doc.dispatchEvent(new dom.window.Event('DOMContentLoaded'));
picker.value = '42'; picker.dispatchEvent(new dom.window.Event('change')); const pack = doc.querySelector('#sr-pack-picker'); pack.value = '10kg'; pack.dispatchEvent(new dom.window.Event('change'));
assert.strictEqual(doc.querySelector('#sr-product-id').value, '42'); assert.strictEqual(doc.querySelector('#sr-option').value, '10kg'); console.log('Courier test picker smoke tests passed.');
