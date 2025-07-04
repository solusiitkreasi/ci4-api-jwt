<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>{subject}</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { background: #f6f6f6; font-family: Arial, sans-serif; margin:0; padding:0; }
    .container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 8px; padding: 32px 24px; box-shadow: 0 2px 8px rgba(0,0,0,.04);}
    .header { text-align:center; }
    .header img { max-width: 80px; margin-bottom: 16px; }
    .title { font-size: 22px; color: #222; margin-bottom: 8px; }
    .content { font-size: 16px; color: #444; margin: 24px 0; }
    .button { display: inline-block; padding: 12px 24px; background:rgb(0, 54, 43); color: #fff !important; border-radius: 4px; text-decoration: none; font-weight: bold;}
    .ii a[href] {
        color: #ffffff;
    }
    .footer { margin-top:32px; font-size:13px; color:#888; text-align:center;}
    @media (max-width:600px) { .container{padding:18px 6px;} }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="https://place-hold.it/150x75/00362b/fff/fff?text=TOL%20API&fontsize=20&bold" alt="Logo">
    </div>
    <div class="title">Aktivasi Akun</div>
    <div class="content">
      <?= $message ?>
      <?= $action_button ?>
      <br><br>
      Email ini dikirim otomatis, mohon tidak membalas.
    </div>
    <div class="footer">
      TOL API &copy; <?= date('Y') ?>. All Rights Reserved.
    </div>
  </div>
</body>
</html>
