const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM, VirtualConsole} = require('jsdom');
const csstree = require('css-tree');
const root = path.join(__dirname, '..');
const script = fs.readFileSync(path.join(root, 'js/theme.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'style.css'), 'utf8');

function markup(legacy = false, empty = false) {
  const links = empty ? '' : `<li class="wp-block-navigation-item"><a href="/programs/">Programet</a><ul class="${legacy ? 'sub-menu' : 'wp-block-navigation__submenu-container'}"><li><a href="/afterschool/">Afterschool</a></li></ul></li><li><a href="/about/">Rreth nesh</a></li>`;
  return `<header id="header" class="site-header"><div class="header-inner"><div class="brand">YEA!</div>
    <nav class="nav ${legacy ? '' : 'wp-block-navigation'}" id="primary-nav">${legacy ? '' : '<button class="wp-block-navigation__responsive-container-open">Menu</button><div class="wp-block-navigation__responsive-container">'}
    <ul ${legacy ? 'id="primary-menu"' : 'class="wp-block-navigation__container"'}>${links}</ul>${legacy ? '' : '</div>'}</nav>
    <div class="header-actions"><a href="/#join">Join YEA</a></div>${legacy ? '<button id="menu-toggle">Menu</button>' : ''}</div></header>
    <main id="main-content"><div class="reveal">Visible content</div><a href="#%E0%A4%A">Malformed anchor</a></main><footer class="site-footer">Footer</footer>`;
}
async function boot(options = {}) {
  const virtualConsole = new VirtualConsole();
  const errors = [];
  virtualConsole.on('jsdomError', error => errors.push(error));
  const dom = new JSDOM(options.html || markup(options.legacy, options.empty), {url:'https://example.test/', runScripts:'outside-only', pretendToBeVisual:true, virtualConsole});
  await new Promise(resolve => dom.window.document.addEventListener('DOMContentLoaded', resolve, {once:true}));
  const w = dom.window;
  w.innerWidth = options.width || 390;
  w.matchMedia = query => ({matches:query.includes('prefers-reduced-motion') ? Boolean(options.reduced) : query.includes('pointer: coarse'), addEventListener(){}, removeEventListener(){}});
  w.scrollTo = () => {};
  // jsdom has no layout engine. Supply visibility only for the focus-loop test;
  // real wrapping/geometry is covered separately by browser smoke tests.
  w.HTMLElement.prototype.getClientRects = function() {return this.closest('[hidden]') ? [] : [{width:10,height:10}];};
  if (options.observer) w.IntersectionObserver = class {observe(){} unobserve(){}};
  w.eval(script);
  w.document.dispatchEvent(new w.Event('DOMContentLoaded'));
  return {dom, w, d:w.document, errors, close:()=>dom.window.close()};
}
for (const legacy of [false,true]) {
  test(`${legacy ? 'PHP' : 'block'} menu: all pages visible, real links, focus and resize`, async () => {
    const ctx=await boot({legacy});const {w,d}=ctx;
    try {
      const trigger=d.querySelector(legacy ? '#menu-toggle' : '.wp-block-navigation__responsive-container-open');
      assert(d.querySelector('#header').classList.contains('yea-nav-ready'));
      trigger.focus();trigger.click();const drawer=d.querySelector('.yea-mobile-drawer');
      assert.equal(drawer.hidden,false);assert.equal(d.activeElement.className,'yea-menu-close');
      assert.equal(d.querySelector('main').inert,true);
      assert.equal(drawer.querySelectorAll('.wp-block-navigation-submenu__toggle').length,0);
      assert.equal(drawer.querySelectorAll('ul[hidden]').length,0);
      assert.equal(drawer.querySelector('.header-actions--mobile'),null,'never copy signup CTAs');
      const submenu=drawer.querySelector('ul ul');const before=submenu;
      const childLink=drawer.querySelector('a[href="/afterschool/"]');childLink.focus();
      w.innerWidth=768;w.dispatchEvent(new w.Event('resize'));
      assert.equal(drawer.querySelector('ul ul'),before);assert.equal(d.activeElement,childLink);
      const closeButton=drawer.querySelector('.yea-menu-close');closeButton.focus();
      d.dispatchEvent(new w.KeyboardEvent('keydown',{key:'Tab',shiftKey:true,bubbles:true,cancelable:true}));
      assert.equal(d.activeElement,Array.from(drawer.querySelectorAll('a[href]')).at(-1));
      d.dispatchEvent(new w.KeyboardEvent('keydown',{key:'Escape',bubbles:true,cancelable:true}));
      assert.equal(drawer.hidden,true);assert.equal(d.activeElement,trigger);assert.equal(d.querySelector('main').inert,undefined);
      assert(!d.body.classList.contains('has-modal-open'));
      trigger.click();let navigable=false;
      w.addEventListener('click',event=>{if(event.target.closest('a')) {navigable=!event.defaultPrevented;event.preventDefault();}},{once:true});
      drawer.querySelector('a[href="/programs/"]').click();assert(navigable,'page links must navigate');assert.equal(drawer.hidden,true);
      trigger.click();w.innerWidth=1280;w.dispatchEvent(new w.Event('resize'));assert.equal(drawer.hidden,true);
      if(!legacy){const native=d.querySelector('.wp-block-navigation__responsive-container');assert(!native.hidden);assert.notEqual(native.getAttribute('aria-hidden'),'true');}
      const ids=Array.from(d.querySelectorAll('[id]'),el=>el.id);assert.equal(new Set(ids).size,ids.length);assert.equal(ctx.errors.length,0);
    }finally{ctx.close();}
  });
}
test('WordPress wrapped page-list fallback stays visible in the drawer',async()=>{
 const html=markup().replace('<ul class="wp-block-navigation__container">','<ul class="wp-block-navigation__container"><ul class="wp-block-page-list">').replace('</ul></div></nav>','</ul></ul></div></nav>');
 const ctx=await boot({html});try{ctx.d.querySelector('.wp-block-navigation__responsive-container-open').click();const list=ctx.d.querySelector('.yea-mobile-nav-list');assert(list.classList.contains('wp-block-page-list'));assert(!list.hidden);assert(!list.querySelector('a').closest('[hidden]'));}finally{ctx.close();}
});
test('empty menus keep native fallback; no custom interception',async()=>{
 const ctx=await boot({empty:true});try{assert(!ctx.d.querySelector('#header').classList.contains('yea-nav-ready'));const button=ctx.d.querySelector('.wp-block-navigation__responsive-container-open');assert(button.dispatchEvent(new ctx.w.MouseEvent('click',{bubbles:true,cancelable:true})));}finally{ctx.close();}
});
test('reduced motion and no observer leave reveal content unhidden',async()=>{
 for(const options of [{observer:true},{reduced:true,observer:true},{}]) {const ctx=await boot(options);try{assert(!ctx.d.querySelector('.reveal').classList.contains('reveal-pending'));ctx.d.querySelector('a[href="#%E0%A4%A"]').click();assert.equal(ctx.errors.length,0);}finally{ctx.close();}}
});
test('CSS parses, content remains visible, brand fonts exist',()=>{
 const errors=[];for(const source of [css,fs.readFileSync(path.join(root,'assets/css/components/partner-logos.css'),'utf8'),...['navigation','content','forms','award-portal'].map(name=>fs.readFileSync(path.join(root,`assets/css/components/${name}.css`),'utf8'))])csstree.parse(source,{onParseError:error=>errors.push(error.message)});assert.deepEqual(errors,[]);
 assert(!/\.reveal[^{}]*\{[^{}]*opacity:\s*0/.test(css));
 const theme=JSON.parse(fs.readFileSync(path.join(root,'theme.json'),'utf8'));
 assert(theme.settings.typography.fontFamilies.some(f=>f.slug==='montserrat'));
 assert(theme.settings.typography.fontFamilies.some(f=>f.slug==='bebas-neue'));
 assert(theme.settings.typography.fontFamilies.some(f=>f.slug==='inter'),'preserve saved Inter token');
});
test('rendered WordPress output, when provided, initializes without theme errors',async t=>{
 if(!process.env.YEA_RENDERED_HTML) return t.skip('Set YEA_RENDERED_HTML to exercise a real WordPress render');
 const ctx=await boot({html:fs.readFileSync(process.env.YEA_RENDERED_HTML,'utf8')});try{assert(ctx.d.querySelector('#header.yea-nav-ready'));assert.equal(ctx.errors.length,0);}finally{ctx.close();}
});

test('anchor links retain native navigation and history',async()=>{
 const ctx=await boot({html:markup().replace('<main id="main-content">','<a id="anchor-fixture" href="#main-content">Skip to content</a><main id="main-content">')});try{
  const link=ctx.d.querySelector('#anchor-fixture');
  const event=new ctx.w.MouseEvent('click',{bubbles:true,cancelable:true});
  link.dispatchEvent(event);assert.equal(event.defaultPrevented,false);
 }finally{ctx.close();}
});
