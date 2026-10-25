<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= \CampusHub\Core\View::escape($title ?? 'CampusHub') ?></title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
    <main class="container" style="max-width: 720px; margin: 4rem auto; padding: 2rem;">
        <h1><?= \CampusHub\Core\View::escape($title ?? 'CampusHub') ?></h1>
        <p>Fondasi routing MVC aktif. Halaman PHP Nativ.</p>
        <nav aria-label="Autentikasi">
            <a href="/login">Login</a>
            <span aria-hidden="true"> · </span>
            <a href="/register">Register</a>
        </nav>
    </main>
</body>
</html>
