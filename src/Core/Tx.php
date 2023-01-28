<?php
namespace App\Core;
use App\Core\TxPayload;

class Tx
{
    public function input($data)
    {
        $sonde = $data[0];
        $txPayload = new TxPayload($sonde, TxPayload::FLAG_REQUEST_STATE);
        $txPayload->temp = 0.01 * $data[1];
        $txPayload->supplyV = 0.001 * $data[2];
        $txPayload->sig = $data[3];

        return $txPayload;
    }

    public function output($txPayload)
    {
        $data = [];
        $data[0] = $txPayload->sonde;
        $data[1] = $txPayload->flag;

        return $data;
    }
}
