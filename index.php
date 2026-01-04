<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alfresco Rental Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="landing-wrapper">
        <!-- Left Side: Building Info -->
        <div class="landing-left d-none d-lg-flex">
            <div class="text-center floating-card">
                <i class="bi bi-building fs-1 mb-3"></i>
                <h1 class="display-3 fw-bold mb-2">A&J Building Inc.</h1>
                <p class="fs-4 white-50">Gatas, Pagadian City</p>
                <div class="mt-5 d-flex gap-4 justify-content-center">
                    <div class="text-center">
                        <i class="bi bi-shop fs-3"></i>
                        <p class="small mt-2">Food Stalls</p>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-mic fs-3"></i>
                        <p class="small mt-2">Karaoke</p>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-heart-pulse fs-3"></i>
                        <p class="small mt-2">Fitness/Gym</p>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-handbag fs-3"></i>
                        <p class="small mt-2">Fashion</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Auth Forms -->
        <div class="landing-right">
            <div class="auth-center-wrapper">
                <div class="auth-box">
                    <div class="text-center mb-3 mb-lg-5">
                        <img src="assets/logo.png" alt="Alfresco Logo" class="mb-2 mb-lg-3" style="width: 60px; height: auto;" width="60" id="authLogo">
                        <h2 class="fw-bold text-primary mb-0 h4 h2-lg">A&J Building Inc.</h2>
                        <p class="text-muted small">Alfresco Rental Management System</p>
                    </div>

                    <!-- Login Form -->
                    <div id="loginSection" class="auth-section">
                        <div class="mb-3 mb-lg-4">
                            <h3 class="fw-bold mb-1 h4 h3-lg">Welcome Back</h3>
                            <p class="text-muted small">Log in to manage your rental operations.</p>
                        </div>

                        <form id="loginForm">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0" name="username" required placeholder="Enter username">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0" name="password" id="loginPassword" required placeholder="Enter password">
                                    <button class="btn btn-toggle-pass" type="button" onclick="togglePassword('loginPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 py-lg-3 fw-bold mb-3 shadow-sm rounded-3">
                                Sign In <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                            <div id="loginAlert" class="alert d-none py-2 small rounded-3"></div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted small">New occupant? <a href="#" onclick="toggleAuth('register')" class="text-primary fw-bold text-decoration-none">Create an account</a></p>
                        </div>
                    </div>

                    <!-- Register Form -->
                    <div id="registerSection" class="auth-section d-none">
                        <div class="mb-2 mb-lg-4">
                            <h3 class="fw-bold mb-1 h4 h3-lg">Get Started</h3>
                            <p class="text-muted small">Create your account to start your rental journey.</p>
                        </div>

                        <form id="registerForm">
                            <div class="mb-2 mb-lg-3">
                                <label class="form-label small fw-semibold">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" class="form-control border-start-0" name="full_name" required placeholder="Juan Dela Cruz">
                                </div>
                            </div>
                            <div class="mb-2 mb-lg-3">
                                <label class="form-label small fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-at"></i></span>
                                    <input type="text" class="form-control border-start-0" name="username" required placeholder="choose_username">
                                </div>
                            </div>
                            <div class="mb-2 mb-lg-3">
                                <label class="form-label small fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0" name="password" id="registerPassword" required placeholder="••••••••">
                                    <button class="btn btn-toggle-pass" type="button" onclick="togglePassword('registerPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3 mb-lg-4">
                                <label class="form-label small fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0" name="confirm_password" id="confirmPassword" required placeholder="Repeat password">
                                    <button class="btn btn-toggle-pass" type="button" onclick="togglePassword('confirmPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 py-lg-3 fw-bold mb-2 mb-lg-3 shadow-sm rounded-3">
                                Create Account <i class="bi bi-check2-circle ms-2"></i>
                            </button>
                            <div id="registerAlert" class="alert d-none py-2 small rounded-3"></div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted small">Already a member? <a href="#" onclick="toggleAuth('login')" class="text-primary fw-bold text-decoration-none">Log in here</a></p>
                        </div>
                    </div>
                    <div class="landing-footer text-center mt-4">
                        <p class="text-muted small fw-medium mb-0">Alfresco &copy; 2025 | A&J Building Inc.</p>
                        <p class="text-muted x-small">Gatas, Pagadian City, Zamboanga del Sur</p>
                    </div>
                </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleAuth(section) {
            $('.auth-section').addClass('d-none');
            $('#' + section + 'Section').removeClass('d-none');
            $('.alert').addClass('d-none');
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        $(document).ready(function() {
            // Login AJAX
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button');
                const alert = $('#loginAlert');
                
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Checking...');
                alert.addClass('d-none');

                $.ajax({
                    url: 'auth/login_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                alert.removeClass('d-none alert-danger').addClass('alert-success').text('Login successful! Redirecting...');
                                setTimeout(() => window.location.href = res.redirect, 1000);
                            } else {
                                alert.removeClass('d-none alert-success').addClass('alert-danger').text(res.message);
                                btn.prop('disabled', false).html('Login Now <i class="bi bi-arrow-right ms-2"></i>');
                            }
                        } catch(e) {
                            alert.removeClass('d-none alert-success').addClass('alert-danger').text('Invalid server response.');
                            btn.prop('disabled', false).html('Login Now <i class="bi bi-arrow-right ms-2"></i>');
                        }
                    },
                    error: function() {
                        alert.removeClass('d-none alert-success').addClass('alert-danger').text('Server error.');
                        btn.prop('disabled', false).html('Login Now <i class="bi bi-arrow-right ms-2"></i>');
                    }
                });
            });

            // Register AJAX
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button');
                const alert = $('#registerAlert');
                
                const pass = $('#registerPassword').val();
                const conf = $('#confirmPassword').val();

                if (pass !== conf) {
                    alert.removeClass('d-none alert-success').addClass('alert-danger').text('Passwords do not match.');
                    return;
                }

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Creating...');
                alert.addClass('d-none');

                $.ajax({
                    url: 'auth/register_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                alert.removeClass('d-none alert-danger').addClass('alert-success').text(res.message);
                                $('#registerForm')[0].reset();
                                setTimeout(() => toggleAuth('login'), 2000);
                            } else {
                                alert.removeClass('d-none alert-success').addClass('alert-danger').text(res.message);
                            }
                        } catch(e) {
                            alert.removeClass('d-none alert-success').addClass('alert-danger').text('Invalid server response.');
                        }
                    },
                    error: function() {
                        alert.removeClass('d-none alert-success').addClass('alert-danger').text('Server error.');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('Register Now <i class="bi bi-check2-circle ms-2"></i>');
                    }
                });
            });
        });
    </script>
</body>
</html>
