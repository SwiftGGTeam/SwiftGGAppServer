<?php
require 'flight/Flight.php';

Flight::route('/', function(){
    echo 'Hello SwiftGG';
});

Flight::start();
?>
