<?php

include_once(__DIR__ . '/libs/stats.php');
include_once(__DIR__ . '/libs/diabloclass.php');

if($_POST['submit']) {
  $diabloProgressUrl = trim($_POST['url']);
  if(preg_match('{^http://www.diabloprogress.com/hero/.*\-[\d]+/.*/[\d]+$}', $diabloProgressUrl) != 1) {
    die('Bad URL.  Please enter the entire diablo progress URL.<br/><br/>Example: http://www.diabloprogress.com/hero/celanian-1548/HsuMing/21706367');
  }


  $stats = StatsFactory::createStatsObject($diabloProgressUrl);

  if($stats === FALSE) {
    die('Bad provider.  Either your provider could not be detected or we do not support your provider at this time.');
  }

  $pve = DiabloClassFactory::createClassObject($stats->class, $stats, 'pve');
  $pvp = DiabloClassFactory::createClassObject($stats->class, $stats, 'pvp');

  if($pve === FALSE) {
    die('Bad class.  Either your class could not be detected or we do not support your class at this time.');
  }
  if($pvp === FALSE) {
    die('Bad class.  Either your class could not be detected or we do not support your class at this time.');
  }
?>
<hr/>
<table border="1" cellpadding="3">
  <thead>
    <tr>
      <th></th>
      <th>PvE</th>
      <th>PvP</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Hall Score</b></td>
      <td><?php echo number_format($pve->hallScore(), 2, '.', ','); ?></td>
      <td><?php echo number_format($pvp->hallScore(), 2, '.', ','); ?></td>
    </tr>
    <tr>
      <td><b>DPS Score</b></td>
      <td><?php echo number_format($pve->DPSScore(), 2, '.', ','); ?></td>
      <td><?php echo number_format($pvp->DPSScore(), 2, '.', ','); ?></td>
    </tr>
    <tr>
      <td><b>EHP Score</b></td>
      <td><?php echo number_format($pve->EHPScore(), 2, '.', ','); ?></td>
      <td><?php echo number_format($pvp->EHPScore(), 2, '.', ','); ?></td>
    </tr>
    <tr>
      <td><b>Sustain Score</b></td>
      <td><?php echo number_format($pve->sustainScore(), 2, '.', ','); ?></td>
      <td><?php echo number_format($pvp->sustainScore(), 2, '.', ','); ?></td>
    </tr>
    <tr>
      <td><b>Move Score</b></td>
      <td><?php echo number_format($pve->moveScore(), 2, '.', ','); ?></td>
      <td><?php echo number_format($pvp->moveScore(), 2, '.', ','); ?></td>
    </tr>
    <tr>
      <td><b>Paragon Score</b></td>
      <td><?php echo number_format($pve->paragonScore(), 2, '.', ','); ?></td>
      <td>0</td>
    </tr>
    <tr>
      <td><b>Misc Score</b></td>
      <td><?php echo number_format($pve->miscScore(), 2, '.', ','); ?></td>
      <td><?php echo number_format($pvp->miscScore(), 2, '.', ','); ?></td>
    </tr>
  </tbody>
</table>
<hr/>
<?php
}
?>
<form method="POST">
  D3 Progress URL: <input type="text" name="url" style="width:500px;" value="<?php echo $diabloProgressUrl ?>" /><br />
  <input type="submit" name="submit" />
</form>