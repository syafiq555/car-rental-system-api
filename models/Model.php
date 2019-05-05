<?php
include_once('Manufacturer.php');

class Model implements iGetManufacturer{
  public $id;
  public $model_name;
  public $manufacturer_name;
  private $manufacturer;

  public function __construct(Manufacturer $manufacturer) {
    $this->manufacturer = $manufacturer;
    $this->manufacturer_name = $manufacturer->manufacturer_name;
  }

  public function getManufacturer() {
    return $this->manufacturer->manufacturer_name;
  }
}