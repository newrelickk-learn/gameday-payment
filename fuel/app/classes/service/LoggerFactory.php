<?php

class LoggerFactory
{
    private static $logger = null;

    public static function getLogger()
    {
        if (self::$logger !== null) {
            return self::$logger;
        }

        // Monologが使える場合
        if (class_exists('Monolog\\Logger')) {
            global $monolog;
            if ($monolog instanceof \Monolog\Logger) {
                self::$logger = $monolog;
                return self::$logger;
            }
            $logger = new \Monolog\Logger('fuel');
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(APPPATH.'logs/monolog.log', \Monolog\Logger::DEBUG));
            self::$logger = $logger;
            return self::$logger;
        }

        // Fallback: FuelPHPのLogラッパー
        self::$logger = new class {
            public function info($msg, $context = []) { \Log::info($msg, $context); }
            public function error($msg, $context = []) { \Log::error($msg, $context); }
            public function debug($msg, $context = []) { \Log::debug($msg, $context); }
        };
        return self::$logger;
    }
} 