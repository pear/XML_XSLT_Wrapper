<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Pierre-Alain Joye <pajoye@pearfr.org>                       |
// +----------------------------------------------------------------------+
//
// $Id$
//

class XML_XSLT_Backend_DOM_XSL extends XML_XSLT_Common
{
    /**
     * _oXML
     *
     * object  DOM XML object
     * @access private
     */
    var $_oXML;

    /**
     * _oXSL
     *
     * object   DOM XSL object
     * @access private
     */
    var $_oXSL;

    // {{{ Backend_DOM_XSL

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     */
    function XML_XSLT_Backend_DOM_XSL ()
    {
        if (!function_exists('domxml_xslt_stylesheet')) {
            if (!PEAR::loadExtension('domxsl')) {
                return PEAR::raiseError(null, XML_XSLT_ERROR_BACKEND_FAILED,
                                    null, null, null, 'XML_XSLT_Error', true);
            }
        }
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
     * Set the parameters for XSLT
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function _initXSL()
    {
        switch( $this->XSL_Mode ){
            case XML_XSLT_MODE_STRING:
                    $this->_oXSL = @domxml_xslt_stylesheet($this->xslt);
                break;
            case XML_XSLT_MODE_FILE:
                    $this->_oXSL = @domxml_xslt_stylesheet_file($this->xslt);
                break;
            case XML_XSLT_MODE_URI:
                    $xslt = $this->getURIContent($this->xslt);
                    $this->_oXSL = @domxml_xslt_stylesheet($this->xslt);
                break;
        }
        if (! $this->_oXSL) {
            return PEAR::raiseError(null, XML_XSLT_ERROR_LOADXSL_FAILED,
                                null, null, null, 'XML_XSLT_Error', true);
            return false;
        }
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
        switch( $this->XML_Mode ){
            case XML_XSLT_MODE_STRING:
                    $this->_oXML = @domxml_open_mem($this->xml);
                break;
            case XML_XSLT_MODE_FILE:
                    $this->_oXML = @domxml_open_file($this->xml);
                break;
            case XML_XSLT_MODE_URI:
                    $xml = $this->getURIContent($this->xml);
                    $this->_oXML = @domxml_open_mem($xml);
                break;
        }
        if (!$this->_oXML) {
            return PEAR::raiseError(null, XML_XSLT_ERROR_XMLPARSER_ERROR,
                        null, null, 'XSL File:' . $data, 'XML_XSLT_Error', true);
            return false;
        }
        $this->_initXML_Done = true;
        return true;
    }

    // }}}
    // {{{ process

    /**
     * Set the parameters for the active XSL sheet
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
            if (!$this->_initXML_Done) {
                if (!$this->_initXML()) {
                    return false;
                }
            }
            if (is_array($this->params)) {
                $this->result = $this->_oXSL->process( $this->_oXML,
                                                        $this->params);
            } else {
                $this->result = $this->_oXSL->process($this->_oXML);
            }
            if (!$this->result) {
                return PEAR::raiseError(null, XML_XSLT_ERROR_PROCESS_FAILED,
                        null, null, 'XSL File:' . $data, 'XML_XSLT_Error', true);

                return false;
            }
        }
        return true;
    }

    // }}}
    // {{{ ResultDumpOut

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  boolean  $free Free the ressources
     *                  after transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpOut($free=true)
    {
        if (!$this->error_code && $this->_initXSL_Done && $this->_initXML_Done) {
            if ($this->outputEncoding!=''){
                echo $this->result->dump_mem(true,$this->outputEncoding);
            } else {
                echo $this->result->dump_mem(true);
            }
            return true;
        }
        return false;
    }

    // }}}
    // {{{ ResultDumpMem

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  boolean  $free Free the ressources
     *                  after transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpMem($free=true)
    {
        if (!$this->error_code && $this->_initXSL_Done && $this->_initXML_Done) {
            if ($this->outputEncoding!=''){
                return $this->result->dump_mem(true,$this->outputEncoding);
            } else {
                return $this->result->dump_mem(true);
            }
        } else {
            return '';
        }
    }

    // }}}
    // {{{ ResultDumpFile

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  boolean  $free Free the ressources
     *                  after transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpFile($output_file='', $free=true)
    {
        $return = false;
        if (!$this->error_code && $this->_initXSL_Done && $this->_initXML_Done) {
            if ( $output_file=='') {
                $output_file = $this->outputFile;
            }
            if (!$this->error_code) {
                if ($this->result->dump_file($output_file,false)) {
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
     * @access public
     * @return mixed return
     * @
     */
    function batchXML($options=null)
    {
        if (is_null($options)) {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_NOOPTIONS,
                                null, null,
                                ' missing XML data',
                                $this->error_class, true
                            );
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
        $dest_dir   = $options['outputfolder'];
        if (isset($options['xml'])) {
            $mode       = $options['xml'][0]=='<'?
                            XML_XSLT_MODE_STRING:XML_XSLT_MODE_FILE;
            if (!$this->setXML($options['xml'],$mode)){
                $this->error = new XML_XSLT_Error (null,
                                XML_XSLT_ERROR_XML_EMPTY,
                                E_USER_ERROR);
                 return false;
            }
            $this->_initXML();
        } else {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_XML_EMPTY,
                                null, null,
                                ' missing XML data',
                                $this->error_class, true
                            );
        }
        if (isset($options['xslt_files']) && is_array($options['xslt_files'])) {
            $xsl_files = $options['xslt_files'];
            $xslt_args = '';
            foreach ($xsl_files as $xslt_file => $xslt) {
                if ($this->setXSL($xslt['filepath'],XML_XSLT_MODE_FILE)) {
                    $this->_initXSL();
                    $this->process();
                    $this->ResultDumpFile($dest_dir.'/' . $xslt['outputfile'],false);
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
     * @access public
     * @return mixed return
     * @
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
        $dest_dir   = $options['outputfolder'];

        if (isset($options['xslt'])) {
            if (!$this->setXSL($options['xslt'],XML_XSLT_MODE_FILE)){
                return false;
            }
            $this->_initXSL();
        }
        if (isset($options['xml_datas']) && is_array($options['xml_datas'])) {
            $xml_files = $options['xml_datas'];
            $xml_args = '';
            foreach ($xml_files as $xml_file => $xml) {
                $mode       = $xml['data'][0]=='<'?
                            XML_XSLT_MODE_STRING:XML_XSLT_MODE_FILE;
                $this->setXML($xml['data'],$mode);
                $this->_initXML();
                $this->process();
                $this->ResultDumpFile($dest_dir.'/' . $xml['outputfile'],false);
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
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     * @
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
