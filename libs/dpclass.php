<?php

class DPClassFactory {
  function createClassObject($characterPage) {
    $class = DPClassFactory::findClass($characterPage);

    include_once(__DIR__ . "/$class.php");

    switch($class) {
      case 'barbarian':
        return new Barbarian($characterPage);
      case 'wizard':
        return new Wizard($characterPage);
      default:
        return false;
    }
  }

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

  function __construct($characterPage) {
    $this->dpHTML = $characterPage;

    $this->class = get_class($this);

    $this->parseStats();
  }

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

    $this->stats['All Elemental Damage'] = $this->elementalDamage();
    // $this->stats['DPS Unbuffed'] = $this->modifiedDPSUnbuffed();
  }

  function elementalDamage() {
    $totalElemental = 0;
    foreach($this->stats as $stat => $value) {
      if(preg_match('/\+DPS \(.*\)/', $stat) > 0) {
        $totalElemental += $value;
      }
    }

    return ($totalElemental > 0) ? $totalElemental + 1 : 0;
  }

  function modifiedDPSUnbuffed() {
    return $this->getStat('DPS Unbuffed') * max(1, 1 + ($this->getStat('All Elemental Damage') / 2))
      * max(1, 1 + ($this->getStat('+DPS Against Elites') / 2));
  }

  function hallScore() {
    return $this->DPSScore() * $this->EHPScore() * $this->sustainScore() 
      * $this->moveScore() * $this->paragonScore() * $this->miscScore();
  }

  function DPSScore() {
    return $this->modifiedDPSUnbuffed() / 1000;
  }

  function EHPScore() {}

  function sustainScore() {
    $effectiveLS = $this->getStat('DPS Unbuffed') * $this->getStat('Life Steal') * .2;

    return 1 + ($this->getStat('Life on Hit') + $this->getStat('Life per Second') + $effectiveLS)
      * 10 / $this->getStat('Life');
  }

  function moveScore() {
    return ($this->getStat('Movement Speed') > .25) ? 1.25 : 1 + $this->getStat('Movement Speed');
  }

  function paragonScore() {
    return 1 + $this->stats['Paragon Level'] / 2 / 100;
  }

  function miscScore() {
    return 1 + ($this->getStat('Melee Damage Reduction') + $this->getStat('Missile Damage Reduction')
      + $this->getStat('Exp Bonus')) / 2;
  }

  function getStat($name) {
    return (isset($this->stats[$name])) ? $this->stats[$name] : 0;
  }
}
