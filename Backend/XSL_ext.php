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

require_once 'PEAR/Exception.php';

/**
 * XML_XSLT_Backend_XSL_et
 *
 * @category XML
 * @package  XML_XSLT_Wrapper
 * @author   Pierre-Alain Joye <pajoye@pearfr.org>
 * @license  PHP 2.02 http://www.php.net/license/2_02.txt
 * @link     http://pear.php.net/packages/XML_XSLT_Wrapper
 */
class XML_XSLT_Backend_XSL_ext extends XML_XSLT_Common
{
    /**
     * XSLT processor
     * 
     * @var object XSLTProcessor
     */
    var $_xsltProcessor;

    /** 
     * XML to transform
     *
     * @var object DOM object 
     */
    var $_oXML;

    // {{{ Backend_DOM_XSL

    /**
     * Set the parameters for the active XSL sheet
     *
     * @access public
     */
    function XML_XSLT_Backend_XSL_ext()
    {
        if (!function_exists('xsl_xsltprocessor_import_stylesheet')) {
            if (!PEAR::loadExtension('xsl')) {
                return $this->raiseError(XML_XSLT_ERROR_BACKEND_FAILED);
            }
        }
        $this->_xsltProcessor = new xsltprocessor();
    }

    // }}}
    // {{{ _buildParams

    /**
     * Set the parameters for the active XSL sheet
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function _buildParams()
    {
        // does not need to build them
        return true;
    }


    // }}}
    // {{{ _initXSL

    /**
     * Raise an exception
     *
     * @param string $errno   Error number
     * @param string $errstr  Error string
     * @param string $errfile Error file
     * @param string $errline Error line
     *
     * @return void
     */
    public static function convertPHPErrToException($errno, $errstr, $errfile, $errline)
    {
        if (!preg_match('/set_error_handler.*/', $errstr)) {
            throw new PEAR_Exception("$errstr in $errfile at line $errline");
        }
    }

    /**
     * Set the parameters for XSLT
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function _initXSL()
    {
        $xsl = new DomDocument();

        $oldErrHandler = set_error_handler(array('XML_XSLT_Backend_XSL_ext', 'convertPHPErrToException'));

        switch($this->XSL_Mode) {
        case XML_XSLT_MODE_STRING:
            $xsl->loadXML($this->xslt);
            break;
        case XML_XSLT_MODE_FILE:
            $xsl->load($this->xslt);
            break;
        case XML_XSLT_MODE_URI:
            $content = $this->getURIContent($this->xslt);
            $xsl->loadXML($content);
            break;
        }

        $this->_xsltProcessor->importStylesheet($xsl);
        set_error_handler($oldErrHandler);
        $this->_initXSL_Done = true;
        return true;
    }

    // }}}
    // {{{ _initXML

    /**
     * Set the parameters for the active XSL sheet
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function _initXML()
    {
        $this->_oXML = new DomDocument();

        $oldErrHandler = set_error_handler(array('XML_XSLT_Backend_XSL_ext', 'convertPHPErrToException'));

        switch($this->XML_Mode) {
        case XML_XSLT_MODE_STRING:
            $loadResult = $this->_oXML->loadXML($this->xml);
            break;
        case XML_XSLT_MODE_FILE:
            $loadResult = $this->_oXML->load($this->xml);
            break;
        case XML_XSLT_MODE_URI:
            $content    = $this->getURIContent($this->xml);
            $loadResult = $this->_oXML->loadXML($content);
            break;
        }

        set_error_handler($oldErrHandler);
        $this->_initXML_Done = true;
        return true;
    }

    // }}}
    // {{{ process

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
        foreach ($params as $key => $value) {
            $this->_xsltProcessor->setParameter('', $key, $value);    
        }
    }
    
    /**
     * Process the transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function process()
    {
        if (!$this->error_code) {
            if (!$this->_initXSL_Done) {
                if (!$this->_initXSL()) {
                    return false;
                }
            }

            try {
                if (!$this->_initXML_Done && !$this->_initXML()) {
                    return false;
                }
            } catch (Exception $e) {
                header('Content-type: text/plain');
                print($e->getMessage());
                print("\nDocument in error:\n");
                print($this->xml);
                exit;
                return false;
            }

            $this->result = $this->_xsltProcessor->transformToXML($this->_oXML);
            if (!$this->result) {
                return $this->raiseError(XML_XSLT_ERROR_XSLEXEC_ERROR);
            }
        }

        return true;
    }

    // }}}
    // {{{ ResultDumpOut

    /**
     * Dump the result to standard output.
     *
     * @param boolean $free Free the ressources
     *                      after transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpOut($free = true)
    {
        if (!$this->error_code && $this->_initXSL_Done && $this->_initXML_Done) {
            echo $this->result;
            return true;
        }

        return false;
    }

    // }}}
    // {{{ ResultDumpMem

    /**
     * Dumps the result to memory
     *
     * @param boolean $free Free the ressources
     *                      after transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpMem($free = true)
    {
        if (!$this->error_code && $this->_initXSL_Done && $this->_initXML_Done) {
            return $this->result;
        }

        return '';
    }

    // }}}
    // {{{ ResultDumpFile

    /**
     * Dumps the result to a file
     *
     * @param string  $output_file filename to output to
     * @param boolean $free        Free the ressources
     *                             after transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpFile($output_file = '', $free = true)
    {
        $return = false;
        if (!$this->error_code && $this->_initXSL_Done && $this->_initXML_Done) {
            if ($output_file == '') {
                $output_file = $this->outputFile;
            }

            if (!$this->error_code) {
                if ($this->result->dump_file($output_file, false)) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    // }}}
    // {{{ batchXML

    /**
     * Transform single XML data with multiple XSL files
     *
     * @param array $options Options
     *
     * @access public
     * @return mixed return
     */
    function batchXML($options = null)
    {
        if (is_null($options)) {
            $this->error = $this->raiseError(XML_XSLT_ERROR_NOOPTIONS);
            return false;
        }

        if (isset($options['outputfolder'])) {
            if (!is_dir($options['outputfolder'])) {
                if (!$this->_mkdir_p($options['outputfolder'])) {
                    return false;
                }
            }
        } else {
            $this->error = new XML_XSLT_Error (null,
                            XML_XSLT_ERROR_MISSEDDIR_FAILED,
                            E_USER_ERROR);
            return false;
        }

        $dest_dir = $options['outputfolder'];
        if (isset($options['xml'])) {
            $mode = $options['xml'][0]=='<'?
                            XML_XSLT_MODE_STRING:XML_XSLT_MODE_FILE;

            if (!$this->setXML($options['xml'], $mode)) {
                 $this->error = new XML_XSLT_Error (null,
                                XML_XSLT_ERROR_XML_EMPTY,
                                E_USER_ERROR);
                 return false;
            }

            $this->_initXML();
        } else {
            $this->error = $this->raiseError(XML_XSLT_ERROR_XML_EMPTY);
        }

        if (isset($options['xslt_files']) && is_array($options['xslt_files'])) {

            $xsl_files = $options['xslt_files'];
            $xslt_args = '';
            foreach ($xsl_files as $xslt_file => $xslt) {
                if ($this->setXSL($xslt['filepath'], XML_XSLT_MODE_FILE)) {
                    $this->_initXSL();
                    $this->process();
                    $this->ResultDumpFile($dest_dir.'/' . $xslt['outputfile'], false);
                } else {
                    return false;
                }
            }
        }

        $this->free();
        return true;
    }

    // }}}
    // {{{ batchXSL

    /**
     * Transform multiple XML data with a single XSL files
     *
     * @param array $options Options
     *
     * @access public
     * @return mixed return
     */
    function batchXSL($options=null)
    {
        if (is_null($options)) {
            return false;
        }
        if (isset($options['outputfolder'])) {
            if (!is_dir($options['outputfolder'])) {
                if (!$this->_mkdir_p($options['outputfolder'])) {
                    return false;
                }
            }
        } else {
            $this->error = new XML_XSLT_Error (null,
                            XML_XSLT_ERROR_MISSEDDIR_FAILED,
                            E_USER_ERROR);
            return false;
        }

        $dest_dir = $options['outputfolder'];

        if (isset($options['xslt'])) {
            if (!$this->setXSL($options['xslt'], XML_XSLT_MODE_FILE)) {
                return false;
            }
            $this->_initXSL();
        }
        if (isset($options['xml_datas']) && is_array($options['xml_datas'])) {
            $xml_files = $options['xml_datas'];
            $xml_args  = '';
            foreach ($xml_files as $xml_file => $xml) {
                $mode = $xml['data'][0]=='<'?
                            XML_XSLT_MODE_STRING:XML_XSLT_MODE_FILE;

                $this->setXML($xml['data'], $mode);
                $this->_initXML();
                $this->process();
                $this->ResultDumpFile($dest_dir.'/' . $xml['outputfile'], false);
            }
        }
        $this->free();
        return true;
    }

    // }}}
    // {{{ free

    /**
     * Set the parameters for the active XSL sheet
     *
     * @access public
     * @return mixed return
     */
    function free()
    {
        unset($this->_oXML);
        unset($this->_oXSL);
    }
    // }}}
}
/*
 * Local Variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
?>
