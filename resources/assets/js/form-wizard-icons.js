/**
 * Form Wizard (Fixed)
 */
'use strict';

$(function () {
  const select2 = $('.select2');
  const selectPicker = $('.selectpicker');

  if (selectPicker.length) {
    selectPicker.selectpicker();
  }

  if (select2.length) {
    select2.each(function () {
      const $this = $(this);
      $this.wrap('<div class="position-relative"></div>');
      $this.select2({
        placeholder: '-- Pilih --',
        dropdownParent: $this.parent()
      });
    });
  }
});

(function () {
  // ==============================
  // Helper: validate current step only
  // ==============================
  function validateCurrentStep(wizardEl) {
    const activeStep = wizardEl.querySelector('.bs-stepper-content .content.active');
    if (!activeStep) return true;

    // ambil semua input/select/textarea dalam step semasa
    const fields = activeStep.querySelectorAll('input, select, textarea');

    for (const field of fields) {
      // skip yang disabled / hidden (wrapper display:none)
      const style = window.getComputedStyle(field);
      if (field.disabled || style.display === 'none') continue;

      // HTML5 required validation
      if (!field.checkValidity()) {
        field.reportValidity();
        field.focus();
        return false;
      }
    }
    return true;
  }

  // ==============================
  // Icons Wizard (yang Pak Abu guna)
  // ==============================
  const wizardIcons = document.querySelector('.wizard-icons-example');

  if (wizardIcons) {
    const btnNextList = wizardIcons.querySelectorAll('.btn-next');
    const btnPrevList = wizardIcons.querySelectorAll('.btn-prev');
    const btnSubmit = wizardIcons.querySelector('.btn-submit');

    const stepper = new Stepper(wizardIcons, { linear: false });

    // NEXT (validate step semasa sebelum next)
    btnNextList.forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        if (!validateCurrentStep(wizardIcons)) return;
        stepper.next();
      });
    });

    // PREV
    btnPrevList.forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        stepper.previous();
      });
    });

    // SUBMIT (validate semua + submit)
    if (btnSubmit) {
      btnSubmit.addEventListener('click', e => {
        e.preventDefault();

        const form =
          document.getElementById('addStaffForm') ||
          document.getElementById('editStaffForm');
        if (!form) return;

        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        btnSubmit.disabled = true;

        const labelSpan = btnSubmit.querySelector('span');
        if (labelSpan) labelSpan.textContent = 'Menghantar...';

        const icon = btnSubmit.querySelector('i');
        if (icon) icon.className = 'mdi mdi-loading mdi-spin';

        HTMLFormElement.prototype.submit.call(form);
      });
    }
  }

  // ==============================
  // Wizard lain (kalau tak guna, boleh buang terus)
  // ==============================
  // Jika Pak Abu memang tak guna class ini di page lain, boleh padam blok bawah:
  const others = [
    '.wizard-vertical-icons-example',
    '.wizard-modern-icons-example',
    '.wizard-modern-vertical-icons-example'
  ];

  others.forEach(selector => {
    const el = document.querySelector(selector);
    if (!el) return;

    const btnNextList = el.querySelectorAll('.btn-next');
    const btnPrevList = el.querySelectorAll('.btn-prev');
    const btnSubmit = el.querySelector('.btn-submit');

    const stepper = new Stepper(el, { linear: false });

    btnNextList.forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        if (!validateCurrentStep(el)) return;
        stepper.next();
      });
    });

    btnPrevList.forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        stepper.previous();
      });
    });

    if (btnSubmit) {
      btnSubmit.addEventListener('click', e => {
        e.preventDefault();

        const form =
          document.getElementById('addStaffForm') ||
          document.getElementById('editStaffForm');
        if (!form) return;


        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        btnSubmit.disabled = true;

        const labelSpan = btnSubmit.querySelector('span');
        if (labelSpan) labelSpan.textContent = 'Menghantar...';

        const icon = btnSubmit.querySelector('i');
        if (icon) icon.className = 'mdi mdi-loading mdi-spin';

        HTMLFormElement.prototype.submit.call(form);
      });
    }
  });
})();
