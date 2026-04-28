<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya</title>

<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/materialdesignicons.css') }}">

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    min-height: 100vh;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e3a8a, #312e81);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 25px;
}

.page-card {
    width: 1120px;
    max-width: 100%;
    height: 88vh;
    background: #eef2ff;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 35px 90px rgba(0,0,0,.45);
}

.profile-header {
    height: 175px;
    background: linear-gradient(135deg, #2563eb, #7c3aed, #c026d3);
    padding: 22px 38px;
    display: flex;
    align-items: flex-end;
    gap: 24px;
    position: relative;
}

.logout-top {
    position: absolute;
    top: 18px;
    right: 24px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid #fff;
    background: transparent;
    color: #fff;
    cursor: pointer;
    font-size: 20px;
    transition: .25s;
}

.logout-top:hover {
    background: #0f172a;
    border-color: #0f172a;
}

.avatar {
    width: 118px;
    height: 118px;
    border-radius: 22px;
    object-fit: cover;
    border: 6px solid #fff;
    transform: translateY(34px);
    box-shadow: 0 15px 35px rgba(0,0,0,.25);
}

.profile-title {
    transform: translateY(18px);
}

.profile-title h1 {
    margin: 0;
    color: #fff;
    font-size: 29px;
    line-height: 1.15;
}

.profile-title p {
    margin: 6px 0 8px;
    color: #e0e7ff;
    font-weight: bold;
}

.status-active,
.status-inactive {
    font-weight: bold;
    font-size: 14px;
}

.status-active { color: #22c55e; }
.status-inactive { color: #ff4d4d; }

.menu {
    position: absolute;
    top: 62px;
    right: 92px;
    width: 110px;
    height: 110px;
    padding: 0;
    margin: 0;
    z-index: 10;
}

.menu .toggle {
    position: absolute;
    left: 35px;
    top: 35px;
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #2563eb, #7c3aed, #c026d3);
    border-radius: 50%;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    font-size: 26px;
    transition: .3s;
    box-shadow: 0 10px 25px rgba(0,0,0,.25);
    z-index: 20;
}

.menu.active .toggle,
.menu:hover .toggle {
    background: #0f172a;
    transform: rotate(45deg);
}

.menu li {
    list-style: none;
    position: absolute;
    left: 40px;
    top: 40px;
    opacity: 0;
    visibility: hidden;
    transform: rotate(calc(360deg / 6 * var(--i))) translateX(0);
    transition: .3s;
}

.menu.active li,
.menu:hover li {
    opacity: 1;
    visibility: visible;
    transform: rotate(calc(360deg / 6 * var(--i))) translateX(68px);
}

.menu li button {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: none;
    background: #ffffff;
    color: #1e3a8a;
    cursor: pointer;
    font-size: 21px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: rotate(calc(360deg / -6 * var(--i)));
    box-shadow: 0 8px 20px rgba(0,0,0,.18);
    transition: .25s;
}

.menu li button:hover,
.menu li.active button {
    background: linear-gradient(135deg, #2563eb, #7c3aed, #c026d3);
    color: white;
    transform: rotate(calc(360deg / -6 * var(--i))) scale(1.12);
}

.menu-label {
    position: absolute;
    left: -35px;
    top: 100px;
    width: 180px;
    text-align: center;
    color: #fff;
    font-size: 13px;
    font-weight: bold;
    text-shadow: 0 2px 8px rgba(0,0,0,.35);
}

.content {
    height: calc(88vh - 175px);
    padding: 55px 35px 35px;
}

.box {
    height: 100%;
    background: rgba(255,255,255,.86);
    border-radius: 24px;
    padding: 28px;
    overflow-y: auto;
    box-shadow: 0 15px 40px rgba(15,23,42,.12);
}

.section { display: none; }
.section.active {
    display: block;
    animation: fadeIn .3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(14px); }
    to { opacity: 1; transform: translateY(0); }
}

.section h3 {
    margin: 0 0 22px;
    color: #1e3a8a;
    font-size: 24px;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.item {
    background: #f8fafc;
    padding: 16px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(15,23,42,.06);
}

.item.full {
    grid-column: 1 / -1;
}

.item label {
    display: block;
    font-size: 12px;
    color: #64748b;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 6px;
}

.item div {
    font-weight: bold;
    color: #0f172a;
}

.timeline {
    padding-left: 20px;
}

.timeline-item {
    border-left: 3px solid #93c5fd;
    padding: 0 0 20px 22px;
    position: relative;
}

.timeline-item::before {
    content: "";
    position: absolute;
    left: -9px;
    top: 3px;
    width: 14px;
    height: 14px;
    background: #2563eb;
    border-radius: 50%;
    border: 3px solid #dbeafe;
}

.timeline-card {
    background: #f8fafc;
    border-radius: 14px;
    padding: 16px;
}

.timeline-title {
    font-weight: bold;
    color: #1e3a8a;
}

.timeline-date {
    font-size: 13px;
    color: #64748b;
    margin: 5px 0 8px;
}

@media (max-width: 900px) {
    body { padding: 15px; align-items: flex-start; }

    .page-card {
        height: auto;
        min-height: 95vh;
    }

    .profile-header {
        height: auto;
        min-height: 300px;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        text-align: center;
        padding-top: 160px;
    }

    .avatar,
    .profile-title {
        transform: none;
    }

    .menu {
        top: 55px;
        right: 50%;
        transform: translateX(50%);
    }

    .content {
        height: auto;
        padding: 30px 20px;
    }

    .box {
        height: auto;
    }

    .grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

@php
    $isActive = (($staff->id_status_pegawai ?? $staff->id_stat_pegawai ?? null) == 1);

    $lastDigit = intval(substr($staff->mykad ?? '0', -1));
    $isFemale = ($lastDigit % 2 === 0);
    $avatar = $isFemale ? asset('assets/img/avatars/2.png') : asset('assets/img/avatars/1.png');
@endphp

<div class="page-card">

    <div class="profile-header">

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-top" title="Logout">
                <i class="mdi mdi-logout"></i>
            </button>
        </form>

        <ul class="menu" id="menu">
            <div class="toggle" onclick="toggleMenu()">
                <i class="mdi mdi-plus"></i>
            </div>

            <li style="--i:0" class="active" data-label="Peribadi">
                <button type="button" onclick="show('peribadi', this)">
                    <i class="mdi mdi-account"></i>
                </button>
            </li>

            <li style="--i:1" data-label="Perjawatan">
                <button type="button" onclick="show('perjawatan', this)">
                    <i class="mdi mdi-briefcase"></i>
                </button>
            </li>

            <li style="--i:2" data-label="Keluarga">
                <button type="button" onclick="show('keluarga', this)">
                    <i class="mdi mdi-account-group"></i>
                </button>
            </li>

            <li style="--i:3" data-label="Kelayakan">
                <button type="button" onclick="show('kelayakan', this)">
                    <i class="mdi mdi-school"></i>
                </button>
            </li>

            <li style="--i:4" data-label="Penempatan">
                <button type="button" onclick="show('penempatan', this)">
                    <i class="mdi mdi-timeline-clock-outline"></i>
                </button>
            </li>

            <li style="--i:5" data-label="Tambahan">
                <button type="button" onclick="show('tambahan', this)">
                    <i class="mdi mdi-dots-horizontal"></i>
                </button>
            </li>

            <div class="menu-label" id="menuLabel">Peribadi</div>
        </ul>

        <img src="{{ $avatar }}" class="avatar" alt="Avatar">

        <div class="profile-title">
            <h1>{{ $staff->nama_gelaran ?? '' }} {{ $staff->nama ?? '-' }}</h1>
            <p>{{ $staff->jawatan_penempatan ?? '-' }}</p>

            <div class="{{ $isActive ? 'status-active' : 'status-inactive' }}">
                <i class="mdi {{ $isActive ? 'mdi-account-check' : 'mdi-account-off' }}"></i>
                {{ $isActive ? 'Pegawai Aktif' : 'Tidak Aktif' }}
            </div>
        </div>

    </div>

    <div class="content">
        <div class="box">

            <div id="peribadi" class="section active">
                <h3>Maklumat Peribadi</h3>
                <div class="grid">
                    <div class="item">
                        <label>No. MyKad</label>
                        <div>{{ $staff->mykad ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Tarikh Lahir</label>
                        <div>
                            @if (!empty($staff->tarikh_lahir))
                                {{ \Carbon\Carbon::parse($staff->tarikh_lahir)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="item">
                        <label>Negeri Lahir</label>
                        <div>{{ $staff->negeri_lahir ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Jantina</label>
                        <div>{{ $staff->jantina ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Status Perkahwinan</label>
                        <div>{{ $staff->status_kahwin ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Bangsa</label>
                        <div>{{ $staff->nama_bangsa ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Etnik</label>
                        <div>{{ $staff->nama_etnik ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Agama</label>
                        <div>{{ $staff->nama_agama ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>No. Telefon</label>
                        <div>{{ $staff->no_telefon ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>No. Telefon Bimbit</label>
                        <div>{{ $staff->no_hp ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Emel</label>
                        <div>{{ $staff->emel ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>MyDigital ID</label>
                        <div>{{ $staff->status_mydid ?? '-' }}</div>
                    </div>

                    <div class="item full">
                        <label>Alamat</label>
                        <div>{{ $staff->alamat ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div id="perjawatan" class="section">
                <h3>Maklumat Perjawatan</h3>
                <div class="grid">
                    <div class="item">
                        <label>Status Perkhidmatan</label>
                        <div>{{ $staff->status_perkhidmatan ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Kumpulan Perkhidmatan</label>
                        <div>{{ $staff->kump_perkhidmatan ?? '-' }}</div>
                    </div>

                    <div class="item full">
                        <label>Klasifikasi Perkhidmatan</label>
                        <div>
                            {{ $staff->kod_klasifikasi_perkhidmatan ?? '-' }}
                            @if(!empty($staff->klasifikasi_perkhidmatan))
                                - {{ $staff->klasifikasi_perkhidmatan }}
                            @endif
                        </div>
                    </div>

                    <div class="item">
                        <label>Skim Perkhidmatan</label>
                        <div>{{ $staff->skim_perkhidmatan ?? '-' }}</div>
                    </div>

                    <div class="item">
                        <label>Kod & Gred Skim</label>
                        <div>
                            {{ $staff->kod_skim_perkhidmatan ?? '-' }}
                            @if(!empty($staff->gred))
                                {{ $staff->gred }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div id="keluarga" class="section">
                <h3>Maklumat Keluarga</h3>
                <div class="item full">
                    <label>Maklumat Keluarga</label>
                    <div>Maklumat keluarga akan dipaparkan di sini.</div>
                </div>
            </div>

            <div id="kelayakan" class="section">
                <h3>Maklumat Kelayakan</h3>
                <div class="item full">
                    <label>Maklumat Kelayakan</label>
                    <div>Maklumat kelayakan akan dipaparkan di sini.</div>
                </div>
            </div>

            <div id="penempatan" class="section">
                <h3>Sejarah Penempatan</h3>

                <div class="timeline">
                    @forelse (($penempatanHistory ?? []) as $pen)
                        <div class="timeline-item">
                            <div class="timeline-card">
                                <div class="timeline-title">
                                    {{ $pen->kategori_penempatan ?? 'Penempatan' }}
                                </div>

                                <div class="timeline-date">
                                    @if ((int)($pen->id_lkp_penempatan ?? 0) === 4)
                                        {{ !empty($pen->tarikh_keluar) ? \Carbon\Carbon::parse($pen->tarikh_keluar)->format('d/m/Y') : '-' }}
                                    @else
                                        {{ !empty($pen->tarikh_masuk) ? \Carbon\Carbon::parse($pen->tarikh_masuk)->format('d/m/Y') : '-' }}
                                    @endif
                                </div>

                                @if (($pen->id_lkp_penempatan ?? null) == 4)
                                    <div>{{ $pen->in_out ?? '-' }}</div>
                                @else
                                    <div>{{ $pen->nama_agensi ?? '-' }}</div>
                                    <div><strong>{{ $pen->nama_bahagian ?? '-' }}</strong></div>
                                    <div>{{ $pen->nama_seksyen ?? '-' }}</div>
                                    <div>{{ $pen->nama_cawangan ?? '-' }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="item full">
                            <label>Penempatan</label>
                            <div>Tiada rekod penempatan direkodkan.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div id="tambahan" class="section">
                <h3>Maklumat Tambahan</h3>
                <div class="item full">
                    <label>Maklumat Tambahan</label>
                    <div>Maklumat tambahan akan dipaparkan di sini.</div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
function toggleMenu() {
    document.getElementById('menu').classList.toggle('active');
}

function show(id, button) {
    document.querySelectorAll('.section').forEach(e => e.classList.remove('active'));
    document.getElementById(id).classList.add('active');

    document.querySelectorAll('.menu li').forEach(li => li.classList.remove('active'));
    button.closest('li').classList.add('active');

    document.getElementById('menuLabel').textContent = button.closest('li').dataset.label;
}
</script>

</body>
</html>
