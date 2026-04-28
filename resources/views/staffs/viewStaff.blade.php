@extends('layouts/layoutMaster')

@section('title', 'Maklumat Pegawai')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-profile.css')}}" />

<style>
:root{
  --kpn-menu-bg: rgba(255,255,255,.7);
  --kpn-border:  rgba(0,0,0,.06);
  --kpn-accent:  #2563eb;
  --kpn-muted:   #6b7280;
  --kpn-radius:  16px;
  --kpn-transition: .3s ease;
}

/* =========================
   ✅ FIX SIDEBAR ISSUE
   - Jangan guna class generic ".menu" / ".link" sebab clash dgn layoutMaster
   - Tukar jadi scoped class "kpn-*"
   ========================= */

.kpn-floating-menu-wrap{
  margin-top: 0 !important;
  margin-bottom: 1rem;
}

.kpn-menu{
    align-self: flex-start;
  padding: 0.5rem;
  background: var(--kpn-menu-bg);
  backdrop-filter: blur(12px);
  border-radius: 10px;
  border: 0.5px solid var(--kpn-border);
  box-shadow: 0 5px 20px rgba(0,0,0,.12);
  display: flex;
  gap: 10px;
}

.kpn-link{
  display:flex;
  align-items:center;
  justify-content:flex-start;
  width: 40px;
  height: 30px;
  border-radius: var(--kpn-radius);
  color: var(--kpn-muted);
  text-decoration:none;
  font-weight:600;
  position:relative;
  overflow:hidden;
  transition: all var(--kpn-transition);
}

.kpn-link:hover,
.kpn-link:focus-visible,
.kpn-link.is-active{
  width:150px;
  color:#fff;
  background: var(--kpn-accent);
  box-shadow: 0 4px 12px rgba(37,99,235,.4);
}

.kpn-link-icon{
  font-size:26px;
  margin-left:16px;
  flex-shrink:0;
  transition: transform var(--kpn-transition);
}

.kpn-link:hover .kpn-link-icon,
.kpn-link.is-active .kpn-link-icon{
  transform: scale(1.1);
}

.kpn-link-title{
  margin-left:12px;
  opacity:0;
  transform: translateX(10px);
  transition: opacity var(--kpn-transition), transform var(--kpn-transition);
  white-space: nowrap;
  font-size: 15px;
}

.kpn-link:hover .kpn-link-title,
.kpn-link:focus-visible .kpn-link-title,
.kpn-link.is-active .kpn-link-title{
  opacity:1;
  transform: translateX(0);
}

/* ✅ TAB PANE (HIDE/SHOW) */
.kpn-tab-pane{ display:none; }
.kpn-tab-pane.is-active{ display:block; }

/* asal awak */
.penempatan-detail p { margin: 0.5px 0 !important; }

/* ICON STATUS PEGAWAI */
.status-btn{
  width:44px;height:44px;
  border-radius:50%;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  font-size:34px;
  background:#fff;
  border:4px solid;
}
.status-active{
  border-color:#28c76f;
  color:#28c76f;
  box-shadow:0 0 0 6px rgba(40,199,111,.15);
}
.status-inactive{
  border-color:#ea5455;
  color:#ea5455;
  box-shadow:0 0 0 6px rgba(234,84,85,.15);
}
.status-btn:hover{ 
    transform: translateY(-3px) scale(1.05); 
}

</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/pages-profile.js')}}"></script>
@endsection

@section('content')
<h4 class="fw-semibold pt-3 mb-1">Maklumat Pegawai</h4>
<p class="mb-4">Paparan maklumat pegawai yang telah direkodkan dalam sistem.</p>
<br><br>

<!-- Header -->
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">

        @php
          $lastDigit = intval(substr($staff->mykad, -1));
          $isFemale = ($lastDigit % 2 === 0);
          $avatar = $isFemale ? asset('assets/img/avatars/2.png') : asset('assets/img/avatars/1.png');
        @endphp

        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
          <img src="{{ $avatar }}"
               alt="user image"
               class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img">
        </div>

        <div class="flex-grow-1 mt-3 mt-sm-5">
          <div class="d-flex align-items-center justify-content-between mx-4 gap-3 flex-wrap">

            <div class="user-profile-info">
              <h4 class="mb-1">{{ $staff->nama_gelaran }} {{ $staff->nama }}</h4>

              <div class="d-flex align-items-center text-muted">
                <i class="mdi mdi-clipboard-account-outline me-1 mdi-20px"></i>
                <span class="fw-semibold text-body">
                  {{ $staff->jawatan_penempatan ?? '-' }}
                </span>
              </div>
            </div>

            <div class="text-end">
              @if ($staff->id_status_pegawai == 1)
                <span class="status-btn status-active" data-bs-toggle="tooltip" title="Pegawai Aktif">
                  <i class="mdi mdi-account-check"></i>
                </span>
              @else
                <span class="status-btn status-inactive" data-bs-toggle="tooltip" title="Pegawai Tidak Aktif">
                  <i class="mdi mdi-account-off"></i>
                </span>
              @endif
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<!--/ Header -->

<div class="row">
  <div class="col-xl-4 col-lg-5 col-md-5">

    <!-- MAKLUMAT PERIBADI (kekal) -->
    <div class="card mb-4" id="sec-peribadi">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-0">
          <small class="card-text text-uppercase text-muted fw-bold" style="font-size: 0.9rem;">
            Maklumat Peribadi
          </small>

          <button type="button"
                  class="btn btn-sm btn rounded-pill btn-outline-warning"
                  data-bs-toggle="tooltip"
                  data-bs-placement="bottom"
                  title="Kemaskini Maklumat"
                  onclick="window.location.href='{{ route('staffs.edit', $staff->id_staff) }}'">
            <span class="mdi mdi-pencil-outline"></span>
          </button>
        </div>

        <ul class="list-unstyled my-3 py-1">
          <li class="d-flex mb-3">
            <i class="mdi mdi-card-account-details-outline mdi-20px me-2 mt-1"></i>
            <div>
              <span class="text-uppercase text-muted small d-block">No. Mykad</span>
              <span class="fw-semibold">{{ $staff->mykad }}</span>
            </div>
          </li>

          <div class="row">
            <div class="col-12 col-md-5">
              <li class="d-flex mb-3">
                <i class="mdi mdi-calendar-range mdi-20px me-2 mt-1"></i>
                <div>
                  <span class="text-uppercase text-muted small d-block">Tarikh Lahir</span>
                  <span class="fw-semibold">{{ \Carbon\Carbon::parse($staff->tarikh_lahir)->format('d/m/Y') }}</span>
                </div>
              </li>
            </div>

            <div class="col-12 col-md-6">
              <li class="d-flex mb-3">
                <i class="mdi mdi-map-marker-account mdi-24px me-2 mt-1"></i>
                <div>
                  <span class="text-uppercase text-muted small d-block">Negeri Lahir</span>
                  <span class="fw-semibold">{{ $staff->negeri_lahir }}</span>
                </div>
              </li>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-md-5">
              <li class="d-flex mb-3">
                <i class="mdi mdi-human-male-female mdi-20px me-2 mt-1"></i>
                <div>
                  <span class="text-uppercase text-muted small d-block">Jantina</span>
                  <span class="fw-semibold">{{ $staff->jantina }}</span>
                </div>
              </li>
            </div>

            <div class="col-12 col-md-6">
              <li class="d-flex mb-3">
                <i class="mdi mdi-heart-outline mdi-20px me-2 mt-1"></i>
                <div>
                  <span class="text-uppercase text-muted small d-block">Status Perkahwinan</span>
                  <span class="fw-semibold">{{ $staff->status_kahwin ?? '-' }}</span>
                </div>
              </li>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-md-5">
              <li class="d-flex mb-3">
                <i class="mdi mdi-arrow-bottom-left-bold-box-outline mdi-20px me-2 mt-1"></i>
                <div>
                  <span class="text-uppercase text-muted small d-block">Bangsa</span>
                  <span class="fw-semibold">{{ $staff->nama_bangsa ?? '-' }}</span>
                </div>
              </li>
            </div>

            <div class="col-12 col-md-6">
              <li class="d-flex mb-3">
                <i class="mdi mdi-arrow-bottom-right-bold-box-outline mdi-20px me-2 mt-1"></i>
                <div>
                  <span class="text-uppercase text-muted small d-block">Etnik</span>
                  <span class="fw-semibold">{{ $staff->nama_etnik ?? '-' }}</span>
                </div>
              </li>
            </div>
          </div>

          <li class="d-flex mb-3">
            <i class="mdi mdi-centos mdi-20px me-2 mt-1"></i>
            <div>
              <span class="text-uppercase text-muted small d-block">Agama</span>
              <span class="fw-semibold">{{ $staff->nama_agama }}</span>
            </div>
          </li>

          <li class="d-flex mb-3">
            <i class="mdi mdi-map-marker-outline mdi-20px me-2 mt-1"></i>
            <div>
              <span class="text-uppercase text-muted small d-block">Alamat</span>
              <span class="fw-semibold">{{ $staff->alamat }}</span>
            </div>
          </li>

          <div class="row">
            <div class="col-12 col-md-5">
              <li class="d-flex mb-3">
                <i class="mdi mdi-phone mdi-20px me-2 mt-1"></i>
                <div>
                  <span class="text-uppercase text-muted small d-block">No. Telefon</span>
                  <span class="fw-semibold">{{ $staff->no_telefon ?? '-' }}</span>
                </div>
              </li>
            </div>

            <div class="col-12 col-md-6">
              <li class="d-flex mb-3">
                <i class="mdi mdi-cellphone mdi-20px me-2 mt-1"></i>
                <div>
                  <span class="text-uppercase text-muted small d-block">No. Telefon Bimbit</span>
                  <span class="fw-semibold">{{ $staff->no_hp ?? '-' }}</span>
                </div>
              </li>
            </div>
          </div>

          <li class="d-flex mb-3">
            <i class="mdi mdi-email-outline mdi-20px me-2 mt-1"></i>
            <div>
              <span class="text-uppercase text-muted small d-block">Emel</span>
              <span class="fw-semibold">{{ $staff->emel ?? '-'}}</span>
            </div>
          </li>

          <li class="d-flex mb-3">
            <i class="mdi mdi-book-account-outline mdi-20px me-2 mt-1"></i>
            <div>
              <span class="text-uppercase text-muted small d-block">MyDigital Id</span>
              <span class="fw-semibold">{{ $staff->status_mydid ?? '-' }}</span>
            </div>
          </li>
        </ul>

      </div>
    </div>
  </div>

  <div class="col-xl-8 col-lg-7 col-md-7">

    {{-- ✅ TAB MENU (KANAN): Perjawatan/Penempatan --}}
    <div class="kpn-floating-menu-wrap mt-0 mb-4">
      <nav class="kpn-menu" id="rightTabs">
        <a href="#tab-keluarga" class="kpn-link is-active" data-target="tab-keluarga">
          <i class="mdi mdi-account-group-outline kpn-link-icon"></i>
          <span class="kpn-link-title">Keluarga</span>
        </a>

        <a href="#tab-perjawatan" class="kpn-link" data-target="tab-perjawatan">
          <i class="mdi mdi-briefcase-outline kpn-link-icon"></i>
          <span class="kpn-link-title">Perjawatan</span>
        </a>

        <a href="#tab-kelayakan" class="kpn-link" data-target="tab-kelayakan">
          <i class="mdi mdi-school-outline kpn-link-icon"></i>
          <span class="kpn-link-title">Kelayakan</span>
        </a>

        <a href="#tab-penempatan" class="kpn-link" data-target="tab-penempatan">
          <i class="mdi mdi-timeline-clock-outline kpn-link-icon"></i>
          <span class="kpn-link-title">Penempatan</span>
        </a>

        <a href="#tab-tambahan" class="kpn-link" data-target="tab-tambahan">
          <i class="mdi mdi-dots-horizontal-circle-outline kpn-link-icon"></i>
          <span class="kpn-link-title">Tambahan</span>
        </a>
      </nav>
    </div>

    {{-- =========================
        TAB 1: KELUARGA
       ========================= --}}
    <div id="tab-keluarga" class="kpn-tab-pane is-active">
      <div class="card card-action mb-4" id="sec-keluarga">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <small class="card-text text-uppercase text-muted fw-bold" style="font-size: 0.9rem;">Maklumat Keluarga</small>
          </div>
        </div>


        

        
      </div>
    </div>

    {{-- =========================
        TAB 2: PERJAWATAN
       ========================= --}}
    <div id="tab-perjawatan" class="kpn-tab-pane">
      <div class="card card-action mb-4" id="sec-perjawatan">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <small class="card-text text-uppercase text-muted fw-bold" style="font-size: 0.9rem;">Maklumat Perjawatan</small>
          </div>
        </div>

        <div class="card-body">
          <ul class="list-unstyled my-1">
            <div class="row">
              <div class="col-md-4">
                <li class="d-flex mb-3">
                  <i class="mdi mdi-briefcase-outline mdi-20px me-2 mt-1"></i>
                  <div>
                    <span class="text-uppercase text-muted small d-block">Status Perkhidmatan</span>
                    <span class="fw-semibold">{{ $staff->status_perkhidmatan ?? '-' }}</span>
                  </div>
                </li>
              </div>

              <div class="col-md-4">
                <li class="d-flex mb-3">
                  <i class="mdi mdi-account-group-outline mdi-20px me-2 mt-1"></i>
                  <div>
                    <span class="text-uppercase text-muted small d-block">Kumpulan Perkhidmatan</span>
                    <span class="fw-semibold">{{ $staff->kump_perkhidmatan ?? '-' }}</span>
                  </div>
                </li>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <li class="d-flex mb-3">
                  <i class="mdi mdi-format-list-bulleted mdi-20px me-2 mt-1"></i>
                  <div>
                    <span class="text-uppercase text-muted small d-block">Klasifikasi Perkhidmatan</span>
                    <span class="fw-semibold">
                      {{ $staff->kod_klasifikasi_perkhidmatan ?? '-' }}
                      @if(!empty($staff->klasifikasi_perkhidmatan))
                        - {{ $staff->klasifikasi_perkhidmatan }}
                      @endif
                    </span>
                  </div>
                </li>
              </div>

              <div class="col-md-5">
                <li class="d-flex mb-3">
                  <i class="mdi mdi-file-document-outline mdi-20px me-2 mt-1"></i>
                  <div>
                    <span class="text-uppercase text-muted small d-block">Skim Perkhidmatan</span>
                    <span class="fw-semibold">{{ $staff->skim_perkhidmatan ?? '-' }}</span>
                  </div>
                </li>
              </div>

              <div class="col-md-3">
                <li class="d-flex mb-3">
                  <i class="mdi mdi-star-outline mdi-20px me-2 mt-1"></i>
                  <div>
                    <span class="text-uppercase text-muted small d-block">Kod & Gred Skim</span>
                    <span class="fw-semibold">
                      {{ $staff->kod_skim_perkhidmatan ?? '-' }}
                      @if(!empty($staff->gred)) {{ $staff->gred }} @endif
                    </span>
                  </div>
                </li>
              </div>
            </div>
          </ul>
        </div>
      </div>
    </div>

    {{-- =========================
        TAB 3: KELAYAKAN
       ========================= --}}
    <div id="tab-kelayakan" class="kpn-tab-pane">
      <div class="card card-action mb-4" id="sec-kelayakan">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <small class="card-text text-uppercase text-muted fw-bold" style="font-size: 0.9rem;">Maklumat Kelayakan</small>
          </div>
        </div>

        
      </div>
    </div>

    {{-- =========================
        TAB 4: PENEMPATAN
       ========================= --}}
    <div id="tab-penempatan" class="kpn-tab-pane">
      <div class="card card-action mb-4" id="sec-penempatan">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <small class="card-text text-uppercase text-muted fw-bold" style="font-size: 0.9rem;">Sejarah Penempatan</small>
          </div>

          <div class="card-action-element">
            <button type="button"
                    class="btn btn-sm btn rounded-pill btn-outline-info"
                    data-bs-toggle="modal"
                    data-bs-target="#onboardImageModal"
                    data-bs-placement="bottom"
                    title="Tambah Penempatan Baharu">
              <span class="mdi mdi-plus-outline"></span>
            </button>
          </div>

          <!-- MODAL TAMBAH PENEMPATAN -->
          <div class="modal-onboarding modal fade animate__animated" id="onboardImageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
              <div class="modal-content text-center">
                <div class="modal-header border-0">
                  <a class="text-muted close-label" href="javascript:void(0);" data-bs-dismiss="modal">Skip Intro</a>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body onboarding-horizontal p-0">
                  <div class="onboarding-media" style="flex: 0.7;">
                    <img src="{{asset('assets/img/illustrations/boy-verify-email-')}}"
                         alt="boy-verify-email-light"
                         width="273"
                         class="img-fluid"
                         data-app-light-img="illustrations/boy-verify-email-light.png"
                         data-app-dark-img="illustrations/boy-verify-email-dark.png">
                  </div>

                  <div class="onboarding-content mb-0" style="flex: 1.3;">
                    <h4 class="onboarding-title text-body">Rekod Penempatan Baharu</h4>
                    <div class="onboarding-info">Masukkan maklumat penempatan baharu untuk memastikan rekod pegawai sentiasa terkini.</div>

                    <form id="modalFormPenempatan" method="POST" action="{{ route('staffs.penempatan.store', $staff->id_staff) }}">
                      @csrf

                      <div class="row g-4 mb-3">
                        <div class="col-sm-7">
                          <div class="form-floating form-floating-outline">
                            <select name="id_lkp_penempatan" id="modal_id_lkp_penempatan" class="form-select">
                              <option value="">-- Pilih Kategori Penempatan --</option>
                              @foreach ($lkpPenempatan as $p)
                                <option value="{{ $p->id_penempatan }}">{{ $p->penempatan }}</option>
                              @endforeach
                            </select>
                            <label for="modal_id_lkp_penempatan">Kategori Penempatan</label>
                          </div>
                        </div>
                      </div>

                      <!-- JENIS 3 (PERTUKARAN DALAMAN) -->
                      <div id="modal_penempatan_dalaman_fields" style="display:none;">
                        <div class="row g-4 mb-3">
                          <div class="col-sm-4">
                            <div class="form-floating form-floating-outline">
                              <input type="date" id="modal_tarikh_masuk" name="tarikh_masuk" class="form-control">
                              <label for="modal_tarikh_masuk">Tarikh Masuk</label>
                            </div>
                          </div>

                          <div class="col-sm-7">
                            <div class="form-floating form-floating-outline">
                              <select name="id_jawatan" id="modal_id_jawatan" class="form-select">
                                <option value="">-- Pilih Jawatan --</option>
                                @foreach($jawatanList as $j)
                                  <option value="{{ $j->id }}" @selected(optional($latestPenempatanJawatan)->id_jawatan == $j->id)>
                                    {{ $j->jawatan }}
                                  </option>
                                @endforeach
                              </select>
                              <label for="modal_id_jawatan">Jawatan</label>
                            </div>
                            <small class="text-muted">Default ikut jawatan terkini. Jika tiada perubahan, biar sahaja.</small>
                          </div>
                        </div>

                        <div class="row g-4 mb-3">
                          <div class="col-sm-7">
                            <div class="form-floating form-floating-outline">
                              <select name="id_agensi" id="modal_id_agensi" class="form-select">
                                <option value="">-- Pilih Kementerian/Agensi --</option>
                                @foreach ($agensi as $a)
                                  <option value="{{ $a->id }}">{{ $a->agensi }}</option>
                                @endforeach
                              </select>
                              <label for="modal_id_agensi">Kementerian/Agensi</label>
                            </div>
                          </div>
                        </div>

                        <div class="row g-4 mb-3">
                          <div class="col-sm-7">
                            <div class="form-floating form-floating-outline">
                              <select name="id_bahagian" id="modal_id_bahagian" class="form-select" disabled>
                                <option value="">-- Pilih Bahagian --</option>
                              </select>
                              <label for="modal_id_bahagian">Bahagian</label>
                            </div>
                          </div>
                        </div>

                        <div class="row g-4 mb-3">
                          <div class="col-sm-7">
                            <div class="form-floating form-floating-outline">
                              <select name="id_seksyen" id="modal_id_seksyen" class="form-select" disabled>
                                <option value="">-- Pilih Seksyen --</option>
                              </select>
                              <label for="modal_id_seksyen">Seksyen</label>
                            </div>
                          </div>
                        </div>

                        <div class="row g-4 mb-3">
                          <div class="col-sm-7">
                            <div class="form-floating form-floating-outline">
                              <select name="id_cawangan" id="modal_id_cawangan" class="form-select" disabled>
                                <option value="">-- Pilih Cawangan --</option>
                              </select>
                              <label for="modal_id_cawangan">Cawangan</label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- JENIS 4 (KELUAR) -->
                      <div id="modal_penempatan_keluar_fields" style="display:none;">
                        <div class="row g-4 mb-3">
                          <div class="col-sm-4">
                            <div class="form-floating form-floating-outline">
                              <input type="date" id="modal_tarikh_keluar" name="tarikh_keluar" class="form-control">
                              <label for="modal_tarikh_keluar">Tarikh Keluar</label>
                            </div>
                          </div>
                        </div>

                        <div class="row g-4 mb-3">
                          <div class="col-sm-8">
                            <div class="form-floating form-floating-outline">
                              <input type="text" id="modal_in_out" name="in_out" class="form-control" placeholder="Bertukar ke Kementerian/Jabatan">
                              <label for="modal_in_out">Bertukar ke Kementerian/Jabatan</label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="modal-footer border-0">
                        <button type="submit" class="btn rounded-pill btn-outline-primary">
                          <span class="me-sm-1">Hantar</span>
                          <i class="mdi mdi-send"></i>
                        </button>
                      </div>

                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="card-body pt-3 pb-0">
          <ul class="timeline mb-0">
            @forelse ($penempatanHistory as $index => $pen)
              <li class="timeline-item timeline-item-transparent {{ $loop->last ? 'border-0' : '' }}">
                @php
                  $colors = ['primary', 'success', 'warning', 'info', 'danger'];
                  $color  = $colors[$index % count($colors)];
                @endphp

                <span class="timeline-point timeline-point-{{ $color }}"></span>

                <div class="timeline-event">
                  <div class="timeline-header mb-1">
                    <h6 class="mb-0">{{ $pen->kategori_penempatan ?? 'Penempatan' }}</h6>

                    <span class="text-muted">
                      @if ((int)$pen->id_lkp_penempatan === 4)
                        {{ $pen->tarikh_keluar ? \Carbon\Carbon::parse($pen->tarikh_keluar)->format('d/m/Y') : '-' }}
                      @else
                        {{ $pen->tarikh_masuk ? \Carbon\Carbon::parse($pen->tarikh_masuk)->format('d/m/Y') : '-' }}
                      @endif
                    </span>
                  </div>

                  <div class="penempatan-detail">
                    @if ($pen->id_lkp_penempatan == 4)
                      @if ($pen->in_out)
                        <p class="text-muted mb-0">{{ $pen->in_out }}</p>
                      @endif
                    @else
                      @if ($pen->nama_agensi)
                        <p class="text-muted mb-0">{{ $pen->nama_agensi }}</p>
                      @endif
                      @if ($pen->nama_bahagian)
                        <p class="text-body fw-semibold mb-0">{{ $pen->nama_bahagian }}</p>
                      @endif
                      @if ($pen->nama_seksyen)
                        <p class="text-muted mb-0">{{ $pen->nama_seksyen }}</p>
                      @endif
                      @if ($pen->nama_cawangan)
                        <p class="text-muted mb-0">{{ $pen->nama_cawangan }}</p>
                      @endif
                    @endif
                  </div>

                </div>
              </li>
            @empty
              <li class="timeline-item timeline-item-transparent border-0">
                <span class="timeline-point timeline-point-secondary"></span>
                <div class="timeline-event">
                  <p class="text-muted mb-0">Tiada rekod penempatan direkodkan.</p>
                </div>
              </li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    {{-- =========================
        TAB 5: HAL-HAL LAIN
       ========================= --}}
    <div id="tab-tambahan" class="kpn-tab-pane">
      <div class="card card-action mb-4" id="sec-tambahan">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <small class="card-text text-uppercase text-muted fw-bold" style="font-size: 0.9rem;">Maklumat Tambahan</small>
          </div>
        </div>

        
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // =========================
  // 1) TAB KANAN (HIDE/SHOW)
  // =========================
  const tabNav = document.getElementById('rightTabs');
  if (tabNav) {
    const links = tabNav.querySelectorAll('.kpn-link[data-target]');
    const panes = document.querySelectorAll('.kpn-tab-pane');

    function activate(targetId) {
      panes.forEach(p => p.classList.remove('is-active'));
      links.forEach(a => a.classList.remove('is-active'));

      const pane = document.getElementById(targetId);
      if (pane) pane.classList.add('is-active');

      const link = tabNav.querySelector(`.kpn-link[data-target="${targetId}"]`);
      if (link) link.classList.add('is-active');

      history.replaceState(null, '', `#${targetId}`);
    }

    links.forEach(a => {
      a.addEventListener('click', function(e){
        e.preventDefault();
        activate(this.dataset.target);
      });
    });

    // ✅ support semua tab + default keluarga
    const hash = (location.hash || '').replace('#','');
    const allowed = ['tab-keluarga','tab-perjawatan','tab-kelayakan','tab-penempatan','tab-tambahan'];

    if (allowed.includes(hash)) {
      activate(hash);
    } else {
      activate('tab-keluarga');
    }
  }

  // =========================
  // 2) Script modal penempatan (asal awak) + guard jQuery
  // =========================
  const modalEl = document.getElementById('onboardImageModal');
  if (!modalEl) return;

  const urlBahagian = "{{ route('ajax.bahagian.byAgensi', ':id') }}";
  const urlSeksyen  = "{{ route('ajax.seksyen.byBahagian', ':id') }}";
  const urlCawangan = "{{ route('ajax.cawangan.bySeksyen', ':id') }}";

  function resetSelect(selectElem, placeholder, disabled = true) {
    if (!selectElem) return;
    selectElem.innerHTML = `<option value="">${placeholder}</option>`;
    selectElem.disabled = disabled;
  }

  function populateSelect(selectElem, items, placeholder, idKey, textKey) {
    resetSelect(selectElem, placeholder, false);
    (items || []).forEach(item => {
      const id = item[idKey];
      const txt = item[textKey];
      if (id === undefined || txt === undefined) return;
      selectElem.insertAdjacentHTML('beforeend', `<option value="${id}">${txt}</option>`);
    });
  }

  modalEl.addEventListener('shown.bs.modal', function () {

    const selectPenempatan = document.getElementById('modal_id_lkp_penempatan');
    const dalamanFields    = document.getElementById('modal_penempatan_dalaman_fields');
    const keluarFields     = document.getElementById('modal_penempatan_keluar_fields');

    const agensiSelect   = document.getElementById('modal_id_agensi');
    const bahagianSelect = document.getElementById('modal_id_bahagian');
    const seksyenSelect  = document.getElementById('modal_id_seksyen');
    const cawanganSelect = document.getElementById('modal_id_cawangan');

    if (!selectPenempatan || !dalamanFields || !keluarFields) return;

    // ✅ init Select2 untuk jawatan (kalau jQuery ada)
    if (window.$ && $.fn.select2) {
      const jawatan = $('#modal_id_jawatan');
      if (jawatan.length) {
        if (jawatan.hasClass("select2-hidden-accessible")) jawatan.select2('destroy');
        jawatan.select2({
          dropdownParent: $('#onboardImageModal'),
          width: '100%',
          placeholder: '-- Pilih Jawatan --',
          allowClear: true
        });
      }
    }

    function toggleModalFields() {
      const val = selectPenempatan.value;
      const isDal = (val === '3');
      const isKel = (val === '4');

      dalamanFields.style.display = isDal ? 'block' : 'none';
      keluarFields.style.display  = isKel ? 'block' : 'none';

      if (!isDal) {
        resetSelect(bahagianSelect, '-- Pilih Bahagian --', true);
        resetSelect(seksyenSelect,  '-- Pilih Seksyen --',  true);
        resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
      }
    }

    if (modalEl.dataset.bound !== '1') {

      agensiSelect?.addEventListener('change', async function () {
        if (selectPenempatan.value !== '3') return;

        const agensiId = this.value;
        resetSelect(bahagianSelect, '-- Pilih Bahagian --', true);
        resetSelect(seksyenSelect,  '-- Pilih Seksyen --',  true);
        resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
        if (!agensiId) return;

        const res = await fetch(urlBahagian.replace(':id', agensiId), {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        populateSelect(bahagianSelect, data, '-- Pilih Bahagian --', 'id', 'bahagian');
      });

      bahagianSelect?.addEventListener('change', async function () {
        if (selectPenempatan.value !== '3') return;

        const bahagianId = this.value;
        resetSelect(seksyenSelect,  '-- Pilih Seksyen --',  true);
        resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
        if (!bahagianId) return;

        const res = await fetch(urlSeksyen.replace(':id', bahagianId), {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        populateSelect(seksyenSelect, data, '-- Pilih Seksyen --', 'id_seksyen', 'seksyen');
      });

      seksyenSelect?.addEventListener('change', async function () {
        if (selectPenempatan.value !== '3') return;

        const seksyenId = this.value;
        resetSelect(cawanganSelect, '-- Pilih Cawangan --', true);
        if (!seksyenId) return;

        const res = await fetch(urlCawangan.replace(':id', seksyenId), {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        populateSelect(cawanganSelect, data, '-- Pilih Cawangan --', 'id_cawangan', 'cawangan');
      });

      selectPenempatan.addEventListener('change', toggleModalFields);
      modalEl.dataset.bound = '1';
    }

    toggleModalFields();
  });

  modalEl.addEventListener('hidden.bs.modal', function () {
    if (window.$ && $.fn.select2) {
      const jawatan = $('#modal_id_jawatan');
      if (jawatan.hasClass("select2-hidden-accessible")) jawatan.select2('destroy');
    }
  });

});
</script>
@endpush
