<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? 'Kedai Matari' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- INI KUNCI CSRF --}}
    
    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    {{-- Global Styles --}}
    <style>
        :root {
            --accent: #f7a20a;
            --accent-dark: #e28e00;
            --bg: #f6f6f6;
            --card: #fff;
            --muted: #7a7a7a;
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
            font-family: 'Inter', system-ui, Arial, sans-serif
        }

        body {
            margin: 0;
            background: var(--bg);
            color: #222
        }

        /* --- LAYOUTS & UI ELEMENTS --- */
        .topbar {
            background: var(--accent);
            padding: 14px 24px;
            color: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.03)
        }

        .brand {
            font-weight: 700
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 22px auto
        }

        .card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: 0 6px 22px rgba(20, 20, 20, 0.04);
            margin-bottom: 18px
        }

        /* --- BUTTONS & LINKS --- */
        .btn {
            background: var(--accent);
            color: #fff;
            padding: 8px 12px;
            border-radius: 10px;
            border: 0;
            cursor: pointer;
            text-decoration: none;
            display: inline-block
        }

        .btn.secondary {
            background: #e9e9e9;
            color: #333
        }
        
        /* --- NAVIGASI (Grouped Tabs) --- */
        .navbar-owner {
            display: flex;
            padding: 5px; 
            background: var(--card); 
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08); 
            margin-bottom: 20px; 
            margin-top: -10px;
            border: 1px solid #eee;
            overflow-x: auto; /* Antisipasi kalau menu kepanjangan di HP */
        }
        
        /* Style Link Navigasi */
        .navbar-owner a {
            text-decoration: none;
            color: #4b5563; 
            font-weight: 500;
            padding: 8px 15px; 
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap; 
        }
        
        .navbar-owner a:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .navbar-owner a.active {
            background: var(--accent); 
            color: white; 
            box-shadow: 0 1px 5px rgba(247, 162, 10, 0.4);
            font-weight: 600;
        }

        /* --- FORMS --- */
        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 999px;
            border: 1px solid #e6e6e6
        }
        
        .form-row {
            display: flex;
            gap: 12px
        }

        .form-row .col-2 {
            flex: 2
        }

        .form-row .col-1 {
            flex: 1
        }
        
        .small-muted {
            font-size: 12px;
            color: var(--muted)
        }

        /* --- TABLES & DATA DISPLAY --- */
        .row {
            display: flex;
            gap: 18px;
            flex-wrap: wrap
        }

        .col {
            flex: 1
        }

        .float-right {
            text-align: right
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px
        }

        .table th {
            background: transparent;
            padding: 14px;
            text-align: left;
            color: var(--muted);
            font-size: 13px
        }

        .table td {
            padding: 12px;
            border-top: 1px solid #f0f0f0
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px
        }

        .badge.green {
            background: #e6fbec;
            color: #0a9a3d
        }

        .badge.yellow {
            background: #fff7e6;
            color: #c07b00
        }

        .badge.red {
            background: #ffecec;
            color: #d02b2b
        }

        /* --- CALENDAR COMPONENTS --- */
        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 12px
        }

        .cal-cell {
            background: #fff;
            border-radius: 10px;
            padding: 12px;
            min-height: 78px;
            position: relative;
            border: 1px solid #f0f0f0
        }

        .cal-day {
            font-weight: 600
        }

        .legend {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 14px
        }

        .legend .dot {
            width: 10px;
            height: 10px;
            border-radius: 99px;
            display: inline-block
        }

        .dot.green {
            background: #26a84b
        }

        .dot.yellow {
            background: #f2b400
        }

        .dot.red {
            background: #e74c3c
        }

        /* --- MODAL --- */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(12, 12, 12, 0.35);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 60
        }

        .modal {
            background: var(--card);
            border-radius: 14px;
            padding: 18px;
            width: 100%;
            max-width: 720px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12)
        }
        
        /* --- TOAST STYLES --- */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }

        .toast {
            background: #fff;
            color: #333;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 250px;
            transition: opacity 0.3s, transform 0.3s;
        }

        .toast.success {
            border-left: 5px solid #2ecc71;
        }

        .toast.error {
            border-left: 5px solid #e74c3c;
        }
    </style>
    @stack('styles')
</head>
<body>

<div id="toast-container"></div>

<div class="topbar">
    <div class="brand">Kedai Matari Absensi & Inventory</div>
    <div style="display:flex; align-items:center; gap:10px;">
        {{-- Display User Info --}}
        <span style="color:#fff;opacity:0.95">{{ auth()->user()->name ?? 'Guest' }}</span>
        
        {{-- TOMBOL LOGOUT (Dipicu oleh Form Logout tersembunyi di bawah) --}}
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-topbar').submit();"
           style="background:#fff;padding:8px;border-radius:8px;color:#cc4444;font-weight:700;font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
            <i class="fa-solid fa-sign-out-alt" style="font-size:12px;"></i> Logout
        </a>
    </div>
</div>

<div class="container">
    {{-- NAVIGASI LEVEL 1 (ROLE-BASED) --}}
    @if(auth()->check())
        <div class="navbar-owner">
            @if(auth()->user()->role === 'owner')
                {{-- NAVIGASI OWNER (FULL ACCESS) --}}
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge" style="font-size:14px;"></i> Dashboard
                </a>
                <a href="{{ route('pegawai.index') }}" class="nav-item {{ request()->is('pegawai*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users" style="font-size:14px;"></i> Kelola Karyawan
                </a>
                <a href="{{ route('shifts.index') }}" class="nav-item {{ request()->is('shifts*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock" style="font-size:14px;"></i> Shift Kerja
                </a>
                <a href="{{ route('absensi.rekap') }}" class="nav-item {{ request()->is('absensi/rekap') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list" style="font-size:14px;"></i> Rekap Absensi
                </a>
                <a href="{{ route('pos.import.form') }}" class="nav-item {{ request()->is('import/csv') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-import" style="font-size:14px;"></i> Impor CSV
                </a>
                <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->is('inventory*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked" style="font-size:14px;"></i> Inventory
                </a>

            @else
                {{-- NAVIGASI PEGAWAI (LIMITED ACCESS: Dashboard, Inventory, Absensi) --}}
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house" style="font-size:14px;"></i> Dashboard
                </a>
                <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->is('inventory*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked" style="font-size:14px;"></i> Inventory
                </a>
                <a href="{{ route('absensi.index') }}" class="nav-item {{ request()->is('absensi') ? 'active' : '' }}">
                    <i class="fa-solid fa-fingerprint" style="font-size:14px;"></i> Riwayat Absensi
                </a>
            @endif
        </div>
    @endif
    
    {{-- Content Section --}}
    @yield('content')
</div>


{{-- JAVASCRIPT LOGIC --}}
<script>
    // MODAL UTILITY
    function openModal(id) {
        document.getElementById(id).style.display = 'flex'
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none'
    }

    // TOAST NOTIFICATION LOGIC (Self-Executing Function)
    (function() {
        const container = document.getElementById('toast-container');
        
        // Looping untuk menampilkan pesan session
        @if (session('success'))
            showToast('{{ session('success') }}', 'success');
        @elseif (session('error'))
            showToast('{{ session('error') }}', 'error');
        @elseif (session('info'))
            showToast('{{ session('info') }}', 'info');
        @endif

        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            let icon = '';
            if (type === 'success') {
                icon = '<i class="fa-solid fa-circle-check" style="color:#2ecc71;"></i>';
            } else if (type === 'error') {
                icon = '<i class="fa-solid fa-circle-xmark" style="color:#e74c3c;"></i>';
            } else {
                icon = '<i class="fa-solid fa-circle-info" style="color:#3498db;"></i>';
            }

            toast.innerHTML = icon + message;
            
            container.appendChild(toast);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
    })();
</script>

{{-- Form tersembunyi untuk Logout --}}
<form id="logout-form-topbar" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
</form>

{{-- Stack for custom scripts from child views --}}
@stack('scripts')

</body>
</html>