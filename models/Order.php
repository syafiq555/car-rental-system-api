<?php
include_once('User.php');
include_once('Order.php');
include_once('Base.php');

class Order extends Base{
  private $id;
  private $car;
  private $user;
  private $approved;
  private $total_price;

  public function __construct(Car $car, User $user) {
    $this->car = $car;
    $this->user = $user;
  }
}