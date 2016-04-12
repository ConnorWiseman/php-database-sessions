<?php
/**
 * A lightweight HTML markup templating engine.
 */
final class Template {
    /**
     * @access private
     * @var string Begins a string to be replaced by $this->replaceKeys().
     */
    private $keyPrefix;

    /**
     * @access private
     * @var string Ends a string to be replaced by $this->replaceKeys().
     */
    private $keySuffix;

    /**
     * @access private
     * @var array A list of keys considered unique by $this->replaceKeys().
     */
    private $unique = array();

    /**
     * access private
     * @var array Keys and values to be replaced by $this->replaceKeys().
     */
    private $values = array();

    /**
     * A constructor function that assigns appropriate values to
     * $this->keyPrefix and $this->keySuffix
     *
     * @access public
     */
    public function __construct($keyPrefix = '{{', $keySuffix = '}}') {
        $this->keyPrefix = $keyPrefix;
        $this->keySuffix = $keySuffix;
    }

    /**
     * Calls $this->replaceKeys() if $this->values has anything inside it
     * and returns the output of the current template.
     *
     * @access public
     * @param string $template An HTML template used to write to the page.
     * @return true
     */
    public function display($template) {
        if(strpos($template, '.tpl') !== false) {
            $template = file_get_contents('templates/' . $template);
            if(!$template) {
                throw new Exception('Template not found.');
            }
        }
        if(empty($this->values)) {
            echo $template;
        } else {
            echo $this->replaceKeys($template);
        }
        return true;
    }

    /**
     * Sets the value of any given key in $this->values.
     * If $unique is set to true, adds key to $this->unique.
     *
     * @access public
     * @param string $key The key to be set.
     * @param string $value The value to be assigned.
     * @param bool $unique Whether the key is unique or not.
     * @return self A reference to the current object.
     */
    public function set($key, $value, $unique = false) {
        if($unique) {
            array_push($this->unique, $key);
        }
        $this->values[$key] = $value;
        return $this;
    }

    /**
     * Sets the value of any given key in $this->values according to a
     * conditional statement. If $unique is set to true, adds key to
     * $this->unique.
     *
     * @access public
     * @param string $key The key to be added.
     * @param string $truevalue The value assigned if $condition is true.
     * @param string $falsevalue The value assigned if $condition is false.
     * @param bool $conditional A conditional expression.
     * @param bool $unique Whether the key is unique or not.
     * @return self A reference to the current object.
     */
    public function setCondition($key, $truevalue, $falsevalue, $conditional, $unique = false) {
        if($conditional) {
            $this->set($key, $truevalue, $unique);
        } else {
            $this->set($key, $falsevalue, $unique);
        }
        return $this;
    }

    /**
     * Replaces text strings using the key-value pairs in $this->values.
     * Keys listed in $this->unique are replaced once and only once.
     *
     * @access private
     * @param string $template An HTML template used to write to the page.
     * @return string $template The HTML template with keys replaced.
     */
    private function replaceKeys($template) {
        foreach($this->values as $key => $value) {
            // Generate string for replacement.
            $keyToReplace = $this->keyPrefix . $key . $this->keySuffix;
            // Conditionally replace strings in $template.
            if(in_array($key, $this->unique)) {
                // Replacement for unique keys:
                $keyPosition = strpos($template, $keyToReplace);
                if($keyPosition) {
                    $template = substr_replace(
                        $template,
                        $value,
                        $keyPosition,
                        strlen($keyToReplace)
                    );
                }
            }
            else {
                // Replacement for regular keys:
                $template = str_replace($keyToReplace, $value, $template);
            }
        }
        return $template;
    }
}