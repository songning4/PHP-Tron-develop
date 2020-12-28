<?php
namespace TronTool;

class ExceptionHandler{
  function __construct(){
    error_reporting(0);
    set_exception_handler(function($e){
      echo "\n=================TRONTOOL EXCEPTION HANDLER====================" . PHP_EOL;
      echo "- Message: " . $e->getMessage() . PHP_EOL;
      echo "- File: " . $e->getFile() . PHP_EOL;
      echo "- Line: " . $e->getLine() . PHP_EOL;
      echo "- Stack Trace: " . PHP_EOL;
      foreach($e->getTrace() as $trace){
        $msg = join(' - ',[
          $trace['function'],
          $trace['class'],
          $trace['line']
        ]);
        echo "  - " . $msg  .   PHP_EOL;
      }
      echo "===============================================================\n".PHP_EOL;
    });
  }
}