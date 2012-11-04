<?php
class Barbarian extends DPClass {
  function EHPScore() {
    $ehp = $this->getStat('EHP Unbuffed');

    if($ehp < 1000000) {
      return $ehp / 10000;
    } elseif(1000000 <= $ehp && $ehp <= 2000000) {
      return 100 + ($ehp - 1000000) / 20000;
    } elseif(2000000 <= $ehp && $ehp <= 5000000) {
      return 150 + ($ehp - 2000000) / 40000;
    } elseif($ehp <= 5000000) {
      return 225 + ($ehp - 5000000) / 100000;
    }
  }
}