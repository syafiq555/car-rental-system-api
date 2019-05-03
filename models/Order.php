<?php
include_once('User.php');
include_once('Order.php');

class Order {
  private $id;
  private $car;
  private $user;
  private $approved;
  private $total_price;

  public function __construct(Car $car, User $user) {
    $this->car = $car;
    $this->user = $user;
  }

  public function __get($name) {
    return $this->$name;
  }

  public function __set($name, $value) {
      $this->$name = $value;
  }
}