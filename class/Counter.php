<?php

/**
 * Very basic utility cass for counting hits.  Numeric dictionary.  Defaults to lowercasing the keys.
 */
class Counter {

  private $counts = array();
  public $lc = true;

  /**
   * Side effect of setting blank counts to 0
   * @param type $val
   * @return type
   */
  private function normalize($val) {
    $val = ($this->lc) ? strtolower($val) : $val;
    if (!isset($this->counts[$val]))
      $this->counts[$val] = 0;
    return $val;
  }

  public function inc($val) {
    return++$this->counts[$this->normalize($val)];
  }

  public function get($val) {
    return $this->counts[$this->normalize($val)];
  }

  public function topX($x) {
    arsort($this->counts);
    return array_slice($this->counts, 0, $x);
  }

}

