<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php echo $__env->make('components.sidebar-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                    <h1>My Profile</h1>
                    <p>Account &amp; security settings</p>
                </div>
            </div>
        </header>

        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner" style="max-width:640px;margin:0 auto;">

                    
                    <div class="panel panel-lg" style="display:flex;align-items:center;gap:18px;">
                        <div class="avatar avatar-xl"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                        <div class="flex-1 min-w-0">
                            <h2 style="font-size:18px;font-weight:600;color:var(--ink-0);margin:0;letter-spacing:-0.01em;"><?php echo e($user->name); ?></h2>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="badge-role <?php echo e($user->role); ?>"><?php echo e(strtoupper($user->role)); ?></span>
                                <?php if($user->last_activity): ?>
                                    <span class="text-xs" style="color:var(--ink-3);">
                                        Last active <?php echo e($user->last_activity->diffForHumans()); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    
                    <div class="panel panel-lg mt-4">
                        <div class="panel-header" style="margin-bottom:16px;">
                            <div>
                                <p class="eyebrow"><i class="fa-solid fa-key"></i> Security</p>
                                <h3 class="panel-title">Change password</h3>
                                <p class="panel-subtitle">Pilih password baru yang kuat dan tidak mudah ditebak</p>
                            </div>
                        </div>

                        <?php if(session('success')): ?>
                            <div class="alert alert-success mb-4">
                                <i class="fa-solid fa-circle-check alert-icon"></i>
                                <div class="alert-body"><?php echo e(session('success')); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if($errors->any()): ?>
                            <div class="alert alert-error mb-4">
                                <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                                <div class="alert-body"><?php echo e($errors->first()); ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/change-password" id="pwForm" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <div class="field">
                                <label class="field-label">New password</label>
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-lock"></i>
                                    <input class="input" type="password" name="password" id="pwInput"
                                           placeholder="Min. 6 characters" required minlength="6" style="padding-right:42px;">
                                    <button type="button" onclick="togglePw('pwInput', this)"
                                            class="btn-icon" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:transparent;border-color:transparent;">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="field">
                                <label class="field-label">Confirm new password</label>
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-lock"></i>
                                    <input class="input" type="password" name="password_confirmation" id="pwConfirm"
                                           placeholder="Ulangi password baru" required style="padding-right:42px;">
                                    <button type="button" onclick="togglePw('pwConfirm', this)"
                                            class="btn-icon" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:transparent;border-color:transparent;">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" id="pwBtn">
                                Update password
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('components.bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.getElementById('pwForm')?.addEventListener('submit', function (e) {
    const pw  = document.getElementById('pwInput').value;
    const pw2 = document.getElementById('pwConfirm').value;
    if (pw !== pw2) {
        e.preventDefault();
        window.smToast('Password dan konfirmasi tidak cocok', 'error');
    }
});
</script>
<?php echo $__env->make('components.sidebar-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views\profile\index.blade.php ENDPATH**/ ?>