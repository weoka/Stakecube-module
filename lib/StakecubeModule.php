<?php

namespace StakecubeModule;

require_once('vendor/autoload.php');

use Stakecube\Stakecube;

class StakecubeModule{
    public function __construct($public_key, $private_key)
    {
        $this->stakecube = new Stakecube($public_key, $private_key);
    }

    public function getDepositAddress($coin)
    {
        try{
           $account = $this->stakecube->getAccount();
           return $account['result']['wallets'][$coin]['address']; 
        }
        catch(\Throwable $e)
        {
            throw $e;
        }
    }

    public function withdraw($coin, $amount, $address)
    {
        try{
            $withdraw = $this->stakecube->withdraw($coin, $address, $amount);
            return $withdraw;
        }
        catch(\Throwable $e)
        {
            throw $e;
        }
    }


}
