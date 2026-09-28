<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Getting ready | Inventory System</title>
    <style>
        :root {
            --accent: #2563eb; --accent-dark: #1d4ed8; --accent-soft: #dbeafe;
            --ink: #0f172a; --text: #1e293b; --muted: #64748b; --line: #dbe4ee;
            --bg: #eaf1f8; --panel: #fff; --ok: #16a34a; --warn: #d97706; --bad: #dc2626;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; font-family: Arial, Helvetica, sans-serif; background: var(--bg); color: var(--text); }
        .shell { width: min(100%, 940px); display: grid; grid-template-columns: 380px 1fr; background: var(--panel); border: 1px solid var(--line); border-radius: 14px; overflow: hidden; box-shadow: 0 18px 45px rgba(15, 23, 42, .12); }

        /* Left: progress side */
        .aside { background: linear-gradient(160deg, var(--accent) 0%, #1e3a8a 100%); color: #fff; padding: 36px 32px; display: flex; flex-direction: column; gap: 28px; }
        .eyebrow { display: inline-block; font-size: 12px; font-weight: bold; letter-spacing: .12em; text-transform: uppercase; background: rgba(255,255,255,.16); padding: 5px 10px; border-radius: 999px; }
        .aside h1 { margin: 14px 0 8px; font-size: 28px; line-height: 1.2; }
        .aside p { margin: 0; color: rgba(255,255,255,.82); line-height: 1.5; font-size: 14.5px; }
        .steps { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; }
        .step { display: grid; grid-template-columns: 32px 1fr; gap: 12px; padding: 10px 0; }
        .dot { width: 32px; height: 32px; border-radius: 50%; display: grid; place-items: center; font-weight: bold; font-size: 14px; border: 2px solid rgba(255,255,255,.4); color: rgba(255,255,255,.8); }
        .step.current .dot { background: #fff; color: var(--accent); border-color: #fff; box-shadow: 0 0 0 5px rgba(255,255,255,.18); }
        .step strong { display: block; font-size: 15px; }
        .step span { font-size: 13px; color: rgba(255,255,255,.72); }
        .step:not(.current) strong { color: rgba(255,255,255,.85); }
        .notice { margin-top: auto; background: rgba(15,23,42,.28); border-radius: 10px; padding: 14px 16px; font-size: 13px; line-height: 1.5; display: flex; gap: 10px; }
        .notice svg { flex: none; margin-top: 1px; }

        /* Right: form side */
        .main { padding: 36px 36px 32px; }
        .main h2 { margin: 0 0 6px; color: var(--ink); font-size: 22px; }
        .intro { margin: 0 0 24px; color: var(--muted); font-size: 14.5px; }
        .role-chip { display: inline-flex; align-items: center; gap: 6px; background: var(--accent-soft); color: var(--accent-dark); font-size: 12.5px; font-weight: bold; padding: 5px 10px; border-radius: 999px; margin-bottom: 18px; }
        .field { margin-bottom: 16px; }
        label { display: block; margin-bottom: 7px; font-weight: bold; font-size: 14px; }
        .input-wrap { position: relative; }
        input { width: 100%; padding: 11px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; color: var(--text); }
        input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
        input.field-invalid { border-color: var(--bad); }
        .input-wrap input { padding-right: 44px; }
        input::-ms-reveal, input::-ms-clear { display: none; }
        .toggle { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: 0; background: none; color: var(--muted); cursor: pointer; padding: 6px; display: grid; place-items: center; border-radius: 6px; }
        .toggle:hover, .toggle:focus-visible { color: var(--accent); background: var(--accent-soft); outline: none; }
        .toggle svg[hidden] { display: none; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .meter { height: 6px; background: #e2e8f0; border-radius: 99px; margin-top: 8px; overflow: hidden; }
        .meter i { display: block; height: 100%; width: 0; background: var(--bad); transition: width .2s, background .2s; }
        .rules { list-style: none; margin: 10px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12.5px; color: var(--muted); }
        .rules li::before { content: "○ "; }
        .rules li.met { color: var(--ok); }
        .rules li.met::before { content: "✓ "; }
        .field-error { margin: 6px 0 0; font-size: 12.5px; color: var(--bad); }
        .field-error[hidden] { display: none; }
        .error { margin: 0 0 16px; padding: 10px 12px; border-radius: 6px; background: #fef2f2; color: #b91c1c; font-size: 14px; }
        button[type=submit] { width: 100%; margin-top: 8px; padding: 12px 14px; border: 0; border-radius: 6px; background: var(--accent); color: #fff; font: inherit; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        button[type=submit]:hover { background: var(--accent-dark); }
        button[type=submit]:disabled { opacity: .75; cursor: not-allowed; }
        .btn-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
        .btn-spinner[hidden] { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .foot { margin: 14px 0 0; font-size: 12.5px; color: var(--muted); text-align: center; }

        @media (max-width: 820px) {
            .shell { grid-template-columns: 1fr; }
            .aside { padding: 28px 22px; gap: 20px; }
            .main { padding: 28px 22px; }
            .row { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="aside">
            <div>
                <span class="eyebrow">Getting ready</span>
                <h1>Welcome to Inventory System</h1>
                <p>Let's set up your system. It only takes a minute, and you'll only do it once.</p>
            </div>

            <ol class="steps">
                <li class="step current">
                    <div class="dot">1</div>
                    <div><strong>Create the admin account</strong><span>The owner account with full access</span></div>
                </li>
                <li class="step">
                    <div class="dot">2</div>
                    <div><strong>Sign in as admin</strong><span>Use the email and password you set</span></div>
                </li>
                <li class="step">
                    <div class="dot">3</div>
                    <div><strong>Add your team</strong><span>Create managers, staff and other admins from User Management</span></div>
                </li>
            </ol>

            <div class="notice">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span><strong>One-time setup.</strong> This page is permanently disabled once the admin account is created, so no one else can make an admin account here.</span>
            </div>
        </aside>

        <main class="main">
            <span class="role-chip">★ Administrator</span>
            <h2>Create your admin account</h2>
            <p class="intro">This account manages users, settings and every part of the system. Keep its password safe.</p>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form id="setup-form" method="POST" action="{{ route('setup.store') }}" novalidate>
                @csrf
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="admin@company.com">
                </div>
                <div class="row">
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                            <button type="button" class="toggle" data-target="password" aria-label="Show password" title="Show password"><svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="2" y1="2" x2="22" y2="22"/></svg></button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirm password</label>
                        <div class="input-wrap">
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                            <button type="button" class="toggle" data-target="password_confirmation" aria-label="Show password" title="Show password"><svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="2" y1="2" x2="22" y2="22"/></svg></button>
                        </div>
                        <p class="field-error" id="confirm-error" hidden>Passwords do not match.</p>
                    </div>
                </div>
                <div class="meter" aria-hidden="true"><i id="meter-bar"></i></div>
                <ul class="rules" id="rules">
                    <li data-rule="len">At least 8 characters</li>
                    <li data-rule="letter">A letter</li>
                    <li data-rule="number">A number</li>
                    <li data-rule="match">Passwords match</li>
                </ul>

                <button type="submit" id="setup-submit" disabled>
                    <span class="btn-spinner" hidden></span>
                    <span class="btn-label">Create admin account</span>
                </button>
            </form>

            <p class="foot">After this you'll be taken to the sign-in page.</p>
        </main>
    </div>

    <script>
        (function () {
            const form = document.getElementById('setup-form');
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const pw = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            const submit = document.getElementById('setup-submit');
            const bar = document.getElementById('meter-bar');
            const confirmError = document.getElementById('confirm-error');

            document.querySelectorAll('.toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const input = document.getElementById(btn.dataset.target);
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.querySelector('.icon-eye').hidden = show;
                    btn.querySelector('.icon-eye-off').hidden = !show;
                    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    btn.title = btn.getAttribute('aria-label');
                });
            });

            function update() {
                const v = pw.value;
                const checks = {
                    len: v.length >= 8,
                    letter: /[A-Za-z]/.test(v),
                    number: /\d/.test(v),
                    match: v !== '' && v === confirm.value,
                };
                document.querySelectorAll('#rules li').forEach(function (li) {
                    li.classList.toggle('met', checks[li.dataset.rule]);
                });

                let score = [checks.len, checks.letter, checks.number, /[^A-Za-z0-9]/.test(v), v.length >= 12].filter(Boolean).length;
                bar.style.width = (score * 20) + '%';
                bar.style.background = score <= 2 ? 'var(--bad)' : score <= 3 ? 'var(--warn)' : 'var(--ok)';

                const mismatch = confirm.value !== '' && !checks.match;
                confirmError.hidden = !mismatch;
                confirm.classList.toggle('field-invalid', mismatch);

                submit.disabled = !(name.value.trim() && email.validity.valid && email.value && checks.len && checks.letter && checks.number && checks.match);
            }

            [name, email, pw, confirm].forEach(function (input) { input.addEventListener('input', update); });
            update();

            form.addEventListener('submit', function (e) {
                if (submit.disabled) { e.preventDefault(); return; }
                submit.disabled = true;
                submit.querySelector('.btn-spinner').hidden = false;
                submit.querySelector('.btn-label').textContent = 'Creating account…';
            });
        })();
    </script>
</body>
</html>
