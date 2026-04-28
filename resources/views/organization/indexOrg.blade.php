@extends('layouts/layoutMaster')

@section('title', 'Rujukan Organisasi')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/jstree/jstree.css') }}">
<style>
  .org-active > a { color: #198754 !important; font-weight: 600; }
  .org-inactive > a { color: #dc3545 !important; font-weight: 600; }
  #orgTree { padding: 1rem; min-height: 300px; }

  .org-add-btn{
    margin-left: .5rem;
    font-size: 16px;
    cursor: pointer;
    color: #0d6efd;
    vertical-align: middle;
    line-height: 1;
  }
  .org-add-btn:hover{ color:#084298; }
  .jstree-anchor { display: inline-flex; align-items: center; }

  .switch-label-status{ margin-left:.5rem; font-weight:600; }
  .switch-label-status.on{ color:#198754; }
  .switch-label-status.off{ color:#dc3545; }
</style>
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/jstree/jstree.js') }}"></script>
@endsection

@section('content')
<h4 class="fw-semibold pt-3 mb-1">Rujukan Organisasi</h4>
<p class="mb-4">Paparan treeview dari data agensi/bahagian/seksyen/cawangan.</p>

<div class="card">
  <h5 class="card-header">Struktur Organisasi</h5>
  <div class="card-body">
    <div id="orgTree"></div>
  </div>
</div>

{{-- ===================== MODAL: TAMBAH AGENSI ===================== --}}
<div class="modal fade" id="modalAddAgensi" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAddAgensi" class="modal-content">
      @csrf
      <input type="hidden" name="type" value="kementerian">
      <input type="hidden" name="status" id="agensi_status" value="1">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Agensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Agensi Baharu</label>
          <input type="text" class="form-control" name="agensi" id="agensi_name" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Akronim (Singkatan)</label>
          <input type="text" class="form-control" name="akronim" id="agensi_akronim" placeholder="Contoh: KPN">
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="switch switch-primary mb-0">
            <input type="checkbox" class="switch-input" id="switchAgensi" checked>
            <span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>
          </label>
          <span id="labelAgensi" class="switch-label-status on">Aktif</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" id="btnSubmitAgensi" class="btn rounded-pill btn-primary waves-effect">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== MODAL: TAMBAH BAHAGIAN ===================== --}}
<div class="modal fade" id="modalAddBahagian" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAddBahagian" class="modal-content">
      @csrf
      <input type="hidden" name="type" value="bahagian">
      <input type="hidden" name="agensi_id" id="bahagian_agensi_id">
      <input type="hidden" name="status" id="bahagian_status" value="1">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Bahagian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Agensi</label>
          <input type="text" class="form-control" id="bahagian_agensi_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Bahagian Baharu</label>
          <input type="text" class="form-control" name="bahagian" id="bahagian_name" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Singkatan Bahagian</label>
          <input type="text" class="form-control" name="singkatan" id="bahagian_singkatan" placeholder="Contoh: UKK">
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="switch switch-primary mb-0">
            <input type="checkbox" class="switch-input" id="switchBahagian" checked>
            <span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>
          </label>
          <span id="labelBahagian" class="switch-label-status on">Aktif</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" id="btnSubmitBahagian" class="btn rounded-pill btn-primary waves-effect">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== MODAL: TAMBAH SEKSYEN ===================== --}}
<div class="modal fade" id="modalAddSeksyen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAddSeksyen" class="modal-content">
      @csrf
      <input type="hidden" name="type" value="seksyen">
      <input type="hidden" name="bahagian_id" id="seksyen_bahagian_id">
      <input type="hidden" name="status" id="seksyen_status" value="1">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Seksyen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Agensi</label>
          <input type="text" class="form-control" id="seksyen_agensi_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Bahagian</label>
          <input type="text" class="form-control" id="seksyen_bahagian_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Seksyen Baharu</label>
          <input type="text" class="form-control" name="seksyen" id="seksyen_name" required>
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="switch switch-primary mb-0">
            <input type="checkbox" class="switch-input" id="switchSeksyen" checked>
            <span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>
          </label>
          <span id="labelSeksyen" class="switch-label-status on">Aktif</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" id="btnSubmitSeksyen" class="btn rounded-pill btn-primary waves-effect">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== MODAL: TAMBAH CAWANGAN ===================== --}}
<div class="modal fade" id="modalAddCawangan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAddCawangan" class="modal-content">
      @csrf
      <input type="hidden" name="type" value="cawangan">
      <input type="hidden" name="id_seksyen" id="cawangan_id_seksyen">
      <input type="hidden" name="status" id="cawangan_status" value="1">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Cawangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Agensi</label>
          <input type="text" class="form-control" id="cawangan_agensi_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Bahagian</label>
          <input type="text" class="form-control" id="cawangan_bahagian_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Seksyen</label>
          <input type="text" class="form-control" id="cawangan_seksyen_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Cawangan Baharu</label>
          <input type="text" class="form-control" name="cawangan" id="cawangan_name" required>
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="switch switch-primary mb-0">
            <input type="checkbox" class="switch-input" id="switchCawangan" checked>
            <span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>
          </label>
          <span id="labelCawangan" class="switch-label-status on">Aktif</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" id="btnSubmitCawangan" class="btn rounded-pill btn-primary waves-effect">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== MODAL: EDIT AGENSI ===================== --}}
<div class="modal fade" id="modalEditAgensi" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formEditAgensi" class="modal-content">
      @csrf
      <input type="hidden" name="type" value="kementerian">
      <input type="hidden" name="id" id="edit_agensi_id">
      <input type="hidden" name="status" id="edit_agensi_status" value="1">

      <div class="modal-header">
        <h5 class="modal-title">Kemaskini Agensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Agensi</label>
          <input type="text" class="form-control" name="agensi" id="edit_agensi_name" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Akronim (Singkatan)</label>
          <input type="text" class="form-control" name="akronim" id="edit_agensi_akronim" placeholder="Contoh: KPN">
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="switch switch-primary mb-0">
            <input type="checkbox" class="switch-input" id="editSwitchAgensi" checked>
            <span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>
          </label>
          <span id="editLabelAgensi" class="switch-label-status on">Aktif</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn rounded-pill btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" id="btnUpdateAgensi" class="btn rounded-pill btn-warning">Kemaskini</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== MODAL: EDIT BAHAGIAN ===================== --}}
<div class="modal fade" id="modalEditBahagian" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formEditBahagian" class="modal-content">
      @csrf
      <input type="hidden" name="type" value="bahagian">
      <input type="hidden" name="id" id="edit_bahagian_id">
      <input type="hidden" name="agensi_id" id="edit_bahagian_agensi_id">
      <input type="hidden" name="status" id="edit_bahagian_status" value="1">

      <div class="modal-header">
        <h5 class="modal-title">Kemaskini Bahagian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Agensi</label>
          <input type="text" class="form-control" id="edit_bahagian_agensi_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Bahagian</label>
          <input type="text" class="form-control" name="bahagian" id="edit_bahagian_name" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Singkatan</label>
          <input type="text" class="form-control" name="singkatan" id="edit_bahagian_singkatan">
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="switch switch-primary mb-0">
            <input type="checkbox" class="switch-input" id="editSwitchBahagian" checked>
            <span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>
          </label>
          <span id="editLabelBahagian" class="switch-label-status on">Aktif</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn rounded-pill btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" id="btnUpdateBahagian" class="btn rounded-pill btn-warning">Kemaskini</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== MODAL: EDIT SEKSYEN ===================== --}}
<div class="modal fade" id="modalEditSeksyen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formEditSeksyen" class="modal-content">
      @csrf
      <input type="hidden" name="type" value="seksyen">
      <input type="hidden" name="id" id="edit_seksyen_id">
      <input type="hidden" name="bahagian_id" id="edit_seksyen_bahagian_id">
      <input type="hidden" name="status" id="edit_seksyen_status" value="1">

      <div class="modal-header">
        <h5 class="modal-title">Kemaskini Seksyen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Agensi</label>
          <input type="text" class="form-control" id="edit_seksyen_agensi_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Bahagian</label>
          <input type="text" class="form-control" id="edit_seksyen_bahagian_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Seksyen</label>
          <input type="text" class="form-control" name="seksyen" id="edit_seksyen_name" required>
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="switch switch-primary mb-0">
            <input type="checkbox" class="switch-input" id="editSwitchSeksyen" checked>
            <span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>
          </label>
          <span id="editLabelSeksyen" class="switch-label-status on">Aktif</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn rounded-pill btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" id="btnUpdateSeksyen" class="btn rounded-pill btn-warning">Kemaskini</button>
      </div>
    </form>
  </div>
</div>

{{-- ===================== MODAL: EDIT CAWANGAN ===================== --}}
<div class="modal fade" id="modalEditCawangan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formEditCawangan" class="modal-content">
      @csrf
      <input type="hidden" name="type" value="cawangan">
      <input type="hidden" name="id" id="edit_cawangan_id">
      <input type="hidden" name="id_seksyen" id="edit_cawangan_id_seksyen">
      <input type="hidden" name="status" id="edit_cawangan_status" value="1">

      <div class="modal-header">
        <h5 class="modal-title">Kemaskini Cawangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Agensi</label>
          <input type="text" class="form-control" id="edit_cawangan_agensi_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Bahagian</label>
          <input type="text" class="form-control" id="edit_cawangan_bahagian_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Seksyen</label>
          <input type="text" class="form-control" id="edit_cawangan_seksyen_text" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Nama Cawangan</label>
          <input type="text" class="form-control" name="cawangan" id="edit_cawangan_name" required>
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="switch switch-primary mb-0">
            <input type="checkbox" class="switch-input" id="editSwitchCawangan" checked>
            <span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>
          </label>
          <span id="editLabelCawangan" class="switch-label-status on">Aktif</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn rounded-pill btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" id="btnUpdateCawangan" class="btn rounded-pill btn-warning">Kemaskini</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

  const treeEl   = $('#orgTree');
  const urlTree  = @json(route('org.tree'));
  const urlStore = @json(route('org.store'));
  const urlUpdate= @json(route('org.update'));
  const redirectUrl = @json(route('org.index'));

  const modalAddAgensi   = new bootstrap.Modal(document.getElementById('modalAddAgensi'));
  const modalAddBahagian = new bootstrap.Modal(document.getElementById('modalAddBahagian'));
  const modalAddSeksyen  = new bootstrap.Modal(document.getElementById('modalAddSeksyen'));
  const modalAddCawangan = new bootstrap.Modal(document.getElementById('modalAddCawangan'));

  const modalEditAgensi   = new bootstrap.Modal(document.getElementById('modalEditAgensi'));
  const modalEditBahagian = new bootstrap.Modal(document.getElementById('modalEditBahagian'));
  const modalEditSeksyen  = new bootstrap.Modal(document.getElementById('modalEditSeksyen'));
  const modalEditCawangan = new bootstrap.Modal(document.getElementById('modalEditCawangan'));

  function statusClass(node){
    return parseInt(node?.data?.status ?? 1, 10) === 1 ? 'org-active' : 'org-inactive';
  }

  function nextChildType(parentType){
    if (parentType === 'root') return 'kementerian';
    if (parentType === 'kementerian') return 'bahagian';
    if (parentType === 'bahagian') return 'seksyen';
    if (parentType === 'seksyen') return 'cawangan';
    return null;
  }

  function addBtnHtml(childType, nodeId){
    return `<i class="mdi mdi-plus-circle-outline org-add-btn"
              data-child-type="${childType}"
              data-node-id="${nodeId}"
              title="Tambah"></i>`;
  }

  function findAncestorText(inst, node, typeWanted){
    let cur = node;
    while (cur && cur.parent && cur.parent !== '#') {
      if (cur.type === typeWanted) return cur.original?.text || cur.text || '';
      cur = inst.get_node(cur.parent);
    }
    return '';
  }

  function findAncestorNode(inst, node, typeWanted){
    let cur = node;
    while (cur && cur.parent && cur.parent !== '#') {
      if (cur.type === typeWanted) return cur;
      cur = inst.get_node(cur.parent);
    }
    return null;
  }

  // ===== switch binder (UNTUK SEMUA) =====
  function bindSwitch(switchId, hiddenId, labelId){
    const sw  = document.getElementById(switchId);
    const hid = document.getElementById(hiddenId);
    const lab = document.getElementById(labelId);
    if (!sw || !hid) return;

    function apply(){
      const on = !!sw.checked;
      hid.value = on ? '1' : '0';
      if (lab){
        lab.textContent = on ? 'Aktif' : 'Tidak Aktif';
        lab.classList.toggle('on', on);
        lab.classList.toggle('off', !on);
      }
    }
    sw.addEventListener('change', apply);
    apply();
  }

  // add
  bindSwitch('switchAgensi','agensi_status','labelAgensi');
  bindSwitch('switchBahagian','bahagian_status','labelBahagian');
  bindSwitch('switchSeksyen','seksyen_status','labelSeksyen');
  bindSwitch('switchCawangan','cawangan_status','labelCawangan');

  // edit
  bindSwitch('editSwitchAgensi','edit_agensi_status','editLabelAgensi');
  bindSwitch('editSwitchBahagian','edit_bahagian_status','editLabelBahagian');
  bindSwitch('editSwitchSeksyen','edit_seksyen_status','editLabelSeksyen');
  bindSwitch('editSwitchCawangan','edit_cawangan_status','editLabelCawangan');

  // ===== init jstree =====
  if (treeEl.jstree(true)) treeEl.jstree('destroy');

  treeEl.jstree({
    core: { data: { url: urlTree, dataType: 'json' }, themes: { responsive: true } },
    types: {
      root:        { icon: 'mdi mdi-sitemap' },
      kementerian: { icon: 'mdi mdi-domain' },
      bahagian:    { icon: 'mdi mdi-layers-triple' },
      seksyen:     { icon: 'mdi mdi-file-tree' },
      cawangan:    { icon: 'mdi mdi-source-branch' }
    },
    plugins: ['types', 'wholerow']
  })
  .on('loaded.jstree', function(){ treeEl.jstree('open_all'); })
  .on('ready.jstree after_open.jstree refresh.jstree', function(){
    const inst = treeEl.jstree(true);
    const all  = inst.get_json('#', { flat: true });

    all.forEach(n => {
      const li = document.getElementById(n.id);
      if (!li) return;

      li.classList.remove('org-active','org-inactive');
      li.classList.add(statusClass(n));

      const st = parseInt(n?.data?.status ?? 1, 10);
      if (st !== 1) return;

      const childType = nextChildType(n.type);
      if (!childType) return;

      const a = li.querySelector('a.jstree-anchor');
      if (!a || a.querySelector('.org-add-btn')) return;

      const entityId = (n.type === 'root') ? 'root' : (n?.data?.entity_id ?? '');
      a.insertAdjacentHTML('beforeend', addBtnHtml(childType, entityId));
    });
  });

  // ===== klik icon tambah =====
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.org-add-btn');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const childType = btn.dataset.childType;
    const parentId  = btn.dataset.nodeId;

    const li   = btn.closest('li');
    const inst = treeEl.jstree(true);
    const node = li ? inst.get_node(li.id) : null;

    if (childType === 'kementerian') {
      document.getElementById('agensi_name').value = '';
      document.getElementById('agensi_akronim').value = '';
      document.getElementById('switchAgensi').checked = true;
      document.getElementById('switchAgensi').dispatchEvent(new Event('change'));
      modalAddAgensi.show();
      return;
    }

    if (childType === 'bahagian') {
      document.getElementById('bahagian_agensi_id').value = parentId;
      document.getElementById('bahagian_agensi_text').value = node?.original?.text || node?.text || '';
      modalAddBahagian.show();
      return;
    }

    if (childType === 'seksyen') {
      document.getElementById('seksyen_bahagian_id').value = parentId;
      document.getElementById('seksyen_bahagian_text').value = node?.original?.text || node?.text || '';
      document.getElementById('seksyen_agensi_text').value = findAncestorText(inst, node, 'kementerian');
      modalAddSeksyen.show();
      return;
    }

    if (childType === 'cawangan') {
      document.getElementById('cawangan_id_seksyen').value = parentId;
      document.getElementById('cawangan_seksyen_text').value = node?.original?.text || node?.text || '';
      document.getElementById('cawangan_bahagian_text').value = findAncestorText(inst, node, 'bahagian');
      document.getElementById('cawangan_agensi_text').value = findAncestorText(inst, node, 'kementerian');
      modalAddCawangan.show();
      return;
    }
  });

  // ===== klik NAMA node => edit (block kalau klik icon +) =====
  treeEl.on('select_node.jstree', function(e, data){
    if (data.event && data.event.target.closest('.org-add-btn')) return;

    const inst = treeEl.jstree(true);
    const node = data.node;
    if (!node || node.type === 'root') return;

    const status = parseInt(node?.data?.status ?? 1, 10);

    if (node.type === 'kementerian') {
      document.getElementById('edit_agensi_id').value = node.data.entity_id;
      document.getElementById('edit_agensi_name').value = node.data.nama_asal
        ?? (node.original?.text || '').replace(/\s*\(.*?\)\s*/g,'');
      document.getElementById('edit_agensi_akronim').value = node.data.akronim ?? '';

      document.getElementById('editSwitchAgensi').checked = (status === 1);
      document.getElementById('editSwitchAgensi').dispatchEvent(new Event('change'));

      modalEditAgensi.show();
      return;
    }

    if (node.type === 'bahagian') {
      const agensiNode = findAncestorNode(inst, node, 'kementerian');
      document.getElementById('edit_bahagian_id').value = node.data.entity_id;
      document.getElementById('edit_bahagian_agensi_id').value = node.data.agensi_id || (agensiNode?.data?.entity_id ?? '');
      document.getElementById('edit_bahagian_agensi_text').value = agensiNode?.original?.text || agensiNode?.text || '';
      document.getElementById('edit_bahagian_name').value = node.original?.text || node.text;
      document.getElementById('edit_bahagian_singkatan').value = node.data.singkatan ?? '';
      document.getElementById('editSwitchBahagian').checked = (status === 1);
      document.getElementById('editSwitchBahagian').dispatchEvent(new Event('change'));
      modalEditBahagian.show();
      return;
    }

    if (node.type === 'seksyen') {
      const bahagianNode = findAncestorNode(inst, node, 'bahagian');
      document.getElementById('edit_seksyen_id').value = node.data.entity_id;
      document.getElementById('edit_seksyen_bahagian_id').value = node.data.bahagian_id || (bahagianNode?.data?.entity_id ?? '');
      document.getElementById('edit_seksyen_agensi_text').value = findAncestorText(inst, node, 'kementerian');
      document.getElementById('edit_seksyen_bahagian_text').value = bahagianNode?.original?.text || bahagianNode?.text || '';
      document.getElementById('edit_seksyen_name').value = node.original?.text || node.text;
      document.getElementById('editSwitchSeksyen').checked = (status === 1);
      document.getElementById('editSwitchSeksyen').dispatchEvent(new Event('change'));
      modalEditSeksyen.show();
      return;
    }

    if (node.type === 'cawangan') {
      const seksyenNode = findAncestorNode(inst, node, 'seksyen');
      document.getElementById('edit_cawangan_id').value = node.data.entity_id;
      document.getElementById('edit_cawangan_id_seksyen').value = node.data.seksyen_id || (seksyenNode?.data?.entity_id ?? '');
      document.getElementById('edit_cawangan_agensi_text').value = findAncestorText(inst, node, 'kementerian');
      document.getElementById('edit_cawangan_bahagian_text').value = findAncestorText(inst, node, 'bahagian');
      document.getElementById('edit_cawangan_seksyen_text').value = seksyenNode?.original?.text || seksyenNode?.text || '';
      document.getElementById('edit_cawangan_name').value = node.original?.text || node.text;
      document.getElementById('editSwitchCawangan').checked = (status === 1);
      document.getElementById('editSwitchCawangan').dispatchEvent(new Event('change'));
      modalEditCawangan.show();
      return;
    }
  });

  // ===== submit AJAX => redirect /org =====
  function attachSubmit(url, formId, btnId, modalInstance){
    const formEl = document.getElementById(formId);
    const btnEl  = document.getElementById(btnId);
    if (!formEl || !btnEl) return;

    formEl.addEventListener('submit', async function(e){
      e.preventDefault();
      e.stopImmediatePropagation();

      if (!formEl.checkValidity()) {
        formEl.reportValidity();
        return;
      }

      const originalHtml = btnEl.innerHTML;
      btnEl.disabled = true;
      btnEl.innerHTML = 'Menghantar...';

      try {
        const fd = new FormData(formEl);

        // uppercase akronim client-side (backup)
        if (fd.has('akronim') && fd.get('akronim')) {
          fd.set('akronim', String(fd.get('akronim')).trim().toUpperCase());
        }

        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: fd
        });

        const out = await res.json().catch(() => ({}));

        if (!res.ok || !out.ok) {
          alert(out.message || 'Gagal simpan/kemaskini data.');
          return;
        }

        modalInstance.hide();

        // ✅ redirect balik /org
        window.location.href = redirectUrl;

      } catch (err) {
        console.error(err);
        alert('Ralat sistem. Sila cuba lagi.');
      } finally {
        btnEl.disabled = false;
        btnEl.innerHTML = originalHtml;
      }
    }, true);
  }

  // add
  attachSubmit(urlStore,'formAddAgensi','btnSubmitAgensi',modalAddAgensi);
  attachSubmit(urlStore,'formAddBahagian','btnSubmitBahagian',modalAddBahagian);
  attachSubmit(urlStore,'formAddSeksyen','btnSubmitSeksyen',modalAddSeksyen);
  attachSubmit(urlStore,'formAddCawangan','btnSubmitCawangan',modalAddCawangan);

  // edit
  attachSubmit(urlUpdate,'formEditAgensi','btnUpdateAgensi',modalEditAgensi);
  attachSubmit(urlUpdate,'formEditBahagian','btnUpdateBahagian',modalEditBahagian);
  attachSubmit(urlUpdate,'formEditSeksyen','btnUpdateSeksyen',modalEditSeksyen);
  attachSubmit(urlUpdate,'formEditCawangan','btnUpdateCawangan',modalEditCawangan);

});
</script>
@endsection
