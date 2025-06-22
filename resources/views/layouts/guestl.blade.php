{{-- File: resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Login') - {{ config('app.name', 'Jewelry Store') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --gold-color: #ffd700;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="50" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="30" r="1" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-color), #667eea, var(--primary-color));
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .auth-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2d3a87 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: sweep 4s ease-in-out infinite;
        }

        @keyframes sweep {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        }

        .auth-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            position: relative;
            z-index: 2;
        }

        .auth-header .subtitle {
            opacity: 0.95;
            font-size: 1rem;
            position: relative;
            z-index: 2;
        }

        .auth-body {
            padding: 2.5rem;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e3e6f0;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.15);
            background: white;
            transform: translateY(-1px);
        }

        .form-control:hover {
            border-color: rgba(78, 115, 223, 0.5);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2d3a87 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(78, 115, 223, 0.4);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            display: block;
            font-size: 0.95rem;
        }

        .auth-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(227, 230, 240, 0.5);
        }

        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .auth-links a:hover {
            color: #2d3a87;
        }

        .auth-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .auth-links a:hover::after {
            width: 100%;
        }

        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(28, 200, 138, 0.15), rgba(28, 200, 138, 0.05));
            color: #0a3622;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(231, 74, 59, 0.15), rgba(231, 74, 59, 0.05));
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(246, 194, 62, 0.15), rgba(246, 194, 62, 0.05));
            color: #856404;
            border-left: 4px solid var(--warning-color);
        }

        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .auth-brand i {
            font-size: 2.5rem;
            margin-right: 0.75rem;
            color: var(--gold-color);
            animation: sparkle 2s ease-in-out infinite;
        }

        @keyframes sparkle {
            0%, 100% { transform: scale(1) rotate(0deg); filter: brightness(1); }
            50% { transform: scale(1.1) rotate(5deg); filter: brightness(1.2); }
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #e3e6f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
            background: white;
            color: var(--primary-color);
        }

        .input-group:focus-within .form-control {
            border-color: var(--primary-color);
        }

        .form-check-input {
            border-radius: 6px;
            border: 2px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.15);
        }

        .form-check-label {
            font-weight: 500;
            color: var(--dark-color);
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-card {
                margin: 1rem;
                border-radius: 20px;
            }
            
            .auth-header,
            .auth-body {
                padding: 2rem 1.5rem;
            }

            .auth-header h1 {
                font-size: 1.75rem;
            }

            .form-control {
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
            }

            .btn-primary {
                padding: 0.75rem 2rem;
                font-size: 0.9rem;
            }
        }

        /* Add some floating elements */
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float-random 6s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 40px;
            height: 40px;
            top: 20%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 15%;
            left: 15%;
            animation-delay: 4s;
        }

        @keyframes float-random {
            0%, 100% { transform: translateY(0) translateX(0) rotate(0deg); }
            25% { transform: translateY(-20px) translateX(10px) rotate(90deg); }
            50% { transform: translateY(-10px) translateX(-15px) rotate(180deg); }
            75% { transform: translateY(-25px) translateX(5px) rotate(270deg); }
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Floating background shapes -->
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-3 col-md-6 col-sm-3">
                <div class="auth-card">
                    <!-- Auth Header -->
                    <div class="auth-header">
                        <div class="auth-brand">
                            <i class="fas fa-gem"></i>
                            <h1>{{ config('app.name', 'Jewelry Store') }}</h1>
                        </div>
                        <p class="subtitle">@yield('subtitle', 'Welcome back! Please sign in to your account.')</p>
                    </div>

                    <!-- Auth Body -->
                    <div class="auth-body">
                        <!-- Session Status Messages -->
                        @if(session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Validation Errors -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Main Content -->
                        @yield('content')

                        <!-- Auth Links -->
                        <div class="auth-links">
                            @yield('auth-links')
                        </div>
                    </div>
                </div>

                <!-- Footer Links -->
                <div class="text-center mt-4">
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (alert.querySelector('.btn-close')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);

            // Form validation feedback
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Please wait...';
                        
                        // Re-enable after 3 seconds as fallback
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
                        }, 3000);
                    }
                });
            });

            // Add hover effects to floating shapes
            const shapes = document.querySelectorAll('.floating-shape');
            shapes.forEach(shape => {
                shape.addEventListener('mouseenter', function() {
                    this.style.animationPlayState = 'paused';
                    this.style.transform = 'scale(1.2)';
                });
                
                shape.addEventListener('mouseleave', function() {
                    this.style.animationPlayState = 'running';
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>