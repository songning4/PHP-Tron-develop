<?php
require('../vendor/autoload.php');

use TronTool\Credential;

echo 'create a new address...' . PHP_EOL;
echo "</br>";
$credential = Credential::create();
echo 'private key => ' . $credential->privateKey() . PHP_EOL;
echo "</br>";
echo 'public key => ' . $credential->publicKey() . PHP_EOL;
echo "</br>";
echo 'address => ' . $credential->address() . PHP_EOL;
echo "</br>";
echo 'import an existing private key...' . PHP_EOL;
echo "</br>";
$credential = Credential::fromPrivateKey('11e086b1ad5cccb6458357e14d2d01037c32a21f331cf5a8ee248a64c02f48a0');
echo 'private key => ' . $credential->privateKey() . PHP_EOL;
echo "</br>";
echo 'public key => ' . $credential->publicKey() . PHP_EOL;
echo "</br>";
echo 'address => ' . $credential->address() . PHP_EOL;
echo "</br>";
