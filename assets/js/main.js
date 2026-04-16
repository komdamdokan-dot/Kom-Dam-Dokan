document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.querySelector('[data-live-search]');
  const suggestBox = document.querySelector('[data-suggest-box]');

  if (searchInput && suggestBox) {
    let timer;
    searchInput.addEventListener('input', () => {
      clearTimeout(timer);
      const query = searchInput.value.trim();
      if (query.length < 2) {
        suggestBox.style.display = 'none';
        suggestBox.innerHTML = '';
        return;
      }

      timer = setTimeout(() => {
        fetch(`ajax/search_suggest.php?q=${encodeURIComponent(query)}`)
          .then((response) => response.json())
          .then((items) => {
            suggestBox.innerHTML = items.map((item) => `<a href="product.php?id=${item.id}">${item.name}</a>`).join('');
            suggestBox.style.display = items.length ? 'block' : 'none';
          })
          .catch(() => {
            suggestBox.style.display = 'none';
          });
      }, 250);
    });
  }

  document.querySelectorAll('[data-cart-action]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const fd = new FormData(form);
      fetch('ajax/cart_action.php', { method: 'POST', body: fd })
        .then((response) => response.json())
        .then((json) => {
          if (json.message) {
            alert(json.message);
          }
          if (json.count !== undefined) {
            document.querySelectorAll('[data-cart-count]').forEach((node) => { node.textContent = json.count; });
          }
          if (form.dataset.reload === '1') {
            window.location.reload();
          }
        });
    });
  });

  document.querySelectorAll('[data-range-sync]').forEach((range) => {
    range.addEventListener('input', () => {
      const target = document.getElementById(range.dataset.rangeSync);
      if (target) {
        target.value = range.value;
      }
    });
  });
});
