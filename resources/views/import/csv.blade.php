@extends('layouts.app')
@section('title', 'Impor CSV POS')
@section('page_title', 'Unggah Data Penjualan CSV')

@section('content')
<style>
/* (style sama seperti sebelumnya) */
:root { --accent: #f59e0b; --accent-dark: #e68b00; --radius: 14px; --shadow: 0 6px 22px rgba(0,0,0,.06); --border-color: #d6d6d6; --bg-light: #fafafa; }
.upload-box { background:#fff;padding:32px;border-radius:var(--radius);box-shadow:var(--shadow);max-width:520px;margin:40px auto;text-align:center;border:1px solid #eee; }
.upload-area { padding:40px;border:2px dashed var(--border-color);border-radius:var(--radius);cursor:pointer;transition:.2s;background:var(--bg-light); }
.upload-area:hover { border-color:var(--accent);background:#fffdf6; }
#csvInput { display:none; }
.upload-icon { font-size:40px;color:var(--accent); }
.btn-upload { margin-top:25px;background:var(--accent);color:white;border:none;padding:10px 18px;border-radius:8px;font-weight:700;transition:background .2s; }
.btn-upload:hover { background:var(--accent-dark); }
.file-info { margin-top:18px;font-size:14px;color:#4b5563;min-height:20px; }
.note { margin-top:15px;font-size:12px;color:#6b7280;line-height:1.5;border-top:1px dashed #eee;padding-top:10px; }
</style>

<form id="upload-form" action="#" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="upload-box">
        <h3 style="margin-top:0;">Impor File CSV</h3>

        <div id="uploadArea" class="upload-area" onclick="document.getElementById('csvInput').click();">
            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
            <p style="margin-top:10px;">Klik / drag file CSV Anda di sini</p>

            <input type="file" name="csv" id="csvInput" accept=".csv" onchange="handleFile(this.files)">
        </div>

        <div id="fileInfo" class="file-info" style="display:none;"></div>

        <button id="uploadBtn" class="btn-upload" style="display:none;" onclick="uploadCsv(); return false;">
            <i class="fa-solid fa-upload"></i> Unggah CSV
        </button>

        <div class="note">
            Format: <strong>.csv</strong> • Maks: <strong>5MB</strong>. Pastikan data berformat Kasir Warung.
        </div>
    </div>
</form>
@endsection

@push('scripts')
<!-- sweetalert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let selectedFile = null;

function handleFile(files){
    if (!files.length) return;
    selectedFile = files[0];

    if (selectedFile.size > 5 * 1024 * 1024) {
        Swal.fire({ icon:'error', title:'File terlalu besar', text:'Maksimal 5MB' });
        selectedFile = null;
        document.getElementById('fileInfo').style.display = 'none';
        document.getElementById('uploadBtn').style.display = 'none';
        return;
    }

    document.getElementById('fileInfo').style.display = 'block';
    document.getElementById('fileInfo').innerHTML = `<strong>File dipilih:</strong><br>${selectedFile.name} (${(selectedFile.size/1024).toFixed(1)} KB)`;
    document.getElementById('uploadBtn').style.display = 'inline-block';
}

function uploadCsv(){
    if (!selectedFile) {
        Swal.fire({ icon:'warning', title:'Belum ada file', text:'Pilih file CSV terlebih dahulu' });
        return;
    }

    const uploadBtn = document.getElementById('uploadBtn');
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengunggah...';

    let formData = new FormData();
    formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);
    formData.append("csv", selectedFile);

    fetch("{{ route('api.pos.import') }}", {
        method: "POST",
        body: formData,
    })
    .then(async r => {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Unggah CSV';

        const isJson = r.headers.get('content-type')?.includes('application/json');
        let payload = {};
        if (isJson) payload = await r.json();

        if (!r.ok) {
            const msg = payload.error || payload.message || r.statusText;
            throw new Error(msg);
        }
        return payload;
    })
    .then(res => {
        if (res.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil mengunggah CSV!',
                text: 'Data siap diverifikasi.',
                confirmButtonText: 'Lihat Rekap',
                confirmButtonColor: '#f59e0b'
            }).then(() => {
                window.location.href = "{{ route('absensi.rekap') }}";
            });
        } else {
            Swal.fire({ icon:'error', title:'Gagal', text: res.error || 'Terjadi kesalahan' });
        }
    })
    .catch(err => {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Unggah CSV';
        console.error(err);
        Swal.fire({ icon:'error', title:'Gagal', text: err.message || 'Terjadi kesalahan server' });
    });
}
</script>
@endpush
