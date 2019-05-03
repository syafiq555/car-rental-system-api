<?php
include_once('Base.php');

class Manufacturer extends Base{
  private $id;
  private $manufacturer_name;

  public function __construct() {
    
  }

  public function __get($name) {
    return $this->$name;
  }

  public function __set($name, $value) {
      $this->$name = $value;
  }
}