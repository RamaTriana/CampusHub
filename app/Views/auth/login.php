<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login | CampusHub</title>
    <link rel="icon" href="/public/assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="/public/assets/css/login.css">
</head>
<body>
    <div class="bg-circle bg-circle-1"></div>
    <div class="bg-circle bg-circle-2"></div>
    <div class="bg-circle bg-circle-3"></div>

    <section class="login-page">
        <div class="login-left">
            <div class="brand">
                <div class="brand-logo">
                    <img src="/public/assets/images/logo.png" alt="CampusHub Logo">
                </div>
                <div>
                    <h1>CampusHub</h1>
                    <p>Campus Organization &amp; Event Management System</p>
                </div>
            </div>

            <div class="hero-content">
                <h2>Manage Campus Events Easily.</h2>
                <p>
                    Atur organisasi, publikasikan acara, kelola peserta,
                    kehadiran, dokumentasi, dan sertifikat
                    dalam satu sistem terintegrasi.
                </p>
            </div>

            <div class="hero-image">
                <img src="/public/assets/images/login-illustration.svg" alt="CampusHub Illustration">
            </div>
        </div>

        <div class="login-right">
            <div class="login-card">
                <div class="login-header">
                    <h2>Welcome Back 👋</h2>
                    <p>Login to continue to CampusHub</p>
                </div>

                <?php if (is_array($flash ?? null)) : ?>
                    <div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>">
                        <?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?>
                    </div>
                <?php endif; ?>

                <form action="/login" method="POST" autocomplete="off">
                    <?= \CampusHub\Core\View::csrfField() ?>
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Enter your username"
                            name="username"
                            id="username"
                            autocomplete="username"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-group">
                            <input
                                type="password"
                                class="form-control"
                                placeholder="Enter your password"
                                id="password"
                                name="password"
                                autocomplete="current-password"
                                required>
                            <button type="button" class="toggle-password" aria-label="Tampilkan password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-options">
                        <label>
                            <input type="checkbox" name="remember">
                            Remember Me
                        </label>
                        <a href="#">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Login
                    </button>
                </form>

                <div class="login-divider"><span>OR</span></div>
                <div class="register-link">
                    Don't have an account?
                    <a href="/register">Register Now</a>
                </div>
            </div>

            <div class="login-footer">© 2026 CampusHub</div>
        </div>
    </section>

    <script src="/public/assets/js/login.js"></script>
</body>
</html>
