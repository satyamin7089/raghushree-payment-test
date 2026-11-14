// Requires jsdom (install outside the repository and set NODE_PATH).
const { JSDOM } = require('jsdom'); const fs = require('fs'); const assert = require('assert');
const script = fs.readFileSync('assets/js/product-gallery-admin.js', 'utf8');
const markup = `<div class="rg-gallery-editor"><div data-gallery-preview></div><button data-gallery-add hidden>Choose images</button><p data-gallery-fallback><input id="raghushree_gallery_ids" value="11, 22"></p></div>`;
const dom = new JSDOM(markup, { runScripts: 'outside-only' });
const doc = dom.window.document; const editor = doc.querySelector('.rg-gallery-editor'); editor.dataset.galleryItems = JSON.stringify([{id: 11, url: '/a.jpg'}, {id: 22, url: '/b.jpg'}]);
let onSelect; let opened = false;
dom.window.wp = { media: () => ({ on: (_, callback) => { onSelect = callback; }, open: () => { opened = true; }, state: () => ({ get: () => ({ each: (fn) => [ { id: 22, url: '/b.jpg' }, { id: 33, url: '/c.jpg' } ].forEach((image) => fn({ toJSON: () => image })) }) }) }) };
dom.window.eval(script); doc.dispatchEvent(new dom.window.Event('DOMContentLoaded'));
const input = doc.querySelector('input'); assert(doc.querySelector('[data-gallery-fallback]').hidden); assert.strictEqual(doc.querySelectorAll('.rg-gallery-item').length, 2);
doc.querySelector('[data-gallery-add]').click(); assert(opened); onSelect(); assert.strictEqual(input.value, '11, 22, 33');
doc.querySelectorAll('.rg-gallery-item')[2].querySelector('[aria-label^="Move earlier"]').click(); assert.strictEqual(input.value, '11, 33, 22');
doc.querySelectorAll('.rg-gallery-item')[0].querySelector('[aria-label^="Remove"]').click(); assert.strictEqual(input.value, '33, 22');
const fallback = new JSDOM(markup, { runScripts: 'outside-only' }); fallback.window.eval(script); fallback.window.document.dispatchEvent(new fallback.window.Event('DOMContentLoaded')); assert(!fallback.window.document.querySelector('[data-gallery-fallback]').hidden, 'IDs remain editable when media scripts unavailable');
console.log('Product gallery admin smoke tests passed.');
