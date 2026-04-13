<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .sidebar {
            transition: all .3s ease;
        }

        .sidebar.close {
            width: 80px;
        }

        .sidebar.close .menu-text {
            display: none;
        }

        .sidebar.close h2 span {
            display: none;
        }

        .sidebar.close ul li a {
            justify-content: center;
        }

        .main-content {
            margin-left: 256px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }

        @media(max-width:900px) {
            .main-content {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                position: fixed;
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        .card {
            transition: all .2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        body {
            overflow-x: hidden;
        }

        #modal {
            transition: all .2s ease;
        }

        .sidebar {
            will-change: transform;
        }
    </style>
</head>

<body class="bg-gray-50">

    <div id="overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden z-40"></div>

    <!-- SIDEBAR -->
    <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full p-6 z-50">

        <div class="flex justify-between items-center pb-5 mb-8 border-b">

            <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2">
                <i class="fa-solid fa-layer-group"></i>
                <span class="menu-text">AC System</span>
            </h2>

            <button onclick="toggleSidebar()" class="text-gray-500 hover:text-blue-500">
                <i class="fa-solid fa-bars"></i>
            </button>

        </div>

        <ul class="space-y-3">

            @auth
                <li>
                    <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                {{-- Admin + Operator --}}
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms</span>
                        </a>
                    </li>
                @endif


                {{-- Admin only --}}
                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="/users"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold hover:bg-blue-100">
                            <i class="fa-solid fa-users"></i>
                            <span class="menu-text">User Management</span>
                        </a>
                    </li>
                @endif

                {{-- Admin only --}}
                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="/logs" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span class="menu-text">Activity Log</span>
                        </a>
                    </li>
                @endif
            @endauth
        </ul>

        <!-- PROFILE PINDAH KE BAWAH -->
        @auth
            <div class="absolute bottom-6 left-6 right-6">

                <!-- MODE NORMAL -->
                <div class="profile-full">
                    <button class="w-full flex items-center gap-3 px-3 py-2">

                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>

                        <div class="text-left menu-text">
                            <p class="text-sm font-semibold text-gray-800">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ Auth::user()->role ?? 'Administrator' }}
                            </p>
                        </div>

                        <a href="/logout" class="ml-auto text-red-500 hover:text-red-600 text-lg"
                            onclick="event.stopPropagation()">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>

                    </button>
                </div>

                <!-- MODE COLLAPSE -->
                <div class="profile-collapse hidden text-center">
                    <a href="/logout" class="text-red-500 text-xl">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                </div>

            </div>
        @endauth

    </div>

    <!-- MAIN -->
    <div class="main-content min-h-screen flex flex-col">

        <header
            class="sticky top-0 bg-white border-b px-4 md:px-6 py-3 md:py-4 flex items-center justify-between shadow-sm">

            <div class="flex items-center gap-3 md:gap-6">

                <button onclick="toggleSidebar()" class="md:hidden text-gray-600 text-base">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <h1 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold text-gray-800">
                    User Management
                </h1>

            </div>

            <button onclick="openModal()"
                class="bg-blue-600 text-white px-3 py-1.5 md:px-4 md:py-2 rounded-lg text-sm shadow hover:bg-blue-700">
                + Add User
            </button>

        </header>

        <div class="px-4 py-4 md:px-6 md:py-6">
            <div class="w-full max-w-7xl mx-auto">

                <!-- STATS -->
                <div class="card mb-6 flex justify-between items-center gap-2 max-w-sm mx-auto md:max-w-full">
                    <div>
                        <p class="text-gray-500 text-sm">Total Users</p>
                        <h2 class="text-2xl md:text-3xl font-bold">
                            {{ $users->count() }}</h2>
                    </div>
                    <i class="fa-solid fa-users text-lg md:text-3xl text-blue-500"></i>
                </div>

                <!-- TABLE -->
                <!-- TABLE / MOBILE CARD -->
                <div class="card">

                    <!--  MOBILE (HP) -->
                    <div class="block md:hidden space-y-3 px-2">
                        @foreach ($users as $user)
                            <div class="border rounded-xl p-3 sm:p-4 shadow-sm hover:shadow-md transition w-full sm:max-w-sm mx-auto">

                                <p class="font-semibold text-gray-800">{{ $user->name }}</p>

                                <div class="mt-2 flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-500">Role</span>
                                    <span
                                        class="px-2 py-1 rounded-full text-xs
                                        {{ $user->role == 'admin' ? 'bg-blue-100 text-blue-600' : '' }}
                                        {{ $user->role == 'operator' ? 'bg-green-100 text-green-600' : '' }}
                                        {{ $user->role == 'user' ? 'bg-gray-100 text-gray-600' : '' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>

                                <div class="mt-2 flex justify-between text-sm">
                                    <span class="text-gray-500">Status</span>
                                    @if ($user->is_online)
                                        <span class="text-green-600 text-xs">Online</span>
                                    @else
                                        <span class="text-gray-500 text-xs">Offline</span>
                                    @endif
                                </div>

                                <form action="/users/{{ $user->id }}" method="POST" class="mt-3">
                                    @csrf
                                    @method('DELETE')
                                    <button class="w-full bg-red-500 hover:bg-red-600 transition text-white py-2 rounded text-sm">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        @endforeach
                    </div>

                    <!-- DESKTOP -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">

                            <thead class="border-b bg-gray-50">
                                <tr class="text-left text-gray-500 text-sm">
                                    <th class="p-3">Name</th>
                                    <th class="p-3">Role</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($users as $user)
                                    <tr class="border-b hover:bg-gray-50 transition">

                                        <td class="p-3 font-medium">{{ $user->name }}</td>

                                        <td class="p-3">
                                            <span
                                                class="px-3 py-1 rounded-full text-xs md:text-sm
                                                {{ $user->role == 'admin' ? 'bg-blue-100 text-blue-600' : '' }}
                                                {{ $user->role == 'operator' ? 'bg-green-100 text-green-600' : '' }}
                                                {{ $user->role == 'user' ? 'bg-gray-100 text-gray-600' : '' }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>

                                        <td class="p-3">
                                            @if ($user->is_online)
                                                <span
                                                    class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">Online</span>
                                            @else
                                                <span
                                                    class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs">Offline</span>
                                            @endif
                                        </td>

                                        <td class="p-3">
                                            <form action="/users/{{ $user->id }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div>

        <!-- MODAL -->
        <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">

            <div class="bg-white p-8 rounded-xl w-[90%] max-w-md">

                <div class="flex justify-between mb-4">
                    <h2 class="text-xl font-bold">Add New User</h2>
                    <button onclick="closeModal()" class="text-gray-500">✕</button>
                </div>

                <form method="POST" action="/users">
                    @csrf

                    <input type="text" name="name" placeholder="Name" class="border p-3 w-full mb-3 rounded"
                        required>

                    <input type="password" name="password" placeholder="Password"
                        class="border p-3 w-full mb-3 rounded" required>

                    <select name="role" class="border p-3 w-full mb-4 rounded">
                        <option value="admin">Admin</option>
                        <option value="operator">Operator</option>
                        <option value="user">User</option>
                    </select>

                    <button class="bg-blue-600 text-white w-full py-2 rounded hover:bg-blue-700">
                        Create User
                    </button>
                </form>

            </div>

        </div>

        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById("sidebar");
                const overlay = document.getElementById("overlay");

                if (window.innerWidth <= 900) {
                    sidebar.classList.toggle("open");
                    overlay.classList.toggle("hidden");
                } else {
                    sidebar.classList.toggle("close");
                }
            }

            document.getElementById("overlay").onclick = function() {
                document.getElementById("sidebar").classList.remove("open");
                this.classList.add("hidden");
            };

            function openModal() {
                document.getElementById("modal").classList.remove("hidden")
            }

            function closeModal() {
                document.getElementById("modal").classList.add("hidden")
            }
        </script>

</body>

</html>
