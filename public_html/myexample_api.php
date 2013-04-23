<?php

error_reporting(E_ALL);

require_once ('../class/Externalize.php');
require_once('MyExample.php');
$ext = new Externalize('MyExample');
$ext->handleRequest();