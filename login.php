
<!-- <!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | CampusHub</title>

    
    <link rel="stylesheet" href="assets/css/style.css">

    
    <link rel="stylesheet" href="assets/css/login.css">

    
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body> -->

    <?php

    require_once 'config/config.php';
    require_once 'config/session.php';
    require_once 'config/functions.php';
    require_once 'config/flash.php';

    if (isLoggedIn()) {

        redirectByRole($_SESSION['role']);

    }

    $pageTitle = "Login | CampusHub";

    $pageCSS = "login.css";

    include 'includes/head.php';

    ?>

    <!-- Background Decoration -->

    <div class="bg-circle bg-circle-1"></div>
    <div class="bg-circle bg-circle-2"></div>
    <div class="bg-circle bg-circle-3"></div>

    <!-- Login Container -->

    <section class="login-page">

        <!-- LEFT -->

        <div class="login-left">

            <div class="brand">

                <div class="brand-logo">

                    <img src="assets/img/logo.png" alt="CampusHub Logo">

                </div>

                <div>

                    <h1>CampusHub</h1>

                    <p>Campus Organization & Event Management System</p>

                </div>

            </div>

            <div class="hero-content">

                <h2>
                    Manage Campus Events Easily.
                </h2>

                <p>
                    Atur organisasi, publikasikan acara, kelola peserta, 
                    kehadiran, dokumentasi, dan sertifikat 
                    dalam satu sistem terintegrasi.
                </p>

            </div>

            <div class="hero-image">

                <img src="assets/img/login-illustration.svg"
                    alt="CampusHub Illustration">

            </div>

        </div>

        <!-- RIGHT -->

        <div class="login-right">

            <div class="login-card">

                <div class="login-header">

                    <h2>Welcome Back 👋</h2>

                    <p>
                        Login to continue to CampusHub
                    </p>

                </div>
                <?php include 'includes/alert.php'; ?>

                <form action="login_process.php" method="POST" autocomplete="off">

                    <!-- Username -->

                    <div class="form-group">

                        <label class="form-label">

                            Username

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            placeholder="Enter your username"
                            name="username">

                    </div>

                    <!-- Password -->

                    <div class="form-group">

                        <label class="form-label">

                            Password

                        </label>

                        <div class="password-group">

                            <input
                                type="password"
                                class="form-control"
                                placeholder="Enter your password"
                                id="password"
                                name="password">

                            <button
                                type="button"
                                class="toggle-password">

                                <i class="fa-regular fa-eye"></i>

                            </button>

                        </div>

                    </div>

                    <!-- Remember -->

                    <div class="login-options">

                        <label>

                            <input type="checkbox" name="remember">

                            Remember Me

                        </label>

                        <a href="#">

                            Forgot Password?

                        </a>

                    </div>

                    <!-- Button -->

                    <button
                        type="submit"
                        class="btn btn-primary btn-block">

                        <i class="fa-solid fa-right-to-bracket"></i>

                        Login

                    </button>

                </form>

                <div class="login-divider">

                    <span>

                        OR

                    </span>

                </div>

                <div class="register-link">

                    Don't have an account?

                    <a href="register.php">

                        Register Now

                    </a>

                </div>

            </div>

            <div class="login-footer">

                © 2026 CampusHub

            </div>

        </div>

    </section>

    <!-- <script src="assets/js/login.js"></script> -->

    <?php

    $pageJS = "login.js";

    include 'includes/scripts.php';

    ?>

<!-- </body>

</html> -->
