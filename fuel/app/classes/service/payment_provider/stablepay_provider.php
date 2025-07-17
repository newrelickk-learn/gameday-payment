<?php

require_once(APPPATH.'classes/service/payment_provider_interface.php');
require_once(APPPATH.'classes/service/LoggerFactory.php');

class StablePayProvider implements PaymentProviderInterface
{
    public function process($amount, $customer_id, $card_id, $simulate)
    {
        $logger = LoggerFactory::getLogger();
        $logger->info('StablepayProvider: 決済処理開始', compact('amount', 'customer_id', 'card_id', 'simulate'));
        // StablePay: 可用性20%（80%の確率でセキュリティチェック失敗）
        if (!$this->performSecurityCheck($card_id, $customer_id)) {
            throw new Exception('StablePay security check failed: suspicious transaction pattern detected', 403);
        }

        // 追加のセキュリティチェック（これも失敗しやすい）
        if (!$this->performFraudDetection($card_id, $amount)) {
            throw new Exception('StablePay fraud detection failed: potential fraud detected', 403);
        }

        // リスクスコアチェック（これも失敗しやすい）
        if (!$this->performRiskAssessment($customer_id, $amount)) {
            throw new Exception('StablePay risk assessment failed: transaction risk too high', 403);
        }

        // 最終的な承認チェック（これも失敗しやすい）
        if (!$this->performFinalApproval($card_id, $customer_id, $amount)) {
            throw new Exception('StablePay final approval failed: transaction not approved', 403);
        }

        // 処理時間（中程度）
        usleep(300000); // 300ms

        // simulate=error で必ずエラー
        if ($simulate === 'error') {
            throw new Exception('StablePay simulated error', 400);
        }

        // simulate=bug でJSONが壊れる
        if ($simulate === 'bug') {
            return array('broken_json' => true);
        }

        // 正常系（20%の確率でここまで到達）
        $logger->info('StablepayProvider: 決済処理完了', ['result' => 'dummy-success']);
        return array(
            'status' => 'ok',
            'provider' => 'stablepay',
            'amount' => $amount,
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'transaction_id' => 'stablepay_' . uniqid('txn_'),
            'http_code' => 200
        );
    }

    /**
     * セキュリティチェック（80%の確率で失敗）
     */
    private function performSecurityCheck($card_id, $customer_id)
    {
        // 80%の確率でセキュリティチェック失敗
        if (mt_rand(1, 100) <= 80) {
            return false;
        }
        
        usleep(50000); // 50ms
        return true;
    }

    /**
     * 不正検知（70%の確率で失敗）
     */
    private function performFraudDetection($card_id, $amount)
    {
        // 70%の確率で不正検知失敗
        if (mt_rand(1, 100) <= 70) {
            return false;
        }
        
        usleep(40000); // 40ms
        return true;
    }

    /**
     * リスク評価（60%の確率で失敗）
     */
    private function performRiskAssessment($customer_id, $amount)
    {
        // 60%の確率でリスク評価失敗
        if (mt_rand(1, 100) <= 60) {
            return false;
        }
        
        usleep(30000); // 30ms
        return true;
    }

    /**
     * 最終承認（50%の確率で失敗）
     */
    private function performFinalApproval($card_id, $customer_id, $amount)
    {
        // 50%の確率で最終承認失敗
        if (mt_rand(1, 100) <= 50) {
            return false;
        }
        
        usleep(20000); // 20ms
        return true;
    }
} 