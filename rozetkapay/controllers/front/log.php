<?php

/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
 */

/**
 * Log class
 */
class Log {

    private $handle;
    private $filename;
    private $dirfilename;

    /**
     * Constructor
     *
     * @param	string	$filename
     */
    public function __construct($filename) {
        $this->filename = $filename;
        $this->dirfilename = $this->getLogDir() . $filename;
        $this->handle = fopen($this->dirfilename, 'a');
    }

    /**
     * 
     *
     * @param	string	$message
     */
    public function write($message) {
        fwrite($this->handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
    }

    /**
     * 
     *
     */
    public function __destruct() {
        fclose($this->handle);
    }

    protected function getLogDir() {
        if (is_dir(_PS_ROOT_DIR_ . '/log')) {
            return _PS_ROOT_DIR_ . '/log/';
        } elseif (is_dir(_PS_ROOT_DIR_ . '/app/logs')) {
            return _PS_ROOT_DIR_ . '/app/logs/';
        } else {
            return _PS_ROOT_DIR_ . '/var/logs/';
        }
    }

    public function clear() {
        file_put_contents($this->dirfilename, "");
    }

    public function getSize() {
        if (file_exists($this->dirfilename)) {
            return filesize($this->dirfilename);
        } else {
            return 0;
        }
    }

    public function getSizeFormat() {

        $size = $this->getSize();

        $suffix = array(
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
            'PB',
            'EB',
            'ZB',
            'YB'
        );

        $i = 0;

        while (($size / 1024) > 1) {
            $size = $size / 1024;
            $i++;
        }
        return round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i];
    }

    public function getContent() {
        $size = (int) $this->getSize();
        if ($this->getSize() == 0) {
            return '';
        }

        if ($size < 5242880) {
            return file_get_contents($this->dirfilename, FILE_USE_INCLUDE_PATH, null);
        } else {
            return '';
        }
    }

    public function download() {
        $logContent = $this->getContent();
        header('Content-Type: application/download');
        header('Content-Disposition: attachment; filename="' . $this->filename . '.txt"');
        header("Content-Length: " . Tools::strlen($logContent));
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $logContent;
        exit();
    }

}
