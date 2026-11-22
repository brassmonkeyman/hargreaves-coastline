<?php
declare(strict_types=1);

/**
 * Contact form handler for Coastline Safety Services.
 *
 * Receives the enquiry form from about-contact.html and emails it to TO_ADDRESS.
 * Needs PHP on the web server. It does not store messages anywhere.
 */

// ---- Settings: change these if the details change ----------------------------
const TO_ADDRESS   = 'info@coastlinesafetyservices.co.uk'; // where enquiries are sent
const FROM_ADDRESS = 'info@coastlinesafetyservices.co.uk'; // must be an address on this domain, or mail may be treated as spam
const SITE_NAME    = 'Coastline Safety Services';
const PHONE        = '02382 448464';
const MIN_SECONDS  = 3;  // a form filled in faster than this is treated as a bot
const MAX_PER_HOUR = 5;  // messages allowed per visitor address per hour
// -------------------------------------------------------------------------------

const SERVICES = [
    'thermal'   => 'Thermal imaging survey',
    'lightning' => 'Lightning conductor testing',
    'both'      => 'Both services',
    'other'     => 'Something else',
];

/** True when the page asked for a JSON answer (the form's JavaScript does). */
function wants_json(): bool
{
    return stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
}

/** Finish the request: JSON for the JavaScript form, a redirect for the plain form. */
function finish(bool $ok, string $message, int $status = 200): never
{
    header('Cache-Control: no-store');
    if (wants_json()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        $flag = $ok ? 'sent=1' : 'error=1';
        header('Location: about-contact.html?' . $flag . '#contact', true, 303);
    }
    exit;
}

/** Tidy a single-line value: strip control characters (blocks email header tricks) and limit length. */
function clean_line(mixed $value, int $max): string
{
    $value = is_string($value) ? trim($value) : '';
    if (!mb_check_encoding($value, 'UTF-8')) {
        return '';
    }
    $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
    return mb_substr(trim($value), 0, $max);
}

/** Tidy the message: keep line breaks, drop other control characters, limit length. */
function clean_text(mixed $value, int $max): string
{
    $value = is_string($value) ? trim($value) : '';
    if (!mb_check_encoding($value, 'UTF-8')) {
        return '';
    }
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $value) ?? '';
    return mb_substr(trim($value), 0, $max);
}

/** Allow a visitor address only a few messages an hour. Returns false when over the limit. */
function within_rate_limit(): bool
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/coastline_form_' . hash('sha256', $ip) . '.json';
    $now  = time();
    $hits = [];

    if (is_readable($file)) {
        $stored = json_decode((string) @file_get_contents($file), true);
        if (is_array($stored)) {
            $hits = array_values(array_filter($stored, static fn ($t) => is_int($t) && $t > $now - 3600));
        }
    }
    if (count($hits) >= MAX_PER_HOUR) {
        return false;
    }
    $hits[] = $now;
    @file_put_contents($file, json_encode($hits), LOCK_EX);
    return true;
}

// ---- Only accept a form being posted -------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    if (wants_json()) {
        finish(false, 'This address only accepts the contact form.', 405);
    }
    // Someone opened contact.php in a browser: just take them to the contact page.
    header('Location: about-contact.html#contact', true, 303);
    exit;
}

// ---- Bot checks (pretend it worked so bots learn nothing) ----------------------
// 1. A hidden field real visitors never see; bots tend to fill it in.
if (clean_line($_POST['website'] ?? '', 200) !== '') {
    finish(true, 'Thank you, your message has been sent.');
}
// 2. Time taken: the form's JavaScript reports how many seconds the visitor spent on it.
if (isset($_POST['elapsed']) && is_numeric($_POST['elapsed']) && (float) $_POST['elapsed'] < MIN_SECONDS) {
    finish(true, 'Thank you, your message has been sent.');
}

// ---- Read and check the fields -------------------------------------------------
$name    = clean_line($_POST['name'] ?? '', 100);
$email   = clean_line($_POST['email'] ?? '', 254);
$phone   = preg_replace('/[^0-9 +().\-]/', '', clean_line($_POST['phone'] ?? '', 30)) ?? '';
$serviceKey = is_string($_POST['service'] ?? null) ? $_POST['service'] : '';
$service    = SERVICES[$serviceKey] ?? 'Not specified';
$message = clean_text($_POST['message'] ?? '', 5000);

if ($name === '' || $message === '' || mb_strlen($message) < 5) {
    finish(false, 'Please fill in your name and a message.', 422);
}
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    finish(false, 'Please enter a valid email address.', 422);
}

if (!within_rate_limit()) {
    finish(false, 'Too many messages from your connection. Please call us on ' . PHONE . ' instead.', 429);
}

// ---- Build and send the email --------------------------------------------------
$subject = mb_substr("Website enquiry: $service - $name", 0, 150);
$body    = "New enquiry from the website contact form\n"
         . "========================================\n\n"
         . "Name:    $name\n"
         . "Email:   $email\n"
         . 'Phone:   ' . ($phone !== '' ? $phone : 'not given') . "\n"
         . "Service: $service\n\n"
         . "Message:\n$message\n\n"
         . "----------------------------------------\n"
         . "Reply to this email to answer the visitor directly.\n";

$headers = [
    'From'                      => SITE_NAME . ' website <' . FROM_ADDRESS . '>',
    'Reply-To'                  => $email,
    'MIME-Version'              => '1.0',
    'Content-Type'              => 'text/plain; charset=UTF-8',
    'Content-Transfer-Encoding' => '8bit',
];

$sent = mail(
    TO_ADDRESS,
    '=?UTF-8?B?' . base64_encode($subject) . '?=',
    $body,
    $headers,
    '-f' . FROM_ADDRESS
);

if (!$sent) {
    error_log('Contact form: mail() failed for an enquiry from ' . $email);
    finish(false, 'Sorry, your message could not be sent. Please call us on ' . PHONE . ' or email ' . TO_ADDRESS . '.', 500);
}

finish(true, 'Thank you, your message has been sent. We will get back to you soon.');
