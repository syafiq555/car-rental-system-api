<?php
include_once('Manufacturer.php');

class Model implements iGetManufacturer{
  public $id;
  public $model_name;
  private $manufacturer;

  public function __construct(Manufacturer $manufacturer) {
    $this->manufacturer = $manufacturer;
  }

  public function getManufacturer() {
    return $this->manufacturer->manufacturer_name;
  }
}