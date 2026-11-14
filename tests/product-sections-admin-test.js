// Requires jsdom (install outside repository and set NODE_PATH).
const { JSDOM } = require('jsdom'); const assert = require('assert'); const fs = require('fs');
const dom = new JSDOM(`<div class="rg-section-editor"><fieldset data-section="features"><div data-section-rows><div data-section-row><label>Feature<input name="rg_sections[features][0][0]" value="one"></label><label>Details<input name="rg_sections[features][0][1]" value="two"></label><button data-section-remove>Remove</button></div></div><button data-section-add data-next-index="1">Add</button></fieldset></div>`, { runScripts: 'outside-only' });
const { window } = dom; window.eval(fs.readFileSync('assets/js/product-sections-admin.js', 'utf8')); window.document.dispatchEvent(new window.Event('DOMContentLoaded'));
const doc = window.document; const add = doc.querySelector('[data-section-add]'); add.click();
assert.strictEqual(doc.querySelectorAll('[data-section-row]').length, 2); assert(doc.querySelector('[name="rg_sections[features][1][0]"]')); assert.strictEqual(doc.querySelector('[name="rg_sections[features][1][0]"]').value, '');
doc.querySelector('[data-section-remove]').click(); doc.querySelector('[data-section-remove]').click(); assert.strictEqual(doc.querySelectorAll('[data-section-row]').length, 1); add.click(); assert.strictEqual(doc.querySelectorAll('[data-section-row]').length, 2);
console.log('Product sections editor smoke tests passed.');
