<?php
namespace App\Core;

class TxPayload
{
    const FLAG_ACK = "0";
    const FLAG_REQUEST_STATE = "1";

    public $sonde;
    public $flag;
    public $temp;
    public $supplyV;
    public $sig;

    public function __construct($sonde, $flag)
    {
        $this->sonde = $sonde;
        $this->flag = $flag;
    }
}
