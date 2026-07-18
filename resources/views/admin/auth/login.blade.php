<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — الذاكرة والمعرفة للدراسات</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #1a2744;
            --blue: #2b4596;
            --blue-deep: #1e3270;
            --orange: #e9640a;
            --sand: #f3efe6;
            --paper: #ffffff;
            --muted: #6a778d;
            --line: rgba(43, 69, 150, 0.14);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Cairo', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(ellipse 80% 50% at 100% 0%, rgba(233, 100, 10, 0.16), transparent 55%),
                radial-gradient(ellipse 70% 45% at 0% 100%, rgba(43, 69, 150, 0.18), transparent 50%),
                linear-gradient(160deg, #f7f4ee 0%, #eef2f8 45%, #e8edf5 100%);
            display: grid;
            place-items: center;
            padding: 1.5rem;
        }

        .login-shell {
            width: min(960px, 100%);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 28px 60px rgba(26, 39, 68, 0.12);
            animation: rise 0.55s ease both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-brand {
            position: relative;
            padding: 2.75rem 2.4rem;
            background:
                linear-gradient(155deg, var(--blue-deep) 0%, var(--blue) 58%, #3a5cb0 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 520px;
            overflow: hidden;
        }

        .login-brand::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.12), transparent 36%),
                radial-gradient(circle at 85% 75%, rgba(233, 100, 10, 0.28), transparent 40%);
            pointer-events: none;
        }

        .login-brand > * { position: relative; z-index: 1; }

        .login-brand__mark {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.22);
            display: grid;
            place-items: center;
            overflow: hidden;
            margin-bottom: 1.75rem;
        }

        .login-brand__mark img {
            width: 88%;
            height: auto;
            display: block;
        }

        .login-brand h1 {
            margin: 0 0 0.75rem;
            font-size: clamp(1.55rem, 2.4vw, 2rem);
            line-height: 1.35;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .login-brand p {
            margin: 0;
            max-width: 28ch;
            font-size: 1rem;
            line-height: 1.7;
            color: rgba(255,255,255,0.86);
        }

        .login-brand__foot {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.78);
        }

        .login-brand__foot span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--orange);
            box-shadow: 0 0 0 4px rgba(233, 100, 10, 0.22);
        }

        .login-panel {
            padding: 2.75rem 2.35rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background:
                linear-gradient(180deg, rgba(243, 239, 230, 0.35), transparent 40%),
                #fff;
        }

        .login-panel__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--blue);
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 0.55rem;
        }

        .login-panel h2 {
            margin: 0 0 0.4rem;
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--ink);
        }

        .login-panel__hint {
            margin: 0 0 1.6rem;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .login-alert {
            background: #fff1f0;
            color: #9b1c1c;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 0.8rem 0.95rem;
            margin-bottom: 1.1rem;
            font-size: 0.9rem;
        }

        .field {
            margin-bottom: 1rem;
        }

        .field label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--ink);
        }

        .field-control {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            border: 1px solid var(--line);
            background: #fbfcfe;
            border-radius: 12px;
            padding: 0.15rem 0.85rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .field-control:focus-within {
            border-color: rgba(43, 69, 150, 0.45);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(43, 69, 150, 0.08);
        }

        .field-control i {
            color: var(--blue);
            opacity: 0.75;
            width: 1.1rem;
            text-align: center;
        }

        .field-control input {
            flex: 1;
            border: 0;
            outline: 0;
            background: transparent;
            padding: 0.85rem 0;
            font: inherit;
            color: var(--ink);
        }

        .field-control input::placeholder {
            color: #9aa6b8;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.35rem 0 1.35rem;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .remember input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--blue);
        }

        .btn-login {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 0.95rem 1.2rem;
            font: inherit;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-deep) 100%);
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            box-shadow: 0 12px 24px rgba(43, 69, 150, 0.22);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 16px 28px rgba(43, 69, 150, 0.28);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-foot {
            margin-top: 1.4rem;
            text-align: center;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .login-foot a {
            color: var(--orange);
            text-decoration: none;
            font-weight: 700;
        }

        @media (max-width: 820px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-brand {
                min-height: auto;
                padding: 2rem 1.6rem 1.6rem;
            }

            .login-brand p {
                max-width: none;
            }

            .login-panel {
                padding: 1.8rem 1.4rem 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <aside class="login-brand">
            <div>
                <div class="login-brand__mark">
                    <img src="{{ asset('images/logo-arabic-header_mks.png') }}" alt="الذاكرة والمعرفة للدراسات">
                </div>
                <h1>الذاكرة والمعرفة للدراسات</h1>
                <p>منصة معرفية لإدارة المحتوى والوثائق والإصدارات من مكان واحد بواجهة آمنة وواضحة.</p>
            </div>
            <div class="login-brand__foot">

            </div>
        </aside>

        <section class="login-panel">
            <div class="login-panel__eyebrow">
                <i class="fas fa-shield-halved"></i>
                لوحة التحكم
            </div>
            <h2>تسجيل الدخول</h2>
            <p class="login-panel__hint">يمكنك الدخول باسم المستخدم أو البريد الإلكتروني.</p>

            @if ($errors->any())
                <div class="login-alert" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" novalidate>
                @csrf

                <div class="field">
                    <label for="login">اسم المستخدم أو البريد الإلكتروني</label>
                    <div class="field-control">
                        <i class="fas fa-user"></i>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            value="{{ old('login') }}"
                            placeholder="مثال: admin أو name@email.com"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="field">
                    <label for="password">كلمة المرور</label>
                    <div class="field-control">
                        <i class="fas fa-lock"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="أدخل كلمة المرور"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    تذكرني على هذا الجهاز
                </label>

                <button type="submit" class="btn-login">
                    دخول إلى لوحة التحكم
                </button>
            </form>

            <div class="login-foot">
                © {{ date('Y') }} الذاكرة والمعرفة للدراسات
            </div>
        </section>
    </div>
</body>
</html>
