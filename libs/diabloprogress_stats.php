<?php

class DiabloProgressStats extends Stats {
  function parse() {
    $this->class = $this->findClass();
    $this->parseStats();
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
    }
}