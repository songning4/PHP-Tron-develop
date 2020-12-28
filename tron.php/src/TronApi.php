<?php
namespace TronTool;

class TronApi{
  protected $fullNode;
  protected $solidityNode;
  protected $eventNode;
  
  static function mainNet(){
    return new self('https://api.trongrid.io');
  }
  
  static function testNet(){
    return new self('https://api.shasta.trongrid.io');
  }
  
  function __construct($fullNodeUrl,$solidityNodeUrl=null,$eventNodeUrl=null){
    if(is_null($solidityNodeUrl)){
      $solidityNodeUrl = $fullNodeUrl;
    }    
    if(is_null($eventNodeUrl)){
      $eventNodeUrl = $fullNodeUrl;
    }
    $this->fullNode = new NodeClient($fullNodeUrl);
    $this->solidityNode = new NodeClient($solidityNodeUrl);
    $this->eventNode = new NodeClient($eventNodeUrl);
  }
  
  function getNextMaintenanceTime(){
    return $this->fullNode->get('/wallet/getnextmaintenancetime');
  }
  
  function timeUntilNextVoteCycle(){
    $args = func_get_args();
    return $this->getNextMaintenanceTime(...$args);
  }
  
  function broadcastTransaction($tx){
    return $this->fullNode->post('/wallet/broadcasttransaction',$tx);
  }  
  
  function sendRawTransaction(){
    $args = func_get_args();
    return $this->broadcastTransaction(...$args);
  }
  /*
  function createTransaction($to,$amount,$from){
    $payload = [
      'to_address' => Address::decode($to),
      'owner_address' => Address::decode($from),
      'amount' => $amount
    ];
    return $this->fullNode->post('/wallet/createtransaction',$payload);
  }  
  */
  
  function sendTrx(){
    $args = func_get_args();
    return $this->createTransaction(...$args);
  }
  
  function getContractEvents($contractAddress,$since){
    $api = '/event/contract/' . $contractAddress;
    $payload = [ 'since' => $since, 'sort' => 'block_timestamp' ];
    return $this->eventNode->get($api,$payload);
  }
  
  function getTransactionEvents($txid){
    $api = '/event/transaction/' . $txid;
    return $this->eventNode->get($api,[]);
  }
  
  /*
  function triggerSmartContract($contractAddress,$functionSelector,$parameter,$fromAddress,$feeLimit=1000000000,$callValue=0,$bandwidthLimit=0){
    $payload = [
      'contract_address' => Address::decode($contractAddress),
      'function_selector' => $functionSelector,
      'parameter' => $parameter,
      'owner_address' =>  Address::decode($fromAddress),
      'fee_limit'     =>  $feeLimit,
      'call_value'    =>  $callValue,
      'consume_user_resource_percent' =>  $bandwidthLimit,
    ];
    return $this->fullNode->post('/wallet/triggersmartcontract', $payload);
  }
  */
  
  function getAccount($address,$confirmed=true){
    $payload = [
      'address' => Address::decode($address)
    ];
    if($confirmed){
      return $this->fullNode->get('/wallet/getaccount',$payload);
    }else{
      return $this->solidityNode->get('/walletsolidity/getaccount',$payload);
    }
  }
    
  //todo
  function getBalance($address,$confirmed=true){
    $accountInfo = $this->getAccount($address,$confirmed);
    if(!isset($accountInfo->balance)){
      throw new \Exception('Balance error. Maybe you should send 10 trx to this address to activate it.');
    }
    return $accountInfo->balance;
  }
  
  function getUncomfirmedBalance($address){
    return $this->getBalance($address,false);
  }
    
  function getAccountNet($address){
    $payload = [
      'address' => Address::decode($address)
    ];
    return $this->fullNode->post('/wallet/getaccountnet',$payload);
  }
  
  function getBandwidth(){
    $args = func_get_args();
    return $this->getAccountNet(...$args);
  }
  
  function getAccountResource($address){
    $payload = [
      'address' => Address::decode($address)
    ];
    return $this->fullNode->post('/wallet/getaccountresource',$payload);
  }
  
  function getContract($address){
    $payload = [
      'value' => Address::decode($address)
    ];
    return $this->fullNode->get('/wallet/getcontract',$payload);
  }
  
  function getChainParameters(){
    return $this->fullNode->get('/wallet/getchainparameters',[]);
  }
  
  function getNodeInfo(){
    return $this->fullNode->get('/wallet/nodeinfo',[]);
  }
  
  function listNodes(){
    return $this->fullNode->get('/wallet/listnodes',[]);
  }
  
  //get|post?
  function getNowBlock($confirmed=true){
    if($confirmed){
      return $this->solidityNode->get('/walletsolidity/getnowblock',[]);
    }else{
      return $this->fullNode->get('/wallet/getnowblock',[]);
    }
  }
  
  function getCurrentBlock(){
    $args = func_get_args();
    return $this->getNowBlock(...$args);
  }
      
  function getBlockById($hash){
    $payload = [
      'value' => $hash
    ];
    return $this->fullNode->post('/wallet/getblockbyid',$payload);
  }
  
  function getBlockByHash(){
    $args = func_get_args();
    return $this->getBlockById(...$args);
  }
  
  function getBlockByNum($num){
    $payload = [
      'num' => $num
    ];
    return $this->fullNode->post('/wallet/getblockbynum',$payload);
  }
  
  function getBlockByNumber(){
    $args = func_get_args();
    return $this->getBlockByNum(...$args);
  }
  
  function getBlockByLimitNext($start,$end){
    $payload = [
      'startNum' => $start,
      'endNum' => $end
    ];
    return $this->fullNode->get('/wallet/getblockbylimitnext',$payload);
  }

  function getBlockRange(){
    $args = func_get_args();
    return $this->getBlockByLimitNext(...$args);
  }  
  
  function getTransactionById($txid,$confirmed=true){
    $payload = [
      'value' => $txid
    ];
    if($confirmed){
      return $this->solidityNode->post('/walletsolidity/gettransactionbyid',$payload);
    }else{
      return $this->fullNode->post('/wallet/gettransactionbyid',$payload);
    }
  }
  
  function getTransaction(){
    $args = func_get_args();
    return $this->gettransactionbyid(...$args);
  }
  
  function getConfirmedTransaction(){
    $args = func_get_args();
    return $this->getTransactionById(...$args);
  }
  
  function getTransactionInfoById($txid,$confirmed=true){
    $payload = [
      'value' => $txid
    ];
    if($confirmed){
      return $this->solidityNode->post('/walletsolidity/gettransactioninfobyid',$payload);
    }else{
      return $this->fullNode->post('/wallet/gettransactioninfobyid',$payload);
    }
  }
  
  function getTransactionInfo(){
    $args = func_get_args();
    return $this->getTransactionInfoById(...$args);
  }

  function getUnconfirmedTransactionInfo($txid){
    return $this->getTransactionInfoById($txid,false);
  }
  
  
  //all|from|to
  function getTransactionsByAddress($address,$direction='from',$offset=0,$limit=30){
    $payload = [
      'account' => [
        'address' => Address::decode($address)
      ],
      'offset' => $offset,
      'limit' => $limit
    ];
    $api = '/walletextension/gettransactions' . $direction . 'this';
    return $this->solidityNode->post($api,$payload);
  }
  
  function getReward($address,$confirmed=true){
    $payload = [
      'address' => Address::decode($address)
    ];
    if($confirmed){
      return $this->solidityNode->post('/walletsolidity/getreward',$payload);
    }else{
      return $this->fullNode->post('/wallet/getreward',$payload);
    }
  }
  
  function getUnconfirmedReward($address){
    return $this->getReward($address,false);
  }
  
  function getApprovedList($tx){
    return $this->fullNode->post('/wallet/getapprovedlist',$tx);
  }
  
  function getSignWeight($tx){
    return $this->fullNode->post('/wallet/getsignweight',$tx);
  }
  
  function listWitnesses($confirmed=true){
    if($confirmed){
      return $this->solidityNode->get('/walletsolidity/listwitnesses',[]);
    }else{
      return $this->fullNode->get('/walletsolidity/listwitnesses',[]);
    }
  }
  
  function listSuperRepresentatives(){
    $args = func_get_args();
    return $this->listWitnesses(...$args);
  }
  
  /*txbuilder*/
  function createTransaction($to,$amount,$from){
    $payload = [
      'to_address' => Address::decode($to),
      'owner_address' => Address::decode($from),
      'amount' => $amount
    ];
    $ret = $this->fullNode->post('/wallet/createtransaction',$payload);
    return $ret;
  }
  
  function transferAsset($to,$asset,$amount,$from){
    $payload = [
      'to_address' => Address::decode($to),
      'asset_name' => bin2hex($asset),
      'amount' => $amount,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/transferasset',$payload);
  }
  
  function sendAsset(){
    $args = func_get_args();
    return $this->transferAsset(...$args);
  }
  
  function sendToken(){
    $args = func_get_args();
    return $this->transferAsset(...$args);
  }
  
  function createAssetIssue($name,$abbr,$desc,$url,$supply,$trxRatio,$tokenRatio,$start,$end,$limit,$publicLimit,$frozenAmout,$frozenDays,$precision,$from){
    $payload = [
      'name' => bin2hex($name),
      'addr' => bin2hex($abbr),
      'total_supply' => $supply,
      'precision' => $precision,
      'trx_num' => $trxRatio,
      'num' => $tokenRatio,
      'start_time' => $start,
      'end_time' => $end,
      'description' => bin2hex($desc),
      'url' => bin2hex($url),
      'free_asset_net_limit' => $limit,
      'public_free_asset_net_limit' => $publicLimit,
      'frozen_supply' => [
        'frozen_amount' => $frozenAmount,
        'frozen_days' => $frozenDays
      ],
      'owner_address' => Address::decode($from)      
    ];
    return $this->fullNode->post('/wallet/createassetissue',$payload);
  }
  
  function createToken(){
    $args = func_get_args();
    return $this->createAssetIssue(...$args);
  }
  
  function createAsset(){
    $args = func_get_args();
    return $this->createAssetIssue(...$args);
  }
  
  function updateAsset($url,$desc,$limit,$publicLimit,$from){
    $payload = [
      'url' => bin2hex($url),
      'description' => bin2hex($desc),
      'new_limit' => $limit,
      'new_public_limit' => $publicLimit,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/updateasset',$payload);
  }
  
  function updateToken(){
    $args = func_get_args();
    return $this->updateAsset(...$args);
  }
    
  function participateAssetIssue($to,$asset,$amount,$from){
    $payload = [
      'to_address' => Address::decode($to),
      'asset_name' => bin2hex($asset),
      'amount' => $amount,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/participateassetissue',$payload);
  }
  
  function purchaseAsset(){
    $args = func_get_args();
    return $this->participateAssetIssue(...$args);
  }

  function purchaseToken(){
    $args = func_get_args();
    return $this->participateAssetIssue(...$args);
  }
  
  function getAssetIssueById($token,$confirmed=true){
    $payload = [
      'value' => $token
    ];
    if($confirmed){
      return $this->solidityNode->post('/walletsolidity/getassetissuebyid',$payload);
    }else{
      return $this->fullNode->post('/wallet/getassetissuebyid',$payload);
    }
  }
  
  function getTokenById(){
    $args = func_get_args();
    return $this->getAssetIssueById(...$args);
  }
  
  function getAssetIssueByName($token){
    $payload = [
      'value' => bin2hex($token)
    ];
    return $this->fullNode->post('/wallet/getassetissuebyname',$payload);
  }
  
  function getTokenFromId(){
    $args = func_get_args();
    return $this->getAssetIssueByName(...$args);
  }
  
  function getAssetIssueList(){
    return $this->fullNode->get('/wallet/getassetissuelist',[]);
  }
  
  function listTokens(){
    $args = func_get_args();
    return $this->getAssetIssueList(...$args);
  }
  
  function getAssetIssueListByName($token){
    $payload = [
      'value' => bin2hex($token)
    ];
    return $this->fullNode->post('/wallet/getassetissuelistbyname',$payload);
  }
  
  function getTokenListByName(){
    $args = func_get_args();
    return $this->getAssetIssueListByName(...$args);
  }
  
  function getAssetIssueByAccount($address){
    $payload = [
      'address' => Address::decode($address)
    ];
    return $this->fullNode->post('/wallet/getassetissuebyaccount',$payload);
  }
  
  function getTokenIssuedByAddress(){
    $args = func_get_args();
    return $this->getAssetIssueByAccount(...$args);
  }
  
  function freezeBalance($balance,$duration,$type,$from,$receiver=null){
    $payload = [
      'freeze_balance' => $balance,
      'freeze_duration' => $duration,
      'resource' => $type,
      'owner_address' => Address::decode($from),
      'receiver_address' => Address::decode($receiver)
    ];
    return $this->fullNode->post('/wallet/freezebalance',$payload);
  }
  
  function unfreezeBalance($type,$from,$receiver=null){
    $payload = [
      'resource' => $type,
      'owner_address' => Address::decode($from),
      'receiver' => Address::decode($receiver)
    ];
    return $this->fullNode->post('/wallet/unfreezebalance',$payload);
  }
  
  function withdrawBalance($address){
    $payload = [
      'owner_address' => Address::decode($address)
    ];
    return $this->fullNode->post('/wallet/withdrawbalance',$payload);
  }
  
  function withdrawBlockRewards(){
    $args = func_get_args();
    return $this->withdrawBalance(...$args);
  }
  
  function createWitness($address,$url){
    $payload = [
      'owner_address' => Address::decode($address),
      'url' => bin2hex($url)
    ];
    return $this->fullNode->post('/wallet/createwitness',$payload);
  }
  
  function applyForSR(){
    $args = func_get_args();
    return $this->createWitness(...$args);
  }
  
  function getBrokerage($address,$confirmed=true){
    $payload = [
      'address' => Address::decode($address)
    ];
    if($confirmed){
      return $this->solidityNode->post('/walletsolidity/getbrokerage',$payload);
    }else{
      return $this->fullNode->post('/wallet/getbrokerage',$payload);
    }
  }
  
  function getUncomfirmedBrokerage($address){
    return $this->getBrokerage($address,false);
  }
  
  function voteWitnessAccount($address,$votes){
    $payload = [
      'owner_address' => Address::decode($address),
      'votes' => $votes
    ];
    return $this->fullNode->post('/wallet/votewitnessaccount',$payload);
  }
  
  function vote(){
    $args = func_get_args();
    return $this->voteWitnessAccount(...$args);
  }
  
  //trc20
  function deployContract($abi,$bytecode,$parameter,$name,$value,$from){
    $payload = [
      'abi' => $abi,
      'bytecode' => $bytecode,
      'parameter' => $parameter,
      'name' => $name,
      'call_value' => $value,
      'owner_address' => Address::decode($from),
      'fee_limit' => 1000000000,
      'origin_energy_limit' => 10000000,
      'consume_user_resource_percent' => 100
    ];
    return $this->fullNode->post('/wallet/deploycontract',$payload);
  }
  
  function createSmartContract(){
    $args = func_get_args();
    return $this->deployContract(...$args);
  }
  
  function triggerSmartContract($contract,$function,$parameter,$value,$from){
    $payload = [
      'contract_address' => Address::decode($contract),
      'function_selector' => $function,
      'parameter' => $parameter,
      'call_value' => $value,
      'owner_address' => Address::decode($from),
      'fee_limit' => 1000000000
    ];
    return $this->fullNode->post('/wallet/triggersmartcontract',$payload);
  }
  
  function triggerConstantSmartContract($contract,$function,$parameter,$value,$from,$confirmed=true){
    $payload = [
      'contract_address' => Address::decode($contract),
      'function_selector' => $function,
      'parameter' => $parameter,
      'call_value' => $value,
      'owner_address' => Address::decode($from),
      'fee_limit' => 1000000000
    ];
    if($confirmed){
      return $this->solidityNode->post('/walletsolidity/triggerconstantsmartcontract',$payload);
    }else{
      return $this->fullNode->post('/wallet/triggerconstantsmartcontract',$payload);
    }
  }
  
  function clearAbi($contract,$from){
    $payload = [
      'contract_address' => Address::decode($contract),
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/clearabi',$payload);
  }
  
  function updateSetting($contract,$userPercent,$from){
    $payload = [
      'contract_address' => Address::decode($contract),
      'consume_user_resource_percent' => $userPercent,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/updatesetting',$payload);
  }
  
  function updateEnergyLimit($contract,$limit,$from){
    $payload = [
      'contract_address' => Address::decode($contract),
      'origin_energy_limit' => $limit,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/updateenergylimit',$payload);
  }
  
  function updateBrokerage($brokerage,$from){
    $payload = [
      'brokerage' => $brokerage,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/updatebrokerage',$payload);
  }
  
  function updateAccount($name,$from){
    $payload = [
      'account_name' => bin2hex($name),
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/updateaccount',$payload);
  }
  
  function accountPermissionUpdate($ownerPermits,$witnessPermits,$activePermits,$from){
    $payload = [
      'owner' => $ownerPermits,
      'witness' => $witnessPermits,
      'active' => $activePermits,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/accountpermissionupdate',$payload);
  }
  
  function setAccountId($id,$from){
    $payload = [
      'account_id' => $id,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/setaccountid',$payload);
  }
  
  //dex
  function proposalCreate($parameters,$from){
    $payload = [
      'parameters' => $parameters,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/proposalcreate',$payload);
  }
  
  function createProposal(){
    $args = func_get_args();
    return $this->proposalCreate(...$args);
  }
  
  function listProposals(){
    return $this->fullNode->post('/wallet/listproposals',[]);
  }
  
  function proposalDelete($id,$from){
    $payload = [
      'proposal_id' => $id,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/proposaldelete',$payload);
  }
  
  function deleteProposal(){
    $args = func_get_args();
    return $this->proposalDelete(...$args);
  }
  
  function proposalApprove($id,$from){
    $payload = [
      'proposal_id' => $id,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/proposalapprove',$payload);
  }
  
  function voteProposal(){
    $args = func_get_args();
    return $this->proposalApprove(...$args);
  }
  
  function exchangeCreate($token1,$balance1,$token2,$balance2,$from){
    $payload = [
      'first_token_id' => bin2hex($token1),
      'first_token_balance' => $balance1,
      'second_token_id' => bin2hex($token2),
      'second_token_balance' => $balance2,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/exchangecreate',$payload);
  }
  
  function listExchanges(){
    return $this->fullNode->post('/wallet/listexchanges',[]);
  }
  
  function getPaginatedExchangeList($offset=0,$limit=30){
    $payload = [
      'offset' => $offset,
      'limit' => $limit
    ];
    return $this->fullNode->post('/wallet/getpaginatedexchangelist',$payload);
  }
  
  function listExchangePaginated(){
    $args = func_get_args();
    return $this->getPaginatedExchangeList(...$args);
  }
  
  function exchangeInject($exchange,$token,$quant,$from){
    $payload = [
      'exchange_id' => $exchange,
      'token_id' => bin2hex($token),
      'quant' => $quant,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/exchangeinject',$payload);
  }
  
  function injectExchangeToken(){
    $args = func_get_args();
    return $this->exchangeInject(...$args);
  }
  
  function exchangeWithdraw($exchange,$token,$quant,$from){
    $payload = [
      'exchange_id' => $exchange,
      'token_id' => bin2hex($token),
      'quant' => $quant,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/exchangewithdraw',$payload);
  }
  
  function withdrawExchangeTokens(){
    $args = func_get_args();
    return $this->exchangeWithdraw(...$args);
  }
  
  function exchangeTransaction($exchange,$token,$quant,$expected,$from){
    $payload = [
      'exchange_id' => $exchange,
      'token_id' => bin2hex($token),
      'quant' => $quant,
      'expected' => $expected,
      'owner_address' => Address::decode($from)
    ];
    return $this->fullNode->post('/wallet/exchangetransaction',$payload);
  }
  
  function getExchangeById($id){
    $payload = [
      'id' => $id
    ];
    return $this->fullNode->post('/wallet/getexchangebyid',$payload);
  }
  
  function tradeExchangeTokens(){
    $args = func_get_args();
    return $this->exchangeTransaction(...$args);
  }
}

