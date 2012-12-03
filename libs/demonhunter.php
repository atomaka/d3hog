<?php
class DemonHunter extends DPClass { 
  function EHPScore() {
    $ehp = $this->getStat('EHP Unbuffed');

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
    $effectiveLs = $this->getStat('DPS Unbuffed') * 
      $this->getStat('Life Steal') * .2;
    $mitigation = $this->getStat('EHP Unbuffed') / $this->getStat('Life');

    return 1 + $mitigation * ($this->getStat('Life on Hit') * 
      ($this->getStat('Attacks per Second') + 1) / 2 + 
      $this->getStat('Life per Second') + $effectiveLs) / 
      ($this->getStat('Life') * $this->EHPScore() * 10000 / 
      $this->getStat('EHP Unbuffed'));
  }

  function miscScore() {
    return 1 + ($this->getStat('+Maximum Discipline') / 2 +
      $this->getStat('+Hatred Regenerated per Second') * 2 +
      $this->getStat('+Discipline Regenerated per Second') * 15) / 100;
  }
}