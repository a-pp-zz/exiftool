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

    /**
     * Output to JSON
     * @return ExifTool
     */
    public function json ()
    {
    	$this->_args[] = '-j';
    	return $this;
    }

    /**
     * Convert to numbers
     * @return ExifTool
     */
    public function numbers ()
    {
    	$this->_args[] = '-n';
    	return $this;
    }

    /**
     * Extract embed data
     * @param  integer $num
     * @return ExifTool
     */
    public function embed ($num = 0)
    {
        $this->_args[] = '-ee'.(($num > 0 && $num <= 3) ? $num : '');
        $this->_args[] = '-G3';
        return $this;
    }

    /**
     * Short Tag Names
     * @return ExifTool
     */
    public function short ()
    {
        $this->_args[] = '-s';
        return $this;
    }

    /**
     * Extract Unknown Tags
     * @return ExifTool
     */
    public function unknown ()
    {
        $this->_args[] = '-U';
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

    public function get_date ()
    {
        $datefields = [
            'DateTimeOriginal',
            'CreateDate-rus',
            'CreationDate',
            'CreateDate',
            'TrackCreateDate',
            'MediaCreateDate',
            'ProfileDateTime',
            'FileModifyDate',
            'FileAccessDate'
        ];

        if ( ! empty ($this->_result)) {
            foreach ($datefields as $df) {
                $datetime = Arr::get ($this->_result, $df);

                if ( ! empty ($datetime)) {
                    $date = substr ($datetime, 0, 10);
                    $time = substr ($datetime, 11, 8);
                    $date = str_replace (':', '-', $date);
                    $time = str_replace ('-', ':', $time);
                    return $date . ' ' . $time;
                }
            }
        }

        return false;
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
