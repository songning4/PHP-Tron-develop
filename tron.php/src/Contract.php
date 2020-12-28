<?php
namespace TronTool;

use InvalidArgumentException;
use Web3\Utils;
use Web3\Contracts\Ethabi;
use Web3\Contracts\Types\Address as EthAddress;
use Web3\Contracts\Types\Boolean;
use Web3\Contracts\Types\Bytes;
use Web3\Contracts\Types\DynamicBytes;
use Web3\Contracts\Types\Integer;
use Web3\Contracts\Types\Str;
use Web3\Contracts\Types\Uinteger;
use Web3\Validators\AddressValidator;
use Web3\Validators\HexValidator;
use Web3\Formatters\AddressFormatter;
use Web3\Validators\StringValidator;

class TronAddress extends EthAddress{
  function inputFormat($value, $name){
    $hex = Address::decode($value);
    return parent::inputFormat($hex,$name);
  }
  public function outputFormat($value, $name){
    $hex = parent::outputFormat($value,$name);
    return Address::encode($hex);
  }
}

class Contract{
  protected $api;
  protected $abi;
  protected $ethabi;
  protected $constructor = [];
  protected $functions = [];
  protected $events = [];
  
  protected $toAddress;
  protected $bytecode;
  
  protected $credential;
  
  function __construct($tronApi,$abi,$credential=null){
    
    $abi = Utils::jsonToArray($abi, 5);
    
    foreach ($abi as $item) {
        if (isset($item['type'])) {
            if ($item['type'] === 'function') {
                $this->functions[$item['name']] = $item;
            } elseif ($item['type'] === 'constructor') {
                $this->constructor = $item;
            } elseif ($item['type'] === 'event') {
                $this->events[$item['name']] = $item;
            }
        }
    }
        
    $this->abi = $abi;
    
    $this->api = $tronApi;
    
    $this->credential = $credential;
    
    $this->ethabi = new Ethabi([
        'address' => new TronAddress,
        'bool' => new Boolean,
        'bytes' => new Bytes,
        'dynamicBytes' => new DynamicBytes,
        'int' => new Integer,
        'string' => new Str,
        'uint' => new Uinteger,
    ]);
    
  }
  
  function at($address) {
    //$this->toAddress = Address::fromBase58($address);
    $this->toAddress = $address;
    return $this;
  }
  
  function bytecode($bytecode){
    $this->bytecode = Utils::stripZero($bytecode);  
    return $this;
  }

  function credential($credential){
    $this->credential = $credential;
    return $this;
  }
  
  public function deploy()
  {
    if(is_null($this->credential)){
      throw new \Exception('Sender credential not set.');
    }
    
    if (isset($this->constructor)) {
      $constructor = $this->constructor;
      $arguments = func_get_args();

      if (count($arguments) < count($constructor['inputs'])) {
          throw new InvalidArgumentException('Please make sure you have put all constructor params and callback.');
      }
      if (!isset($this->bytecode)) {
          throw new \InvalidArgumentException('Please call bytecode($bytecode) before new().');
      }
      $params = array_splice($arguments, 0, count($constructor['inputs']));
      $data = $this->ethabi->encodeParameters($constructor, $params);
      $data = substr($data,2);
      
      $tx = $this->api->deployContract(
        $this->abi,
        $this->bytecode,
        $data,
        'EzToken',
        0,
        $this->credential->address()->base58()
      );
      $signedTx = $this->credential->signTx($tx);
      //var_dump($signedTx);
      $ret = $this->api->broadcastTransaction($signedTx);
      return (object)[
        'tx' => $signedTx,
        'result' => $ret->result
      ];
      
      /*
      $transaction = [];

      if (count($arguments) > 0) {
          $transaction = $arguments[0];
      }
      $transaction['data'] = '0x' . $this->bytecode . Utils::stripZero($data);

      $this->eth->sendTransaction($transaction, function ($err, $transaction) use ($callback){
          if ($err !== null) {
              return call_user_func($callback, $err, null);
          }
          return call_user_func($callback, null, $transaction);
      });
      */
    }
  }

  
  function send()
  {
    if(is_null($this->credential)){
      throw new \Exception('Sender credential not set.');
    }
    
    if (isset($this->functions)) {
      $arguments = func_get_args();
      $method = array_splice($arguments, 0, 1)[0];

      if (!is_string($method) || !isset($this->functions[$method])) {
          throw new InvalidArgumentException('Please make sure the method exists.');
      }
      $function = $this->functions[$method];

      if (count($arguments) < count($function['inputs'])) {
          throw new InvalidArgumentException('Please make sure you have put all function params and callback.');
      }
      
      $params = array_splice($arguments, 0, count($function['inputs']));
      $data = $this->ethabi->encodeParameters($function, $params);
      $data = substr($data,2);
      $functionName = Utils::jsonMethodToString($function);
      //var_dump($data,$functionName);
      
      $feeLimit = 1000000000;
      $callValue = 0;
      $bandwidthLimit = 0;
      
      /*
      $payload = [
        'contract_address' => $this->toAddress->hex(),
        'function_selector' => $functionName,
        'parameter' => $data,
        'owner_address' =>  $this->credential->address()->hex(),
        'fee_limit'     =>  $feeLimit,
        'call_value'    =>  $callValue,
        'consume_user_resource_percent' =>  $bandwidthLimit,
      ];
      //var_dump($payload);
      $ret = $this->api->post('/wallet/triggersmartcontract', $payload);
      */
      $ret = $this->api->triggerSmartContract(
        $this->toAddress,
        $functionName,
        $data,
        0,
        $this->credential->address()->base58()
      );
      //var_dump($ret);
      if($ret->result->result == false){
        throw new Exception('Error build contract transaction.');
      }      
      $signedTx = $this->credential->signTx($ret->transaction);
      //var_dump($signedTx);
      $ret = $this->api->broadcastTransaction($signedTx);
      return (object)[
        'tx' => $signedTx,
        'result' => $ret->result
      ];
    }
  }
  
  function call()
  {
    if(is_null($this->credential)){
      throw new \Exception('Sender credential not set.');
    }
    
    if (isset($this->functions)) {
      $arguments = func_get_args();
      $method = array_splice($arguments, 0, 1)[0];

      if (!is_string($method) || !isset($this->functions[$method])) {
          throw new InvalidArgumentException('Please make sure the method exists.');
      }
      $function = $this->functions[$method];

      if (count($arguments) < count($function['inputs'])) {
          throw new InvalidArgumentException('Please make sure you have put all function params and callback.');
      }
      
      $params = array_splice($arguments, 0, count($function['inputs']));
      $data = $this->ethabi->encodeParameters($function, $params);
      $data = substr($data,2);
      $functionName = Utils::jsonMethodToString($function);
      //var_dump($data,$functionName);
      
      $feeLimit = 1000000000;
      $callValue = 0;
      $bandwidthLimit = 0;
      
      /*
      $payload = [
        'contract_address' => $this->toAddress->hex(),
        'function_selector' => $functionName,
        'parameter' => $data,
        'owner_address' =>  $this->credential->address()->hex(),
        'fee_limit'     =>  $feeLimit,
        'call_value'    =>  $callValue,
        'consume_user_resource_percent' =>  $bandwidthLimit,
      ];
      //var_dump($payload);
      $ret = $this->client->post('/wallet/triggersmartcontract', $payload);
      */
      $ret = $this->api->triggerSmartContract(
        $this->toAddress,
        $functionName,
        $data,
        0,
        $this->credential->address()->base58()
      );
      //var_dump($ret);
      if($ret->result->result == false){
        throw new \Exception('Error build contract transaction.');
      }      
      $decoded = $this->ethabi->decodeParameters($function,$ret->constant_result[0]);
      return array_values($decoded);
    }
  }
  
  function events($since = 0){
    /*
    $api = '/event/contract/' . $this->toAddress->base58();
    $payload = [ 'since' => $since, 'sort' => 'block_timestamp' ];
    $ret = $this->api->get($api,$payload);
    */
    $ret = $this->api->getContractEvents($this->toAddress,$since);
    return $ret;
  }

  
  
}