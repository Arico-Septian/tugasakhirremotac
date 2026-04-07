<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart AC Control System</title>

    <!-- FONT (PREMIUM LOOK) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <!-- Particles -->
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2/tsparticles.bundle.min.js"></script>

    <!-- Tilt -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(-45deg, #4f46e5, #2563eb, #06b6d4, #6366f1);
            background-size: 400% 400%;
            animation: gradientMove 12s ease infinite;
        }

        @keyframes gradientMove {
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

        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(14px);
        }

        .parallax-layer {
            transition: transform 0.2s linear;
        }
    </style>
</head>

<body class="text-white overflow-x-hidden">

    <!-- PARTICLES -->
    <div id="tsparticles" class="fixed top-0 left-0 w-full h-full -z-10"></div>

    <!-- HERO -->
    <section id="parallax" class="min-h-screen flex flex-col items-center justify-center text-center px-6 relative">

        <!-- PARALLAX BACKGROUND -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="parallax-layer absolute w-72 h-72 bg-white/10 rounded-full blur-3xl top-20 left-20"></div>
            <div class="parallax-layer absolute w-96 h-96 bg-blue-300/10 rounded-full blur-3xl bottom-20 right-20">
            </div>
        </div>

        <!-- ICON -->
        <div data-aos="zoom-in" class="text-6xl mb-6">❄️</div>

        <!-- TITLE -->
        <h1 data-aos="fade-up" class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight">
            Intelligent Climate Control
            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-white to-blue-200">
                Powered by IoT Technology
            </span>
        </h1>

        <!-- DESCRIPTION -->
        <p data-aos="fade-up" data-aos-delay="200"
            class="max-w-2xl text-lg md:text-xl opacity-90 mb-10 leading-relaxed">
            A modern, intelligent air conditioning management system designed to deliver
            seamless control, real-time monitoring, and energy-efficient operation —
            all from a single elegant dashboard.
        </p>

        <!-- MICRO TEXT -->
        <p class="text-sm opacity-70 mb-6 tracking-wide">
            Realtime • Scalable • Smart Automation
        </p>

        <!-- BUTTON -->
        <div data-aos="fade-up" data-aos-delay="400" class="flex gap-4">
            <a href="/login"
                class="bg-white text-blue-600 px-6 py-3 rounded-xl font-semibold shadow
                  hover:scale-110 hover:shadow-xl transition duration-300">
                Get Started
            </a>
        </div>

    </section>

    <!-- ILUSTRASI -->
    <section class="py-16 px-8 flex flex-col md:flex-row items-center gap-10 max-w-6xl mx-auto">

        <div data-aos="fade-right" class="flex-1 tilt">
            <img src="https://cdn-icons-png.flaticon.com/512/1048/1048943.png" class="w-full max-w-md mx-auto">
        </div>

        <div data-aos="fade-left" class="flex-1">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 leading-tight">
                Smart Automation for
                <span class="text-blue-200">Modern Environments</span>
            </h2>

            <p class="opacity-90 leading-relaxed text-lg">
                Built with ESP32, MQTT, and Laravel, this system enables efficient,
                scalable, and intelligent environmental control tailored for modern infrastructures.
            </p>
        </div>

    </section>

    <!-- FEATURES -->
    <section class="py-16 px-8 bg-white text-gray-800">

        <h2 class="text-3xl font-bold text-center mb-12">
            Key Features
        </h2>

        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">

            <div class="tilt p-6 rounded-2xl shadow hover:shadow-xl transition">
                <h3 class="font-bold text-lg mb-2">⚡ Real-Time Intelligence</h3>
                <p>
                    Experience instant system feedback with live data synchronization.
                </p>
            </div>

            <div class="tilt p-6 rounded-2xl shadow hover:shadow-xl transition">
                <h3 class="font-bold text-lg mb-2">📱 Seamless Remote Access</h3>
                <p>
                    Control your AC system anytime, anywhere with ease.
                </p>
            </div>

            <div class="tilt p-6 rounded-2xl shadow hover:shadow-xl transition">
                <h3 class="font-bold text-lg mb-2">🔄 Intelligent Synchronization</h3>
                <p>
                    Keeps system data consistent even after device restart.
                </p>
            </div>

        </div>

    </section>

    <!-- FOOTER -->
    <footer class="text-center py-10 text-sm opacity-70">
        <p class="font-semibold tracking-wide">Smart AC Control System</p>
        <p class="mt-2">Designed & Developed by <span class="font-semibold">Arico Official</span></p>
        <p class="mt-1 text-xs opacity-60">© 2026 All Rights Reserved</p>
    </footer>

    <!-- SCRIPT -->
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        tsParticles.load("tsparticles", {
            particles: {
                number: {
                    value: 60
                },
                size: {
                    value: 3
                },
                move: {
                    speed: 1
                },
                links: {
                    enable: true,
                    color: "#ffffff",
                    opacity: 0.2
                },
                opacity: {
                    value: 0.3
                }
            }
        });

        VanillaTilt.init(document.querySelectorAll(".tilt"), {
            max: 10,
            speed: 400,
            glare: true,
            "max-glare": 0.2
        });

        document.addEventListener("mousemove", (e) => {
            document.querySelectorAll(".parallax-layer").forEach(layer => {
                let speed = 20;
                let x = (window.innerWidth - e.pageX * speed) / 100;
                let y = (window.innerHeight - e.pageY * speed) / 100;
                layer.style.transform = `translate(${x}px, ${y}px)`;
            });
        });
    </script>

</body>

</html>
