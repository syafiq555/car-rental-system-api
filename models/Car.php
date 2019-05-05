<?php

class Car implements iGetManufacturer, iGetModel {
  public $id;
  public $plate_number;
  public $price_per_hour;
  private $model;
  public $model_name;
  public $manufacturer_name;
  public $year;

  public function __construct(Model $model) {
    $this->model = $model;
    $this->manufacturer_name = $model->getManufacturer();
    $this->model_name = $model->model_name;
  }

  public function getModel() {
    return $this->model->model_name;
  }

  public function getManufacturer() {
    return $this->model->getManufacturer();
  }
}