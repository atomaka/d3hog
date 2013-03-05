<?php

class DiabloProgressStats extends Stats {
  function parse() {
    $this->class = $this->findClass();
    $this->parseStats();
  }

  protected
    function isParagonMaxed() {
      return $this->getStat('Paragon Level') == 100;
    }

  private
    function findClass() {
      preg_match('{<span class="diablo_.*?">(.*?)</span>}', $this->html, $class);

      return str_replace(' ', '', strtolower($class[1]));
    }

    function parseStats() {
      preg_match_all('{<div class="char_attr"><nobr><span class="char_attr_name">(.*?):<\/span> <span class="char_attr_value">(.*?)<\/span><\/nobr><\/div>}', $this->html, $attributes);

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
      $totalElemental = 0;
      foreach($this->stats as $stat => $value) {
        if(preg_match('/\+DPS \(.*\)/', $stat) > 0) {
          $totalElemental += $value;
        }
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