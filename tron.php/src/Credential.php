<?php
namespace TronTool;

use Elliptic\EC;
use kornrunner\Keccak;

class Credential{
  protected $keyPair;
  
  function __construct($privateKey){
    $ec = new EC('secp256k1');
    $this->keyPair = $ec->keyFromPrivate($privateKey);
  }
  
  static function fromPrivateKey($privateKey){
    return new self($privateKey);
  }
  
  static function create(){
    $bin = random_bytes(32);
    $privateKey = bin2hex($bin);
    return new self($privateKey);
  }
  
  function privateKey(){
    return  $this->keyPair->getPrivate()->toString(16,2);
  }
  
  function publicKey(){
    return $this->keyPair->getPublic()->encode('hex');
  }
  
  function address(){
    return Address::fromPublicKey($this->publicKey());
  }
  
  function sign($hex){
    $signature = $this->keyPair->sign($hex);
    $r = $signature->r->toString('hex');
    $s = $signature->s->toString('hex');
    //$v = bin2hex(pack('C',$signature->recoveryParam));
    $v = bin2hex(chr($signature->recoveryParam));
    return $r.$s.$v;
  }
  
  function signTx($tx){
    $signature = $this->sign($tx->txID);
    //var_dump($signature);
    $tx->signature = [$signature];
    return $tx;
  }
}