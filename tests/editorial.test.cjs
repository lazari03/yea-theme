const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');
const script = fs.readFileSync(require('node:path').join(__dirname, '../js/editorial.js'), 'utf8');
function boot(content, desktop = false) {
  const dom = new JSDOM(`<div id="section-1"></div><main class="editorial-page"><article class="editorial-content">${content}</article><details class="editorial-toc" hidden><summary>On this page</summary><nav><ol></ol></nav></details></main>`, {runScripts:'outside-only'});
  dom.window.matchMedia = () => ({matches: desktop});
  dom.window.eval(script);
  return dom;
}
test('section navigation preserves authored IDs, avoids collisions, and uses real targets', () => {
  const dom = boot('<h2>First</h2><h3 id="existing">Second</h3><h2>Third</h2>', true);
  const d = dom.window.document;
  assert.equal(d.querySelector('.editorial-toc').hidden, false);
  assert.equal(d.querySelector('.editorial-toc').open, true);
  const links = [...d.querySelectorAll('nav a')];
  assert.equal(links.length, 3);
  assert.equal(links[1].hash, '#existing');
  assert.equal(d.querySelector('h2').id, 'section-1-1');
  for (const a of links) assert.equal(d.getElementById(decodeURIComponent(a.hash.slice(1))).textContent, a.textContent);
  dom.window.close();
});
test('mobile section navigation starts collapsed and ignores form and card headings', () => {
  const dom = boot('<h2>Overview</h2><form><h2>Private</h2></form><div class="wp-block-cover"><h2>Card</h2></div><h3>Details</h3>');
  assert.equal(dom.window.document.querySelector('.editorial-toc').open, false);
  assert.equal(dom.window.document.querySelectorAll('nav a').length, 2);
  dom.window.close();
});
test('short content remains visible without an empty navigation panel', () => {
  const dom = boot('<h2>Only heading</h2><p>Full page remains readable.</p>');
  assert.equal(dom.window.document.querySelector('.editorial-toc').hidden, true);
  assert.equal(dom.window.document.querySelector('p').hidden, false);
  dom.window.close();
});
