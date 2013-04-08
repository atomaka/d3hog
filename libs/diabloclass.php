<?php

class DiabloClassFactory {
  function createClassObject($class, $stats) {
    if(!DiabloClassFactory::isValidClass($class)) {
      return false;
    }

    include_once(__DIR__ . "/$class.php");
    return new $class($stats);
  }

  private 
    function isValidClass($class) {
      $classes = array(
        'barbarian',
        'demonhunter'
      );

      return in_array($class, $classes);
    }
}

class DiabloClass {
  var $class;
  var $stats = array();

  function __construct($stats) {
    $this->stats = $stats;
  }

  function hallScore() {
    return $this->DPSScore() * $this->EHPScore() * $this->sustainScore() 
      * $this->moveScore() * $this->paragonScore() * $this->miscScore();
  }

  function DPSScore() {
    return $this->stats->getStat('DPS Unbuffed') / 1000;
  }

  function EHPScore() {
    return 0;
  }

  function sustainScore() {
    $effectiveLS = $this->stats->getStat('DPS Unbuffed') * 
      $this->stats->getStat('Life Steal') * .5;
    $mitigation = $this->stats->getStat('EHP Unbuffed') / $this->stats->getStat('Life');

    $rawSustainScore = 1 + $mitigation * ($this->stats->getStat('Life on Hit') * 
      (1 + ($this->stats->getStat('Attacks per Second') - 1) / 2) + 
      $effectiveLS + $this->stats->getStat('Life per Second')) / 
      ($this->stats->getStat('Life') * $this->EHPScore() * 10000 / 
      $this->stats->getStat('EHP Unbuffed'));

    if($rawSustainScore <= 1.5) {
      return $rawSustainScore;
    } elseif(1.5 < $rawSustainScore && $rawSustainScore <= 2) {
      return 1.5 + ($rawSustainScore - 1.5) / 2;
    } elseif(2 < $rawSustainScore && $rawSustainScore <= 3) {
      return 1.75 + ($rawSustainScore - 2) / 4;
    } else {
      return 2 + ($rawSustainScore - 3) / 10;
    }
  }

  function moveScore() {
    return ($this->stats->getStat('Movement Speed') > .25) ? 1.25 : 1 + $this->stats->getStat('Movement Speed');
  }

  function paragonScore() {
    return 1 + $this->stats->getStat('Paragon Level') / 2 / 100;
  }

  function miscScore() {
    return 1;
  }
}
