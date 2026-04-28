@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Senarai Skim Perkhidmatan')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('content')
<h4 class="fw-semibold pt-3 mb-1">Senarai Skim Perkhidmatan</h4>
<p class="mb-4">Gunakan penapis untuk membuat carian. Senarai hanya dipaparkan selepas anda klik butang <b>Carian</b>.</p>

{{-- FILTER CARD --}}
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('services.skim.index') }}">
      {{-- trigger untuk paparkan table --}}
      <input type="hidden" name="do_search" value="1">

      <div class="row g-3">

        {{-- Filter: Kod Skim --}}
        <div class="col-md-4">
          <label class="form-label">Skim Perkhidmatan (Kod Skim)</label>
          <select name="kod_skim_perkhidmatan" class="form-select select2">
            <option value="">-- Semua --</option>
            @foreach($optsKodSkim as $kod)
              <option value="{{ $kod }}" @selected(request('kod_skim_perkhidmatan') == $kod)>
                {{ $kod }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Filter: Kumpulan --}}
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

        {{-- Optional: carian nama skim --}}
        <div class="col-md-4">
          <label class="form-label">Nama Skim (optional)</label>
          <input type="text" name="nama_skim" class="form-control" value="{{ request('nama_skim') }}" placeholder="Contoh: Pegawai Kebudayaan">
        </div>

        <div class="col-12 d-flex gap-2 justify-content-end mt-2">
          <a href="{{ route('services.skim.index') }}" class="btn btn-outline-secondary">
            Reset
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-magnify"></i> Carian
          </button>
        </div>

      </div>
    </form>
  </div>
</div>

{{-- TABLE: hanya keluar bila doSearch = true --}}
@if(!$doSearch)
  <div class="alert alert-info">
    Sila pilih penapis dan klik <b>Carian</b> untuk memaparkan senarai.
  </div>
@else

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped" id="tblSkim">
          <thead>
            <tr>
              <th>#</th>
              <th>Kod Skim</th>
              <th>Skim Perkhidmatan</th>
              <th>Kumpulan Perkhidmatan</th>
              <th>ID Klasifikasi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $i => $r)
              <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $r->kod_skim_perkhidmatan }}</td>
                <td>{{ $r->skim_perkhidmatan }}</td>
                <td>{{ $r->kump_perkhidmatan ?? '-' }}</td>
                <td>{{ $r->id_klasifikasi_perkhidmatan ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted">Tiada rekod dijumpai.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endif
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // select2
  if (window.$ && $.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  // datatable hanya bila table wujud (doSearch = true)
  const tbl = document.querySelector('#tblSkim');
  if (tbl) {
    new DataTable(tbl, {
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      ordering: true
    });
  }
});
</script>
@endsection
