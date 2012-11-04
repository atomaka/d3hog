<?php
class Wizard extends DPClass {
  function EHPScore() {
    $ehp = $this->getStat('EHP Unbuffed');

    switch($ehp) {
      case ($ehp < 250000):
        return $ehp / 5000;
      case ($ehp >= 250000 && $ehp < 450000):
        return 50 + ($ehp - 250000) / 4000;
      case ($ehp >= 450000 && $ehp < 600000):
        return 100 + ($ehp - 450000) / 3000;
      case ($ehp >= 600000 && $ehp < 1000000):                                    
        return 150 + ($ehp - 600000) / 8000;
      case ($ehp >= 1000000):
        return 200 + ($ehp - 1000000) / 25000;
    }
  }  
}
