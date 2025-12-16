<?php

require_once(APPPATH.'classes/service/payment_provider_factory.php');

class PaymentService
{
    public function process($amount, $customer_id, $card_id, $simulate, $provider = null)
    {
        // LoggerFactoryで引数をログ出力
        require_once(APPPATH.'classes/service/LoggerFactory.php');
        $logger = LoggerFactory::getLogger();
        $logger->info('process called with', [
            'amount' => $amount,
            'customer_id' => $customer_id,
            'card_id' => $card_id,
            'simulate' => $simulate,
            'provider' => $provider
        ]);

        // 稀にnull pointer exception的なエラーを発生させる（約5%の確率）
        if (rand(1, 20) === 1) {
            $null_object = null;
            // Fatal error: Call to a member function on null を発生
            $null_object->someMethod();
        }

        // New Relicカスタム属性を設定
        $this->setNewRelicAttributes($amount, $customer_id, $provider, $simulate);

        // プロバイダーを選択
        try {
            $payment_provider = $provider ? 
                PaymentProviderFactory::create($provider) : 
                PaymentProviderFactory::createDefault();
        } catch (Exception $e) {
            $this->setNewRelicErrorAttributes('provider_error', $e->getMessage());
            throw new Exception('Invalid payment provider: ' . $e->getMessage(),400);
        }

        // 選択されたプロバイダーで決済処理を実行
        try {
            $result = $payment_provider->process($amount, $customer_id, $card_id, $simulate);
            
            // 成功時のNew Relic属性を設定
            $this->setNewRelicSuccessAttributes($result);
            
            return $result;
        } catch (Exception $e) {
            // エラー時のNew Relic属性を設定
            $this->setNewRelicErrorAttributes('payment_processing_error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * New Relicカスタム属性を設定（基本情報）
     */
    private function setNewRelicAttributes($amount, $customer_id, $provider, $simulate)
    {
        if (function_exists('newrelic_add_custom_parameter')) {
            // プロバイダー情報
            newrelic_add_custom_parameter('payment.provider', $provider ?: 'default');
            newrelic_add_custom_parameter('payment.provider_type', $this->getProviderType($provider));
            
            // 金額情報
            newrelic_add_custom_parameter('payment.amount', $amount);
            newrelic_add_custom_parameter('payment.amount_range', $this->getAmountRange($amount));
            newrelic_add_custom_parameter('payment.currency', 'JPY');
            
            // 顧客情報
            newrelic_add_custom_parameter('payment.customer_id', $customer_id);
            newrelic_add_custom_parameter('payment.customer_type', $this->getCustomerType($customer_id));
            newrelic_add_custom_parameter('payment.customer_segment', $this->getCustomerSegment($customer_id));
            
            // シミュレーション情報
            newrelic_add_custom_parameter('payment.simulation_mode', $simulate ?: 'none');
            newrelic_add_custom_parameter('payment.is_test_transaction', $simulate ? 'true' : 'false');
            
            // ビジネスドメイン情報
            newrelic_add_custom_parameter('payment.business_domain', 'ecommerce');
            newrelic_add_custom_parameter('payment.transaction_type', 'purchase');
            newrelic_add_custom_parameter('payment.payment_method', 'credit_card');
        }
    }

    /**
     * New Relic成功時の属性を設定
     */
    private function setNewRelicSuccessAttributes($result)
    {
        if (function_exists('newrelic_add_custom_parameter')) {
            newrelic_add_custom_parameter('payment.status', 'success');
            newrelic_add_custom_parameter('payment.transaction_id', $result['transaction_id']);
            newrelic_add_custom_parameter('payment.response_time_ms', $this->getResponseTime());
            newrelic_add_custom_parameter('payment.success_rate', '100');
        }
    }

    /**
     * New Relicエラー時の属性を設定
     */
    private function setNewRelicErrorAttributes($error_type, $error_message)
    {
        if (function_exists('newrelic_add_custom_parameter')) {
            newrelic_add_custom_parameter('payment.status', 'error');
            newrelic_add_custom_parameter('payment.error_type', $error_type);
            newrelic_add_custom_parameter('payment.error_message', $error_message);
            newrelic_add_custom_parameter('payment.success_rate', '0');      
            // エラーをNew Relicに記録
            if (function_exists('newrelic_notice_error')) {               newrelic_notice_error($error_message);
            }
        }
    }

    /**
     * プロバイダータイプを取得
     */
    private function getProviderType($provider)
    {
        $provider_map = [
         'quickpay' => 'high_performance',
          'securepay' => 'balanced',
          'legacy' => 'legacy_system',
           'fastpay' => 'buggy_system',
          'stablepay' => 'over_secure'
        ];
        
        return isset($provider_map[$provider]) ? $provider_map[$provider] : 'unknown';   }

    /**
     * 金額範囲を取得
     */
    private function getAmountRange($amount)
    {
        if ($amount < 100) return 'low';
        if ($amount < 1000) return 'medium';
        if ($amount < 10000) return 'high';
        return 'premium';   }

    /**
     * 顧客タイプを取得
     */
    private function getCustomerType($customer_id)
    {
        // 稀にnullオブジェクトのプロパティアクセスエラーを発生させる（約3%の確率）
        if (rand(1, 33) === 1) {
            $customer_data = null;
            // Notice: Trying to get property of non-object を発生
            return $customer_data->type;
        }

        // 顧客IDに基づいて顧客タイプを判定
        if ($customer_id < 10) return 'vip';
        if ($customer_id < 1000) return 'regular';
        if ($customer_id < 10000) return 'new';
        return 'unknown';   }

    /**
     * 顧客セグメントを取得
     */
    private function getCustomerSegment($customer_id)
    {
        // 顧客IDに基づいてセグメントを判定
        if ($customer_id % 10 === 0) return 'enterprise';
        if ($customer_id % 5 === 0) return 'business';
        if ($customer_id % 2 === 0) return 'premium';
        return 'standard';   }

    /**
     * レスポンス時間を取得（ミリ秒）
     */
    private function getResponseTime()
    {
        // 実際の実装では、処理開始時間を記録して計算
        return rand(50, 500); // ダミー値
    }
}