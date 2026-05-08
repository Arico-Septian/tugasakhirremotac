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
            --
        </div>
        <div id="status" class="text-sm text-gray-400 mt-4">Menghubungkan...</div>
    </div>

    <script>
        function getSuhu() {
            fetch('/suhu-raspi')
                .then(res => res.json())
                .then(data => {
                    const el = document.getElementById('suhu');
                    const st = document.getElementById('status');
                    if (data.value !== null && data.value !== undefined) {
                        el.innerText = data.value + ' °C';
                        el.className = 'text-6xl font-bold ' + (data.value >= 70 ? 'text-red-400' : data.value >= 55 ? 'text-yellow-400' : 'text-green-400');
                        st.innerText = 'Update tiap 1 menit';
                    } else {
                        el.innerText = '--';
                        st.innerText = 'Menunggu data dari Raspberry Pi...';
                    }
                })
                .catch(() => {
                    document.getElementById('status').innerText = 'Gagal mengambil data';
                });
        }

        setInterval(getSuhu, 5000);
        getSuhu();
    </script>

</body>
</html>
