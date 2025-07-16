<?php

use PHPUnit\Framework\TestCase;

class PaymentApiTest extends TestCase
{
    private $baseUrl = 'http://localhost:8080/api/payment';

    private function post($data)
    {
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$response, $http_code];
    }

    public function test_normal()
    {
        list($response, $code) = $this->post([
            'amount' => 500,
            'customer_id' => 1,
            'card_id' => '1234567890123456',
            'simulate' => ''
        ]);
        var_dump(['response' => $response, 'code' => $code]);
        $this->assertEquals(200, $code);
        $json = json_decode($response, true);
        $this->assertEquals('ok', $json['status']);
    }

    public function test_amount_1000_error()
    {
        list($response, $code) = $this->post([
            'amount' => 1000,
            'customer_id' => 1,
            'card_id' => '1234567890123456',
            'simulate' => ''
        ]);
        var_dump(['response' => $response, 'code' => $code]);
        $this->assertEquals(500, $code);
        $json = json_decode($response, true);
        $this->assertStringContainsString('Intentional bug', $json['error']);
    }

    public function test_customer_id_19_error()
    {
        list($response, $code) = $this->post([
            'amount' => 500,
            'customer_id' => 19,
            'card_id' => '1234567890123456',
            'simulate' => ''
        ]);
        var_dump(['response' => $response, 'code' => $code]);
        $this->assertEquals(400, $code);
        $json = json_decode($response, true);
        $this->assertStringContainsString('Invalid customer_id', $json['error']);
    }

    public function test_simulate_delay()
    {
        $start = microtime(true);
        list($response, $code) = $this->post([
            'amount' => 500,
            'customer_id' => 1,
            'card_id' => '1234567890123456',
            'simulate' => 'delay'
        ]);
        $elapsed = microtime(true) - $start;
        var_dump(['response' => $response, 'code' => $code, 'elapsed' => $elapsed]);
        $this->assertGreaterThan(2.5, $elapsed); // 3秒遅延
        $this->assertEquals(200, $code);
    }

    public function test_simulate_error()
    {
        list($response, $code) = $this->post([
            'amount' => 500,
            'customer_id' => 1,
            'card_id' => '1234567890123456',
            'simulate' => 'error'
        ]);
        var_dump(['response' => $response, 'code' => $code]);
        $this->assertEquals(400, $code);
        $json = json_decode($response, true);
        $this->assertStringContainsString('Simulated error', $json['error']);
    }

    public function test_simulate_bug()
    {
        list($response, $code) = $this->post([
            'amount' => 500,
            'customer_id' => 1,
            'card_id' => '1234567890123456',
            'simulate' => 'bug'
        ]);
        var_dump(['response' => $response, 'code' => $code]);
        $this->assertEquals(200, $code);
        // 壊れたJSONなのでjson_decodeは失敗する
        $this->assertNull(json_decode($response, true));
    }
} 