<?php
/**
 * ExifTool Wrapper
 * @package CLI/ExifTool
 * @version	1.0.0
 */
namespace AppZz\CLI\Wrappers;
use \AppZz\Helpers\Arr;

class ExifTool {

	static $binary = '/usr/local/bin/exiftool';
	private $_input;
	private $_result;
	private $_args = [];

    public function __construct ($input = null)
    {
        $this->_input = $input;
        $this->_args = [];
    }

    public static function factory ($input = null)
    {
    	return new self ($input);
    }

    public function json ()
    {
    	$this->_args[] = '-j';
    	return $this;
    }

    public function numbers ()
    {
    	$this->_args[] = '-n';
    	return $this;
    }

    public function analyze ()
    {
    	$this->json();
    	$args = array_unique ($this->_args);
    	$args = implode (' ', $args);
    	$this->_result = shell_exec (sprintf ('%s %s %s', self::$binary, trim ($args), escapeshellarg($this->_input)));
        $this->_result = $this->_json_decode ($this->_result);

        if ($this->_result) {
            $this->_result = array_shift ($this->_result);
        } else {
            $this->_result = false;
        }

        return $this;
    }

    public function get_result (array $fields = [])
    {
        if ( ! empty ($fields)) {
        	$fields = array_fill_keys ($fields, 1);
        	return array_intersect_key ((array)$this->_result, $fields);
        }

        return $this->_result;
    }

    private function _json_decode ($json)
    {
        $json = json_decode ($json, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        return false;
    }
}
