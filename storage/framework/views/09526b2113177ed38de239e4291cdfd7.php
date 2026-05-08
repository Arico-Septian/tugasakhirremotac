<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Monitoring Raspberry Pi — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php echo $__env->make('components.sidebar-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>
        .raspi-card {
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-lg);
            padding: 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .raspi-temp {
            font-size: 72px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1;
            transition: color 0.4s ease;
        }
        .temp-cool  { color: var(--cyan); }
        .temp-warm  { color: var(--amber, #f59e0b); }
        .temp-hot   { color: var(--coral); }
        .temp-muted { color: var(--ink-3); }
        .raspi-label {
            font-size: 12px;
            color: var(--ink-3);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 600;
        }
        .raspi-status {
            font-size: 12px;
            color: var(--ink-3);
            margin-top: 4px;
        }
        .raspi-indicator {
            width: 8px; height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .raspi-indicator.online { background: var(--cyan); box-shadow: 0 0 6px var(--cyan); }
        .raspi-indicator.offline { background: var(--ink-3); }
    </style>
</head>
<body>
<div class="custom-bg"></div>
<div id="overlay"></div>

<div class="layout">
    <?php echo $__env->make('components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="main-content">
        <header class="main-header">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="lg:hidden btn-icon" title="Menu">
                    <i class="fa-solid fa-bars text-xs"></i>
                </button>
                <div class="app-header-title">
                    <h1>Monitoring Raspberry Pi</h1>
                    <p>Suhu CPU server IoT secara realtime</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <?php echo $__env->make('components.notification-bell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </header>

        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner space-y-4">

                    <div class="raspi-card">
                        <p class="raspi-label">
                            <i class="fa-brands fa-raspberry-pi" style="margin-right:6px;"></i>
                            Suhu CPU Raspberry Pi
                        </p>
                        <div id="raspi-temp" class="raspi-temp temp-muted">--</div>
                        <p id="raspi-status" class="raspi-status">
                            <span class="raspi-indicator offline" id="raspi-dot"></span>
                            Menghubungkan...
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <?php echo $__env->make('components.bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>

<?php echo $__env->make('components.sidebar-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script>
    function getSuhu() {
        fetch('/suhu-raspi?_=' + Date.now(), { cache: 'no-store' })
            .then(res => res.json())
            .then(data => {
                const el  = document.getElementById('raspi-temp');
                const st  = document.getElementById('raspi-status');
                const dot = document.getElementById('raspi-dot');

                if (data.value !== null && data.value !== undefined) {
                    el.innerText = data.value + ' °C';
                    el.className = 'raspi-temp ' + (
                        data.value >= 70 ? 'temp-hot' :
                        data.value >= 55 ? 'temp-warm' :
                        'temp-cool'
                    );
                    dot.className = 'raspi-indicator online';
                    st.innerHTML  = '<span class="raspi-indicator online" id="raspi-dot"></span>Online · Update tiap 1 menit';
                } else {
                    el.innerText  = '--';
                    el.className  = 'raspi-temp temp-muted';
                    dot.className = 'raspi-indicator offline';
                    st.innerHTML  = '<span class="raspi-indicator offline" id="raspi-dot"></span>Menunggu data...';
                }
            })
            .catch(() => {
                document.getElementById('raspi-status').innerText = 'Gagal mengambil data';
            });
    }

    getSuhu();
    setInterval(getSuhu, 3000);
</script>
</body>
</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views/server/monitoring.blade.php ENDPATH**/ ?>