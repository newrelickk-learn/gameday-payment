#!/bin/sh
set -e

# New Relicのini書き換えやデーモン起動
sed -i -e "s/REPLACE_WITH_REAL_KEY/${NEW_RELIC_LICENSE_KEY}/" \
    -e "s/newrelic.appname.*/newrelic.appname = \"${NEW_RELIC_APP_NAME}\"/" \
    -e 's/;newrelic.daemon.loglevel.*/newrelic.daemon.loglevel = "verbosedebug"/' \
    -e "s/;newrelic.labels.*/newrelic.labels = \"commit:${COMMIT_SHA}\"/" \
    -e '$anewrelic.distributed_tracing_enabled = true' \
    -e '$anewrelic.application_logging.forwarding.log_level = INFO' \
    -e '$anewrelic.application_logging.forwarding.context_data.enabled = true' \
    $(php -r "echo(PHP_CONFIG_FILE_SCAN_DIR);")/newrelic.ini

echo "Starting New Relic daemon..."
newrelic-daemon -f 2>/dev/null &
if [ $? -eq 0 ]; then
    echo "New Relic daemon started successfully"
    sleep 2
else
    echo "New Relic daemon failed to start (continuing without monitoring)"
fi

# すべての年月ディレクトリ配下のphpログファイルをtail -Fで追いかける
# health、Request::execute、/app/fuel/app/logsを含む行を除外する

echo "Starting log tail..."
tail -F /app/fuel/app/logs/*/*/*.php 2>/dev/null | sed '/health/d;/Request::execute/d;/\/app\/fuel\/app\/logs/d;s/^/[LOG] /' &

# php-fpm起動
echo "Starting php-fpm..."
exec php-fpm 