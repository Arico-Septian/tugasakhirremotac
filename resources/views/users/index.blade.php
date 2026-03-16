<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Management</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* SIDEBAR */

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

        /* CONTENT SHIFT */

        .main-content {
            margin-left: 260px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        /* CARD */

        .card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }

        /* MOBILE */

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
    </style>

</head>

<body class="bg-gray-50">

    <!-- SIDEBAR -->

    <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full p-6 border-r z-50">

        <div class="flex justify-between items-center pb-5 mb-8 border-b">

            <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2">
                <i class="fa-solid fa-layer-group"></i>
                <span class="menu-text">AC System</span>
            </h2>

            <button onclick="toggleSidebar()" class="text-gray-500">
                <i class="fa-solid fa-bars"></i>
            </button>

        </div>

        <ul class="space-y-3">

            <li>
                <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="/rooms" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                    <i class="fa-solid fa-server"></i>
                    <span class="menu-text">Manage Rooms</span>
                </a>
            </li>

            <li>
                <a href="/users"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold">
                    <i class="fa-solid fa-users"></i>
                    <span class="menu-text">User Management</span>
                </a>
            </li>

        </ul>

    </div>



    <!-- MAIN -->

    <div class="main-content min-h-screen flex flex-col">

        <header class="sticky top-0 bg-white border-b px-8 py-5 flex justify-between items-center">

            <h1 class="text-2xl font-bold text-gray-800">
                User Management
            </h1>

            <button onclick="openModal()" class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700">

                + Add User

            </button>

        </header>



        <div class="p-8">

            <!-- STATS -->

            <div class="card mb-6 flex justify-between items-center">

                <div>
                    <p class="text-gray-500 text-sm">Total Users</p>
                    <h2 class="text-3xl font-bold">{{ $users->count() }}</h2>
                </div>

                <i class="fa-solid fa-users text-3xl text-blue-500"></i>

            </div>


            <!-- USER TABLE -->

            <div class="card overflow-x-auto">

                <table class="w-full">

                    <thead class="border-b">

                        <tr class="text-left text-gray-500 text-sm">

                            <th class="p-3">Name</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($users as $user)
                            <tr class="border-b hover:bg-gray-50">

                                <td class="p-3">{{ $user->name }}</td>
                                <td class="p-3">{{ $user->email }}</td>
                                <td class="p-3">{{ $user->role }}</td>

                                <td class="p-3 flex gap-2">

                                    <form action="/users/{{ $user->id }}" method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this user?')"
                                            class="bg-red-500 text-white px-3 py-1 rounded">

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

    <!-- MODAL ADD USER -->

    <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">

        <div class="bg-white p-8 rounded-xl w-96">

            <h2 class="text-xl font-bold mb-5">
                Add New User
            </h2>

            <form method="POST" action="/users">

                @csrf

                <input type="text" name="name" placeholder="Name" class="border p-3 w-full mb-3 rounded">

                <input type="email" name="email" placeholder="Email" class="border p-3 w-full mb-3 rounded">

                <input type="password" name="password" placeholder="Password" class="border p-3 w-full mb-3 rounded">

                <select name="role" class="border p-3 w-full mb-4 rounded">

                    <option value="admin">Admin</option>
                    <option value="operator">Operator</option>

                </select>

                <button class="bg-blue-600 text-white w-full py-2 rounded">

                    Create User

                </button>

            </form>

        </div>

    </div>

    </div>


    <script>
        function toggleSidebar() {

            let sidebar = document.getElementById("sidebar")

            sidebar.classList.toggle("close")
            sidebar.classList.toggle("open")

        }

        function openModal() {

            document.getElementById("modal").classList.remove("hidden")

        }
    </script>

</body>

</html>
