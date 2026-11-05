/* Run against a server-rendered WordPress page without a local web server.
 * See README.md for the required input paths. Does not submit forms or visit production.
 */
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
let playwright;
try { playwright = require('playwright'); } catch {
  playwright = require(require.resolve('playwright', {paths:[process.env.CODEX_PRIMARY_RUNTIME_NODE_MODULES].filter(Boolean)}));
}
(async () => {
  if (!process.env.YEA_RENDERED_HTML || !process.env.YEA_PREVIEW_ROOT) throw new Error('YEA_RENDERED_HTML and YEA_PREVIEW_ROOT are required');
  const html = fs.readFileSync(process.env.YEA_RENDERED_HTML,'utf8');
  const wpRoot=path.resolve(process.env.YEA_PREVIEW_ROOT);
  const cache=process.env.YEA_ASSET_CACHE ? JSON.parse(fs.readFileSync(process.env.YEA_ASSET_CACHE,'utf8')) : {};
  const fonts=process.env.YEA_FONT_CSS ? fs.readFileSync(process.env.YEA_FONT_CSS,'utf8') : '';
  const resultsDir=path.resolve(process.env.YEA_SCREENSHOTS || 'test-results');fs.mkdirSync(resultsDir,{recursive:true});
  const browser=await playwright.chromium.launch({headless:true,executablePath:process.env.YEA_CHROMIUM || undefined,args:['--no-sandbox']});
  const results=[];
  try {
    for(const width of JSON.parse(process.env.YEA_WIDTHS || '[320,375,390,560,768,820,821,1024,1025,1100,1280,1440]')) {
      const context=await browser.newContext({viewport:{width,height:900},reducedMotion:'reduce'});
      const errors=[];const page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));
      await page.route('**/*',async route=>{
        const url=new URL(route.request().url());
        if(url.hostname==='fonts.googleapis.com' && fonts) return route.fulfill({contentType:'text/css',body:fonts});
        const asset=cache[url.href];if(asset?.file) return route.fulfill({path:asset.file,contentType:asset.mime});
        if(url.hostname==='127.0.0.1') {
          const file=path.resolve(wpRoot,'.'+decodeURIComponent(url.pathname));
          if(file.startsWith(wpRoot+path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
            const ext=path.extname(file);const type={'.css':'text/css','.js':'text/javascript','.mjs':'text/javascript','.svg':'image/svg+xml','.webp':'image/webp','.png':'image/png','.jpg':'image/jpeg'}[ext];
            return route.fulfill({path:file,contentType:type || 'application/octet-stream'});
          }
        }
        return route.abort();
      });
      await page.setContent(html,{waitUntil:'networkidle'});
      await page.waitForSelector('#header.yea-nav-ready');
      await page.evaluate(()=>document.fonts.ready);
      const gap=await page.evaluate(()=>{
        const header=document.querySelector('#header');
        const main=document.querySelector('main.site-main');
        return main ? main.getBoundingClientRect().top-header.getBoundingClientRect().bottom : 0;
      });
      assert(gap<=1,`${width}px: unexpected header/main gap ${gap}`);
      await page.locator('main').evaluate(main=>{
        const fixture=document.createElement('section');fixture.id='component-regressions';fixture.className='container entry-content';
        fixture.innerHTML=`<figure class="wp-block-image"><img id="article-portrait" width="240" height="360" style="width:240px;height:auto" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='360'/%3E" alt="Portrait regression fixture"></figure>
        <figure class="wp-block-image"><img id="article-landscape" width="600" height="300" style="width:600px;height:auto" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='600' height='300'/%3E" alt="Landscape regression fixture"></figure>
        <div class="wpcf7"><form><div class="form-grid"><p><label>Name<input type="text"></label></p><p><label>Email<input type="email"></label></p><p class="form-full-width"><span class="wpcf7-checkbox"><span class="wpcf7-list-item"><label><input type="checkbox">A longer readable option</label></span><span class="wpcf7-list-item"><label><input type="checkbox">Another option</label></span></span></p></div><input type="hidden"><button type="submit">Submit</button></form></div>
        <figure class="wp-block-table"><table><thead><tr><th>Column</th><th>Other column</th></tr></thead><tbody><tr><td>${'LongUnbrokenTableValue'.repeat(8)}</td><td>Details</td></tr></tbody></table></figure>`;
        main.append(fixture);
      });
      const components=await page.locator('#component-regressions').evaluate(el=>{
        const img=id=>{const i=el.querySelector(id),r=i.getBoundingClientRect();return {ratio:r.width/r.height,width:r.width,margin:getComputedStyle(i).marginTop}};
        const fields=[...el.querySelectorAll('input:not([type=hidden]):not([type=checkbox])')].map(i=>({w:i.getBoundingClientRect().width,parent:i.closest('p').getBoundingClientRect().width,top:i.closest('p').getBoundingClientRect().top}));
        return {portrait:img('#article-portrait'),landscape:img('#article-landscape'),fields,formDisplay:getComputedStyle(el.querySelector('form')).display,checkbox:el.querySelector('[type=checkbox]').getBoundingClientRect().width,table:el.querySelector('.wp-block-table').getBoundingClientRect().width,container:el.clientWidth};
      });
      assert(Math.abs(components.portrait.ratio-2/3)<0.01,'portrait proportions changed');
      assert(Math.abs(components.landscape.ratio-2)<0.01,'landscape proportions changed');
      assert(components.portrait.width<=240.1,'authored article image enlarged');
      assert.equal(components.portrait.margin,'0px');
      assert.equal(components.formDisplay,'block');
      assert(components.checkbox<=24,'checkbox stretched');
      for(const f of components.fields)assert(f.w<=f.parent+1,'form field escapes its column');
      if(width>=1024)assert(Math.abs(components.fields[0].top-components.fields[1].top)<1,'form columns no longer inline');
      assert(components.table<=components.container+1,'table escapes content');
      await page.locator('#component-regressions').evaluate(el=>el.remove());
      if(width<=1024) {
        const trigger=page.locator('#header .wp-block-navigation__responsive-container-open').first();
        await trigger.click();await page.waitForSelector('.yea-mobile-drawer:not([hidden])');
        const drawer=page.locator('.yea-mobile-drawer');
        assert.equal(await drawer.getAttribute('role'),'dialog');
        assert.equal(await drawer.locator('.wp-block-navigation-submenu__toggle').count(),0);
        assert.equal(await drawer.locator('ul[hidden]').count(),0);
        assert.equal(await drawer.locator('.header-actions--mobile').count(),0);
        for(const link of await drawer.locator('a[href]').all()) assert(await link.isVisible(), 'every menu destination must be visible');
        if(width===390) await page.screenshot({path:path.join(resultsDir,'mobile-menu.png')});
        await page.keyboard.press('Escape');assert.equal(await drawer.getAttribute('hidden'),'');
        assert.equal(await page.evaluate(()=>document.activeElement.getAttribute('aria-controls')),'yea-mobile-navigation');
        assert.equal(await page.evaluate(()=>document.body.classList.contains('has-modal-open')),false);
      } else {assert(await page.locator('#header nav a').first().isVisible());}
      const logos=page.locator('.logo-wall-grid');
      if(await logos.count()) {
        await logos.scrollIntoViewIfNeeded();
        if(process.env.YEA_ASSERT_PARTNER_ASSETS) {
          await page.waitForFunction(()=>Array.from(document.querySelectorAll('.logo-wall-grid img')).every(img=>img.complete && img.naturalWidth>0));
          const marks=await logos.locator('img').evaluateAll(imgs=>imgs.map(img=>({height:img.getBoundingClientRect().height,fit:getComputedStyle(img).objectFit,margin:getComputedStyle(img).marginTop})));
          for(const mark of marks){assert(mark.height>=120,`${width}px: partner logo too small`);assert.equal(mark.fit,'contain');assert.equal(mark.margin,'0px','partner image offset clips the mark');}
        }
        if([390,1440].includes(width)) await logos.screenshot({path:path.join(resultsDir,`partners-${width}.png`)});
      }
      await page.evaluate(()=>scrollTo(0,0));
      const geometry=await page.evaluate(()=>({scroll:document.documentElement.scrollWidth,client:document.documentElement.clientWidth,
        buttons:Array.from(document.querySelectorAll('a.wp-block-button__link')).filter(el=>el.getClientRects().length).map(el=>({text:el.textContent.trim(),scroll:el.scrollWidth,client:el.clientWidth})),
        headingFont:getComputedStyle(document.querySelector('h1')).fontFamily,
        fontLoaded:document.fonts.check('16px "Bebas Neue"') && document.fonts.check('16px Montserrat')}));
      if(geometry.scroll>geometry.client+1) {console.log(await page.evaluate(()=>Array.from(document.querySelectorAll('body *')).filter(el=>el.getClientRects().length && el.getBoundingClientRect().right>innerWidth+2).map(el=>({tag:el.tagName,cls:el.className,right:el.getBoundingClientRect().right,width:el.getBoundingClientRect().width})).slice(0,25)));await page.screenshot({path:path.join(resultsDir,'overflow.png'),fullPage:true});}
      assert(geometry.scroll<=geometry.client+1,`${width}px: page horizontal overflow`);
      for(const button of geometry.buttons) assert(button.scroll<=button.client+1,`${width}px: clipped button ${button.text}`);
      assert.equal(errors.length,0,errors.join('\n'));
      if([390,768,1440].includes(width)) await page.screenshot({path:path.join(resultsDir,`homepage-${width}.png`),fullPage:true});
      results.push({width,...geometry,errors});await context.close();
    }
    const noJs = await browser.newContext({javaScriptEnabled:false,viewport:{width:390,height:900}});
    const fallbackPage=await noJs.newPage();
    await fallbackPage.route('**/*',async route=>{
      const url=new URL(route.request().url());
      if(url.hostname==='127.0.0.1' && url.pathname.endsWith('.css')) {
        const file=path.resolve(wpRoot,'.'+decodeURIComponent(url.pathname));
        if(file.startsWith(wpRoot+path.sep) && fs.existsSync(file)) return route.fulfill({path:file,contentType:'text/css'});
      }
      return route.abort();
    });
    await fallbackPage.setContent(html.replace('</main>','<div class="reveal" id="fallback-reveal">Readable without JavaScript</div></main>'),{waitUntil:'networkidle'});
    assert(await fallbackPage.locator('#header nav a').first().isVisible(),'no-JS navigation must remain reachable');
    assert.equal(await fallbackPage.locator('#fallback-reveal').evaluate(el=>getComputedStyle(el).opacity),'1');
    await noJs.close();
    fs.writeFileSync(path.join(resultsDir,'browser-results.json'),JSON.stringify(results,null,2));
    console.log(`Passed WordPress browser checks at ${results.length} widths. Screenshots: ${resultsDir}`);
  } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exit(1)});
