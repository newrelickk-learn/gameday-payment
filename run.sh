#!/bin/sh
set -e

# FuelPHPのビルトインサーバー起動
php -S 0.0.0.0:8080 -t public public/index.php 