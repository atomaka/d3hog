<?php

ini_set('display_errors', 1);

include_once(__DIR__ . '/libs/dpclass.php');

if($_POST['submit']) {
  $diabloProgressUrl = trim($_POST['url']);
  if(preg_match('{^http://www.diabloprogress.com/hero/[\w]+\-[\d]+/[\w]+/[\d]+$}', $diabloProgressUrl) != 1) {
    die('Bad URL.  Please enter the entire diablo progress URL.<br/><br/>Example: http://www.diabloprogress.com/hero/celanian-1548/HsuMing/21706367');
  }

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $diabloProgressUrl);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  $contents = curl_exec($curl);
  curl_close($curl);

  $character = DPClassFactory::createClassObject($contents);

  if($character === FALSE) {
    die('Bad class.  Either your class could not be detected or we do not support your class at this time.');
  }
?>
<hr/>
<b>Hall Score: <?php echo number_format($character->hallScore(), 2, '.', ','); ?></b><br/><br/>

DPS Score: <?php echo number_format($character->DPSScore(), 2, '.', ','); ?><br/>
EHP Score: <?php echo number_format($character->EHPScore(), 2, '.', ','); ?><br/>
Sustain Score: <?php echo number_format($character->sustainScore(), 2, '.', ','); ?><br/>
Move Score: <?php echo number_format($character->moveScore(), 2, '.', ','); ?><br/>
Paragon Score: <?php echo number_format($character->paragonScore(), 2, '.', ','); ?><br/>
<hr/>
<?php
}
?>
<form method="POST">
  D3 Progress URL: <input type="text" name="url" style="width:500px;" value="<?php echo $diabloProgressUrl ?>" /><br />
  <input type="submit" name="submit" />
</form>