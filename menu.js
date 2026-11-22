// Mobile menu: the hamburger button opens and closes the main navigation.
(function () {
  var button = document.querySelector('.nav-toggle');
  var nav = document.getElementById('site-nav');
  if (!button || !nav) return;

  function setOpen(open) {
    nav.classList.toggle('open', open);
    button.classList.toggle('open', open);
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
    button.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
  }

  button.addEventListener('click', function (event) {
    event.stopPropagation();
    setOpen(!nav.classList.contains('open'));
  });

  // Close when a link is chosen, when clicking elsewhere, or on Escape.
  nav.addEventListener('click', function (event) {
    if (event.target.closest('a')) setOpen(false);
  });
  document.addEventListener('click', function (event) {
    if (!nav.contains(event.target)) setOpen(false);
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      setOpen(false);
      button.focus();
    }
  });

  // If the window is resized to desktop width, reset the menu.
  window.addEventListener('resize', function () {
    if (window.innerWidth > 760) setOpen(false);
  });

  setOpen(false);
})();
