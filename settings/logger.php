<?php
// Simple file logger used during debugging. Appends messages to logs/app.log
function app_log(string $level, string $message): void {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $file = $logDir . '/app.log';
    $time = date('Y-m-d H:i:s');
    $line = "[$time] [$level] $message\n";
    @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}

// Helper to log exceptions easily
function app_log_exception(Throwable $e): void {
    app_log('ERROR', $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    app_log('ERROR', $e->getTraceAsString());
}

?>
