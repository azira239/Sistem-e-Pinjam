@php
  $isEdit = $isEdit ?? true;
  $configData = Helper::appClasses();

  /**
   * ✅ Elak clash variable
   * $penempatanSemasa  = rekod penempatan terkini (1 row) dari controller
   * $lkpPenempatan12   = lookup kategori penempatan (collection) id 1/2 dari controller
   */
  $penempatanSemasa = $penempatanSemasa ?? ($penempatanCurrent ?? null);

  // Prefill penempatan utk JS chain dropdown
  $selectedAgensi   = old('id_agensi', optional($penempatanSemasa)->id_agensi ?? '');
  $selectedBahagian = old('id_bahagian', optional($penempatanSemasa)->id_bahagian ?? '');
  $selectedSeksyen  = old('id_seksyen', optional($penempatanSemasa)->id_seksyen ?? '');
  $selectedCawangan = old('id_cawangan', optional($penempatanSemasa)->id_cawangan ?? '');
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Kemaskini Pegawai')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('vendor-script')
  <script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
  <script src="{{ asset('assets/js/form-wizard-icons.js') }}"></script>

  <script>
    /* =========================================
       EDIT STAFF - FULL JS (sebiji macam addStaff + prefill)
       ========================================= */

    document.addEventListener('DOMContentLoaded', function () {

      /* ========= STEP 0: STEPPER ========= */
      (function initStepper(){
        const el = document.querySelector('#editStaffStepper');
        if (!el || typeof Stepper === 'undefined') return;

        const stepper = new Stepper(el);

        el.querySelectorAll('.btn-next').forEach(btn => btn.addEventListener('click', () => stepper.next()));
        el.querySelectorAll('.btn-prev').forEach(btn => btn.addEventListener('click', () => stepper.previous()));
      })();

      /* ========= AUTO DOB/NEGERI/JANTINA ========= */
      window.updateDOB = function updateDOB(ic) {
        const dobInput     = document.getElementById("dob");
        const negeriInput  = document.getElementById("negeri_lahir");
        const jantinaInput = document.getElementById("jantina");
        if (!dobInput || !negeriInput || !jantinaInput) return;

        if ((ic || '').length < 12) {
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

      /* ========= BANGSA -> ETNIK (prefill) ========= */
      (function bangsaToEtnik(){
        const bangsaSelect = document.getElementById('bangsa');
        const etnikSelect  = document.getElementById('etnik');
        if (!bangsaSelect || !etnikSelect) return;

        const preEtnik = @json(old('id_etnik', $staff->id_etnik ?? ''));

        function resetEtnik(disabled = false) {
          etnikSelect.innerHTML = '<option value="">-- Pilih Etnik --</option>';
          etnikSelect.disabled = disabled;
        }

        async function loadEtnik(idBangsa, preselect = null) {
          resetEtnik(false);
          if (!idBangsa) return;

          try {
            const url = @json(url('/ajax/etnik-by-bangsa')) + '/' + encodeURIComponent(idBangsa);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            let html = '<option value="">-- Pilih Etnik --</option>';
            data.forEach(row => {
              const selected = (preselect !== null && String(preselect) === String(row.id_etnik)) ? 'selected' : '';
              html += `<option value="${row.id_etnik}" ${selected}>${row.etnik}</option>`;
            });

            etnikSelect.innerHTML = html;
          } catch (e) {
            console.error('Gagal load etnik:', e);
            resetEtnik(false);
          }
        }

        bangsaSelect.addEventListener('change', () => loadEtnik(bangsaSelect.value, null));

        if (bangsaSelect.value) loadEtnik(bangsaSelect.value, preEtnik);
        else resetEtnik(false);
      })();

      /* ========= KUMP + KLAS -> SKIM + AUTO KOD SKIM (prefill) ========= */
      (function kumpKlasToSkim(){
        const kumpSelect = document.getElementById('id_kump_perkhidmatan');
        const klasSelect = document.getElementById('id_klasifikasi_perkhidmatan');
        const skimSelect = document.getElementById('id_skim_perkhidmatan');
        const kodInput   = document.getElementById('kod_skim_perkhidmatan'); // ✅ mesti wujud
        if (!kumpSelect || !klasSelect || !skimSelect) return;

        const preSkim = @json(old('id_skim_perkhidmatan', optional($perjawatan)->id_skim_perkhidmatan ?? ''));

        function resetSkim(disabled = true) {
          skimSelect.innerHTML = '<option value="">-- Pilih Skim Perkhidmatan --</option>';
          skimSelect.disabled = disabled;
          if (kodInput) kodInput.value = '';
        }

        function setKodFromSelected(){
          if (!kodInput) return;
          const opt = skimSelect.options[skimSelect.selectedIndex];
          kodInput.value = opt ? (opt.dataset.kod || '') : '';
        }

        async function loadSkim(preselectValue = null) {
          resetSkim(true);

          const kump = kumpSelect.value;
          const klas = klasSelect.value;
          if (!kump || !klas) return;

          const url = @json(route('ajax.skim-perkhidmatan')) +
            `?kump=${encodeURIComponent(kump)}&klasifikasi=${encodeURIComponent(klas)}`;

          try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            // ✅ build option + simpan kod dalam data-kod
            skimSelect.innerHTML = '<option value="">-- Pilih Skim Perkhidmatan --</option>';
            data.forEach(item => {
              const opt = document.createElement('option');
              opt.value = item.id_skim_perkhidmatan;
              opt.textContent = item.skim_perkhidmatan;
              opt.dataset.kod = item.kod_skim_perkhidmatan || ''; // ✅ paling penting
              if (preselectValue !== null && String(preselectValue) === String(item.id_skim_perkhidmatan)) {
                opt.selected = true;
              }
              skimSelect.appendChild(opt);
            });

            skimSelect.disabled = false;

            // ✅ bila prefill, terus isi kod
            setKodFromSelected();

          } catch (e) {
            console.error('Gagal load skim:', e);
            resetSkim(true);
          }
        }

        // bila user pilih skim, auto isi kod
        skimSelect.addEventListener('change', setKodFromSelected);

        // bila kump/klas berubah, refresh skim
        kumpSelect.addEventListener('change', () => loadSkim(null));
        klasSelect.addEventListener('change', () => loadSkim(null));

        // init
        resetSkim(true);
        if (kumpSelect.value && klasSelect.value) loadSkim(preSkim);
      })();

      /* ========= KUMP -> GRED (prefill) ========= */
      (function kumpToGred(){
        const kumpSelect = document.getElementById('id_kump_perkhidmatan');
        const gredSelect = document.getElementById('id_gred');
        if (!kumpSelect || !gredSelect) return;

        const preGred = @json(old('id_gred', optional($perjawatan)->id_gred ?? ''));

        function resetGred(disabled = true) {
          gredSelect.innerHTML = '<option value="">-- Pilih Gred --</option>';
          gredSelect.disabled = disabled;
        }

        async function loadGred(preselectValue = null) {
          resetGred(true);
          const idKump = kumpSelect.value;
          if (!idKump) return;

          const url = @json(route('ajax.gredByKump', ['id_kump' => '__IDKUMP__']))
            .replace('__IDKUMP__', encodeURIComponent(idKump));

          try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            let html = '<option value="">-- Pilih Gred --</option>';
            data.forEach(item => {
              const selected = (preselectValue !== null && String(preselectValue) === String(item.id_gred)) ? 'selected' : '';
              html += `<option value="${item.id_gred}" ${selected}>${item.gred}</option>`;
            });

            gredSelect.innerHTML = html;
            gredSelect.disabled = false;
          } catch (e) {
            console.error('Gagal load gred:', e);
            resetGred(true);
          }
        }

        kumpSelect.addEventListener('change', () => loadGred(null));

        resetGred(true);
        if (kumpSelect.value) loadGred(preGred);
      })();

      /* ========= KUMP -> JAWATAN (prefill) ========= */
      (function kumpToJawatan(){
        const kumpSelect = document.getElementById('id_kump_perkhidmatan');
        const jawatanSelect = document.getElementById('id_jawatan');
        if (!kumpSelect || !jawatanSelect) return;

        const preJawatan = @json(old('id_jawatan', optional($penempatanSemasa)->id_jawatan ?? ''));

        function resetJawatan(disabled = true) {
          jawatanSelect.innerHTML = '<option value="">-- Pilih Jawatan di Kementerian --</option>';
          jawatanSelect.disabled = disabled;
        }

        async function loadJawatan(preselectValue = null) {
          resetJawatan(true);

          const kumpId = kumpSelect.value;
          if (!kumpId) return;

          const url = @json(route('ajax.jawatan-by-kump', ['kump' => '__KUMP__']))
            .replace('__KUMP__', encodeURIComponent(kumpId));

          try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            let html = '<option value="">-- Pilih Jawatan di Kementerian --</option>';
            data.forEach(item => {
              const selected = (preselectValue !== null && String(preselectValue) === String(item.id)) ? 'selected' : '';
              html += `<option value="${item.id}" ${selected}>${item.jawatan}</option>`;
            });

            jawatanSelect.innerHTML = html;
            jawatanSelect.disabled = false;
          } catch (e) {
            console.error('Gagal load jawatan:', e);
            resetJawatan(true);
          }
        }

        kumpSelect.addEventListener('change', () => loadJawatan(null));

        resetJawatan(true);
        if (kumpSelect.value) loadJawatan(preJawatan);
      })();

      /* ========= PENEMPATAN = 2 -> show in_out ========= */
      (function penempatanToggle(){
        const kategoriSelect = document.getElementById('id_penempatan');
        const inOutWrapper   = document.getElementById('in_out_dari_wrapper');
        const inOutInput     = document.getElementById('in_out');
        if (!kategoriSelect || !inOutWrapper || !inOutInput) return;

        function toggle() {
          const val = kategoriSelect.value;
          inOutWrapper.style.display = 'none';
          inOutInput.required = false;

          if (val === '2') {
            inOutWrapper.style.display = '';
            inOutInput.required = true;
          } else {
            // jangan padam value kalau edit (optional)
            // inOutInput.value = '';
          }
        }
        kategoriSelect.addEventListener('change', toggle);
        toggle();
      })();

      /* ========= AGENSI -> BAHAGIAN -> SEKSYEN -> CAWANGAN (prefill) ========= */
      (function chainPenempatan(){
        const agensiSelect   = document.getElementById('id_agensi');
        const bahagianSelect = document.getElementById('id_bahagian');
        const seksyenSelect  = document.getElementById('id_seksyen');
        const cawanganSelect = document.getElementById('id_cawangan');
        if (!agensiSelect || !bahagianSelect || !seksyenSelect || !cawanganSelect) return;

        const preAgensi   = @json($selectedAgensi);
        const preBahagian = @json($selectedBahagian);
        const preSeksyen  = @json($selectedSeksyen);
        const preCawangan = @json($selectedCawangan);

        const bahagianUrlTemplate = @json(route('ajax.bahagian.byAgensi', ['agensi' => 'AGENSI_ID']));
        const seksyenUrlTemplate  = @json(route('ajax.seksyen.byBahagian', ['bahagian' => 'BAHAGIAN_ID']));
        const cawanganUrlTemplate = @json(route('ajax.cawangan.bySeksyen', ['seksyen' => 'SEKSYEN_ID']));

        function resetSelect(sel, placeholder, disabled = true) {
          sel.innerHTML = `<option value="">${placeholder}</option>`;
          sel.disabled = disabled;
        }

        async function loadBahagian(agensiId, preselect = null) {
          resetSelect(bahagianSelect, '-- Pilih Bahagian --', true);
          resetSelect(seksyenSelect,  '-- Pilih Seksyen --',  true);
          resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
          if (!agensiId) return;

          const res = await fetch(bahagianUrlTemplate.replace('AGENSI_ID', encodeURIComponent(agensiId)));
          const data = await res.json();

          let html = '<option value="">-- Pilih Bahagian --</option>';
          data.forEach(item => {
            const selected = (preselect !== null && String(preselect) === String(item.id)) ? 'selected' : '';
            html += `<option value="${item.id}" ${selected}>${item.bahagian}</option>`;
          });

          bahagianSelect.innerHTML = html;
          bahagianSelect.disabled = false;

          if (preselect) await loadSeksyen(preselect, preSeksyen);
        }

        async function loadSeksyen(bahagianId, preselect = null) {
          resetSelect(seksyenSelect,  '-- Pilih Seksyen --',  true);
          resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
          if (!bahagianId) return;

          const res = await fetch(seksyenUrlTemplate.replace('BAHAGIAN_ID', encodeURIComponent(bahagianId)));
          const data = await res.json();

          let html = '<option value="">-- Pilih Seksyen --</option>';
          data.forEach(item => {
            const selected = (preselect !== null && String(preselect) === String(item.id_seksyen)) ? 'selected' : '';
            html += `<option value="${item.id_seksyen}" ${selected}>${item.seksyen}</option>`;
          });

          seksyenSelect.innerHTML = html;
          seksyenSelect.disabled = false;

          if (preselect) await loadCawangan(preselect, preCawangan);
        }

        async function loadCawangan(seksyenId, preselect = null) {
          resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
          if (!seksyenId) return;

          const res = await fetch(cawanganUrlTemplate.replace('SEKSYEN_ID', encodeURIComponent(seksyenId)));
          const data = await res.json();

          let html = '<option value="">-- Pilih Cawangan --</option>';
          data.forEach(item => {
            const selected = (preselect !== null && String(preselect) === String(item.id_cawangan)) ? 'selected' : '';
            html += `<option value="${item.id_cawangan}" ${selected}>${item.cawangan}</option>`;
          });

          cawanganSelect.innerHTML = html;
          cawanganSelect.disabled = false;
        }

        agensiSelect.addEventListener('change', () => loadBahagian(agensiSelect.value, null));
        bahagianSelect.addEventListener('change', () => loadSeksyen(bahagianSelect.value, null));
        seksyenSelect.addEventListener('change', () => loadCawangan(seksyenSelect.value, null));

        // init prefill
        if (preAgensi) {
          agensiSelect.value = preAgensi;
          loadBahagian(preAgensi, preBahagian);
        } else {
          resetSelect(bahagianSelect, '-- Pilih Bahagian --', true);
          resetSelect(seksyenSelect,  '-- Pilih Seksyen --',  true);
          resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
        }
      })();

      /* ========= SUBMIT BUTTON ========= */
      (function submitHook(){
        const form = document.getElementById('editStaffForm');
        const submitBtn = document.getElementById('btnSubmitStaff');
        if (!form || !submitBtn) return;

        submitBtn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopImmediatePropagation();

          if (!form.checkValidity()) {
            form.reportValidity();
            return;
          }

          submitBtn.disabled = true;
          const span = submitBtn.querySelector('span');
          if (span) span.textContent = 'Menghantar...';

          HTMLFormElement.prototype.submit.call(form);
        }, true);
      })();

    });
    </script>


@endsection

<style>
  .icon-pulse { animation: pulse 1.5s infinite; }
  @keyframes pulse {
    0%   { transform: scale(1); opacity: 0.9; }
    50%  { transform: scale(1.18); opacity: 1; }
    100% { transform: scale(1); opacity: 0.9; }
  }
</style>

@section('content')
  <h4 class="fw-semibold pt-3 mb-1">Kemaskini Rekod Pegawai</h4>
  <p class="mb-4">Sila kemaskini rekod pegawai dengan lengkap.</p>

  <div class="row">
    <div class="col-12 mb-4">

      {{-- ✅ tambah id untuk init stepper --}}
      <div class="bs-stepper wizard-icons wizard-icons-example mt-2" id="editStaffStepper">

        {{-- HEADER WIZARD --}}
        <div class="bs-stepper-header">

          {{-- ✅ ACTIVE step pertama --}}
          <div class="step active" data-target="#maklumat-peribadi">
            <button type="button" class="step-trigger" aria-selected="true">
              <span class="bs-stepper-icon">
                <svg viewBox="0 0 54 54">
                  <use xlink:href='{{ asset('assets/svg/icons/form-wizard-account.svg#wizardAccount') }}'></use>
                </svg>
              </span>
              <span class="bs-stepper-label">Maklumat Peribadi</span>
            </button>
          </div>

          <div class="line"><i class="mdi mdi-chevron-right"></i></div>

          <div class="step" data-target="#maklumat-perjawatan">
            <button type="button" class="step-trigger" aria-selected="false">
              <span class="bs-stepper-icon">
                <svg viewBox="0 0 58 54">
                  <use xlink:href='{{ asset('assets/svg/icons/form-wizard-personal.svg#wizardPersonal') }}'></use>
                </svg>
              </span>
              <span class="bs-stepper-label">Maklumat Perjawatan</span>
            </button>
          </div>

          <div class="line"><i class="mdi mdi-chevron-right"></i></div>

          <div class="step" data-target="#maklumat-penempatan">
            <button type="button" class="step-trigger" aria-selected="false">
              <span class="bs-stepper-icon">
                <svg viewBox="0 0 54 54">
                  <use xlink:href='{{ asset('assets/svg/icons/form-wizard-address.svg#wizardAddress') }}'></use>
                </svg>
              </span>
              <span class="bs-stepper-label">Maklumat Penempatan</span>
            </button>
          </div>
        </div>

        {{-- CONTENT --}}
        <div class="bs-stepper-content">
          <form action="{{ route('staffs.update', $staff->id_staff) }}" method="POST" id="editStaffForm">
            @csrf
            @method('PUT')

            {{-- ERROR GLOBAL --}}
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            {{-- =======================
                STEP 1: MAKLUMAT PERIBADI
                ======================= --}}
            {{-- ✅ ACTIVE content pertama --}}
            <div id="maklumat-peribadi" class="content active">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Peribadi</h6>
                <small>Masukkan maklumat peribadi pegawai.</small>
              </div>

              {{-- GELARAN --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select name="id_gelaran" id="id_gelaran" class="form-select">
                      <option value="">-- Pilih Gelaran --</option>
                      @foreach ($gelaran as $g)
                        <option value="{{ $g->id_gelaran }}"
                          @selected(old('id_gelaran', $staff->id_gelaran) == $g->id_gelaran)>
                          {{ $g->gelaran }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_gelaran">Gelaran</label>
                  </div>
                </div>
              </div>

              {{-- NAMA --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-8">
                  <div class="form-floating form-floating-outline">
                    <input type="text" name="nama" id="nama" class="form-control"
                      placeholder="Aminah Binti Ahmad"
                      value="{{ old('nama', $staff->nama) }}"
                      oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="nama">Nama Penuh</label>
                  </div>
                </div>
              </div>

              {{-- MYKAD + AUTO DOB/NEGERI/JANTINA --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" name="mykad" id="mykad" class="form-control"
                      placeholder="xxxxxxxxxxxx" maxlength="12"
                      value="{{ old('mykad', $staff->mykad) }}"
                      oninput="this.value = this.value.replace(/[^0-9]/g, ''); updateDOB(this.value);">
                    <label for="mykad">No. Kad Pengenalan</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="date" id="dob" name="tarikh_lahir" class="form-control"
                      value="{{ old('tarikh_lahir', $staff->tarikh_lahir) }}" readonly>
                    <label for="dob">Tarikh Lahir</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="negeri_lahir" name="negeri_lahir" class="form-control"
                      value="{{ old('negeri_lahir', $staff->negeri_lahir) }}" readonly>
                    <label for="negeri_lahir">Negeri Lahir</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="jantina" name="jantina" class="form-control"
                      value="{{ old('jantina', $staff->jantina) }}" readonly>
                    <label for="jantina">Jantina</label>
                  </div>
                </div>
              </div>

              {{-- BANGSA/ETNIK/AGAMA --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select id="bangsa" name="id_bangsa" class="form-control">
                      <option value="">-- Pilih Bangsa --</option>
                      @foreach($bangsa as $b)
                        <option value="{{ $b->id_bangsa }}"
                          @selected(old('id_bangsa', $staff->id_bangsa) == $b->id_bangsa)>
                          {{ $b->bangsa }}
                        </option>
                      @endforeach
                    </select>
                    <label for="bangsa">Bangsa</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select id="etnik" name="id_etnik" class="form-control">
                      <option value="">-- Pilih Etnik --</option>
                      {{-- diisi ajax + prefill --}}
                    </select>
                    <label for="etnik">Etnik / Kaum</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select id="agama" name="id_agama" class="form-control">
                      <option value="">-- Pilih Agama --</option>
                      @foreach($agama as $a)
                        <option value="{{ $a->id_agama }}"
                          @selected(old('id_agama', $staff->id_agama) == $a->id_agama)>
                          {{ $a->agama }}
                        </option>
                      @endforeach
                    </select>
                    <label for="agama">Agama</label>
                  </div>
                </div>
              </div>

              {{-- STATUS KAHWIN --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select name="id_status_kahwin" id="id_status_kahwin" class="form-select">
                      <option value="">-- Pilih Status --</option>
                      @foreach($statusKahwin as $s)
                        <option value="{{ $s->id_status_kahwin }}"
                          @selected(old('id_status_kahwin', $staff->id_status_kahwin) == $s->id_status_kahwin)>
                          {{ $s->status_kahwin }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_status_kahwin">Status Perkahwinan</label>
                  </div>
                </div>
              </div>

              {{-- ALAMAT --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-8">
                  <div class="form-floating form-floating-outline">
                    <input type="text" name="alamat" id="alamat" class="form-control"
                      value="{{ old('alamat', $staff->alamat) }}"
                      placeholder="Alamat"
                      oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="alamat">Alamat</label>
                  </div>
                </div>
              </div>

              {{-- TELEFON --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="no_telefon" name="no_telefon" class="form-control"
                      placeholder="0380918000" maxlength="15"
                      value="{{ old('no_telefon', $staff->no_telefon) }}"
                      oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    <label for="no_telefon">No. Telefon</label>
                  </div>
                </div>

                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="no_hp" name="no_hp" class="form-control"
                      placeholder="0123456789" maxlength="15"
                      value="{{ old('no_hp', $staff->no_hp) }}"
                      oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    <label for="no_hp">No. Telefon Bimbit</label>
                  </div>
                </div>
              </div>

              {{-- EMEL --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-5">
                  <div class="form-floating form-floating-outline">
                    <input type="email" id="emel" name="emel" class="form-control"
                      placeholder="example@perpaduan.gov.my"
                      value="{{ old('emel', $staff->emel) }}"
                      oninput="this.value = this.value.toLowerCase();"
                      pattern="^[a-z0-9._%+-]+@perpaduan\.gov\.my$"
                      title="Hanya emel @perpaduan.gov.my dibenarkan">
                    <label for="emel">Emel</label>
                  </div>
                </div>
              </div>

              {{-- ✅ MYDID (guna lkp_mydid) --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-3">
                  <div class="form-floating form-floating-outline">
                    <select name="id_mydid" id="id_mydid" class="form-select">
                      <option value="">-- Pilih --</option>
                      @foreach ($mydid as $m)
                        <option value="{{ $m->id_mydid }}"
                          @selected(old('id_mydid', $staff->id_mydid) == $m->id_mydid)>
                          {{ $m->status_mydid }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_mydid">Status MyDigital ID</label>
                  </div>
                </div>
              </div>

              <br>

              <div class="col-12 d-flex justify-content-between">
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
                STEP 2: MAKLUMAT PERJAWATAN
                ======================= --}}
            <div id="maklumat-perjawatan" class="content">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Perjawatan</h6>
                <small>Masukkan maklumat perjawatan pegawai.</small>
              </div>

              {{-- STATUS PERKHIDMATAN --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select name="id_status_perkhidmatan" id="id_status_perkhidmatan" class="form-select">
                      <option value="">-- Pilih Status Perkhidmatan --</option>
                      @foreach ($statusPerkhidmatan as $s)
                        <option value="{{ $s->id_status_perkhidmatan }}"
                          @selected(old('id_status_perkhidmatan', optional($perjawatan)->id_status_perkhidmatan) == $s->id_status_perkhidmatan)>
                          {{ $s->status_perkhidmatan }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_status_perkhidmatan">Status Perkhidmatan</label>
                  </div>
                </div>
              </div>

              {{-- KUMP PERKHIDMATAN --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select name="id_kump_perkhidmatan" id="id_kump_perkhidmatan" class="form-select">
                      <option value="">-- Pilih Kumpulan Perkhidmatan --</option>
                      @foreach ($kumpPerkhidmatan as $k)
                        <option value="{{ $k->id_kump_perkhidmatan }}"
                          @selected(old('id_kump_perkhidmatan', optional($perjawatan)->id_kump_perkhidmatan) == $k->id_kump_perkhidmatan)>
                          {{ $k->kump_perkhidmatan }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_kump_perkhidmatan">Kumpulan Perkhidmatan</label>
                  </div>
                </div>
              </div>

              {{-- KLASIFIKASI + SKIM + GRED --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select id="id_klasifikasi_perkhidmatan" name="id_klasifikasi_perkhidmatan" class="form-select">
                      <option value="">-- Pilih Klasifikasi Perkhidmatan --</option>
                      @foreach ($klasifikasiPerkhidmatan as $k)
                        <option value="{{ $k->id_klasifikasi_perkhidmatan }}"
                          @selected(old('id_klasifikasi_perkhidmatan', optional($perjawatan)->id_klasifikasi_perkhidmatan) == $k->id_klasifikasi_perkhidmatan)>
                          {{ $k->kod_klasifikasi_perkhidmatan }} - {{ $k->klasifikasi_perkhidmatan }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_klasifikasi_perkhidmatan">Klasifikasi Perkhidmatan</label>
                  </div>
                </div>

                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select id="id_skim_perkhidmatan" name="id_skim_perkhidmatan" class="form-select" disabled>
                      <option value="">-- Pilih Skim Perkhidmatan --</option>
                      {{-- diisi ajax + prefill --}}
                    </select>
                    <label for="id_skim_perkhidmatan">Skim Perkhidmatan</label>
                  </div>
                </div>

                <div class="col-sm-2">
                  <div class="form-floating form-floating-outline">
                    <input type="text" name="kod_skim_perkhidmatan" id="kod_skim_perkhidmatan"
                           class="form-control" placeholder="Kod Skim" readonly>
                    <label for="kod_skim_perkhidmatan">Kod Skim Perkhidmatan</label>
                  </div>
                </div>

                <div class="col-sm-2">
                  <div class="form-floating form-floating-outline">
                    <select name="id_gred" id="id_gred" class="form-select" disabled>
                      <option value="">-- Pilih Gred --</option>
                      {{-- diisi ajax + prefill --}}
                    </select>
                    <label for="id_gred">Gred Skim Perkhidmatan</label>
                  </div>
                </div>
              </div>

              {{-- JAWATAN (disimpan pada penempatan) --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select name="id_jawatan" id="id_jawatan" class="form-select" disabled>
                      <option value="">-- Pilih Jawatan di Kementerian --</option>
                    </select>
                    <label for="id_jawatan">Jawatan di Kementerian</label>
                  </div>
                </div>
              </div>

              <br>

              <div class="col-12 d-flex justify-content-between">
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
                STEP 3: MAKLUMAT PENEMPATAN
                ======================= --}}
            <div id="maklumat-penempatan" class="content">
              <div class="content-header mb-3">
                <h6 class="mb-0">Maklumat Penempatan</h6>
                <small>Masukkan maklumat penempatan pegawai.</small>
              </div>

              {{-- KATEGORI PENEMPATAN --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <select id="id_penempatan" name="id_penempatan" class="form-select">
                      <option value="">-- Pilih Kategori Penempatan --</option>

                      {{-- controller WAJIB pass: $lkpPenempatan12 (collection) --}}
                      @foreach (($lkpPenempatan12 ?? $penempatan ?? collect()) as $p)
                        <option value="{{ $p->id_penempatan }}"
                          @selected(old('id_penempatan', optional($penempatanSemasa)->id_lkp_penempatan) == $p->id_penempatan)>
                          {{ $p->penempatan }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_penempatan">Kategori Penempatan</label>
                  </div>
                </div>

                {{-- Dari Kementerian/Jabatan --}}
                <div class="col-sm-8" id="in_out_dari_wrapper" style="display:none;">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="in_out" name="in_out" class="form-control"
                      placeholder="Dari Kementerian/Jabatan"
                      value="{{ old('in_out', optional($penempatanSemasa)->in_out) }}"
                      oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    <label for="in_out">Dari Kementerian/Jabatan</label>
                  </div>
                </div>
              </div>

              {{-- TARIKH MASUK --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-4">
                  <div class="form-floating form-floating-outline">
                    <input type="date"
                 id="tarikh_masuk"
                 name="tarikh_masuk"
                 class="form-control"
                 value="{{ old('tarikh_masuk', optional($penempatanSemasa)->tarikh_masuk ? \Carbon\Carbon::parse($penempatanSemasa->tarikh_masuk)->format('Y-m-d') : '') }}"
                 required>
                    <label for="tarikh_masuk">Tarikh Masuk Kementerian</label>
                  </div>
                </div>
              </div>

              {{-- AGENSI --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <select name="id_agensi" id="id_agensi" class="form-select">
                      <option value="">-- Pilih Agensi --</option>
                      @foreach ($agensi as $a)
                        <option value="{{ $a->id }}"
                          @selected($selectedAgensi == $a->id)>
                          {{ $a->agensi }}
                        </option>
                      @endforeach
                    </select>
                    <label for="id_agensi">Kementerian/Agensi</label>
                  </div>
                </div>
              </div>

              {{-- BAHAGIAN --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <select name="id_bahagian" id="id_bahagian" class="form-select">
                      <option value="">-- Pilih Bahagian --</option>
                      {{-- diisi ajax + prefill --}}
                    </select>
                    <label for="id_bahagian">Bahagian</label>
                  </div>
                </div>
              </div>

              {{-- SEKSYEN --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <select name="id_seksyen" id="id_seksyen" class="form-select">
                      <option value="">-- Pilih Seksyen --</option>
                      {{-- diisi ajax + prefill --}}
                    </select>
                    <label for="id_seksyen">Seksyen</label>
                  </div>
                </div>
              </div>

              {{-- CAWANGAN --}}
              <div class="row g-4 mb-3">
                <div class="col-sm-6">
                  <div class="form-floating form-floating-outline">
                    <select name="id_cawangan" id="id_cawangan" class="form-select">
                      <option value="">-- Pilih Cawangan --</option>
                      {{-- diisi ajax + prefill --}}
                    </select>
                    <label for="id_cawangan">Cawangan</label>
                  </div>
                </div>
              </div>

              <br>

              <div class="col-12 d-flex justify-content-between">
                <button type="button" class="btn rounded-pill btn-label-secondary btn-prev">
                  <i class="mdi mdi-arrow-left me-sm-1 me-0"></i>
                  <span class="align-middle d-sm-inline-block d-none">Sebelumnya</span>
                </button>

                <button type="button" id="btnSubmitStaff" class="btn rounded-pill btn-label-primary btn-submit">
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
