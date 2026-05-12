<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(rgba(7,16,31,0.55), rgba(7,16,31,0.55)),
                url('/images/wallpaper.jpeg') center/cover no-repeat fixed !important;
        }

        .login-shell {
            position: relative; z-index: 1;
            width: 100%; max-width: 420px;
        }
        .login-card {
            background: linear-gradient(180deg, rgba(18, 32, 66, 0.85), rgba(12, 24, 48, 0.85));
            border: 1px solid var(--line);
            border-radius: var(--r-3xl);
            padding: 36px 32px 28px;
            -webkit-backdrop-filter: blur(24px);
            backdrop-filter: blur(24px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.40), inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .login-brand {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 28px;
        }
        .login-logo {
            width: 56px; height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--cyan), var(--lavender-d));
            display: inline-flex; align-items: center; justify-content: center;
            color: #07101f;
            font-size: 22px;
            box-shadow: 0 12px 30px rgba(77, 212, 255, 0.30), inset 0 1px 0 rgba(255,255,255,0.30);
            margin-bottom: 16px;
        }
        .login-title {
            font-size: 20px; font-weight: 700; color: var(--ink-0);
            margin: 0; letter-spacing: -0.015em;
        }
        .login-subtitle {
            font-size: 13px; color: var(--ink-3);
            margin: 6px 0 0;
        }
        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--line-soft);
            font-size: 11.5px;
            color: var(--ink-4);
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <div class="login-brand">
                <div class="login-logo"><i class="fa-solid fa-snowflake"></i></div>
                <h1 class="login-title">Welcome back</h1>
                <p class="login-subtitle">Sign in to your SmartAC control panel</p>
            </div>

            <?php if(session('error')): ?>
                <div class="alert alert-error mb-4">
                    <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                    <div class="alert-body"><?php echo e(session('error')); ?></div>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-error mb-4">
                    <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                    <div class="alert-body"><?php echo e($errors->first()); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" id="loginForm" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div class="field">
                    <label class="field-label">Username</label>
                    <div class="input-icon-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input class="input" type="text" name="name"
                               required autofocus autocomplete="username"
                               pattern="\S+" title="Username tidak boleh mengandung spasi"
                               placeholder="Enter your username"
                               value="<?php echo e(old('name')); ?>">
                    </div>
                </div>

                <div class="field">
                    <label class="field-label">Password</label>
                    <div class="input-icon-wrap" style="position:relative;">
                        <i class="fa-solid fa-lock"></i>
                        <input class="input" type="password" name="password" id="password"
                               required autocomplete="current-password"
                               minlength="8" title="Password minimal 8 karakter"
                               placeholder="Enter your password" style="padding-right:42px;">
                        <button type="button" onclick="togglePassword()"
                                class="btn-icon" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:transparent;border-color:transparent;">
                            <i class="fa-regular fa-eye text-[11px]" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" id="loginBtn" style="margin-top:8px;">
                    <span>Sign in</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="login-footer">
                <i class="fa-regular fa-copyright"></i> <?php echo e(date('Y')); ?> SmartAC Control System
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.className = 'fa-regular fa-eye-slash text-[11px]';
            } else {
                password.type = 'password';
                icon.className = 'fa-regular fa-eye text-[11px]';
            }
        }

        document.getElementById('loginForm')?.addEventListener('submit', function () {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('is-loading');
        });
    </script>
</body>
</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views/auth/login.blade.php ENDPATH**/ ?>