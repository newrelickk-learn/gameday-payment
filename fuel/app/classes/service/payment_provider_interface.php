<?php

interface PaymentProviderInterface
{
    /**
     * 決済処理を実行する
     * 
     * @param int $amount 金額
     * @param int $customer_id 顧客ID
     * @param string $card_id カードID
     * @param string $simulate シミュレーションオプション
     * @return array 処理結果
     * @throws Exception エラー時
     */
    public function process($amount, $customer_id, $card_id, $simulate);
} 