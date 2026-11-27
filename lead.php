<?php
declare(strict_types=1);
/**
 * Landsoukk Realtors — Westin Residences lead handler.
 * Every lead is appended to westin-leads.csv OUTSIDE public_html
 * (/home/<user>/domains/landsoukk.com/westin-leads.csv), so it is never web-accessible.
 * Set $notifyEmail to also receive each lead by email.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$notifyEmail = '';                          // e.g. 'advisory@landsoukk.com'
$fromEmail   = 'noreply@landsoukk.com';     // must be a mailbox on this domain for mail() to deliver

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($data)) { $data = $_POST; }

// honeypot
if (!empty($data['website'])) { echo json_encode(['ok' => true]); exit; }

$f = function (string $k, int $max = 200): string {
    global $data;
    $v = trim((string)($data[$k] ?? ''));
    $v = str_replace(["\r", "\n", "\t"], ' ', $v);
    return mb_substr($v, 0, $max);
};

$name  = $f('name', 80);
$phone = $f('phone', 20);
$email = $f('email', 120);
$digits = preg_replace('/\D/', '', $phone);

if (mb_strlen($name) < 2 || strlen($digits) < 10 || strlen($digits) > 13
    || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Please enter a valid name and mobile number.']);
    exit;
}

$row = [
    'received_at' => (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d H:i:s'),
    'name' => $name, 'phone' => $digits, 'email' => $email,
    'configuration' => $f('config', 40), 'budget' => $f('budget', 40),
    'form' => $f('source', 40), 'intent' => $f('intent', 60),
    'utm_source' => $f('utm_source'), 'utm_medium' => $f('utm_medium'), 'utm_campaign' => $f('utm_campaign'),
    'utm_content' => $f('utm_content'), 'utm_term' => $f('utm_term'),
    'fbclid' => $f('fbclid', 300), 'gclid' => $f('gclid', 300),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
];
// neutralise spreadsheet formula injection
foreach ($row as $k => $v) { if ($v !== '' && strpbrk($v[0], '=+-@') !== false) { $row[$k] = "'" . $v; } }

$file = dirname(__DIR__, 2) . '/westin-leads.csv';
$isNew = !file_exists($file);
$fh = @fopen($file, 'ab');
if (!$fh) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save enquiry.']);
    exit;
}
flock($fh, LOCK_EX);
if ($isNew) { fputcsv($fh, array_keys($row)); }
fputcsv($fh, array_values($row));
flock($fh, LOCK_UN);
fclose($fh);

if ($notifyEmail !== '') {
    $body = "New Westin Residences lead\n\n";
    foreach ($row as $k => $v) { if ($v !== '') { $body .= ucfirst(str_replace('_', ' ', $k)) . ": $v\n"; } }
    $headers = ["From: Landsoukk Website <{$fromEmail}>", 'Content-Type: text/plain; charset=UTF-8'];
    if ($email !== '') { $headers[] = "Reply-To: {$name} <{$email}>"; }
    @mail($notifyEmail, "Westin Residences lead: {$name} ({$row['configuration']})", $body, implode("\r\n", $headers));
}

echo json_encode(['ok' => true]);
