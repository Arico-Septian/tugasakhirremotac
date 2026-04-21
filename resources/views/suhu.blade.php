<!DOCTYPE html>
<html>
<head>
    <title>Monitoring Suhu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center h-screen">

    <div class="text-center">
        <h1 class="text-3xl mb-4">🌡️ Suhu Raspberry Pi</h1>
        <div id="suhu" class="text-6xl font-bold text-green-400">
            Loading...
        </div>
    </div>

    <script>
        function getSuhu() {
            fetch('/suhu-raspi')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('suhu').innerText = data.suhu;
                });
        }

        setInterval(getSuhu, 2000);
        getSuhu();
    </script>

</body>
</html>
