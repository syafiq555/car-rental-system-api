<?php
include_once('Manufacturer.php');

class Model implements iGetManufacturer{
  public $id;
  public $model_name;
  private $manufacturer;

  public function __construct(Manufacturer $manufacturer=null) {
    $this->manufacturer = $manufacturer;
  }

  public function getManufacturer() {
    return $this->manufacturer->manufacturer_name;
  }

  public function getManufacturerId() {
    return $this->manufacturer->id;
  }
}