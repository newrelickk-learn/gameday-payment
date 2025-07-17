<?php
use Fuel\Core\Controller_Rest;

class Controller_Api_Payment extends Controller_Rest
{
    protected $format = 'json';

    public function post_index()
    {
        // JSONリクエスト対応
        $input = \Input::json() ?: \Input::post();

        $amount = isset($input['amount']) ? (int)$input['amount'] : null;
        $customer_id = isset($input['customer_id']) ? (int)$input['customer_id'] : null;
        $card_id = isset($input['card_id']) ? $input['card_id'] : null;
        $simulate = isset($input['simulate']) ? $input['simulate'] : null;
        $provider = isset($input['provider']) ? $input['provider'] : null;

        try {
            require_once(APPPATH.'classes/service/payment.php');
            $service = new PaymentService();
            $result = $service->process($amount, $customer_id, $card_id, $simulate, $provider);
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