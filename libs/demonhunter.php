<?php
class DemonHunter extends DiabloClass { 
  function __construct($stats, $type) {
    parent::__construct($stats, $type);
  }

  function DPSScore() {
    if($this->type == 'pvp') {
      $eliteDivisor = 1;
    } else {
      $eliteDivisor = 2;
    }
    $dps = $this->stats->getStat('DPS Unbuffed') * 
      max(1, 1 + ($this->stats->getStat('+DPS Against Elites') / $eliteDivisor));

    return $dps / 1000;
  }

  function EHPScore() {
    $ehp = $this->calculateEHP();

    if($this->type == 'pvp') {
      return $ehp / 10000;
    }

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
    $ehp = $this->calculateEHP();
    if($this->stats->getStat('Attacks per Second') > 2) {
      $lsCoefficient = .1;
    } else {
      $lsCoefficient = .2;
    }

    $effectiveLs = $this->stats->getStat('DPS Unbuffed') * 
      $this->stats->getStat('Life Steal')  * $lsCoefficient;
    $mitigation = $ehp / $this->stats->getStat('Life');
    $loh = $this->stats->getStat('Life on Hit');

    if($this->type == 'pvp') {
      $ls = 0;
      $loh = 0;
    }


    $rawSustainScore = 1 + $mitigation * ($loh * 
      (1 + ($this->stats->getStat('Attacks per Second') - 1) / 2) + 
      $this->stats->getStat('Life per Second') + $effectiveLs) / 
      ($this->stats->getStat('Life') * $this->EHPScore() * 10000 / 
      $ehp);

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

  function miscScore() {
    if($this->type == 'pvp') {
      $multiplier = .30;
      $divisor = 100;
    } else {
      $multiplier = .15;
      $divisor = 200;
    }

    $miscScore = 1 +
      $this->stats->getStat('+Hatred Regenerated per Second') / 100 * 2 +
      $this->stats->getStat('+Maximum Discipline') / $divisor +
      $this->stats->getStat('+Discipline Regenerated per Second') * $multiplier;

    return $miscScore;

  }

  protected 
    function calculateEHP() {
      if($this->type == 'pvp') {
        $ar_mod = 300;$armor_mod = 3000;$inherent_red = 0.30;$incoming_attack = 250000;
        $net_mod = .50; // no idea what this is for
      } else {
        $ar_mod = 315;$armor_mod = 3150;$inherent_red = 0.00;$incoming_attack = 100000;
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

      $after_block = $net * (1 - $this->stats->getStat('Block Chance')) + ($net - $block_amount)
        * $this->stats->getStat('Block Chance');

      $new_mit = $after_block / $incoming_attack;

      $raw_no_dodge = $this->stats->getStat('Life') / $new_mit;

      if($this->type == 'pvp') {
        $net_no_dodge = ($raw_no_dodge - $raw_ehp) * .75 + $raw_ehp;
      } else {
        $net_no_dodge = $raw_no_dodge;
      }

      $raw_ehp_dodge = $raw_no_dodge / (1 - $this->stats->getStat('Dodge Chance'));
      $net_ehp_dodge = ($raw_ehp_dodge - $net_no_dodge) * $net_mod + $net_no_dodge;

      $final_ehp = $net_ehp_dodge * (1 + ($this->stats->getStat('Melee Damage Reduction')
        + $this->stats->getStat('Missile Damage Reduction')) / 2);

      $this->stats->stats['EHP Unbuffed'] = $final_ehp;
      return $final_ehp;
    }
}