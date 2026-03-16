<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | AC Management</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-400 to-sky-200">

    <div class="bg-white shadow-2xl rounded-2xl flex overflow-hidden max-w-4xl w-full">

        <!-- LEFT SIDE -->
        <div class="w-1/2 bg-gradient-to-br from-blue-500 to-blue-700 text-white p-10 flex flex-col justify-center">

            <h1 class="text-3xl font-bold mb-4">
                Create Admin Account
            </h1>

            <p class="text-blue-100 mb-6">
                Register a new administrator to manage and control
                air conditioning systems within the centralized platform.
            </p>

            <div class="space-y-3 text-sm">

                <div>✔ Centralized AC management</div>
                <div>✔ Real-time system monitoring</div>
                <div>✔ Intelligent temperature control</div>
                <div>✔ Secure access for administrators</div>

            </div>

        </div>


        <!-- RIGHT SIDE -->
        <div class="w-1/2 p-10">

            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                Create Account
            </h2>

            <p class="text-gray-500 text-sm mb-6">
                Register administrator account
            </p>

            <!-- ERROR MESSAGE -->
            @if ($errors->any())
                <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form method="POST" action="/register" class="space-y-4">

                @csrf

                <!-- USERNAME -->
                <div>

                    <label class="text-sm text-gray-600">Username</label>

                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="Enter username">

                </div>


                <!-- EMAIL -->
                <div>

                    <label class="text-sm text-gray-600">Email</label>

                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="username@email.com">

                </div>


                <!-- PASSWORD -->
                <div>

                    <label class="text-sm text-gray-600">Password</label>

                    <input type="password" name="password" required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="••••••••">

                </div>


                <!-- CONFIRM PASSWORD -->
                <div>

                    <label class="text-sm text-gray-600">Confirm Password</label>

                    <input type="password" name="password_confirmation" required
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                        placeholder="••••••••">

                </div>


                <!-- BUTTON -->
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">

                    Register

                </button>

            </form>


            <div class="text-center mt-4 text-sm text-gray-500">

                Already have an account?

                <a href="/login" class="text-blue-600 hover:underline">
                    Login
                </a>

            </div>

        </div>

    </div>

</body>

</html>
