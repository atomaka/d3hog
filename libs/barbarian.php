<?php
class Barbarian extends DiabloClass { 
  function __construct($stats) {
    $this->class = $stats->class;

    parent::__construct($stats);
  }

  function EHPScore() {
    $ehp = $this->stats->getStat('EHP Unbuffed');

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

  private 
    function calculateEHP() {
      $incoming_attack = 100000;
      $block_amount = 4206;
      $ar_red = $this->getStat('Resistance') / 
        ($this->getStat('Resistance') + 315);
      $armor_red = $this->getStat('Armor') / 
        ($this->getStat('Armor') + 3150);
      $inherent_red = .3;

      $total_red = (1 - $ar_red) * (1 - $armor_red) * (1 - $inherent_red);
      $raw_ehp = $this->getStat('Life') / $total_red;

      $net = $incoming_attack * $total_red;

      $after_block = $net * (1 - $this->getStat('Block')) + ($net - $block_amount)
        * $this->getStat('Block');

      $new_mit = $after_block / $incoming_attack;

      $raw_no_dodge = $this->getStat('Life') / $new_mit;
      $net_no_dodge = $raw_no_dodge;

      $raw_ehp_dodge = $net_no_dodge / (1 - $this->getStat('Dodge Chance'));
      $net_ehp_dodge = ($raw_ehp_dodge - $net_no_dodge) * .75 + $net_no_dodge;

      $final_ehp = $net_ehp_dodge * (1 + $this->getStat('Melee Damage Reduction')
        + $this->getStat('Missile Damage Reduction') / 2);

      $this->stats->stats['EHP Unbuffed'] = $final_ehp;
    }
}