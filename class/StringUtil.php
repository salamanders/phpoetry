<?php

/**
 * Easy conversion for simple hashes.  Includes "beginsWith" and "endsWith" if not already defined.
 *
 * @author benjamin
 */
class StringUtil {

  const CHARS_INT = '0123456789';
  const CHARS_HEX = '0123456789abcdef';
  const CHARS_BASE62 = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

  /** Helper function for generating small text-safe unique public keys, for example from a URL */
  public static function shortHash($a) {
    return self::convert_big(md5(strtolower('nohaCKth1s5a1t' . $a . 'really543')), CHARS_HEX, CHARS_BASE62);
  }

  public static function convert_big($numstring, $frombase = self::CHARS_INT, $tobase = self::CHARS_HEX) {
    $from_count = strlen($frombase);
    $to_count = strlen($tobase);
    $length = strlen($numstring);
    $result = '';
    $number = array();
    for ($i = 0; $i < $length; $i++) {
      $number[$i] = strpos($frombase, $numstring { $i });
    }
    // Loop until whole number is converted
    do {
      $divide = 0;
      $newlen = 0;
      // Perform division manually (which is why this works with big numbers)
      for ($i = 0; $i < $length; $i++) {
        $divide = $divide * $from_count + $number[$i];
        if ($divide >= $to_count) {
          $number[$newlen++] = (int) ($divide / $to_count);
          $divide = $divide % $to_count;
        } elseif ($newlen > 0) {
          $number[$newlen++] = 0;
        }
      }
      $length = $newlen;
      $result = $tobase { $divide } . $result;
      // Divide is basically $numstring % $to_count (i.e. the new character)
    } while ($newlen != 0);
    return $result;
  }

}

if (!function_exists('endsWith')) {

  function endsWith($str, $sub) {
    return (substr(strtolower($str), strlen($str) - strlen($sub)) == strtolower($sub));
  }

}

if (!function_exists('beginsWith')) {

  function beginsWith($str, $sub) {
    return (substr(strtolower($str), 0, strlen($sub)) === strtolower($sub));
  }

}