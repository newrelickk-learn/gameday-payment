<?php
use Fuel\Core\Controller_Rest;

class Controller_Api_Payment extends Controller_Rest
{
    protected $format = 'json';

    public function post_index()
    {
        $amount = (int)Input::post('amount');
        $customer_id = (int)Input::post('customer_id');
        $card_id = Input::post('card_id');
        $simulate = Input::post('simulate');

        try {
            require_once(APPPATH.'classes/service/payment.php');
            $service = new PaymentService();
            $result = $service->process($amount, $customer_id, $card_id, $simulate);
            // simulate=bug の場合は壊れたJSONを返す
            if (isset($result['broken_json']) && $result['broken_json']) {
                // 故意に不正なJSONを返す
                header('Content-Type: application/json', true, 200);
                echo '{"broken_json": true'; // } が無いので壊れている
                exit;
            }
            return $this->response($result, 200);
        } catch (Exception $e) {
            return $this->response([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], $e->getCode() ?: 500);
        }
    }
} 