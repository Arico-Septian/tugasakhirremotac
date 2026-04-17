<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AC System</title>

    <link href="/css/app.css" rel="stylesheet">
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 via-sky-400 to-cyan-300">

    <div class="w-full max-w-md p-6">

        <div class="backdrop-blur-lg bg-white/30 shadow-xl rounded-2xl p-6">

            <!-- TITLE -->
            <h2 class="text-2xl font-bold text-white text-center mb-2">
                AC Management
            </h2>

            <p class="text-white/80 text-center text-sm mb-6">
                Login to control your system
            </p>

            <!-- ERROR GLOBAL -->
            @if (session('error'))
                <div class="bg-red-100 text-red-600 p-2 rounded mb-4 text-sm text-center">
                    {{ session('error') }}
                </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="/login" class="space-y-4"
                onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerText='Loading...';">
                @csrf

                <!-- USERNAME -->
                <div>
                    <label class="text-white text-sm">Username</label>
                    <input type="text" name="name" required autofocus autocomplete="username"
                        placeholder="Enter username"
                        class="w-full mt-1 px-4 py-3 rounded-lg bg-white/80 focus:ring-2 focus:ring-blue-400 outline-none">

                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PASSWORD -->
                <div>
                    <label class="text-white text-sm">Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                        placeholder="Enter password"
                        class="w-full mt-1 px-4 py-3 rounded-lg bg-white/80 focus:ring-2 focus:ring-blue-400 outline-none">

                    @error('password')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- BUTTON -->
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Login
                </button>

            </form>

        </div>

    </div>

</body>

</html>
