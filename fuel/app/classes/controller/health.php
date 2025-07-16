<?php
use Fuel\Core\Controller_Rest;

class Controller_Health extends Controller_Rest
{
    protected $format = 'json';

    public function get_index()
    {
        return $this->response(['status' => 'ok'], 200);
    }
} 