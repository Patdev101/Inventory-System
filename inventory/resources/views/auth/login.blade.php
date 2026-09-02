<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | Inventory System</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; font-family: Arial, Helvetica, sans-serif; background: #eaf1f8; color: #1e293b; }
        .panel { width: min(100%, 420px); background: white; padding: 32px; border: 1px solid #dbe4ee; border-radius: 10px; box-shadow: 0 12px 35px rgba(15, 23, 42, .1); }
        h1 { margin: 0 0 8px; color: #0f172a; font-size: 26px; }
        .intro { margin: 0 0 26px; color: #64748b; }
        .field { margin-bottom: 18px; }
        label { display: block; margin-bottom: 7px; font-weight: bold; }
        input { width: 100%; padding: 11px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
        .remember { display: flex; align-items: center; gap: 8px; margin: 4px 0 20px; color: #475569; font-size: 14px; }
        .remember input { width: auto; }
        button { width: 100%; padding: 11px 14px; border: 0; border-radius: 6px; background: #2563eb; color: white; font: inherit; font-weight: bold; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .error { margin: 0 0 16px; color: #b91c1c; font-size: 14px; }
        .status { margin: 0 0 16px; color: #166534; font-size: 14px; }
        .forgot-link { margin: 16px 0 0; text-align: center; font-size: 14px; }
        .forgot-link a { color: #2563eb; text-decoration: none; }
        .forgot-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="panel">
        <h1>Inventory System</h1>
        <p class="intro">Sign in to manage inventory and stock alerts.</p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <label class="remember">
                <input name="remember" type="checkbox" value="1">
                Remember me
            </label>
            <button type="submit">Sign in</button>
        </form>

        <p class="forgot-link"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
    </main>
</body>
</html>
