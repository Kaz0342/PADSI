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
/* ===============================
   RESET & BASE
   =============================== */
* {
    font-family: "Inter", sans-serif;
    margin: 0; padding: 0;
    box-sizing: border-box;
}

/* ===============================
   🔥 FULL BACKGROUND IMAGE MODE
   =============================== */
body {
    width: 100%;
    height: 100vh;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;

    /* GANTI URL INI SESUAI LOKASI GAMBAR LO */
    background: url('/images/bg-matari.jpg') no-repeat center center/cover;
}

/* DARK OVERLAY BIAR CARD NYA POP-UP & TEXT KE BACA */
body::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.45); /* Gw gelapin dikit biar dramatis */
    backdrop-filter: blur(4px);
    z-index: 1;
}

/* ===============================
   🌟 LOGIN CARD - GLASS EFFECT
   =============================== */
.card {
    width: 380px;
    padding-bottom: 15px;
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: 18px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.25);
    border: 1px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
    z-index: 2;
}

/* ==================== HEADER ==================== */
.header {
    background: rgba(247,162,10,0.9);
    backdrop-filter: blur(4px);
    padding: 35px 25px 45px;
    text-align: center;
    color: white;
    position: relative;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}
.header h2 { margin-top: 8px; font-size: 22px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.header .subtitle { opacity: 0.95; font-size: 13px; font-weight: 500; }

/* WIFI BADGE */
.wifi-status {
    position: absolute;
    right: 15px; top: 15px;
    background: rgba(255,255,255,0.95);
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: 0.3s;
}
/* Class ini dipake sama JS lo nanti */
.wifi-status.green { color:#0a8f36; }
.wifi-status.red { color:#d62222; }

/* ==================== FORM WRAPPER ==================== */
.inner {
    padding: 30px;
}

/* INPUT STYLING */
.input-group { margin-bottom: 22px; }

label {
    font-size: 13px; color:#fff;
    font-weight: 600; margin-bottom: 8px;
    display: block;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.input-wrap {
    position: relative;
}
.input-wrap input {
    width: 100%;
    padding: 12px 40px;
    height: 48px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.15);
    color: #fff;
    font-size: 15px;
    backdrop-filter: blur(4px);
    transition: 0.2s;
}
.input-wrap input::placeholder { color: rgba(255,255,255,0.7); }
.input-wrap input:focus {
    outline: none;
    background: rgba(255,255,255,0.25);
    border-color: #ffe3b3;
    box-shadow: 0 0 0 2px rgba(247, 162, 10, 0.5);
}

/* ICONS */
.icon-left, .icon-right {
    position: absolute;
    top: 50%; transform: translateY(-50%);
    color: #fff;
    font-size: 16px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.icon-left { left: 14px; opacity: 0.9; }
.icon-right { right: 14px; cursor:pointer; opacity: 0.8; }
.icon-right:hover { opacity: 1; }

/* ==================== LOGIN BUTTON ==================== */
.btn-login {
    width: 100%;
    padding: 14px;
    border: none;
    margin-top: 15px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    background: linear-gradient(135deg, #f7a20a, #e68a00);
    color:white;
    transition: 0.2s;
    box-shadow: 0 4px 15px rgba(247, 162, 10, 0.4);
}
.btn-login:hover { 
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(247, 162, 10, 0.6);
}
.btn-login:active { transform: translateY(0); }

/* GPS INFO & FOOTER */
.info-status {
    font-size: 11px;
    text-align: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px dashed rgba(255,255,255,0.3);
    color: rgba(255,255,255,0.8);
}
.footer {
    text-align: center;
    font-size: 11px;
    margin-top: 15px;
    color: rgba(255,255,255,0.6);
}

/* ALERT ERROR (Glass Style) */
.alert {
    background: rgba(220, 38, 38, 0.85); /* Merah semi-transparan */
    backdrop-filter: blur(4px);
    border-left: 4px solid #fff;
    padding: 12px;
    color: #fff;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    font-weight: 500;
}
</style>
</head>

<body>

<div class="card">

    <div class="header">
        <div id="wifiStatusBadge" class="wifi-status">
            <span id="wifiEmoji"></span>
            <span id="wifiText">Checking...</span>
        </div>

        <div class="cup-icon" style="font-size:48px; margin-bottom:5px;">☕</div>
        <h2>Sistem Absensi Matari</h2>
        <p class="subtitle">Silakan login untuk memulai shift</p>
    </div>

    <div class="inner">

        @if(session('error'))
        <div class="alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form id="login-form" action="{{ route('login.process') }}" method="POST">
            @csrf

            <div class="input-group">
                <label>Username</label>
                <div class="input-wrap">
                    <span class="icon-left"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required autocomplete="off">
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-wrap">
                    <span class="icon-left"><i class="fa-solid fa-lock"></i></span>
                    <input id="password" type="password" name="password" placeholder="Masukkan password" required>
                    <span id="togglePass" class="icon-right" title="Lihat Password"><i class="fa-solid fa-eye"></i></span>
                </div>
            </div>

            <input type="hidden" id="lat" name="lat">
            <input type="hidden" id="lng" name="lng">
            <input type="hidden" id="wifi_connected" name="wifi_connected" value="0">

            <button type="submit" class="btn-login">→ Login ke Sistem</button>
        </form>

        <div class="info-status">Status Lokasi (GPS): <span id="geoText" style="font-weight:700;">—</span></div>

        <div class="footer">© Kedai Matari — Sistem Absensi V2</div>
    </div>
</div>

<script>
// 1. Toggle Password (UI Logic)
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

// 2. CONFIG SYSTEM
const WIFI_PROBE_URL = 'http://192.168.0.1/'; 
const WIFI_TIMEOUT_MS = 1500; 
const GEO_TIMEOUT_MS = 3000; 

// 3. Logic Cek WiFi (Fetch Timeout)
async function probeWifi(){
    try {
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), WIFI_TIMEOUT_MS);
        
        // Mode no-cors karena kita cuma mau tau connect/ga, bukan baca konten
        await fetch(WIFI_PROBE_URL, {mode:'no-cors', signal: controller.signal});
        clearTimeout(id);
        
        return true; 
    } catch(e){
        return false;
    }
}

// 4. Logic Cek GPS (Promise Wrapper)
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
        }, err => {
            if (resolved) return;
            resolved = true; clearTimeout(timer);
            resolve(null);
            console.error("Geolocation Error:", err.code, err.message);
        }, {enableHighAccuracy:true, maximumAge:10000, timeout:timeout});
    });
}

// 5. Main Execution (IIFE)
(async function initChecks(){
    const wifiBadge = document.getElementById('wifiStatusBadge');
    const wifiText = document.getElementById('wifiText');
    const wifiEmoji = document.getElementById('wifiEmoji');
    const geoText = document.getElementById('geoText');
    
    // Reset State
    wifiBadge.classList.remove('green', 'red');
    wifiEmoji.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    wifiText.innerText = 'Mendeteksi...';
    geoText.innerText = 'Mencari sinyal...';

    // Run Checks Parallel
    const [wifi, geo] = await Promise.allSettled([probeWifi(), getGeoPosition(GEO_TIMEOUT_MS)]);
    
    // --- Result WiFi ---
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

    // --- Result GPS ---
    const geoVal = (geo.status === 'fulfilled' && geo.value) ? geo.value : null;

    if (geoVal) {
        document.getElementById('lat').value = geoVal.lat;
        document.getElementById('lng').value = geoVal.lng;
        geoText.innerText = `OK (${geoVal.lat.toFixed(5)}, ${geoVal.lng.toFixed(5)})`;
        geoText.style.color = "#a7f3d0"; // Hijau muda dikit biar keliatan di dark overlay
    } else {
        geoText.innerText = 'Gagal / Izin Ditolak';
        geoText.style.color = "#fecaca"; // Merah muda dikit
    }
})();
</script>

</body>
</html>