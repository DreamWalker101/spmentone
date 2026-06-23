<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => true,
    'cookie_samesite' => 'Strict',
]);
if (!isset($_SESSION['pp_auth'])) {
    header('Location: index.php');
    exit;
}
$contentFile = __DIR__ . '/../content.json';
$c = file_exists($contentFile) ? json_decode(file_get_contents($contentFile), true) : [];
function v($val, $default = '') { return htmlspecialchars($val ?? $default, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>St Patrick's Primary School — Content Editor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1F4C23',
            accent:  '#C72027',
            bg:      '#F9F8F5',
            bgTint:  '#ECEBE6',
          },
          fontFamily: {
            heading: ['Montserrat', 'sans-serif'],
            body:    ['Inter', 'sans-serif'],
          }
        }
      }
    }
  </script>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: #F9F8F5; color: #1F4C23; }

    /* ── Tab bar ── */
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .tab-btn {
      padding: 8px 20px;
      border-radius: 999px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      color: rgba(30,58,95,0.5);
      cursor: pointer;
      border: none;
      background: transparent;
      transition: background 0.15s, color 0.15s, box-shadow 0.15s;
      white-space: nowrap;
    }
    .tab-btn.active {
      background: #C72027;
      color: #ffffff;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(185,28,28,0.25);
    }
    .tab-btn:hover:not(.active) { background: rgba(30,58,95,0.06); color: #1F4C23; }

    /* ── Card sections ── */
    .section-card {
      background: #ffffff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 1px 4px rgba(30,58,95,0.07);
      margin-bottom: 20px;
    }
    .section-title {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: 16px;
      color: #1F4C23;
      margin-bottom: 4px;
    }
    .section-subtitle {
      font-size: 13px;
      color: rgba(30,58,95,0.5);
      margin-bottom: 20px;
    }
    .divider { height: 1px; background: #E2E6EA; margin: 20px 0; }

    /* ── Group cards (quote / tour groups) ── */
    .group-card {
      background: #ECEBE6;
      border-radius: 10px;
      padding: 16px;
      margin-bottom: 12px;
    }
    .group-label {
      font-family: 'Inter', sans-serif;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: #C72027;
      margin-bottom: 12px;
    }

    /* ── Fields ── */
    .field-label {
      display: block;
      font-family: 'Inter', sans-serif;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: rgba(30,58,95,0.55);
      margin-bottom: 6px;
    }
    .field-input {
      width: 100%;
      border: 1.5px solid #E2E6EA;
      border-radius: 10px;
      padding: 0 14px;
      height: 44px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      background: #ffffff;
      color: #1F4C23;
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    .field-input:focus {
      outline: none;
      border-color: #1F4C23;
      box-shadow: 0 0 0 2px rgba(30,58,95,0.15);
    }
    textarea.field-input {
      height: auto;
      min-height: 80px;
      padding: 12px 14px;
      resize: vertical;
    }
    .field-hint {
      font-size: 12px;
      color: rgba(30,58,95,0.45);
      margin-top: 6px;
      line-height: 1.5;
    }

    /* ── Toggle ── */
    .toggle-row {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 4px;
    }
    .toggle-switch {
      position: relative;
      width: 44px;
      height: 24px;
      flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-track {
      position: absolute;
      inset: 0;
      background: #D1D5DB;
      border-radius: 999px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .toggle-track::after {
      content: '';
      position: absolute;
      top: 3px; left: 3px;
      width: 18px; height: 18px;
      background: white;
      border-radius: 50%;
      transition: transform 0.2s;
    }
    .toggle-switch input:checked + .toggle-track { background: #1F4C23; }
    .toggle-switch input:checked + .toggle-track::after { transform: translateX(20px); }
    .toggle-label { font-size: 14px; font-family: 'Inter', sans-serif; color: #1F4C23; }


    /* ── Split layout ── */
    .page-editor-split { display: flex; gap: 36px; align-items: flex-start; }
    .page-editor-left { flex: 1; min-width: 0; }
    .page-editor-right { width: 640px; flex-shrink: 0; position: sticky; top: 80px; }
    .preview-panel { background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(30,58,95,0.07); }
    .preview-panel-header { padding: 12px 16px; border-bottom: 1px solid #E2E6EA; display: flex; align-items: center; justify-content: space-between; }
    .preview-panel-title { font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 13px; color: #1F4C23; }
    .preview-panel-hint { font-size: 11px; color: rgba(30,58,95,0.45); margin-top: 2px; }
    /* ── Static section preview ── */
    .section-preview-wrap { background: #ECEBE6; border-radius: 0 0 8px 8px; overflow: hidden; min-height: 300px; }
    .section-preview-img { width: 100%; display: block; }
    .section-preview-ph { display: none; flex-direction: column; align-items: center; justify-content: center; min-height: 300px; padding: 40px 28px; text-align: center; gap: 10px; }
    .section-preview-ph-icon { width: 40px; height: 40px; background: rgba(30,58,95,0.08); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 4px; }
    .section-preview-ph-title { font-family: 'Montserrat', sans-serif; font-size: 14px; font-weight: 700; color: #1F4C23; }
    .section-preview-ph-hint { font-size: 12px; color: rgba(30,58,95,0.45); line-height: 1.7; max-width: 260px; }
    .section-preview-ph-hint code { background: #E2E6EA; border-radius: 4px; padding: 1px 6px; font-family: monospace; font-size: 11px; color: #1F4C23; }
    .preview-panel-footer { padding: 8px 16px; font-size: 11px; color: rgba(30,58,95,0.4); border-top: 1px solid #E2E6EA; text-align: center; }

    /* ── Section nav ── */
    .section-nav { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 16px; scrollbar-width: none; }
    .section-nav::-webkit-scrollbar { display: none; }
    .section-nav-btn { flex-shrink: 0; padding: 6px 14px; border-radius: 999px; font-size: 12px; font-family: 'Inter', sans-serif; font-weight: 500; color: rgba(30,58,95,0.5); cursor: pointer; border: none; background: transparent; transition: background 0.15s, color 0.15s; white-space: nowrap; }
    .section-nav-btn.active { background: #1F4C23; color: #ffffff; }
    .section-nav-btn:hover:not(.active) { background: #ECEBE6; color: #1F4C23; }

    /* ── Section panels ── */
    .section-panel { display: none; }
    .section-panel.active { display: block; }
    .tab-save-row { display: flex; justify-content: flex-end; margin-top: 8px; padding-bottom: 32px; }

    /* Media picker row */
    .img-picker-row {
      display: flex;
      align-items: center;
      gap: 12px;
      background: #ECEBE6;
      border-radius: 10px;
      padding: 10px 12px;
    }
    .img-picker-preview {
      width: 64px;
      height: 64px;
      border-radius: 8px;
      background: #E2E6EA;
      flex-shrink: 0;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .img-picker-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .img-picker-preview .no-img-icon {
      color: #9CA3AF;
      font-size: 24px;
    }
    .img-picker-actions {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .img-picker-btn {
      background: #1F4C23;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 6px 14px;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      cursor: pointer;
      white-space: nowrap;
    }
    .img-picker-btn:hover { background: #0D3D17; }
    .img-picker-clear {
      background: transparent;
      border: 1.5px solid #E2E6EA;
      border-radius: 8px;
      padding: 4px 10px;
      font-size: 12px;
      color: #9CA3AF;
      cursor: pointer;
    }
    .img-picker-clear:hover { border-color: #DC2626; color: #DC2626; }
    .img-picker-filename {
      font-size: 12px;
      color: rgba(30,58,95,0.45);
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* Media modal */
    #media-modal {
      position: fixed;
      inset: 0;
      z-index: 9000;
      background: rgba(30,58,95,0.55);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s;
    }
    #media-modal.open {
      opacity: 1;
      pointer-events: all;
    }
    .media-modal-box {
      background: #fff;
      border-radius: 16px;
      width: 720px;
      max-width: 95vw;
      max-height: 85vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 20px 60px rgba(30,58,95,0.25);
      overflow: hidden;
    }
    .media-modal-header {
      padding: 20px 24px 16px;
      border-bottom: 1px solid #E2E6EA;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .media-modal-title {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: 16px;
      color: #1F4C23;
    }
    .media-modal-close {
      width: 32px; height: 32px;
      border: none; background: #ECEBE6;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      color: #1F4C23;
      display: flex; align-items: center; justify-content: center;
    }
    .media-modal-toolbar {
      padding: 12px 24px;
      border-bottom: 1px solid #E2E6EA;
      display: flex;
      gap: 10px;
    }
    .media-modal-upload-btn {
      background: #C72027;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 8px 18px;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      font-weight: 600;
      cursor: pointer;
    }
    .media-modal-upload-btn:hover { background: #A01B1B; }
    .media-modal-grid {
      flex: 1;
      overflow-y: auto;
      padding: 16px 24px;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 10px;
      align-content: start;
    }
    .media-thumb {
      aspect-ratio: 1;
      border-radius: 8px;
      overflow: hidden;
      cursor: pointer;
      border: 2.5px solid transparent;
      position: relative;
      background: #ECEBE6;
      transition: border-color 0.15s;
    }
    .media-thumb:hover { border-color: #1F4C23; }
    .media-thumb.selected { border-color: #C72027; }
    .media-thumb img {
      width: 100%; height: 100%;
      object-fit: cover;
      display: block;
    }
    .media-thumb-label {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      background: rgba(30,58,95,0.7);
      color: white;
      font-size: 10px;
      padding: 3px 5px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      opacity: 0;
      transition: opacity 0.15s;
    }
    .media-thumb:hover .media-thumb-label { opacity: 1; }
    .media-modal-footer {
      padding: 14px 24px;
      border-top: 1px solid #E2E6EA;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .media-modal-selected-info {
      font-size: 13px;
      color: rgba(30,58,95,0.55);
      flex: 1;
    }
    .media-modal-insert-btn {
      background: #1F4C23;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 8px 20px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      font-weight: 600;
      cursor: pointer;
    }
    .media-modal-insert-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .media-empty {
      grid-column: 1 / -1;
      text-align: center;
      padding: 40px;
      color: rgba(30,58,95,0.35);
      font-size: 14px;
    }

    /* ── Save button ── */
    .save-btn {
      background: #1F4C23;
      color: #ffffff;
      font-family: 'Montserrat', sans-serif;
      font-weight: 600;
      font-size: 14px;
      height: 44px;
      padding: 0 28px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background 0.15s, opacity 0.15s;
    }
    .save-btn:hover { background: #0D3D17; }
    .save-btn:disabled { opacity: 0.55; cursor: not-allowed; }

    /* ── Toast ── */
    #toast {
      position: fixed;
      bottom: 28px;
      right: 28px;
      z-index: 9999;
      width: 320px;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(30,58,95,0.18);
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: opacity 0.3s, transform 0.3s;
      opacity: 0;
      transform: translateY(12px);
      pointer-events: none;
    }
    #toast.show {
      opacity: 1;
      transform: translateY(0);
    }
    #toast .toast-bar {
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 4px;
      border-radius: 16px 0 0 16px;
    }
    #toast .toast-bar.success { background: #16A34A; }
    #toast .toast-bar.error   { background: #DC2626; }
    #toast-msg {
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      color: #1F4C23;
      font-weight: 500;
    }
  </style>
</head>
<body class="min-h-screen">

  <!-- ═══════════════════════════════════════ TOP NAV ═══ -->
  <header style="position:sticky;top:0;z-index:50;background:#ffffff;border-bottom:1px solid #E2E6EA;box-shadow:0 1px 4px rgba(30,58,95,0.06);">
    <div style="max-width:1800px;margin:0 auto;padding:0 48px;height:60px;display:flex;align-items:center;justify-content:space-between;">

      <!-- Left: combined school logo + label -->
      <div style="display:flex;align-items:center;gap:16px;">
        <img src="../assets/images/St-Patricks-Mentone-vertical-lock-up-COLOUR.png" alt="St Patrick's Primary School" style="height:38px;width:auto;">
        <span style="width:1px;height:20px;background:#E2E6EA;display:block;flex-shrink:0;"></span>
        <span style="font-family:'Inter',sans-serif;font-size:13px;color:rgba(30,58,95,0.45);font-weight:400;letter-spacing:0.01em;">Content Editor</span>
      </div>

      <!-- Right: actions -->
      <div style="display:flex;align-items:center;gap:4px;">
        <a href="../index.html" target="_blank"
           style="display:flex;align-items:center;gap:6px;font-family:'Inter',sans-serif;font-size:13px;color:rgba(30,58,95,0.55);text-decoration:none;padding:6px 14px;border-radius:8px;transition:background 0.15s,color 0.15s;"
           onmouseover="this.style.background='#ECEBE6';this.style.color='#1F4C23';"
           onmouseout="this.style.background='transparent';this.style.color='rgba(30,58,95,0.55)';">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          Preview Site
        </a>
        <a href="index.php?logout=1"
           style="display:flex;align-items:center;gap:6px;font-family:'Inter',sans-serif;font-size:13px;color:rgba(30,58,95,0.55);text-decoration:none;padding:6px 14px;border-radius:8px;transition:background 0.15s,color 0.15s;"
           onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
           onmouseout="this.style.background='transparent';this.style.color='rgba(30,58,95,0.55)';">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </div>
    </div>
  </header>

  <!-- ═══════════════════════════════════════ MAIN CONTENT ═══ -->
  <div style="max-width:1800px;margin:0 auto;padding:32px 48px 64px;">

    <!-- Tab bar -->
    <div style="background:#ECEBE6;border-radius:999px;padding:5px;display:flex;gap:4px;margin-bottom:32px;overflow-x:auto;">
      <?php
      $tabs = [
        'global'      => 'Global',
        'homepage'    => 'Homepage',
        'about'       => 'About',
        'learning'    => 'Learning',
        'community'   => 'Community',
        'enrolments'  => 'Enrolments',
        'contact'     => 'Contact',
        'policies'    => 'Policies',
        'access'      => 'Access',
      ];
      $first = true;
      foreach ($tabs as $key => $label):
      ?>
      <button onclick="switchTab('<?= $key ?>', this)"
        class="tab-btn <?= $first ? 'active' : '' ?>">
        <?= $label ?>
      </button>
      <?php $first = false; endforeach; ?>
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- GLOBAL TAB                                              -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-global" class="tab-panel active">
      <div class="section-card">
        <div class="section-title">School Details</div>
        <div class="section-subtitle">Contact information and social links shown sitewide.</div>

        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="g-phone">Phone</label>
            <input type="text" id="g-phone" class="field-input"
              value="<?= v($c['global']['phone'] ?? '(XX) XXXX XXXX') ?>">
          </div>
          <div>
            <label class="field-label" for="g-email">Email</label>
            <input type="email" id="g-email" class="field-input"
              value="<?= v($c['global']['email'] ?? 'admin@yourschool.edu.au') ?>">
          </div>
          <div>
            <label class="field-label" for="g-address">Address</label>
            <input type="text" id="g-address" class="field-input"
              value="<?= v($c['global']['address'] ?? '123 School Street, Suburb STATE POSTCODE') ?>">
          </div>
          <div>
            <label class="field-label" for="g-facebook">Facebook URL</label>
            <input type="url" id="g-facebook" class="field-input"
              value="<?= v($c['global']['facebookUrl'] ?? '#') ?>">
          </div>
          <div>
            <label class="field-label" for="g-principal">Principal Name</label>
            <input type="text" id="g-principal" class="field-input"
              value="<?= v($c['global']['principalName'] ?? 'Principal Name') ?>">
          </div>
          <div>
            <label class="field-label" for="g-officehours">Office Hours</label>
            <input type="text" id="g-officehours" class="field-input"
              value="<?= v($c['global']['officeHours'] ?? 'Monday to Friday · 8:00am – 4:00pm') ?>"
              placeholder="Monday to Friday · 8:00am – 4:00pm">
          </div>
          <div>
            <label class="field-label" for="g-footer-tagline">Footer Tagline</label>
            <input type="text" id="g-footer-tagline" class="field-input"
              value="<?= v($c['global']['footerTagline'] ?? '') ?>"
              placeholder="Short tagline shown in the footer">
          </div>
          <div>
            <label class="field-label" for="g-newsletter-url">Newsletter URL</label>
            <input type="url" id="g-newsletter-url" class="field-input"
              value="<?= v($c['global']['newsletterUrl'] ?? '') ?>"
              placeholder="https://…">
          </div>
          <div>
            <label class="field-label" for="g-annual-report-url">Annual Report URL</label>
            <input type="url" id="g-annual-report-url" class="field-input"
              value="<?= v($c['global']['annualReportUrl'] ?? '') ?>"
              placeholder="https://…">
          </div>
        </div>

        <div class="divider"></div>
        <div style="display:flex;justify-content:flex-end;">
          <button class="save-btn" onclick="saveTab('global', event)">Save Changes</button>
        </div>
      </div>
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- HOMEPAGE TAB                                            -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-homepage" class="tab-panel">
      <div class="page-editor-split">
        <div class="page-editor-left">
          <nav class="section-nav">
            <button class="section-nav-btn active" onclick="showSection('homepage','hero','pp-section-hero',this)">Hero</button>
            <button class="section-nav-btn" onclick="showSection('homepage','quotes','pp-section-quotes',this)">Quotes Carousel</button>
            <button class="section-nav-btn" onclick="showSection('homepage','welcome','pp-section-welcome',this)">Welcome Block</button>
            <button class="section-nav-btn" onclick="showSection('homepage','pillars','pp-section-pillars',this)">Pillars</button>
            <button class="section-nav-btn" onclick="showSection('homepage','effect','pp-section-effect',this)">Effect Section</button>
            <button class="section-nav-btn" onclick="showSection('homepage','testimonials','pp-section-testimonials',this)">Testimonials</button>
            <button class="section-nav-btn" onclick="showSection('homepage','videos','pp-section-videos',this)">Video Grid</button>
            <button class="section-nav-btn" onclick="showSection('homepage','cta','pp-section-enrol-cta',this)">Enrolments CTA</button>
          </nav>

      <!-- Hero -->
      <div id="homepage-sec-hero" class="section-panel active">
      <div class="section-card">
        <div class="section-title">Hero</div>
        <div class="section-subtitle">Main animated heading and background media.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="hp-hero-heading">Hero Heading</label>
            <textarea id="hp-hero-heading" class="field-input"><?= v($c['homepage']['hero']['heroHeading'] ?? '') ?></textarea>
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="hp-hero-video">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('hp-hero-video')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('hp-hero-video')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="hp-hero-video" value="<?= v($c['homepage']['hero']['heroVideo'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /homepage-sec-hero -->

      <div id="homepage-sec-quotes" class="section-panel">
      <!-- Quotes Carousel -->
      <div class="section-card">
        <div class="section-title">Quotes Carousel</div>
        <div class="section-subtitle">Rotating quotes shown on the homepage.</div>

        <?php for ($i = 0; $i < 6; $i++):
          $q = $c['homepage']['quotes'][$i] ?? [];
        ?>
        <div class="group-card">
          <div class="group-label">Quote <?= $i + 1 ?></div>
          <div style="display:grid;gap:12px;">
            <div>
              <label class="field-label" for="quote-<?= $i ?>-text">Quote Text</label>
              <textarea id="quote-<?= $i ?>-text" class="field-input"><?= v($q['text'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="field-label" for="quote-<?= $i ?>-author">Author</label>
              <input type="text" id="quote-<?= $i ?>-author" class="field-input"
                value="<?= v($q['author'] ?? '') ?>">
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
      </div><!-- /homepage-sec-quotes -->

      <div id="homepage-sec-welcome" class="section-panel">
      <!-- Welcome Block -->
      <div class="section-card">
        <div class="section-title">Welcome Block</div>
        <div class="section-subtitle">Intro text block below the hero.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="hp-welcome-heading">Welcome Heading</label>
            <input type="text" id="hp-welcome-heading" class="field-input"
              value="<?= v($c['homepage']['welcomeBlock']['welcomeHeading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="hp-welcome-tagline">Tagline (script font)</label>
            <input type="text" id="hp-welcome-tagline" class="field-input"
              value="<?= v($c['homepage']['welcomeBlock']['welcomeTagline'] ?? '') ?>">
          </div>
        </div>
      </div>
      </div><!-- /homepage-sec-welcome -->

      <div id="homepage-sec-pillars" class="section-panel">
      <!-- Pillars 1–4 -->
      <div class="section-card">
        <div class="section-title">Pillars</div>
        <div class="section-subtitle">Four core value pillars shown in alternating sections.</div>
        <?php
        $pillarNames = ['Faith & Formation', 'Belonging & Wellbeing', 'Teaching that Inspires', 'Community Partnership'];
        for ($i = 0; $i < 4; $i++):
          $p = $c['homepage']['pillars'][$i] ?? [];
        ?>
        <div class="group-card">
          <div class="group-label">Pillar <?= $i + 1 ?> — <?= $pillarNames[$i] ?></div>
          <div style="display:grid;gap:12px;">
            <div>
              <label class="field-label" for="hp-pillar-<?= $i ?>-heading">Heading</label>
              <input type="text" id="hp-pillar-<?= $i ?>-heading" class="field-input"
                value="<?= v($p['heading'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="hp-pillar-<?= $i ?>-body">Body</label>
              <textarea id="hp-pillar-<?= $i ?>-body" class="field-input"><?= v($p['body'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="field-label" for="hp-pillar-<?= $i ?>-tagline">Tagline (script font)</label>
              <input type="text" id="hp-pillar-<?= $i ?>-tagline" class="field-input"
                value="<?= v($p['tagline'] ?? '') ?>">
            </div>
            <div style="margin-bottom:16px;">
              <label class="field-label">Image / Video</label>
              <div class="img-picker-row" data-field="hp-pillar-<?= $i ?>-image">
                <div class="img-picker-preview"></div>
                <div class="img-picker-actions">
                  <button type="button" class="img-picker-btn" onclick="openMediaPicker('hp-pillar-<?= $i ?>-image')">Choose Image</button>
                  <button type="button" class="img-picker-clear" onclick="clearImage('hp-pillar-<?= $i ?>-image')" title="Remove">&#x2715;</button>
                </div>
                <input type="hidden" id="hp-pillar-<?= $i ?>-image" value="<?= v($p['image'] ?? '') ?>">
                <span class="img-picker-filename">No image selected</span>
              </div>
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
      </div><!-- /homepage-sec-pillars -->

      <div id="homepage-sec-effect" class="section-panel">
      <!-- The School Effect -->
      <div class="section-card">
        <div class="section-title">The School Effect</div>
        <div class="section-subtitle">Impact statement section.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="hp-effect-label">Eyebrow Label</label>
            <input type="text" id="hp-effect-label" class="field-input"
              value="<?= v($c['homepage']['effect']['effectLabel'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="hp-effect-heading">Heading</label>
            <input type="text" id="hp-effect-heading" class="field-input"
              value="<?= v($c['homepage']['effect']['effectHeading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="hp-effect-body">Body</label>
            <textarea id="hp-effect-body" class="field-input"><?= v($c['homepage']['effect']['effectBody'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      </div><!-- /homepage-sec-effect -->

      <div id="homepage-sec-testimonials" class="section-panel">
      <!-- Testimonials -->
      <div class="section-card">
        <div class="section-title">Testimonials</div>
        <div class="section-subtitle">Three parent / community testimonials.</div>
        <?php for ($i = 0; $i < 3; $i++):
          $t = $c['homepage']['testimonials'][$i] ?? [];
        ?>
        <div class="group-card">
          <div class="group-label">Testimonial <?= $i + 1 ?></div>
          <div style="display:grid;gap:12px;">
            <div>
              <label class="field-label" for="hp-test-<?= $i ?>-quote">Quote</label>
              <textarea id="hp-test-<?= $i ?>-quote" class="field-input"><?= v($t['quote'] ?? '') ?></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div>
                <label class="field-label" for="hp-test-<?= $i ?>-name">Name</label>
                <input type="text" id="hp-test-<?= $i ?>-name" class="field-input"
                  value="<?= v($t['name'] ?? '') ?>">
              </div>
              <div>
                <label class="field-label" for="hp-test-<?= $i ?>-role">Role</label>
                <input type="text" id="hp-test-<?= $i ?>-role" class="field-input"
                  value="<?= v($t['role'] ?? '') ?>">
              </div>
            </div>
            <div style="margin-bottom:16px;">
              <label class="field-label">Image / Video</label>
              <div class="img-picker-row" data-field="hp-test-<?= $i ?>-image">
                <div class="img-picker-preview"></div>
                <div class="img-picker-actions">
                  <button type="button" class="img-picker-btn" onclick="openMediaPicker('hp-test-<?= $i ?>-image')">Choose Image</button>
                  <button type="button" class="img-picker-clear" onclick="clearImage('hp-test-<?= $i ?>-image')" title="Remove">&#x2715;</button>
                </div>
                <input type="hidden" id="hp-test-<?= $i ?>-image" value="<?= v($t['image'] ?? '') ?>">
                <span class="img-picker-filename">No image selected</span>
              </div>
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
      </div><!-- /homepage-sec-testimonials -->

      <div id="homepage-sec-videos" class="section-panel">
      <!-- Video Grid -->
      <div class="section-card">
        <div class="section-title">Video Grid</div>
        <div class="section-subtitle">Three featured video cards.</div>
        <div style="display:grid;gap:16px;margin-bottom:20px;">
          <div>
            <label class="field-label" for="hp-videos-label">Section Label</label>
            <input type="text" id="hp-videos-label" class="field-input"
              value="<?= v($c['homepage']['videoGrid']['videosLabel'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="hp-videos-heading">Section Heading</label>
            <input type="text" id="hp-videos-heading" class="field-input"
              value="<?= v($c['homepage']['videoGrid']['videosHeading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="hp-videos-body">Section Body</label>
            <textarea id="hp-videos-body" class="field-input"><?= v($c['homepage']['videoGrid']['videosBody'] ?? '') ?></textarea>
          </div>
        </div>
        <?php for ($i = 0; $i < 3; $i++):
          $vc = $c['homepage']['videoCards'][$i] ?? [];
        ?>
        <div class="group-card">
          <div class="group-label">Video Card <?= $i + 1 ?></div>
          <div style="display:grid;gap:12px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div>
                <label class="field-label" for="hp-vc-<?= $i ?>-category">Category</label>
                <input type="text" id="hp-vc-<?= $i ?>-category" class="field-input"
                  value="<?= v($vc['category'] ?? '') ?>">
              </div>
              <div>
                <label class="field-label" for="hp-vc-<?= $i ?>-duration">Duration</label>
                <input type="text" id="hp-vc-<?= $i ?>-duration" class="field-input"
                  value="<?= v($vc['duration'] ?? '') ?>" placeholder="e.g. 2:34">
              </div>
            </div>
            <div>
              <label class="field-label" for="hp-vc-<?= $i ?>-title">Title</label>
              <input type="text" id="hp-vc-<?= $i ?>-title" class="field-input"
                value="<?= v($vc['title'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="hp-vc-<?= $i ?>-videosrc">Video File URL</label>
              <input type="url" id="hp-vc-<?= $i ?>-videosrc" class="field-input"
                value="<?= v($vc['videoSrc'] ?? '') ?>" placeholder="https://… or images/video.mp4">
              <p class="field-hint">Direct URL to the video file.</p>
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
      </div><!-- /homepage-sec-videos -->

      <div id="homepage-sec-cta" class="section-panel">
      <!-- Enrolments CTA -->
      <div class="section-card">
        <div class="section-title">Enrolments CTA</div>
        <div class="section-subtitle">Call-to-action panel linking to the enrolments page.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="hp-cta-label">Eyebrow Label</label>
            <input type="text" id="hp-cta-label" class="field-input"
              value="<?= v($c['homepage']['enrolmentsCta']['ctaLabel'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="hp-cta-heading">Heading</label>
            <input type="text" id="hp-cta-heading" class="field-input"
              value="<?= v($c['homepage']['enrolmentsCta']['ctaHeading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="hp-cta-body">Body</label>
            <textarea id="hp-cta-body" class="field-input"><?= v($c['homepage']['enrolmentsCta']['ctaBody'] ?? '') ?></textarea>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
              <label class="field-label" for="hp-cta-btn-text">Button Text</label>
              <input type="text" id="hp-cta-btn-text" class="field-input"
                value="<?= v($c['homepage']['enrolmentsCta']['ctaBtnText'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="hp-cta-btn-url">Button URL</label>
              <input type="url" id="hp-cta-btn-url" class="field-input"
                value="<?= v($c['homepage']['enrolmentsCta']['ctaBtnUrl'] ?? '') ?>">
            </div>
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="hp-cta-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('hp-cta-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('hp-cta-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="hp-cta-image" value="<?= v($c['homepage']['enrolmentsCta']['ctaImage'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /homepage-sec-cta -->


        </div><!-- /page-editor-left -->

        <div class="page-editor-right">
          <div class="preview-panel">
            <div class="preview-panel-header">
              <div>
                <span class="preview-panel-title">Page Preview</span>
                <div class="preview-panel-hint" id="homepage-preview-hint">Hero</div>
              </div>
              <button class="save-btn" onclick="saveTab('homepage', event)" style="height:38px;padding:0 20px;font-size:13px;">Save Changes</button>
            </div>
            <div class="section-preview-wrap">
              <img id="preview-img-homepage" class="section-preview-img" src="../images/admin-previews/homepage-hero.jpg" alt=""
                   onerror="this.style.display='none';document.getElementById('preview-ph-homepage').style.display='flex'">
              <div id="preview-ph-homepage" class="section-preview-ph">
                <div class="section-preview-ph-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="2" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/></svg></div>
                <p class="section-preview-ph-title" id="preview-ph-name-homepage">Hero</p>
                <span class="section-preview-ph-hint">Run <code>node site/admin/capture-previews.js</code> once to generate section screenshots.</span>
              </div>
            </div>
            <div class="preview-panel-footer">📸 Visual reference only — screenshots do not update when you save</div>
          </div>
        </div><!-- /page-editor-right -->
      </div><!-- /page-editor-split -->
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- ABOUT TAB                                               -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-about" class="tab-panel">
      <div class="page-editor-split">
        <div class="page-editor-left">
          <nav class="section-nav">
            <button class="section-nav-btn active" onclick="showSection('about','hero','pp-section-hero',this)">Hero</button>
            <button class="section-nav-btn" onclick="showSection('about','smv','pp-section-smv',this)">Story / Mission / Vision</button>
            <button class="section-nav-btn" onclick="showSection('about','intro','pp-section-about-intro',this)">About Intro</button>
            <button class="section-nav-btn" onclick="showSection('about','principal','pp-section-principal',this)">Principal</button>
            <button class="section-nav-btn" onclick="showSection('about','people','pp-section-people',this)">Our People</button>
            <button class="section-nav-btn" onclick="showSection('about','teams','pp-section-teams',this)">Team Sections</button>
            <button class="section-nav-btn" onclick="showSection('about','parish','pp-section-parish',this)">Parish</button>
          </nav>

      <div id="about-sec-hero" class="section-panel active">
      <!-- Hero -->
      <div class="section-card">
        <div class="section-title">Hero</div>
        <div class="section-subtitle">About page hero heading and background.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="ab-hero-heading">Hero Heading</label>
            <input type="text" id="ab-hero-heading" class="field-input"
              value="<?= v($c['about']['hero']['heroHeading'] ?? '') ?>">
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="ab-hero-video">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('ab-hero-video')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('ab-hero-video')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="ab-hero-video" value="<?= v($c['about']['hero']['heroVideo'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /about-sec-hero -->

      <div id="about-sec-smv" class="section-panel">
      <!-- Story / Mission / Vision -->
      <div class="section-card">
        <div class="section-title">Story, Mission &amp; Vision</div>
        <div class="section-subtitle">Three core statement panels.</div>
        <?php
        $smvNames = ['Our Story', 'Our Mission', 'Our Vision'];
        for ($i = 0; $i < 3; $i++):
          $smv = $c['about']['smv'][$i] ?? [];
        ?>
        <div class="group-card">
          <div class="group-label"><?= $smvNames[$i] ?></div>
          <div style="display:grid;gap:12px;">
            <div>
              <label class="field-label" for="ab-smv-<?= $i ?>-heading">Heading</label>
              <textarea id="ab-smv-<?= $i ?>-heading" class="field-input" style="min-height:60px;"><?= v($smv['heading'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="field-label" for="ab-smv-<?= $i ?>-body">Body</label>
              <textarea id="ab-smv-<?= $i ?>-body" class="field-input"><?= v($smv['body'] ?? '') ?></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div>
                <label class="field-label" for="ab-smv-<?= $i ?>-ctalabel">CTA Label</label>
                <input type="text" id="ab-smv-<?= $i ?>-ctalabel" class="field-input"
                  value="<?= v($smv['ctaLabel'] ?? '') ?>">
              </div>
              <div>
                <label class="field-label" for="ab-smv-<?= $i ?>-ctaurl">CTA URL</label>
                <input type="url" id="ab-smv-<?= $i ?>-ctaurl" class="field-input"
                  value="<?= v($smv['ctaUrl'] ?? '') ?>">
              </div>
            </div>
            <div style="margin-bottom:16px;">
              <label class="field-label">Image / Video</label>
              <div class="img-picker-row" data-field="ab-smv-<?= $i ?>-image">
                <div class="img-picker-preview"></div>
                <div class="img-picker-actions">
                  <button type="button" class="img-picker-btn" onclick="openMediaPicker('ab-smv-<?= $i ?>-image')">Choose Image</button>
                  <button type="button" class="img-picker-clear" onclick="clearImage('ab-smv-<?= $i ?>-image')" title="Remove">&#x2715;</button>
                </div>
                <input type="hidden" id="ab-smv-<?= $i ?>-image" value="<?= v($smv['image'] ?? '') ?>">
                <span class="img-picker-filename">No image selected</span>
              </div>
            </div>
            <div>
              <label class="field-label" for="ab-smv-<?= $i ?>-imagelabel">Image Caption</label>
              <input type="text" id="ab-smv-<?= $i ?>-imagelabel" class="field-input"
                value="<?= v($smv['imageLabel'] ?? '') ?>">
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
      </div><!-- /about-sec-smv -->

      <div id="about-sec-intro" class="section-panel">
      <!-- About Intro -->
      <div class="section-card">
        <div class="section-title">About Intro</div>
        <div class="section-subtitle">Introductory text block.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="ab-intro-heading">Heading</label>
            <input type="text" id="ab-intro-heading" class="field-input"
              value="<?= v($c['about']['aboutIntro']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="ab-intro-tagline">Tagline (script font)</label>
            <input type="text" id="ab-intro-tagline" class="field-input"
              value="<?= v($c['about']['aboutIntro']['tagline'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="ab-intro-body">Body</label>
            <textarea id="ab-intro-body" class="field-input"><?= v($c['about']['aboutIntro']['body'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      </div><!-- /about-sec-intro -->

      <div id="about-sec-principal" class="section-panel">
      <!-- Principal -->
      <div class="section-card">
        <div class="section-title">Principal's Message</div>
        <div class="section-subtitle">Featured message from the principal.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="ab-principal-heading">Section Label</label>
            <input type="text" id="ab-principal-heading" class="field-input"
              value="<?= v($c['about']['principal']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="ab-principal-name">Principal Name</label>
            <input type="text" id="ab-principal-name" class="field-input"
              value="<?= v($c['about']['principal']['name'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="ab-principal-body">Message</label>
            <textarea id="ab-principal-body" class="field-input" style="min-height:120px;"><?= v($c['about']['principal']['body'] ?? '') ?></textarea>
          </div>
          <div>
            <label class="field-label" for="ab-principal-signoff">Sign-off (script font)</label>
            <input type="text" id="ab-principal-signoff" class="field-input"
              value="<?= v($c['about']['principal']['signoff'] ?? '') ?>">
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="ab-principal-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('ab-principal-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('ab-principal-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="ab-principal-image" value="<?= v($c['about']['principal']['image'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /about-sec-principal -->

      <div id="about-sec-people" class="section-panel">
      <!-- People Intro -->
      <div class="section-card">
        <div class="section-title">People Intro</div>
        <div class="section-subtitle">Intro text before the team sections.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="ab-people-heading">Heading</label>
            <input type="text" id="ab-people-heading" class="field-input"
              value="<?= v($c['about']['peopleIntro']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="ab-people-script">Script Line</label>
            <input type="text" id="ab-people-script" class="field-input"
              value="<?= v($c['about']['peopleIntro']['script'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="ab-people-body">Body</label>
            <textarea id="ab-people-body" class="field-input"><?= v($c['about']['peopleIntro']['body'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      </div><!-- /about-sec-people -->

      <div id="about-sec-teams" class="section-panel">
      <!-- Team Sections -->
      <div class="section-card">
        <div class="section-title">Team Sections</div>
        <div class="section-subtitle">Four year-level team cards.</div>
        <?php
        $teamNames = ['Foundation', 'Mid Primary', 'Upper Primary', 'Specialist'];
        for ($i = 0; $i < 4; $i++):
          $team = $c['about']['teams'][$i] ?? [];
        ?>
        <div class="group-card">
          <div class="group-label"><?= $teamNames[$i] ?></div>
          <div style="display:grid;gap:12px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div>
                <label class="field-label" for="ab-team-<?= $i ?>-level">Level Label</label>
                <input type="text" id="ab-team-<?= $i ?>-level" class="field-input"
                  value="<?= v($team['level'] ?? '') ?>">
              </div>
              <div>
                <label class="field-label" for="ab-team-<?= $i ?>-heading">Heading</label>
                <input type="text" id="ab-team-<?= $i ?>-heading" class="field-input"
                  value="<?= v($team['heading'] ?? '') ?>">
              </div>
            </div>
            <div>
              <label class="field-label" for="ab-team-<?= $i ?>-body">Body</label>
              <textarea id="ab-team-<?= $i ?>-body" class="field-input"><?= v($team['body'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="field-label" for="ab-team-<?= $i ?>-quote">Quote</label>
              <textarea id="ab-team-<?= $i ?>-quote" class="field-input"><?= v($team['quote'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="field-label" for="ab-team-<?= $i ?>-caption">Caption</label>
              <input type="text" id="ab-team-<?= $i ?>-caption" class="field-input"
                value="<?= v($team['caption'] ?? '') ?>">
            </div>
            <div style="margin-bottom:16px;">
              <label class="field-label">Image / Video</label>
              <div class="img-picker-row" data-field="ab-team-<?= $i ?>-image">
                <div class="img-picker-preview"></div>
                <div class="img-picker-actions">
                  <button type="button" class="img-picker-btn" onclick="openMediaPicker('ab-team-<?= $i ?>-image')">Choose Image</button>
                  <button type="button" class="img-picker-clear" onclick="clearImage('ab-team-<?= $i ?>-image')" title="Remove">&#x2715;</button>
                </div>
                <input type="hidden" id="ab-team-<?= $i ?>-image" value="<?= v($team['image'] ?? '') ?>">
                <span class="img-picker-filename">No image selected</span>
              </div>
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
      </div><!-- /about-sec-teams -->

      <div id="about-sec-parish" class="section-panel">
      <!-- Parish Connection -->
      <div class="section-card">
        <div class="section-title">Parish Connection</div>
        <div class="section-subtitle">Section about the school's link to the local parish.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="ab-parish-heading">Heading</label>
            <input type="text" id="ab-parish-heading" class="field-input"
              value="<?= v($c['about']['parish']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="ab-parish-body">Body</label>
            <textarea id="ab-parish-body" class="field-input"><?= v($c['about']['parish']['body'] ?? '') ?></textarea>
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="ab-parish-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('ab-parish-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('ab-parish-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="ab-parish-image" value="<?= v($c['about']['parish']['image'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>

      </div>
      </div><!-- /about-sec-parish -->

        </div><!-- /page-editor-left -->

        <div class="page-editor-right">
          <div class="preview-panel">
            <div class="preview-panel-header">
              <div>
                <span class="preview-panel-title">Page Preview</span>
                <div class="preview-panel-hint" id="about-preview-hint">Hero</div>
              </div>
              <button class="save-btn" onclick="saveTab('about', event)" style="height:38px;padding:0 20px;font-size:13px;">Save Changes</button>
            </div>
            <div class="section-preview-wrap">
              <img id="preview-img-about" class="section-preview-img" src="../images/admin-previews/about-hero.jpg" alt=""
                   onerror="this.style.display='none';document.getElementById('preview-ph-about').style.display='flex'">
              <div id="preview-ph-about" class="section-preview-ph">
                <div class="section-preview-ph-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="2" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/></svg></div>
                <p class="section-preview-ph-title" id="preview-ph-name-about">Hero</p>
                <span class="section-preview-ph-hint">Run <code>node site/admin/capture-previews.js</code> once to generate section screenshots.</span>
              </div>
            </div>
            <div class="preview-panel-footer">📸 Visual reference only — screenshots do not update when you save</div>
          </div>
        </div><!-- /page-editor-right -->
      </div><!-- /page-editor-split -->
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- LEARNING TAB                                            -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-learning" class="tab-panel">
      <div class="page-editor-split">
        <div class="page-editor-left">
          <nav class="section-nav">
            <button class="section-nav-btn active" onclick="showSection('learning','hero','pp-section-hero',this)">Hero</button>
            <button class="section-nav-btn" onclick="showSection('learning','introquote','pp-section-intro-quote',this)">Intro Quote</button>
            <button class="section-nav-btn" onclick="showSection('learning','faith','pp-section-faith',this)">Faith &amp; Formation</button>
            <button class="section-nav-btn" onclick="showSection('learning','wellbeing','pp-section-wellbeing',this)">Belonging &amp; Wellbeing</button>
            <button class="section-nav-btn" onclick="showSection('learning','teaching','pp-section-teaching',this)">Teaching</button>
            <button class="section-nav-btn" onclick="showSection('learning','everychild','pp-section-every-child',this)">Every Child</button>
            <button class="section-nav-btn" onclick="showSection('learning','beyond','pp-section-beyond',this)">Beyond the Classroom</button>
          </nav>

      <div id="learning-sec-hero" class="section-panel active">
      <!-- Hero -->
      <div class="section-card">
        <div class="section-title">Hero</div>
        <div class="section-subtitle">Learning page hero.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="lrn-hero-heading">Hero Heading</label>
            <input type="text" id="lrn-hero-heading" class="field-input"
              value="<?= v($c['learning']['hero']['heroHeading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="lrn-hero-body">Hero Body</label>
            <textarea id="lrn-hero-body" class="field-input"><?= v($c['learning']['hero']['heroBody'] ?? '') ?></textarea>
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="lrn-hero-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('lrn-hero-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('lrn-hero-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="lrn-hero-image" value="<?= v($c['learning']['hero']['heroImage'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /learning-sec-hero -->

      <div id="learning-sec-introquote" class="section-panel">
      <!-- Intro Quote -->
      <div class="section-card">
        <div class="section-title">Intro Quote</div>
        <div class="section-subtitle">Pull-quote shown below the hero.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="lrn-quote">Quote</label>
            <textarea id="lrn-quote" class="field-input"><?= v($c['learning']['introQuote']['quote'] ?? '') ?></textarea>
          </div>
          <div>
            <label class="field-label" for="lrn-quote-subtitle">Subtitle</label>
            <input type="text" id="lrn-quote-subtitle" class="field-input"
              value="<?= v($c['learning']['introQuote']['subtitle'] ?? '') ?>">
          </div>
        </div>
      </div>
      </div><!-- /learning-sec-introquote -->

      <?php
      $lrnSections = [
        0 => ['id'=>'faith',      'label'=>'Faith &amp; Formation',     'anchor'=>'pp-section-faith'],
        1 => ['id'=>'wellbeing',  'label'=>'Belonging &amp; Wellbeing', 'anchor'=>'pp-section-wellbeing'],
        2 => ['id'=>'teaching',   'label'=>'Teaching that Inspires',    'anchor'=>'pp-section-teaching'],
        3 => ['id'=>'everychild', 'label'=>'Learning for Every Child',  'anchor'=>'pp-section-every-child'],
        4 => ['id'=>'beyond',     'label'=>'Beyond the Classroom',      'anchor'=>'pp-section-beyond'],
      ];
      for ($i = 0; $i < 5; $i++):
        $ls = $c['learning']['sections'][$i] ?? [];
        $sec = $lrnSections[$i];
      ?>
      <div id="learning-sec-<?= $sec['id'] ?>" class="section-panel">
      <div class="section-card">
        <div class="section-title"><?= $sec['label'] ?></div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="lrn-sec-<?= $i ?>-eyebrow">Eyebrow Label</label>
            <input type="text" id="lrn-sec-<?= $i ?>-eyebrow" class="field-input"
              value="<?= v($ls['eyebrow'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="lrn-sec-<?= $i ?>-heading">Heading</label>
            <input type="text" id="lrn-sec-<?= $i ?>-heading" class="field-input"
              value="<?= v($ls['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="lrn-sec-<?= $i ?>-body">Body</label>
            <textarea id="lrn-sec-<?= $i ?>-body" class="field-input"><?= v($ls['body'] ?? '') ?></textarea>
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="lrn-sec-<?= $i ?>-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('lrn-sec-<?= $i ?>-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('lrn-sec-<?= $i ?>-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="lrn-sec-<?= $i ?>-image" value="<?= v($ls['image'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /learning-sec-<?= $sec['id'] ?> -->
      <?php endfor; ?>

        </div><!-- /page-editor-left -->

        <div class="page-editor-right">
          <div class="preview-panel">
            <div class="preview-panel-header">
              <div>
                <span class="preview-panel-title">Page Preview</span>
                <div class="preview-panel-hint" id="learning-preview-hint">Hero</div>
              </div>
              <button class="save-btn" onclick="saveTab('learning', event)" style="height:38px;padding:0 20px;font-size:13px;">Save Changes</button>
            </div>
            <div class="section-preview-wrap">
              <img id="preview-img-learning" class="section-preview-img" src="../images/admin-previews/learning-hero.jpg" alt=""
                   onerror="this.style.display='none';document.getElementById('preview-ph-learning').style.display='flex'">
              <div id="preview-ph-learning" class="section-preview-ph">
                <div class="section-preview-ph-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="2" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/></svg></div>
                <p class="section-preview-ph-title" id="preview-ph-name-learning">Hero</p>
                <span class="section-preview-ph-hint">Run <code>node site/admin/capture-previews.js</code> once to generate section screenshots.</span>
              </div>
            </div>
            <div class="preview-panel-footer">📸 Visual reference only — screenshots do not update when you save</div>
          </div>
        </div><!-- /page-editor-right -->
      </div><!-- /page-editor-split -->
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- COMMUNITY TAB                                           -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-community" class="tab-panel">
      <div class="page-editor-split">
        <div class="page-editor-left">
          <nav class="section-nav">
            <button class="section-nav-btn active" onclick="showSection('community','hero','pp-section-hero',this)">Hero</button>
            <button class="section-nav-btn" onclick="showSection('community','intro','pp-section-intro',this)">Intro</button>
            <button class="section-nav-btn" onclick="showSection('community','pillar1','pp-section-pillar-1',this)">Welcoming</button>
            <button class="section-nav-btn" onclick="showSection('community','pillar2','pp-section-pillar-2',this)">Families</button>
            <button class="section-nav-btn" onclick="showSection('community','pillar3','pp-section-pillar-3',this)">Celebrating</button>
            <button class="section-nav-btn" onclick="showSection('community','pillar4','pp-section-pillar-4',this)">Faith</button>
            <button class="section-nav-btn" onclick="showSection('community','spirit','pp-section-spirit',this)">Spirit</button>
          </nav>

      <div id="community-sec-hero" class="section-panel active">
      <!-- Hero -->
      <div class="section-card">
        <div class="section-title">Hero</div>
        <div class="section-subtitle">Community page hero.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="com-hero-heading">Hero Heading</label>
            <input type="text" id="com-hero-heading" class="field-input"
              value="<?= v($c['community']['hero']['heroHeading'] ?? '') ?>">
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="com-hero-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('com-hero-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('com-hero-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="com-hero-image" value="<?= v($c['community']['hero']['heroImage'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /community-sec-hero -->

      <div id="community-sec-intro" class="section-panel">
      <!-- Community Intro -->
      <div class="section-card">
        <div class="section-title">Community Intro</div>
        <div class="section-subtitle">Opening paragraph for the community page.</div>
        <div>
          <label class="field-label" for="com-intro-body">Body</label>
          <textarea id="com-intro-body" class="field-input" style="min-height:100px;"><?= v($c['community']['intro']['body'] ?? '') ?></textarea>
        </div>
      </div>
      </div><!-- /community-sec-intro -->

      <?php
      $comPillars = [
        0 => ['id'=>'pillar1','label'=>'A Welcoming Community'],
        1 => ['id'=>'pillar2','label'=>'Partnerships with Families'],
        2 => ['id'=>'pillar3','label'=>'Celebrating Together'],
        3 => ['id'=>'pillar4','label'=>'Faith in Community'],
      ];
      for ($i = 0; $i < 4; $i++):
        $cp = $c['community']['pillars'][$i] ?? [];
        $cpl = $comPillars[$i];
      ?>
      <div id="community-sec-<?= $cpl['id'] ?>" class="section-panel">
      <div class="section-card">
        <div class="section-title"><?= $cpl['label'] ?></div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="com-pillar-<?= $i ?>-heading">Heading</label>
            <input type="text" id="com-pillar-<?= $i ?>-heading" class="field-input"
              value="<?= v($cp['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="com-pillar-<?= $i ?>-body">Body</label>
            <textarea id="com-pillar-<?= $i ?>-body" class="field-input"><?= v($cp['body'] ?? '') ?></textarea>
          </div>
          <div>
            <label class="field-label" for="com-pillar-<?= $i ?>-tagline">Tagline (script font)</label>
            <input type="text" id="com-pillar-<?= $i ?>-tagline" class="field-input"
              value="<?= v($cp['tagline'] ?? '') ?>">
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="com-pillar-<?= $i ?>-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('com-pillar-<?= $i ?>-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('com-pillar-<?= $i ?>-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="com-pillar-<?= $i ?>-image" value="<?= v($cp['image'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /community-sec-<?= $cpl['id'] ?> -->
      <?php endfor; ?>

      <div id="community-sec-spirit" class="section-panel">
      <!-- Spirit Section -->
      <div class="section-card">
        <div class="section-title">Spirit Section</div>
        <div class="section-subtitle">Closing statement about the school's spirit.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="com-spirit-heading">Heading</label>
            <input type="text" id="com-spirit-heading" class="field-input"
              value="<?= v($c['community']['spirit']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="com-spirit-body">Body</label>
            <textarea id="com-spirit-body" class="field-input"><?= v($c['community']['spirit']['body'] ?? '') ?></textarea>
          </div>
          <div>
            <label class="field-label" for="com-spirit-tagline">Tagline (script font)</label>
            <input type="text" id="com-spirit-tagline" class="field-input"
              value="<?= v($c['community']['spirit']['tagline'] ?? '') ?>">
          </div>
        </div>

      </div>
      </div><!-- /community-sec-spirit -->

        </div><!-- /page-editor-left -->

        <div class="page-editor-right">
          <div class="preview-panel">
            <div class="preview-panel-header">
              <div>
                <span class="preview-panel-title">Page Preview</span>
                <div class="preview-panel-hint" id="community-preview-hint">Hero</div>
              </div>
              <button class="save-btn" onclick="saveTab('community', event)" style="height:38px;padding:0 20px;font-size:13px;">Save Changes</button>
            </div>
            <div class="section-preview-wrap">
              <img id="preview-img-community" class="section-preview-img" src="../images/admin-previews/community-hero.jpg" alt=""
                   onerror="this.style.display='none';document.getElementById('preview-ph-community').style.display='flex'">
              <div id="preview-ph-community" class="section-preview-ph">
                <div class="section-preview-ph-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="2" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/></svg></div>
                <p class="section-preview-ph-title" id="preview-ph-name-community">Hero</p>
                <span class="section-preview-ph-hint">Run <code>node site/admin/capture-previews.js</code> once to generate section screenshots.</span>
              </div>
            </div>
            <div class="preview-panel-footer">📸 Visual reference only — screenshots do not update when you save</div>
          </div>
        </div><!-- /page-editor-right -->
      </div><!-- /page-editor-split -->
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- ENROLMENTS TAB                                          -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-enrolments" class="tab-panel">
      <div class="page-editor-split">
        <div class="page-editor-left">
          <nav class="section-nav">
            <button class="section-nav-btn active" onclick="showSection('enrolments','hero','pp-section-hero',this)">Hero</button>
            <button class="section-nav-btn" onclick="showSection('enrolments','intro','pp-section-intro',this)">Intro</button>
            <button class="section-nav-btn" onclick="showSection('enrolments','step1','pp-section-step1',this)">Step 1 — Visit</button>
            <button class="section-nav-btn" onclick="showSection('enrolments','step2','pp-section-step2',this)">Step 2 — Enquire</button>
            <button class="section-nav-btn" onclick="showSection('enrolments','step3','pp-section-step3',this)">Step 3 — Enrol</button>
            <button class="section-nav-btn" onclick="showSection('enrolments','transition','pp-section-transition',this)">Transition</button>
            <button class="section-nav-btn" onclick="showSection('enrolments','closingcta','pp-section-closing-cta',this)">Closing CTA</button>
            <button class="section-nav-btn" onclick="showSection('enrolments','tourdates','pp-section-step1',this)">Tour Dates</button>
          </nav>

      <div id="enrolments-sec-hero" class="section-panel active">
      <!-- Hero -->
      <div class="section-card">
        <div class="section-title">Hero</div>
        <div class="section-subtitle">Enrolments page hero.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="enr-hero-heading">Hero Heading</label>
            <input type="text" id="enr-hero-heading" class="field-input"
              value="<?= v($c['enrolments']['hero']['heroHeading'] ?? '') ?>">
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="enr-hero-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('enr-hero-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('enr-hero-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="enr-hero-image" value="<?= v($c['enrolments']['hero']['heroImage'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /enrolments-sec-hero -->

      <div id="enrolments-sec-intro" class="section-panel">
      <!-- Enrolment Intro -->
      <div class="section-card">
        <div class="section-title">Enrolment Intro</div>
        <div class="section-subtitle">Opening paragraph on the enrolments page.</div>
        <div>
          <label class="field-label" for="enr-intro-body">Body</label>
          <textarea id="enr-intro-body" class="field-input" style="min-height:100px;"><?= v($c['enrolments']['intro']['body'] ?? '') ?></textarea>
        </div>
      </div>
      </div><!-- /enrolments-sec-intro -->

      <div id="enrolments-sec-step1" class="section-panel">
      <!-- Step 1 -->
      <div class="section-card">
        <div class="section-title">Step 1 — Visit Us</div>
        <div class="section-subtitle">First step in the enrolment journey.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="enr-s1-heading">Heading</label>
            <input type="text" id="enr-s1-heading" class="field-input"
              value="<?= v($c['enrolments']['step1']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="enr-s1-body">Body</label>
            <textarea id="enr-s1-body" class="field-input"><?= v($c['enrolments']['step1']['body'] ?? '') ?></textarea>
          </div>
          <div>
            <label class="field-label" for="enr-s1-opendays">Open Days Info</label>
            <textarea id="enr-s1-opendays" class="field-input"><?= v($c['enrolments']['step1']['openDays'] ?? '') ?></textarea>
            <p class="field-hint">List upcoming open day / tour dates here.</p>
          </div>
          <div>
            <label class="field-label" for="enr-s1-tourinfo">Tour Info</label>
            <input type="text" id="enr-s1-tourinfo" class="field-input"
              value="<?= v($c['enrolments']['step1']['tourInfo'] ?? '') ?>">
          </div>
        </div>
      </div>
      </div><!-- /enrolments-sec-step1 -->

      <div id="enrolments-sec-step2" class="section-panel">
      <!-- Step 2 -->
      <div class="section-card">
        <div class="section-title">Step 2 — Start the Conversation</div>
        <div class="section-subtitle">Second step: contact and enquiry actions.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="enr-s2-heading">Heading</label>
            <input type="text" id="enr-s2-heading" class="field-input"
              value="<?= v($c['enrolments']['step2']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="enr-s2-body">Body</label>
            <textarea id="enr-s2-body" class="field-input"><?= v($c['enrolments']['step2']['body'] ?? '') ?></textarea>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
              <label class="field-label" for="enr-s2-btn1text">Button 1 Text</label>
              <input type="text" id="enr-s2-btn1text" class="field-input"
                value="<?= v($c['enrolments']['step2']['btn1Text'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="enr-s2-btn1url">Button 1 URL</label>
              <input type="url" id="enr-s2-btn1url" class="field-input"
                value="<?= v($c['enrolments']['step2']['btn1Url'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="enr-s2-btn2text">Button 2 Text</label>
              <input type="text" id="enr-s2-btn2text" class="field-input"
                value="<?= v($c['enrolments']['step2']['btn2Text'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="enr-s2-btn2url">Button 2 URL</label>
              <input type="url" id="enr-s2-btn2url" class="field-input"
                value="<?= v($c['enrolments']['step2']['btn2Url'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>
      </div><!-- /enrolments-sec-step2 -->

      <div id="enrolments-sec-step3" class="section-panel">
      <!-- Step 3 -->
      <div class="section-card">
        <div class="section-title">Step 3 — The Enrolment Process</div>
        <div class="section-subtitle">Third step: formal enrolment application.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="enr-s3-heading">Heading</label>
            <input type="text" id="enr-s3-heading" class="field-input"
              value="<?= v($c['enrolments']['step3']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="enr-s3-body">Body</label>
            <textarea id="enr-s3-body" class="field-input"><?= v($c['enrolments']['step3']['body'] ?? '') ?></textarea>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
              <label class="field-label" for="enr-s3-btntext">Button Text</label>
              <input type="text" id="enr-s3-btntext" class="field-input"
                value="<?= v($c['enrolments']['step3']['btnText'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="enr-s3-btnurl">Button URL</label>
              <input type="url" id="enr-s3-btnurl" class="field-input"
                value="<?= v($c['enrolments']['step3']['btnUrl'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>
      </div><!-- /enrolments-sec-step3 -->

      <div id="enrolments-sec-transition" class="section-panel">
      <!-- Transition to School -->
      <div class="section-card">
        <div class="section-title">Transition to School</div>
        <div class="section-subtitle">Section about the Prep transition program.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="enr-trans-heading">Heading</label>
            <input type="text" id="enr-trans-heading" class="field-input"
              value="<?= v($c['enrolments']['transition']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="enr-trans-body">Body</label>
            <textarea id="enr-trans-body" class="field-input"><?= v($c['enrolments']['transition']['body'] ?? '') ?></textarea>
          </div>
          <div>
            <label class="field-label" for="enr-trans-tagline">Tagline</label>
            <input type="text" id="enr-trans-tagline" class="field-input"
              value="<?= v($c['enrolments']['transition']['tagline'] ?? '') ?>">
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="enr-trans-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('enr-trans-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('enr-trans-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="enr-trans-image" value="<?= v($c['enrolments']['transition']['image'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /enrolments-sec-transition -->

      <div id="enrolments-sec-closingcta" class="section-panel">
      <!-- Closing CTA -->
      <div class="section-card">
        <div class="section-title">Closing CTA</div>
        <div class="section-subtitle">Final call-to-action panel at the bottom of the enrolments page.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="enr-closecta-label">Eyebrow Label</label>
            <input type="text" id="enr-closecta-label" class="field-input"
              value="<?= v($c['enrolments']['closingCta']['label'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="enr-closecta-heading">Heading</label>
            <input type="text" id="enr-closecta-heading" class="field-input"
              value="<?= v($c['enrolments']['closingCta']['heading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="enr-closecta-body">Body</label>
            <textarea id="enr-closecta-body" class="field-input"><?= v($c['enrolments']['closingCta']['body'] ?? '') ?></textarea>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
              <label class="field-label" for="enr-closecta-btn1text">Button 1 Text</label>
              <input type="text" id="enr-closecta-btn1text" class="field-input"
                value="<?= v($c['enrolments']['closingCta']['btn1Text'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="enr-closecta-btn1url">Button 1 URL</label>
              <input type="url" id="enr-closecta-btn1url" class="field-input"
                value="<?= v($c['enrolments']['closingCta']['btn1Url'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="enr-closecta-btn2text">Button 2 Text</label>
              <input type="text" id="enr-closecta-btn2text" class="field-input"
                value="<?= v($c['enrolments']['closingCta']['btn2Text'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label" for="enr-closecta-btn2url">Button 2 URL</label>
              <input type="url" id="enr-closecta-btn2url" class="field-input"
                value="<?= v($c['enrolments']['closingCta']['btn2Url'] ?? '') ?>">
            </div>
          </div>
          <div>
            <label class="field-label" for="enr-closecta-tagline">Tagline</label>
            <input type="text" id="enr-closecta-tagline" class="field-input"
              value="<?= v($c['enrolments']['closingCta']['tagline'] ?? '') ?>">
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="enr-closecta-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('enr-closecta-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('enr-closecta-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="enr-closecta-image" value="<?= v($c['enrolments']['closingCta']['image'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /enrolments-sec-closingcta -->

      <div id="enrolments-sec-tourdates" class="section-panel">
      <!-- Tour Dates -->
      <div class="section-card">
        <div class="section-title">School Tour Dates</div>
        <div class="section-subtitle">Upcoming open day / tour dates shown on the enrolments page.</div>

        <?php for ($i = 0; $i < 3; $i++):
          $tour = $c['enrolments']['tourDates'][$i] ?? [];
        ?>
        <div class="group-card">
          <div class="group-label">Tour <?= $i + 1 ?></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
              <label class="field-label" for="tour-<?= $i ?>-date">Date</label>
              <input type="text" id="tour-<?= $i ?>-date" class="field-input"
                value="<?= v($tour['date'] ?? '') ?>" placeholder="e.g. Wednesday 23 July 2025">
            </div>
            <div>
              <label class="field-label" for="tour-<?= $i ?>-time">Time</label>
              <input type="text" id="tour-<?= $i ?>-time" class="field-input"
                value="<?= v($tour['time'] ?? '') ?>" placeholder="e.g. 9:30 AM">
            </div>
          </div>
        </div>
        <?php endfor; ?>

        <div class="divider"></div>

        <!-- Enquiry Note -->
        <div class="section-title" style="margin-bottom:4px;">Enquiry Note</div>
        <div class="section-subtitle">Short note displayed beneath the enquiry form.</div>
        <div>
          <label class="field-label" for="enrol-enquiry">Enquiry Note</label>
          <textarea id="enrol-enquiry" class="field-input"><?= v($c['enrolments']['enquiryNote'] ?? '') ?></textarea>
        </div>

      </div>
      </div><!-- /enrolments-sec-tourdates -->

        </div><!-- /page-editor-left -->

        <div class="page-editor-right">
          <div class="preview-panel">
            <div class="preview-panel-header">
              <div>
                <span class="preview-panel-title">Page Preview</span>
                <div class="preview-panel-hint" id="enrolments-preview-hint">Hero</div>
              </div>
              <button class="save-btn" onclick="saveTab('enrolments', event)" style="height:38px;padding:0 20px;font-size:13px;">Save Changes</button>
            </div>
            <div class="section-preview-wrap">
              <img id="preview-img-enrolments" class="section-preview-img" src="../images/admin-previews/enrolments-hero.jpg" alt=""
                   onerror="this.style.display='none';document.getElementById('preview-ph-enrolments').style.display='flex'">
              <div id="preview-ph-enrolments" class="section-preview-ph">
                <div class="section-preview-ph-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="2" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/></svg></div>
                <p class="section-preview-ph-title" id="preview-ph-name-enrolments">Hero</p>
                <span class="section-preview-ph-hint">Run <code>node site/admin/capture-previews.js</code> once to generate section screenshots.</span>
              </div>
            </div>
            <div class="preview-panel-footer">📸 Visual reference only — screenshots do not update when you save</div>
          </div>
        </div><!-- /page-editor-right -->
      </div><!-- /page-editor-split -->
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- CONTACT TAB                                             -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-contact" class="tab-panel">
      <div class="page-editor-split">
        <div class="page-editor-left">
          <nav class="section-nav">
            <button class="section-nav-btn active" onclick="showSection('contact','hero','pp-section-hero',this)">Hero</button>
            <button class="section-nav-btn" onclick="showSection('contact','intro','pp-section-intro',this)">Intro</button>
            <button class="section-nav-btn" onclick="showSection('contact','git','pp-section-contact-details',this)">Contact Details</button>
            <button class="section-nav-btn" onclick="showSection('contact','map','pp-section-contact-details',this)">Map Embed</button>
          </nav>

      <div id="contact-sec-hero" class="section-panel active">
      <!-- Hero -->
      <div class="section-card">
        <div class="section-title">Hero</div>
        <div class="section-subtitle">Contact page hero.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="con-hero-heading">Hero Heading</label>
            <input type="text" id="con-hero-heading" class="field-input"
              value="<?= v($c['contact']['hero']['heroHeading'] ?? '') ?>">
          </div>
          <div style="margin-bottom:16px;">
            <label class="field-label">Image / Video</label>
            <div class="img-picker-row" data-field="con-hero-image">
              <div class="img-picker-preview"></div>
              <div class="img-picker-actions">
                <button type="button" class="img-picker-btn" onclick="openMediaPicker('con-hero-image')">Choose Image</button>
                <button type="button" class="img-picker-clear" onclick="clearImage('con-hero-image')" title="Remove">&#x2715;</button>
              </div>
              <input type="hidden" id="con-hero-image" value="<?= v($c['contact']['hero']['heroImage'] ?? '') ?>">
              <span class="img-picker-filename">No image selected</span>
            </div>
          </div>
        </div>
      </div>
      </div><!-- /contact-sec-hero -->

      <div id="contact-sec-intro" class="section-panel">
      <!-- Contact Intro -->
      <div class="section-card">
        <div class="section-title">Contact Intro</div>
        <div class="section-subtitle">Opening text on the contact page.</div>
        <div>
          <label class="field-label" for="con-intro-body">Body</label>
          <textarea id="con-intro-body" class="field-input" style="min-height:80px;"><?= v($c['contact']['intro']['body'] ?? '') ?></textarea>
        </div>
      </div>
      </div><!-- /contact-sec-intro -->

      <div id="contact-sec-git" class="section-panel">
      <!-- Get In Touch -->
      <div class="section-card">
        <div class="section-title">Get In Touch</div>
        <div class="section-subtitle">Section heading above the contact details and form.</div>
        <div>
          <label class="field-label" for="con-git-heading">Section Heading</label>
          <input type="text" id="con-git-heading" class="field-input"
            value="<?= v($c['contact']['getInTouch']['heading'] ?? '') ?>">
        </div>
      </div>
      </div><!-- /contact-sec-git -->

      <div id="contact-sec-map" class="section-panel">
      <!-- Map -->
      <div class="section-card">
        <div class="section-title">Google Maps Embed</div>
        <div class="section-subtitle">Embed URL for the map shown on the contact page.</div>
        <div>
          <label class="field-label" for="contact-map">Google Maps Embed URL</label>
          <textarea id="contact-map" class="field-input" style="min-height:100px;"><?= v($c['contact']['mapEmbedUrl'] ?? '') ?></textarea>
          <p class="field-hint">Get this from Google Maps &rarr; Share &rarr; Embed a map &rarr; Copy the <strong>iframe src URL only</strong> (not the full &lt;iframe&gt; tag).</p>
        </div>

      </div>
      </div><!-- /contact-sec-map -->

        </div><!-- /page-editor-left -->

        <div class="page-editor-right">
          <div class="preview-panel">
            <div class="preview-panel-header">
              <div>
                <span class="preview-panel-title">Page Preview</span>
                <div class="preview-panel-hint" id="contact-preview-hint">Hero</div>
              </div>
              <button class="save-btn" onclick="saveTab('contact', event)" style="height:38px;padding:0 20px;font-size:13px;">Save Changes</button>
            </div>
            <div class="section-preview-wrap">
              <img id="preview-img-contact" class="section-preview-img" src="../images/admin-previews/contact-hero.jpg" alt=""
                   onerror="this.style.display='none';document.getElementById('preview-ph-contact').style.display='flex'">
              <div id="preview-ph-contact" class="section-preview-ph">
                <div class="section-preview-ph-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="2" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/></svg></div>
                <p class="section-preview-ph-title" id="preview-ph-name-contact">Hero</p>
                <span class="section-preview-ph-hint">Run <code>node site/admin/capture-previews.js</code> once to generate section screenshots.</span>
              </div>
            </div>
            <div class="preview-panel-footer">📸 Visual reference only — screenshots do not update when you save</div>
          </div>
        </div><!-- /page-editor-right -->
      </div><!-- /page-editor-split -->
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- POLICIES TAB                                            -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-policies" class="tab-panel">
      <div class="page-editor-split">
        <div class="page-editor-left">
          <nav class="section-nav">
            <button class="section-nav-btn active" onclick="showSection('policies','hero','pp-section-hero',this)">Hero</button>
            <button class="section-nav-btn" onclick="showSection('policies','intro','pp-section-intro',this)">Policy Intro</button>
            <button class="section-nav-btn" onclick="showSection('policies','safesmart','pp-section-safesmart',this)">SafeSmart</button>
          </nav>

      <div id="policies-sec-hero" class="section-panel active">
      <!-- Hero -->
      <div class="section-card">
        <div class="section-title">Hero</div>
        <div class="section-subtitle">Policies page hero heading.</div>
        <div>
          <label class="field-label" for="pol-hero-heading">Hero Heading</label>
          <input type="text" id="pol-hero-heading" class="field-input"
            value="<?= v($c['policies']['hero']['heroHeading'] ?? 'School Policies') ?>">
        </div>
      </div>
      </div><!-- /policies-sec-hero -->

      <div id="policies-sec-intro" class="section-panel">
      <!-- Policy Intro -->
      <div class="section-card">
        <div class="section-title">Policy Intro</div>
        <div class="section-subtitle">Introductory text above the policy list.</div>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="field-label" for="pol-intro-heading">Intro Heading</label>
            <input type="text" id="pol-intro-heading" class="field-input"
              value="<?= v($c['policies']['intro']['introHeading'] ?? '') ?>">
          </div>
          <div>
            <label class="field-label" for="pol-intro-body">Intro Body</label>
            <textarea id="pol-intro-body" class="field-input"><?= v($c['policies']['intro']['introBody'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      </div><!-- /policies-sec-intro -->

      <div id="policies-sec-safesmart" class="section-panel">
      <!-- SafeSmart Embed -->
      <div class="section-card">
        <div class="section-title">SafeSmart Portal Embed</div>
        <div class="section-subtitle">Embed the SafeSmart policy portal.</div>
        <div>
          <label class="field-label" for="pol-safesmart-url">SafeSmart Embed URL</label>
          <input type="url" id="pol-safesmart-url" class="field-input"
            value="<?= v($c['policies']['safesmartUrl'] ?? '') ?>"
            placeholder="https://safesmart.edu.au/embed/…">
          <p class="field-hint">Paste the SafeSmart portal embed URL here.</p>
        </div>

      </div>
      </div><!-- /policies-sec-safesmart -->

        </div><!-- /page-editor-left -->

        <div class="page-editor-right">
          <div class="preview-panel">
            <div class="preview-panel-header">
              <div>
                <span class="preview-panel-title">Page Preview</span>
                <div class="preview-panel-hint" id="policies-preview-hint">Hero</div>
              </div>
              <button class="save-btn" onclick="saveTab('policies', event)" style="height:38px;padding:0 20px;font-size:13px;">Save Changes</button>
            </div>
            <div class="section-preview-wrap">
              <img id="preview-img-policies" class="section-preview-img" src="../images/admin-previews/policies-hero.jpg" alt=""
                   onerror="this.style.display='none';document.getElementById('preview-ph-policies').style.display='flex'">
              <div id="preview-ph-policies" class="section-preview-ph">
                <div class="section-preview-ph-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="2" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="2" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/><rect x="11" y="11" width="7" height="7" rx="1.5" fill="#1F4C23" opacity=".25"/></svg></div>
                <p class="section-preview-ph-title" id="preview-ph-name-policies">Hero</p>
                <span class="section-preview-ph-hint">Run <code>node site/admin/capture-previews.js</code> once to generate section screenshots.</span>
              </div>
            </div>
            <div class="preview-panel-footer">📸 Visual reference only — screenshots do not update when you save</div>
          </div>
        </div><!-- /page-editor-right -->
      </div><!-- /page-editor-split -->
    </div>


    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- ACCESS TAB                                              -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div id="tab-access" class="tab-panel">
      <div style="max-width:680px;">

        <div class="section-card">
          <div class="section-title">Allowed Email Addresses</div>
          <div class="section-subtitle">Only these addresses can receive a login code. Add or remove as needed.</div>

          <!-- Current list -->
          <div id="access-email-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
            <div style="color:rgba(30,58,95,0.4);font-size:13px;">Loading&hellip;</div>
          </div>

          <div class="divider"></div>

          <!-- Add email -->
          <div style="display:flex;gap:10px;align-items:flex-end;">
            <div style="flex:1;">
              <label class="field-label" for="access-new-email">Add Email Address</label>
              <input type="email" id="access-new-email" class="field-input" placeholder="name@example.com"
                onkeydown="if(event.key==='Enter'){event.preventDefault();accessAddEmail();}">
            </div>
            <button class="save-btn" style="flex-shrink:0;" onclick="accessAddEmail()">Add</button>
          </div>
          <p class="field-hint" style="margin-top:8px;">Only add email addresses of people who should have admin access to this site.</p>
        </div>

      </div>
    </div><!-- /tab-access -->


  </div><!-- /main content -->

  <!-- ═══ MEDIA PICKER MODAL ═══ -->
  <div id="media-modal" onclick="if(event.target===this)closeMediaPicker()">
    <div class="media-modal-box">
      <div class="media-modal-header">
        <span class="media-modal-title">Media Library</span>
        <button class="media-modal-close" onclick="closeMediaPicker()">&#x2715;</button>
      </div>
      <div class="media-modal-toolbar">
        <button class="media-modal-upload-btn" onclick="uploadNewMedia()">+ Upload New</button>
      </div>
      <div class="media-modal-grid" id="media-grid">
        <div class="media-empty">Loading&hellip;</div>
      </div>
      <div class="media-modal-footer">
        <span class="media-modal-selected-info" id="media-selected-info">No image selected</span>
        <button class="media-modal-insert-btn" id="media-insert-btn" onclick="insertMedia()" disabled>Insert Image</button>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════ TOAST ═══ -->
  <div id="toast" role="alert" aria-live="polite">
    <span class="toast-bar" id="toast-bar"></span>
    <span id="toast-msg">Saved!</span>
  </div>

<script>
// ── Tab switching ─────────────────────────────────────────────────────────────
// (defined later alongside Access tab logic)

// ── Section switching ─────────────────────────────────────────────────────────
function showSection(tabId, sectionId, anchorId, btn) {
  document.querySelectorAll(`#tab-${tabId} .section-panel`).forEach(p => p.classList.remove('active'));
  document.getElementById(`${tabId}-sec-${sectionId}`).classList.add('active');
  btn.closest('.section-nav').querySelectorAll('.section-nav-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  const hint = document.getElementById(`${tabId}-preview-hint`);
  if (hint) hint.textContent = btn.textContent.trim();

  // Swap static preview screenshot
  const img = document.getElementById(`preview-img-${tabId}`);
  const ph  = document.getElementById(`preview-ph-${tabId}`);
  const phn = document.getElementById(`preview-ph-name-${tabId}`);
  if (phn) phn.textContent = btn.textContent.trim();
  if (img) {
    img.style.display = '';
    if (ph) ph.style.display = 'none';
    img.onerror = () => { img.style.display = 'none'; if (ph) ph.style.display = 'flex'; };
    img.src = `../images/admin-previews/${tabId}-${sectionId}.jpg`;
  }
}


// ── Helpers ───────────────────────────────────────────────────────────────────
function val(id) {
  const el = document.getElementById(id);
  if (!el) return undefined;
  if (el.type === 'checkbox') return el.checked;
  return el.value;
}

// ── Toast ─────────────────────────────────────────────────────────────────────
let _toastTimer = null;
function showToast(msg, success = true) {
  const t   = document.getElementById('toast');
  const bar = document.getElementById('toast-bar');
  document.getElementById('toast-msg').textContent = msg;
  bar.className = 'toast-bar ' + (success ? 'success' : 'error');
  t.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}

// ── Media picker ─────────────────────────────────────────────────────────────
let _mediaPickerTarget = null;
let _mediaPickerSelected = null;

function openMediaPicker(fieldId) {
  _mediaPickerTarget = fieldId;
  _mediaPickerSelected = null;
  document.getElementById('media-modal').classList.add('open');
  loadMediaGrid();
}

function closeMediaPicker() {
  document.getElementById('media-modal').classList.remove('open');
  _mediaPickerTarget = null;
  _mediaPickerSelected = null;
}

async function loadMediaGrid() {
  const grid = document.getElementById('media-grid');
  grid.innerHTML = '<div class="media-empty">Loading…</div>';
  document.getElementById('media-insert-btn').disabled = true;
  document.getElementById('media-selected-info').textContent = 'No image selected';

  try {
    const res = await fetch('../admin/api.php?action=images');
    const images = await res.json();
    if (!images.length) {
      grid.innerHTML = '<div class="media-empty">No images uploaded yet. Click “Upload New” to add files.</div>';
      return;
    }
    grid.innerHTML = '';
    images.forEach(img => {
      const div = document.createElement('div');
      div.className = 'media-thumb';
      div.dataset.url = img.url;
      div.innerHTML = `<img src="../${img.url}" alt="${img.name}" loading="lazy"><span class="media-thumb-label">${img.name}</span>`;
      div.onclick = () => selectMediaThumb(div, img);
      grid.appendChild(div);
    });
  } catch(e) {
    grid.innerHTML = '<div class="media-empty">Failed to load images.</div>';
  }
}

function selectMediaThumb(el, img) {
  document.querySelectorAll('.media-thumb').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  _mediaPickerSelected = img;
  document.getElementById('media-insert-btn').disabled = false;
  document.getElementById('media-selected-info').textContent = img.name + ' · ' + formatBytes(img.size);
}

function insertMedia() {
  if (!_mediaPickerSelected || !_mediaPickerTarget) return;
  setImageField(_mediaPickerTarget, _mediaPickerSelected.url);
  closeMediaPicker();
}

function setImageField(fieldId, url) {
  const input = document.getElementById(fieldId);
  if (!input) return;
  input.value = url;
  const row = document.querySelector(`.img-picker-row[data-field="${fieldId}"]`);
  if (row) {
    const preview = row.querySelector('.img-picker-preview');
    const fname = row.querySelector('.img-picker-filename');
    const name = url.split('/').pop();
    preview.innerHTML = `<img src="../${url}" alt="">`;
    fname.textContent = name;
  }
}

function clearImage(fieldId) {
  const input = document.getElementById(fieldId);
  if (!input) return;
  input.value = '';
  const row = document.querySelector(`.img-picker-row[data-field="${fieldId}"]`);
  if (row) {
    row.querySelector('.img-picker-preview').innerHTML = '<span class="no-img-icon">🖼</span>';
    row.querySelector('.img-picker-filename').textContent = 'No image selected';
  }
}

async function uploadNewMedia() {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*,video/*';
  input.multiple = true;
  input.onchange = async () => {
    const files = Array.from(input.files);
    for (const file of files) {
      const fd = new FormData();
      fd.append('image', file);
      const res = await fetch('../admin/api.php?action=upload', { method: 'POST', body: fd });
      await res.json();
    }
    loadMediaGrid();
    showToast(`Uploaded ${files.length} file${files.length > 1 ? 's' : ''}`, true);
  };
  input.click();
}

function formatBytes(bytes) {
  if (!bytes) return '';
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(1) + ' MB';
}

// ── Build payloads per tab ────────────────────────────────────────────────────
const payloads = {

  global: () => ({
    global: {
      phone:           val('g-phone'),
      email:           val('g-email'),
      address:         val('g-address'),
      facebookUrl:     val('g-facebook'),
      principalName:   val('g-principal'),
      officeHours:     val('g-officehours'),
      footerTagline:   val('g-footer-tagline'),
      newsletterUrl:   val('g-newsletter-url'),
      annualReportUrl: val('g-annual-report-url'),
    }
  }),

  homepage: () => ({
    homepage: {
      hero: {
        heroHeading: val('hp-hero-heading'),
        heroVideo:   val('hp-hero-video'),
      },
      quotes: [0,1,2,3,4,5].map(i => ({
        text:   val(`quote-${i}-text`),
        author: val(`quote-${i}-author`),
      })),
      welcomeBlock: {
        welcomeHeading: val('hp-welcome-heading'),
        welcomeTagline: val('hp-welcome-tagline'),
      },
      pillars: [0,1,2,3].map(i => ({
        heading: val(`hp-pillar-${i}-heading`),
        body:    val(`hp-pillar-${i}-body`),
        tagline: val(`hp-pillar-${i}-tagline`),
        image:   val(`hp-pillar-${i}-image`),
      })),
      effect: {
        effectLabel:   val('hp-effect-label'),
        effectHeading: val('hp-effect-heading'),
        effectBody:    val('hp-effect-body'),
      },
      testimonials: [0,1,2].map(i => ({
        quote: val(`hp-test-${i}-quote`),
        name:  val(`hp-test-${i}-name`),
        role:  val(`hp-test-${i}-role`),
        image: val(`hp-test-${i}-image`),
      })),
      videoGrid: {
        videosLabel:   val('hp-videos-label'),
        videosHeading: val('hp-videos-heading'),
        videosBody:    val('hp-videos-body'),
      },
      videoCards: [0,1,2].map(i => ({
        category: val(`hp-vc-${i}-category`),
        duration: val(`hp-vc-${i}-duration`),
        title:    val(`hp-vc-${i}-title`),
        videoSrc: val(`hp-vc-${i}-videosrc`),
      })),
      enrolmentsCta: {
        ctaLabel:   val('hp-cta-label'),
        ctaHeading: val('hp-cta-heading'),
        ctaBody:    val('hp-cta-body'),
        ctaBtnText: val('hp-cta-btn-text'),
        ctaBtnUrl:  val('hp-cta-btn-url'),
        ctaImage:   val('hp-cta-image'),
      },
    }
  }),

  about: () => ({
    about: {
      hero: {
        heroHeading: val('ab-hero-heading'),
        heroVideo:   val('ab-hero-video'),
      },
      smv: [0,1,2].map(i => ({
        heading:    val(`ab-smv-${i}-heading`),
        body:       val(`ab-smv-${i}-body`),
        ctaLabel:   val(`ab-smv-${i}-ctalabel`),
        ctaUrl:     val(`ab-smv-${i}-ctaurl`),
        image:      val(`ab-smv-${i}-image`),
        imageLabel: val(`ab-smv-${i}-imagelabel`),
      })),
      aboutIntro: {
        heading: val('ab-intro-heading'),
        tagline: val('ab-intro-tagline'),
        body:    val('ab-intro-body'),
      },
      principal: {
        heading: val('ab-principal-heading'),
        name:    val('ab-principal-name'),
        body:    val('ab-principal-body'),
        signoff: val('ab-principal-signoff'),
        image:   val('ab-principal-image'),
      },
      peopleIntro: {
        heading: val('ab-people-heading'),
        script:  val('ab-people-script'),
        body:    val('ab-people-body'),
      },
      teams: [0,1,2,3].map(i => ({
        level:   val(`ab-team-${i}-level`),
        heading: val(`ab-team-${i}-heading`),
        body:    val(`ab-team-${i}-body`),
        quote:   val(`ab-team-${i}-quote`),
        caption: val(`ab-team-${i}-caption`),
        image:   val(`ab-team-${i}-image`),
      })),
      parish: {
        heading: val('ab-parish-heading'),
        body:    val('ab-parish-body'),
        image:   val('ab-parish-image'),
      },
    }
  }),

  learning: () => ({
    learning: {
      hero: {
        heroHeading: val('lrn-hero-heading'),
        heroBody:    val('lrn-hero-body'),
        heroImage:   val('lrn-hero-image'),
      },
      introQuote: {
        quote:    val('lrn-quote'),
        subtitle: val('lrn-quote-subtitle'),
      },
      sections: [0,1,2,3,4].map(i => ({
        eyebrow: val(`lrn-sec-${i}-eyebrow`),
        heading: val(`lrn-sec-${i}-heading`),
        body:    val(`lrn-sec-${i}-body`),
        image:   val(`lrn-sec-${i}-image`),
      })),
    }
  }),

  community: () => ({
    community: {
      hero: {
        heroHeading: val('com-hero-heading'),
        heroImage:   val('com-hero-image'),
      },
      intro: {
        body: val('com-intro-body'),
      },
      pillars: [0,1,2,3].map(i => ({
        heading: val(`com-pillar-${i}-heading`),
        body:    val(`com-pillar-${i}-body`),
        tagline: val(`com-pillar-${i}-tagline`),
        image:   val(`com-pillar-${i}-image`),
      })),
      spirit: {
        heading: val('com-spirit-heading'),
        body:    val('com-spirit-body'),
        tagline: val('com-spirit-tagline'),
      },
    }
  }),

  enrolments: () => ({
    enrolments: {
      hero: {
        heroHeading: val('enr-hero-heading'),
        heroImage:   val('enr-hero-image'),
      },
      intro: {
        body: val('enr-intro-body'),
      },
      step1: {
        heading:  val('enr-s1-heading'),
        body:     val('enr-s1-body'),
        openDays: val('enr-s1-opendays'),
        tourInfo: val('enr-s1-tourinfo'),
      },
      step2: {
        heading:  val('enr-s2-heading'),
        body:     val('enr-s2-body'),
        btn1Text: val('enr-s2-btn1text'),
        btn1Url:  val('enr-s2-btn1url'),
        btn2Text: val('enr-s2-btn2text'),
        btn2Url:  val('enr-s2-btn2url'),
      },
      step3: {
        heading: val('enr-s3-heading'),
        body:    val('enr-s3-body'),
        btnText: val('enr-s3-btntext'),
        btnUrl:  val('enr-s3-btnurl'),
      },
      transition: {
        heading: val('enr-trans-heading'),
        body:    val('enr-trans-body'),
        tagline: val('enr-trans-tagline'),
        image:   val('enr-trans-image'),
      },
      closingCta: {
        label:    val('enr-closecta-label'),
        heading:  val('enr-closecta-heading'),
        body:     val('enr-closecta-body'),
        btn1Text: val('enr-closecta-btn1text'),
        btn1Url:  val('enr-closecta-btn1url'),
        btn2Text: val('enr-closecta-btn2text'),
        btn2Url:  val('enr-closecta-btn2url'),
        tagline:  val('enr-closecta-tagline'),
        image:    val('enr-closecta-image'),
      },
      tourDates: [0,1,2].map(i => ({
        date: val(`tour-${i}-date`),
        time: val(`tour-${i}-time`),
      })),
      enquiryNote: val('enrol-enquiry'),
    }
  }),

  contact: () => ({
    contact: {
      hero: {
        heroHeading: val('con-hero-heading'),
        heroImage:   val('con-hero-image'),
      },
      intro: {
        body: val('con-intro-body'),
      },
      getInTouch: {
        heading: val('con-git-heading'),
      },
      mapEmbedUrl: val('contact-map'),
    }
  }),

  policies: () => ({
    policies: {
      hero: {
        heroHeading: val('pol-hero-heading'),
      },
      intro: {
        introHeading: val('pol-intro-heading'),
        introBody:    val('pol-intro-body'),
      },
      safesmartUrl: val('pol-safesmart-url'),
    }
  }),

};

// ── Save ──────────────────────────────────────────────────────────────────────
async function saveTab(tab, event) {
  const btn = event.currentTarget;
  btn.disabled = true;
  const orig = btn.textContent;
  btn.textContent = 'Saving…';

  try {
    const data   = payloads[tab]();
    const res    = await fetch('../admin/api.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(data),
    });
    const result = await res.json();
    if (result.success) {
      showToast('Saved!', true);
    } else {
      showToast(result.error || 'Save failed', false);
    }
  } catch (e) {
    showToast('Network error — check server', false);
  }

  btn.disabled = false;
  btn.textContent = orig;
}

// ── Access tab — email management ────────────────────────────────────────────
async function accessLoadEmails() {
  const list = document.getElementById('access-email-list');
  if (!list) return;
  try {
    const res  = await fetch('api.php?action=get_emails');
    const data = await res.json();
    if (!Array.isArray(data) || data.length === 0) {
      list.innerHTML = '<div style="color:rgba(30,58,95,0.4);font-size:13px;padding:8px 0;">No emails added yet.</div>';
      return;
    }
    list.innerHTML = data.map(email => `
      <div style="display:flex;align-items:center;justify-content:space-between;background:#ECEBE6;border-radius:10px;padding:10px 14px;">
        <span style="font-size:14px;color:#1F4C23;font-family:'Inter',sans-serif;">${escHtml(email)}</span>
        <button onclick="accessRemoveEmail('${escHtml(email)}')"
          style="background:transparent;border:1.5px solid #E2E6EA;border-radius:8px;padding:4px 12px;font-size:12px;color:#9CA3AF;cursor:pointer;font-family:'Inter',sans-serif;transition:border-color 0.15s,color 0.15s;"
          onmouseover="this.style.borderColor='#DC2626';this.style.color='#DC2626';"
          onmouseout="this.style.borderColor='#E2E6EA';this.style.color='#9CA3AF';">
          Remove
        </button>
      </div>`).join('');
  } catch (e) {
    list.innerHTML = '<div style="color:#B91C1C;font-size:13px;">Could not load email list.</div>';
  }
}

async function accessAddEmail() {
  const input = document.getElementById('access-new-email');
  const email = input.value.trim();
  if (!email) return;
  try {
    const res  = await fetch('api.php?action=add_email', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email }),
    });
    const data = await res.json();
    if (data.success) {
      input.value = '';
      showToast('Email added', true);
      accessLoadEmails();
    } else {
      showToast(data.error || 'Could not add email', false);
    }
  } catch (e) {
    showToast('Network error', false);
  }
}

async function accessRemoveEmail(email) {
  if (!confirm(`Remove ${email} from the allowed list?`)) return;
  try {
    const res  = await fetch('api.php?action=remove_email', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email }),
    });
    const data = await res.json();
    if (data.success) {
      showToast('Email removed', true);
      accessLoadEmails();
    } else {
      showToast(data.error || 'Could not remove email', false);
    }
  } catch (e) {
    showToast('Network error', false);
  }
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Tab switching (includes Access tab load trigger) ─────────────────────────
function switchTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  btn.classList.add('active');
  const tab = document.getElementById('tab-' + name);
  if (tab && !tab.querySelector('.section-nav-btn.active')) {
    const first = tab.querySelector('.section-nav-btn');
    if (first) first.click();
  }
  if (name === 'access') accessLoadEmails();
}

// ── Init picker previews on load ──────────────────────────────────────────────
document.querySelectorAll('.img-picker-row').forEach(row => {
  const fieldId = row.dataset.field;
  const input = document.getElementById(fieldId);
  if (input && input.value) {
    const preview = row.querySelector('.img-picker-preview');
    preview.innerHTML = `<img src="../${input.value}" alt="">`;
    row.querySelector('.img-picker-filename').textContent = input.value.split('/').pop();
  } else {
    row.querySelector('.img-picker-preview').innerHTML = '<span class="no-img-icon">🖼</span>';
  }
});
</script>
</body>
</html>
