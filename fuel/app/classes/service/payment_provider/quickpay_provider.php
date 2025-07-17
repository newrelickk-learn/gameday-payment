<?php

require_once(APPPATH.'classes/service/payment_provider_interface.php');
require_once(APPPATH.'classes/service/LoggerFactory.php');

class QuickPayProvider implements PaymentProviderInterface
{
    public function process($amount, $customer_id, $card_id, $simulate)
    {
        $logger = LoggerFactory::getLogger();
        $logger->info('QuickpayProvider: 決済処理開始', compact('amount', 'customer_id', 'card_id', 'simulate'));
        // New Relicプロバイダー固有属性を設定
        $this->setNewRelicProviderAttributes('quickpay', $amount, $customer_id);

        // QuickPay: 高速だが時々エラー（1/10確率）
        if (mt_rand(1, 10) === 1) {
            $this->setNewRelicProviderError('quickpay, rarily_unavailable', 'QuickPay payment provider temporarily unavailable');
            throw new Exception('QuickPay payment provider temporarily unavailable', 503);
        }

        // 高速処理（50ms）
        usleep(50000);

        // クレジットカード番号チェック（軽量）
        $this->luhnCheck($card_id, 1);

        // simulate=error で必ずエラー
        if ($simulate === 'error') {
            $this->setNewRelicProviderError('quickpay', 'simulated_error', 'QuickPay simulated error');
            throw new Exception('QuickPay simulated error', 400);
        }

        // simulate=bug でJSONが壊れる
        if ($simulate === 'bug') {
            $this->setNewRelicProviderError('quickpay', 'broken_json', 'QuickPay broken JSON response');
            return array('broken_json' => true);
        }

        // 正常系
        $result = array(
            'status' => 'ok',
            'provider' => 'quickpay',
            'amount' => $amount,
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'transaction_id' => 'quickpay_' . uniqid('txn_'),
            'http_code' => 200
        );

        // 成功時のプロバイダー固有属性を設定
        $this->setNewRelicProviderSuccess('quickpay', $result);
        $logger->info('QuickpayProvider: 決済処理完了', ['result' => 'dummy-success']);

        return $result;
    }

    private function luhnCheck($card_id, $retry_count = 1)
    {
        for ($i = 0; $i < $retry_count; $i++) {
            usleep(50000); // 50msスリープ
        }
        return true;
    }

    /**
     * New Relicプロバイダー固有属性を設定
     */
    private function setNewRelicProviderAttributes($provider, $amount, $customer_id)
    {
        if (function_exists('newrelic_add_custom_parameter')) {
            newrelic_add_custom_parameter("{$provider}.processing_speed", 'fast');
            newrelic_add_custom_parameter("{$provider}.reliability_score", 90);
            newrelic_add_custom_parameter("{$provider}.error_rate", 10);
            newrelic_add_custom_parameter("{$provider}.processing_time_ms", 50);
            newrelic_add_custom_parameter("{$provider}.card_validation_retries", '1');
        }
    }

    /**
     * New Relicプロバイダー成功時の属性を設定
     */
    private function setNewRelicProviderSuccess($provider, $result)
    {
        if (function_exists('newrelic_add_custom_parameter')) {
            newrelic_add_custom_parameter("{$provider}.transaction_success", 'true');
            newrelic_add_custom_parameter("{$provider}.transaction_id", $result['transaction_id']);
            newrelic_add_custom_parameter("{$provider}.response_time_ms", 50);
        }
    }

    /**
     * New Relicプロバイダーエラー時の属性を設定
     */
    private function setNewRelicProviderError($provider, $error_type, $error_message)
    {
        if (function_exists('newrelic_add_custom_parameter')) {
            newrelic_add_custom_parameter("{$provider}.transaction_success", 'false');
            newrelic_add_custom_parameter("{$provider}.error_type", $error_type);
            newrelic_add_custom_parameter("{$provider}.error_message", $error_message);
        }
    }
} 