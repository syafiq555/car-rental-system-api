<?php
include_once('Base.php');

class User extends Base {
  private $id;
  private $username;
  private $first_name;
  private $last_name;
  private $email;
  private $password;
  private $role;
  private $ic_number;
  private $mobile_phone;

  public function __construct() {

  }

  public function __get($name) {
    return $this->$name;
  }

  public function __set($name, $value) {
      $this->$name = $value;
  }
}