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
           $wallets = $account['result']['wallets'];
           foreach($wallets as $wallet)
           {
               if($wallet['asset'] == $coin)
               {
                   return $wallet['address'];
               }
           } 
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

    public function getQuote($baseMarket, $tradeMarket, $coinToSell, $amount)
    {
        try{
            $market = "$tradeMarket"."_"."$baseMarket";
            if($coinToSell == $tradeMarket)
            {
                $side = 'BUY';
                $array_key = 'bids';
            }
            else{
                $side = 'SELL';
                $array_key = 'asks';
            }
            $market = array_reverse(($this->stakecube->getOrderbook($market, $side))['result'][$array_key]);

            $prices = [];
            $filled_sell = 0;
            $filled_buy = 0;

            $position = 0;
            while($amount > $filled_sell)
            {
                $order_price = $market[$position]['price'];
                $order_amount = $market[$position]['amount'];
                $order_total = $order_price*$order_amount;
                
                if( ($amount - $filled_sell) > $order_total)
                {
                    $filled_buy += $order_amount; 
                    $filled_sell += $order_total;
                }
                else
                {
                    $filled_buy += ($amount-$filled_sell)/$order_price;
                    $filled_sell += ($amount - $filled_sell);
                }

                array_push($prices, $order_price);

                $position += 1;
            }

            $average_buy_value = array_sum($prices)/count($prices);

            return [
                "necessary_bid" => $prices[count($prices)-1],
                "average_price" => number_format($average_buy_value, 8),
                "filled_sell" => number_format($filled_sell,8),
                "total_order" => number_format($filled_buy,5)
            ];
        }
        catch(\Throwable $e)
        {
            throw $e;
        }
    }

    public function addOrder($baseMarket, $tradeMarket, $coinToSell, $amount, $price)
    {
        try{
            $market = "$tradeMarket"."_"."$baseMarket";
            if($coinToSell == $tradeMarket)
            {
                $side = 'BUY';
            }
            else{
                $side = 'SELL';
            }

            $order = $this->stakecube->postOrder($market, $side, $price, $amount);
            return $order;
        }
        catch(\Throwable $e)
        {
            throw $e;
        }
    }
}