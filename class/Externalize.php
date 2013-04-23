<?php

/**
 * Exposes a class as a JSON REST API
 * Can be used for the server side JSON interface to allow easy batch function
 * calls to any PHP class
 * To embed the JavaScript hooks in your HTML page:
 * <script type="text/javascript" src="api.php?a=js"></script>
 *
 * (c) 2007 Benjamin Hill
 */
class Externalize {

  const FORM_ACTION = 'a';
  const FORM_ACTION_JS = 'js';
  const FORM_FUNCTION = 'f';

  private $className, $classObj, $reflectionObj;

  public function __construct($className) {
    if (empty($className))
      throw new Exception('Missing class name');
    $this->className = $className;
    if (!class_exists($this->className)) {
      throw new Exception('Class not loaded:' . $this->className);
    }
    $this->classObj = new $this->className;
    $this->reflectionObj = new ReflectionClass($this->className);
  }

  public function handleRequest() {
    $REQ = array_merge($_GET, $_POST); // Ignore cookies
    if (!isset($REQ[self :: FORM_ACTION])) {
      $REQ[self :: FORM_ACTION] = null;
    }
    switch ($REQ[self :: FORM_ACTION]) {
      case (self :: FORM_ACTION_JS) :
        header('Content-Type: text/javascript');
        echo $this->display_js();
        break;

      default : // handle function call
        header('Content-Type: application/json');
        $call = $REQ[self :: FORM_FUNCTION];
        $args = reset($call);
        $functName = key($call);
        try {
          $result = $this->callFunction($functName, $args);
        } catch (exception $ex) {
          $result = array('error' => $ex->getMessage());
        }

        echo json_encode($result);
    }
  }

  protected function callFunction($f, $p) {
    if (is_null($p) or $p == '') {
      $p = array();
    }
    if (!is_array($p)) {
      throw new Exception("$this->className::$f parameters must be a json-encoded associative array: " . var_export($p, true));
    }
    if (!$this->reflectionObj->hasMethod($f)) {
      throw new Exception("Unknown call: $this->className::$f");
    }
    $m = $this->reflectionObj->getMethod($f);
    if (!$m->isPublic()) {
      throw new exception("Non-public call: $this->className::$f");
    }
    if ($f[0] == '_') {
      throw new exception("Not allowed to access: $this->className::$f");
    }
    $params = $m->getParameters();
    $args = array();
    foreach ($params as $param) {
      $name = $param->getName();
      if (!isset($p[$name]) or is_null($p[$name])) {
        if ($param->isOptional()) {
          $p[$name] = null;
        } else {
          throw new Exception("$this->className::$f requires $name");
        }
      }
      $args[$name] = $p[$name];
    }
    return $m->invokeArgs($this->classObj, $args);
  }

  protected function display_js() {
    echo "var $this->className = {callback:{}};\n\n";
    foreach ($this->reflectionObj->getConstants() as $cname => $cval) {
      echo "$this->className.$cname = '" . addslashes($cval) . "';\n";
    }
    echo "\n";


    foreach ($this->reflectionObj->getMethods() as $m) {
      if ($m->isPublic()) {
        $mname = $m->getName();
        if ($mname[0] == '_')
          continue;

        echo "$this->className.callback.$mname = [];\n";
        // Generate the function
        $ps = $m->getParameters();
        $pnames = array_map(function($p) {
                  return $p->getName();
                }, $ps);
        echo"$this->className.$mname = function(" . implode(',', $pnames) . ") {\n";
        echo"  var vals = {\n";
        foreach ($ps as $p) {
          $name = $p->getName();
          echo"    $name:";
          if ($p->isOptional()) {
            if ($p->getDefaultValue() === null) {
              $default = 'null';
            } elseif (is_numeric($p->getDefaultValue())) {
              $default = $p->getDefaultValue();
            } else {
              $default = "'" . $p->getDefaultValue() . "'";
            }
            echo"($.type($name) != 'undefined')?$name:$default,\n";
          } else {
            echo"$name,\n";
          }
        }
        echo"  };\n";
        echo "  console.log('$this->className.$mname:'+JSON.stringify(vals));\n";
        // Call a single (non-batch) function
        echo <<<EOF
  var req = $.ajax({
    type:'POST',
    url: "{$_SERVER['PHP_SELF']}",
    data:{f:{ $mname:vals}},
  });
  $.each($this->className.callback.$mname, function(index, value) {
    req.done(value);
  });
  req.fail(function(data){console.log('$this->className.$mname fail:'+JSON.stringify(data)); });
};

EOF;

        //echo "$this->className.callback.$mname.push(function(data) { console.log('$this->className.$mname done:'+data);});\n";
      }
    }
  }

}

