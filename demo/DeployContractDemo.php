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

echo 'load contract abi and bytecode...' . PHP_EOL;
$abi = file_get_contents('./contract/build/EzToken.abi');
$bytecode = file_get_contents('./contract/build/EzToken.bin');

echo 'deploy contract...' . PHP_EOL;
$inst = $kit->contract($abi)->bytecode($bytecode);
$ret = $inst->deploy(1000000,'HAPPY COIN',0,'HAPY');
echo 'txid => ' . $ret->tx->txID . PHP_EOL;
echo 'contract address => ' . $ret->tx->contract_address . PHP_EOL;
echo 'result => ' . $ret->result . PHP_EOL;

