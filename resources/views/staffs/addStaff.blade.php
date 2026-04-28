@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Add Staff')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker.min.css">
@endsection

@section('vendor-script')
  <script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/locales/bootstrap-datepicker.ms.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
  <script>
  document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // 0) UTILITIES
    // =========================================================
    async function fetchJson(url) {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    }

    function resetSelect(selectElem, placeholder, disabled = true) {
      if (!selectElem) return;
      selectElem.innerHTML = `<option value="">${placeholder}</option>`;
      selectElem.disabled = disabled;
    }

    // =========================================================
    // STEPPER INSTANCE (guna utk auto-lompat step bila invalid)
    // =========================================================
    let stepper = null;
    const stepperEl = document.querySelector('#addStaffStepper') || document.querySelector('#editStaffStepper');
    if (stepperEl && typeof Stepper !== 'undefined') {
      stepper = new Stepper(stepperEl, { linear: false });
    }

    function getLabelText(el){
      if (!el) return '';
      const id = el.getAttribute('id');
      let label = null;
      try {
        if (id) label = document.querySelector(`label[for="${CSS.escape(id)}"]`);
      } catch (e) {
        if (id) label = document.querySelector(`label[for="${id}"]`);
      }
      if (!label) label = el.closest('.form-floating')?.querySelector('label');
      return (label?.textContent || el.name || id || 'Field').replace(/\s*\*+\s*$/, '').trim();
    }

    function gotoStepOfElement(el){
      if (!stepper || !stepperEl || !el) return;
      const content = el.closest('.content');
      if (!content) return;
      const targetId = content.getAttribute('id');
      if (!targetId) return;

      const steps = [...stepperEl.querySelectorAll('.step')];
      const idx = steps.findIndex(s => s.getAttribute('data-target') === `#${targetId}`);
      if (idx >= 0) stepper.to(idx + 1);
    }

    // =========================================================
    // 1) AUTO DOB / NEGERI LAHIR / JANTINA (MYKAD)
    // =========================================================
    window.updateDOB = function (ic) {
      const dobInput     = document.getElementById("dob");
      const negeriInput  = document.getElementById("negeri_lahir");
      const jantinaInput = document.getElementById("jantina");
      if (!dobInput || !negeriInput || !jantinaInput) return;

      if (!ic || ic.length < 12) {
        dobInput.value = "";
        negeriInput.value = "";
        jantinaInput.value = "";
        return;
      }

      const yy = ic.substring(0, 2);
      const mm = ic.substring(2, 4);
      const dd = ic.substring(4, 6);

      const fullYear = parseInt(yy, 10) >= 50 ? "19" + yy : "20" + yy;
      dobInput.value = `${fullYear}-${mm}-${dd}`;

      const negeriCode = ic.substring(6, 8);
      const negeriMap = {
        "01":"Johor","21":"Johor","22":"Johor","23":"Johor","24":"Johor",
        "02":"Kedah","25":"Kedah","26":"Kedah","27":"Kedah",
        "03":"Kelantan","28":"Kelantan","29":"Kelantan",
        "04":"Melaka","30":"Melaka",
        "05":"Negeri Sembilan","31":"Negeri Sembilan","59":"Negeri Sembilan",
        "06":"Pahang","32":"Pahang","33":"Pahang",
        "07":"Pulau Pinang","34":"Pulau Pinang","35":"Pulau Pinang",
        "08":"Perak","36":"Perak","37":"Perak","38":"Perak","39":"Perak",
        "09":"Perlis","40":"Perlis",
        "10":"Selangor","41":"Selangor","42":"Selangor","43":"Selangor","44":"Selangor",
        "11":"Terengganu","45":"Terengganu","46":"Terengganu",
        "12":"Sabah","47":"Sabah","48":"Sabah","49":"Sabah",
        "13":"Sarawak","50":"Sarawak","51":"Sarawak","52":"Sarawak","53":"Sarawak",
        "14":"W.P. Kuala Lumpur","54":"W.P. Kuala Lumpur","55":"W.P. Kuala Lumpur","56":"W.P. Kuala Lumpur","57":"W.P. Kuala Lumpur",
        "15":"W.P. Labuan","58":"W.P. Labuan",
        "16":"W.P. Putrajaya",
        "82":"Negeri Tidak Diketahui",
        "83":"Lahir Diluar Negara",
        "84":"Pemastautin Tetap",
        "85":"Penduduk Tanpa Negara"
      };
      negeriInput.value = negeriMap[negeriCode] ?? "Tidak Diketahui";

      const lastDigit = parseInt(ic.substring(11, 12), 10);
      jantinaInput.value = (lastDigit % 2 === 0) ? "Perempuan" : "Lelaki";
    };

    const mykad = document.getElementById('mykad');
    if (mykad && mykad.value) window.updateDOB(mykad.value);

    // =========================================================
    // 2) BANGSA -> ETNIK
    // =========================================================
    (function initBangsaEtnik(){
      const bangsaSelect = document.getElementById('bangsa');
      const etnikSelect  = document.getElementById('etnik');
      if (!bangsaSelect || !etnikSelect) return;

      async function loadEtnik(idBangsa) {
        resetSelect(etnikSelect, '-- Pilih Etnik --', true);
        if (!idBangsa) return;

        try {
          const url = @json(url('/ajax/etnik-by-bangsa')) + "/" + encodeURIComponent(idBangsa);
          const data = await fetchJson(url);

          resetSelect(etnikSelect, '-- Pilih Etnik --', false);
          data.forEach(row => {
            const opt = document.createElement('option');
            opt.value = row.id_etnik;
            opt.textContent = row.etnik;
            etnikSelect.appendChild(opt);
          });
        } catch (e) {
          console.error('Gagal load etnik:', e);
          resetSelect(etnikSelect, '-- Pilih Etnik --', true);
        }
      }

      bangsaSelect.addEventListener('change', function(){
        loadEtnik(this.value);
      });
    })();

    // =========================================================
    // 3) PERJAWATAN DROPDOWNS
    // =========================================================
    (function initPerjawatanDropdowns(){
      const kumpSelect   = document.getElementById('id_kump_perkhidmatan');
      const klasSelect   = document.getElementById('id_klasifikasi_perkhidmatan');

      const skimSelect   = document.getElementById('id_skim_perkhidmatan');
      const kodSkimInput = document.getElementById('kod_skim_perkhidmatan');

      const gredSelect   = document.getElementById('id_gred');
      const jawatanSelect= document.getElementById('id_jawatan');

      if (!kumpSelect || !klasSelect || !skimSelect || !kodSkimInput || !gredSelect || !jawatanSelect) return;

      function resetSkim() {
        resetSelect(skimSelect, '-- Pilih Skim Perkhidmatan --', true);
        kodSkimInput.value = '';
      }
      function resetGred() {
        resetSelect(gredSelect, '-- Pilih Gred --', true);
      }
      function resetJawatan() {
        resetSelect(jawatanSelect, '-- Pilih Gelaran Jawatan di Kementerian --', true);
      }

      async function loadSkim() {
        resetSkim();
        const kump = (kumpSelect.value || '').trim();
        const klas = (klasSelect.value || '').trim();
        if (!kump || !klas) return;

        const url = `{{ route('ajax.skim-perkhidmatan') }}?kump=${encodeURIComponent(kump)}&klasifikasi=${encodeURIComponent(klas)}`;

        try {
          const data = await fetchJson(url);

          resetSelect(skimSelect, '-- Pilih Skim Perkhidmatan --', false);
          data.forEach(item => {
            const id  = item.id_skim_perkhidmatan ?? item.id;
            const txt = item.skim_perkhidmatan ?? item.nama ?? '';
            const kod = item.kod_skim_perkhidmatan ?? item.kod_skim ?? item.kod ?? '';

            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = txt;
            opt.dataset.kod = kod;
            skimSelect.appendChild(opt);
          });
        } catch (e) {
          console.error('Gagal load skim:', e);
          resetSkim();
        }
      }

      async function loadGred() {
        resetGred();
        const idKump = (kumpSelect.value || '').trim();
        if (!idKump) return;

        const url = "{{ route('ajax.gredByKump', ['id_kump' => '__IDKUMP__']) }}"
          .replace('__IDKUMP__', encodeURIComponent(idKump));

        try {
          const data = await fetchJson(url);

          resetSelect(gredSelect, '-- Pilih Gred --', false);
          data.forEach(item => {
            const id  = item.id_gred ?? item.id;
            const txt = item.gred ?? item.nama ?? '';
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = txt;
            gredSelect.appendChild(opt);
          });
        } catch (e) {
          console.error('Gagal load gred:', e);
          resetGred();
        }
      }

      async function loadJawatan() {
        resetJawatan();
        const kumpId = (kumpSelect.value || '').trim();
        if (!kumpId) return;

        const url = "{{ route('ajax.jawatan-by-kump', ['kump' => '__KUMP__']) }}"
          .replace('__KUMP__', encodeURIComponent(kumpId));

        try {
          const data = await fetchJson(url);

          resetSelect(jawatanSelect, '-- Pilih Gelaran Jawatan di Kementerian --', false);
          data.forEach(j => {
            const id  = j.id ?? j.id_jawatan;
            const txt = j.jawatan ?? j.nama ?? '';
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = txt;
            jawatanSelect.appendChild(opt);
          });
        } catch (e) {
          console.error('Gagal load jawatan:', e);
          resetJawatan();
        }
      }

      skimSelect.addEventListener('change', function(){
        const opt = skimSelect.options[skimSelect.selectedIndex];
        kodSkimInput.value = opt ? (opt.dataset.kod || '') : '';
      });

      kumpSelect.addEventListener('change', async function(){
        resetSkim(); resetGred(); resetJawatan();
        await loadGred();
        await loadJawatan();
        if ((klasSelect.value || '').trim()) await loadSkim();
      });

      klasSelect.addEventListener('change', async function(){
        resetSkim();
        if ((kumpSelect.value || '').trim()) await loadSkim();
      });

      resetSkim(); resetGred(); resetJawatan();
    })();

    // =========================================================
    // 4) PENEMPATAN: id_penempatan=2 -> paparkan in_out & required
    // =========================================================
    (function initPenempatanInOut(){
      const kategoriSelect = document.getElementById('id_penempatan');
      const inOutWrapper   = document.getElementById('in_out_dari_wrapper');
      const inOutInput     = document.getElementById('in_out');
      if (!kategoriSelect || !inOutWrapper || !inOutInput) return;

      function toggle() {
        const val = String(kategoriSelect.value || '');
        if (val === '2') {
          inOutWrapper.style.display = '';
          inOutInput.required = true;
        } else {
          inOutWrapper.style.display = 'none';
          inOutInput.required = false;
          inOutInput.value = '';
        }
      }

      kategoriSelect.addEventListener('change', toggle);
      toggle();
    })();

    // =========================================================
    // 5) AGENSI -> BAHAGIAN -> SEKSYEN -> CAWANGAN
    // =========================================================
    (function initPenempatanChain(){
      const agensiSelect   = document.getElementById('id_agensi');
      const bahagianSelect = document.getElementById('id_bahagian');
      const seksyenSelect  = document.getElementById('id_seksyen');
      const cawanganSelect = document.getElementById('id_cawangan');
      if (!agensiSelect || !bahagianSelect || !seksyenSelect || !cawanganSelect) return;

      const bahagianUrlTemplate = @json(route('ajax.bahagian.byAgensi', ['agensi' => 'AGENSI_ID']));
      const seksyenUrlTemplate  = @json(route('ajax.seksyen.byBahagian', ['bahagian' => 'BAHAGIAN_ID']));
      const cawanganUrlTemplate = @json(route('ajax.cawangan.bySeksyen', ['seksyen' => 'SEKSYEN_ID']));

      async function loadBahagian(agensiId) {
        resetSelect(bahagianSelect, '-- Pilih Bahagian --', true);
        resetSelect(seksyenSelect,  '-- Pilih Seksyen --', true);
        resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
        if (!agensiId) return;

        try {
          const url = bahagianUrlTemplate.replace('AGENSI_ID', encodeURIComponent(agensiId));
          const data = await fetchJson(url);

          resetSelect(bahagianSelect, '-- Pilih Bahagian --', false);
          data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.bahagian;
            bahagianSelect.appendChild(opt);
          });
        } catch (e) {
          console.error('Gagal load bahagian:', e);
        }
      }

      async function loadSeksyen(bahagianId) {
        resetSelect(seksyenSelect, '-- Pilih Seksyen --', true);
        resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
        if (!bahagianId) return;

        try {
          const url = seksyenUrlTemplate.replace('BAHAGIAN_ID', encodeURIComponent(bahagianId));
          const data = await fetchJson(url);

          resetSelect(seksyenSelect, '-- Pilih Seksyen --', false);
          data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id_seksyen;
            opt.textContent = item.seksyen;
            seksyenSelect.appendChild(opt);
          });
        } catch (e) {
          console.error('Gagal load seksyen:', e);
        }
      }

      async function loadCawangan(seksyenId) {
        resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
        if (!seksyenId) return;

        try {
          const url = cawanganUrlTemplate.replace('SEKSYEN_ID', encodeURIComponent(seksyenId));
          const data = await fetchJson(url);

          resetSelect(cawanganSelect, '-- Pilih Cawangan --', false);
          data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id_cawangan;
            opt.textContent = item.cawangan;
            cawanganSelect.appendChild(opt);
          });
        } catch (e) {
          console.error('Gagal load cawangan:', e);
        }
      }

      agensiSelect.addEventListener('change', function(){ loadBahagian(this.value); });
      bahagianSelect.addEventListener('change', function(){ loadSeksyen(this.value); });
      seksyenSelect.addEventListener('change', function(){ loadCawangan(this.value); });

      resetSelect(bahagianSelect, '-- Pilih Bahagian --', true);
      resetSelect(seksyenSelect,  '-- Pilih Seksyen --', true);
      resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
    })();

    // =========================================================
    // 6) DYNAMIC ROWS: AHLI KELUARGA (optional)
    // =========================================================
    (function initAhliKeluargaDynamic(){
      const container = document.getElementById('keluargaContainer');
      if (!container) return;

      const hubunganOptions = @json(
        collect($hubunganList ?? [])->map(fn($h) => [
          'id' => $h->id_hubungan,
          'text' => ($h->hubungan ?? '')
        ])->values()
      );

      function optionHtml() {
        return ['<option value="">-- Pilih Hubungan --</option>']
          .concat(hubunganOptions.map(o => `<option value="${o.id}">${o.text}</option>`))
          .join('');
      }

      function renumber() {
        const rows = container.querySelectorAll('.keluarga-row');
        rows.forEach((row, idx) => {
          row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/keluarga\[\d+\]/, `keluarga[${idx}]`);
          });
          const rm = row.querySelector('.btnRemoveAhli');
          if (rm) rm.disabled = (rows.length === 1);
        });
      }

      function addRow() {
        const idx = container.querySelectorAll('.keluarga-row').length;

        const html = `
          <div class="row g-3 keluarga-row align-items-end">
            <div class="col-md-5">
              <div class="form-floating form-floating-outline">
                <input type="text" name="keluarga[${idx}][nama]" class="form-control" placeholder="Nama ahli keluarga">
                <label>Nama</label>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-floating form-floating-outline">
                <input type="date" name="keluarga[${idx}][tarikh_lahir]" class="form-control">
                <label>Tarikh Lahir</label>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-floating form-floating-outline">
                <select name="keluarga[${idx}][id_hubungan]" class="form-select">
                  ${optionHtml()}
                </select>
                <label>Hubungan</label>
              </div>
            </div>

            <div class="col-md-1 d-flex gap-2 justify-content-end">
              <button type="button" class="btn btn-sm rounded-pill btn-outline-primary btnAddAhli" title="Tambah Ahli">
                <i class="mdi mdi-account-plus-outline"></i>
              </button>
              <button type="button" class="btn btn-sm rounded-pill btn-outline-danger btnRemoveAhli" title="Buang Ahli">
                <i class="mdi mdi-delete-outline"></i>
              </button>
            </div>
          </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        renumber();
      }

      container.addEventListener('click', function(e){
        const addBtn = e.target.closest('.btnAddAhli');
        if (addBtn) { e.preventDefault(); addRow(); return; }

        const rmBtn = e.target.closest('.btnRemoveAhli');
        if (rmBtn) {
          e.preventDefault();
          const rows = container.querySelectorAll('.keluarga-row');
          if (rows.length <= 1) return;
          rmBtn.closest('.keluarga-row')?.remove();
          renumber();
        }
      });

      renumber();
    })();

    // =========================================================
    // 7) DYNAMIC ROWS: KURSUS (optional)
    // =========================================================
    (function initKursusDynamic(){
      const container = document.getElementById('kursusContainer');
      if (!container) return;

      function renumber() {
        const rows = container.querySelectorAll('.kursus-row');
        rows.forEach((row, idx) => {
          row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/kursus\[\d+\]/, `kursus[${idx}]`);
          });
          const rm = row.querySelector('.btnRemoveKursus');
          if (rm) rm.disabled = (rows.length === 1);
        });
      }

      function addRow() {
        const idx = container.querySelectorAll('.kursus-row').length;
        const html = `
          <div class="row g-3 kursus-row align-items-end">
            <div class="col-md-5">
              <div class="form-floating form-floating-outline">
                <input type="text" name="kursus[${idx}][nama_kursus]" class="form-control" placeholder="Nama kursus"
                  oninput="this.value = this.value.replace(/\\b\\w/g, c => c.toUpperCase());">
                <label>Nama Kursus</label>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-floating form-floating-outline">
                <input type="text" name="kursus[${idx}][tempat_kursus]" class="form-control" placeholder="Tempat kursus"
                  oninput="this.value = this.value.replace(/\\b\\w/g, c => c.toUpperCase());">
                <label>Tempat Kursus</label>
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-floating form-floating-outline">
                <input type="date" name="kursus[${idx}][tarikh_kursus]" class="form-control">
                <label>Tarikh Kursus</label>
              </div>
            </div>

            <div class="col-md-1 d-flex gap-2 justify-content-end">
              <button type="button" class="btn btn-sm rounded-pill btn-outline-primary btnAddKursus" title="Tambah Kursus">
                <i class="mdi mdi-plus"></i>
              </button>
              <button type="button" class="btn btn-sm rounded-pill btn-outline-danger btnRemoveKursus" title="Buang Kursus">
                <i class="mdi mdi-delete-outline"></i>
              </button>
            </div>
          </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        renumber();
      }

      container.addEventListener('click', function(e){
        const addBtn = e.target.closest('.btnAddKursus');
        if (addBtn) { e.preventDefault(); addRow(); return; }

        const rmBtn = e.target.closest('.btnRemoveKursus');
        if (rmBtn) {
          e.preventDefault();
          const rows = container.querySelectorAll('.kursus-row');
          if (rows.length <= 1) return;
          rmBtn.closest('.kursus-row')?.remove();
          renumber();
        }
      });

      renumber();
    })();

    // =========================================================
    // 8) STATUS OKU (YA -> papar Jenis OKU)
    // =========================================================
    (function initOkuToggle(){
      const oku = document.getElementById('tambahan_oku');
      const wrapper = document.getElementById('jenis_oku_wrapper');
      const jenis = document.getElementById('tambahan_jenis_oku');
      if (!oku || !wrapper) return;

      function toggle() {
        const isYa = (oku.value === '1');
        wrapper.style.display = isYa ? '' : 'none';

        if (jenis) {
          jenis.required = isYa;
          if (!isYa) jenis.value = '';
        }
      }

      oku.addEventListener('change', toggle);
      toggle();
    })();

    // =========================================================
    // 9) STATUS KAHWIN: bila = 3, papar pasangan + required
    // =========================================================
    (function initPasanganToggle(){
      const statusKahwin = document.getElementById('id_status_kahwin');
      const wrapper = document.getElementById('pasangan_wrapper');

      const fNama     = document.getElementById('pasangan_nama');
      const fKerja    = document.getElementById('pasangan_pekerjaan');
      const fAlamat   = document.getElementById('pasangan_alamat_bertugas');
      const fTelPej   = document.getElementById('pasangan_notel_pej');
      const fTelHp    = document.getElementById('pasangan_notel_bimbit');

      if (!statusKahwin || !wrapper) return;

      const pasanganFields = [fNama, fKerja, fAlamat, fTelPej, fTelHp].filter(Boolean);

      function setRequired(isRequired) { pasanganFields.forEach(el => el.required = isRequired); }
      function clearFields() { pasanganFields.forEach(el => el.value = ''); }

      function toggle() {
        const isKahwin = (String(statusKahwin.value) === '3');
        wrapper.style.display = isKahwin ? '' : 'none';
        setRequired(isKahwin);
        if (!isKahwin) clearFields();
      }

      statusKahwin.addEventListener('change', toggle);
      toggle();
    })();

    // =========================================================
    // 10) STEPPER NAV
    // =========================================================
    (function initStepper(){
      if (!stepperEl || !stepper) return;

      stepperEl.addEventListener('click', function(e){
        const nextBtn = e.target.closest('.btn-next');
        const prevBtn = e.target.closest('.btn-prev');

        if (nextBtn) { e.preventDefault(); stepper.next(); }
        if (prevBtn) { e.preventDefault(); stepper.previous(); }
      });
    })();

    // =========================================================
    // 11) YEAR PICKER
    // =========================================================
    (function initYearPicker(){
      if (!window.jQuery || !jQuery.fn.datepicker) return;

      jQuery('.year-picker').each(function(){
        const $input = jQuery(this);
        const v = ($input.val() || '').trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(v)) $input.val(v.substring(0, 4));

        $input.datepicker({
          format: "yyyy",
          startView: 2,
          minViewMode: 2,
          autoclose: true,
          language: "ms"
        }).on('changeDate', function(e){
          const year = e.date.getFullYear();
          $input.val(String(year));
        });
      });
    })();

    document.addEventListener('click', function(e){
      const icon = e.target.closest('.input-group-text');
      if (!icon) return;

      const input = icon.closest('.input-group')?.querySelector('.year-picker');
      if (input) {
        input.focus();
        if (window.jQuery) jQuery(input).datepicker('show');
      }
    });

    // =========================================================
    // 12) PERJAWATAN RULES (FIX: RUN AFTER DOM READY)
    // =========================================================
    (function initPerjawatanRules(){
      const specialStatuses = ['3','4','5','6','7','11'];

      const status = document.getElementById('id_status_perkhidmatan');

      const kump   = document.getElementById('id_kump_perkhidmatan');
      const klas   = document.getElementById('id_klasifikasi_perkhidmatan');
      const skim   = document.getElementById('id_skim_perkhidmatan');
      const kodSkim= document.getElementById('kod_skim_perkhidmatan');
      const gred   = document.getElementById('id_gred');

      const tMula  = document.getElementById('tkh_lantikan_mula');
      const sMula  = document.getElementById('tkh_sah_mula');

      const tSek   = document.getElementById('tkh_lantikan_sekarang');
      const sSek   = document.getElementById('tkh_sah_sekarang');

      const taraf  = document.getElementById('taraf_berpencen');
      const jawatan= document.getElementById('id_jawatan');

      const rowKlasSkim   = document.getElementById('row_perjawatan_klas_skim');
      const rowTarikhMula = document.getElementById('row_perjawatan_tarikh_mula');
      const rowTaraf      = document.getElementById('row_perjawatan_taraf_berpencen');

      if (!status) return;

      const colKlas = klas?.closest('.col-sm-4');
      const colSkim = skim?.closest('.col-sm-4');
      const colKod  = kodSkim?.closest('.col-sm-2');
      const colGred = gred?.closest('.col-sm-2');

      function setRequired(el, isReq){
        if (!el) return;
        el.required = !!isReq;
      }

      function setDisabledClear(el, isDisabled){
        if (!el) return;
        el.disabled = !!isDisabled;
        if (isDisabled) el.value = '';
      }

      function show(el, isShow){
        if (!el) return;
        el.style.display = isShow ? '' : 'none';
      }

      function apply(){
        const v = String(status.value || '');
        const isSpecial = specialStatuses.includes(v);

        // wajib untuk semua
        setRequired(status, true);
        setRequired(kump, true);
        setRequired(gred, true);
        setRequired(tSek, true);
        setRequired(sSek, true);
        setRequired(jawatan, true);

        show(rowKlasSkim, true);

        // hide klas/skim/kod bila special
        if (colKlas) colKlas.style.display = isSpecial ? 'none' : '';
        if (colSkim) colSkim.style.display = isSpecial ? 'none' : '';
        if (colKod)  colKod.style.display  = isSpecial ? 'none' : '';
        if (colGred) colGred.style.display = '';

        // non-special wajib klas/skim
        setRequired(klas, !isSpecial);
        setRequired(skim, !isSpecial);

        setDisabledClear(klas, isSpecial);
        setDisabledClear(skim, isSpecial);
        setDisabledClear(kodSkim, isSpecial);

        // tarikh mula hanya non-special
        show(rowTarikhMula, !isSpecial);
        setRequired(tMula, !isSpecial);
        setRequired(sMula, !isSpecial);
        setDisabledClear(tMula, isSpecial);
        setDisabledClear(sMula, isSpecial);

        // taraf berpencen hanya non-special
        show(rowTaraf, !isSpecial);
        setRequired(taraf, !isSpecial);
        setDisabledClear(taraf, isSpecial);

        // untuk special: pastikan gred & jawatan enable (kalau asal disabled)
        if (isSpecial) {
          if (gred) gred.disabled = false;
          if (jawatan) jawatan.disabled = false;
        }
      }

      status.addEventListener('change', apply);
      apply();
    })();

    // =========================================================
    // 13) SUBMIT HANDLER + SWEETALERT LIST INVALID + AUTO STEP
    // =========================================================
    (function initSubmit(){
      const form = document.getElementById('addStaffForm');
      const submitBtn = document.getElementById('btnSubmitStaff');
      if (!form || !submitBtn) return;

      function doSubmit(e){
        if (e) { e.preventDefault(); e.stopPropagation(); }

        if (!form.checkValidity()) {
          const invalidEls = [...form.querySelectorAll(':invalid')].filter(el => !el.disabled);
          const first = invalidEls[0];
          if (first) {
            gotoStepOfElement(first);
            setTimeout(() => first.focus({ preventScroll:false }), 50);
          }

          const names = invalidEls.slice(0, 12).map(getLabelText);
          const extra = invalidEls.length > 12 ? `<br><small>+${invalidEls.length - 12} lagi...</small>` : '';

          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'warning',
              title: 'Maklumat belum lengkap',
              html: `<div style="text-align:left;">
                      <p>Sila lengkapkan ruangan wajib berikut:</p>
                      <ul>${names.map(n => `<li>${n}</li>`).join('')}</ul>
                      ${extra}
                    </div>`,
              confirmButtonText: 'Tutup'
            });
          } else {
            alert('Sila lengkapkan ruangan wajib.');
          }

          form.reportValidity();
          return;
        }

        submitBtn.disabled = true;
        const span = submitBtn.querySelector('span');
        if (span) span.textContent = 'Menghantar...';

        HTMLFormElement.prototype.submit.call(form);
      }

      submitBtn.addEventListener('click', doSubmit);
      form.addEventListener('submit', doSubmit);
    })();

  });
  </script>

  {{-- ✅ Papar SEMUA error validation (bukan mykad sahaja) --}}
  @if ($errors->any())
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal === 'undefined') return;

    const msgs = @json($errors->all());
    Swal.fire({
      icon: 'error',
      title: 'Makluman',
      html: `<div style="text-align:left;">
              <p>Sila semak semula maklumat berikut:</p>
              <ul>${msgs.map(m => `<li>${m}</li>`).join('')}</ul>
            </div>`,
      confirmButtonText: 'Tutup'
    });
  });
  </script>
@endif
@endsection

<style>
  .icon-pulse { animation: pulse 1.5s infinite; }
  @keyframes pulse {
    0%   { transform: scale(1); opacity: 0.9; }
    50%  { transform: scale(1.18); opacity: 1; }
    100% { transform: scale(1); opacity: 0.9; }
  }

  /* Besarkan icon dalam bs-stepper (mdi) */
  .bs-stepper-icon i {
    font-size: 34px;
    line-height: 1;
  }

  .step.active .bs-stepper-icon i {
    color: #696cff;
    transform: scale(1.15);
    transition: all 0.3s ease;
  }

  /* =========================================================
     ✅ FIX: FORM-FLOATING + INPUT-GROUP (YEAR PICKER)
     Label "Tahun" duduk atas border macam field lain
     ========================================================= */

  /* 1) Bagi container outline ikut theme (border di wrapper, bukan input) */
  .form-floating.form-floating-outline .input-group.input-group-merge{
    position: relative;
    border: 1px solid rgba(67, 89, 113, .2);
    border-radius: .375rem;
    background: #fff;
    overflow: hidden;
  }

  /* 2) Buang border input & icon sebab border dah kat wrapper */
  .form-floating.form-floating-outline .input-group.input-group-merge .form-control,
  .form-floating.form-floating-outline .input-group.input-group-merge .input-group-text{
    border: 0 !important;
    box-shadow: none !important;
  }

  /* 3) Samakan tinggi + ruang untuk floating label */
  .form-floating.form-floating-outline .input-group.input-group-merge .form-control{
    height: calc(2.9rem + 2px);
    padding-top: 1.625rem;   /* ruang label floating */
    padding-bottom: .625rem;
  }

  .form-floating.form-floating-outline .input-group.input-group-merge .input-group-text{
    height: calc(2.9rem + 2px);
    display: flex;
    align-items: center;
    background: transparent;
  }

  /* 4) Bagi label floating behave macam input biasa */
  .form-floating.form-floating-outline > label{
    z-index: 5;
    pointer-events: none;
  }

  /* 5) Trigger floating bila ada value / focus dalam input-group */
  .form-floating.form-floating-outline:focus-within > label,
  .form-floating.form-floating-outline .input-group:focus-within ~ label {
    opacity: 1;
  }

  /* 6) Highlight border bila focus */
  .form-floating.form-floating-outline .input-group.input-group-merge:focus-within{
    border-color: rgba(105, 108, 255, .6);
    box-shadow: 0 0 0 .2rem rgba(105,108,255,.12);
  }
</style>


@section('content')
  <h4 class="fw-semibold pt-3 mb-1">Tambah Pegawai</h4>
  <p class="mb-4">Sila isi maklumat pegawai baharu dengan lengkap.</p>

  <div class="row">
    <div class="col-12 mb-4">
      <div class="bs-stepper wizard-icons wizard-icons-example mt-2" id="addStaffStepper">

        <div class="bs-stepper-header">

          {{-- 1: PERIBADI --}}
          <div class="step active" data-target="#maklumat-peribadi">
            <button type="button" class="step-trigger" aria-selected="true">
              <span class="bs-stepper-icon">
                <i class="mdi mdi-account-circle-outline"></i>
              </span>
              <span class="bs-stepper-label">Peribadi</span>
            </button>
          </div>

          <div class="line"><i class="mdi mdi-chevron-right"></i></div>

          {{-- 2: KELUARGA --}}
          <div class="step" data-target="#maklumat-keluarga">
            <button type="button" class="step-trigger" aria-selected="false">
              <span class="bs-stepper-icon">
                <i class="mdi mdi-account-group-outline"></i>
              </span>
              <span class="bs-stepper-label">Keluarga</span>
            </button>
          </div>

          <div class="line"><i class="mdi mdi-chevron-right"></i></div>

          {{-- 3: PERJAWATAN --}}
          <div class="step" data-target="#maklumat-perjawatan">
            <button type="button" class="step-trigger" aria-selected="false">
              <span class="bs-stepper-icon">
                <i class="mdi mdi-briefcase-outline"></i>
              </span>
              <span class="bs-stepper-label">Perjawatan</span>
            </button>
          </div>

          <div class="line"><i class="mdi mdi-chevron-right"></i></div>

          {{-- 4: KELAYAKAN --}}
          <div class="step" data-target="#maklumat-kelayakan">
            <button type="button" class="step-trigger" aria-selected="false">
              <span class="bs-stepper-icon">
                <i class="mdi mdi-school-outline"></i>
              </span>
              <span class="bs-stepper-label">Kelayakan</span>
            </button>
          </div>

          <div class="line"><i class="mdi mdi-chevron-right"></i></div>

          {{-- 5: PENEMPATAN --}}
          <div class="step" data-target="#maklumat-penempatan">
            <button type="button" class="step-trigger" aria-selected="false">
              <span class="bs-stepper-icon">
                <i class="mdi mdi-map-marker-outline"></i>
              </span>
              <span class="bs-stepper-label">Penempatan</span>
            </button>
          </div>

          <div class="line"><i class="mdi mdi-chevron-right"></i></div>

          {{-- 6: TAMBAHAN --}}
          <div class="step" data-target="#maklumat-tambahan">
            <button type="button" class="step-trigger" aria-selected="false">
              <span class="bs-stepper-icon">
                <i class="mdi mdi-dots-horizontal-circle-outline"></i>
              </span>
              <span class="bs-stepper-label">Tambahan</span>
            </button>
          </div>

        </div>

        <div class="bs-stepper-content">
          <form action="{{ route('staffs.store') }}" method="POST" id="addStaffForm">
            @csrf

            {{-- =======================
                1) MAKLUMAT PERIBADI
                ======================= --}}
            <div id="maklumat-peribadi" class="content active">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Peribadi</h6>
                <small>Masukkan maklumat peribadi pegawai.</small>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select name="id_gelaran" id="id_gelaran" class="form-select">
                      <option value="">-- Pilih Gelaran --</option>
                      @foreach ($gelaran as $g)
                        <option value="{{ $g->id_gelaran }}">{{ $g->gelaran }}</option>
                      @endforeach
                    </select>
                    <label for="id_gelaran">Gelaran <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-8">
                  <div class="form-floating form-floating-outline">
                    <input type="text" name="nama" id="nama" class="form-control" placeholder="Aminah Binti Ahmad"
                      oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="nama">Nama Penuh <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" name="mykad" id="mykad" class="form-control" placeholder="xxxxxxxxxxxx"
                      maxlength="12"
                      oninput="this.value = this.value.replace(/[^0-9]/g, ''); updateDOB(this.value);">
                    <label for="mykad">No. Kad Pengenalan <span class="text-danger">*</span></label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="date" id="dob" name="tarikh_lahir" class="form-control" readonly>
                    <label for="tarikh_lahir">Tarikh Lahir</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="negeri_lahir" name="negeri_lahir" class="form-control" readonly>
                    <label for="negeri_lahir">Negeri Lahir</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="jantina" name="jantina" class="form-control" readonly>
                    <label for="jantina">Jantina</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select id="bangsa" name="id_bangsa" class="form-control">
                      <option value="">-- Pilih Bangsa --</option>
                      @foreach($bangsa as $b)
                        <option value="{{ $b->id_bangsa }}">{{ $b->bangsa }}</option>
                      @endforeach
                    </select>
                    <label for="bangsa">Bangsa <span class="text-danger">*</span></label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select id="etnik" name="id_etnik" class="form-control" disabled>
                      <option value="">-- Pilih Etnik --</option>
                    </select>
                    <label for="etnik">Etnik / Kaum <span class="text-danger">*</span></label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select id="agama" name="id_agama" class="form-control">
                      <option value="">-- Pilih Agama --</option>
                      @foreach($agama as $a)
                        <option value="{{ $a->id_agama }}">{{ $a->agama }}</option>
                      @endforeach
                    </select>
                    <label for="agama">Agama <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select name="id_status_kahwin" id="id_status_kahwin" class="form-select">
                      <option value="">-- Pilih Status --</option>
                      @foreach($statusKahwin as $s)
                        <option value="{{ $s->id_status_kahwin }}">{{ $s->status_kahwin }}</option>
                      @endforeach
                    </select>
                    <label for="id_status_kahwin">Status Perkahwinan <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-8">
                  <div class="form-floating form-floating-outline">
                    <!-- <input type="text" name="alamat" id="alamat" class="form-control" placeholder="Alamat"
                      oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());"> -->
                    <textarea name="alamat" class="form-control" placeholder="Alamat" style="height: 120px;" required></textarea>
                    <label for="alamat">Alamat <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="no_telefon" name="no_telefon"
                      class="form-control"
                      placeholder="0380918000"
                      maxlength="15"
                      oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    <label for="no_telefon">No. Telefon</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="no_hp" name="no_hp"
                      class="form-control"
                      placeholder="0123456789"
                      maxlength="15"
                      oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    <label for="no_hp">No. Telefon Bimbit <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-5">
                  <div class="form-floating form-floating-outline">
                    <input type="email" id="emel" name="emel" class="form-control"
                      placeholder="example@perpaduan.gov.my"
                      oninput="this.value = this.value.toLowerCase();"
                      pattern="^[a-z0-9._%+-]+@perpaduan\.gov\.my$"
                      title="Hanya emel @perpaduan.gov.my dibenarkan">
                    <label for="emel">Emel</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select name="id_mydid" id="id_mydid" class="form-select">
                      <option value="">-- Pilih --</option>
                      @foreach ($mydid as $m)
                        <option value="{{ $m->id_mydid }}">{{ $m->status_mydid }}</option>
                      @endforeach
                    </select>
                    <label for="id_mydid">Status MyDigital ID <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="col-12 d-flex justify-content-between mt-4">
                <button type="button" class="btn rounded-pill btn-label-secondary btn-prev" disabled>
                  <i class="mdi mdi-arrow-left me-sm-1"></i>
                  <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                </button>

                <button type="button" class="btn rounded-pill btn-label-primary btn-next">
                  <span class="align-middle d-sm-inline-block d-none me-sm-1">Seterusnya</span>
                  <i class="mdi mdi-arrow-right"></i>
                </button>
              </div>
            </div>

            {{-- =======================
                2) MAKLUMAT KELUARGA
                ======================= --}}
            <div id="maklumat-keluarga" class="content">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Keluarga</h6>
                <small>Masukkan maklumat pasangan, ahli keluarga dan waris.</small>
              </div>

              <br>
              <div id="pasangan_wrapper" style="display:none;">
                <div class="content-header mb-3">
                  <h6 class="mb-0">Maklumat Pasangan</h6>
                  <small class="text-muted">Wajib diisi jika Status Perkahwinan = Berkahwin</small>
                </div>

                <div class="row g-4 mb-3">
                  <div class="col-sm-8">
                    <div class="form-floating form-floating-outline">
                      <input type="text"
                            name="pasangan[nama]"
                            id="pasangan_nama"
                            class="form-control"
                            placeholder="Nama pasangan"
                            oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                      <label for="pasangan_nama">Nama Pasangan <span class="text-danger">*</span></label>
                    </div>
                  </div>
                </div>

                <div class="row g-4 mb-3">
                  <div class="col-md-4">
                    <div class="form-floating form-floating-outline">
                      <input type="text"
                            name="pasangan[pekerjaan]"
                            id="pasangan_pekerjaan"
                            class="form-control"
                            placeholder="Pekerjaan pasangan"
                            oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                      <label for="pasangan_pekerjaan">Pekerjaan Pasangan <span class="text-danger">*</span></label>
                    </div>
                  </div>
                </div>

                <div class="row g-4 mb-3">
                  <div class="col-8">
                    <div class="form-floating form-floating-outline">
                      <textarea name="pasangan[alamat_bertugas]"
                                id="pasangan_alamat_bertugas"
                                class="form-control"
                                placeholder="Alamat bertugas"
                                style="height: 120px;"></textarea>
                      <label for="pasangan_alamat_bertugas">Alamat Tempat Bertugas <span class="text-danger">*</span></label>
                    </div>
                  </div>
                </div>

                <div class="row g-4 mb-3">
                  <div class="col-md-3">
                    <div class="form-floating form-floating-outline">
                      <input type="text"
                            name="pasangan[notel_pej]"
                            id="pasangan_notel_pej"
                            class="form-control"
                            placeholder="03xxxxxxxx"
                            maxlength="15"
                            oninput="this.value = this.value.replace(/[^0-9]/g,'');">
                      <label for="pasangan_notel_pej">No. Telefon Pejabat <span class="text-danger">*</span></label>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-floating form-floating-outline">
                      <input type="text"
                            name="pasangan[notel_bimbit]"
                            id="pasangan_notel_bimbit"
                            class="form-control"
                            placeholder="01xxxxxxxx"
                            maxlength="15"
                            oninput="this.value = this.value.replace(/[^0-9]/g,'');">
                      <label for="pasangan_notel_bimbit">No. Telefon Bimbit <span class="text-danger">*</span></label>
                    </div>
                  </div>
                </div>

                <hr class="my-4">
              </div>

              <div class="content-header mb-3">
                <h6 class="mb-0">Senarai Nama Ahli Keluarga</h6>
                <small>Klik butang <b>+</b> untuk tambah ahli keluarga.</small>
              </div>

              {{-- LIST AHLI KELUARGA --}}
              <div class="card-body p-0">
                <div id="keluargaContainer" class="d-flex flex-column gap-3">

                  {{-- Row pertama --}}
                  <div class="row g-3 keluarga-row align-items-end">
                    <div class="col-md-5">
                      <div class="form-floating form-floating-outline">
                        <input type="text" name="keluarga[0][nama]" class="form-control" placeholder="Nama ahli keluarga">
                        <label>Nama <span class="text-danger">*</span></label>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-floating form-floating-outline">
                        <input type="date" name="keluarga[0][tarikh_lahir]" class="form-control">
                        <label>Tarikh Lahir <span class="text-danger">*</span></label>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-floating form-floating-outline">
                        <select name="keluarga[0][id_hubungan]" class="form-select">
                          <option value="">-- Pilih Hubungan --</option>
                          @foreach(($hubunganList ?? []) as $h)
                            <option value="{{ $h->id_hubungan }}">{{ $h->hubungan }}</option>
                          @endforeach
                        </select>
                        <label>Hubungan <span class="text-danger">*</span></label>
                      </div>
                    </div>

                    {{-- BUTTON + & DELETE sebelah-sebelah --}}
                    <div class="col-md-1 d-flex gap-2 justify-content-end">
                      <button type="button"
                              class="btn btn-sm rounded-pill btn-outline-primary btnAddAhli"
                              title="Tambah Ahli">
                        <i class="mdi mdi-account-plus-outline"></i>
                      </button>

                      <button type="button"
                              class="btn btn-sm rounded-pill btn-outline-danger btnRemoveAhli"
                              title="Buang Ahli"
                              disabled>
                        <i class="mdi mdi-delete-outline"></i>
                      </button>
                    </div>
                  </div>

                </div>
              </div>

              <hr class="my-4">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Waris (Kecemasan)</h6>
                <small>Masukkan maklumat waris yang mudah untuk dihubungi semasa berlakunya kecemasan.</small>
              </div>

              {{-- WARIS --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-8">
                  <div class="form-floating form-floating-outline">
                    <input type="text"
                          name="waris[nama_waris]"
                          id="waris_nama"
                          class="form-control"
                          placeholder="Nama waris"
                          oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="waris_nama">Nama Waris <span class="text-danger">*</span></label>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text"
                          name="waris[notel_waris]"
                          id="waris_notel"
                          class="form-control"
                          placeholder="No. telefon waris">
                    <label for="waris_notel">No. Telefon Waris <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-8">
                  <div class="form-floating form-floating-outline">
                    <textarea name="waris[alamat_waris]"
                              id="waris_alamat"
                              class="form-control"
                              placeholder="Alamat waris"
                              style="height: 120px;"></textarea>
                    <label for="waris_alamat">Alamat Waris <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="col-12 d-flex justify-content-between mt-4">
                <button type="button" class="btn rounded-pill btn-label-secondary btn-prev">
                  <i class="mdi mdi-arrow-left me-sm-1"></i>
                  <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                </button>

                <button type="button" class="btn rounded-pill btn-label-primary btn-next">
                  <span class="align-middle d-sm-inline-block d-none me-sm-1">Seterusnya</span>
                  <i class="mdi mdi-arrow-right"></i>
                </button>
              </div>
            </div>

            {{-- =======================
                3) MAKLUMAT PERJAWATAN
                ======================= --}}
            <div id="maklumat-perjawatan" class="content">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Perjawatan</h6>
                <small>Masukkan maklumat perjawatan pegawai.</small>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select name="id_status_perkhidmatan" id="id_status_perkhidmatan" class="form-select">
                      <option value="">-- Pilih Status Perkhidmatan --</option>
                      @foreach ($statusPerkhidmatan as $s)
                        <option value="{{ $s->id_status_perkhidmatan }}">{{ $s->status_perkhidmatan }}</option>
                      @endforeach
                    </select>
                    <label for="id_status_perkhidmatan">Status Perkhidmatan <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select name="id_kump_perkhidmatan" id="id_kump_perkhidmatan" class="form-select">
                      <option value="">-- Pilih Kumpulan Perkhidmatan --</option>
                      @foreach ($kumpPerkhidmatan as $k)
                        <option value="{{ $k->id_kump_perkhidmatan }}">{{ $k->kump_perkhidmatan }}</option>
                      @endforeach
                    </select>
                    <label for="id_kump_perkhidmatan">Kumpulan Perkhidmatan <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <!-- <div class="row g-4 mb-3"> -->
              <div class="row g-4 mb-3" id="row_perjawatan_klas_skim">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select id="id_klasifikasi_perkhidmatan" name="id_klasifikasi_perkhidmatan" class="form-select">
                      <option value="">-- Pilih Klasifikasi Perkhidmatan --</option>
                      @foreach ($klasifikasiPerkhidmatan as $k)
                        <option value="{{ $k->id_klasifikasi_perkhidmatan }}">
                          {{ $k->kod_klasifikasi_perkhidmatan }} - {{ $k->klasifikasi_perkhidmatan }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_klasifikasi_perkhidmatan">Klasifikasi Perkhidmatan <span class="text-danger">*</span></label>
                  </div>
                </div>

                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select name="id_skim_perkhidmatan" id="id_skim_perkhidmatan" class="form-select" disabled>
                      <option value="">-- Pilih Skim Perkhidmatan --</option>
                    </select>
                    <label for="id_skim_perkhidmatan">Skim Perkhidmatan <span class="text-danger">*</span></label>
                  </div>
                </div>

                <div class="col-sm-2">
                  <div class="form-floating form-floating-outline">
                    <input type="text" name="kod_skim_perkhidmatan" id="kod_skim_perkhidmatan"
                           class="form-control" placeholder="Kod Skim" readonly>
                    <label for="kod_skim_perkhidmatan">Kod Skim Perkhidmatan <span class="text-danger">*</span></label>
                  </div>
                </div>

                <div class="col-sm-2">
                  <div class="form-floating form-floating-outline">
                    <select name="id_gred" id="id_gred" class="form-select" disabled>
                      <option value="">-- Pilih Gred --</option>
                    </select>
                    <label for="id_gred">Gred Skim Perkhidmatan <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <br>
              <!-- <div class="row g-4 mb-3"> -->
              <div class="row g-4 mb-3" id="row_perjawatan_tarikh_mula">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="date" name="tkh_lantikan_mula" id="tkh_lantikan_mula"
                          class="form-control" value="{{ old('tkh_lantikan_mula') }}">
                    <label for="tkh_lantikan_mula">Tarikh Lantikan (Pertama) </label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="date" name="tkh_sah_mula" id="tkh_sah_mula"
                          class="form-control" value="{{ old('tkh_sah_mula') }}">
                    <label for="tkh_sah_mula">Tarikh Sah (Pertama)</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="date" name="tkh_lantikan_sekarang" id="tkh_lantikan_sekarang"
                          class="form-control" value="{{ old('tkh_lantikan_sekarang') }}">
                    <label for="tkh_lantikan_sekarang">Tarikh Lantikan (Sekarang) <span class="text-danger">*</span></label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="date" name="tkh_sah_sekarang" id="tkh_sah_sekarang"
                          class="form-control" value="{{ old('tkh_sah_sekarang') }}">
                    <label for="tkh_sah_sekarang">Tarikh Sah (Sekarang) <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <br>
              <!-- <div class="row g-4 mb-3"> -->
              <div class="row g-4 mb-3" id="row_perjawatan_taraf_berpencen">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select name="taraf_berpencen" id="taraf_berpencen" class="form-select" required>
                      <option value="">-- Pilih Taraf Berpencen --</option>
                      <option value="1" {{ old('taraf_berpencen') === '1' ? 'selected' : '' }}>Ya</option>
                      <option value="0" {{ old('taraf_berpencen') === '0' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    <label for="taraf_berpencen">Taraf Berpencen <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select name="id_jawatan" id="id_jawatan" class="form-select" disabled>
                      <option value="">-- Pilih Gelaran Jawatan di Kementerian --</option>
                    </select>
                    <label for="id_jawatan">Gelaran Jawatan di Kementerian <span class="text-danger">*</span></label>
                  </div>
                </div>
              </div>

              <div class="col-12 d-flex justify-content-between mt-4">
                <button type="button" class="btn rounded-pill btn-label-secondary btn-prev">
                  <i class="mdi mdi-arrow-left me-sm-1"></i>
                  <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                </button>

                <button type="button" class="btn rounded-pill btn-label-primary btn-next">
                  <span class="align-middle d-sm-inline-block d-none me-sm-1">Seterusnya</span>
                  <i class="mdi mdi-arrow-right"></i>
                </button>
              </div>
            </div>

            {{-- =======================
                4) MAKLUMAT KELAYAKAN
                ======================= --}}
            <div id="maklumat-kelayakan" class="content">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Kelayakan</h6>
                <small>Masukkan maklumat kelayakan pegawai (sebelum & semasa perkhidmatan) dan kursus.</small>
              </div>

              {{-- ====== KELAYAKAN SEBELUM PERKHIDMATAN ====== --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <input type="text"
                          name="kelayakan[kelulusan_sebelum]"
                          id="kelulusan_sebelum"
                          class="form-control"
                          placeholder="Contoh: Diploma Kejuruteraan Elektrik"
                          value="{{ old('kelayakan.kelulusan_sebelum') }}"
                          oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="kelulusan_sebelum">Kelulusan Tertinggi Sebelum Perkhidmatan</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text"
                          name="kelayakan[institusi_sebelum]"
                          id="institusi_sebelum"
                          class="form-control"
                          placeholder="Kod/Institusi"
                          maxlength="10"
                          value="{{ old('kelayakan.institusi_sebelum') }}">
                    <label for="institusi_sebelum">Institusi/ Pusat Pengajian</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <div class="input-group input-group-merge">
                      <input type="text"
                        name="kelayakan[tahun_sebelum]"
                        id="tahun_sebelum"
                        class="form-control year-picker"
                        value="{{ old('kelayakan.tahun_sebelum') }}"
                        autocomplete="off">

                      <span class="input-group-text bg-transparent">
                        <i class="mdi mdi-calendar-month-outline"></i>
                      </span>
                    </div>
                    <label for="tahun_sebelum">Tahun</label>
                  </div>
                </div>
              </div>

              {{-- ====== KELAYAKAN SELEPAS PERKHIDMATAN ====== --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <input type="text"
                          name="kelayakan[kelulusan_selepas]"
                          id="kelulusan_selepas"
                          class="form-control"
                          placeholder="Contoh: Ijazah Sarjana"
                          value="{{ old('kelayakan.kelulusan_selepas') }}"
                          oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="kelulusan_selepas">Kelulusan Diperolehi Semasa Dalam Perkhidmatan</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text"
                          name="kelayakan[institusi_selepas]"
                          id="institusi_selepas"
                          class="form-control"
                          placeholder="Kod/Institusi"
                          maxlength="10"
                          value="{{ old('kelayakan.institusi_selepas') }}">
                    <label for="institusi_selepas">Institusi/ Pusat Pengajian</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <div class="input-group input-group-merge">
                      <input type="text"
                        name="kelayakan[tahun_selepas]"
                        id="tahun_selepas"
                        class="form-control year-picker"
                        value="{{ old('kelayakan.tahun_selepas') }}"
                        autocomplete="off">

                      <span class="input-group-text bg-transparent">
                        <i class="mdi mdi-calendar-month-outline"></i>
                      </span>
                    </div>
                    <label for="tahun_selepas">Tahun</label>
                  </div>
                </div>

              </div>

              <hr class="my-4">

              {{-- ====== KURSUS (p_kursus) ====== --}}
              <div class="content-header mb-2 mt-2">
                <h6 class="mb-0">Kursus Yang Pernah Diikuti Dalam Tahun Semasa</h6>
                <small>Klik butang <b>+</b> untuk tambah kursus (jika ada).</small>
              </div>

              <div class="card-body p-0">
                <div id="kursusContainer" class="d-flex flex-column gap-3 mb-4">

                  {{-- Row pertama --}}
                  <div class="row g-3 kursus-row align-items-end">
                    <div class="col-md-5">
                      <div class="form-floating form-floating-outline">
                        <input type="text"
                              name="kursus[0][nama_kursus]"
                              class="form-control"
                              placeholder="Nama kursus"
                              value="{{ old('kursus.0.nama_kursus') }}"
                              oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                        <label>Nama Kursus</label>
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-floating form-floating-outline">
                        <input type="text"
                              name="kursus[0][tempat_kursus]"
                              class="form-control"
                              placeholder="Tempat kursus"
                              value="{{ old('kursus.0.tempat_kursus') }}"
                              oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                        <label>Tempat Kursus</label>
                      </div>
                    </div>

                    <div class="col-md-2">
                      <div class="form-floating form-floating-outline">
                        <input type="date"
                              name="kursus[0][tarikh_kursus]"
                              class="form-control"
                              value="{{ old('kursus.0.tarikh_kursus') }}">
                        <label>Tarikh Kursus</label>
                      </div>
                    </div>

                    <div class="col-md-1 d-flex gap-2 justify-content-end">
                      <button type="button"
                              class="btn btn-sm rounded-pill btn-outline-primary btnAddKursus"
                              title="Tambah Kursus">
                        <i class="mdi mdi-plus"></i>
                      </button>

                      <button type="button"
                              class="btn btn-sm rounded-pill btn-outline-danger btnRemoveKursus"
                              title="Buang Kursus"
                              disabled>
                        <i class="mdi mdi-delete-outline"></i>
                      </button>
                    </div>
                  </div>

                </div>
              </div>

              <hr class="my-4">

              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <input type="text"
                          name="kelayakan[kursus_diperlukan]"
                          id="kursus_diperlukan"
                          class="form-control"
                          placeholder="Contoh: Kursus Pengurusan Projek"
                          value="{{ old('kelayakan.kursus_diperlukan') }}"
                          oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="kursus_diperlukan">Kursus Yang Ingin Diikuti Di Masa Akan Datang</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <input type="text"
                          name="kelayakan[pengkhususan]"
                          id="pengkhususan"
                          class="form-control"
                          placeholder="Contoh: Kejuruteraan Elektrik / ICT / Pentadbiran"
                          value="{{ old('kelayakan.pengkhususan') }}"
                          oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="pengkhususan">Pengkhususan Yang Diminati</label>
                  </div>
                </div>
              </div>

              <div class="col-12 d-flex justify-content-between mt-4">
                <button type="button" class="btn rounded-pill btn-label-secondary btn-prev">
                  <i class="mdi mdi-arrow-left me-sm-1"></i>
                  <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                </button>

                <button type="button" class="btn rounded-pill btn-label-primary btn-next">
                  <span class="align-middle d-sm-inline-block d-none me-sm-1">Seterusnya</span>
                  <i class="mdi mdi-arrow-right"></i>
                </button>
              </div>
            </div>

            {{-- =======================
                5) MAKLUMAT PENEMPATAN
                ======================= --}}
            <div id="maklumat-penempatan" class="content">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Penempatan</h6>
                <small>Masukkan maklumat penempatan pegawai.</small>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select id="id_penempatan" name="id_penempatan" class="form-select">
                      <option value="">-- Pilih Kategori Penempatan --</option>
                      @foreach ($penempatan as $p)
                        <option value="{{ $p->id_penempatan }}">{{ $p->penempatan }}</option>
                      @endforeach
                    </select>
                    <label for="id_penempatan">Kategori Penempatan</label>
                  </div>
                </div>

                <div class="col-sm-8" id="in_out_dari_wrapper" style="display:none;">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="in_out" name="in_out" class="form-control" placeholder="Dari Kementerian/Jabatan">
                    <label for="in_out">Dari Kementerian/Jabatan</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <input type="date" id="tarikh_masuk" name="tarikh_masuk" class="form-control" required>
                    <label for="tarikh_masuk">Tarikh Masuk Kementerian</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <select name="id_agensi" id="id_agensi" class="form-select">
                      <option value="">-- Pilih Agensi --</option>
                      @foreach ($agensi as $a)
                        <option value="{{ $a->id }}">{{ $a->agensi }}</option>
                      @endforeach
                    </select>
                    <label for="id_agensi">Kementerian/Agensi</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <select name="id_bahagian" id="id_bahagian" class="form-select" disabled>
                      <option value="">-- Pilih Bahagian --</option>
                    </select>
                    <label for="id_bahagian">Bahagian</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <select name="id_seksyen" id="id_seksyen" class="form-select" disabled>
                      <option value="">-- Pilih Seksyen --</option>
                    </select>
                    <label for="id_seksyen">Seksyen</label>
                  </div>
                </div>
              </div>

              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <select name="id_cawangan" id="id_cawangan" class="form-select" disabled>
                      <option value="">-- Pilih Cawangan --</option>
                    </select>
                    <label for="id_cawangan">Cawangan</label>
                  </div>
                </div>
              </div>

              <div class="col-12 d-flex justify-content-between mt-4">
                <button type="button" class="btn rounded-pill btn-label-secondary btn-prev">
                  <i class="mdi mdi-arrow-left me-sm-1 me-0"></i>
                  <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                </button>

                <button type="button" class="btn rounded-pill btn-label-primary btn-next">
                  <span class="align-middle d-sm-inline-block d-none me-sm-1">Seterusnya</span>
                  <i class="mdi mdi-arrow-right"></i>
                </button>
              </div>
            </div>

           {{-- =======================
              6) MAKLUMAT TAMBAHAN
              ======================= --}}
          <div id="maklumat-tambahan" class="content">
            <div class="content-header mb-3">
              <h6 class="mb-0">Maklumat Tambahan</h6>
              <small>Masukkan maklumat tambahan pegawai (jika berkaitan).</small>
            </div>

            {{-- ===== MAKLUMAT GAJI & INSURANS ===== --}}
            <div class="row g-4 mb-3">
              <div class="col-sm-3">
                <div class="form-floating form-floating-outline">
                  <input type="text"
                        name="tambahan[no_gaji]"
                        id="tambahan_no_gaji"
                        class="form-control"
                        placeholder="No Gaji">
                  <label for="tambahan_no_gaji">No. Gaji</label>
                </div>
              </div>

              <div class="col-sm-3">
                <div class="form-floating form-floating-outline">
                  <input type="text"
                        name="tambahan[no_kwsp]"
                        id="tambahan_no_kwsp"
                        class="form-control"
                        placeholder="No KWSP">
                  <label for="tambahan_no_kwsp">No. KWSP</label>
                </div>
              </div>
            </div>

            <hr class="my-4">
            
            <div class="row g-4 mb-3">
              <div class="col-sm-3">
                <div class="form-floating form-floating-outline">
                  <input type="text"
                        name="tambahan[no_ins]"
                        id="tambahan_no_ins"
                        class="form-control"
                        placeholder="No Insurans">
                  <label for="tambahan_no_ins">No. Polisi</label>
                </div>
              </div>
            </div>

            <div class="row g-4 mb-4">
              <div class="col-sm-6">
                <div class="form-floating form-floating-outline">
                  <input type="text"
                        name="tambahan[nama_ins]"
                        id="tambahan_nama_ins"
                        class="form-control"
                        placeholder="Nama Insurans">
                  <label for="tambahan_nama_ins">Nama Polisi</label>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- ===== STATUS OKU ===== --}}
            <div class="row g-4 mb-3">
              <div class="col-sm-3">
                <div class="form-floating form-floating-outline">
                  <select name="tambahan[oku]"
                          id="tambahan_oku"
                          class="form-select">
                    <option value="">-- Pilih --</option>
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                  </select>
                  <label for="tambahan_oku">Status Kecacatan/ OKU</label>
                </div>
              </div>
            </div>
            
            <div class="row g-4 mb-3">
              <div class="col-sm-5" id="jenis_oku_wrapper" style="display:none;">
                <div class="form-floating form-floating-outline">
                  <input type="text"
                        name="tambahan[jenis_oku]"
                        id="tambahan_jenis_oku"
                        class="form-control"
                        placeholder="Jenis Kecacatan/ OKU">
                  <label for="tambahan_jenis_oku">Nyatakan Jenis Kecacatan/ OKU</label>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- ===== STATUS KUARTERS ===== --}}
            <div class="row g-4 mb-3">
              <div class="col-sm-3">
                <div class="form-floating form-floating-outline">
                  <select name="tambahan[status_kuarters]"
                          id="tambahan_status_kuarters"
                          class="form-select">
                    <option value="">-- Pilih --</option>
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                  </select>
                  <label for="tambahan_status_kuarters">Menduduki Kuarters</label>
                </div>
              </div>
            </div>


            <hr class="my-4">

            {{-- ===== MINAT & AKTIVITI ===== --}}
            <div class="row g-4 mb-3">
              <div class="col-sm-4">
                <div class="form-floating form-floating-outline">
                  <input type="text"
                        name="tambahan[persatuan]"
                        id="tambahan_persatuan"
                        class="form-control"
                        placeholder="Persatuan/ Kesatuan">
                  <label for="tambahan_persatuan">Persatuan/ Kesatuan Yang Diceburi</label>
                </div>
              </div>

              <div class="col-sm-4">
                <div class="form-floating form-floating-outline">
                  <input type="text"
                        name="tambahan[sukan]"
                        id="tambahan_sukan"
                        class="form-control"
                        placeholder="Sukan">
                  <label for="tambahan_sukan">Sukan Yang Diminati</label>
                </div>
              </div>

              <div class="col-sm-4">
                <div class="form-floating form-floating-outline">
                  <input type="text"
                        name="tambahan[hobi]"
                        id="tambahan_hobi"
                        class="form-control"
                        placeholder="Hobi">
                  <label for="tambahan_hobi">Hobi</label>
                </div>
              </div>
            </div>

            {{-- ALERT --}}
            <div class="alert alert-warning d-flex align-items-center mt-4"
                style="border-radius:100px; font-size:13px;">
              <i class="mdi mdi-alert icon-pulse me-3"
                style="font-size:28px; color:#ff6f00;"></i>
              <span>Sila pastikan semua maklumat pegawai telah diisi dengan tepat dan disahkan sebelum penghantaran.</span>
            </div>

            {{-- BUTTON --}}
            <div class="col-12 d-flex justify-content-between mt-4">
              <button type="button" class="btn rounded-pill btn-label-secondary btn-prev">
                <i class="mdi mdi-arrow-left me-sm-1"></i>
                <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
              </button>

              <button type="button"
                      id="btnSubmitStaff"
                      class="btn rounded-pill btn-label-primary btn-submit">
                <span class="align-middle d-sm-inline-block d-none me-sm-1">Hantar</span>
                <i class="mdi mdi-send"></i>
              </button>
            </div>
          </div>


          </form>
        </div>

      </div>
    </div>
  </div>
@endsection
