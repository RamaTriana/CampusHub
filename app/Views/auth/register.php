<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Register | CampusHub</title>
    <link rel="icon" href="/public/assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="/public/assets/css/register.css">
</head>
<body>
    <main class="auth">
        <section class="register-left">
            <div>
                <div class="brand">
                    <div class="brand-logo">
                        <img src="/public/assets/images/logo.png" alt="CampusHub Logo">
                    </div>
                    <div>
                        <h1>CampusHub</h1>
                        <p>Connect. Collaborate. Celebrate.</p>
                    </div>
                </div>

                <div class="register-step">Account Setup</div>
                <div class="hero-text">
                    <h2>Join Your Campus Community</h2>
                    <p>
                        Kelola organisasi, ikuti event,
                        kumpulkan sertifikat digital,
                        dan bangun relasi kampus
                        dalam satu platform modern.
                    </p>
                </div>
            </div>

            <div class="hero-image">
                <img src="/public/assets/images/register-illustration.svg" alt="CampusHub Illustration">
            </div>
        </section>

        <section class="register-card">
            <div class="register-form">
                <div class="card-header">
                    <h2>Create Account</h2>
                    <p>Silakan lengkapi data di bawah ini.</p>
                </div>

                <?php if (is_array($flash ?? null)) : ?>
                    <div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>">
                        <?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?>
                    </div>
                <?php endif; ?>

                <form id="registerForm" action="/register" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <?= \CampusHub\Core\View::csrfField() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input
                                type="text"
                                name="nama"
                                id="nama"
                                class="form-control"
                                placeholder="Nama lengkap"
                                maxlength="100"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input
                                type="text"
                                name="username"
                                id="username"
                                class="form-control"
                                placeholder="Username"
                                maxlength="50"
                                autocomplete="username"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="nama@student.ac.id"
                            maxlength="100"
                            autocomplete="email"
                            required>
                    </div>

                    <div class="form-row">
                        <div class="password-wrapper">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div id="strengthProgress" class="strength-progress"></div>
                                </div>
                                <div id="strengthLabel" class="strength-label">Password belum diisi</div>
                            </div>
                            <div class="password-checklist">
                                <div id="ruleLength">✖ Minimal 8 karakter</div>
                                <div id="ruleUpper">✖ Huruf Besar</div>
                                <div id="ruleLower">✖ Huruf Kecil</div>
                                <div id="ruleNumber">✖ Angka</div>
                                <div id="ruleSymbol">✖ Simbol</div>
                            </div>
                        </div>

                        <div class="password-wrapper">
                            <label for="confirm_password">Confirm Password</label>
                            <input
                                type="password"
                                name="confirm_password"
                                id="confirm_password"
                                class="form-control"
                                autocomplete="new-password"
                                required>
                            <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Tampilkan konfirmasi password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <small id="confirmMessage" class="confirm-message"></small>
                        </div>
                    </div>

                    <div class="form-group upload-group">
                        <label class="upload-label" for="foto">Foto Profil</label>
                        <label for="foto" class="upload-area" id="uploadArea">
                            <div class="upload-content" id="uploadContent">
                                <div class="upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                <div class="upload-text">Upload Foto Profil</div>
                                <div class="upload-info">JPG, PNG • Maks. 2 MB</div>
                            </div>
                            <div class="upload-preview" id="uploadPreview">
                                <img id="previewImage" src="" alt="Preview">
                                <div class="preview-info">
                                    <strong id="fileName"></strong>
                                    <small id="fileSize"></small>
                                </div>
                                <button type="button" class="remove-photo" id="removePhoto" aria-label="Hapus foto">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </label>
                        <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png" hidden>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-user-plus"></i>
                        Create Account
                    </button>
                </form>

                <div class="auth-footer">
                    Sudah punya akun?
                    <a href="/login">Login</a>
                </div>
            </div>
        </section>
    </main>

    <script src="/public/assets/js/register.js"></script>
</body>
</html>
