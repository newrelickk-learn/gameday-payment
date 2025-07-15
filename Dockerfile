# PHP公式イメージ（軽量Alpine）
FROM php:8.2-cli-alpine

# 必要なパッケージとcomposerインストール
RUN apk add --no-cache git unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# New Relic PHPエージェントのインストール
ARG NEW_RELIC_INSTALL_KEY=0000000000000000000000000000000000000000
ENV NEW_RELIC_VERSION=11.9.0.23
RUN apk add --no-cache bash ca-certificates wget && \
    wget -O - https://download.newrelic.com/php_agent/archive/${NEW_RELIC_VERSION}/newrelic-php5-${NEW_RELIC_VERSION}-linux-musl.tar.gz | tar -zx -C /tmp && \
    export NR_INSTALL_SILENT=1 && \
    export NR_INSTALL_KEY="$NEW_RELIC_INSTALL_KEY" && \
    /tmp/newrelic-php5-${NEW_RELIC_VERSION}-linux-musl/newrelic-install install && \
    rm -rf /tmp/newrelic-php5-${NEW_RELIC_VERSION}-linux-musl && \
    rm -rf /tmp/newrelic-php5-${NEW_RELIC_VERSION}-linux-musl.tar.gz

# newrelic.iniを配置
COPY newrelic.ini /usr/local/etc/php/conf.d/

# 作業ディレクトリ作成
WORKDIR /app

# composer.json, composer.lock, public, src, vendor などをコピー
COPY . /app

# 依存インストール
RUN composer install --no-interaction --no-dev --optimize-autoloader

# 8080ポートを開放
EXPOSE 8080

# サーバー起動（ビルトインサーバー）
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"] 