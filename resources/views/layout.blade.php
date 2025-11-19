<!DOCTYPE html>

<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Kedai Matari - @yield('title', 'Dashboard')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<!-- Tambahkan Font Awesome untuk ikon logout -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    :root {
        --orange: #f7a20a;
        --orange-dark: #e58e00;
        --bg: #f6f6f6;
        --card: #ffffff;
        --muted: #6b7280;
    }

    * {
        box-sizing: border-box;
        font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial;
    }

    body {
        margin: 0;
        background: var(--bg);
        color: #111;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    /* ===== LAYOUT WRAPPER ===== */
    .wrap {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar (owner only) */
    .sidebar {
        width: 220px;
        background: #fff;
        padding: 18px;
        border-right: 1px solid #eee;
        display: flex;
        flex-direction: column;
        gap: 18px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .brand .logo {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--orange), var(--orange-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
    }

    .brand h3 {
        margin: 0;
        font-size: 16px
    }

    .nav {
        margin-top: 8px;
        display: flex;
        flex-direction: column;
        gap: 6px
    }

    .nav a {
        padding: 10px;
        border-radius: 8px;
        color: #333;
        font-weight: 600
    }

    .nav a.active {
        background: linear-gradient(90deg, var(--orange), var(--orange-dark));
        color: white;
    }

    .nav a small {
        display: block;
        font-weight: 400;
        color: var(--muted);
        font-size: 12px
    }

    /* Main area */
    .main {
        flex: 1;
        padding: 24px;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px
    }

    .top-left {
        display: flex;
        align-items: center;
        gap: 12px
    }

    .user-bubble {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        padding: 8px 12px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03)
    }

    .user-bubble .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--orange);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700
    }

    .clock {
        font-weight: 600;
        color: var(--muted)
    }

    /* Cards grid */
    .grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px
    }

    .card {
        background: var(--card);
        padding: 18px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04)
    }

    /* small screens */
    @media (max-width:900px) {
        .sidebar {
            display: none
        }

        .grid {
            grid-template-columns: repeat(2, 1fr)
        }

        .main {
            padding: 16px
        }
    }

    @media (max-width:480px) {
        .grid {
            grid-template-columns: 1fr
        }
    }

    /* helper buttons */
    .btn {
        background: var(--orange);
        color: white;
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 700
    }

    .btn-outline {
        background: #fff;
        border: 1px solid #eee;
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 600;
        display: flex; /* Tambahkan flex untuk ikon */
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
</style>


</head>
<body>
<div class="wrap">
{{-- SIDEBAR only for owner or manajer --}}
@php $me = session('user'); @endphp
@if($me && ($me->role === 'owner' || $me->role === 'manajer'))
<aside class="sidebar">
<div class="brand">
<div class="logo">KM</div>
<div>
<h3>Kedai Matari</h3>
<small style="color:var(--muted)">Cafe Attendance</small>
</div>
</div>

        <nav class="nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">🏠 Dashboard</a>
            <a href="{{ route('absensi.index') }}" class="{{ request()->is('absensi*') ? 'active' : '' }}">🕒 Absensi</a>
            <a href="{{ route('pegawai.index') }}" class="{{ request()->is('pegawai*') ? 'active' : '' }}">👥 Kelola Karyawan</a>
            <a href="#">📦 Stok Opname</a>
        </nav>

        <div style="margin-top:auto">
            {{-- Form tersembunyi untuk POST Logout --}}
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
            
            {{-- Link yang memicu submit form POST --}}
            <a href="#" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
               class="btn-outline">
               <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>
    @endif

    {{-- MAIN --}}
    <main class="main">
        <div class="topbar">
            <div class="top-left">
                <h2 style="margin:0">@yield('page_title', 'Dashboard')</h2>
            </div>

            <div style="display:flex;align-items:center;gap:12px">
                {{-- Clock only for owner --}}
                @if($me && ($me->role === 'owner' || $me->role === 'manajer'))
                <div class="clock" id="realtime-clock">-- : --</div>
                @endif

                <div class="user-bubble">
                    <div class="avatar">{{ strtoupper(substr($me->name ?? 'U',0,1)) }}</div>
                    <div>
                        <div style="font-weight:700">{{ $me->name ?? 'Guest' }}</div>
                        <div style="font-size:12px;color:var(--muted)">{{ ucfirst($me->role ?? '') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- content --}}
        @yield('content')
    </main>
</div>

<script>
    // realtime clock (owner only)
    (function() {
        function pad(n) {
            return n < 10 ? ('0' + n) : n
        }

        function update() {
            const el = document.getElementById('realtime-clock');
            if (!el) return;
            const d = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const str = days[d.getDay()] + ', ' + pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() + ' · ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
            el.innerText = str;
        }
        update();
        setInterval(update, 1000);
    })();
</script>


</body>
</html>