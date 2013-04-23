<?php

class MyExample {

  const CAN_YOU = "see 'me'?";

  public function __construct() {
    session_start();
    // This could be a DB connection
    if (!isset($_SESSION['count'])) {
      $_SESSION['count'] = 0;
    }
  }

  public function hello($who, $newCount = null) {
    if ($newCount != null) {
      $_SESSION['count'] = $newCount;
    }
    $_SESSION['count']++;
    return "Hi there $who this is PHP speaking and you have asked {$_SESSION['count']} time(s).";
  }

}

