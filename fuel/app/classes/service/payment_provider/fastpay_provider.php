<?php

require_once(APPPATH.'classes/service/payment_provider_interface.php');
require_once(APPPATH.'classes/service/LoggerFactory.php');

class FastPayProvider implements PaymentProviderInterface
{
    public function process($amount, $customer_id, $card_id, $simulate)
    {
        $logger = LoggerFactory::getLogger();
        $logger->info('FastpayProvider: 決済処理開始', compact('amount', 'customer_id', 'card_id', 'simulate'));
        // FastPay: 処理時間がかかるバグがある（1/15の確率でエラー）
        if (mt_rand(1, 15) === 1) {
            throw new Exception('FastPay payment provider timeout', 408);
        }

        // 重い処理を実行（バグにより処理時間がかかる）
        $this->performHeavyValidation($card_id, $customer_id, $amount);
        
        // さらに重い処理
        $this->performDeepSecurityCheck($card_id);
        
        // 無限ループに近い重い処理
        $this->performExcessiveLogging($amount, $customer_id, $card_id);

        // simulate=error で必ずエラー
        if ($simulate === 'error') {
            throw new Exception('FastPay simulated error', 400);
        }

        // simulate=bug でJSONが壊れる
        if ($simulate === 'bug') {
            return array('broken_json' => true);
        }

        // 正常系
        $logger->info('FastpayProvider: 決済処理完了', ['result' => 'dummy-success']);
        return array(
            'status' => 'ok',
            'provider' => 'fastpay',
            'amount' => $amount,
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'transaction_id' => 'fastpay_' . uniqid('txn_'),
            'http_code' => 200
        );
    }

    /**
     * 重いバリデーション処理（バグにより処理時間がかかる）
     */
    private function performHeavyValidation($card_id, $customer_id, $amount)
    {
        // 多重ループで重い処理
        for ($i = 0; $i < 100; $i++) {
            $this->validateCardFormat($card_id);
            $this->validateCustomerStatus($customer_id);
            $this->validateAmountRange($amount);
            
            // さらにネストしたループ
            for ($j = 0; $j < 10; $j++) {
                $this->checkCardHistory($card_id);
                $this->checkCustomerHistory($customer_id);
            }
        }
    }

    /**
     * 深いセキュリティチェック（バグにより処理時間がかかる）
     */
    private function performDeepSecurityCheck($card_id)
    {
        // 再帰的に呼び出す重い処理
        $this->deepSecurityCheck($card_id, 0);
    }

    private function deepSecurityCheck($card_id, $depth)
    {
        if ($depth >= 5) {
            return true;
        }
        
        // 各深さで複数のチェックを実行
        $this->checkCardSecurity($card_id);
        $this->checkFraudPattern($card_id);
        $this->checkRiskScore($card_id);
        
        // 再帰呼び出し
        $this->deepSecurityCheck($card_id, $depth + 1);
    }

    /**
     * 過度なログ出力（バグにより処理時間がかかる）
     */
    private function performExcessiveLogging($amount, $customer_id, $card_id)
    {
        // 大量のログ出力
        for ($i = 0; $i < 1000; $i++) {
            $this->logTransaction($amount, $customer_id, $card_id, $i);
            $this->logSecurityEvent($card_id, $i);
            $this->logPerformanceMetric($i);
        }
    }

    // 以下、重い処理を表現するためのダミー関数群
    private function validateCardFormat($card_id)
    {
        usleep(1000); // 1ms
        return true;
    }

    private function validateCustomerStatus($customer_id)
    {
        usleep(1000); // 1ms
        return true;
    }

    private function validateAmountRange($amount)
    {
        usleep(1000); // 1ms
        return true;
    }

    private function checkCardHistory($card_id)
    {
        usleep(2000); // 2ms
        return true;
    }

    private function checkCustomerHistory($customer_id)
    {
        usleep(2000); // 2ms
        return true;
    }

    private function checkCardSecurity($card_id)
    {
        usleep(3000); // 3ms
        return true;
    }

    private function checkFraudPattern($card_id)
    {
        usleep(3000); // 3ms
        return true;
    }

    private function checkRiskScore($card_id)
    {
        usleep(3000); // 3ms
        return true;
    }

    private function logTransaction($amount, $customer_id, $card_id, $index)
    {
        usleep(500); // 0.5ms
        return true;
    }

    private function logSecurityEvent($card_id, $index)
    {
        usleep(500); // 0.5ms
        return true;
    }

    private function logPerformanceMetric($index)
    {
        usleep(500); // 0.5ms
        return true;
    }
} 