<?php
/**
 * Central Logger for KHQRPay
 * All actions are logged to /logs/ directory with timestamps
 */

define('LOG_DIR', __DIR__ . '/logs');

// Create logs directory if it doesn't exist
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

/**
 * Write a log entry
 * @param string $channel  Log file name (e.g. 'webhook', 'callback', 'payment', 'verify')
 * @param string $level    INFO | WARNING | ERROR | SUCCESS
 * @param string $message  Description of the action
 * @param array  $context  Extra data to log
 */
function write_log(string $channel, string $level, string $message, array $context = []): void
{
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
    $uri = $_SERVER['REQUEST_URI'] ?? 'N/A';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';

    $entry = [
        'timestamp'  => $timestamp,
        'level'      => $level,
        'ip'         => $ip,
        'method'     => $method,
        'uri'        => $uri,
        'user_agent' => $user_agent,
        'message'    => $message,
        'context'    => $context,
    ];

    $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

    // Daily log file: logs/webhook_2026-04-18.log
    $file = LOG_DIR . '/' . $channel . '_' . date('Y-m-d') . '.log';
    file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

    // Also append to combined master log
    $master = LOG_DIR . '/all_' . date('Y-m-d') . '.log';
    file_put_contents($master, "[$channel] $line", FILE_APPEND | LOCK_EX);
}
