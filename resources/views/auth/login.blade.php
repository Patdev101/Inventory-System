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
        button { width: 100%; padding: 11px 14px; border: 0; border-radius: 6px; background: #2563eb; color: white; font: inherit; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        button:hover { background: #1d4ed8; }
        button:disabled { opacity: .75; cursor: not-allowed; }
        .btn-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: inv-spin .7s linear infinite; }
        .btn-spinner[hidden] { display: none; }
        @keyframes inv-spin { to { transform: rotate(360deg); } }
        .field-error { margin: 6px 0 0; font-size: 12.5px; line-height: 1.4; color: #dc2626; display: flex; align-items: flex-start; gap: 6px; }
        .field-error[hidden] { display: none; }
        .field-error::before { content: "!"; flex: none; width: 15px; height: 15px; border-radius: 50%; background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; line-height: 15px; text-align: center; }
        input.field-invalid { border-color: #dc2626; }
        input.field-invalid:focus { box-shadow: 0 0 0 3px rgba(220, 38, 38, .15); }
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

        <form id="login-form" method="POST" action="{{ route('login.store') }}" novalidate>
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                <p class="field-error" id="email-error" hidden></p>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
                <p class="field-error" id="password-error" hidden></p>
            </div>
            <label class="remember">
                <input name="remember" type="checkbox" value="1">
                Remember me
            </label>
            <button type="submit" id="login-submit">
                <span class="btn-spinner" hidden></span>
                <span class="btn-label">Sign in</span>
            </button>
        </form>

        <p class="forgot-link"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
    </main>

    <script>
        (function () {
            const form = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            function friendlyMessage(input) {
                const validity = input.validity;

                if (validity.valueMissing) {
                    return input === emailInput ? 'Please enter your email address.' : 'Please enter your password.';
                }

                if (validity.typeMismatch && input === emailInput) {
                    return "Please include an '@' in the email address. '" + input.value + "' is missing an '@'.";
                }

                return input.validationMessage || 'This field is invalid.';
            }

            function validateField(input) {
                const errorEl = document.getElementById(input.id + '-error');
                if (!errorEl) return true;

                if (input.validity.valid) {
                    errorEl.hidden = true;
                    errorEl.textContent = '';
                    input.classList.remove('field-invalid');
                } else {
                    errorEl.textContent = friendlyMessage(input);
                    errorEl.hidden = false;
                    input.classList.add('field-invalid');
                }

                return input.validity.valid;
            }

            [emailInput, passwordInput].forEach(function (input) {
                input.addEventListener('input', function () {
                    if (input.classList.contains('field-invalid')) {
                        validateField(input);
                    }
                });
                input.addEventListener('blur', function () {
                    validateField(input);
                });
            });

            form.addEventListener('submit', function (e) {
                const emailValid = validateField(emailInput);
                const passwordValid = validateField(passwordInput);

                if (!emailValid || !passwordValid) {
                    e.preventDefault();
                    (emailValid ? passwordInput : emailInput).focus();
                    return;
                }

                const btn = document.getElementById('login-submit');
                btn.disabled = true;
                btn.querySelector('.btn-spinner').hidden = false;
                btn.querySelector('.btn-label').textContent = 'Signing in…';
            });
        })();
    </script>
</body>
</html>
