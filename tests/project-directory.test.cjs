const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const script = fs.readFileSync(path.join(__dirname,'../js/project-directory.js'),'utf8');
const pattern = fs.readFileSync(path.join(__dirname,'../patterns/project-directory.php'),'utf8').replace(/<\?php[\s\S]*?\?>/g,'');
test('directory combines search and categories, announces results, and recovers from no matches', () => {
 const dom = new JSDOM(pattern,{runScripts:'outside-only'});const w=dom.window,d=w.document;w.eval(script);
 assert.equal(d.querySelector('.directory-tools').hidden,false);
 const visible=()=>[...d.querySelectorAll('.project-directory-card')].filter(c=>!c.hidden);
 assert.equal(visible().length,12);
 d.querySelector('[data-project-filter="Learning"]').click();assert.equal(visible().length,6);
 const input=d.querySelector('input');input.value='After School';input.dispatchEvent(new w.Event('input'));assert.equal(visible().length,1);
 input.value='no-such-project';input.dispatchEvent(new w.Event('input'));assert.equal(visible().length,0);assert.equal(d.querySelector('.directory-empty').hidden,false);assert.match(d.querySelector('[role=status]').textContent,/0 projects/);
 input.value='';input.dispatchEvent(new w.Event('input'));d.querySelector('[data-project-filter="All"]').click();assert.equal(visible().length,12);assert.equal(d.querySelector('.directory-empty').hidden,true);
 assert.equal(d.querySelectorAll('[aria-pressed="true"]').length,1);dom.window.close();
});
test('without JavaScript every project remains available as one ordinary link',()=>{
 const dom=new JSDOM(pattern);const cards=[...dom.window.document.querySelectorAll('.project-directory-card')];assert.equal(cards.length,12);
 for(const card of cards){assert.equal(card.hidden,false);assert.equal(card.querySelectorAll('a[href]').length,1);assert(!card.querySelector('a').getAttribute('href').startsWith('#'));}dom.window.close();
});
