<?php
class Logger extends Trongate {

    public function log_message(string $level, string $message): void {
        $log_file = APPPATH . 'modules/logger/assets/app.log';
        $entry = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message . PHP_EOL;
        error_log($entry, 3, $log_file);
    }

}
