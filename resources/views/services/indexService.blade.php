@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Index Services')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/swiper/swiper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/cards-statistics.css')}}">

<style>
#swiper-perkhidmatan { border-radius: 1rem; }

/* slide base */
.kpn-slide{
  border-radius: 1rem;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  color: #fff;
  min-height: 260px;
}
.kpn-slide > *{ position: relative; z-index: 1; }
.kpn-slide small{ opacity: .95; }

/* Perkhidmatan */
.kpn-slide--perkhidmatan{
  background: linear-gradient(135deg, #5dade2, #85c1e9);
}
.kpn-slide--perkhidmatan::before{
  content:'';
  position:absolute; top:-45px; right:-45px;
  width:170px; height:170px;
  background: rgba(255,255,255,.16);
  border-radius: 50%;
}
.kpn-slide--perkhidmatan::after{
  content:'';
  position:absolute; bottom:-70px; left:-70px;
  width:260px; height:260px;
  background: rgba(255,255,255,.10);
  border-radius: 50%;
}

/* Perjawatan */
.kpn-slide--perjawatan{
  background: linear-gradient(135deg, #58d68d, #82e0aa);
}
.kpn-slide--perjawatan::before{
  content:'';
  position:absolute; top:-50px; left:-50px;
  width:190px; height:190px;
  background: rgba(255,255,255,.14);
  border-radius: 50%;
}
.kpn-slide--perjawatan::after{
  content:'';
  position:absolute; bottom:-50px; right:-50px;
  width:160px; height:160px;
  background: rgba(255,255,255,.10);
  border-radius: 50%;
}

.swiper-pagination-bullet{ opacity: .55; }
.swiper-pagination-bullet-active{ opacity: 1; }

.kpn-text-dark h5,
.kpn-text-dark p,
.kpn-text-dark small { color: #1f2d3d !important; }
.kpn-text-dark .fw-bold { color: #0f172a !important; }

/* COLOR ICON STATUS */
.status-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  font-size: 18px;
}

.status-active {
  background-color: #e9f9ee; /* hijau lembut */
  color: #28c76f;           /* hijau */
}

.status-inactive {
  background-color: #fdecec; /* merah lembut */
  color: #ea5455;            /* merah */
}


</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
<script src="{{asset('assets/vendor/libs/swiper/swiper.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('content')
<h4 class="fw-semibold pt-3 mb-1">Pengurusan Perkhidmatan dan Perjawatan</h4>
<p class="mb-4">Sila lengkapkan maklumat rujukan perkhidmatan dan perjawatan Kementerian.</p>

<div class="row">
  <div class="col-12 mb-4">
    <div class="bs-stepper wizard-icons wizard-icons-example mt-2" id="perkhidmatanStepper">

      <div class="row justify-content-center w-100 g-0">
        <div class="col-lg-2 d-none d-lg-block"></div>

        <div class="col-lg-8 col-md-10 col-12">
          <div class="swiper-container swiper" id="swiper-perkhidmatan">
            <div class="swiper-wrapper">

              {{-- SLIDE 1: PERKHIDMATAN --}}
              <div class="swiper-slide kpn-slide kpn-slide--perkhidmatan kpn-text-dark">
                <div class="row align-items-center">
                  <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1">
                    <h5 class="mb-2">Skim Perkhidmatan Mengikut SSPA</h5>
                    <div class="d-flex align-items-center gap-2 mb-3">
                      <small>Rujukan data skim perkhidmatan yang digunakan dalam modul perjawatan</small>
                    </div>

                    <div class="row">
                      <div class="col-8">
                        <ul class="list-unstyled mb-0">
                          <li class="d-flex mb-3 align-items-center">
                            <p class="mb-0 me-2 fw-bold">{{ $jumlahSkim }}</p>
                            <p class="mb-0">Skim Perkhidmatan di Kementerian</p>
                          </li>
                          <li class="d-flex align-items-center">
                            <p class="mb-0 me-2 fw-bold">{{ $jumlahKumpulan }}</p>
                            <p class="mb-0">Kumpulan Perkhidmatan di Kementerian</p>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 text-center mb-3 mb-md-0">
                    <img src="{{asset('assets/img/icons/sspa.png')}}" alt="Perkhidmatan" width="330" class="img-fluid">
                  </div>
                </div>

                {{-- FOOTER BUTTON: buka bawah (tanpa blade lain) --}}
                <div class="kpn-slide-footer mt-3 pt-3 border-top border-secondary d-flex justify-content-end">
                  <a href="{{ route('services.index', ['show' => 'skim']) }}#senarai-skim"
                     class="btn btn-sm rounded-pill btn-outline-warning text-dark d-inline-flex align-items-center gap-1">
                    <i class="mdi mdi-format-list-bulleted"></i>
                    Senarai Penuh
                  </a>
                </div>
              </div>

              {{-- SLIDE 2: PERJAWATAN --}}
              <div class="swiper-slide kpn-slide kpn-slide--perjawatan kpn-text-dark">
                <div class="row align-items-center">
                  <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1">
                    <h5 class="text-white mb-2">Perjawatan di Kementerian</h5>
                    <div class="d-flex align-items-center gap-2 mb-3">
                      <small class="text-white">Maklumat rujukan perjawatan mengikut kumpulan</small>
                    </div>
                    
                    <div class="row">
                      <!-- KIRI -->
                      <div class="col-6">
                        <ul class="list-unstyled mb-0">
                          <li class="d-flex mb-3 align-items-center">
                            <p class="mb-0 me-2 fw-bold">{{ $jawatanStat->pengurusan_tertinggi }}</p>
                            <p class="mb-0">Pengurusan Tertinggi</p>
                          </li>
                          <li class="d-flex align-items-center">
                            <p class="mb-0 me-2 fw-bold">{{ $jawatanStat->pengurusan_profesional }} </p>
                            <p class="mb-0">Pengurusan & Profesional</p>
                          </li>
                        </ul>
                      </div>

                      <!-- KANAN -->
                      <div class="col-6">
                        <ul class="list-unstyled mb-0">
                          <li class="d-flex mb-3 align-items-center">
                            <p class="mb-0 me-2 fw-bold">{{ $jawatanStat->pelaksana_1 }}</p>
                            <p class="mb-0">Pelaksana I</p>
                          </li>
                          <li class="d-flex align-items-center">
                            <p class="mb-0 me-2 fw-bold">{{ $jawatanStat->pelaksana_2 }}</p>
                            <p class="mb-0">Pelaksana II</p>
                          </li>
                        </ul>
                      </div>
                    </div>

                  </div>

                  <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 text-center mb-3 mb-md-0 solid #1f2d3d;">
                    <img src="{{asset('assets/img/icons/perjawatan.png')}}"
                        alt="Perjawatan" width="272" class="img-fluid">
                  </div>

                </div>

                <div class="kpn-slide-footer mt-3 pt-3 border-top border-secondary d-flex justify-content-end">
                  <a href="{{ route('services.index', ['show' => 'jawatan']) }}#senarai-jawatan"
                    class="btn btn-sm rounded-pill btn-outline-warning text-dark d-inline-flex align-items-center gap-1">
                    <i class="mdi mdi-format-list-bulleted"></i>
                    Senarai Penuh
                  </a>
                </div>

              </div>

            </div>

            <div class="swiper-pagination"></div>
          </div>
        </div>

        <div class="col-lg-2 d-none d-lg-block"></div>
      </div>
    </div>
  </div>
</div>

{{-- =========================
   SECTION BAWAH (SENARAI PENUH SKIM) - SAME BLADE
   ========================= --}}
<div id="senarai-skim" class="row">
  <div class="col-12">

    {{-- kalau show=skim -> auto buka (collapse show) --}}
    <div class="collapse {{ ($show ?? null) === 'skim' ? 'show' : '' }}" id="collapseSenaraiSkim">

      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-0">Senarai Skim Perkhidmatan</h5>
            <!-- Sila pilih <b>kriteria carian</b> dan klik Carian untuk memaparkan senarai. -->
          </div>

          <a href="{{ route('services.index') }}" class="btn rounded-pill btn-outline-dark btn-sm">
            Tutup
          </a>
        </div>

        <div class="card-body">

          {{-- FILTER (mula-mula memang ada, table bawah hanya keluar bila do_search=1) --}}
          <form method="GET" action="{{ route('services.index') }}#senarai-skim" class="mb-3">
            <input type="hidden" name="show" value="skim">

            <div class="row g-3">

              <div class="col-md-4">
                <label class="form-label">Klasifikasi Perkhidmatan</label>
                <select name="id_klasifikasi_perkhidmatan" id="id_klasifikasi_perkhidmatan" class="form-select select2">
                  <option value="">-- Pilih Klasifikasi --</option>
                  @foreach($optsKlasifikasi as $k)
                    <option value="{{ $k->id_klasifikasi_perkhidmatan }}"
                      @selected(request('id_klasifikasi_perkhidmatan') == $k->id_klasifikasi_perkhidmatan)>
                      {{ $k->kod_klasifikasi_perkhidmatan }} - {{ $k->klasifikasi_perkhidmatan }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Skim Perkhidmatan (Kod Skim)</label>
                <select name="kod_skim_perkhidmatan" id="kod_skim_perkhidmatan" class="form-select select2"
                  {{ empty(request('id_klasifikasi_perkhidmatan')) ? 'disabled' : '' }}>
                  <option value="">
                    {{ empty(request('id_klasifikasi_perkhidmatan')) ? '-- Pilih Klasifikasi dahulu --' : '-- Semua --' }}
                  </option>

                  {{-- Jika klasifikasi sudah dipilih (refresh page), populate semula --}}
                  @foreach($optsKodSkim as $row)
                    <option value="{{ $row->kod_skim_perkhidmatan }}"
                      @selected(request('kod_skim_perkhidmatan') == $row->kod_skim_perkhidmatan)>
                      {{ $row->kod_skim_perkhidmatan }} - {{ $row->skim_perkhidmatan }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Kumpulan Perkhidmatan</label>
                <select name="id_kump_perkhidmatan" class="form-select select2">
                  <option value="">-- Semua --</option>
                  @foreach($optsKumpulan as $k)
                    <option value="{{ $k->id_kump_perkhidmatan }}" @selected(request('id_kump_perkhidmatan') == $k->id_kump_perkhidmatan)>
                      {{ $k->kump_perkhidmatan }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('services.index', ['show' => 'skim']) }}#senarai-skim" class="btn rounded-pill btn-outline-secondary btn-sm">
                  Reset
                </a>

                <button type="submit" name="do_search" value="1" class="btn rounded-pill btn-outline-primary btn-sm">
                  <i class="mdi mdi-magnify"></i> Carian
                </button>
              </div>

            </div>
          </form>

          {{-- RESULT: hanya keluar bila klik Carian --}}
          @if(($show ?? null) === 'skim' && ($doSearch ?? false))

            <div class="table-responsive">
              <table class="table table-striped" id="tblSkim">
                <thead>
                  <tr>
                    <th></th>
                    <th>Bil.</th>
                    <th>Kod Skim</th>
                    <th>Skim Perkhidmatan</th>
                    <th>Kumpulan Perkhidmatan</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($rowsSkim as $i => $r)
                    <tr>
                      <td></td>
                      <td>{{ $i+1 }}</td>
                      <td>{{ $r->kod_skim_perkhidmatan }}</td>
                      <td>{{ $r->skim_perkhidmatan }}</td>
                      <td>{{ $r->kump_perkhidmatan ?? '-' }}</td>
                      <td></td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-muted">Tiada rekod dijumpai.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

          @else
            <div class="alert alert-info mb-0">
              Sila pilih <b>kriteria carian</b> dan klik Carian untuk memaparkan senarai.
            </div>
          @endif

        </div>
      </div>

    </div>
  </div>
</div>


{{-- =========================
   SECTION BAWAH (SENARAI PENUH JAWATAN) - SAME BLADE
   ========================= --}}
<div id="senarai-jawatan" class="row">
  <div class="col-12">

    <div class="collapse {{ (($show ?? null) === 'jawatan') ? 'show' : '' }}" id="collapseSenaraiJawatan">
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="mb-0">Senarai Jawatan di Kementerian</h5>
            <!-- <small class="text-muted">
              Sila pilih <b>kriteria carian</b> dan klik Carian untuk memaparkan senarai.
            </small> -->
          </div>

          <a href="{{ route('services.index') }}" class="btn rounded-pill btn-outline-dark btn-sm">
            Tutup
          </a>
        </div>

        <div class="card-body">

          {{-- FILTER --}}
          <form method="GET" action="{{ route('services.index') }}#senarai-jawatan" class="mb-3">
            <input type="hidden" name="show" value="jawatan">

            <div class="row g-3">

              {{-- 1) KUMPULAN --}}
              <div class="col-md-3">
                <label class="form-label">Kumpulan Perkhidmatan</label>
                <select name="id_kump_perkhidmatan" id="ddl_kump_jawatan" class="form-select select2">
                  <option value="">-- Pilih Kumpulan --</option>
                  @foreach($optsKumpulan as $k)
                    <option value="{{ $k->id_kump_perkhidmatan }}"
                      @selected(request('id_kump_perkhidmatan') == $k->id_kump_perkhidmatan)>
                      {{ $k->kump_perkhidmatan }}
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- 2) JAWATAN (enable bila kumpulan dipilih) --}}
              <div class="col-md-6">
                <label class="form-label">Jawatan di Kementerian</label>
                <select name="id_jawatan" id="ddl_jawatan" class="form-select select2"
                  {{ empty(request('id_kump_perkhidmatan')) ? 'disabled' : '' }}>
                  <option value="">
                    {{ empty(request('id_kump_perkhidmatan')) ? '-- Pilih Kumpulan dahulu --' : '-- Semua --' }}
                  </option>

                  {{-- jika page refresh & kumpulan dah dipilih, controller boleh pass $optsJawatan --}}
                  @if(!empty($optsJawatan))
                    @foreach($optsJawatan as $j)
                      <option value="{{ $j->id }}"
                        @selected(request('id_jawatan') == $j->id)>
                        {{ $j->jawatan }}
                      </option>
                    @endforeach
                  @endif
                </select>
              </div>

              {{-- 3) STATUS JAWATAN --}}
              <div class="col-md-3">
                <label class="form-label">Status Jawatan</label>
                <select name="status_jawatan" class="form-select select2">
                  <option value="">-- Semua --</option>
                  <option value="1" @selected(request('status_jawatan') === '1')>
                    Aktif
                  </option>
                  <option value="0" @selected(request('status_jawatan') === '0')>
                    Tidak Aktif
                  </option>
                </select>
              </div>

              <div class="col-12 d-flex justify-content-between align-items-center mt-3">

                {{-- KIRI: Tambah Jawatan --}}
                <div>
                  <button type="button"
                    class="btn btn-primary rounded-pill"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambahJawatan">
                    <i class="mdi mdi-plus"></i>
                  </button>
                </div>

                {{-- KANAN: Reset & Carian --}}
                <div class="d-flex gap-2">
                  <a href="{{ route('services.index', ['show' => 'jawatan']) }}#senarai-jawatan"
                    class="btn rounded-pill btn-outline-secondary btn-sm">
                    Reset
                  </a>

                  <button type="submit" name="do_search" value="1"
                    class="btn rounded-pill btn-outline-primary btn-sm">
                    <i class="mdi mdi-magnify"></i> Carian
                  </button>
                </div>

              </div>

            </div>
          </form>


          {{-- MODAL TAMBAH JAWATAN --}}
          <div class="modal fade" id="modalTambahJawatan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">

                <div class="modal-header">
                  <h5 class="modal-title">Tambah Jawatan</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formTambahJawatan" method="POST" action="{{ route('services.jawatan.store') }}">
                  @csrf

                  <div class="modal-body">

                    <div class="mb-3">
                      <label class="form-label">Kumpulan Perkhidmatan</label>
                      <select name="id_kump_perkhidmatan" class="form-select select2" required>
                        <option value="">-- Pilih Kumpulan --</option>
                        @foreach($optsKumpulan as $k)
                          <option value="{{ $k->id_kump_perkhidmatan }}">
                            {{ $k->kump_perkhidmatan }}
                          </option>
                        @endforeach
                      </select>
                      <div class="invalid-feedback">Sila pilih kumpulan perkhidmatan.</div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Jawatan</label>
                      <input type="text" name="jawatan" class="form-control" placeholder="Contoh: Ketua Setiausaha" required>
                      <div class="invalid-feedback">Sila isi jawatan.</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between border rounded p-3">
                      <div>
                        <div class="fw-semibold">Status Jawatan</div>
                        <small class="text-muted">Aktif = 1, Tidak Aktif = 0</small>
                      </div>

                      <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="switchStatusJawatan" checked>
                      </div>
                    </div>

                    {{-- nilai sebenar untuk DB --}}
                    <input type="hidden" name="status_jawatan" id="status_jawatan" value="1">

                  </div>

                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">
                      <i class="mdi mdi-content-save"></i> Simpan
                    </button>
                  </div>

                </form>

              </div>
            </div>
          </div>


          {{-- RESULT: hanya keluar bila klik Carian --}}
          @if((($show ?? null) === 'jawatan') && (($doSearchJawatan ?? false) === true))

            <div class="table-responsive">
              <table class="table table-striped" id="tblJawatan">
                <thead>
                  <tr>
                    <th></th>
                    <th>Bil.</th>
                    <th>Jawatan</th>
                    <th>Kumpulan Perkhidmatan</th>
                    <th class="text-center">Status Jawatan</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($rowsJawatan as $i => $r)
                    <tr>
                      <td></td>
                      <td>{{ $i+1 }}</td>
                      <td>{{ $r->jawatan }}</td>
                      <td>{{ $r->kump_perkhidmatan ?? '-' }}</td>
                      <td class="text-center">
                        @if(($r->status_jawatan ?? 0) == 1)
                          <span class="status-icon status-active" title="Aktif">
                            <i class="mdi mdi-briefcase-check"></i>
                          </span>
                        @else
                          <span class="status-icon status-inactive" title="Tidak Aktif">
                            <i class="mdi mdi-briefcase-remove"></i>
                          </span>
                        @endif
                      </td>
                      <td></td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center text-muted">Tiada rekod dijumpai.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

          @else
            <div class="alert alert-info mb-0">
              Sila pilih <b>kriteria carian</b> dan klik Carian untuk memaparkan senarai.
            </div>
          @endif

        </div>
      </div>
    </div>

  </div>
</div>


@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // Swiper
  if (window.Swiper && typeof window.Swiper.use === 'function') {
    window.Swiper.use([window.Swiper.Autoplay, window.Swiper.Pagination]);
  }

  const swiperEl = document.querySelector('#swiper-perkhidmatan');
  if (swiperEl) {
    const swiper = new Swiper(swiperEl, {
      loop: true,
      speed: 900,
      spaceBetween: 16,
      grabCursor: true,
      autoHeight: true,
      watchSlidesProgress: true,
      observer: true,
      observeParents: true,
      autoplay: { delay: 4500, disableOnInteraction: false },
      pagination: { el: '#swiper-perkhidmatan .swiper-pagination', clickable: true }
    });

    swiperEl.addEventListener('mouseenter', () => swiper.autoplay && swiper.autoplay.stop());
    swiperEl.addEventListener('mouseleave', () => swiper.autoplay && swiper.autoplay.start());
  }

  // Pastikan jQuery wujud (DataTables theme biasanya guna jQuery)
  if (window.$) {

    // Select2
    if ($.fn.select2) {
      $('.select2').select2({ width: '100%' });
    }

    // ✅ DataTables: SKIM (limit 25)
    if ($.fn.DataTable) {
      const $tblSkim = $('#tblSkim');

      if ($tblSkim.length) {
        if ($.fn.dataTable.isDataTable('#tblSkim')) {
          $tblSkim.DataTable().clear().destroy();
        }

        $tblSkim.DataTable({
          pageLength: 10,
          lengthChange: false,
          paging: true,
          searching: false,
          info: true,
          ordering: true
        });
      }

      // ✅ DataTables: JAWATAN (limit 25)
      const $tblJawatan = $('#tblJawatan');

      if ($tblJawatan.length) {
        if ($.fn.dataTable.isDataTable('#tblJawatan')) {
          $tblJawatan.DataTable().clear().destroy();
        }

        $tblJawatan.DataTable({
          pageLength: 10,
          lengthChange: false,
          paging: true,
          searching: false,
          info: true,
          ordering: true
        });
      }
    }
  }
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
  // select2 init
  if (window.$ && $.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  const $ddlKlas = $('#id_klasifikasi_perkhidmatan');
  const $ddlSkim = $('#kod_skim_perkhidmatan');

  function resetSkimDropdown(message) {
    $ddlSkim.prop('disabled', true);
    $ddlSkim.html(`<option value="">${message}</option>`);
    $ddlSkim.trigger('change.select2');
  }

  $ddlKlas.on('change', function () {
    const idKlas = $(this).val();

    if (!idKlas) {
      resetSkimDropdown('-- Pilih Klasifikasi dahulu --');
      return;
    }

    resetSkimDropdown('Sedang memuatkan...');

    $.get("{{ route('services.api.skimByKlasifikasi') }}", {
      id_klasifikasi_perkhidmatan: idKlas
    })
    .done(function (items) {
      $ddlSkim.prop('disabled', false);

      let html = `<option value="">-- Semua --</option>`;
      items.forEach(function (it) {
        html += `<option value="${it.id}">${it.text}</option>`;
      });

      $ddlSkim.html(html);
      $ddlSkim.val('').trigger('change'); // kosongkan selection bila klas berubah
    })
    .fail(function () {
      resetSkimDropdown('Gagal memuatkan skim. Cuba semula.');
    });
  });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

  // ===== Select2 =====
  if (window.$ && $.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  // ===== Dependent dropdown: Kumpulan -> Jawatan =====
  const $ddlKump = $('#ddl_kump_jawatan');
  const $ddlJawatan = $('#ddl_jawatan');

  function resetJawatanDropdown(message) {
    $ddlJawatan.prop('disabled', true);
    $ddlJawatan.html(`<option value="">${message}</option>`);
    $ddlJawatan.val('').trigger('change');
  }

  $ddlKump.on('change', function () {
    const idKump = $(this).val();

    if (!idKump) {
      resetJawatanDropdown('-- Pilih Kumpulan dahulu --');
      return;
    }

    resetJawatanDropdown('Sedang memuatkan...');

    $.get("{{ route('services.api.jawatanByKumpulan') }}", {
      id_kump_perkhidmatan: idKump
    })
    .done(function (items) {
      $ddlJawatan.prop('disabled', false);

      let html = `<option value="">-- Semua --</option>`;
      items.forEach(function (it) {
        html += `<option value="${it.id}">${it.text}</option>`;
      });

      $ddlJawatan.html(html);
      $ddlJawatan.val('').trigger('change'); // reset pilihan jawatan bila kumpulan berubah
    })
    .fail(function () {
      resetJawatanDropdown('Gagal memuatkan jawatan. Cuba semula.');
    });
  });

  // ===== DataTable paginate 25 (jalan bila table wujud) =====
  const tblJawatan = document.querySelector('#tblJawatan');
  if (tblJawatan && window.DataTable) {
    // elak duplicate init kalau reload partial / script run banyak kali
    if (tblJawatan.dataset.dtInit !== '1') {
      new DataTable(tblJawatan, {
        paging: true,
        pageLength: 25,
        lengthMenu: [25, 50, 100],
        ordering: true
      });
      tblJawatan.dataset.dtInit = '1';
    }
  }

});
</script>

<!-- MODAL TAMBAH UNTUK TAMBAH JAWAPAN  -->
<script>
document.addEventListener('DOMContentLoaded', function () {

  // select2 untuk modal (penting: dropdown render dalam modal)
  if (window.$ && $.fn.select2) {
    $('#modalTambahJawatan .select2').select2({
      width: '100%',
      dropdownParent: $('#modalTambahJawatan')
    });
  }

  // switch aktif/tidak aktif -> hidden input
  const sw = document.getElementById('switchStatusJawatan');
  const hidden = document.getElementById('status_jawatan');

  if (sw && hidden) {
    sw.addEventListener('change', function () {
      hidden.value = sw.checked ? '1' : '0';
    });
  }

});
</script>


@endsection

