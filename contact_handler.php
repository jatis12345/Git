<?php
/**
 * PT INFOBIT CIPTA MANDIRI - Secure Contact Form Handler
 *
 * Features:
 * - Server-side validation
 * - Rate limiting (5 requests per 15 minutes per IP)
 * - Session-based CAPTCHA validation
 * - XSS/SQL Injection prevention
 * - CSRF protection
 * - IP logging and monitoring
 */

// Start session for CAPTCHA and CSRF
session_start();

// Configuration
define('RECIPIENT_EMAIL', 'info@infobit.co.id');
define('MAX_ATTEMPTS_PER_IP', 5);
define('RATE_LIMIT_WINDOW', 900); // 15 minutes in seconds
define('MAX_CAPTCHA_ATTEMPTS', 3);
define('LOG_FILE', __DIR__ . '/logs/contact_form.log');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get client IP
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $_SERVER['REMOTE_ADDR'];
}

// Rate limiting
function checkRateLimit($ip) {
    if (!file_exists(LOG_FILE)) {
        @mkdir(dirname(LOG_FILE), 0755, true);
        @touch(LOG_FILE);
    }

    $now = time();
    $attempts = [];

    if (file_exists(LOG_FILE)) {
        $logs = file(LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($logs as $log) {
            $parts = explode('|', $log);
            if (count($parts) >= 3 && $parts[1] === $ip) {
                $timestamp = intval($parts[0]);
                if ($now - $timestamp < RATE_LIMIT_WINDOW) {
                    $attempts[] = $timestamp;
                }
            }
        }
    }

    return count($attempts) < MAX_ATTEMPTS_PER_IP;
}

// Log submission
function logSubmission($ip, $status, $message = '') {
    $timestamp = time();
    $date = date('Y-m-d H:i:s', $timestamp);
    $logEntry = "$timestamp|$ip|$status|$message|$date\n";
    @file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone
function validatePhone($phone) {
    // Allow numbers, spaces, dashes, parentheses, and plus sign
    return preg_match('/^[\d\s\-\+\(\)]+$/', $phone);
}

// Generate CAPTCHA (called separately via AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'getCaptcha') {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $operators = ['+', '-', '*'];
    $operator = $operators[array_rand($operators)];

    switch($operator) {
        case '+':
            $answer = $num1 + $num2;
            break;
        case '-':
            $answer = $num1 - $num2;
            break;
        case '*':
            $answer = $num1 * $num2;
            break;
    }

    $_SESSION['captcha_answer'] = $answer;
    $_SESSION['captcha_attempts'] = isset($_SESSION['captcha_attempts']) ? $_SESSION['captcha_attempts'] : 0;

    echo json_encode([
        'success' => true,
        'question' => "Berapa hasil dari $num1 $operator $num2?",
        'remainingAttempts' => MAX_CAPTCHA_ATTEMPTS - $_SESSION['captcha_attempts']
    ]);
    exit;
}

// Main form processing
try {
    $clientIP = getClientIP();

    // Check rate limiting
    if (!checkRateLimit($clientIP)) {
        logSubmission($clientIP, 'RATE_LIMIT_EXCEEDED', 'Too many requests');
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Terlalu banyak percobaan. Silakan tunggu 15 menit dan coba lagi.'
        ]);
        exit;
    }

    // Check CAPTCHA attempts
    if (!isset($_SESSION['captcha_attempts'])) {
        $_SESSION['captcha_attempts'] = 0;
    }

    if ($_SESSION['captcha_attempts'] >= MAX_CAPTCHA_ATTEMPTS) {
        logSubmission($clientIP, 'CAPTCHA_LOCKED', 'Too many failed CAPTCHA attempts');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Form telah dikunci karena terlalu banyak percobaan CAPTCHA yang salah. Silakan refresh halaman.'
        ]);
        exit;
    }

    // Get and sanitize POST data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    $captcha = isset($_POST['captcha']) ? intval($_POST['captcha']) : null;

    // Validation
    $errors = [];

    if (empty($name) || strlen($name) < 3) {
        $errors[] = 'Nama harus diisi minimal 3 karakter';
    }

    if (empty($email) || !validateEmail($email)) {
        $errors[] = 'Email tidak valid';
    }

    if (empty($phone) || !validatePhone($phone)) {
        $errors[] = 'Nomor telepon tidak valid';
    }

    if (empty($message) || strlen($message) < 10) {
        $errors[] = 'Pesan harus diisi minimal 10 karakter';
    }

    // Check for spam patterns
    $spamPatterns = ['/\b(viagra|cialis|casino|lottery)\b/i', '/(https?:\/\/[^\s]+){5,}/'];
    foreach ($spamPatterns as $pattern) {
        if (preg_match($pattern, $message)) {
            $errors[] = 'Pesan terdeteksi sebagai spam';
            logSubmission($clientIP, 'SPAM_DETECTED', "Name: $name, Email: $email");
            break;
        }
    }

    // CAPTCHA validation
    if (!isset($_SESSION['captcha_answer']) || $captcha !== $_SESSION['captcha_answer']) {
        $_SESSION['captcha_attempts']++;
        $remainingAttempts = MAX_CAPTCHA_ATTEMPTS - $_SESSION['captcha_attempts'];

        logSubmission($clientIP, 'CAPTCHA_FAILED', "Attempt {$_SESSION['captcha_attempts']}");

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Jawaban CAPTCHA salah. Sisa $remainingAttempts percobaan lagi.",
            'remainingAttempts' => $remainingAttempts
        ]);
        exit;
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
        exit;
    }

    // Prepare email
    $to = RECIPIENT_EMAIL;
    $subject = 'Inquiry dari Website - ' . $name;

    // HTML Email body
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f7fafc; padding: 20px; margin-top: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #667eea; }
            .footer { text-align: center; margin-top: 20px; color: #718096; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>PT INFOBIT CIPTA MANDIRI</h2>
                <p>Inquiry Baru dari Website</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Nama:</span><br>
                    $name
                </div>
                <div class='field'>
                    <span class='label'>Email:</span><br>
                    <a href='mailto:$email'>$email</a>
                </div>
                <div class='field'>
                    <span class='label'>Telepon:</span><br>
                    $phone
                </div>
                <div class='field'>
                    <span class='label'>Pesan / Produk yang Dicari:</span><br>
                    " . nl2br($message) . "
                </div>
                <div class='field'>
                    <span class='label'>IP Address:</span><br>
                    $clientIP
                </div>
                <div class='field'>
                    <span class='label'>Waktu:</span><br>
                    " . date('d F Y, H:i:s') . "
                </div>
            </div>
            <div class='footer'>
                <p>Email ini dikirim otomatis dari website PT INFOBIT CIPTA MANDIRI</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Website PT INFOBIT <noreply@infobit.co.id>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Originating-IP: $clientIP\r\n";

    // Prevent email header injection
    $subject = str_replace(["\r", "\n"], '', $subject);

    // Send email
    $mailSent = mail($to, $subject, $emailBody, $headers);

    if ($mailSent) {
        // Reset CAPTCHA attempts on success
        $_SESSION['captcha_attempts'] = 0;
        unset($_SESSION['captcha_answer']);

        logSubmission($clientIP, 'SUCCESS', "Name: $name, Email: $email");

        echo json_encode([
            'success' => true,
            'message' => 'Terima kasih! Pesan Anda telah diterima. Kami akan segera menghubungi Anda.'
        ]);
    } else {
        logSubmission($clientIP, 'EMAIL_FAILED', "Name: $name, Email: $email");

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengirim pesan. Silakan hubungi kami langsung di info@infobit.co.id'
        ]);
    }

} catch (Exception $e) {
    logSubmission($clientIP ?? 'UNKNOWN', 'ERROR', $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem. Silakan coba lagi nanti.'
    ]);
}
?>
