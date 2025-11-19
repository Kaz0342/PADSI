<!DOCTYPE html>

<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kedai Matari | Login</title>
<!-- Pastikan ini ada di head, meskipun @csrf dipakai di form -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

<style>
    /* CSS GABUNGAN DAN DIRAPIKAN */
    * {
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #fff8e1; /* Warna cream khas kedai kopi */
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .login-card {
        background: white;
        width: 350px;
        border-radius: 12px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        text-align: center;
    }

    .login-header {
        background: #f7a20a; /* Warna orange accent */
        color: white;
        padding: 20px;
    }

    .login-header h2 {
        margin-bottom: 5px;
        font-size: 18px;
    }

    .login-header p {
        font-size: 13px;
        opacity: 0.9;
    }

    .login-body {
        padding: 25px 30px;
    }

    .form-group {
        margin-bottom: 15px;
        text-align: left;
    }

    label {
        font-size: 13px;
        color: #333;
        display: block;
        margin-bottom: 5px;
    }

    input {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    input:focus {
        outline: none;
        border-color: #f7a20a;
        box-shadow: 0 0 0 1px #f7a20a;
    }

    .btn {
        width: 100%;
        background: #f7a20a;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
        margin-top: 10px;
    }

    .btn:hover {
        background: #e28e00;
    }

    .footer {
        font-size: 12px;
        margin-top: 20px;
        color: #777;
    }

    .alert {
        background-color: #ffdddd;
        border-left: 4px solid #f44336;
        color: #a94442;
        padding: 10px;
        font-size: 13px;
        margin-bottom: 15px;
        border-radius: 6px;
        text-align: left;
    }
</style>


</head>
<body>
<div class="login-card">
<div class="login-header">
<div style="font-size:40px;">☕</div>
<h2>Cafe Attendance System</h2>
<p>Silakan login untuk melanjutkan</p>
</div>

    <div class="login-body">
        @if(session('error'))
            <div class="alert">{{ session('error') }}</div>
        @endif

        <form action="{{ route('login.process') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn">→ Login ke Sistem</button>
        </form>

        <div class="footer">
            <p>© Kedai Matari</p>
        </div>
    </div>
</div>


</body>
</html>