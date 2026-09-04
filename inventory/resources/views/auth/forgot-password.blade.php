<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Inventory System</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; font-family: Arial, Helvetica, sans-serif; background: #eaf1f8; color: #1e293b; }
        .panel { width: min(100%, 420px); background: white; padding: 32px; border: 1px solid #dbe4ee; border-radius: 10px; box-shadow: 0 12px 35px rgba(15, 23, 42, .1); }
        h1 { margin: 0 0 8px; color: #0f172a; font-size: 26px; }
        .intro { margin: 0 0 20px; color: #64748b; line-height: 1.5; }
        .info-box { margin-bottom: 20px; padding: 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; color: #1e40af; font-size: 14px; line-height: 1.5; }
        .back-link { margin: 16px 0 0; text-align: center; font-size: 14px; }
        .back-link a { color: #2563eb; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="panel">
        <h1>Forgot your password?</h1>
        <p class="intro">
            This is an internal business system with no automated email
            password reset.
        </p>

        <div class="info-box">
            Please contact an administrator. They can reset your password
            for you from <strong>User Management</strong>.
        </div>

        <p class="back-link"><a href="{{ route('login') }}">Back to sign in</a></p>
    </main>
</body>
</html>
