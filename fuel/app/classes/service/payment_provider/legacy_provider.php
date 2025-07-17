<?php

require_once(APPPATH.'classes/service/payment_provider_interface.php');
require_once(APPPATH.'classes/service/LoggerFactory.php');

class LegacyProvider implements PaymentProviderInterface
{
    public function process($amount, $customer_id, $card_id, $simulate)
    {
        $logger = LoggerFactory::getLogger();
        $logger->info('LegacyProvider: 決済処理開始', compact('amount', 'customer_id', 'card_id', 'simulate'));
        // Legacy: 低速で不安定（1/3の確率でエラー）
        if (mt_rand(1, 3) === 1) {
            throw new Exception('Legacy payment provider did not respond', 500);
        }

        // 低速処理（500ms）
        usleep(500000);

        // クレジットカード番号チェック（重い処理）
        $this->luhnCheck($card_id, 5);

        // simulate=error で必ずエラー
        if ($simulate === 'error') {
            throw new Exception('Legacy simulated error', 400);
        }

        // simulate=bug でJSONが壊れる
        if ($simulate === 'bug') {
            return array('broken_json' => true);
        }

        // 正常系
        $logger->info('LegacyProvider: 決済処理完了', ['result' => 'dummy-success']);
        return array(
            'status' => 'ok',
            'provider' => 'legacy',
            'amount' => $amount,
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'transaction_id' => 'legacy_' . uniqid('txn_'),
            'http_code' => 200
        );
    }

    private function luhnCheck($card_id, $retry_count = 5)
    {
        for ($i = 0; $i < $retry_count; $i++) {
            usleep(100000); // 100msスリープ
        }
        return true;
    }
} 