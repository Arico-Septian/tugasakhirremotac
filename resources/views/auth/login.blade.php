<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SmartAC Control System</title>

    <link href="/css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(rgba(10, 20, 80, 0.7), rgba(10, 20, 80, 0.9)),
                url('/images/wallpaper.jpeg') no-repeat center center fixed;
            background-size: cover;
            animation: gradientShift 8s ease infinite;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Animated Background Blobs */
        .blob {
            position: fixed;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 10s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(30px, -30px) scale(1.1);
            }
        }

        .blob-1 {
            background: radial-gradient(circle, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0));
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            animation-delay: 0s;
        }

        .blob-2 {
            background: radial-gradient(circle, rgba(139, 92, 246, 0.6), rgba(139, 92, 246, 0));
            bottom: -100px;
            left: -100px;
            width: 350px;
            height: 350px;
            animation-delay: 3s;
        }

        .blob-3 {
            background: radial-gradient(circle, rgba(59, 130, 246, 0.5), rgba(59, 130, 246, 0));
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 500px;
            animation-delay: 6s;
        }

        /* Particles */
        .particle {
            position: fixed;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            pointer-events: none;
            animation: particleFloat 15s infinite linear;
            z-index: 0;
        }

        .text-white\/70 {
            color: #94a3b8 !important;
        }

        .input-label {
            color: #cbd5f5;
            /* lebih soft */
        }
        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 0.5;
            }

            90% {
                opacity: 0.5;
            }

            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Glass Card */
        .glass-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* Input Fields */
        .input-group {
            position: relative;
            margin-bottom: 24px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .input-field {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            font-size: 15px;
            color: white;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .input-field:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .input-field:-webkit-autofill,
        .input-field:-webkit-autofill:focus {
            -webkit-text-fill-color: white;
            -webkit-box-shadow: 0 0 0px 1000px rgba(255, 255, 255, 0.15) inset;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Label */
        .input-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(37,99,235,0.3);
            font-size: 16px;
            font-weight: 700;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .login-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* Error Alert */
        .error-alert {
            background: rgba(239, 68, 68, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .error-alert i {
            color: #f87171;
            font-size: 18px;
        }

        .error-alert p {
            color: #fecaca;
            font-size: 13px;
            flex: 1;
        }

        /* Icon Animation */
        .logo-icon {
            animation: pulse 2s ease infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.9;
            }
        }

        /* Footer Links */
        .footer-links {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .glass-card {
                margin: 16px;
                border-radius: 24px;
            }

            .input-field {
                padding: 12px 12px 12px 44px;
                font-size: 14px;
            }

            .login-btn {
                padding: 12px;
                font-size: 15px;
            }
        }

        /* Loading State */
        .login-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Floating Shapes */
        .floating-shape {
            position: fixed;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>

<body>

    <!-- Animated Background Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- Floating Particles -->
    <div id="particles-container"></div>

    <!-- Login Card -->
    <div class="w-full max-w-md p-4 relative z-10">
        <div class="glass-card p-8 md:p-10">

            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-700/50 mb-4 logo-icon">
                    <i class="fa-solid fa-snowflake text-4xl text-white"></i>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">
                    Welcome Back
                </h2>
                <p class="text-white/70 text-sm">
                    Sign in to control your AC system
                </p>
            </div>

            <!-- Error Alert -->
            @if (session('error'))
                <div class="error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Error from validation -->
            @if ($errors->any())
                <div class="error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="/login" id="loginForm">
                @csrf

                <!-- Username Field -->
                <div class="input-group">
                    <label class="input-label">
                        <i class="fa-regular fa-user mr-1"></i> USERNAME
                    </label>
                    <div class="relative">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input type="text" name="name" required autofocus autocomplete="username"
                            placeholder="Enter your username" class="input-field" value="{{ old('name') }}">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="input-group">
                    <label class="input-label">
                        <i class="fa-regular fa-lock mr-1"></i> PASSWORD
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-key input-icon"></i>
                        <input type="password" name="password" required autocomplete="current-password"
                            placeholder="Enter your password" class="input-field" id="password">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white/50 hover:text-white/80 transition">
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="login-btn" id="loginBtn">
                    <span>Sign In</span>
                </button>

            </form>

            <!-- Footer Links -->
            <div class="footer-links">
                <p class="text-white/50 text-xs">
                    <i class="fa-regular fa-copyright"></i> 2026 SmartAC Control System
                </p>
            </div>

        </div>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');

            if (password.type === 'password') {
                password.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                password.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        }

        // Form Submit Loading State
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.querySelector('span').style.opacity = '0';
        });

        // Create Floating Particles
        function createParticles() {
            const container = document.getElementById('particles-container');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 4 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = Math.random() * 10 + 10 + 's';
                particle.style.animationDelay = Math.random() * 10 + 's';
                container.appendChild(particle);
            }
        }

        // Add floating shapes
        function createFloatingShapes() {
            const shapeCount = 15;
            for (let i = 0; i < shapeCount; i++) {
                const shape = document.createElement('div');
                shape.className = 'floating-shape';
                const size = Math.random() * 150 + 50;
                shape.style.width = size + 'px';
                shape.style.height = size + 'px';
                shape.style.left = Math.random() * 100 + '%';
                shape.style.top = Math.random() * 100 + '%';
                shape.style.animation = `float ${Math.random() * 20 + 15}s ease-in-out infinite`;
                document.body.appendChild(shape);
            }
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            createFloatingShapes();
        });
    </script>

</body>

</html>
