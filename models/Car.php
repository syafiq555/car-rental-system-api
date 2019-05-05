<?php

class Car implements iGetManufacturer, iGetModel {
  public $id;
  public $plate_number;
  public $price_per_hour;
  private $model;
  public $year;

  public function __construct(Model $model) {
    $this->model = $model;
  }

  public function getModel() {
    return $this->model->model_name;
  }

  public function getManufacturer() {
    return $this->model->getManufacturer();
  }
}