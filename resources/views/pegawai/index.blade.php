@extends('layouts.app')
@section('title', 'Kelola Karyawan')
@section('content')
<style>
/* Header & Actions */
.header-actions {
display: flex;
justify-content: space-between;
align-items: center;
margin-bottom: 25px;
}
.header-actions h2 { margin: 0; font-size: 22px; color: #1f2937; font-weight: 700; }

/* Table Styles */
.card-table {
    background: var(--card);
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    border: 1px solid #e5e7eb;
}
.table-responsive { overflow-x: auto; }
.pegawai-table { width: 100%; border-collapse: collapse; }
.pegawai-table th {
    background: #f9fafb;
    padding: 12px 24px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e5e7eb;
}
.pegawai-table td {
    padding: 16px 24px;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
    font-size: 14px;
    vertical-align: middle;
}
.pegawai-table tr:last-child td { border-bottom: none; }
.pegawai-table tbody tr:hover { background-color: #f9fafb; }

/* Pegawai Info Cell */
.pegawai-info { display: flex; align-items: center; gap: 12px; }
.pegawai-avatar {
    width: 40px; height: 40px;
    background: #e0f2fe; color: #0369a1;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 16px;
}
.pegawai-details div:first-child { font-weight: 600; color: #111827; }
.pegawai-username { font-size: 12px; color: #6b7280; }

/* Badges */
.badge { padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.status-aktif { background: #dcfce7; color: #166534; }
.status-cuti { background: #fef9c3; color: #854d0e; }
.status-nonaktif { background: #fee2e2; color: #991b1b; }
.badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

/* Action Buttons */
.action-btn {
    padding: 6px; border-radius: 6px; border: 1px solid transparent;
    background: transparent; color: #6b7280; cursor: pointer; transition: all 0.2s;
}
.action-btn:hover { background: #f3f4f6; color: #111827; }
.action-btn.edit { color: #d97706; }
.action-btn.edit:hover { background: #fffbeb; }
.action-btn.delete { color: #ef4444; }
.action-btn.delete:hover { background: #fef2f2; }

/* Modal Styles */
.modal-content {
    background: white; padding: 0; border-radius: 12px;
    width: 100%; max-width: 500px; position: relative;
}
.modal-header {
    padding: 20px 24px; border-bottom: 1px solid #e5e7eb;
    display: flex; justify-content: space-between; align-items: center;
}
.modal-header h3 { margin: 0; font-size: 18px; font-weight: 600; }
.modal-body { padding: 24px; }
.modal-footer {
    padding: 16px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb;
    display: flex; justify-content: flex-end; gap: 12px; border-radius: 0 0 12px 12px;
}

label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 500; color: #374151; }
input, select, textarea {
    width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
    font-size: 14px; margin-bottom: 16px; transition: border-color 0.15s;
}
input:focus, select:focus, textarea:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 0 3px rgba(247, 162, 10, 0.1); }

/* Status Toggle */
.status-options { display: flex; gap: 8px; margin-bottom: 16px; background: #f3f4f6; padding: 4px; border-radius: 8px; }
.status-btn {
    flex: 1; padding: 8px; border: none; background: transparent;
    font-size: 13px; font-weight: 500; color: #6b7280; border-radius: 6px; cursor: pointer;
}
.status-btn.active { background: white; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,0.05); font-weight: 600; }
</style>
{{-- Header --}}
<div class="header-actions">
<div>
<h2>Kelola Karyawan</h2>
<p class="small-muted" style="margin-top:4px;">Manajemen data pegawai, akun, dan status kerja.</p>
</div>
<button class="btn" onclick="openModal('tambahKaryawanModal')">
<i class="fa-solid fa-plus"></i> Tambah Karyawan
</button>
</div>
{{-- Summary Chips (Optional, simplified) --}}
<div style="display:flex; gap:15px; margin-bottom:20px;">
<span class="badge status-aktif" style="font-size:13px;">Aktif: {{ $aktifCount }}</span>
<span class="badge status-cuti" style="font-size:13px;">Cuti: {{ $cutiCount }}</span>
<span class="badge status-nonaktif" style="font-size:13px;">Nonaktif: {{ $nonaktifCount }}</span>
</div>
{{-- Table --}}
<div class="card-table">
<div class="table-responsive">
<table class="pegawai-table">
<thead>
<tr>
<th width="5%">ID</th>
<th width="30%">Nama & Username</th>
<th width="15%">Role</th>
<th width="15%">Status</th>
<th width="20%">Keterangan Cuti</th>
<th width="15%" style="text-align:right;">Aksi</th>
</tr>
</thead>
<tbody>
@forelse($pegawais as $pegawai)
<tr>
<td>#{{ $loop->iteration }}</td>
<td>
<div class="pegawai-info">
<div class="pegawai-avatar">{{ substr($pegawai->nama, 0, 1) }}</div>
<div class="pegawai-details">
<div>{{ $pegawai->nama }}</div>
<div class="pegawai-username">@ {{ $pegawai->user->username ?? '-' }}</div>
</div>
</div>
</td>
<td>{{ ucfirst($pegawai->jabatan) }}</td>
<td>
<span class="badge status-{{ strtolower($pegawai->status) }}">
{{ $pegawai->status }}
</span>
</td>
<td style="color:{{ $pegawai->status === 'Cuti' ? '#d97706' : '#9ca3af' }}; font-size:13px;">
{{ $pegawai->alasan_cuti }}
</td>
<td style="text-align:right;">
<button class="action-btn edit" onclick="openEditModal({{ $pegawai->id }})" title="Edit Data">
<i class="fa-solid fa-pen-to-square"></i>
</button>
<button class="action-btn delete" onclick="openDeleteModal({{ $pegawai->id }}, '{{ $pegawai->nama }}')" title="Hapus Pegawai">
<i class="fa-solid fa-trash-can"></i>
</button>
</td>
</tr>
@empty
<tr>
<td colspan="6" style="text-align:center; padding:40px; color:#9ca3af;">
<i class="fa-solid fa-users-slash" style="font-size:24px; margin-bottom:10px;"></i>

Belum ada data pegawai.
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>
{{-- MODAL 1: TAMBAH KARYAWAN --}}
<div id="tambahKaryawanModal" class="modal-backdrop" onclick="closeModal('tambahKaryawanModal')">
<div class="modal-content" onclick="event.stopPropagation()">
<div class="modal-header">
<h3>Tambah Karyawan</h3>
<span class="close-btn" onclick="closeModal('tambahKaryawanModal')" style="cursor:pointer;">&times;</span>
</div>
<form action="{{ route('pegawai.store') }}" method="POST">
<div class="modal-body">
@csrf
<label>Nama Lengkap</label>
<input type="text" name="nama_lengkap" placeholder="Contoh: Budi Santoso" required>

            <div class="form-row">
                <div class="col">
                    <label>Username (Login)</label>
                    <input type="text" name="username" placeholder="budi123" required>
                </div>
                <div class="col">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter" required>
                </div>
            </div>

            <label>Role / Posisi</label>
            <select name="role_posisi" required>
                <option value="">-- Pilih Posisi --</option>
                <option value="barista">Barista</option>
                <option value="kasir">Kasir</option>
                <option value="waiter">Waiter</option>
                <option value="chef">Chef</option>
            </select>
            
            {{-- Status Hidden (Default Aktif) --}}
            <input type="hidden" name="status" value="Aktif">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn secondary" onclick="closeModal('tambahKaryawanModal')">Batal</button>
            <button type="submit" class="btn">Simpan Karyawan</button>
        </div>
    </form>
</div>
</div>
{{-- MODAL 2: EDIT KARYAWAN --}}
<div id="editKaryawanModal" class="modal-backdrop" onclick="closeModal('editKaryawanModal')">
<div class="modal-content" onclick="event.stopPropagation()">
<div class="modal-header">
<h3>Edit Data Karyawan</h3>
<span class="close-btn" onclick="closeModal('editKaryawanModal')" style="cursor:pointer;">&times;</span>
</div>
<form id="editForm" method="POST">
<div class="modal-body">
@csrf
@method('PUT')

            <label>Nama Lengkap</label>
            <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required>

            <div class="form-row">
                <div class="col">
                    <label>Username</label>
                    <input type="text" id="edit_username" name="username" required>
                </div>
                <div class="col">
                    <label>Password Baru (Opsional)</label>
                    <input type="password" name="password_baru" placeholder="Isi jika ingin ubah">
                </div>
            </div>

            <label>Role / Posisi</label>
            <select id="edit_role_posisi" name="role_posisi" required>
                <option value="barista">Barista</option>
                <option value="kasir">Kasir</option>
                <option value="manager">Manager</option>
                <option value="owner">Owner</option>
                <option value="waiter">Waiter</option>
                <option value="chef">Chef</option>
            </select>

            <label>Status Karyawan</label>
            <div class="status-options">
                <button type="button" class="status-btn" data-status="Aktif">Aktif</button>
                <button type="button" class="status-btn" data-status="Cuti">Cuti</button>
                <button type="button" class="status-btn" data-status="Nonaktif">Nonaktif</button>
                <input type="hidden" id="edit_status" name="status">
            </div>

            <div id="alasanCutiWrapper" style="display:none;">
                <label style="color:#d97706;">Keterangan Cuti</label>
                <textarea id="edit_alasan_cuti" name="alasan_cuti" rows="2" placeholder="Contoh: Sakit, Izin Keluarga..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn secondary" onclick="closeModal('editKaryawanModal')">Batal</button>
            <button type="submit" class="btn">Simpan Perubahan</button>
        </div>
    </form>
</div>
</div>
{{-- MODAL 3: HAPUS KARYAWAN --}}
<div id="deleteKaryawanModal" class="modal-backdrop" onclick="closeModal('deleteKaryawanModal')">
<div class="modal-content" onclick="event.stopPropagation()">
<div class="modal-header" style="border-bottom:none; padding-bottom:0;">
<h3 style="color:#ef4444;">Hapus Karyawan?</h3>
</div>
<form id="deleteForm" method="POST">
<div class="modal-body">
@csrf
@method('DELETE')
<p style="margin-bottom:15px; color:#4b5563;">
Anda akan menghapus <strong id="deletePegawaiNama"></strong> secara permanen. Data absensi dan login juga akan hilang.
</p>

            <label>Alasan Penghapusan <span style="color:red">*</span></label>
            <textarea name="alasan_penghapusan" rows="2" placeholder="Contoh: Resign, Diberhentikan..." required></textarea>
        </div>
        <div class="modal-footer" style="background:#fef2f2; border-top-color:#fee2e2;">
            <button type="button" class="btn secondary" onclick="closeModal('deleteKaryawanModal')">Batal</button>
            <button type="submit" class="btn" style="background:#ef4444; color:white;">Ya, Hapus Permanen</button>
        </div>
    </form>
</div>
</div>
@endsection
@push('scripts')
<script>
// Fungsi untuk membuka modal Edit dan mengambil data dari Controller (via JSON)
function openEditModal(pegawaiId) {
// Buka modal dulu biar kerasa cepet
openModal('editKaryawanModal');

// Fetch data
fetch(`/pegawai/${pegawaiId}/edit`, { headers: { 'Accept': 'application/json' } })
.then(res => {
    // FIX: Tambahkan error handling di fetch
    if (!res.ok) {
        throw new Error('Gagal mengambil data pegawai. Status: ' + res.status);
    }
    return res.json();
})
.then(data => {
    document.getElementById('editForm').action = `/pegawai/${data.id}`;
    document.getElementById('edit_nama_lengkap').value = data.nama;
    document.getElementById('edit_username').value = data.user.username;
    
    // Lo di controller pakai 'jabatan', di view ini juga pakai 'jabatan'
    document.getElementById('edit_role_posisi').value = data.jabatan; 

    // Update status toggle
    updateStatusUI(data.status);
    
    // Update alasan cuti
    const alasanInput = document.getElementById('edit_alasan_cuti');
    if(data.status === 'Cuti' && data.active_cuti) {
        alasanInput.value = data.active_cuti.keterangan;
    } else {
        alasanInput.value = '';
    }
})
.catch(err => {
    closeModal('editKaryawanModal');
    // FIX: Gunakan showToast untuk notifikasi yang konsisten
    if (typeof showToast !== 'undefined') {
        showToast('Gagal memuat data pegawai: ' + err.message, 'error');
    } else {
        alert('Gagal memuat data pegawai: ' + err.message);
    }
});
}

// Logic Status Toggle
function updateStatusUI(status) {
    document.querySelectorAll('#editKaryawanModal .status-btn').forEach(btn => {
        if(btn.dataset.status === status) btn.classList.add('active');
        else btn.classList.remove('active');
    });
    document.getElementById('edit_status').value = status;

    const wrapper = document.getElementById('alasanCutiWrapper');
    wrapper.style.display = (status === 'Cuti') ? 'block' : 'none';
}

// Event Listener untuk Status Button
document.querySelectorAll('#editKaryawanModal .status-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        updateStatusUI(this.dataset.status);
    });
});

// Fungsi untuk membuka modal Hapus
function openDeleteModal(id, nama) {
    document.getElementById('deleteForm').action = `/pegawai/${id}`;
    document.getElementById('deletePegawaiNama').innerText = nama;
    openModal('deleteKaryawanModal');
}
</script>
@endpush