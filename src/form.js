// Contact form: sends the enquiry without leaving the page, with clear messages.
// Without JavaScript the form still works: it posts to contact.php, which sends the
// visitor back to this page with ?sent=1 or ?error=1.
(function () {
  var form = document.getElementById('enquiry-form');
  var status = document.getElementById('form-status');
  if (!form || !status) return;

  var button = form.querySelector('button[type="submit"]');
  var loadedAt = Date.now();
  var FALLBACK = 'Sorry, your message could not be sent. Please call 02382 448464 or email info@coastlinesafetyservices.co.uk.';

  function show(kind, text) {
    status.hidden = false;
    status.className = 'form-status ' + kind;
    status.textContent = text;
    status.focus();
  }

  // Messages coming back from the plain (no JavaScript) form post.
  var params = new URLSearchParams(window.location.search);
  if (params.get('sent') === '1') {
    show('success', 'Thank you, your message has been sent. We will get back to you soon.');
  } else if (params.get('error') === '1') {
    show('error', FALLBACK);
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var data = new FormData(form);
    data.append('elapsed', String(Math.round((Date.now() - loadedAt) / 1000)));

    button.disabled = true;
    var label = button.textContent;
    button.textContent = 'Sending...';
    status.hidden = true;

    fetch(form.getAttribute('action'), {
      method: 'POST',
      body: data,
      headers: { Accept: 'application/json' }
    })
      .then(function (response) {
        return response.json().then(
          function (result) { return { ok: response.ok && result.ok, message: result.message }; },
          function () { return { ok: false }; }
        );
      })
      .then(function (result) {
        if (result.ok) {
          form.reset();
          loadedAt = Date.now();
          show('success', result.message || 'Thank you, your message has been sent.');
        } else {
          show('error', result.message || FALLBACK);
        }
      })
      .catch(function () {
        show('error', FALLBACK);
      })
      .then(function () {
        button.disabled = false;
        button.textContent = label;
      });
  });
})();
