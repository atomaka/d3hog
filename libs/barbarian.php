<?php
class Barbarian extends DiabloClass { 
  function __construct($stats) {
    $this->class = $stats->class;

    parent::__construct($stats);
  }
}