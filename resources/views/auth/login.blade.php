<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | AC Management</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-400 to-sky-200">

    <div class="bg-white shadow-2xl rounded-2xl flex overflow-hidden w-[850px]">

        <!-- LEFT SIDE -->
        <div class="w-1/2 bg-gradient-to-br from-blue-500 to-blue-700 text-white p-10 flex flex-col justify-center">

            <h1 class="text-3xl font-bold mb-4">
                Centralized AC System
            </h1>

            <p class="text-blue-100 mb-6">
                Access the centralized dashboard to monitor and control
                air conditioning systems across all server rooms.
            </p>

            <div class="space-y-3 text-sm">

                <div>✔ Monitor AC status in real time</div>
                <div>✔ Adjust temperature remotely</div>
                <div>✔ Manage AC operating modes</div>
                <div>✔ Ensure optimal server room environment</div>

            </div>

        </div>


        <!-- RIGHT SIDE -->
        <div class="w-1/2 p-10">

            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                Login Admin
            </h2>

            <p class="text-gray-500 text-sm mb-6">
                Login using your username
            </p>

            @if (session('error'))
                <div class="bg-red-100 text-red-600 p-2 rounded mb-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-4">

                @csrf

                <div>
                    <label class="text-sm text-gray-600">Username</label>

                    <input type="text" name="name" required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="Username">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Password</label>

                    <input type="password" name="password" required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="••••••••">
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">

                    Login

                </button>

            </form>

            <div class="text-center mt-4 text-sm text-gray-500">

                Don't have an account?

                <a href="/register" class="text-blue-600 hover:underline">
                    Register
                </a>

            </div>

        </div>

    </div>

</body>

</html>
