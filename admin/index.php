<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => true,
    'cookie_samesite' => 'Strict',
]);
require_once __DIR__ . '/config.php';

if (isset($_POST['password'])) {
    if (password_verify($_POST['password'], ADMIN_PASSWORD_HASH)) {
        $_SESSION['pp_auth'] = true;
        header('Location: editor.php');
        exit;
    }
    $loginError = true;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['pp_auth'])) {
    header('Location: editor.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Content Admin — PlainPress</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #F9F8F5;
    }
    h1, h2, h3, .font-heading {
      font-family: 'Montserrat', sans-serif;
    }
    .pp-card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(30, 58, 95, 0.10), 0 1px 4px rgba(30, 58, 95, 0.06);
      max-width: 400px;
      width: 100%;
    }
    .pp-card-header {
      background-color: #1E3A5F;
      border-radius: 16px 16px 0 0;
      padding: 32px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
    }
    .pp-cross {
      position: relative;
      width: 22px;
      height: 22px;
      flex-shrink: 0;
    }
    .pp-cross::before,
    .pp-cross::after {
      content: '';
      position: absolute;
      background-color: #B91C1C;
      border-radius: 2px;
    }
    .pp-cross::before {
      width: 4px;
      height: 22px;
      top: 0;
      left: 9px;
    }
    .pp-cross::after {
      width: 22px;
      height: 4px;
      top: 9px;
      left: 0;
    }
    .pp-label {
      font-family: 'Montserrat', sans-serif;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.55);
    }
    .pp-card-title {
      font-family: 'Montserrat', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: #ffffff;
      text-align: center;
      line-height: 1.2;
    }
    .pp-card-body {
      padding: 32px;
    }
    .pp-input-label {
      display: block;
      font-family: 'Inter', sans-serif;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #1E3A5F;
      margin-bottom: 8px;
    }
    .pp-input {
      width: 100%;
      height: 48px;
      padding: 0 16px;
      border: 1.5px solid #E2E6EA;
      border-radius: 16px;
      font-family: 'Inter', sans-serif;
      font-size: 15px;
      color: #1E3A5F;
      background: #ffffff;
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s;
      box-sizing: border-box;
    }
    .pp-input:focus {
      border-color: #1E3A5F;
      box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.18);
    }
    .pp-input::placeholder {
      color: #B0B8C4;
    }
    .pp-btn {
      display: block;
      width: 100%;
      height: 48px;
      background-color: #1E3A5F;
      color: #ffffff;
      font-family: 'Montserrat', sans-serif;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: background-color 0.15s;
      margin-top: 24px;
    }
    .pp-btn:hover {
      background-color: #0A3070;
    }
    .pp-btn:active {
      background-color: #041530;
    }
    .pp-error {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #FEF2F2;
      border: 1px solid #FECACA;
      border-radius: 10px;
      padding: 10px 14px;
      margin-bottom: 20px;
      color: #B91C1C;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
    }
    .pp-error svg {
      flex-shrink: 0;
    }
    .pp-page-footer {
      text-align: center;
      margin-top: 28px;
      font-family: 'Inter', sans-serif;
      font-size: 12px;
      color: rgba(30, 58, 95, 0.38);
      letter-spacing: 0.01em;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6">

  <div class="pp-card">

    <!-- Card header -->
    <div class="pp-card-header">
      <img src="../images/logo-white.png" alt="School Logo" style="height:52px;width:auto;">
      <h1 class="pp-card-title" style="font-size:15px;font-weight:600;letter-spacing:0.01em;opacity:0.75;margin-top:4px;">Content Admin</h1>
    </div>

    <!-- Card body -->
    <div class="pp-card-body">

      <?php if (!empty($loginError)): ?>
      <div class="pp-error">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <circle cx="8" cy="8" r="7.25" stroke="#B91C1C" stroke-width="1.5"/>
          <path d="M8 4.5v4" stroke="#B91C1C" stroke-width="1.5" stroke-linecap="round"/>
          <circle cx="8" cy="11" r="0.75" fill="#B91C1C"/>
        </svg>
        Incorrect password — try again.
      </div>
      <?php endif; ?>

      <form method="POST" autocomplete="off" novalidate>
        <label class="pp-input-label" for="password">Password</label>
        <input
          id="password"
          type="password"
          name="password"
          class="pp-input"
          placeholder="Enter admin password"
          required
          autofocus
        >
        <button type="submit" class="pp-btn">Sign In</button>
      </form>

    </div>
  </div>

  <p class="pp-page-footer">PlainPress — School Website Boilerplate</p>

</body>
</html>
