// Submit the contact form to Formspree via fetch, reproducing the inline
// success/error behaviour Contact Form 7 provided on the WordPress site.
// With JS disabled the form still POSTs to Formspree's hosted fallback page.
(function () {
  var form = document.querySelector('form.wpcf7-form') ||
             document.querySelector('form[action^="https://formspree.io"]');
  if (!form) return;
  var output = form.querySelector('.wpcf7-response-output');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    output.textContent = '';
    output.className = 'wpcf7-response-output';
    output.removeAttribute('aria-hidden');
    fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { Accept: 'application/json' }
    }).then(function (res) {
      if (res.ok) {
        form.reset();
        output.textContent = 'Thank you for your message. It has been sent.';
        output.classList.add('wpcf7-mail-sent-ok');
      } else {
        return res.json().then(function (body) {
          output.textContent = (body && body.errors && body.errors.length)
            ? body.errors.map(function (er) { return er.message; }).join(', ')
            : 'There was an error trying to send your message. Please try again later.';
          output.classList.add('wpcf7-mail-sent-ng');
        });
      }
    }).catch(function () {
      output.textContent = 'There was an error trying to send your message. Please try again later.';
      output.classList.add('wpcf7-mail-sent-ng');
    });
  });
})();
