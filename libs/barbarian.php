<?php
class Barbarian extends DiabloClass { 
  function __construct($stats, $type) {
    parent::__construct($stats, $type);
  }

  function EHPScore() {
    $ehp = $this->stats->getStat('EHP Unbuffed');

    if($this->type == 'pvp') {
      return $ehp / 10000;
    }

    if($ehp < 1000000) {
      return $ehp / 10000;
    } elseif(1000000 <= $ehp && $ehp <= 2000000) {
      return 100 + ($ehp - 1000000) / 20000;
    } elseif(2000000 <= $ehp && $ehp <= 5000000) {
      return 150 + ($ehp - 2000000) / 40000;
    } elseif($ehp >= 5000000) {
      return 225 + ($ehp - 5000000) / 100000;
    }
  }

  protected 
    function calculateEHP() {
      if($this->type == 'pvp') {
        $ar_mod = 300;$armor_mod = 3000;$inherent_red = .35;$incoming_attack = 250000;
        $net_mod = .50; // no idea what this is for
      } else {
        $ar_mod = 315;$armor_mod = 3150;$inherent_red = .30;$incoming_attack = 100000;
        $net_mod = .75; // no idea what this is for
      }

      $block_amount = 4206;
      $ar_red = $this->stats->getStat('Resistance') / 
        ($this->stats->getStat('Resistance') + $ar_mod);
      $armor_red = $this->stats->getStat('Armor') / 
        ($this->stats->getStat('Armor') + $armor_mod);

      $total_red = (1 - $ar_red) * (1 - $armor_red) * (1 - $inherent_red);
      $raw_ehp = $this->stats->getStat('Life') / $total_red;

      $net = $incoming_attack * $total_red;

      $after_block = $net * (1 - $this->stats->getStat('Block')) + ($net - $block_amount)
        * $this->stats->getStat('Block');

      $new_mit = $after_block / $incoming_attack;

      $raw_no_dodge = $this->stats->getStat('Life') / $new_mit;
      if($this->type == 'pvp') {
        $net_no_dodge = ($raw_no_dodge - $raw_ehp) * .75 + $raw_ehp;
      } else {
        $net_no_dodge = $raw_no_dodge;
      }

      $raw_ehp_dodge = $net_no_dodge / (1 - $this->stats->getStat('Dodge Chance'));
      $net_ehp_dodge = ($raw_ehp_dodge - $net_no_dodge) * $net_mod + $net_no_dodge;

      $final_ehp = $net_ehp_dodge * (1 + $this->stats->getStat('Melee Damage Reduction')
        + $this->stats->getStat('Missile Damage Reduction') / 2);

      $this->stats->stats['EHP Unbuffed'] = $final_ehp;
    }
}