@extends('layout')

@section('content')
<style>
    .form-container {
        background-color: #a5a290;
        padding: 25px 30px;
        border-radius: 8px;
        width: 420px;
        margin: 40px auto;
        color: #1b1b1b;
        font-family: "Poppins", sans-serif;
    }

    h3 {
        margin-top: 0;
        color: #1b2c25;
    }

    .form-group {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    label {
        width: 120px;
        font-weight: bold;
        color: #1b2c25;
    }

    input, select {
        flex: 1;
        padding: 8px;
        border: 1px solid #888;
        border-radius: 5px;
        background-color: #f0f0f0;
        font-family: "Poppins", sans-serif;
    }

    input:focus, select:focus {
        outline: 2px solid #2e473b;
    }

    .btn-group {
        text-align: right;
        margin-top: 15px;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-cancel {
        background-color: #ccc;
        color: black;
        margin-right: 10px;
    }

    .btn-save {
        background-color: #2ecc71;
        color: black;
    }

    .btn-save:hover {
        background-color: #27ae60;
        color: white;
    }

    /* Error Message */
    .error-box {
        background-color: red;
        color: white;
        text-align: center;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
        margin-top: 15px;
        display: none;
    }

    /* Modal Confirmation */
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
    }

    .modal-content {
        background-color: #1b1b1b;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        color: white;
        width: 300px;
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

<div class="form-container">
    <h3>Tambah Data Pegawai</h3>

    <form id="pegawaiForm" method="POST" action="{{ route('pegawai.store') }}">
        @csrf

        <!-- ID Pegawai otomatis, tidak bisa diubah -->
        <div class="form-group">
            <label>ID Pegawai :</label>
            <input type="text" placeholder="Otomatis dari sistem" disabled>
        </div>

        <div class="form-group">
            <label>Nama Pegawai :</label>
            <input type="text" name="nama" placeholder="Masukkan nama pegawai">
        </div>

        <div class="form-group">
            <label>Role :</label>
            <select name="role">
                <option value="">-- Pilih Role --</option>
                <option value="Barista">Barista</option>
                <option value="Kasir">Kasir</option>
                <option value="Manager">Manager</option>
            </select>
        </div>

        <div class="form-group">
            <label>Status :</label>
            <select name="status">
                <option value="">-- Pilih Status --</option>
                <option value="Aktif">Aktif</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>
        </div>

        <div class="btn-group">
            <button type="button" class="btn btn-cancel" onclick="window.history.back()">Batal</button>
            <button type="button" class="btn btn-save" id="btnSimpan">Simpan</button>
        </div>

        <div class="error-box" id="errorBox">Data belum lengkap</div>
    </form>
</div>

<!-- Modal Konfirmasi -->
<div class="modal" id="confirmModal">
    <div class="modal-content">
        <p>Yakin untuk menambah data?</p>
        <div class="modal-buttons">
            <button class="btn-no" id="btnCancelModal">Batal</button>
            <button class="btn-yes" id="btnYesModal">Ya</button>
        </div>
    </div>
</div>

<script>
    const btnSimpan = document.getElementById('btnSimpan');
    const form = document.getElementById('pegawaiForm');
    const errorBox = document.getElementById('errorBox');
    const modal = document.getElementById('confirmModal');
    const btnCancelModal = document.getElementById('btnCancelModal');
    const btnYesModal = document.getElementById('btnYesModal');

    btnSimpan.addEventListener('click', () => {
        const nama = form.nama.value.trim();
        const role = form.role.value.trim();
        const status = form.status.value.trim();

        if (!nama || !role || !status) {
            errorBox.style.display = 'block';
            setTimeout(() => {
                errorBox.style.display = 'none';
            }, 2000);
        } else {
            modal.style.display = 'flex';
        }
    });

    btnCancelModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    btnYesModal.addEventListener('click', () => {
        modal.style.display = 'none';
        form.submit();
    });
</script>
@endsection
