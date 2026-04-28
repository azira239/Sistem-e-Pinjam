@extends('layouts/layoutMaster')

@section('title', 'Senarai Pegawai')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>

{{-- DataTables Buttons (CDN) --}}
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

{{-- Excel --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

{{-- PDF --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

{{-- Print --}}
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

@endsection

@section('content')
<h4 class="fw-semibold pt-3 mb-1">Senarai Pegawai Kementerian</h4>
<p class="mb-4">Tapis senarai mengikut keperluan. (Paparan hanya keluar selepas carian dibuat)</p>

{{-- FILTER CARD --}}
<div class="card">
	<h5 class="card-header"></h5>
   	<div class="card-datatable text-nowrap">

		<div class="row g-2 p-2">
			<div class="col-md-8">
				<div class="form-floating form-floating-outline">
				  	<input
					    type="text"
					    id="fNama"
					    class="form-control"
					    placeholder="Cari nama pegawai"
					    oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
			  		<label for="fNama">Nama Pegawai</label>
				</div>
			</div>

			<div class="col-md-4">
				<div class="form-floating form-floating-outline">
			    	<select id="fStatus" class="form-select">
				      	<option value="">Semua</option>
				        <option value="1">Aktif</option>
						<option value="0">Tidak Aktif</option>
			    	</select>
			    	<label for="fStatus">Status Pegawai</label>
			  	</div>
			</div>
		</div>


		<div class="row g-2 p-2">
			<div class="col-md-4">
			  	<div class="form-floating form-floating-outline">
			    	<select id="fKementerian" class="form-select">
			      		<option value="">-- Pilih Agensi --</option>
			      		@foreach($lkpAgensi as $a)
			        		<option value="{{ $a->id }}">{{ $a->agensi }}</option>
			      		@endforeach
			   		</select>
		    		<label for="fKementerian">Kementerian</label>
			  	</div>
			</div>


			<div class="col-md-4">
			  	<div class="form-floating form-floating-outline">
			    	<select id="fBahagian" class="form-select" disabled>
			      		<option value="">-- Pilih Bahagian --</option>
			    	</select>
			    	<label for="fBahagian">Bahagian</label>
			  	</div>
			</div>


			<div class="col-md-4">
			  	<div class="form-floating form-floating-outline">
			    	<select id="fSeksyen" class="form-select" disabled>
			      		<option value="">-- Pilih Seksyen --</option>
			    	</select>
			    	<label for="fSeksyen">Seksyen</label>
			  	</div>
			</div>
		</div>

		<div class="row g-2 p-2">
      <div class="col-md-12 d-flex justify-content-between align-items-center">

        <!-- KIRI -->
        <a href="{{ route('staffs.add') }}"
            class="btn btn-outline-info rounded-pill btn-sm px-4">
            TAMBAH
        </a>

        <!-- KANAN -->
        <div class="d-flex gap-2">
          <button type="button"
                  id="btnSearch"
                  class="btn btn-outline-primary rounded-pill btn-sm px-4">
              CARIAN
          </button>

          <button type="button"
                  id="btnResetFilter"
                  class="btn btn-outline-secondary rounded-pill btn-sm px-4">
              RESET
          </button>
        </div>
      </div>
    </div>


	</div>
</div>

<br>

{{-- TABLE --}}
<div class="card">
  	<h5 class="card-header"></h5>

	<div class="card-datatable table-responsive">
	<table class="table datatables-permissions w-100">
	  <thead class="table-light">
	    <tr>
	      <th class="text-center">Bil.</th>
	      <th>Nama Pegawai</th>
	      <th>Kementerian</th>
	      <th>Bahagian</th>
	      <th>Seksyen</th>
	      <th class="text-center">Status</th>
	    </tr>
	  </thead>
	  <tbody>
	    {{-- kosong, data masuk melalui AJAX --}}
	  </tbody>
	</table>
	</div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const fNama        = document.getElementById('fNama');
  const fKementerian = document.getElementById('fKementerian');
  const fBahagian    = document.getElementById('fBahagian');
  const fSeksyen     = document.getElementById('fSeksyen');
  const fStatus      = document.getElementById('fStatus');

  const btnSearch    = document.getElementById('btnSearch');
  const btnReset     = document.getElementById('btnResetFilter');

  let hasSearched = false;

  const bahagianUrlTemplate = @json(route('ajax.bahagian.byAgensi', ['agensi' => 'AGENSI_ID']));
  const seksyenUrlTemplate  = @json(route('ajax.seksyen.byBahagian', ['bahagian' => 'BAHAGIAN_ID']));

  function resetSelect(sel, placeholder='-- Pilih --', disable=true) {
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    sel.disabled = disable;
  }

  async function loadOptions(url, selectEl, idKey, textKey) {
    const res = await fetch(url);
    const data = await res.json();
    resetSelect(selectEl, '-- Pilih --', false);

    data.forEach(row => {
      const opt = document.createElement('option');
      opt.value = row[idKey];
      opt.textContent = row[textKey];
      selectEl.appendChild(opt);
    });
  }

  function anyFilterFilled() {
    return (
      (fNama.value || '').trim() !== '' ||
      (fKementerian.value || '').trim() !== '' ||
      (fBahagian.value || '').trim() !== '' ||
      (fSeksyen.value || '').trim() !== '' ||
      (fStatus.value || '').trim() !== ''
    );
  }

  function safeReload() {
    // ✅ reload bila: user dah tekan Carian (papar semua) ATAU ada filter diisi
    if (hasSearched || anyFilterFilled()) {
      dt.ajax.reload();
    }
  }

  const dt = $('.datatables-permissions').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    lengthChange: true,
    pageLength: 10,
    pagingType: 'full_numbers',

    // ✅ TAMBAH INI
    dom: "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'B>>" +
         "<'row'<'col-12'tr>>" +
         "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",

    buttons: [
      {
        extend: 'excelHtml5',
        text: '<i class="mdi mdi-file-excel"></i> Excel',
        className: 'btn btn-outline-success btn-sm rounded-pill me-1',
        title: 'Senarai Pegawai',
        exportOptions: {
          columns: [0,1,2,3,4] // exclude status icon
        }
      },
      {
        extend: 'pdfHtml5',
        text: '<i class="mdi mdi-file-pdf-box"></i> PDF',
        className: 'btn btn-outline-danger btn-sm rounded-pill me-1',
        title: 'Senarai Pegawai',
        orientation: 'landscape',
        pageSize: 'A4',
        exportOptions: {
          columns: [0,1,2,3,4]
        }
      },
      {
        extend: 'print',
        text: '<i class="mdi mdi-printer"></i> Print',
        className: 'btn btn-outline-secondary btn-sm rounded-pill',
        exportOptions: {
          columns: [0,1,2,3,4]
        }
      }
    ],

    ajax: {
      url: @json(route('staffs.datatable')),
      data: function (d) {
        d.hasSearched = hasSearched ? 1 : 0;
        d.nama     = (fNama.value || '').trim();
        d.agensi   = (fKementerian.value || '').trim();
        d.bahagian = (fBahagian.value || '').trim();
        d.seksyen  = (fSeksyen.value || '').trim();
        d.status   = (fStatus.value || '').trim();
      }
    },

    columns: [
      { data: 'bil', className: 'text-center', orderable: false },
      {
        data: 'nama',
        orderable: false,
        render: function(data, type, row){
          const url = @json(url('/staff')) + '/' + row.id_staff + '/view';
          return `<a href="${url}" class="text-primary">${escapeHtml(data)}</a>`;
        }
      },
      { data: 'kementerian', orderable: false },
      { data: 'bahagian', orderable: false },
      { data: 'seksyen', orderable: false },
      {
        data: 'status',
        className: 'text-center',
        orderable: false,
        render: function(val){
          return (parseInt(val,10) === 1)
            ? `<span class="text-success"><i class="mdi mdi-account-check mdi-24px"></i></span>`
            : `<span class="text-danger"><i class="mdi mdi-account-off mdi-24px"></i></span>`;
        }
      }
    ],

    language: {
      emptyTable: "Sila tekan butang Carian atau isi filter untuk paparan data."
    }
  });










  // ✅ Tekan Carian -> papar semua (walaupun filter kosong)
  btnSearch.addEventListener('click', function () {
    hasSearched = true;
    dt.ajax.reload();
  });

  // Nama (debounce)
  let tNama = null;
  fNama.addEventListener('input', function(){
    clearTimeout(tNama);
    tNama = setTimeout(() => safeReload(), 350);
  });

  // Status
  fStatus.addEventListener('change', function(){
    safeReload();
  });

  // Kementerian -> Bahagian
  fKementerian.addEventListener('change', async function(){
    resetSelect(fBahagian, '-- Pilih Bahagian --', true);
    resetSelect(fSeksyen, '-- Pilih Seksyen --', true);

    if (!this.value) {
      safeReload();
      return;
    }

    await loadOptions(
      bahagianUrlTemplate.replace('AGENSI_ID', encodeURIComponent(this.value)),
      fBahagian,
      'id',
      'bahagian'
    );

    safeReload();
  });

  // Bahagian -> Seksyen
  fBahagian.addEventListener('change', async function(){
    resetSelect(fSeksyen, '-- Pilih Seksyen --', true);

    if (!this.value) {
      safeReload();
      return;
    }

    await loadOptions(
      seksyenUrlTemplate.replace('BAHAGIAN_ID', encodeURIComponent(this.value)),
      fSeksyen,
      'id_seksyen',
      'seksyen'
    );

    safeReload();
  });

  // Seksyen
  fSeksyen.addEventListener('change', function(){
    safeReload();
  });

  // Reset
  btnReset.addEventListener('click', function(){
    fNama.value = '';
    fKementerian.value = '';
    resetSelect(fBahagian, '-- Pilih Bahagian --', true);
    resetSelect(fSeksyen, '-- Pilih Seksyen --', true);
    fStatus.value = '';

    hasSearched = false;  // kembali ke mode “kosong awal”
    dt.ajax.reload();     // server akan pulangkan kosong
  });

  function escapeHtml(str){
    return String(str ?? '')
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }
});
</script>

@endsection
