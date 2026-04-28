<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Personel</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: #fff;
        }

        body {
            background: linear-gradient(135deg, #5267f5, #6a5dfc, #b83bdc);
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .login-wrapper {
            flex: 1;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 13px 3px;
        }

        .login-area {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1100px;
            max-width: 100%;
        }

        .character {
            width: 400px;
            max-width: 45%;
            margin-right: -105px;
            z-index: 3;
            animation: floatCharacter 4s ease-in-out infinite;
            filter: drop-shadow(0 25px 35px rgba(0,0,0,0.30));
        }

        @keyframes floatCharacter {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0);
            }
        }

        .login-box {
            width: 460px;
            z-index: 2;
            background: rgba(255, 255, 255, 0.14);
            padding: 45px 35px 35px;
            border-radius: 28px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
        }

        .login-box h1 {
            font-size: 30px;
            line-height: 1.1;
            margin: 0 0 10px;
            font-weight: 800;
        }

        .login-box p {
            font-size: 17px;
            margin: 0 0 25px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }

        .tab-btn {
            flex: 1;
            padding: 13px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            transition: 0.25s;
        }

        .tab-btn.active {
            background: #ffffff;
            color: #5267f5;
        }

        .tab-btn:not(.active) {
            background: rgba(255,255,255,0.25);
            color: #ffffff;
        }

        .tab-btn:hover {
            transform: translateY(-1px);
        }

        .form-card {
            display: none;
        }

        .form-card.active {
            display: block;
            animation: fadeIn 0.35s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        input {
            width: 100%;
            padding: 17px 18px;
            border-radius: 12px;
            border: none;
            outline: none;
            margin-bottom: 15px;
            font-size: 15px;
            background: #ffffff;
            color: #333333;
        }

        input::placeholder {
            color: #777777;
        }

        .login-btn {
            width: 100%;
            padding: 17px;
            border: none;
            border-radius: 12px;
            background: #ffffff;
            color: #5267f5;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        /* 🔵 HOVER EFFECT */
        .login-btn:hover {
            background: #1e3a8a;   /* dark blue */
            color: #ffffff;
            transform: translateY(-1px);
        }

        .error {
            background: #ffffff;
            color: #d60000;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: 600;
        }

        .footer {
            width: 100%;
            background: linear-gradient(270deg, #3650d8, #7b61ff, #c13584);
            background-size: 600% 600%;
            animation: gradientMove 8s ease infinite;
            color: #ffffff;
            text-align: center;
            padding: 14px 10px;
            font-size: 14px;
            line-height: 1.5;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.18);
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .footer strong {
            font-size: 15px;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0%;
            }

            50% {
                background-position: 100%;
            }

            100% {
                background-position: 0%;
            }
        }

        @media (max-width: 900px) {
            .login-wrapper {
                padding: 30px 20px;
            }

            .login-area {
                flex-direction: column;
            }

            .character {
                width: 290px;
                max-width: 90%;
                margin-right: 0;
                margin-bottom: -65px;
            }

            .login-box {
                width: 92%;
                max-width: 460px;
                padding-top: 75px;
            }

            .login-box h1 {
                font-size: 32px;
            }

            .footer {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="page">

        <main class="login-wrapper">
            <div class="login-area">

                <img
                    src="{{ asset('assets/img/illustrations/login_image_2.png') }}"
                    class="character"
                    alt="Login Sistem Personel"
                >

                <section class="login-box">
                    <h1>Sistem Personel</h1>
                    <p>Sila pilih jenis login</p>

                    @if ($errors->any())
                        <div class="error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="tabs">
                        <button type="button" class="tab-btn active" onclick="showForm('staff', this)">
                            Pengguna
                        </button>

                        <button type="button" class="tab-btn" onclick="showForm('admin', this)">
                            Pentadbir
                        </button>
                    </div>

                    <form method="POST" action="{{ route('login.process') }}" id="staff" class="form-card active">
                        @csrf

                        <input type="hidden" name="login_type" value="staff">

                        <input
                            type="text"
                            name="mykad"
                            placeholder="No Kad Pengenalan"
                            maxlength="12"
                            pattern="[0-9]{12}"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            required
                        >

                        <button type="submit" class="login-btn">
                            Login Pengguna
                        </button>
                    </form>

                    <form method="POST" action="{{ route('login.process') }}" id="admin" class="form-card">
                        @csrf

                        <input type="hidden" name="login_type" value="admin">

                        <input
                            type="text"
                            name="nokp"
                            placeholder="No Kad Pengenalan"
                            maxlength="12"
                            pattern="[0-9]{12}"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            required
                        >

                        <input
                            type="password"
                            name="password"
                            placeholder="Password"
                            required
                        >

                        <button type="submit" class="login-btn">
                            Login Pentadbir
                        </button>
                    </form>

                </section>

            </div>
        </main>

        <footer class="footer">
            <strong>© 2026 - Kementerian Perpaduan Negara</strong><br>
            Paparan terbaik menggunakan pelayar internet <em>Google Chrome</em> atau <em>Mozilla Firefox</em> terkini dengan resolusi 1024 x 768 px
        </footer>

    </div>

    <script>
        function showForm(type, button) {
            document.querySelectorAll('.form-card').forEach(function (form) {
                form.classList.remove('active');
            });

            document.querySelectorAll('.tab-btn').forEach(function (btn) {
                btn.classList.remove('active');
            });

            document.getElementById(type).classList.add('active');
            button.classList.add('active');
        }
    </script>
</body>
</html>
