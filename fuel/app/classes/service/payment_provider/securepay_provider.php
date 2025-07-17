<?php

require_once(APPPATH.'classes/service/payment_provider_interface.php');
require_once(APPPATH.'classes/service/LoggerFactory.php');

class SecurePayProvider implements PaymentProviderInterface
{
    public function process($amount, $customer_id, $card_id, $simulate)
    {
        $logger = LoggerFactory::getLogger();
        $logger->info('SecurepayProvider: 決済処理開始', compact('amount', 'customer_id', 'card_id', 'simulate'));
        // SecurePay: 安定しているが中程度の速度（1/20の確率でエラー）
        if (mt_rand(1, 20) === 1) {
            throw new Exception('SecurePay payment provider service error', 500);
        }

        // 中程度の処理時間（200ms）
        usleep(200000);

        // クレジットカード番号チェック（中程度）
        $this->luhnCheck($card_id, 2);

        // simulate=error で必ずエラー
        if ($simulate === 'error') {
            throw new Exception('SecurePay simulated error', 400);
        }

        // simulate=bug でJSONが壊れる
        if ($simulate === 'bug') {
            return array('broken_json' => true);
        }

        // 正常系
        $logger->info('SecurepayProvider: 決済処理完了', ['result' => 'dummy-success']);
        return array(
            'status' => 'ok',
            'provider' => 'securepay',
            'amount' => $amount,
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'transaction_id' => 'securepay_' . uniqid('txn_'),
            'http_code' => 200
        );
    }

    private function luhnCheck($card_id, $retry_count = 2)
    {
        for ($i = 0; $i < $retry_count; $i++) {
            usleep(100000); // 100msスリープ
        }
        return true;
    }
} 