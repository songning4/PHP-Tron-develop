<?php
require('../vendor/autoload.php');

use TronTool\TronKit;
use TronTool\TronApi;
use TronTool\Credential;

$fromKey = '8D9142B97B38F992B4ADF9FB3D0DD527B1F47BE113C6D0B5C32A0571EF1E7B5F';
$credential = Credential::fromPrivateKey($fromKey);
$from = $credential->address()->base58();
echo 'from address => ' . $from . PHP_EOL;

$kit = new TronKit(TronApi::testNet(),$credential);

$contractAddress = 'TS2Hzo6KpAc8Ym2nGb3idpMtUpM2GiK2gL';
echo 'contract address => ' . $contractAddress . PHP_EOL;
$inst = $kit->trc20($contractAddress);

function query(){
  global $inst,$from;
  
  echo 'query trc20 token info...' . PHP_EOL;
  
  $balance = $inst->balanceOf($from);
  echo 'balance => ' . $balance . PHP_EOL;
  
  $supply = $inst->totalSupply();
  echo 'total supply => ' . $supply . PHP_EOL;
  
  $name = $inst->name();
  echo 'name => ' . $name . PHP_EOL;
  
  $symbol = $inst->symbol();
  echo 'symbol => ' . $symbol . PHP_EOL;
  
  $decimals = $inst->decimals();
  echo 'decimals => ' . $decimals .PHP_EOL;
}

function transfer(){
  global $inst;
  
  echo 'transfer trc20 token...' . PHP_EOL;
  
  $to = 'TBujbL5TkgxNg7NyM97LY6tZE7xdF1RhDT';
  echo  'to addres => ' . $to . PHP_EOL;
  
  $ret = $inst->transfer($to,2);
  echo 'txid => ' . $ret->tx->txID . PHP_EOL;
  echo 'result => ' . $ret->result . PHP_EOL;
}

function events(){
  global $inst;
  
  echo 'fetch trc20 token events...' . PHP_EOL;
  $since = 0;
  $events = $inst->events($since);
  foreach($events as $event){
    echo 'event name => ' . $event->event_name . ' | timestamp => ' . $event->block_timestamp . PHP_EOL;
    foreach($event->result_type as $key=>$_){
      echo '  ' . $key . ' => ' . $event->result->$key . PHP_EOL;
    }
  }
}

query();
transfer();
events();
