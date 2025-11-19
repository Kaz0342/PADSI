@extends('layout')

@section('content')
<div class="page-header">
    <h2>DATA PEGAWAI</h2>
    <a href="{{ route('pegawai.create') }}" class="btn-add">+</a>
</div>

{{-- 🔔 ALERT SUKSES --}}
@if(session('success'))
    <div class="alert-success" id="successAlert">
        {{ session('success') }}
    </div>
@endif

<style>
    .alert-success {
        background-color: #2ecc71;
        color: black;
        font-weight: bold;
        text-align: center;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Tombol edit dan delete */
    .aksi-button {
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }

    .btn-edit i {
        color: #f39c12;
        font-size: 18px;
        transition: all 0.2s ease;
    }

    .btn-delete i {
        color: #e74c3c;
        font-size: 18px;
        transition: all 0.2s ease;
    }

    .btn-edit i:hover {
        color: #ffb347;
        transform: scale(1.2);
    }

    .btn-delete i:hover {
        color: #ff6b6b;
        transform: scale(1.2);
    }

    .btn-delete {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }

    .btn-edit {
        text-decoration: none;
    }

    /* Modal konfirmasi hapus */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.6);
        justify-content: center;
        align-items: center;
        z-index: 999;
    }

    .modal-content {
        background-color: #1b1b1b;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        color: white;
        width: 300px;
        animation: fadeIn 0.3s ease;
    }

    .modal-buttons {
        display: flex;
        justify-content: center;
        margin-top: 15px;
    }

    .modal-buttons button {
        margin: 0 10px;
        padding: 6px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-yes {
        background-color: #2ecc71;
        color: black;
    }

    .btn-no {
        background-color: #ccc;
        color: black;
    }
</style>

<table class="tabel-pegawai">
    <thead>
        <tr>
            <th>ID PEGAWAI</th>
            <th>NAMA</th>
            <th>ROLE</th>
            <th>STATUS</th>
            <th>AKSI</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pegawai as $p)
        <tr>
            <td>{{ $p->id }}</td>
            <td>{{ $p->nama }}</td>
            <td>{{ $p->role }}</td>
            <td>{{ $p->status }}</td>
            <td>
                <div class="aksi-button">
                    <a href="{{ route('pegawai.edit', $p->id) }}" class="btn-edit" title="Edit Pegawai">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn-delete" data-id="{{ $p->id }}" data-nama="{{ $p->nama }}" title="Hapus Pegawai">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- 🔘 Modal Konfirmasi Hapus (1 modal global) -->
<div class="modal" id="confirmDeleteModal">
    <div class="modal-content">
        <p id="deleteMessage">Yakin ingin menghapus data ini?</p>
        <div class="modal-buttons">
            <button class="btn-no" id="btnCancelDelete">Batal</button>
            <form id="deleteForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-yes">Ya</button>
            </form>
        </div>
    </div>
</div>

<script>
    // 🔔 Hilangkan alert sukses otomatis
    setTimeout(() => {
        const alertBox = document.getElementById('successAlert');
        if (alertBox) alertBox.style.display = 'none';
    }, 3000);

    // 🗑️ Logika Konfirmasi Hapus
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const modal = document.getElementById('confirmDeleteModal');
    const cancelButton = document.getElementById('btnCancelDelete');
    const deleteForm = document.getElementById('deleteForm');
    const deleteMessage = document.getElementById('deleteMessage');

    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const pegawaiId = button.getAttribute('data-id');
            const namaPegawai = button.getAttribute('data-nama');

            deleteMessage.innerText = `Yakin ingin menghapus data ${namaPegawai}?`;
            deleteForm.action = `/pegawai/${pegawaiId}`;

            modal.style.display = 'flex';
        });
    });

    cancelButton.addEventListener('click', () => {
        modal.style.display = 'none';
    });
</script>
@endsection
