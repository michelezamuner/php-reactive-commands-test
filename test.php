<?php
class Test
{
  protected $var = 0;

  public function fun()
  {
    $var = &$this->var;
    $this->fun1(function() use(&$var) {
      $var++;
    });
    echo $this->var, PHP_EOL;
  }

  protected function fun1(closure $callback)
  {
    $callback();
  }

  abstract public function isHanging();
}

$test = new Test();
$test->fun();
