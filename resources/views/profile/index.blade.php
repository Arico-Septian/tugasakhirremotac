<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Profile</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body class="bg-gray-50">
    <div class="max-w-3xl mx-auto mt-12">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center gap-6 mb-8">

                <div
                    class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white flex items-center justify-center text-3xl font-bold">

                    {{ strtoupper(substr($user->name, 0, 1)) }}

                </div>

                <div>

                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ $user->name }}
                    </h2>

                    <p class="text-gray-500">
                        {{ '@' . strtolower(str_replace(' ', '', $user->name)) }}
                    </p>

                    <span class="text-sm bg-blue-100 text-blue-600 px-3 py-1 rounded-full">
                        {{ $user->role }}
                    </span>

                </div>
            </div>

            <hr class="mb-6">

            <h3 class="text-lg font-semibold mb-4">
                Account Information
            </h3>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>

                    <label class="text-gray-500 text-sm">
                        Name
                    </label>

                    <p class="font-semibold">
                        {{ $user->name }}
                    </p>

                </div>

                <div>

                    <label class="text-gray-500 text-sm">
                        Username
                    </label>

                    <p class="font-semibold">
                        {{ $user->name }}
                    </p>

                </div>
            </div>

            <hr class="mb-6">

            <h3 class="text-lg font-semibold mb-4">
                Change Password
            </h3>

            <form method="POST" action="/change-password">
                @csrf
                <input type="password" name="password" placeholder="New Password"
                    class="border p-3 rounded-lg w-full mb-4">
                <input type="password" name="password_confirmation" placeholder="Confirm New Password"
                    class="border p-3 rounded-lg w-full mb-4">

                <button class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">

                    Update Password

                </button>

            </form>
        </div>
    </div>
</body>

</html>
