<?php
// One-time installer: adds Google Tag Manager to the main site's index.html. Removed after use.
header('Content-Type: text/plain; charset=utf-8');
if (!hash_equals('007b9f894850539273cb185dd88cc67e', (string)($_GET['k'] ?? ''))) { http_response_code(404); exit; }
$file = dirname(__DIR__) . '/index.html';
$html = file_get_contents($file);
if ($html === false) { echo "ERROR: cannot read"; exit; }
if (strpos($html, 'GTM-MVSG9X7Z') !== false) { echo "ALREADY INSTALLED\n"; exit; }
$head = <<<'H'
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MVSG9X7Z');</script>
    <!-- End Google Tag Manager -->
    <script>
    // WhatsApp / call taps -> GTM dataLayer event "contact_click"
    document.addEventListener('click', function(e){
      var a = e.target.closest && e.target.closest('a[href*="wa.me"],a[href*="api.whatsapp.com"],a[href^="tel:"]');
      if (a) (window.dataLayer = window.dataLayer || []).push({event:'contact_click', contact_method: a.href.indexOf('tel:')===0 ? 'call' : 'whatsapp'});
    }, true);
    </script>
H;
$body = <<<'B'

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MVSG9X7Z"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
B;
$backup = dirname(__DIR__) . '/index.pre-gtm.html';
if (!file_exists($backup)) { copy($file, $backup); }
$new = preg_replace('~(<meta charset="[^"]*"\s*/?>)~i', "$1\n" . $head, $html, 1, $n1);
$new = preg_replace('~(<body[^>]*>)~i', "$1" . $body, $new, 1, $n2);
if ($n1 !== 1 || $n2 !== 1 || file_put_contents($file, $new) === false) { echo "ERROR: markers not found ($n1/$n2)"; exit; }
echo "INSTALLED\n";
