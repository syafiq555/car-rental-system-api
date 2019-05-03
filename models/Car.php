<?php
include_once('Model.php');

class Car {
  private $id;
  private $plate_number;
  private $price_per_hour;
  private $model;
  private $year;

  public function __construct(Model $model) {
    $this->model = $model;
  }

  public function __get($name) {
    return $this->$name;
  }

  public function __set($name, $value) {
      $this->$name = $value;
  }
}