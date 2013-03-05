<?php

class StatsFactory {
  function createStatsObject($characterPageUrl) {
    $provider = StatsFactory::findProvider($characterPageUrl);

    if(!StatsFactory::isValidProvider($provider)) {
      return false;
    }

    include_once(__DIR__ . '/' . $provider . '_stats.php');
    $class = $provider . 'Stats';
    return new $class($characterPageUrl);
  }

  private 
    function findProvider($characterPageUrl) {
      preg_match('{.*\.(.*)\..*/.*}', $characterPageUrl, $provider);

      return strtolower($provider[1]);
    }

    function isValidProvider($provider) {
      $providers = array(
        'diabloprogress'
      );

      return in_array($provider, $providers);
    }
}

class Stats {
  private $url;
  protected $html;
  public $stats = array();
  public $class;

  function __construct($characterPageUrl) {
    $this->url = $characterPageUrl;
    $this->html = $this->getPageContents();

    $this->parse();
  }

  function getStat($name) {
    return (isset($this->stats[$name])) ? $this->stats[$name] : 0;
  }

  private
    function getPageContents() {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $this->url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      $contents = curl_exec($curl);
      curl_close($curl);

      //handle httpCodes and other failures

      return $contents;
    }
}
