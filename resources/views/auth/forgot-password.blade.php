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
        .status { margin: 0 0 16px; padding: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; color: #065f46; font-size: 14px; line-height: 1.5; }
        .error { margin: 0 0 16px; color: #b91c1c; font-size: 14px; }
        .field { margin-bottom: 18px; }
        label { display: block; margin-bottom: 7px; font-weight: bold; }
        input { width: 100%; padding: 11px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
        input.field-invalid { border-color: #dc2626; }
        .field-error { margin: 6px 0 0; font-size: 12.5px; line-height: 1.4; color: #dc2626; display: flex; align-items: flex-start; gap: 6px; }
        .field-error[hidden] { display: none; }
        .field-error::before { content: "!"; flex: none; width: 15px; height: 15px; border-radius: 50%; background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; line-height: 15px; text-align: center; }
        button { width: 100%; padding: 11px 14px; border: 0; border-radius: 6px; background: #2563eb; color: white; font: inherit; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        button:hover { background: #1d4ed8; }
        button:disabled { opacity: .75; cursor: not-allowed; }
        .btn-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: fp-spin .7s linear infinite; }
        .btn-spinner[hidden] { display: none; }
        @keyframes fp-spin { to { transform: rotate(360deg); } }
        .back-link { margin: 16px 0 0; text-align: center; font-size: 14px; }
        .back-link a { color: #2563eb; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="panel">
        <h1>Forgot your password?</h1>
        <p class="intro">
            Enter your account email and we'll send you a link to reset your password.
        </p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form id="forgot-form" method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                <p class="field-error" id="email-error" hidden></p>
            </div>
            <button type="submit" id="forgot-submit">
                <span class="btn-spinner" hidden></span>
                <span class="btn-label">Send Reset Link</span>
            </button>
        </form>

        <div class="info-box" style="margin-top: 20px; margin-bottom: 0;">
            No luck? An administrator can still reset your password directly from <strong>User Management</strong>.
        </div>

        <p class="back-link"><a href="{{ route('login') }}">Back to sign in</a></p>
    </main>

    <script>
        (function () {
            var form = document.getElementById('forgot-form');
            var emailInput = document.getElementById('email');

            function validateField(input) {
                var errorEl = document.getElementById(input.id + '-error');
                if (!errorEl) return true;

                if (input.validity.valid) {
                    errorEl.hidden = true;
                    errorEl.textContent = '';
                    input.classList.remove('field-invalid');
                } else {
                    errorEl.textContent = input.validity.valueMissing
                        ? 'Please enter your email address.'
                        : "Please include an '@' in the email address. '" + input.value + "' is missing an '@'.";
                    errorEl.hidden = false;
                    input.classList.add('field-invalid');
                }

                return input.validity.valid;
            }

            emailInput.addEventListener('input', function () {
                if (emailInput.classList.contains('field-invalid')) validateField(emailInput);
            });
            emailInput.addEventListener('blur', function () { validateField(emailInput); });

            form.addEventListener('submit', function (e) {
                if (!validateField(emailInput)) {
                    e.preventDefault();
                    emailInput.focus();
                    return;
                }

                var btn = document.getElementById('forgot-submit');
                btn.disabled = true;
                btn.querySelector('.btn-spinner').hidden = false;
                btn.querySelector('.btn-label').textContent = 'Sending…';
            });
        })();
    </script>
</body>
</html>
