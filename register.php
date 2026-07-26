<?php

require_once 'config/config.php';
require_once 'config/session.php';
require_once 'config/functions.php';
require_once 'config/flash.php';

if (isLoggedIn()) {
    redirectByRole($_SESSION['role']);
}

$pageTitle = "Register | " . APP_NAME;
$pageCSS = "register.css";

include 'includes/head.php';

?>

<div class="auth">

    <div class="register-left">

            <div>

                <div class="brand">

                    <div class="brand-logo">

                        <img src="<?= BASE_URL; ?>/assets/img/logo.png">

                    </div>

                    <div>

                        <h1>CampusHub</h1>

                        <p>Connect. Collaborate. Celebrate.</p>

                    </div>

                </div>

                <div class="register-step">

                    Account Setup

                </div>

                <div class="hero-text">

                    <h2>

                        Join Your Campus Community

                    </h2>

                    <p>

                        Kelola organisasi, ikuti event,
                        kumpulkan sertifikat digital,
                        dan bangun relasi kampus
                        dalam satu platform modern.

                    </p>

                </div>

            </div>

            <div class="hero-image">

                <img src="<?= BASE_URL; ?>/assets/img/register-illustration.svg">

            </div>

        </div>

    <div class="register-card">

        <!-- ================= LEFT ================= -->



        <!-- ================= RIGHT ================= -->

        <div class="register-form">

            <div class="card-header">

                <h2>Create Account</h2>

                <p>

                    Silakan lengkapi data di bawah ini.

                </p>

            </div>

            <?php include 'includes/alert.php'; ?>

            <form id="registerForm"
                action="register_process.php"
                method="POST"
                enctype="multipart/form-data"
                autocomplete="off">

                <div class="form-row">

                    <div class="form-group">

                        <label>Nama Lengkap</label>

                        <input
                            type="text"
                            name="nama"
                            id="nama"
                            class="form-control"
                            placeholder="Nama lengkap">

                    </div>

                    <div class="form-group">

                        <label>Username</label>

                        <input
                            type="text"
                            name="username"
                            id="username"
                            class="form-control"
                            placeholder="Username">

                    </div>

                </div>

                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="nama@student.ac.id">

                </div>

                <div class="form-row">

                    <!-- <div class="form-group">

                        <label>Password</label>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control">

                    </div> -->

                    <div class="password-wrapper">
                        <label>Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control">

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password">

                            <i class="fa-regular fa-eye"></i>

                        </button>

                        <div class="password-strength">

                            <div class="strength-bar">

                                <div
                                    id="strengthProgress"
                                    class="strength-progress">

                                </div>

                            </div>

                            <div
                                id="strengthLabel"
                                class="strength-label">

                                Password belum diisi

                            </div>

                        </div>

                        <div class="password-checklist">

                            <div id="ruleLength">
                                ✖ Minimal 8 karakter
                            </div>

                            <div id="ruleUpper">
                                ✖ Huruf Besar
                            </div>

                            <div id="ruleLower">
                                ✖ Huruf Kecil
                            </div>

                            <div id="ruleNumber">
                                ✖ Angka
                            </div>

                            <div id="ruleSymbol">
                                ✖ Simbol
                            </div>

                        </div>

                    </div>

                    <div class="password-wrapper">

                        <label>Confirm Password</label>

                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            class="form-control">

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="confirm_password">

                            <i class="fa-regular fa-eye"></i>

                        </button>
                        <small
                            id="confirmMessage"
                            class="confirm-message">
                        </small>

                    </div>

                </div>

                <div class="form-group upload-group">

                    <label class="upload-label">Foto Profil</label>

                    <label for="foto"
                        class="upload-area"
                        id="uploadArea">

                        <!-- Placeholder -->
                        <div class="upload-content" id="uploadContent">

                            <div class="upload-icon">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>

                            <div class="upload-text">
                                Upload Foto Profil
                            </div>

                            <div class="upload-info">
                                JPG, PNG • Maks. 2 MB
                            </div>

                        </div>

                        <!-- Preview -->
                        <div class="upload-preview" id="uploadPreview">

                            <img
                                id="previewImage"
                                src=""
                                alt="Preview">

                            <div class="preview-info">

                                <strong id="fileName"></strong>

                                <small id="fileSize"></small>

                            </div>

                            <button
                                type="button"
                                class="remove-photo"
                                id="removePhoto">

                                <i class="fa-solid fa-trash"></i>

                            </button>

                        </div>

                    </label>

                    <input
                        type="file"
                        id="foto"
                        name="foto"
                        accept=".jpg,.jpeg,.png"
                        hidden>

                </div>

                <button type="submit" class="btn btn-primary w-100">

                    <i class="fa-solid fa-user-plus"></i>

                    Create Account

                </button>

            </form>

            <div class="auth-footer">

                Sudah punya akun?

                <a href="login.php">

                    Login

                </a>

            </div>

        </div>

    </div>

</div>

<?php

$pageJS = "register.js";

include 'includes/scripts.php';

?>