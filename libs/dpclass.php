<?php

class DPClassFactory {
  function createClassObject($characterPage, $elementalOnWeapon) {
    $class = DPClassFactory::findClass($characterPage);

    include_once(__DIR__ . "/$class.php");

    switch($class) {
      case 'barbarian':
        return new Barbarian($characterPage, $elementalOnWeapon);
      case 'wizard':
        return new Wizard($characterPage, $elementalOnWeapon);
      default:
        return false;
    }
  }

  private
    function findClass($characterPage) {
      preg_match('{<span class="diablo_.*?">(.*?)</span>}', $characterPage, $class);

      return strtolower($class[1]);
    }
}

class DPClass {
  var $dpHTML;
  var $class;
  var $stats = array();
  var $items = array();

  function __construct($characterPage, $elementalOnWeapon) {
    $this->dpHTML = $characterPage;

    $this->class = get_class($this);
    $this->elementalOnWeapon = $elementalOnWeapon;

    $this->parseStats();
  }

  function hallScore() {
    return $this->DPSScore() * $this->EHPScore() * $this->sustainScore() 
      * $this->moveScore() * $this->paragonScore();
  }

  function DPSScore() {
    return $this->getStat('DPS Unbuffed') / 1000;
  }

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

  function sustainScore() {
    $effectiveLS = $this->getStat('DPS Unbuffed') * 
      $this->getStat('Life Steal') * .5;
    $mitigation = $this->getStat('EHP Unbuffed') / $this->getStat('Life');

    $rawSustainScore = 1 + $mitigation * ($this->getStat('Life on Hit') * 
      (1 + ($this->getStat('Attacks per Second') - 1) / 2) + 
      $effectiveLS + $this->getStat('Life per Second')) / 
      ($this->getStat('Life') * $this->EHPScore() * 10000 / 
      $this->getStat('EHP Unbuffed'));

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
    return ($this->getStat('Movement Speed') > .25) ? 1.25 : 1 + $this->getStat('Movement Speed');
  }

  function paragonScore() {
    return 1 + $this->stats['Paragon Level'] / 2 / 100;
  }

  protected
    function isParagonMaxed() {
      return $this->getStat('Paragon Level') == 100;
    }
    
    function getStat($name) {
      return (isset($this->stats[$name])) ? $this->stats[$name] : 0;
    }

  private
    function parseStats() {
      preg_match_all('{<div class="char_attr"><nobr><span class="char_attr_name">(.*?):<\/span> <span class="char_attr_value">(.*?)<\/span><\/nobr><\/div>}', $this->dpHTML, $attributes);

      for($i = 0; $i < count($attributes[0]); $i++) {
        $attributes[2][$i] = str_replace(',', '', $attributes[2][$i]);
        if(preg_match('/%/', $attributes[2][$i]) > 0) {
          $attributes[2][$i] = str_replace('%', '', $attributes[2][$i]);
          $attributes[2][$i] /= 100;
        }
        $this->stats[$attributes[1][$i]] = $attributes[2][$i];
      }

      $this->stats['Gem Life'] = $this->calculateGemLife();
      $this->modifyExpBonus();

      $this->stats['All Elemental Damage'] = $this->elementalDamage();
      
      $this->modifyDPSUnbuffed();
      $this->modifyEHP();
      $this->modifyHP();
    }

    function elementalDamage() {
      $totalElemental = 1;
      foreach($this->stats as $stat => $value) {
        if(preg_match('/\+DPS \(.*\)/', $stat) > 0) {
          $totalElemental += $value;
        }
      }

      if($this->elementalOnWeapon && $totalElemental != 1) {
        $totalElemental *= .5;
      }

      return ($totalElemental > 0) ? $totalElemental : 0;
    }

    function calculateGemLife() {
      if($this->isParagonMaxed()) return 0;

      switch($this->getStat('Exp Bonus')) {
        case .19: return .12;
        case .21: return .14;
        case .25: return .15;
        case .27: return .16;
        case .29: return .17;
        case .31: return .18;
        default: return 0;
      }
    }

    function modifyExpBonus() {
      if($this->getStat('Exp Bonus') >= .35) {
        $this->stats['Exp Bonus'] = $this->getStat('Exp Bonus') - .35;
      }
    }

    function modifyDPSUnbuffed() {
      $this->stats['DPS Unbuffed'] = $this->getStat('DPS Unbuffed') * 
        $this->getStat('All Elemental Damage') * 
        max(1, 1 + ($this->getStat('+DPS Against Elites') / 2));
    }

    function modifyEHP() {
      $this->stats['EHP Unbuffed'] = $this->getStat('EHP Unbuffed') * 
        (1 + $this->getStat('Life Bonus') + $this->getStat('Gem Life')) / 
        (1 + $this->getStat('Life Bonus'));
    }

    function modifyHP() {
      $this->stats['Life'] = $this->getStat('Life') * 
        (1 + $this->getStat('Life Bonus') + $this->getStat('Gem Life')) / 
        (1 + $this->getStat('Life Bonus'));
    }
}
