<?php

class User {
  private $id;
  private $username;
  private $first_name;
  private $last_name;
  private $email;
  private $password;
  private $role;
  private $ic_number;

  public function __construct() {

  }

  public function __get($name) {
    return $this->$name;
  }

  public function __set($name, $value) {
      $this->$name = $value;
  }
}