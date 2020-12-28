<?php
require('../vendor/autoload.php');

use TronTool\TronKit;
use TronTool\TronApi;
use TronTool\Credential;

$api = TronApi::testNet();
$credential = Credential::fromPrivateKey('8D9142B97B38F992B4ADF9FB3D0DD527B1F47BE113C6D0B5C32A0571EF1E7B5F');
$kit = new TronKit($api,$credential);

$from = $credential->address()->base58();
echo 'from address => ' . $from . PHP_EOL;
$balance = $kit->getTrxBalance($from);
echo 'from adress balance(trx) => ' . $balance . PHP_EOL;

$to = 'TBujbL5TkgxNg7NyM97LY6tZE7xdF1RhDT';
echo 'send trx to ' . $to . '...' . PHP_EOL;
$ret = $kit->sendTrx($to,1000,$from);
echo 'txid => ' . $ret->txid . PHP_EOL;
echo 'result => ' . $ret->result . PHP_EOL;

$balance = $kit->getTrxBalance($from);
echo 'from adress balance(trx) => ' . $balance . PHP_EOL;
