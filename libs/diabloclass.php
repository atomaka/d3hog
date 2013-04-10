<?php

class DiabloClassFactory {
  function createClassObject($class, $stats, $type) {
    if(!DiabloClassFactory::isValidClass($class)) {
      return false;
    }

    include_once(__DIR__ . "/$class.php");
    return new $class($stats, $type);
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
  var $type;
  var $stats = array();

  function __construct($stats, $type) {
    $this->stats = $stats;
    $this->type = $type;
    $this->class = $stats->class;

    $this->modifyExpBonus();

    $this->stats->stats['All Elemental Damage'] = $this->elementalDamage();

    $this->modifyDPSUnbuffed();
    $this->modifyEHP();    
  }

  function hallScore() {
    $hallScore = $this->DPSScore() * $this->EHPScore() * $this->sustainScore() 
      * $this->moveScore() * $this->miscScore();
    if($this->type == 'pve') {
      $hallScore *= $this->paragonScore();
    }
    return $hallScore;
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
    $loh = $this->stats->getStat('Life on Hit');

    if($this->type == 'pvp') {
      $effectiveLS = 0;
      $loh = 0;
    }

    $rawSustainScore = 1 + $mitigation * ($loh * 
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

  protected
    function isParagonMaxed() {
      return $this->stats->getStat('Paragon Level') == 100;
    }

    function modifyDPSUnbuffed() {
      if($this->type == 'pvp') {
        $eliteDivisor = 1;
      } else {
        $eliteDivisor = 2;
      }
      $this->stats->stats['DPS Unbuffed'] = $this->stats->getStat('DPS Unbuffed') * 
        max(1, 1 + ($this->stats->getStat('+DPS Against Elites') / $eliteDivisor));
    }

  private
    function elementalDamage() {
      $totalElemental = 0;
      foreach($this->stats as $stat => $value) {
        if(preg_match('/\+DPS \(.*\)/', $stat) > 0) {
          $totalElemental += $value;
        }
      }

      return ($totalElemental > 0) ? $totalElemental : 0;
    }

    function calculateGemLife() {
      if($this->isParagonMaxed() || $this->type == 'pvp') return 0;

      switch($this->stats->getStat('Exp Bonus')) {
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
      if($this->stats->getStat('Exp Bonus') >= .35) {
        $this->stats->stats['Exp Bonus'] = $this->stats->getStat('Exp Bonus') - .35;
      }
    }

    function modifyEHP() {
      $this->calculateEHP();
    }
}
