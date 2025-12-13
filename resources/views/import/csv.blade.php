@extends('layouts.app')
@section('title', 'Impor Data POS')
@section('page_title', 'Unggah Data Penjualan CSV')

@section('content')

<style>
/* =========================================
   🎨 UPLOAD PAGE SPECIFIC STYLES
   ========================================= */
:root {
    --text-mid: #d1d5db; /* Abu terang */
}

/* Glass Card Container */
.upload-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    box-shadow: var(--glass-shadow);
    max-width: 580px;
    margin: 40px auto;
    padding: 40px 30px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

/* Header */
.upload-header h2 { margin: 0 0 10px 0; font-size: 24px; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
.upload-header p { margin: 0; font-size: 14px; color: var(--text-mid); }

/* Drag & Drop Area */
.upload-area {
    margin-top: 30px;
    border: 2px dashed rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 50px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.05);
    position: relative;
}

.upload-area:hover, .upload-area.dragover {
    background: rgba(247, 162, 10, 0.1);
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.upload-icon {
    font-size: 48px;
    margin-bottom: 15px;
    color: var(--text-mid);
    transition: 0.3s;
}

.upload-area:hover .upload-icon { color: var(--accent); transform: scale(1.1); }

.upload-text { font-size: 15px; font-weight: 600; color: white; }
.upload-hint { font-size: 12px; color: var(--text-mid); margin-top: 6px; }

/* File Info Box */
.file-info-box {
    margin-top: 20px;
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    padding: 15px;
    display: none; /* Default Hidden */
    align-items: center;
    gap: 15px;
    text-align: left;
    animation: fadeIn 0.3s ease;
}
.file-icon { font-size: 24px; color: #4ade80; }
.file-details div { font-weight: 700; font-size: 14px; }
.file-details span { font-size: 11px; opacity: 0.7; }

/* Buttons */
.btn-upload {
    margin-top: 30px;
    background: linear-gradient(135deg, var(--accent), #d97706);
    color: white; border: none;
    padding: 12px 30px; border-radius: 10px;
    font-weight: 700; font-size: 15px;
    cursor: pointer; width: 100%;
    transition: 0.2s;
    box-shadow: 0 4px 15px rgba(245,158,11,0.3);
    display: none; /* Default Hidden */
}
.btn-upload:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245,158,11,0.5); }
.btn-upload:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

/* Note */
.note-box {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    font-size: 12px; color: var(--text-mid); line-height: 1.6;
}
.note-box strong { color: var(--accent); }

@keyframes fadeIn { from{opacity:0; transform:translateY(10px);} to{opacity:1; transform:translateY(0);} }
</style>

<div class="upload-card">
    <div class="upload-header">
        <h2><i class="fa-solid fa-file-csv" style="color:var(--accent); margin-right:8px;"></i> Impor Data POS</h2>
        <p>Unggah laporan CSV dari aplikasi Kasir Warung</p>
    </div>

    <form id="upload-form" action="#" method="POST" enctype="multipart/form-data">
        @csrf
        
        {{-- HIDDEN INPUT --}}
        <input type="file" name="csv" id="csvInput" accept=".csv" onchange="handleFile(this.files)" style="display:none;">

        {{-- DRAG DROP AREA --}}
        <div id="uploadArea" class="upload-area" onclick="document.getElementById('csvInput').click();">
            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
            <div class="upload-text">Klik atau tarik file CSV ke sini</div>
            <div class="upload-hint">Format: <strong>.csv</strong> (Maks 5MB)</div>
        </div>

        {{-- FILE PREVIEW --}}
        <div id="fileInfo" class="file-info-box">
            <i class="fa-solid fa-file-csv file-icon"></i>
            <div class="file-details">
                <div id="fileName">laporan_penjualan.csv</div>
                <span id="fileSize">2.4 MB</span>
            </div>
            <button type="button" onclick="resetFile()" style="margin-left:auto; background:none; border:none; color:#f87171; cursor:pointer;" title="Hapus File">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>

        {{-- ACTION BUTTON --}}
        <button id="uploadBtn" class="btn-upload" onclick="uploadCsv(); return false;">
            <i class="fa-solid fa-upload"></i> Proses & Unggah
        </button>

        {{-- FOOTER NOTE --}}
        <div class="note-box">
            <i class="fa-solid fa-circle-info"></i> Pastikan format kolom CSV sesuai standar aplikasi <strong>Kasir Warung</strong>. <br>
            Data yang diimpor akan masuk ke <strong>Rekap Absensi</strong>.
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let selectedFile = null;
const dropArea = document.getElementById('uploadArea');

// --- DRAG & DROP EVENTS ---
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight() { dropArea.classList.add('dragover'); }
function unhighlight() { dropArea.classList.remove('dragover'); }

dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFile(files);
}

// --- FILE HANDLING ---
function handleFile(files){
    if (!files.length) return;
    const file = files[0];

    // Validasi Size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire({
            icon: 'error',
            title: 'File Terlalu Besar',
            text: 'Maksimal ukuran file adalah 5MB.',
            background: '#1f2937', color: '#fff', confirmButtonColor: '#f59e0b'
        });
        resetFile();
        return;
    }

    // Validasi Tipe (Basic Check)
    if (!file.name.endsWith('.csv')) {
        Swal.fire({
            icon: 'warning',
            title: 'Format Salah',
            text: 'Harap unggah file dengan format .csv',
            background: '#1f2937', color: '#fff', confirmButtonColor: '#f59e0b'
        });
        return;
    }

    selectedFile = file;
    showFileInfo(file);
}

function showFileInfo(file) {
    document.getElementById('uploadArea').style.display = 'none'; // Sembunyikan area upload
    
    const infoBox = document.getElementById('fileInfo');
    document.getElementById('fileName').innerText = file.name;
    document.getElementById('fileSize').innerText = (file.size/1024).toFixed(1) + ' KB';
    
    infoBox.style.display = 'flex'; // Tampilkan file info
    document.getElementById('uploadBtn').style.display = 'block'; // Tampilkan tombol
}

function resetFile() {
    selectedFile = null;
    document.getElementById('csvInput').value = ''; // Reset input file
    
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('uploadBtn').style.display = 'none';
    document.getElementById('uploadArea').style.display = 'block'; // Munculin lagi area upload
}

// --- UPLOAD LOGIC ---
function uploadCsv(){
    if (!selectedFile) {
        Swal.fire({ icon:'warning', title:'Belum ada file', text:'Pilih file CSV terlebih dahulu' });
        return;
    }

    const uploadBtn = document.getElementById('uploadBtn');
    const originalText = uploadBtn.innerHTML;
    
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
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;

        if (res.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data CSV berhasil diimpor dan siap diverifikasi.',
                background: '#1f2937', color: '#fff', 
                confirmButtonText: 'Lihat Rekap',
                confirmButtonColor: '#f59e0b'
            }).then(() => {
                window.location.href = "{{ route('absensi.rekap') }}";
            });
        } else {
            Swal.fire({ icon:'error', title:'Gagal', text: res.error || 'Terjadi kesalahan', background: '#1f2937', color: '#fff' });
        }
    })
    .catch(err => {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
        console.error(err);
        Swal.fire({ icon:'error', title:'Gagal', text: err.message || 'Terjadi kesalahan server', background: '#1f2937', color: '#fff' });
    });
}
</script>
@endpush