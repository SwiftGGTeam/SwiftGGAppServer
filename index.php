<?php
/**
 * Demo Application
 */
require __DIR__."/bootstrap/init.php";

Flight::before("start", array("Controller", "init"));
Flight::start();

