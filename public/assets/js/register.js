/* Registration form — reveal only the fieldset for the chosen role.
   Server-side validation is authoritative; this is a convenience only. */
(function () {
  'use strict';

  var form = document.querySelector('[data-register-form]');
  if (!form) return;

  var sections = form.querySelectorAll('[data-role-section]');
  var radios = form.querySelectorAll('[data-role-radio]');

  function sync() {
    var role = (form.querySelector('[data-role-radio]:checked') || {}).value;
    sections.forEach(function (section) {
      var show = section.getAttribute('data-role-section') === role;
      section.hidden = !show;
      section.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.disabled = !show;
      });
    });
    form.querySelectorAll('label:has([data-role-radio])').forEach(function (label) {
      var checked = label.querySelector('[data-role-radio]').checked;
      label.classList.toggle('border-brand-600', checked);
      label.classList.toggle('bg-brand-50', checked);
      label.classList.toggle('border-gray-300', !checked);
    });
  }

  radios.forEach(function (radio) {
    radio.addEventListener('change', sync);
  });
  sync();
})();
