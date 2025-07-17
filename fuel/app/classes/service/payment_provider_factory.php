<?php

require_once(APPPATH.'classes/service/payment_provider_interface.php');
require_once(APPPATH.'classes/service/payment_provider/quickpay_provider.php');
require_once(APPPATH.'classes/service/payment_provider/securepay_provider.php');
require_once(APPPATH.'classes/service/payment_provider/legacy_provider.php');
require_once(APPPATH.'classes/service/payment_provider/fastpay_provider.php');
require_once(APPPATH.'classes/service/payment_provider/stablepay_provider.php');

class PaymentProviderFactory
{
    /**
     * プロバイダーを取得する
     * 
     * @param string $provider_name プロバイダー名（quickpay, securepay, legacy, fastpay, stablepay）
     * @return PaymentProviderInterface
     * @throws Exception 無効なプロバイダー名の場合
     */
    public static function create($provider_name)
    {
        switch (strtolower($provider_name)) {
            case 'quickpay':
                return new QuickPayProvider();
            case 'securepay':
                return new SecurePayProvider();
            case 'legacy':
                return new LegacyProvider();
            case 'fastpay':
                return new FastPayProvider();
            case 'stablepay':
                return new StablePayProvider();
            default:
                throw new Exception("Unknown payment provider: {$provider_name}", 400);
        }
    }

    /**
     * デフォルトプロバイダーを取得する
     * 
     * @return PaymentProviderInterface
     */
    public static function createDefault()
    {
        return new QuickPayProvider();
    }
} 