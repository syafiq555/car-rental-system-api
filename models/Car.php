<?php

class Car implements iGetManufacturer, iGetModel, iNullCheck {
  public $id;
  public $plate_number;
  public $price_per_hour;
  private $model;
  public $model_name;
  public $manufacturer_name;
  public $year;

  public function __construct(Model $model) {
    $this->model = $model;
    $model->getManufacturer() ? $this->manufacturer_name = $model->getManufacturer(): '';
    $model->model_name ? $this->model_name = $model->model_name: '';
  }

  public function getModel() {
    return $this->model->model_name;
  }

  public function getManufacturer() {
    return $this->model->getManufacturer();
  }

  public function getManufacturerId() {
    return $this->model->getManufacturerId();
  }

  public function getModelId() {
    return $this->model->id;
  }

  public function checkNull() {
    return $this->plate_number&&$this->price_per_hour&&$this->getModelId()&&$this->year;
  }
}