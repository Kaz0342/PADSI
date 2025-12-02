@extends('layouts.app')

@section('title', 'Kelola Shift Kerja')

@section('content')

<style>
/* Konsistensi style dengan Pegawai Index */
.header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.header-actions h2 { margin: 0; font-size: 22px; color: #1f2937; font-weight: 700; }

.card-table { background: var(--card); border-radius: var(--radius); padding: 0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; }

.shift-table { width: 100%; border-collapse: collapse; }
.shift-table th, .shift-table td { padding: 12px 24px; text-align: left; border-bottom: 1px solid #e5e7eb; }
.shift-table th { background: #f9fafb; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
.shift-table tr:last-child td { border-bottom: none; }
.shift-table tbody tr:hover { background: #f9fafb; }

.shift-name { font-weight: 600; color: #1f2937; font-size: 15px; }
.shift-time { font-family: monospace; font-weight: 600; color: #4b5563; background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 13px; display: inline-block; }

/* Action Buttons (Konsisten dengan Pegawai) */
.action-btn { padding: 6px; border-radius: 6px; border: 1px solid transparent; background: transparent; color: #6b7280; cursor: pointer; transition: all 0.2s; }
.action-btn:hover { background: #f3f4f6; color: #111827; }
.action-btn.edit { color: #d97706; }
.action-btn.edit:hover { background: #fffbeb; }
.action-btn.delete { color: #ef4444; }
.action-btn.delete:hover { background: #fef2f2; }

/* Modal Styles (reused) */
.modal-content { padding: 25px; width: 100%; max-width: 450px; background: white; border-radius: 12px; }
.modal-content h3 { margin-top: 0; margin-bottom: 20px; font-size: 18px; }
label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 14px; color: #374151; }
input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 15px; }
.modal-footer { margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px; }
</style>

<div class="header-actions">
<div>
<h2>Kelola Shift Kerja</h2>
<p class="small-muted" style="margin:5px 0 0;">Atur jam kerja dan durasi untuk jadwal karyawan.</p>
</div>
<button class="btn" onclick="openModal('tambahShiftModal')">
<i class="fa-solid fa-plus"></i> Tambah Shift
</button>
</div>

<div class="card-table">
<table class="shift-table">
<thead>
<tr>
<th width="5%">No</th>
<th width="30%">Nama Shift</th>
<th width="20%">Jam Mulai</th>
<th width="20%">Jam Selesai</th>
<th width="15%">Durasi</th>
<th width="10%" style="text-align:center;">Aksi</th>
</tr>
</thead>
<tbody>
@forelse($shifts as $shift)
<tr>
<td>{{ $loop->iteration }}</td>
<td><div class="shift-name">{{ $shift->nama }}</div></td>
<td><span class="shift-time">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</span></td>
<td><span class="shift-time">{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</span></td>
<td>
@php
$start = \Carbon\Carbon::parse($shift->start_time);
$end = \Carbon\Carbon::parse($shift->end_time);
// Hitung durasi, tambahkan hari jika waktu selesai lebih kecil (lintas hari)
if($end < $start) $end->addDay();
$diff = $start->diffInHours($end);
@endphp
{{ $diff }} Jam
</td>
<td style="text-align:center;">
<button class="action-btn edit" onclick="openEditModal({{ $shift->id }})" title="Edit">
<i class="fa-solid fa-pen-to-square"></i>
</button>
<button class="action-btn delete" onclick="openDeleteModal({{ $shift->id }}, '{{ $shift->nama }}')" title="Hapus">
<i class="fa-solid fa-trash-can"></i>
</button>
</td>
</tr>
@empty
<tr>
<td colspan="6" style="text-align:center; padding: 30px; color: #9ca3af;">
<i class="fa-solid fa-clock-rotate-left" style="font-size: 24px; margin-bottom: 10px; display:block;"></i>
Belum ada data shift. Silakan tambah shift baru.
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

{{-- Modal Tambah Shift --}}
<div id="tambahShiftModal" class="modal-backdrop" onclick="closeModal('tambahShiftModal')">
    <div class="modal-content" onclick="event.stopPropagation()">
        <h3>Tambah Shift Baru</h3>
        <form action="{{ route('shifts.store') }}" method="POST">
            @csrf
            <label>Nama Shift</label>
            <input type="text" name="nama" placeholder="Contoh: Pagi, Sore, Malam" required>

            <div style="display:flex; gap:15px;">
                <div style="flex:1;">
                    <label>Jam Mulai</label>
                    <input type="time" name="start_time" required>
                </div>
                <div style="flex:1;">
                    <label>Jam Selesai</label>
                    <input type="time" name="end_time" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn secondary" onclick="closeModal('tambahShiftModal')">Batal</button>
                <button type="submit" class="btn">Simpan</button>
            </div>
        </form>
    </div>
</div>


{{-- Modal Edit Shift --}}
<div id="editShiftModal" class="modal-backdrop" onclick="closeModal('editShiftModal')">
    <div class="modal-content" onclick="event.stopPropagation()">
        <h3>Edit Shift</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <label>Nama Shift</label>
            <input type="text" id="edit_nama" name="nama" required>

            <div style="display:flex; gap:15px;">
                <div style="flex:1;">
                    <label>Jam Mulai</label>
                    <input type="time" id="edit_start" name="start_time" required>
                </div>
                <div style="flex:1;">
                    <label>Jam Selesai</label>
                    <input type="time" id="edit_end" name="end_time" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn secondary" onclick="closeModal('editShiftModal')">Batal</button>
                <button type="submit" class="btn">Update</button>
            </div>
        </form>
    </div>
</div>


{{-- Modal Hapus Shift --}}
<div id="deleteShiftModal" class="modal-backdrop" onclick="closeModal('deleteShiftModal')">
    <div class="modal-content" onclick="event.stopPropagation()">
        <h3>Hapus Shift</h3>
        <p style="color:#6b7280; margin-bottom:20px;">
            Apakah Anda yakin ingin menghapus shift <strong id="deleteShiftName"></strong>?
            Tindakan ini tidak dapat dibatalkan dan shift ini tidak boleh terpakai di jadwal.
        </p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-footer">
                <button type="button" class="btn secondary" onclick="closeModal('deleteShiftModal')">Batal</button>
                <button type="submit" class="btn" style="background:#ef4444; color:white;">Hapus</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')

<script>
function openEditModal(id) {
    // Fetch data via AJAX
    fetch(`/shifts/${id}/edit`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Failed to fetch data');
        }
        return res.json();
    })
    .then(data => {
        document.getElementById('editForm').action = `/shifts/${id}`;
        document.getElementById('edit_nama').value = data.nama;
        
        // Format time H:i (ambil 5 karakter pertama) karena input type=time hanya butuh H:i
        document.getElementById('edit_start').value = data.start_time.substring(0, 5);
        document.getElementById('edit_end').value = data.end_time.substring(0, 5);

        openModal('editShiftModal');
    })
    .catch(err => {
        // Asumsi showToast ada di layouts/app.blade.php
        if (typeof showToast !== 'undefined') {
            showToast('Gagal memuat data shift.', 'error');
        } else {
            console.error('Error loading shift data:', err);
            alert('Gagal memuat data shift.');
        }
    });
}

function openDeleteModal(id, name) {
    document.getElementById('deleteForm').action = `/shifts/${id}`;
    document.getElementById('deleteShiftName').innerText = name;
    openModal('deleteShiftModal');
}
</script>

@endpush