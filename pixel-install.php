<?php
// One-time installer: adds the Meta Pixel to the main site's index.html. Removed after use.
header('Content-Type: text/plain; charset=utf-8');
if (!hash_equals('d993f5eed8354fc4ed2d83c3b197f120', (string)($_GET['k'] ?? ''))) { http_response_code(404); exit; }
$file = dirname(__DIR__) . '/index.html';
$html = file_get_contents($file);
if ($html === false) { echo "ERROR: cannot read index.html"; exit; }
if (strpos($html, '1459825486336695') !== false) { echo "ALREADY INSTALLED\n"; exit; }
$pixel = <<<'PX'
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1459825486336695');
    fbq('track', 'PageView');
    // Single-page app: send a PageView whenever the route changes
    (function(){
      var last = location.pathname + location.search;
      function changed(){ var now = location.pathname + location.search; if (now !== last) { last = now; fbq('track','PageView'); } }
      ['pushState','replaceState'].forEach(function(m){ var o = history[m]; history[m] = function(){ var r = o.apply(this, arguments); setTimeout(changed, 0); return r; }; });
      window.addEventListener('popstate', changed);
      // WhatsApp / call taps anywhere on the site count as Contact
      document.addEventListener('click', function(e){
        var a = e.target.closest && e.target.closest('a[href*="wa.me"],a[href*="api.whatsapp.com"],a[href^="tel:"]');
        if (a) fbq('track','Contact');
      }, true);
    })();
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1459825486336695&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
PX;
$backup = dirname(__DIR__) . '/index.pre-pixel.html';
if (!file_exists($backup)) { copy($file, $backup); }
$new = preg_replace('~</head>~i', $pixel . "\n  </head>", $html, 1, $n);
if ($n !== 1 || file_put_contents($file, $new) === false) { echo "ERROR: could not write"; exit; }
echo "INSTALLED\n";
