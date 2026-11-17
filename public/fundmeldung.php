<?php
/**
 * ATE Bus AG – Fundmeldung Fahrpersonal
 * Eine einzige Datei: Formular + Versand per Mail an das Fundpostfach.
 * Aufruf: https://www.ate-bus.ch/fundmeldung.php?k=ZUGANGSCODE
 * Versand über den Laravel-Mailer der Website (SMTP aus .env), Fotos als Anhang. Keine Datenbank nötig.
 */

// ===== EINSTELLUNGEN =====
const EMPFAENGER   = 'fundgegenstaende@ate-bus.ch';
const ABSENDER     = 'noreply@ate-bus.ch';
const ZUGANGSCODE  = '8307';   // steht im Link der Infosoft-App (?k=...)
const MAX_FOTO_MB  = 8;                          // pro Foto (vor Verkleinerung im Browser)
// =========================

$LINIEN_VBG = ['640','650','652','653','654','655','656','657','658','659','662','765','N62','N70'];
$LINIEN_VBZ = ['701','702','703','704','705','706'];
$GEGENSTAENDE = ['Handy','Portemonnaie / Geld','Karte / Ausweis','Schlüssel','Tasche / Rucksack',
  'Kleidung','Mütze / Handschuhe / Schal','Brille','Kopfhörer / AirPods','Schirm','Spielzeug',
  'Schmuck / Uhr','Dokumente','Sonstiges'];

$code = $_GET['k'] ?? $_POST['k'] ?? '';
$zugang = hash_equals(ZUGANGSCODE, (string)$code);

// Versteckte Seite: nie in Suchmaschinen, ohne gültigen Link "existiert" sie nicht (404)
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');
if (!$zugang) {
  http_response_code(404);
  echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>';
  exit;
}

$jetzt = new DateTime('now', new DateTimeZone('Europe/Zurich'));
$jetztDatum = $jetzt->format('Y-m-d'); $jetztZeit = $jetzt->format('H:i');

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$fehler = ''; $ok = false;

if ($zugang && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!empty($_POST['website'])) { $ok = true; }          // Honeypot: Bots still verwerfen
  else {
    $rolle = ($_POST['rolle'] ?? '') === 'Reinigung' ? 'Reinigung' : (($_POST['rolle'] ?? '') === 'Fahrer' ? 'Fahrer' : '');
    $f = [
      'Gefunden von'  => $rolle,
      'Name'          => trim(trim($_POST['nachname'] ?? '') . ' ' . trim($_POST['vorname'] ?? '')),   // "Nachname Vorname" wie im Dienstplan
      'Datum'         => trim($_POST['datum'] ?? ''),
      'Uhrzeit'       => trim($_POST['zeit'] ?? ''),
      'Linie'         => trim($_POST['linie'] ?? ''),
      'Bus-Nr'        => trim($_POST['bus'] ?? ''),
      'Fundort im Bus'=> trim($_POST['ort'] ?? ''),
      'Gegenstand'    => trim($_POST['gegenstand'] ?? ''),
      'Beschreibung'  => trim($_POST['beschreibung'] ?? ''),
      'Abgegeben bei' => trim($_POST['abgabe'] ?? ''),
    ];
    $gruppe = $_POST['gruppe'] ?? '';
    $linienDerGruppe = $gruppe === 'VBZ' ? $LINIEN_VBZ : ($gruppe === 'VBG' ? $LINIEN_VBG : []);
    if ($rolle === '' || trim($_POST['nachname'] ?? '') === '' || trim($_POST['vorname'] ?? '') === '' || $f['Datum'] === '' || $f['Gegenstand'] === '' || $f['Abgegeben bei'] === '') {
      $fehler = 'Bitte «Gefunden von», Familienname, Vorname, Datum, Gegenstand und Abgabeort ausfüllen.';
    } elseif ($linienDerGruppe === []) {
      $fehler = 'Bitte Gruppe (VBZ/VBG) wählen.';
    } elseif ($rolle === 'Fahrer' && !in_array($f['Linie'], $linienDerGruppe, true)) {
      $fehler = 'Bitte eine passende Linie wählen.';
    } elseif ($rolle === 'Reinigung' && !preg_match('/^\d{1,3}$/', $f['Bus-Nr'])) {
      $fehler = 'Bitte die Bus-Nr. angeben.';
    } else {
      if ($rolle === 'Reinigung') { $f['Linie'] = ''; }
      // Fahrer: "VBG 765"  ·  Reinigung (keine Linie): "VBG"
      $f['Buslinie'] = $rolle === 'Fahrer' ? $gruppe . ' ' . $f['Linie'] : $gruppe;
      $datumAnz = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $f['Datum'], $m) ? "$m[3].$m[2].$m[1]" : $f['Datum'];

      // Fotos einsammeln
      $anh = [];
      foreach (['foto1', 'foto2'] as $i => $feld) {
        if (!empty($_FILES[$feld]['tmp_name']) && is_uploaded_file($_FILES[$feld]['tmp_name'])) {
          if ($_FILES[$feld]['size'] > MAX_FOTO_MB * 1024 * 1024) { $fehler = 'Foto zu gross.'; break; }
          $typ = mime_content_type($_FILES[$feld]['tmp_name']);
          if (!in_array($typ, ['image/jpeg','image/png','image/webp','image/heic','image/heif'], true)) { $fehler = 'Nur Fotos erlaubt.'; break; }
          $ext = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/heic'=>'heic','image/heif'=>'heif'][$typ];
          $anh[] = ['name' => 'Foto' . ($i + 1) . '.' . $ext, 'typ' => $typ, 'daten' => file_get_contents($_FILES[$feld]['tmp_name'])];
        }
      }

      if ($fehler === '') {
        // Laravel nur für den Versand booten (Mail-Konfiguration aus .env)
        require __DIR__ . '/../vendor/autoload.php';
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // HTML-Tabelle im gleichen Aufbau wie die Verlustmeldung (für die automatische Auswertung)
        $zeilen = '';
        foreach (['Gefunden von','Name','Datum','Uhrzeit','Buslinie','Bus-Nr','Fundort im Bus','Gegenstand','Beschreibung','Abgegeben bei'] as $k) {
          $v = $k === 'Datum' ? $datumAnz : $f[$k];
          $zeilen .= '<tr><td style="padding:6px 12px;color:#555">' . h($k) . '</td><td style="padding:6px 12px">' . nl2br(h($v)) . '</td></tr>';
        }
        $html = '<h2>Neue Fundmeldung (' . h($rolle) . ')</h2><p>Ein Fundgegenstand wurde erfasst:</p>'
              . '<table style="border-collapse:collapse">' . $zeilen . '</table>'
              . '<p>Fotos: ' . count($anh) . ' im Anhang</p>';

        $wo = $rolle === 'Fahrer' ? $f['Buslinie'] : $gruppe . ' Bus ' . $f['Bus-Nr'] . ' (Reinigung)';
        $betreff = 'Neue Fundmeldung: ' . $f['Gegenstand'] . ' – ' . $wo . ' – ' . $datumAnz;
        // Versand über den Laravel-Mailer (gleicher SMTP wie «Verlust melden»), nicht über PHP mail()
        try {
          \Illuminate\Support\Facades\Mail::send([], [], function ($m) use ($html, $betreff, $anh) {
            $m->to(EMPFAENGER)->from(ABSENDER, 'ATE Bus Fundmeldung')->subject($betreff)->html($html);
            foreach ($anh as $a) { $m->attachData($a['daten'], $a['name'], ['mime' => $a['typ']]); }
          });
          $ok = true;
        } catch (\Throwable $e) {
          \Illuminate\Support\Facades\Log::error('Fundmeldung: Mail konnte nicht versendet werden', ['betreff' => $betreff, 'error' => $e->getMessage()]);
          $fehler = 'Senden fehlgeschlagen. Bitte Disposition informieren.';
        }
      }
    }
  }
}
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Fundmeldung • Ate Bus</title>
<style>
  /* Design wie www.ate-bus.ch: Inter, Dunkelblau #14244E, ATE-Blau #02529F, Hellblau-Felder #CAE0F4, eckig, schräge Buttons */
  :root { --ate:#02529f; --dunkel:#14244e; --hell:#cae0f4; --grund:#f9f9f9; --grau:#5b6b8c;
          --vbz:#0a6ebd; --vbg:#1e9b3c; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background:var(--grund); color:var(--dunkel); font-size:16px; }
  header { padding:14px 20px; display:flex; align-items:center; }
  header svg { width:124px; height:auto; display:block; }
  main { max-width:560px; margin:0 auto; padding:8px 20px 28px; }
  h1 { font-size:26px; font-weight:500; margin:18px 0 6px; }
  .intro { margin:0 0 18px; line-height:1.45; }
  h2 { font-size:20px; font-weight:500; margin:26px 0 10px; }
  .feld { background:var(--hell); padding:8px 12px; margin-bottom:12px; display:block; }
  .feld > .titel { display:block; font-weight:500; margin-bottom:2px; }
  .feld small { font-weight:400; color:var(--grau); }
  .feld input, .feld select, .feld textarea { width:100%; min-width:0; border:0; background:transparent; color:var(--dunkel);
        font:inherit; font-size:16px; padding:6px 0; outline:none; -webkit-appearance:none; appearance:none; }
  .feld select { background-image:linear-gradient(45deg,transparent 50%,var(--dunkel) 50%),linear-gradient(135deg,var(--dunkel) 50%,transparent 50%);
        background-position:calc(100% - 12px) 55%,calc(100% - 7px) 55%; background-size:5px 5px; background-repeat:no-repeat; padding-right:22px; }
  .feld select:disabled { color:var(--grau); }
  .feld textarea { min-height:64px; resize:vertical; }
  ::placeholder { color:var(--grau); opacity:1; }
  .reihe { display:flex; gap:12px; } .reihe > .feld { flex:1; min-width:0; }
  .fotos { display:flex; gap:12px; margin-bottom:12px; }
  .foto { flex:1; aspect-ratio:1; background:var(--hell); display:flex; flex-direction:column; align-items:center; justify-content:center;
          text-align:center; font-weight:500; cursor:pointer; overflow:hidden; position:relative; border:2px dashed #8fb5dc; }
  .foto svg { width:34px; height:34px; margin-bottom:6px; }
  .foto img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
  .foto input { display:none; }
  .wahl { display:flex; gap:12px; margin-bottom:12px; }
  .knopf { flex:1; }
  .wahl.vier { display:grid; grid-template-columns:1fr 1fr; }
  .knopf input { position:absolute; opacity:0; width:1px; height:1px; }
  .knopf span { display:flex; align-items:center; justify-content:center; text-align:center; min-height:54px; padding:8px 4px;
                background:var(--hell); font-weight:500; font-size:17px; hyphens:manual; border:2px solid transparent; cursor:pointer; }
  .knopf input:checked + span { background:var(--ate); color:#fff; }
  .knopf.vbz span { color:var(--vbz); border-color:var(--vbz); background:#e6f1fb; font-weight:700; font-size:20px; }
  .knopf.vbg span { color:var(--vbg); border-color:var(--vbg); background:#e7f6eb; font-weight:700; font-size:20px; }
  .knopf.vbz input:checked + span { background:var(--vbz); color:#fff; }
  .knopf.vbg input:checked + span { background:var(--vbg); color:#fff; }
  .knopf input:focus-visible + span { outline:3px solid var(--dunkel); outline-offset:2px; }
  .label { font-weight:500; margin:4px 0 6px; display:block; }
  .btn { display:inline-flex; align-items:center; justify-content:center; border:0; cursor:pointer; font:inherit; font-weight:500; font-size:17px;
         background:var(--ate); color:#fff; padding:14px 48px 14px 16px; margin-top:10px; text-decoration:none;
         clip-path:polygon(0% 0%,100% 0%,calc(100% - 1.6rem) 100%,0% 100%); transition:background .3s; }
  .btn:hover { background:var(--dunkel); } .btn:disabled { opacity:.5; cursor:not-allowed; }
  .meldung { padding:12px 14px; margin-bottom:14px; font-weight:500; }
  .gut { background:#e7f6eb; color:#14532d; border-left:4px solid var(--vbg); }
  .schlecht { background:#fdecec; color:#8a1c1c; border-left:4px solid #c62828; }
  footer { background:var(--ate); color:#f9f9f9; padding:18px 20px; font-size:14px; line-height:1.5; }
  .hp { position:absolute; left:-5000px; }
</style>
</head>
<body>
<header>
  <svg viewBox="0 0 181 55" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="ATE Bus">
<path d="M0 0V38.4052H50.9458H124.042H144.61L162.489 0H0Z" fill="#02529F"/>
<path d="M172.456 0L150.464 46.9397H129.738H50.9458H0V55.1581H50.9458H133.535H155.527L181.316 0H172.456Z" fill="#02529F"/>
<path d="M9.96774 6.79595L15.8218 6.47986L21.0429 31.135L15.98 32.0833L15.3471 27.3419L10.4424 27.9741L9.6513 33.1896L4.74658 34.1379L9.96774 6.79595ZM12.8156 11.3793L11.0753 23.2327L14.556 22.7586L12.8156 11.3793Z" fill="white"/>
<path d="M23.0995 10.9052L18.8276 11.0632V6.32182L32.2761 5.68964V10.1149L28.0042 10.431V29.8707L23.2577 30.6609V10.9052H23.0995Z" fill="white"/>
<path d="M33.2256 5.68965L44.9336 5.05746V9.16666L37.9721 9.6408V14.2241L44.459 13.5919V17.7011L37.9721 18.4914V23.7069L45.2501 22.6006V26.7098L33.2256 28.9224V5.68965Z" fill="white"/>
<path d="M53.1611 4.58332L59.0152 4.26723C61.8631 4.10918 63.7617 5.68964 63.7617 8.85056C63.7617 11.0632 62.6541 12.9598 61.072 13.4339V13.5919C62.3377 13.5919 64.0781 14.0661 64.0781 18.0172C64.0781 20.2299 63.287 23.3908 58.6987 24.3391L53.1611 25.2873V4.58332ZM56.9583 12.4856L57.9076 12.3276C59.3316 12.1695 59.8062 11.2213 59.8062 9.79884C59.8062 8.85056 59.3316 7.74424 58.0659 7.90229H56.9583V12.4856ZM56.9583 21.1782L58.0659 20.8621C59.648 20.704 60.1227 19.4396 60.1227 18.0172C60.1227 16.1207 59.3316 15.3304 58.2241 15.4885L56.9583 15.6465V21.1782Z" fill="white"/>
<path d="M75.1532 21.1781L71.6724 21.8103V20.2299C71.0395 21.6523 69.932 22.6006 68.6663 22.7586C66.6095 23.2327 65.502 22.1264 65.502 19.4396V8.69251L69.1409 8.37642V17.8592C69.1409 18.9655 69.4574 19.4396 70.2485 19.2816C70.8813 19.2816 71.5142 18.4914 71.5142 17.227V8.06032L75.1532 7.74423V21.1781Z" fill="white"/>
<path d="M79.4251 16.2787C79.5833 16.9109 79.5833 17.385 79.7415 17.7011C79.8997 18.1753 80.3744 18.3333 80.849 18.1753C81.7983 18.0172 82.273 17.385 82.273 16.7529C82.273 16.1207 81.9565 15.6465 80.849 15.3305C77.8429 14.6983 76.419 14.2241 76.419 11.3793C76.419 8.69252 78.1593 7.27011 81.0072 6.95401C84.488 6.79597 85.5955 8.37643 85.5955 10.1149V10.5891L82.273 10.9052V10.7471C82.273 9.64079 81.7983 9.3247 81.0072 9.48275C80.0579 9.48275 79.7415 10.1149 79.7415 10.7471C79.7415 13.1178 85.5955 10.9052 85.5955 15.6465C85.5955 18.1753 84.0134 20.0718 81.0072 20.546C77.6847 21.1782 76.2607 20.2299 76.2607 17.5431V16.9109L79.4251 16.2787Z" fill="white"/>
  </svg>
</header>
<main>
  <h1>Fundmeldung</h1>
<?php if ($ok): ?>
  <div class="meldung gut">Danke! Die Fundmeldung wurde gesendet.</div>
  <a class="btn" href="?k=<?= h($code) ?>">Weiteren Gegenstand melden</a>
<?php else: ?>
  <p class="intro">Fundgegenstand im Bus? Kurz erfassen, Fotos machen und senden.</p>
  <?php if ($fehler): ?><div class="meldung schlecht"><?= h($fehler) ?></div><?php endif; ?>
  <form id="f" method="post" enctype="multipart/form-data">
    <input type="hidden" name="k" value="<?= h($code) ?>">
    <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">

    <span class="label">Gefunden von*</span>
    <div class="wahl">
      <label class="knopf"><input type="radio" name="rolle" value="Fahrer" required><span>Fahrer</span></label>
      <label class="knopf"><input type="radio" name="rolle" value="Reinigung" required><span>Reinigung</span></label>
    </div>

    <h2>Fotos</h2>
    <div class="fotos">
      <label class="foto" id="b1"><span><svg viewBox="0 0 24 24" fill="none" stroke="#02529F" stroke-width="1.8"><path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/></svg><br>Foto 1</span><input type="file" name="foto1" accept="image/*" capture="environment"></label>
      <label class="foto" id="b2"><span><svg viewBox="0 0 24 24" fill="none" stroke="#02529F" stroke-width="1.8"><path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/></svg><br>Foto 2</span><input type="file" name="foto2" accept="image/*" capture="environment"></label>
    </div>

    <h2>Gegenstand</h2>
    <label class="feld"><span class="titel">Gegenstand*</span>
      <select id="gegenstand" name="gegenstand" required>
        <option value="">bitte wählen</option>
        <?php foreach ($GEGENSTAENDE as $g): ?><option><?= h($g) ?></option><?php endforeach; ?>
      </select></label>
    <label class="feld"><span class="titel">Beschreibung <small>(Farbe, Marke, Inhalt)</small></span>
      <textarea id="beschreibung" name="beschreibung" placeholder="z.B. schwarzer Rucksack, gelbe Reissverschlüsse"></textarea></label>

    <h2>Wo und wann</h2>
    <div class="reihe">
      <label class="feld"><span class="titel">Datum*</span><input type="date" id="datum" name="datum" value="<?= h($jetztDatum) ?>" required></label>
      <label class="feld"><span class="titel">Uhrzeit</span><input type="time" id="zeit" name="zeit" value="<?= h($jetztZeit) ?>"></label>
    </div>
    <span class="label">Gruppe*</span>
    <div class="wahl">
      <label class="knopf vbz"><input type="radio" name="gruppe" value="VBZ" required><span>VBZ</span></label>
      <label class="knopf vbg"><input type="radio" name="gruppe" value="VBG" required><span>VBG</span></label>
    </div>
    <div class="reihe">
      <label class="feld" id="feldLinie"><span class="titel">Linie*</span>
        <select id="linie" name="linie" required disabled><option value="">zuerst Gruppe</option></select></label>
      <label class="feld"><span class="titel">Bus-Nr.<span id="busPflicht"></span></span><input type="number" id="bus" name="bus" inputmode="numeric" min="1" max="999" placeholder="z.B. 61"></label>
    </div>
    <label class="feld"><span class="titel">Fundort im Bus</span>
      <input id="ort" name="ort" placeholder="z.B. hinten links, Kinderwagenplatz"></label>

    <h2>Abgabe</h2>
    <div class="reihe">
      <label class="feld"><span class="titel">Familienname*</span>
        <input id="nachname" name="nachname" autocomplete="family-name" placeholder="z.B. Muster" required></label>
      <label class="feld"><span class="titel">Vorname*</span>
        <input id="vorname" name="vorname" autocomplete="given-name" placeholder="z.B. Max" required></label>
    </div>
    <span class="label">Abgegeben bei*</span>
    <div class="wahl vier">
      <label class="knopf"><input type="radio" name="abgabe" value="Kiste" required><span>Kiste</span></label>
      <label class="knopf"><input type="radio" name="abgabe" value="Aufenthaltsraum" required><span>Aufenthaltsraum</span></label>
      <label class="knopf"><input type="radio" name="abgabe" value="Dispo" required><span>Dispo</span></label>
      <label class="knopf"><input type="radio" name="abgabe" value="Tresor" required><span>Tresor</span></label>
    </div>

    <button class="btn" id="senden" type="submit">Fundmeldung senden</button>
  </form>
<?php endif; ?>
</main>
<footer>ATE Bus AG · Bietenholzstrasse 30 · 8307 Effretikon</footer>
<script>
(function () {
  var f = document.getElementById('f'); if (!f) return;
  var jetzt = new Date(), p = function (n) { return (n < 10 ? '0' : '') + n; };
  document.getElementById('datum').value = jetzt.getFullYear() + '-' + p(jetzt.getMonth() + 1) + '-' + p(jetzt.getDate());
  document.getElementById('zeit').value = p(jetzt.getHours()) + ':' + p(jetzt.getMinutes());
  try { ['nachname', 'vorname'].forEach(function (k) { var w = localStorage.getItem('ate_' + k); if (w) document.getElementById(k).value = w; }); } catch (x) {}

  var LINIEN = { VBZ: <?= json_encode($LINIEN_VBZ) ?>, VBG: <?= json_encode($LINIEN_VBG) ?> };
  var sel = document.getElementById('linie');
  Array.prototype.forEach.call(document.querySelectorAll('input[name=gruppe]'), function (r) {
    r.addEventListener('change', function () {
      sel.innerHTML = '<option value="">– Linie wählen –</option>';
      LINIEN[r.value].forEach(function (l) { var o = document.createElement('option'); o.textContent = l; sel.appendChild(o); });
      sel.disabled = false;
    });
  });

  // Reinigung: keine Linie, dafür Bus-Nr. Pflicht; Gruppe aus Bus-Nr. (10–40 VBZ, 54–78 VBG, 53 beide)
  var bus = document.getElementById('bus'), feldLinie = document.getElementById('feldLinie');
  function rolle() { var r = f.querySelector('input[name=rolle]:checked'); return r ? r.value : ''; }
  function rolleAnwenden() {
    var rein = rolle() === 'Reinigung';
    feldLinie.style.display = rein ? 'none' : '';
    sel.required = !rein; bus.required = rein;
    document.getElementById('busPflicht').textContent = rein ? '*' : '';
  }
  Array.prototype.forEach.call(f.querySelectorAll('input[name=rolle]'), function (r) { r.addEventListener('change', rolleAnwenden); });
  bus.addEventListener('input', function () {
    var n = parseInt(bus.value, 10), g = (n >= 10 && n <= 40) ? 'VBZ' : (n >= 54 && n <= 78) ? 'VBG' : '';
    var r = g && f.querySelector('input[name=gruppe][value=' + g + ']');
    if (r && !r.checked) { r.checked = true; r.dispatchEvent(new Event('change')); }
  });

  try { var gr = localStorage.getItem('ate_rolle'); var rr = gr && f.querySelector('input[name=rolle][value=' + gr + ']'); if (rr) { rr.checked = true; rolleAnwenden(); } } catch (x) {}

  // Fotos im Browser verkleinern (max. 1600 px, JPEG) – schneller Upload auch bei schwachem Netz
  var klein = {};
  ['1', '2'].forEach(function (n) {
    var box = document.getElementById('b' + n), inp = box.querySelector('input');
    inp.addEventListener('change', function () {
      var datei = inp.files[0]; if (!datei) return;
      var img = new Image(), url = URL.createObjectURL(datei);
      img.onload = function () {
        var s = Math.min(1, 1600 / Math.max(img.width, img.height)), c = document.createElement('canvas');
        c.width = Math.round(img.width * s); c.height = Math.round(img.height * s);
        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
        c.toBlob(function (b) { klein['foto' + n] = b; }, 'image/jpeg', 0.8);
        box.querySelector('span').style.display = 'none';
        var alt = box.querySelector('img'); if (alt) alt.remove();
        var vor = document.createElement('img'); vor.src = url; box.appendChild(vor);
      };
      img.src = url;
    });
  });

  f.addEventListener('submit', function (e) {
    try { localStorage.setItem('ate_nachname', document.getElementById('nachname').value); localStorage.setItem('ate_vorname', document.getElementById('vorname').value); localStorage.setItem('ate_rolle', rolle()); } catch (x) {}
    if (!window.FormData || !window.fetch) return;           // ohne JS: normales Absenden
    e.preventDefault();
    var btn = document.getElementById('senden'); btn.disabled = true; btn.textContent = 'Wird gesendet …';
    var fd = new FormData(f);
    Object.keys(klein).forEach(function (k) { fd.set(k, klein[k], k + '.jpg'); });
    fetch(location.href, { method: 'POST', body: fd })
      .then(function (r) { return r.text(); })
      .then(function (html) { document.open(); document.write(html); document.close(); })
      .catch(function () { btn.disabled = false; btn.textContent = 'Fundmeldung senden'; alert('Keine Verbindung – bitte nochmals versuchen.'); });
  });
})();
</script>
</body>
</html>
