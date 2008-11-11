<?php
/**
 * XML_XSLT_Wrapper
 *
 * PHP Version 4
 *
 * Copyright (c) 1997-2003 The PHP Group
 *
 * This source file is subject to version 2.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available at through the world-wide-web at
 * http://www.php.net/license/2_02.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category XML
 * @package  XML_XSLT_Wrapper
 * @author   Pierre-Alain Joye <pajoye@pearfr.org>
 * @license  PHP 2.02 http://www.php.net/license/2_02.txt
 * @version  CVS: $Id$
 * @link     http://pear.php.net/packages/XML_XSLT_Wrapper
 */

/**
 * Required files
 */
require_once 'PEAR.php';

/**
 * Input modes constants
 */
define('XML_XSLT_MODE_STRING', 0); // default mode for the XML data
define('XML_XSLT_MODE_FILE', 1); // default mode for the XSL data
define('XML_XSLT_MODE_URI', 2);
define('XML_XSLT_CACHE_XSLT_URI', false);

/**
 * Backends names constants
 */
define('XML_XSLT_XSLTPROC_CMD', 'xsltproc');
define('XML_XSLT_XSLT_EXT', 'XSLT_ext');
define('XML_XSLT_DOM', 'DOM_XSL');
define('XML_XSLT_SABLOTRON', 'Sablotron');
define('XML_XSLT_XT', 'XT');
define('XML_XSLT_MSXML_CMD', 'MSXML_tty');
define('XML_XSLT_MSXSL_COM', 'MSXSL_com');

/**
 * Output modes constants
 */
define('XML_XSLT_OUTPUT_STDOUT', 1);
define('XML_XSLT_OUTPUT_FILE', 2);
define('XML_XSLT_OUTPUT_MEM', 4);
define('XML_XSLT_OUTPUT_CALLBACK', 5);

/**
 * Errors constants
 */
define('XML_XSLT_ERROR', -1);
define('XML_XSLT_NOT_SUPPORTED', -2);
define('XML_XSLT_ERROR_BACKEND_NOTFOUND', -1000);
define('XML_XSLT_ERROR_BACKEND_FAILED', -1003);

define('XML_XSLT_ERROR_UNKNOWN_MODE', -1001);

define('XML_XSLT_ERROR_TMPFILE_FAILED', -1002);
define('XML_XSLT_ERROR_FILE_FAILED', -1003);
define('XML_XSLT_ERROR_MKDIR_FAILED', -1004);
define('XML_XSLT_ERROR_MISSEDDIR_FAILED', -1005);

define('XML_XSLT_ERROR_XML_EMPTY', -2002);
define('XML_XSLT_ERROR_XSL_EMPTY', -2003);
define('XML_XSLT_ERROR_NOOPTIONS', -2004);

define('XML_XSLT_ERROR_XSLFILE_NOTFOUND', -3003);
define('XML_XSLT_ERROR_XMLFILE_NOTFOUND', -3004);
define('XML_XSLT_ERROR_LOADXSL_FAILED', -3005);
define('XML_XSLT_ERROR_XMLPARSER_ERROR', -4001);
define('XML_XSLT_ERROR_XSLPARSER_ERROR', -4002);
define('XML_XSLT_ERROR_XSLEXEC_ERROR', -4003);

/**
 * XSLT wrapper classes
 *
 * @category XML
 * @package  XML_XSLT_Wrapper
 * @author   Pierre-Alain Joye <pajoye@pearfr.org>
 * @license  PHP 2.02 http://www.php.net/license/2_02.txt
 * @link     http://pear.php.net/packages/XML_XSLT_Wrapper
 * @see      http://pear.php.net/ for releases and cvs
 * @see      http://www.pearfr.org/xslt_wrapper/ for docs & snapshots
 */
class XML_XSLT_Wrapper
{
    // {{{ XML_XSLT_Wrapper
    /**
     * Factory
     *
     * @param string $backend name of the backend
     *
     * @access public
     * @return mixed a newly created XSLT object, or a XSLT error code
     * @see backend
     */
    function &factory($backend)
    {
        @include_once "XML/XSLT/Wrapper/Backend/$backend.php";
        $classname = 'XML_XSLT_Backend_' . $backend;
        if (!class_exists($classname)) {
            return PEAR::raiseError(null, XML_XSLT_ERROR_BACKEND_NOTFOUND,
                                    null, null, null, 'XML_XSLT_Error', true);
        }
        @$obj =& new $classname;
        return $obj;
    }

    // }}}
    // {{{ Init

    /**
     * Init
     *
     * @param array $options options
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function Init($options)
    {
        return $obj;
    }

    // }}}
    // {{{ errorMessage

    /**
     * errorMessage
     *
     * @param int $value error code
     *
     * @access private
     * @return mixed return
     * @see backend
     */
    function errorMessage($value)
    {
        static $errorMessages;

        if (!isset($errorMessages)) {
            $errorMessages = array(
                XML_XSLT_ERROR                      => 'Unknown Error',
                XML_XSLT_ERROR_BACKEND_NOTFOUND     => 'Unknown backend',
                XML_XSLT_ERROR_UNKNOWN_MODE         => 'Unknown mode',
                XML_XSLT_ERROR_TMPFILE_FAILED       => 'Cannot create temp file',
                XML_XSLT_ERROR_XML_EMPTY            => 'XML data is empty',
                XML_XSLT_ERROR_XSL_EMPTY            => 'XSL data is empty',
                XML_XSLT_ERROR_XSLFILE_NOTFOUND     => 'Cannot load/find XSL file',
                XML_XSLT_ERROR_XMLFILE_NOTFOUND     => 'Cannot load/find XML file',
                XML_XSLT_ERROR_XMLPARSER_ERROR      =>
                                            'Error while parsing the XML tree',
                XML_XSLT_ERROR_XSLPARSER_ERROR      =>
                                            'Error while parsing the XSL tree',
                XML_XSLT_ERROR_XSLEXEC_ERROR        =>
                                            'Error while running transformation',
                XML_XSLT_ERROR_NOOPTIONS            =>
                                            'Missing Options for batch mode',
                XML_XSLT_NOT_SUPPORTED              =>
                                            'Method not implemented in backend'
            );
        }

        if (XML_XSLT_Wrapper::isError($value)) {
            $value = $value->getCode();
        }

        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[XML_XSLT_ERROR];
    }

    // }}}
    // {{{ isError

    /**
     * Tell whether a result code from a DB method is an error
     *
     * @param int $value result code
     *
     * @return bool whether $value is an error
     *
     * @access public
     */
    function isError($value)
    {
        return (is_object($value)
                && (strtolower(get_class($value)) == 'xlst_error'
                || is_subclass_of($value, 'XML_XSLT_Error')));
    }
    // }}}
}

/**
 * Base class from which backends
 * inherit.
 *
 * @category XML
 * @package  XML_XSLT_Wrapper
 * @author   Pierre-Alain Joye <pajoye@pearfr.org>
 * @license  PHP 2.02 http://www.php.net/license/2_02.txt
 * @link     http://pear.php.net/packages/XML_XSLT_Wrapper
 */
class XML_XSLT_Common extends PEAR
{
    /**
     * Defines if the backend works in a shell
     *
     * @var    boolean  $_console_mode
     * @access private
     */
    var $_console_mode = false;

    /**
     * Key/Value parameters passed to the XSLT sheet
     *
     * @var    array $params
     * @access private
     */
    var $error_class = 'XML_XSLT_Error';

    /**
     * Key/Value parameters passed to the XSLT sheet
     *
     * @var    array $params
     * @access private
     */
    var $params;

    /**
     * Key/Value options passed to the XSLT command
     * Currenlty not used
     *
     * @var    array  $options
     * @access private
     */
    var $options;

    /**
     * string or file path
     *
     * @var    string  $xml
     * @access private
     */
    var $xml = '';

    /**
     * XSLT sheet
     *
     * @var    string  $xml    string or file path
     * @access private
     */
    var $xslt = '';

    /**
     * XSL_Mode
     *
     * @var    integer $XSL_Mode       Mode to use for XSL
     * @access private
     */
    var $XSL_Mode = XML_XSLT_MODE_FILE;

    /**
     * XML_Mode
     *
     * @var integer $XML_Mode       Mode to use for XML
     * @access private
     */
    var $XML_Mode = XML_XSLT_MODE_STRING;

    /**
     * _initXSL_Done
     *
     * @var integer $XML_Mode       Defines if the XSL init
     *                              has been done.
     * @access private
     */
    var $_initXSL_Done = false;

    /**
     * _initXML_Done
     *
     * @var integer $XML_Mode       Defines if the XML init
     *                              has been done.
     * @access private
     */
    var $_initXML_Done = false;

    /**
     * String or file path
     *
     * @var string  $result
     */
    var $result = '';

    /**
     * Output encode format
     *
     * @var string $outputEncoding
     */
    var $outputEncoding = '';

    /**
     * Output filepath
     *
     * @var string  $outputFile
     */
    var $outputFile = '';


    /**
     * Native Backend Error code
     *
     * @var string $native_error_code
     */
    var $native_error_code = 0;

    /**
     * Native Backend Error message
     *
     * @var string $native_error_message
     */
    var $native_error_message = '';

    /**
     * Error code
     *
     * @var integer $error_code
     */
    var $error_code = 0;

    // {{{ constructor

    /**
     * Class constructor
     */
    function XML_XSLT_Common()
    {
        $this->PEAR($this->error_class);
        $this->setErrorHandling(PEAR_ERROR_RETURN);
    } // end func XML_XSLT_Common

    /**
     * Destructor
     */
    function _XML_XSLT_Common()
    {
        $this->_PEAR();
    } // end func _XML_XSLT_Common

    // }}}

    // {{{ raiseError()

    /**
     * This method is used to communicate an error and invoke error
     * callbacks etc.  Basically a wrapper for PEAR::raiseError
     * without the message string.
     *
     * @param mixed  $code       integer error code, or a PEAR error object (all
     *                           other parameters are ignored if this parameter is
     *                           an object
     *
     * @param int    $mode       error mode, see PEAR_Error docs
     *
     * @param mixed  $options    If error mode is PEAR_ERROR_TRIGGER, this is the
     *                           error level (E_USER_NOTICE etc).  If error mode is
     *                           PEAR_ERROR_CALLBACK, this is the callback function,
     *                           either as a function name, or as an array of an
     *                           object and method name.  For other error modes this
     *                           parameter is ignored.
     *
     * @param string $userinfo   Extra debug information.  Defaults to the last
     *                           query and native error code.
     *
     * @param mixed  $nativecode Native error code, integer or string depending the
     *                           backend.
     *
     * @return object  a PEAR error object
     *
     * @access public
     * @see PEAR_Error
     */
    function &raiseError($code = XML_XSLT_ERROR, $mode = null, $options = null,
                         $userinfo = null, $nativecode = null)
    {
        // The error is yet a DB error object
        if (is_object($code)) {
            // because we the static PEAR::raiseError, our global
            // handler should be used if it is set
            if ($mode === null && !empty($this->_default_error_mode)) {
                $mode    = $this->_default_error_mode;
                $options = $this->_default_error_options;
            }

            return PEAR::raiseError($code, null, $mode, $options, null, null, true);
        }

        return PEAR::raiseError(null, $code, $mode, $options, $userinfo,
                                  'XML_XSLT_Error', true);
    }

    // }}}
    // {{{ setParams

    /**
     * Set the XML data to transform.
     *
     * @param string $data    source origin (file path, URI)
     *                        or XSLT definitions within
     *                        inside a string variable.
     * @param string $mode    the conversion mode, see constants
     * @param string $options options
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function setXML($data = '', $mode = XML_XSLT_MODE_STRING, $options = null)
    {
        $error = false;

        if (strlen($data)) {
            $this->XML_Mode = $mode;

            switch ($mode) {
            case XML_XSLT_MODE_STRING:
                $this->xml = $data;
                break;

            case XML_XSLT_MODE_FILE:
                if (file_exists($data)) {
                    $this->xml = $this->_console_mode ?
                                    escapeshellarg($data) : $data;
                } else {
                    $error      = true;
                    $error_code = XML_XSLT_ERROR_XMLFILE_NOTFOUND;
                    $error_user = 'Failed to load `' . $data . '`';
                }
                break;

            case XML_XSLT_MODE_URI:
                $this->xml = $data;
                break;

            default:
                $error      = true;
                $error_code = XML_XSLT_ERROR_UNKNOWN_MODE;
                $error_user = 'Unknown Input mode `' . $mode.'`';
            }
        } else {
            $error      = true;
            $error_code = XML_XSLT_ERROR_XML_EMPTY;
            $error_user = 'Missed XML data ';
        }

        if ($error) {
            $this->error = $this->raiseError($error_code, null, null, $error_user);
            return false;
        }

        return true;
    }

    // }}}
    // {{{ setXSL

    /**
     * Load the XSL sheet from a defined source
     *
     * @param string $data    source origin (file path, URI)
     *                        or XSLT definitions within
     *                        inside a string variable.
     * @param string $mode    the conversion mode, see constants
     * @param string $options options
     *
     * @access public
     * @return mixed return
     */
    function setXSL($data = '', $mode = XML_XSLT_MODE_FILE, $options = null)
    {
        $error = false;

        if ($data != "") {
            $this->XSL_Mode = $mode;

            switch ($mode) {
            case XML_XSLT_MODE_STRING:
                $this->xslt = $data;
                break;

            case XML_XSLT_MODE_FILE:
                if (file_exists($data)) {
                    $this->xslt = $this->_console_mode?
                                    escapeshellarg($data):$data;
                } else {
                    $error      = true;
                    $error_code = XML_XSLT_ERROR_XSLFILE_NOTFOUND;
                    $error_user = 'Failed to load `' . $data . '`';
                }
                break;

            case XML_XSLT_MODE_URI:
                $this->xslt = $data;
                break;

            default:
                $error      = true;
                $error_code = XML_XSLT_ERROR_UNKNOWN_MODE;
                $error_user = 'Unknown input mode `' . $mode . '`';
            }
        } else {
            $error      = true;
            $error_code = XML_XSLT_ERROR_XSL_EMPTY;
            $error_user = 'Missed XSL data';
        }

        if ($error) {

            echo "Error in XSL";

            $this->error = PEAR::raiseError(null, $error_code, null, null,
                                            $error_user,
                                            $this->error_class, true);
            return false;
        }

        return true;
    }

    // }}}
    // {{{ setParams

    /**
     * Set a group of parameters for XSLT
     *
     * @param array $params parameters which will be used by the backend
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function setParams($params)
    {
        foreach ($params as $param => $value) {
            if (is_string($param) && strlen($param) || is_numeric($param)) {
                $this->options[$param] = $value;
            }
        }
    }

    // }}}
    // {{{ setParam

    /**
     * Set one parameter for XSLT
     *
     * @param string $param parameter name
     * @param mixed  $value parameter vamue
     *
     * @access public
     * @return null
     */
    function setParam($param, $value)
    {
        if (is_string($param) &&  strlen($param)) {
            $this->params[$param] = $value;
        }
    }

    // }}}
    // {{{ setOutputEconding

    /**
     * Set the output encoding (does not work with all backend)
     *
     * @param string $encode type of encoding
     *
     * @access public
     * @return null
     */
    function setOutputEconding($encode = '')
    {
        $this->outputEncoding = $encode;
    }

    // }}}
    // {{{ setOptions

    /**
     * Defines the options for the active backend
     *
     * @param array $options the options to set
     *
     * @access public
     * @return boolean
     * @see backend
     */
    function setOptions($options)
    {
        foreach ($options as $option => $value) {
            if (is_string($option) && strlen($option)) {
                $this->options[$option] = $value;
            }
        }
    }

    // }}}
    // {{{ setOption

    /**
     * Defines one option for the active backend
     *
     * @param string $option name of the param
     * @param mixed  $value  value of the param
     *
     * @access public
     *
     * @return boolean
     */
    function setOption($option, $value)
    {
        if (is_string($option) &&  strlen($option)) {
            $this->params[$option] = $value;
        }
    }

    // }}}
    // {{{ _getFileContent

    /**
     * Gets data from a file
     *
     * @param string $path Path to read
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function _getFileContent($path)
    {
        if ($fd = @fopen($path, "r")) {
            while (!feof($fd)) {
                $buffer .= fgets($fd, 4096);
            }
            fclose($fd);

            return $buffer;
        }

        $this->error = PEAR::raiseError(null, XML_XSLT_ERROR_FILE_FAILED,
                            null, null,
                            'Cannot open file `' . $path . '`',
                            $this->error_class, true);
        return '';   
    }

    // }}}
    // {{{ _getURIContent

    /**
     * Gets data from an URI (XML or XSLT sheet)
     *
     * @param string $uri URI to read
     *
     * @access private
     * @return mixed return
     * @see backend
     */
    function _getURIContent($uri)
    {
        return $this->_getFileContent($uri);
    }

    // }}}
    // {{{ _saveResult

    /**
     * Save data to a file
     *
     * @param string $filepath Filepath
     *
     * @access private
     * @return mixed return
     */
    function _saveResult($filepath = '')
    {
        if ($fd = @fopen($filepath, 'wb+')) {
            fputs($fd, $this->result);
            fclose($fd);
            return true;
        }

        $this->error = PEAR::raiseError(null, XML_XSLT_ERROR_FILE_FAILED,
                            null, null,
                            'Cannot write file `' . $data . '`',
                            $this->error_class, true);
        return false;
    }

    // }}}
    // {{{ _saveTempData

    /**
     * Save data to a temp file
     *
     * @param string $data Data to be stored
     *
     * @access private
     * @return mixed return
     */
    function _saveTempData($data)
    {
        include_once 'System.php';
        $tempfile = System::mktemp('pxslt_');
        if (!PEAR::isError($tempfile)) {
            if ($fd = @fopen($tempfile, 'wb+')) {
                fputs($fd, $data);
                fclose($fd);
                return $tempfile;
            }
        }

        $this->error = PEAR::raiseError(null, XML_XSLT_ERROR_TMPFILE_FAILED,
                            null, null,
                            'Cannot write file `' . $tempfile . '`',
                            $this->error_class, true);
        return false;
    }

    // }}}
    // {{{ _saveTempData

    /**
     * Save data to a temp file
     *
     * @access private
     * @return mixed return
     */
    function _removeTempData()
    {
        include_once 'System.php';
        $tempfile = System::_removeTmpFiles();
    }

    // }}}
    // {{{ setOutputMode

    /**
     * Sets the desired output mode.
     *
     * @param int    $mode the desired output mode
     * @param string $arg  file name or callback function name
     *
     * @access public
     * @return boolean
     * @see backend
     */
    function setOuputMode($mode = XSLT_OUTPUT_MEM, $arg = '')
    {
        if ($mode = XML_XSLT_OUTPUT_FILE) {
            $this->outputFile = $arg;
        }
        if ($mode = XML_XSLT_OUTPUT_CALLBACK) {
            $this->callback = $arg;
        }
        $this->OutputMode = $mode;
    }

    // }}}
    // {{{ ResultDumpFile

    /**
     * Output to a file.
     *
     * This method has to be overridden in the backend.
     *
     * @access public
     * @return boolean  true if success, false otherwise
     * @see backend
     */
    function ResultDumpFile()
    {
        return $this->raiseError(XML_XSLT_NOT_SUPPORTED);
    }

    // }}}
    // {{{ ResultDumpMem

    /**
     * Return the output.
     *
     * This method has to be overridden in the backend.
     *
     * @access public
     * @return boolean  true if success, false otherwise
     * @return mixed    string if success, false otherwise
     * @see backend
     */
    function ResultDumpMem()
    {
        return $this->raiseError(XML_XSLT_NOT_SUPPORTED);
    }

    // }}}
    // {{{ ResultDumpOut

    /**
     * Output to the default output.
     *
     * This method has to be overridden in the backend.
     *
     * @access public
     * @return mixed  nothing if all went well, false otherwise
     * @see backend
     */
    function ResultDumpOut()
    {
        return $this->raiseError(XML_XSLT_NOT_SUPPORTED);
    }

    // }}}
    // {{{ batchXML

    /**
     * Transform one single XML data with multiple XSL files.
     *
     * This method has to be overridden in the backend.
     *
     * @param array $options Array with all data and options needed
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function batchXML($options)
    {
        return $this->raiseError(XML_XSLT_NOT_SUPPORTED);
    }

    // }}}
    // {{{ batchXSL

    /**
     * Transform multiple XML data with a single XSL files
     *
     * @param array   $options      Array with all data and options needed
     * @param boolean $singleoutput not used
     *
     * @access public
     * @return boolean true if success, false otherwise
     * @see backend
     */
    function batchXSL($options, $singleoutput = false)
    {
        return $this->raiseError(XML_XSLT_NOT_SUPPORTED);
    }

    // }}}
    // {{{ _mkdir_p()

    /**
     * Creates recursively a directory path
     * Credits : copy/paste from go-pear script :)
     *
     * @param string  $dir  Directory path
     * @param integer $mode Permission (chmod)
     *
     * @access private
     * @return mixed return
     * @see backend
     */
    function _mkdir_p($dir, $mode = 0777)
    {
        include_once 'System.php';
        $lastdir = '';
        if (@is_dir($dir)) {
            return true;
        }

        $return = true;
        $parent = dirname($dir);

        $parent_exists = (int)@is_dir($parent);

        if (!@is_dir($parent) && $parent != $dir) {
            $return = $this->_mkdir_p(dirname($dir), $mode);
        }

        if ($return) {
            $return = @mkdir($dir, $mode);
            if (!$return) {
                $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_MKDIR_FAILED,
                                    null, null,
                                    'Cannot create folder `' . $dir.'`',
                                    $this->error_class, true);

                return false;
            }
        }
        return $return;
    }

    // }}}
}

/**
 * XML_XSLT_Error implements a class for reporting portable XSLT error
 * messages.
 *
 * @category XML
 * @package  XML_XSLT_Wrapper
 * @author   Pierre-Alain Joye <pajoye@pearfr.org>
 * @license  PHP 2.02 http://www.php.net/license/2_02.txt
 * @link     http://pear.php.net/packages/XML_XSLT_Wrapper
 */
class XML_XSLT_Error extends PEAR_Error
{
    /**
     * XML_XSLT_Error constructor.
     * bases on DB_Error implementation
     *
     * @param mixed   $code      XSLT error code, or string with error message.
     * @param integer $mode      what "error mode" to operate in
     * @param integer $level     what error level to use for $mode & PEAR_ERROR_TRIGGER
     * @param mixed   $debuginfo additional debug info, such as the last query
     *
     * @access public
     *
     * @see PEAR_Error
     */
    function XML_XSLT_Error($code = XML_XSLT_ERROR, $mode = PEAR_ERROR_RETURN,
              $level = E_USER_WARNING, $debuginfo = null)
    {
        if (is_int($code)) {
            $this->PEAR_Error('XSLT Wrapper Error: ' . XML_XSLT_Wrapper::errorMessage($code), $code, $mode, $level, $debuginfo);
        } else {
            $this->PEAR_Error("XSLT Wrapper Error: $code", XML_XSLT_ERROR, $mode, $level, $debuginfo);
        }
    }
}
/*
 * Local Variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
?>
