# FuelPHP

* Version: 1.9 [under development]
* [Website](https://fuelphp.com/)
* [Release Documentation](https://fuelphp.com/docs)
* [Release API browser](https://fuelphp.com/api)
* [Development branch Documentation](https://fuelphp.com/dev-docs)
* [Development branch API browser](https://fuelphp.com/dev-api)
* [Support Forum](https://forums.fuelphp.com) for comments, discussion and community support

## Description

FuelPHP is a fast, lightweight PHP 5.4+ framework. In an age where frameworks are a dime a dozen, we believe that FuelPHP will stand out in the crowd. It will do this by combining all the things you love about the great frameworks out there, while getting rid of the bad.

FuelPHP is fully PHP 7 compatible.

## More information

For more detailed information, see the [development wiki](https://github.com/fuelphp/fuelphp/wiki).

## Development Team

* Harro Verton - Project Manager, Developer ([http://wanwizard.eu/](http://wanwizard.eu/))
* Steve West - Core Developer, ORM
* Márk Sági-Kazár - Developer

### Want to join?

The FuelPHP development team is always looking for new team members, who are willing to help lift the framework to the next level, and have the commitment to not only produce awesome code, but also great documentation, and support to our users.

You can not apply for membership. Start by sending in pull-requests, work on outstanding feature requests or bugs, and become active in the #fuelphp IRC channel. If your skills are up to scratch, we will notice you, and will ask you to become a team member.

### Alumni

* Frank de Jonge - Developer ([http://frenky.net/](http://frenky.net/))
* Jelmer Schreuder - Developer ([http://jelmerschreuder.nl/](http://jelmerschreuder.nl/))
* Phil Sturgeon - Developer ([http://philsturgeon.co.uk](http://philsturgeon.co.uk))
* Dan Horrigan - Founder, Developer ([http://dhorrigan.com](http://dhorrigan.com))

## Payment API 動作確認

### 起動方法

```
docker-compose up --build
```

### APIエンドポイント

- POST http://localhost:8080/api/payment
    - パラメータ: amount, customer_id, card_id, simulate

### curl例

#### 正常系
```
curl -X POST http://localhost:8080/api/payment \
  -d 'amount=500' -d 'customer_id=1' -d 'card_id=1234567890123456' -d 'simulate='
```

#### バグ・エラー系
- amount=1000 で意図的なエラー
```
curl -X POST http://localhost:8080/api/payment \
  -d 'amount=1000' -d 'customer_id=1' -d 'card_id=1234567890123456' -d 'simulate='
```
- customer_id=19 でエラー
```
curl -X POST http://localhost:8080/api/payment \
  -d 'amount=500' -d 'customer_id=19' -d 'card_id=1234567890123456' -d 'simulate='
```
- simulate=delay で3秒遅延
```
curl -X POST http://localhost:8080/api/payment \
  -d 'amount=500' -d 'customer_id=1' -d 'card_id=1234567890123456' -d 'simulate=delay'
```
- simulate=error で必ずエラー
```
curl -X POST http://localhost:8080/api/payment \
  -d 'amount=500' -d 'customer_id=1' -d 'card_id=1234567890123456' -d 'simulate=error'
```
- simulate=bug で壊れたJSON
```
curl -X POST http://localhost:8080/api/payment \
  -d 'amount=500' -d 'customer_id=1' -d 'card_id=1234567890123456' -d 'simulate=bug'
```

## テスト実行方法

1. サーバーが起動している状態で（docker-compose up）
2. 別ターミナルで下記コマンドを実行

```
docker-compose exec payment-api vendor/bin/phpunit fuel/app/tests/controller/api/payment.php
```
