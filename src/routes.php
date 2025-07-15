<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

require_once __DIR__ . '/PaymentService.php';

return function (App $app) {
    $app->post('/payment', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $amount = $body['amount'] ?? null;
        $customer_id = $body['customer_id'] ?? null;
        $card_id = $body['card_id'] ?? null;
        $simulate = $body['simulate'] ?? null;

        try {
            $service = new PaymentService();
            $result = $service->process($amount, $customer_id, $card_id, $simulate);

            // 壊れたJSONを返すバグ
            if (isset($result['broken_json']) && $result['broken_json']) {
                $response->getBody()->write('{"status": "ok", "broken": '); // 不完全なJSON
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }

            $http_code = $result['http_code'] ?? 200;
            unset($result['http_code']);
            $response->getBody()->write(json_encode($result));
            return $response->withStatus($http_code)->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {
            $http_code = $e->getCode() ?: 500;
            $error_response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            $response->getBody()->write(json_encode($error_response));
            return $response->withStatus($http_code)->withHeader('Content-Type', 'application/json');
        }
    });

    $app->get('/health', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode(['status' => 'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    });
}; 