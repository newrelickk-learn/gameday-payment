<?php

class PaymentService
{
    public function process($amount, $customer_id, $card_id, $simulate)
    {
        // 引数をログに出力
        \Log::info('process called with', array(
            'amount' => $amount,
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'simulate' => $simulate
        ));

        // simulate=delay で3秒遅延
        if ($simulate === 'delay') {
            sleep(3);
        }

        // 意図的なバグ: amountが1000のとき必ずエラー
        if ($amount === 1000) {
            throw new Exception('Intentional bug: amount 1000 is not allowed.', 500);
        }

        // 1/3の確率でエラー
        if (mt_rand(1, 3) === 1) {
            throw new Exception('Payment provider did not respond.', 400);
        }

        // customer_id=43で「クレジットカード番号チェック」風の重い処理
        if ($customer_id === 43) {
            $this->luhnCheck($card_id, 50);
        } else {
            $this->luhnCheck($card_id, 2);
        }

        // simulate=error で必ずエラー
        if ($simulate === 'error') {
            throw new Exception('Simulated error.', 400);
        }

        // simulate=bug でJSONが壊れる
        if ($simulate === 'bug') {
            return array(
                'broken_json' => true
            );
        }

        // 正常系
        return array(
            'status' => 'ok',
            'amount' => $amount,
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'transaction_id' => uniqid('txn_'),
            'http_code' => 200
        );
    }

    // クレジットカード番号のLuhnチェック。リトライ機能付き。
    private function luhnCheck($card_id, $retry_count = 1)
    {
        $sum = 0;
        for ($i = 0; $i < $retry_count; $i++) {
            $sum += $i;
            $this->luhnCheckSingle($card_id); // 100msスリープ
        }
        // $sumは使わない
        return true;
    }

    // クレジットカード番号のLuhnチェック
    private function luhnCheckSingle($card_id)
    {
        usleep(100000); // 100msスリープ
    }
}