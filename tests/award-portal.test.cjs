const {test}=require('node:test');
const assert=require('node:assert/strict');
const {JSDOM}=require('jsdom');
const fs=require('node:fs');
const script=fs.readFileSync(require('node:path').join(__dirname,'../js/award-portal.js'),'utf8');
function boot(){
 const dom=new JSDOM(`<div class="award-portal"><div class="wpcf7"><form><div class="yea-award-form"><fieldset class="award-step" id="step-1" data-step-title="Project"><legend>Project</legend><input name="project-title" aria-required="true"></fieldset><fieldset class="award-step" id="step-2" data-step-title="Contact"><legend>Contact</legend><input name="coordinator-email" type="email" aria-required="true"><input type="submit"></fieldset></div></form></div><section class="award-success" hidden></section></div>`,{runScripts:'outside-only',pretendToBeVisual:true});
 dom.window.eval(script);dom.window.document.dispatchEvent(new dom.window.Event('DOMContentLoaded'));return dom;
}
test('award steps block empty fields and retain edits when navigating back',()=>{
 const dom=boot(),d=dom.window.document;try{
  d.querySelector('.award-next').click();assert.equal(d.querySelector('#step-1').hidden,false);assert.equal(d.activeElement.name,'project-title');
  d.querySelector('[name=project-title]').value='A project';d.querySelector('.award-next').click();assert(d.querySelector('#step-1').hidden);assert.equal(d.querySelector('#step-2').hidden,false);
  d.querySelector('.award-back').click();assert.equal(d.querySelector('[name=project-title]').value,'A project');
  assert(d.querySelector('.award-success').hidden);
 }finally{dom.window.close();}
});
test('CF7 server errors reveal the field and only a real CF7 success event shows the next action',()=>{
 const dom=boot(),w=dom.window,d=w.document;try{
  d.querySelector('[name=project-title]').value='A project';d.querySelector('.award-next').click();
  d.querySelector('.wpcf7').dispatchEvent(new w.CustomEvent('wpcf7invalid',{detail:{apiResponse:{invalid_fields:[{field:'project-title'}]}}}));
  assert.equal(d.querySelector('#step-1').hidden,false);assert.equal(d.activeElement.name,'project-title');assert(d.querySelector('.award-success').hidden);
  d.querySelector('.wpcf7').dispatchEvent(new w.CustomEvent('wpcf7mailsent'));assert.equal(d.querySelector('.award-success').hidden,false);
 }finally{dom.window.close();}
});
test('Enter on an intermediate step cannot submit an incomplete application',()=>{
 const dom=boot(),d=dom.window.document;try{
  const event=new dom.window.Event('submit',{bubbles:true,cancelable:true});d.querySelector('form').dispatchEvent(event);assert(event.defaultPrevented);
 }finally{dom.window.close();}
});
