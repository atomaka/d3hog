<?php
  include_once(__DIR__ . '/libs/stats.php');

  $stats = StatsFactory::createStatsObject('http://www.diabloprogress.com/hero/celanian-1548/HsuMing/21706367');

  print_r($stats->class);
  echo "\n\n";
  print_r($stats->stats);
  echo "\n\n";