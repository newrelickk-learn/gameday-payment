# PHP公式イメージ（軽量Alpine）
FROM php:8.0-fpm-alpine

# 必要なパッケージとcomposerインストール
RUN apk add --no-cache git unzip bash ca-certificates wget \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version

# New Relic PHPエージェントのインストール
ENV NEW_RELIC_AGENT_VERSION=11.9.0.23

# New Relic PHPエージェントを手動でインストール
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && sed -i -e "s/memory_limit = .*/memory_limit = -1/" /usr/local/etc/php/php.ini \
    && curl -L https://download.newrelic.com/php_agent/archive/${NEW_RELIC_AGENT_VERSION}/newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux-musl.tar.gz | tar -C /tmp -zx \
    && export NR_INSTALL_USE_CP_NOT_LN=1 \
    && export NR_INSTALL_SILENT=1 \
    && /tmp/newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux-musl/newrelic-install install \
    && rm -rf /tmp/newrelic-php5-* /tmp/nrinstall*

# 作業ディレクトリ作成
WORKDIR /app

# アプリケーションファイルをコピー
COPY . /app

# FuelPHPのセットアップとComposer依存関係のインストール
RUN composer install --no-interaction --no-dev --optimize-autoloader

# FuelPHPのキャッシュ・ログディレクトリを作成
RUN mkdir -p /app/fuel/app/logs /app/fuel/app/cache

# run.shに実行権限を付与（COPYの後に必ず実行）
RUN chmod +x /app/run.sh

EXPOSE 9000

CMD ["/app/run.sh"]
