<?php
include_once('Model.php');
include_once('Base.php');

class Car extends Base{
  private $id;
  private $plate_number;
  private $price_per_hour;
  private $model;
  private $year;

  public function __construct(Model $model) {
    $this->model = $model;
  }
}