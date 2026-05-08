<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart AC Control System | Premium IoT Climate Control</title>

    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* ===== CUSTOM STYLES ===== */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* Premium Gradient Background */
        .premium-bg {
            background:
                linear-gradient(rgba(10, 20, 80, 0.7), rgba(10, 20, 80, 0.9)),
                url('/images/wallpaper.jpeg') no-repeat center center fixed;
            position: relative;
        }
        .premium-bg::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.15), transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(30, 64, 175, 0.2), transparent 50%);
            z-index: 0;
        }
        /* Animated Gradient Border */
        .gradient-border {
            position: relative;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border-radius: 24px;
        }

        .gradient-border::before {
            content: '';
            position: absolute;
            inset: -1px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb, #4facfe);
            border-radius: 24px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .gradient-border:hover::before {
            opacity: 1;
        }

        /* Glass Morphism Premium */
        .glass-premium {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
      /* Animated Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradientShift 3s ease infinite;
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

        /* Floating Animation */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .float {
            animation: float 6s ease-in-out infinite;
        }

        .float-delayed {
            animation: float 6s ease-in-out infinite 2s;
        }

        /* Glow Effect */
        .glow {
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.5);
            transition: box-shadow 0.3s ease;
        }

        .glow:hover {
            box-shadow: 0 0 50px rgba(102, 126, 234, 0.8);
        }

        /* Button Hover Effect */
        .btn-premium {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-premium::before {
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

        .btn-premium:hover::before {
            width: 300px;
            height: 300px;
        }

        /* Scroll Reveal Animation */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Custom Cursor */
        .custom-cursor {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(102, 126, 234, 0.8);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s ease;
            backdrop-filter: blur(2px);
        }

        .custom-cursor-dot {
            width: 4px;
            height: 4px;
            background: #667eea;
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
        }

        /* Feature Card Hover */
        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
        }

        /* Stats Counter */
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body class="premium-bg text-white relative">

    <!-- Custom Cursor -->
    <div class="custom-cursor hidden md:block"></div>
    <div class="custom-cursor-dot hidden md:block"></div>

    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm -z-10"></div>

    <!-- Particles Background -->
    <div id="tsparticles" class="fixed top-0 left-0 w-full h-full -z-10"></div>

    <!-- Animated Background Blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-5">
        <div class="absolute top-20 left-10 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div
            class="absolute bottom-20 right-10 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl animate-pulse delay-1000">
        </div>
        <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-blue-800/10 rounded-full blur-3xl animate-pulse delay-2000">
        </div>
    </div>

    <!-- ==================== NAVBAR ==================== -->
    <nav class="fixed top-0 left-0 w-full z-50 glass-premium backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-snowflake text-2xl text-blue-400"></i>
                <span class="text-xl font-bold gradient-text">SmartAC</span>
            </div>
            <div class="hidden md:flex gap-8">
                <a href="#home" class="hover:text-blue-400 transition">Home</a>
                <a href="#features" class="hover:text-blue-400 transition">Features</a>
                <a href="#about" class="hover:text-blue-400 transition">About</a>
                <a href="#contact" class="hover:text-blue-400 transition">Contact</a>
            </div>
            <a href="/login"
                class="bg-gradient-to-r from-blue-600 to-blue-900 px-5 py-2 rounded-full text-sm font-semibold hover:scale-105 transition">
                Get Started
            </a>
        </div>
    </nav>

    <!-- ==================== HERO SECTION ==================== -->
    <section id="home" class="min-h-screen flex items-center justify-center relative overflow-hidden pt-20">

        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 text-6xl float opacity-20">❄️</div>
        <div class="absolute bottom-20 right-10 text-8xl float-delayed opacity-20">🌡️</div>

        <div class="container mx-auto px-6 text-center relative z-10">
            <div data-aos="zoom-in-down" class="inline-block mb-6">
                <div class="glass-premium rounded-full px-6 py-2 text-sm">
                    <i class="fa-solid fa-microchip mr-2"></i> IoT Powered System
                </div>
            </div>

            <h1 data-aos="fade-up" class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-black mb-6 leading-tight">
                Intelligent
                <span class="gradient-text block">Climate Control</span>
            </h1>

            <p data-aos="fade-up" data-aos-delay="200"
                class="max-w-2xl mx-auto text-lg md:text-xl text-gray-300 mb-10 leading-relaxed">
                Experience the future of air conditioning management with real-time monitoring,
                smart automation, and energy-efficient technology.
            </p>

            <div data-aos="fade-up" data-aos-delay="400" class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/login"
                    class="btn-premium bg-gradient-to-r from-blue-600 to-blue-900 px-8 py-4 rounded-full font-semibold text-lg hover:shadow-2xl transition inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rocket"></i> Launch Dashboard
                </a>
                <a href="#features"
                    class="glass-premium px-8 py-4 rounded-full font-semibold text-lg hover:bg-white/10 transition inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-play"></i> Watch Demo
                </a>
            </div>

            <!-- Stats -->
            <div data-aos="fade-up" data-aos-delay="600"
                class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-20 pt-10 border-t border-white/10">
                <div>
                    <div class="stat-number">500+</div>
                    <div class="text-sm text-gray-400">Active Devices</div>
                </div>
                <div>
                    <div class="stat-number">99.9%</div>
                    <div class="text-sm text-gray-400">Uptime</div>
                </div>
                <div>
                    <div class="stat-number">24/7</div>
                    <div class="text-sm text-gray-400">Monitoring</div>
                </div>
                <div>
                    <div class="stat-number">30%</div>
                    <div class="text-sm text-gray-400">Energy Savings</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FEATURES SECTION ==================== -->
    <section id="features" class="py-24 px-6 relative">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <div data-aos="fade-up" class="inline-block glass-premium rounded-full px-6 py-2 text-sm mb-4">
                    <i class="fa-solid fa-gem mr-2"></i> Premium Features
                </div>
                <h2 data-aos="fade-up" data-aos-delay="100" class="text-3xl md:text-5xl font-bold mb-4">
                    Why Choose <span class="gradient-text">SmartAC?</span>
                </h2>
                <p data-aos="fade-up" data-aos-delay="200" class="text-gray-400 max-w-2xl mx-auto">
                    Cutting-edge technology combined with elegant design for the ultimate control experience
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div data-aos="fade-up" data-aos-delay="300"
                    class="feature-card glass-premium rounded-2xl p-8 text-center hover:glow transition-all">
                    <div
                        class="w-20 h-20 bg-gradient-to-r from-blue-600 to-blue-900 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-bolt text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Real-Time Intelligence</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Instant system feedback with live data synchronization and predictive analytics
                    </p>
                </div>

                <div data-aos="fade-up" data-aos-delay="400"
                    class="feature-card glass-premium rounded-2xl p-8 text-center hover:glow transition-all">
                    <div
                        class="w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Seamless Remote Access</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Control your AC system anytime, anywhere with our cloud-based platform
                    </p>
                </div>

                <div data-aos="fade-up" data-aos-delay="500"
                    class="feature-card glass-premium rounded-2xl p-8 text-center hover:glow transition-all">
                    <div
                        class="w-20 h-20 bg-gradient-to-r from-pink-600 to-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-chart-line text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Energy Efficiency</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Reduce energy consumption by up to 30% with smart scheduling and automation
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== HOW IT WORKS ==================== -->
    <section class="py-24 px-6 bg-gradient-to-b from-transparent to-white/5">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <h2 data-aos="fade-up" class="text-3xl md:text-5xl font-bold mb-4">
                    How It <span class="gradient-text">Works</span>
                </h2>
                <p data-aos="fade-up" data-aos-delay="100" class="text-gray-400 max-w-2xl mx-auto">
                    Simple setup, powerful results — in just three steps
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div data-aos="fade-up" data-aos-delay="200" class="text-center">
                    <div
                        class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-600 to-blue-900 flex items-center justify-center mx-auto mb-6 text-3xl font-black shadow-lg">
                        1</div>
                    <h3 class="text-xl font-bold mb-3">Install Device</h3>
                    <p class="text-gray-400">Connect your ESP32 device to power and network</p>
                </div>

                <div data-aos="fade-up" data-aos-delay="300" class="text-center">
                    <div
                        class="w-24 h-24 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center mx-auto mb-6 text-3xl font-black shadow-lg">
                        2</div>
                    <h3 class="text-xl font-bold mb-3">Configure System</h3>
                    <p class="text-gray-400">Set your preferences and automation rules</p>
                </div>

                <div data-aos="fade-up" data-aos-delay="400" class="text-center">
                    <div
                        class="w-24 h-24 rounded-full bg-gradient-to-r from-pink-600 to-orange-600 flex items-center justify-center mx-auto mb-6 text-3xl font-black shadow-lg">
                        3</div>
                    <h3 class="text-xl font-bold mb-3">Enjoy Control</h3>
                    <p class="text-gray-400">Monitor and manage your AC from anywhere</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== ILLUSTRATION SECTION ==================== -->
    <section id="about" class="py-24 px-6">
        <div class="container mx-auto max-w-7xl">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div data-aos="fade-right" class="flex-1 text-center md:text-left">
                    <div class="inline-block glass-premium rounded-full px-6 py-2 text-sm mb-4">
                        <i class="fa-solid fa-microchip mr-2"></i> Advanced Technology
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">
                        Built with
                        <span class="gradient-text">ESP32 & MQTT</span>
                    </h2>
                    <p class="text-gray-300 text-lg leading-relaxed mb-6">
                        Our system leverages the power of ESP32 microcontrollers and MQTT protocol
                        to deliver lightning-fast responses and reliable communication between your
                        devices and the cloud platform.
                    </p>
                    <div class="flex gap-4 justify-center md:justify-start">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check-circle text-green-500"></i>
                            <span>Low Latency</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check-circle text-green-500"></i>
                            <span>Secure Connection</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check-circle text-green-500"></i>
                            <span>Scalable</span>
                        </div>
                    </div>
                </div>
                <div data-aos="fade-left" class="flex-1">
                    <div class="relative">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-900 rounded-full blur-3xl opacity-30">
                        </div>
                        <img src="https://cdn-icons-png.flaticon.com/512/1048/1048943.png"
                            class="relative w-full max-w-md mx-auto float" alt="Smart AC Illustration">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== TESTIMONIALS ==================== -->
    <section class="py-24 px-6 bg-gradient-to-t from-transparent to-white/5">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-16">
                <h2 data-aos="fade-up" class="text-3xl md:text-5xl font-bold mb-4">
                    What Our <span class="gradient-text">Clients Say</span>
                </h2>
                <p data-aos="fade-up" data-aos-delay="100" class="text-gray-400 max-w-2xl mx-auto">
                    Trusted by businesses and homeowners worldwide
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div data-aos="fade-up" data-aos-delay="200" class="glass-premium rounded-2xl p-8">
                    <i class="fa-solid fa-quote-left text-4xl text-blue-500 mb-4"></i>
                    <p class="text-gray-300 leading-relaxed mb-6">
                        "The best investment we've made for our office. Energy bills reduced by 25% and employees are
                        more comfortable."
                    </p>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-600 to-blue-900 flex items-center justify-center">
                            <i class="fa-solid fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Michael Chen</p>
                            <p class="text-sm text-gray-400">Facility Manager</p>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="300" class="glass-premium rounded-2xl p-8">
                    <i class="fa-solid fa-quote-left text-4xl text-purple-500 mb-4"></i>
                    <p class="text-gray-300 leading-relaxed mb-6">
                        "Finally, an AC control system that actually works. The real-time monitoring is a game changer."
                    </p>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center">
                            <i class="fa-solid fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Sarah Johnson</p>
                            <p class="text-sm text-gray-400">Homeowner</p>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="400" class="glass-premium rounded-2xl p-8">
                    <i class="fa-solid fa-quote-left text-4xl text-pink-500 mb-4"></i>
                    <p class="text-gray-300 leading-relaxed mb-6">
                        "Amazing support team and incredible technology. Highly recommended for any smart building."
                    </p>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-to-r from-pink-600 to-orange-600 flex items-center justify-center">
                            <i class="fa-solid fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold">David Williams</p>
                            <p class="text-sm text-gray-400">Building Owner</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== CTA SECTION ==================== -->
    <section class="py-24 px-6">
        <div class="container mx-auto max-w-5xl">
            <div data-aos="zoom-in" class="glass-premium rounded-3xl p-12 text-center relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-r from-blue-600 to-blue-900 rounded-full blur-3xl opacity-20">
                </div>
                <h2 class="text-3xl md:text-5xl font-bold mb-4">
                    Ready to Get Started?
                </h2>
                <p class="text-gray-300 text-lg mb-8 max-w-2xl mx-auto">
                    Join thousands of satisfied users and transform your climate control experience today
                </p>
                <a href="/login"
                    class="btn-premium bg-gradient-to-r from-blue-600 to-blue-900 px-10 py-4 rounded-full font-semibold text-lg inline-flex items-center gap-2 hover:shadow-2xl transition">
                    <i class="fa-solid fa-arrow-right"></i> Start Free Trial
                </a>
                <p class="text-sm text-gray-400 mt-6">No credit card required · Free 14-day trial</p>
            </div>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer id="contact" class="py-12 px-6 border-t border-white/10">
        <div class="container mx-auto max-w-7xl">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-snowflake text-2xl text-blue-400"></i>
                        <span class="text-xl font-bold gradient-text">SmartAC</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Intelligent climate control for modern environments
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="#" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition">Documentation</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Connect</h4>
                    <div class="flex gap-4">
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                            <i class="fa-brands fa-twitter"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                            <i class="fa-brands fa-github"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="text-center pt-8 border-t border-white/10">
                <p class="text-gray-400 text-sm">
                    Designed & Developed by <span class="font-semibold text-white">Arico Official</span>
                </p>
                <p class="text-gray-500 text-xs mt-2">© 2026 SmartAC. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- ==================== SCRIPTS ==================== -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2/tsparticles.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Particles Configuration (Premium)
        if (window.innerWidth > 768) {
            tsParticles.load('tsparticles', {
                particles: {
                    number: {
                        value: 80,
                        density: {
                            enable: true,
                            area: 800
                        }
                    },
                    color: {
                        value: ["#667eea", "#764ba2", "#f093fb", "#4facfe"]
                    },
                    shape: {
                        type: "circle"
                    },
                    opacity: {
                        value: 0.3,
                        random: true
                    },
                    size: {
                        value: 2,
                        random: true
                    },
                    move: {
                        enable: true,
                        speed: 1,
                        direction: "none",
                        random: true,
                        straight: false
                    },
                    links: {
                        enable: true,
                        color: "#ffffff",
                        opacity: 0.1,
                        distance: 150
                    },
                    interactivity: {
                        events: {
                            onHover: {
                                enable: true,
                                mode: "grab"
                            },
                            onClick: {
                                enable: true,
                                mode: "push"
                            }
                        }
                    }
                }
            });
        }

        // Vanilla Tilt for 3D Cards
        VanillaTilt.init(document.querySelectorAll('.feature-card, .glass-premium'), {
            max: 5,
            speed: 400,
            glare: true,
            'max-glare': 0.1,
            scale: 1.02
        });

        // Custom Cursor
        const cursor = document.querySelector('.custom-cursor');
        const cursorDot = document.querySelector('.custom-cursor-dot');

        if (cursor && cursorDot && window.innerWidth > 768) {
            document.addEventListener('mousemove', (e) => {
                cursor.style.transform = `translate(${e.clientX - 10}px, ${e.clientY - 10}px)`;
                cursorDot.style.transform = `translate(${e.clientX - 2}px, ${e.clientY - 2}px)`;
            });

            document.querySelectorAll('a, button').forEach(el => {
                el.addEventListener('mouseenter', () => {
                    cursor.style.transform = 'scale(1.5)';
                    cursor.style.borderColor = '#f093fb';
                });
                el.addEventListener('mouseleave', () => {
                    cursor.style.transform = 'scale(1)';
                    cursor.style.borderColor = 'rgba(102, 126, 234, 0.8)';
                });
            });
        }

        // Scroll Reveal
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));

        // Smooth Scroll for Nav Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== "#" && href !== "#home") {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Parallax Effect
        if (window.innerWidth > 768) {
            window.addEventListener('mousemove', (e) => {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                document.querySelectorAll('.float, .float-delayed').forEach(el => {
                    const moveX = (x - 0.5) * 20;
                    const moveY = (y - 0.5) * 20;
                    el.style.transform = `translate(${moveX}px, ${moveY}px)`;
                });
            });
        }
    </script>
</body>

</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views/welcome.blade.php ENDPATH**/ ?>