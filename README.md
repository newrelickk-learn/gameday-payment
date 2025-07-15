# gameday-payment stub payment API

## セットアップ

```
php composer.phar install
```

## サーバー起動（開発用）

```
php -S localhost:8080 -t public
```

## エンドポイント

### POST /payment

- Content-Type: application/json
- Body例:

```
{
  "amount": 500,
  "method": "credit_card",
  "simulate": "success" // または "delay", "error", "bug" など
}
```

### simulate/amountによる挙動

- `simulate=delay` : 3秒遅延して正常レスポンス
- `amount=1000` : 500エラー（意図的なバグ）
- `simulate=error` : 400エラー
- `simulate=bug` : 壊れたJSONを返す
- それ以外 : 正常な決済レスポンス

### レスポンス例

#### 正常
```
{
  "status": "ok",
  "amount": 500,
  "method": "credit_card",
  "transaction_id": "txn_..."
}
```

#### エラー
```
{
  "status": "error",
  "message": "..."
}
``` 