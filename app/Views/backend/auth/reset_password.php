<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { background:#f6f6f6; font-family:Arial; }
        .box { max-width:400px; margin:60px auto; background:#fff; padding:32px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.07);}
        input { width:100%; padding:12px; margin:10px 0; border-radius:4px; border:1px solid #ccc;}
        button { width:100%; padding:12px; background:#007bff; color:#fff; border:none; border-radius:4px; font-weight:bold;}
        .msg { margin:12px 0; }
        .success { color: #13a813; }
        .error { color: #e74c3c; }
    </style>
</head>
<body>
<div class="box">
    <h2>Reset Password</h2>
    <?php if (!empty($error)): ?>
        <div class="msg error"><?= $error ?></div>
    <?php endif ?>
    <?php if (!empty($success)): ?>
        <div class="msg success"><?= $success ?></div>
    <?php endif ?>
    <?php if (empty($success)): ?>
        <?php if (isset($is_valid) && !$is_valid): ?>
            <div class="msg error">Token tidak valid atau sudah kadaluwarsa.</div>
        <?php else: ?>
        <form method="post" action="/reset_password">
            <input type="hidden" name="token" value="<?= esc($token) ?>">
            <input type="password" name="password" placeholder="Password Baru" required>
            <input type="password" name="password2" placeholder="Ulangi Password Baru" required>
            <button type="submit">Reset Password</button>
        </form>
        <?php endif ?>
    <?php endif ?>
</div>
</body>
</html>