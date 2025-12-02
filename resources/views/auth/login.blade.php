<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Absensi Matari | Login</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* --- FONT & RESET --- */
    * {
        font-family: "Inter", sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* --- LAYOUT & BACKGROUND --- */
    body {
        background: #fff3d4; /* Warna lo yang baru */
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    
    /* --- CARD CONTAINER --- */
    .card {
        width: 380px; 
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.10);
    }

    /* --- CARD HEADER (Warna Matari) --- */
    .header {
        background: #f7a20a;
        padding: 35px 25px 45px;
        text-align: center;
        position: relative;
        color: white;
    }
    .header h2 {
        margin-top: 8px;
        font-size: 22px;
        font-weight: 700;
    }
    .header .subtitle {
        font-size: 13px;
        opacity: 0.9;
        margin-top: 5px;
    }

    /* --- WIFI STATUS BADGE (Top Right) --- */
    .wifi-status {
        position: absolute;
        right: 15px;
        top: 15px;
        background: rgba(255, 255, 255, 0.85);
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
    }
    .wifi-ok { color: #0a8f36; }
    .wifi-bad { color: #d62222; }

    /* --- ICON --- */
    .cup-icon {
        font-size: 48px;
        margin-bottom: 5px;
    }

    /* --- CARD BODY (Form Content) --- */
    .inner {
        padding: 30px;
    }

    /* --- FORM COMPONENTS --- */
    .input-group {
        position: relative;
        margin-bottom: 22px;
    }
    label {
        font-size: 13px;
        color: #444;
        font-weight: 600;
        display: block; 
        margin-bottom: 6px;
    }

    /* Input Wrapper & Input Field */
    .input-wrap {
        position: relative;
        width: 100%;
    }
    .input-wrap input {
        width: 100%;
        padding: 12px 40px; 
        height: 48px;
        border-radius: 10px;
        border: 1px solid #ddd;
        font-size: 15px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .input-wrap input:focus {
        outline: none;
        border-color: #f7a20a;
        box-shadow: 0 0 0 1px #f7a20a;
    }

    /* Icon Kiri (Username/Lock) */
    .icon-left {
        position: absolute;
        left: 14px; 
        top: 50%;
        transform: translateY(-50%);
        font-size: 17px;
        color: #8a8a8a;
        opacity: 0.7;
        z-index: 2; 
    }

    /* Icon Kanan (Toggle Password) */
    .icon-right {
        position: absolute;
        right: 14px; 
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
        color: #777;
        opacity: 0.9;
        cursor: pointer;
        z-index: 2; 
    }

    /* --- BUTTON LOGIN --- */
    .btn-login {
        width: 100%;
        padding: 14px;
        margin-top: 20px;
        border: none;
        border-radius: 12px;
        background: #f7a20a;
        color: #fff;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        transition: background 0.2s;
    }
    .btn-login:hover {
        background: #e28e00;
    }

    /* --- FOOTER INFO --- */
    .footer {
        text-align: center;
        margin-top: 25px;
        font-size: 12px;
        color: #777;
    }
    .info-status {
        font-size: 12px;
        color: #777;
        text-align: center;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #eee;
    }
    
    /* --- ALERT MESSAGE (Error Blade) --- */
    .alert {
        background: #ffe0e0;
        border-left: 4px solid #d32f2f;
        padding: 10px;
        color: #b00020;
        border-radius: 6px;
        margin-bottom: 15px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
</head>

<body>
<div class="card">
    
    <!-- HEADER -->
    <div class="header">
        <div id="wifiStatusBadge" class="wifi-status">
             <span id="wifiEmoji"></span>
             <span id="wifiText">Checking...</span>
        </div>

        <div class="cup-icon">☕</div>
        <h2>Sistem Absensi Matari</h2>
        <p class="subtitle">Silakan login untuk melanjutkan</p>
    </div>

    <!-- BODY -->
    <div class="inner">

        <!-- Logika Error Blade Laravel -->
        @if(session('error'))
          <div class="alert">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
          </div>
        @endif
        
        <form id="login-form" action="{{ route('login.process') }}" method="POST">
        @csrf

            <!-- Field Username -->
            <div class="input-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <span class="icon-left"><i class="fa-solid fa-user"></i></span>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required>
                </div>
            </div>

            <!-- Field Password -->
            <div class="input-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="icon-left"><i class="fa-solid fa-lock"></i></span>
                    <input id="password" type="password" name="password" placeholder="Masukkan password" required>
                    <span id="togglePass" class="icon-right" title="Tampilkan/Sembunyikan Password"><i class="fa-solid fa-eye"></i></span>
                </div>
            </div>

            <!-- Hidden fields buat logic Absensi: GPS & WiFi (Dikirim ke Backend) -->
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lng" id="lng">
            <input type="hidden" name="wifi_connected" id="wifi_connected" value="0">

            <button id="submitBtn" type="submit" class="btn-login">→ Login ke Sistem</button>
        </form>

        <!-- Info status Geo di bawah form -->
        <div class="info-status">Status Lokasi (GPS): <span id="geoText">—</span></div>

        <div class="footer">
            © Kedai Matari — Sistem Absensi
        </div>
    </div>
</div>

<!-- --- LOGIC JAVASCRIPT ABSENSI GILA --- -->
<script>
// Toggle Password
document.getElementById('togglePass').addEventListener('click', function(){
    const p = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (p.type === 'password') {
        p.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        p.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});


// ⚙️ CONFIG UTAMA ABSENSI (ASUMSI DARI ENV LARAVEL)
// Karena ini front-end, kita harus hardcode atau ambil dari data blade. Kita hardcode dulu.
const WIFI_PROBE_URL = 'http://192.168.0.1/'; 
const WIFI_TIMEOUT_MS = 1500; // Toleransi waktu 1.5 detik
const GEO_TIMEOUT_MS = 3000;  // Toleransi waktu 3 detik

/**
 * Cek Koneksi ke IP Lokal (Simulasi)
 */
async function probeWifi(){
    try {
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), WIFI_TIMEOUT_MS);
        
        await fetch(WIFI_PROBE_URL, {mode:'no-cors', signal: controller.signal});
        clearTimeout(id);
        
        return true; 
    } catch(e){
        return false;
    }
}

/**
 * Ambil Koordinat GPS User dengan Promise dan Timeout
 */
function getGeoPosition(timeout){
    return new Promise((resolve) => {
        if (!navigator.geolocation) return resolve(null);
        let resolved = false;
        
        const timer = setTimeout(() => { 
            if(!resolved){ resolved = true; resolve(null); } 
        }, timeout);
        
        navigator.geolocation.getCurrentPosition(pos => {
            if (resolved) return;
            resolved = true; clearTimeout(timer);
            resolve({lat: pos.coords.latitude, lng: pos.coords.longitude});
        }, err=>{
            if (resolved) return;
            resolved = true; clearTimeout(timer);
            resolve(null);
            console.error("Geolocation Error:", err.code, err.message);
        }, {enableHighAccuracy:true, maximumAge:10000, timeout:timeout});
    });
}

/**
 * Inisialisasi: Cek WiFi dan GPS di background saat page load
 */
(async function initChecks(){
    const wifiBadge = document.getElementById('wifiStatusBadge');
    const wifiText = document.getElementById('wifiText');
    const wifiEmoji = document.getElementById('wifiEmoji');
    const geoText = document.getElementById('geoText');
    
    // Set status awal
    wifiBadge.classList.remove('green', 'red');
    wifiEmoji.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    wifiText.innerText = 'Mendeteksi...';
    geoText.innerText = 'Mencari...';

    // Jalanin 2 proses bebarengan (Paralel)
    const [wifi, geo] = await Promise.allSettled([probeWifi(), getGeoPosition(GEO_TIMEOUT_MS)]);
    
    // 1. Hasil Cek WiFi
    const wifiOk = wifi.status === 'fulfilled' && wifi.value === true;
    document.getElementById('wifi_connected').value = wifiOk ? 1 : 0; 

    if (wifiOk) {
        wifiBadge.classList.add('green');
        wifiEmoji.innerHTML = '<i class="fa-solid fa-wifi"></i>';
        wifiText.innerText = 'Matari WiFi ON';
    } else {
        wifiBadge.classList.add('red');
        wifiEmoji.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        wifiText.innerText = 'WiFi Not Found';
    }

    // 2. Hasil Cek GPS
    const geoVal = (geo.status === 'fulfilled' && geo.value) ? geo.value : null;

    if (geoVal) {
        document.getElementById('lat').value = geoVal.lat;
        document.getElementById('lng').value = geoVal.lng;
        geoText.innerText = `OK (${geoVal.lat.toFixed(6)}, ${geoVal.lng.toFixed(6)})`;
    } else {
        geoText.innerText = 'Gagal atau Izin Ditolak';
    }
})();
</script>
</body>
</html>