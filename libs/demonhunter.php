<?php
class DemonHunter extends DiabloClass { 
  function __construct($stats) {
    parent::__construct($stats);
  }
  
  function EHPScore() {
    $ehp = $this->stats->getStat('EHP Unbuffed');

    if($ehp <= 500000) {
      return $ehp / 10000;
    } elseif(500000 < $ehp && $ehp <= 1000000) {
      return 50 + ($ehp-500000) / 20000;
    } elseif(1000000 < $ehp && $ehp <= 2000000) {
      return 75 + ($ehp - 1000000) / 40000;
    } else {
      return 100 + ($ehp - 2000000) / 100000;
    }
  }

  function sustainScore() {
    $effectiveLs = $this->stats->getStat('DPS Unbuffed') * 
      $this->stats->getStat('Life Steal') * .2;
    $mitigation = $this->stats->getStat('EHP Unbuffed') / $this->stats->getStat('Life');

    return 1 + $mitigation * ($this->stats->getStat('Life on Hit') * 
      ($this->stats->getStat('Attacks per Second') + 1) / 2 + 
      $this->stats->getStat('Life per Second') + $effectiveLs) / 
      ($this->stats->getStat('Life') * $this->EHPScore() * 10000 / 
      $this->stats->getStat('EHP Unbuffed'));
  }

  function miscScore() {
    return 1 + ($this->stats->getStat('+Maximum Discipline') / 2 +
      $this->stats->getStat('+Hatred Regenerated per Second') * 2 +
      $this->stats->getStat('+Discipline Regenerated per Second') * 15) / 100;
  }
}