/* All project links remain usable when JavaScript is unavailable. */
(function () {
  'use strict';
  document.querySelectorAll('.project-directory').forEach(function (directory) {
    var tools = directory.querySelector('.directory-tools');
    var search = directory.querySelector('input[type="search"]');
    var cards = Array.from(directory.querySelectorAll('.project-directory-card'));
    var buttons = Array.from(directory.querySelectorAll('[data-project-filter]'));
    if (!tools || !search || !cards.length) return;
    var category = 'All';
    function normalize(value) { return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim(); }
    function update() {
      var query = normalize(search.value);
      var count = 0;
      cards.forEach(function (card) {
        var label = card.querySelector('.project-card-category');
        var matches = (category === 'All' || (label && label.textContent.trim() === category)) && normalize(card.textContent).includes(query);
        card.hidden = !matches;
        if (matches) count++;
      });
      directory.querySelector('.directory-count').textContent = count + (count === 1 ? ' project' : ' projects') + ' found';
      directory.querySelector('.directory-empty').hidden = count > 0;
    }
    search.addEventListener('input', update);
    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        category = button.getAttribute('data-project-filter');
        buttons.forEach(function (item) { item.setAttribute('aria-pressed', String(item === button)); });
        update();
      });
    });
    tools.hidden = false;
    update();
  });
}());
