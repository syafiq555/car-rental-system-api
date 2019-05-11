<?php

class Car implements iGetManufacturer, iGetModel, iNullCheck {
  public $id;
  public $plate_number;
  public $price_per_hour;
  private $model;
  public $model_name;
  public $manufacturer_name;
  public $year;
  public $approved;

  public function __construct(Model $model=null) {
    if ($model) {
      $this->model = $model;
    }
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