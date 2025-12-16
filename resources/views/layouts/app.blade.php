<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? 'Kedai Matari System' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    
    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        /* =========================================
           🎨 GLOBAL VARIABLES & RESET
           ========================================= */
        :root {
            --accent: #f7a20a;         /* Orange Matari */
            --accent-hover: #e28e00;
            --text-main: #ffffff;      /* Text Utama Putih */
            --text-muted: #d1d5db;     /* Text Muted Abu Terang */
            
            /* GLASS VARIABLES */
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.15);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            
            --radius: 14px;
        }

        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }

        /* =========================================
           🖼️ BACKGROUND CINEMATIC
           ========================================= */
        body {
            margin: 0;
            /* Background Image Fixed biar gak gerak pas scroll */
            background: url('/images/bg-matari.jpg') no-repeat center center/cover fixed;
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Dark Overlay Global */
        body::before {
            content: ""; position: fixed; inset: 0;
            background: rgba(0,0,0,0.65); /* Overlay Gelap */
            backdrop-filter: blur(6px);    /* Blur Background */
            z-index: -1;
        }

        /* =========================================
           💎 UI COMPONENTS (GLASSMOPHISM)
           ========================================= */
        
        /* 1. TOPBAR */
        .topbar {
            background: rgba(0,0,0,0.4); /* Semi-transparent dark */
            backdrop-filter: blur(10px);
            padding: 15px 5%;
            border-bottom: 1px solid var(--glass-border);
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }

        .brand {
            font-weight: 800; font-size: 18px; 
            background: linear-gradient(90deg, #fff, #f7a20a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        /* 2. CONTAINER */
        .container {
            width: 92%; max-width: 1200px; margin: 25px auto;
            padding-bottom: 40px;
        }

        /* 3. CARD (GLOBAL) - Biar semua page otomatis Glassy */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--glass-shadow);
            margin-bottom: 20px;
            color: var(--text-main);
        }

        /* =========================================
           🧭 NAVIGATION BAR (SCROLLABLE GLASS)
           ========================================= */
        .navbar-owner {
            display: flex; gap: 8px; padding: 8px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            margin-bottom: 25px;
            overflow-x: auto; /* Scrollable di HP */
            white-space: nowrap;
            backdrop-filter: blur(10px);
        }
        
        /* Custom Scrollbar tipis */
        .navbar-owner::-webkit-scrollbar { height: 4px; }
        .navbar-owner::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }

        .navbar-owner a {
            text-decoration: none; color: var(--text-muted);
            font-weight: 500; font-size: 13px;
            padding: 10px 16px; border-radius: 8px;
            transition: all 0.2s ease;
            display: flex; align-items: center; gap: 8px;
        }
        
        .navbar-owner a:hover {
            background: rgba(255,255,255,0.1); color: #fff;
        }
        
        .navbar-owner a.active {
            background: var(--accent); 
            color: #fff; /* Text hitam di orange biar kontras */
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(247, 162, 10, 0.4);
        }

        /* =========================================
           📝 FORMS & INPUTS (GLOBAL DARK MODE)
           ========================================= */
        input, select, textarea {
            width: 100%; padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.3); /* Dark input bg */
            color: #fff;
            outline: none; transition: 0.2s;
            font-size: 14px;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(247, 162, 10, 0.2);
            background: rgba(0, 0, 0, 0.5);
        }
        /* Placeholder color */
        ::placeholder { color: rgba(255,255,255,0.4); }

        /* Option di select (Browser default ga bisa transparan) */
        option { background: #222; color: #fff; }

        .btn {
            background: var(--accent); color: #fff;
            padding: 10px 20px; border-radius: 10px; border: 0;
            font-weight: 600; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
            transition: 0.2s;
        }
        .btn:hover { background: var(--accent-hover); transform: translateY(-2px); }
        .btn.secondary { background: rgba(255,255,255,0.1); color: #fff; }
        .btn.secondary:hover { background: rgba(255,255,255,0.2); }

        /* =========================================
           📊 TABLES (TRANSPARENT)
           ========================================= */
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th {
            text-align: left; padding: 15px;
            color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 1px solid var(--glass-border);
        }
        .table td {
            padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
        }
        .table tr:hover td { background: rgba(255,255,255,0.05); }

        /* =========================================
           🔔 TOAST & MODAL
           ========================================= */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .toast {
            background: rgba(30, 30, 30, 0.9); backdrop-filter: blur(10px);
            color: #fff; padding: 14px 20px; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid var(--glass-border);
            display: flex; align-items: center; gap: 12px; min-width: 280px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn { from{transform:translateX(100%);opacity:0;} to{transform:translateX(0);opacity:1;} }
        
        .toast.success i { color: #4ade80; }
        .toast.error i { color: #f87171; }
        .toast.info i { color: #60a5fa; }

    </style>
    @stack('styles')
</head>
<body>

<div id="toast-container"></div>

{{-- TOPBAR GLASS --}}
<div class="topbar">
    <div class="brand">
        <i class="fa-solid fa-mug-hot" style="margin-right:8px; color:var(--accent);"></i>
        Kedai Matari
    </div>
    
    <div style="display:flex; align-items:center; gap:15px;">
        {{-- User Info --}}
        <div style="text-align:right; display:none; @media(min-width:768px){display:block;}">
            <div style="font-size:14px; font-weight:700;">{{ auth()->user()->name ?? 'Guest' }}</div>
            <div style="font-size:11px; opacity:0.7; text-transform:uppercase;">{{ auth()->user()->role ?? '-' }}</div>
        </div>
        
        {{-- LOGOUT BUTTON --}}
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-topbar').submit();"
           style="background:rgba(255,50,50,0.15); padding:8px 12px; border-radius:8px; color:#f87171; font-weight:600; font-size:13px; text-decoration:none; display:flex; align-items:center; gap:6px; border:1px solid rgba(255,50,50,0.2); transition:0.2s;">
            <i class="fa-solid fa-power-off"></i> 
            <span style="display:none; @media(min-width:600px){display:inline;}">Logout</span>
        </a>
    </div>
</div>

<div class="container">
    {{-- NAVIGASI LEVEL 1 (ROLE-BASED) --}}
    @if(auth()->check())
        <div class="navbar-owner">
            @if(auth()->user()->role === 'owner')
                {{-- OWNER NAV --}}
                <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
                <a href="{{ route('pegawai.index') }}" class="{{ request()->is('pegawai*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Karyawan
                </a>
                <a href="{{ route('shifts.index') }}" class="{{ request()->is('shifts*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i> Shift
                </a>
                <a href="{{ route('absensi.rekap') }}" class="{{ request()->is('absensi/rekap') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i> Rekap Absen
                </a>
                <a href="{{ route('pos.import.form') }}" class="{{ request()->is('import/csv') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-csv"></i> POS Data
                </a>
            @else
                {{-- EMPLOYEE NAV --}}
                <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Home
                </a>
                <a href="{{ route('absensi.index') }}" class="{{ request()->is('absensi') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Saya
                </a>
            @endif
        </div>
    @endif
    
    {{-- CONTENT SECTION --}}
    @yield('content')
</div>

{{-- LOGOUT FORM HIDDEN --}}
<form id="logout-form-topbar" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
</form>

{{-- SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // --- UTILITIES ---
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    // --- TOAST NOTIFICATION LOGIC ---
    (function() {
        const container = document.getElementById('toast-container');

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            let icon = '<i class="fa-solid fa-circle-info"></i>';
            if (type === 'success') icon = '<i class="fa-solid fa-circle-check"></i>';
            if (type === 'error') icon = '<i class="fa-solid fa-circle-exclamation"></i>';

            toast.innerHTML = `${icon} <span>${message}</span>`;
            container.appendChild(toast);

            // Auto remove
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Trigger dari Session Laravel
        @if (session('success')) window.showToast("{{ session('success') }}", 'success'); @endif

        @if (session()->has('error') && !request()->is('dashboard'))
            window.showToast("{{ session('error') }}", 'error');
        @endif

        @if (session()->has('info') && !request()->is('dashboard'))
            window.showToast("{{ session('info') }}", 'info');
        @endif

        // Expose ke global biar bisa dipanggil dari JS lain
        window.showToast = showToast;
    })();
</script>

@stack('scripts')

</body>
</html>