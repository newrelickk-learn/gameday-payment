#!/bin/sh
set -e

sed -i -e "s/REPLACE_WITH_REAL_KEY/${NEW_RELIC_LICENSE_KEY}/" \
    -e "s/newrelic.appname.*/newrelic.appname = \"${NEW_RELIC_APP_NAME}\"/" \
    -e 's/;newrelic.daemon.loglevel.*/newrelic.daemon.loglevel = "verbosedebug"/' \
    -e "s/;newrelic.labels.*/newrelic.labels = \"commit:${COMMIT_SHA}\"/" \
    -e '$anewrelic.distributed_tracing_enabled = true' \
    $(php -r "echo(PHP_CONFIG_FILE_SCAN_DIR);")/newrelic.ini
    
# New Relic デーモンを起動（エラーを無視）
echo "Starting New Relic daemon..."
newrelic-daemon -f 2>/dev/null &
if [ $? -eq 0 ]; then
    echo "New Relic daemon started successfully"
    sleep 2
else
    echo "New Relic daemon failed to start (continuing without monitoring)"
fi

# FuelPHPのビルトインサーバー起動
php -S 0.0.0.0:8080 -t public public/index.php 