<?php
include_once('User.php');
include_once('Order.php');

class Order {
  public $id;
  public $car;
  public $user;
  public $approved;
  public $total_price;
  public $date_to;
  public $date_from;
  public $time_to;
  public $time_from;

  public function __construct(Car $car, User $user) {
    $this->car = $car;
    $this->user = $user;
  }
}