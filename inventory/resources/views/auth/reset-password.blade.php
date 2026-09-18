<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Inventory System</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; font-family: Arial, Helvetica, sans-serif; background: #eaf1f8; color: #1e293b; }
        .panel { width: min(100%, 420px); background: white; padding: 32px; border: 1px solid #dbe4ee; border-radius: 10px; box-shadow: 0 12px 35px rgba(15, 23, 42, .1); }
        h1 { margin: 0 0 8px; color: #0f172a; font-size: 26px; }
        .intro { margin: 0 0 20px; color: #64748b; line-height: 1.5; }
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
        .btn-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: rp-spin .7s linear infinite; }
        .btn-spinner[hidden] { display: none; }
        @keyframes rp-spin { to { transform: rotate(360deg); } }
        .back-link { margin: 16px 0 0; text-align: center; font-size: 14px; }
        .back-link a { color: #2563eb; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="panel">
        <h1>Reset your password</h1>
        <p class="intro">Choose a new password for your account.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form id="reset-form" method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus>
                <p class="field-error" id="email-error" hidden></p>
            </div>

            <div class="field">
                <label for="password">New Password</label>
                <input id="password" name="password" type="password" required minlength="8">
                <p class="field-error" id="password-error" hidden></p>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm New Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8">
                <p class="field-error" id="password_confirmation-error" hidden></p>
            </div>

            <button type="submit" id="reset-submit">
                <span class="btn-spinner" hidden></span>
                <span class="btn-label">Reset Password</span>
            </button>
        </form>

        <p class="back-link"><a href="{{ route('login') }}">Back to sign in</a></p>
    </main>

    <script>
        (function () {
            var form = document.getElementById('reset-form');
            var emailInput = document.getElementById('email');
            var passwordInput = document.getElementById('password');
            var confirmInput = document.getElementById('password_confirmation');

            function messageFor(input) {
                if (input.validity.valueMissing) {
                    return input === emailInput ? 'Please enter your email address.' : 'Please enter a password.';
                }
                if (input.validity.typeMismatch && input === emailInput) {
                    return "Please include an '@' in the email address. '" + input.value + "' is missing an '@'.";
                }
                if (input.validity.tooShort) {
                    return 'Password must be at least 8 characters.';
                }
                if (input === confirmInput && passwordInput.value !== confirmInput.value) {
                    return 'Passwords do not match.';
                }
                return input.validationMessage || 'This field is invalid.';
            }

            function isValid(input) {
                if (input === confirmInput) {
                    return input.validity.valid && passwordInput.value === confirmInput.value;
                }
                return input.validity.valid;
            }

            function validateField(input) {
                var errorEl = document.getElementById(input.id + '-error');
                if (!errorEl) return true;

                if (isValid(input)) {
                    errorEl.hidden = true;
                    errorEl.textContent = '';
                    input.classList.remove('field-invalid');
                } else {
                    errorEl.textContent = messageFor(input);
                    errorEl.hidden = false;
                    input.classList.add('field-invalid');
                }

                return isValid(input);
            }

            [emailInput, passwordInput, confirmInput].forEach(function (input) {
                input.addEventListener('input', function () {
                    if (input.classList.contains('field-invalid')) validateField(input);
                });
                input.addEventListener('blur', function () { validateField(input); });
            });

            form.addEventListener('submit', function (e) {
                var allValid = [emailInput, passwordInput, confirmInput]
                    .map(validateField)
                    .every(Boolean);

                if (!allValid) {
                    e.preventDefault();
                    return;
                }

                var btn = document.getElementById('reset-submit');
                btn.disabled = true;
                btn.querySelector('.btn-spinner').hidden = false;
                btn.querySelector('.btn-label').textContent = 'Resetting…';
            });
        })();
    </script>
</body>
</html>
