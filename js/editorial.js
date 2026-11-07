/* Progressive section navigation; article content works without JavaScript. */
(function () {
  'use strict';
  document.querySelectorAll('.editorial-page').forEach(function (page) {
    var content = page.querySelector('.editorial-content');
    var toc = page.querySelector('.editorial-toc');
    if (!content || !toc) return;
    var headings = Array.from(content.querySelectorAll('h2, h3')).filter(function (heading) {
      return heading.textContent.trim() && !heading.closest('form, .wp-block-cover, .wp-block-query');
    });
    if (headings.length < 2) return;
    var list = toc.querySelector('ol');
    headings.forEach(function (heading, index) {
      if (!heading.id) {
        var base = 'section-' + (index + 1);
        var id = base;
        var suffix = 1;
        while (document.getElementById(id)) id = base + '-' + suffix++;
        heading.id = id;
      }
      var item = document.createElement('li');
      var link = document.createElement('a');
      link.href = '#' + encodeURIComponent(heading.id);
      link.textContent = heading.textContent.trim();
      item.appendChild(link);
      list.appendChild(item);
    });
    toc.hidden = false;
    toc.open = window.matchMedia('(min-width: 901px)').matches;
  });
}());
