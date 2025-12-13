@extends('layouts.app')
@section('title', 'Kelola Karyawan')
@section('content')

{{-- 1. ERROR VALIDASI FORM (Tampil Statis di Atas) --}}
@if ($errors->any())
    <div style="
        background: rgba(239, 68, 68, 0.1); 
        border: 1px solid rgba(239, 68, 68, 0.3); 
        color: #f87171; 
        padding: 12px 16px; 
        border-radius: 12px; 
        margin-bottom: 20px; 
        font-size: 14px; 
        backdrop-filter: blur(5px);
        animation: slideDown 0.3s ease-out;">
        <strong style="display:block; margin-bottom:4px;"><i class="fa-solid fa-triangle-exclamation"></i> Terjadi Kesalahan:</strong>
        <ul style="margin:0; padding-left:20px; opacity:0.9;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<style>
    /* =========================================
       🎨 STYLE CONFIGURATION
       ========================================= */
    :root { --text-sub: #9ca3af; }
    
    @keyframes slideDown { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(120%); opacity: 0; } }
    @keyframes modalIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }

    /* HEADER CARD */
    .header-card {
        background: var(--glass-bg); padding: 24px; border-radius: 16px;
        border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow);
        backdrop-filter: blur(12px); margin-bottom: 24px;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
    }
    .header-title { font-size: 24px; font-weight: 800; color: white; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
    .header-sub { font-size: 13px; margin-top: 6px; color: var(--text-sub); }

    /* LEGEND */
    .legend { display:flex; gap:12px; margin-top:10px; flex-wrap: wrap; }
    .legend-item {
        background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border);
        padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; color: white;
        display: flex; align-items: center; gap: 8px; backdrop-filter: blur(5px);
    }
    .legend-dot { width: 8px; height: 8px; border-radius: 50%; box-shadow: 0 0 8px rgba(255,255,255,0.4); }
    .green { background: #4ade80; box-shadow: 0 0 8px #4ade80; }
    .yellow { background: #facc15; box-shadow: 0 0 8px #facc15; }
    .red { background: #f87171; box-shadow: 0 0 8px #f87171; }

    /* TABLE STYLES */
    .table-card {
        background: var(--glass-bg); border-radius: 16px; border: 1px solid var(--glass-border);
        overflow: hidden; box-shadow: var(--glass-shadow); backdrop-filter: blur(12px);
    }
    table { width: 100%; border-collapse: collapse; }
    th {
        background: rgba(0,0,0,0.2); padding: 16px 20px; text-align: left;
        font-size: 12px; font-weight: 700; color: var(--text-sub);
        text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--glass-border);
    }
    td {
        padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.05);
        font-size: 14px; color: white; vertical-align: middle;
    }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(255,255,255,0.08); }

    /* UI COMPONENTS (Badge, Avatar, Buttons) */
    .pegawai-flex { display: flex; align-items: center; gap: 14px; }
    .avatar {
        width: 42px; height: 42px; border-radius: 12px;
        background: linear-gradient(135deg, var(--accent), #d97706);
        display: flex; justify-content: center; align-items: center;
        color: white; font-weight: 800; font-size: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.2);
    }
    .pegawai-username { font-size: 12px; color: var(--text-sub); margin-top: 2px; }

    .badge {
        padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;
        display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; border: 1px solid transparent;
    }
    .badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; }
    .Aktif { background: rgba(74, 222, 128, 0.15); color: #4ade80; border-color: rgba(74, 222, 128, 0.3); }
    .Aktif::before { background: #4ade80; box-shadow: 0 0 5px #4ade80; }
    .Cuti { background: rgba(250, 204, 21, 0.15); color: #facc15; border-color: rgba(250, 204, 21, 0.3); }
    .Cuti::before { background: #facc15; box-shadow: 0 0 5px #facc15; }
    .Nonaktif { background: rgba(248, 113, 113, 0.15); color: #f87171; border-color: rgba(248, 113, 113, 0.3); }
    .Nonaktif::before { background: #f87171; box-shadow: 0 0 5px #f87171; }

    .btn-main {
        background: linear-gradient(135deg, var(--accent), #d97706); color: white; padding: 10px 20px;
        border-radius: 10px; border: none; font-weight: 700; cursor: pointer;
        box-shadow: 0 4px 15px rgba(245,158,11,0.4); transition: transform 0.2s;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-main:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245,158,11,0.6); }

    .act-btn {
        border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05);
        width: 32px; height: 32px; border-radius: 8px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; transition: 0.2s;
    }
    .act-btn:hover { background: rgba(255,255,255,0.2); transform: scale(1.1); }
    .edit { color: #fbbf24; } .delete { color: #f87171; }

    /* MODAL STYLES */
    .modal-backdrop {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7);
        backdrop-filter: blur(5px); align-items: center; justify-content: center; z-index: 5000; padding: 20px;
    }
    .modal-content {
        background: rgba(30, 30, 30, 0.95); width: 100%; max-width: 500px;
        border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        border: 1px solid var(--glass-border); color: white; animation: modalIn 0.2s ease-out;
    }
    .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; font-size: 18px; font-weight: 700; background: rgba(255,255,255,0.03); }
    .modal-body { padding: 24px; }
    .modal-footer { padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px; background: rgba(0,0,0,0.2); border-top: 1px solid var(--glass-border); }
    
    .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .full { grid-column: span 2; }
    
    label { font-size: 12px; font-weight: 600; color: var(--text-sub); margin-bottom: 6px; display: block; }
    input, select, textarea {
        width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--glass-border);
        background: rgba(0,0,0,0.3); color: white; font-size: 14px; outline: none; transition: 0.2s;
    }
    input:focus, select:focus, textarea:focus { border-color: var(--accent); background: rgba(0,0,0,0.5); box-shadow: 0 0 0 2px rgba(247, 162, 10, 0.2); }
    
    .btn-cancel { background: transparent; border: 1px solid var(--glass-border); color: #d1d5db; padding: 10px 18px; border-radius: 10px; cursor: pointer; font-weight: 600; transition: 0.2s; }
    .btn-cancel:hover { background: rgba(255,255,255,0.1); color: white; }

    /* TOAST STYLES (TOP RIGHT) */
    .toast-container {
        position: fixed; top: 24px; right: 24px; z-index: 9999;
        display: flex; flex-direction: column; gap: 12px; pointer-events: none;
    }
    .toast {
        min-width: 300px; padding: 16px 20px; border-radius: 14px;
        background: rgba(20, 20, 20, 0.85); color: white;
        backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
        box-shadow: 0 10px 40px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1);
        display: flex; align-items: center; gap: 14px; pointer-events: auto;
        animation: slideInRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        transform: translateX(120%);
    }
    .toast.success { border-left: 4px solid #4ade80; }
    .toast.error { border-left: 4px solid #f87171; }
    .toast i { font-size: 18px; }
    .toast span { font-size: 14px; font-weight: 500; }
</style>

{{-- ================= HEADER =================--}}
<div class="header-card">
    <div style="flex: 1;">
        <h2 class="header-title">Kelola Karyawan</h2>
        <p class="header-sub">Manajemen data pegawai, akun, dan status kerja.</p>
        <div class="legend">
            <div class="legend-item"><span class="legend-dot green"></span> Aktif: {{ $aktifCount }}</div>
            <div class="legend-item"><span class="legend-dot yellow"></span> Cuti: {{ $cutiCount }}</div>
            <div class="legend-item"><span class="legend-dot red"></span> Nonaktif: {{ $nonaktifCount }}</div>
        </div>
    </div>
    <button class="btn-main" onclick="openModal('modalTambah')">
        <i class="fa-solid fa-user-plus"></i> Tambah Karyawan
    </button>
</div>

{{-- ================= TABLE =================--}}
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama & Username</th>
                <th>Posisi</th>
                <th>Status</th>
                <th>Info Cuti</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pegawais as $p)
            <tr>
                <td style="color:var(--text-sub); font-weight:600;">{{ $loop->iteration }}</td>
                <td>
                    <div class="pegawai-flex">
                        <div class="avatar">{{ strtoupper(substr($p->nama, 0, 1)) }}</div>
                        <div>
                            <div style="font-weight:700;">{{ $p->nama }}</div>
                            <div class="pegawai-username">{{ '@' . optional($p->user)->username }}</div>
                        </div>
                    </div>
                </td>
                <td><span style="font-size:13px; opacity:0.9;">{{ ucfirst($p->jabatan) }}</span></td>
                <td><span class="badge {{ $p->status }}">{{ $p->status }}</span></td>
                <td style="color:#fcd34d; font-size:13px; font-style:italic;">{{ $p->status === 'Cuti' ? ($p->alasan_cuti ?? '-') : '-' }}</td>
                <td style="text-align:right;">
                    <button class="act-btn edit" onclick="openEditModal({{ $p->id }})" title="Edit Data"><i class="fa-solid fa-pen"></i></button>
                    <button class="act-btn delete" onclick="openDeleteModal({{ $p->id }}, '{{ $p->nama }}')" title="Hapus Data"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ================= MODAL TAMBAH =================--}}
<div id="modalTambah" class="modal-backdrop" onclick="closeModal('modalTambah')">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <span><i class="fa-solid fa-user-plus" style="margin-right:8px; color:var(--accent);"></i> Tambah Karyawan</span>
            <span onclick="closeModal('modalTambah')" style="cursor:pointer; opacity:0.6;"><i class="fa-solid fa-xmark"></i></span>
        </div>
        <form action="{{ route('pegawai.store') }}" method="POST">
            @csrf
            <div class="modal-body modal-grid">
                <div class="full">
                    <label>Nama Lengkap</label>
                    <input name="nama" required placeholder="Contoh: Budi Santoso" autocomplete="off" value="{{ old('nama') }}">
                </div>
                <div>
                    <label>Username Login</label>
                    <input name="username" required placeholder="budi123" autocomplete="off" value="{{ old('username') }}">
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Min 6 karakter">
                </div>
                <div>
                    <label>Role / Posisi</label>
                    <select name="jabatan" required>
                        <option value="Barista">Barista</option>
                        <option value="Kasir">Kasir</option>
                    </select>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modalTambah')">Batal</button>
                <button class="btn-main">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= MODAL EDIT =================--}}
<div id="modalEdit" class="modal-backdrop" onclick="closeModal('modalEdit')">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <span><i class="fa-solid fa-user-pen" style="margin-right:8px; color:#fbbf24;"></i> Edit Karyawan</span>
            <span onclick="closeModal('modalEdit')" style="cursor:pointer; opacity:0.6;"><i class="fa-solid fa-xmark"></i></span>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body modal-grid">
                <div class="full">
                    <label>Nama Lengkap</label>
                    <input id="edit_nama" name="nama" required>
                </div>
                <div>
                    <label>Username Login</label>
                    <input id="edit_username" name="username" required>
                </div>
                <div>
                    <label>Password Baru <span style="opacity:0.5; font-weight:400;">(Opsional)</span></label>
                    <input type="password" id="edit_password" name="password" placeholder="Kosongkan jika tetap">
                </div>
                <div>
                    <label>Role / Posisi</label>
                    <select id="edit_posisi" name="jabatan" required>
                        <option value="Barista">Barista</option>
                        <option value="Kasir">Kasir</option>
                    </select>
                </div>
                <div>
                    <label>Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div id="alasanCutiWrapper" class="full" style="display:none; animation: fadeIn 0.3s;">
                    <label>Alasan Cuti</label>
                    <textarea id="edit_alasan_cuti" name="alasan_cuti" placeholder="Contoh: Acara keluarga / sakit" style="height:80px"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modalEdit')">Batal</button>
                <button class="btn-main">Update Data</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= MODAL DELETE =================--}}
<div id="modalDelete" class="modal-backdrop" onclick="closeModal('modalDelete')">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="border-bottom:none; padding-bottom:0;">
            <span style="color:#f87171;"><i class="fa-solid fa-triangle-exclamation"></i> Konfirmasi Hapus</span>
        </div>
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <div class="modal-body">
                <p style="margin-bottom:15px; font-size:15px; line-height:1.5;">
                    Apakah Anda yakin ingin menghapus data <b id="deleteNama" style="color:var(--accent);"></b>?
                    <br><span style="font-size:13px; color:var(--text-sub);">Data yang dihapus tidak dapat dikembalikan.</span>
                </p>
                <label>Alasan Penghapusan (Wajib)</label>
                <textarea name="alasan_penghapusan" placeholder="Contoh: Resign / Diberhentikan" style="height:90px" required></textarea>
            </div>
            <div class="modal-footer" style="background: rgba(248, 113, 113, 0.1);">
                <button type="button" class="btn-cancel" onclick="closeModal('modalDelete')">Batal</button>
                <button class="btn-main" style="background:#dc2626; box-shadow:none;">Hapus Permanen</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // --- 1. TOAST FUNCTION (Global Helper) ---
    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icon = type === 'success' 
            ? '<i class="fa-solid fa-circle-check" style="color:#4ade80"></i>' 
            : '<i class="fa-solid fa-circle-xmark" style="color:#f87171"></i>';
            
        toast.innerHTML = `${icon} <span>${message}</span>`;
        container.appendChild(toast);

        // Auto remove
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.4s ease forwards';
            setTimeout(() => {
                toast.remove();
                if (container.children.length === 0) container.remove();
            }, 400); 
        }, 3500);
    }

    // --- 2. MODAL CONTROLS ---
    function openModal(id){ const el = document.getElementById(id); el.style.display = "flex"; }
    function closeModal(id){ document.getElementById(id).style.display = "none"; }

    // --- 3. EDIT LOGIC (Fetch Data) ---
    function openEditModal(id){
        fetch(`/pegawai/${id}/edit`, {headers:{'Accept':'application/json'}})
        .then(res => res.json())
        .then(data => {
            document.getElementById('editForm').action = `/pegawai/${data.id}`;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_username').value = data.user ? data.user.username : '';
            document.getElementById('edit_posisi').value = data.jabatan; 
            document.getElementById('edit_status').value = data.status;

            // Handle Field Cuti
            const cutiField = document.getElementById('alasanCutiWrapper');
            const cutiInput = document.getElementById('edit_alasan_cuti');

            if(data.status === "Cuti"){
                cutiField.style.display = "block";
                cutiInput.value = data.alasan_cuti || ""; 
            } else {
                cutiField.style.display = "none";
                cutiInput.value = "";
            }
            openModal('modalEdit');
        })
        .catch(err => showToast("Gagal mengambil data pegawai", "error"));
    }

    // --- 4. DELETE LOGIC ---
    function openDeleteModal(id, nama){
        document.getElementById('deleteNama').innerText = nama;
        document.getElementById('deleteForm').action = `/pegawai/${id}`;
        openModal('modalDelete');
    }

    // --- 5. EVENT LISTENERS (DOM Ready) ---
    document.addEventListener("DOMContentLoaded", () => {
        
        // Trigger Toast from Session
        @if (session('success')) showToast("{{ session('success') }}", "success"); @endif
        @if (session('error')) showToast("{{ session('error') }}", "error"); @endif

        // Listener Status Change (Cuti)
        const statusSelect = document.getElementById("edit_status");
        const cutiWrapper = document.getElementById("alasanCutiWrapper");
        if(statusSelect){
            statusSelect.addEventListener("change", () => {
                cutiWrapper.style.display = statusSelect.value === "Cuti" ? "block" : "none";
            });
        }
    });
</script>
@endpush