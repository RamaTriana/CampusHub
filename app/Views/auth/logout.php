<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logout | CampusHub</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="/public/assets/css/login.css">
</head>
<body>
    <main class="login-page">
        <section class="login-right">
            <div class="login-card">
                <div class="login-header">
                    <h2>Logout dari CampusHub?</h2>
                    <p>Konfirmasi untuk mengakhiri sesi Anda.</p>
                </div>
                <form action="/logout" method="POST">
                    <?= \CampusHub\Core\View::csrfField() ?>
                    <button type="submit" class="btn btn-primary btn-block">Logout</button>
                </form>
                <div class="register-link">
                    <a href="/">Kembali ke CampusHub</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
