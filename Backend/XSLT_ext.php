<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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

class XML_XSLT_Backend_XSLT_ext extends XML_XSLT_Common
{
    /**
     * _hXSLT
     *
     * object  DOM XML object
     * @access private
     */
    var $_hXSLT;


    /**
     * _XML
     *
     * string  filepath or 'arg:' format
     * @access private
     */
    var $_XML;

    /**
     * _arguments
     *
     * string  filepath or 'arg:' format
     * @access private
     */
    var $_arguments = null;

    /**
     * _XSL
     *
     * string  filepath or 'arg:' format
     * @access private
     */
    var $_XSL;

    /**
     * _XSL
     *
     * string  filepath or 'arg:' format
     * @access private
     */
    var $nativeErrorCode;

    // {{{ Backend_XSLT_ext

    /**
     * Constructor
     *
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     * @see backend
     */
    function Backend_XSLT_ext ()
    {
        if (!function_exists('xslt_create')) {
            include_once('PEAR.php');
            if (!PEAR::loadExtension('xslt')) {
                return PEAR::raiseError(
                    'The xslt extension can not be found.', true
                );
            }
        }
    }

    // }}}
    // {{{ buildParams

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     * @see backend
     */
    function buildParams()
    {
        // do not need to build them
    }


    // }}}
    // {{{ initXSL

    /**
     * Set the XSLT data
     *
     * @access public
     * @return mixed return
     */
    function _initXSL()
    {
        if ($this->hXSLT    = xslt_create()) {
            switch( $this->XSL_Mode ){
                case XML_XSLT_MODE_STRING:
                        $this->_arguments['/_xsl'] = &$this->xslt;
                        $this->arg_xsl  = 'arg:/_xsl';
                    break;
                case XML_XSLT_MODE_FILE:
                        $this->arg_xsl  = $this->xslt;
                        if (isset($this->_arguments['_xsl'])){
                            unset($this->_arguments['_xsl']);
                        }
                    break;
                case XML_XSLT_MODE_URI:
                        $this->arg_xsl  = $this->xslt;
                        if (isset($this->_arguments['_xsl'])){
                            unset($this->_arguments['_xsl']);
                        }
                    break;
                default:
                    return false;
            }
            $this->_initXSL_Done = true;
            return true;
        } else {
            $this->error = PEAR::raiseError(null,
                    XML_XSLT_ERROR_XSLPARSER_ERROR,
                    null, null,
                    'Error: '.xslt_errno($this->_oXSL).':'.
                    xslt_error($this->_oXSL),
                    $this->error_class, true);
            return false;
        }
    }

    // }}}
    // {{{ initXML

    /**
     * Set the XML DATA
     *
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function _initXML()
    {
        switch( $this->XML_Mode ){
            case XML_XSLT_MODE_STRING:
                    $this->_arguments['/_xml'] = &$this->xml;
                    $this->arg_xml  = 'arg:/_xml';
                break;
            case XML_XSLT_MODE_FILE:
                    $this->arg_xml  = $this->xml;
                    if (isset($this->_arguments['_xml'])){
                        unset($this->_arguments['_xml']);
                    }
                break;
            case XML_XSLT_MODE_URI:
                    $this->arg_xml  = $this->xml;
                    if (isset($this->_arguments['_xml'])){
                        unset($this->_arguments['_xml']);
                    }
                break;
            default:
                $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_UNKNOWN_MODE,
                                    null, null,
                                    'Unknown mode',
                                    $this->error_class, true
                                );
                return false;
        }
        $this->_initXML_Done = true;
        return true;
    }

    // }}}
    // {{{ process

    /**
     * Do nothing with this backend, all process
     * and ouputs are done in ResultDumpXXXX methods
     *
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     * @see backend
     */
    function process()
    {
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
        return true;
    }

    // }}}
    // {{{ ResultDumpOut

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpOut($free=true)
    {
        $error = false;
        if ($this->_initXSL_Done && $this->_initXSL_Done) {
            if ($this->error_code==0) {
                $args = isset($this->_arguments)&&sizeof($this->_arguments)?$this->_arguments:array();
                if ($this->result=xslt_process($this->hXSLT, $this->arg_xml, $this->arg_xsl, null, $args, $this->params)) {
                    echo $this->result;
                    if ($free) {
                        $this->free();
                    }
                } else {
                    $error      = true;
                    $error_code = XML_XSLT_ERROR_XSLEXEC_ERROR;
                    $error_user = xslt_errno($this->_oXSL).
                                  ': '.xslt_error($this->_oXSL);
                }
            }
        }
        if ($error) {
            $this->error = PEAR::raiseError(null,
                            $error_code,
                            null, null,
                            $error_user,
                            $this->error_class, true
                        );
            return false;
        } else {
            return true;
        }
    }

    // }}}
    // {{{ dumpResult

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpMem($free=true)
    {
        $result = '';
        if ($this->_initXSL_Done && $this->_initXSL_Done) {
            if ($this->error_code==0) {
                $args = isset($this->_arguments)&&sizeof($this->_arguments)?
                        $this->_arguments:array();
                $result = @xslt_process($this->hXSLT, $this->arg_xml,
                            $this->arg_xsl, null, $args, $this->params);
                if ($result){
                    if ($free) {
                        $this->free();
                    }
                } else {
                    $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_XSLEXEC_ERROR,
                                    null, null,
                                    xslt_errno($this->_oXSL).
                                    ': '.xslt_error($this->_oXSL),
                                    $this->error_class, true
                                );
                }
            }
        }
        return $result;
    }

    // }}}
    // {{{ saveResult

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpFile($output_file='',$free=true)
    {
        $error = false;
        if ($this->_initXSL_Done && $this->_initXSL_Done) {
            if ($this->error_code==0) {
                if ($output_file=='') {
                    $output_file = $this->outputFile;
                }
                $args = isset($this->_arguments)&&sizeof($this->_arguments)?
                        $this->_arguments:array();
                $params = isset($this->params)&&sizeof($this->params)?
                        $this->params:array();
                $result = xslt_process($this->hXSLT, $this->arg_xml,
                            $this->arg_xsl, $output_file,
                            $args, $params);
                if (!$result) {
                    $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_XSLEXEC_ERROR,
                                    null, null,
                                    xslt_errno($this->hXSLT).
                                    ': '.xslt_error($this->hXSLT),
                                    $this->error_class, true
                                );
                    $error = true;
                }
                if ($free) {
                    $this->free();
                }
            }
        }
        return !$error;
    }

    // }}}
    // {{{ batchXML

    /**
     * Transform single XML data with multiple XSL files
     *
     * TODO Add errors in the loop process
     *
     * @access public
     * @return mixed return
     * @
     */
    function batchXML($options=null)
    {
        $error = false;
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
            $this->error = PEAR::raiseError(null,
                            XML_XSLT_ERROR_MISSEDDIR_FAILED,
                            null, null,
                           'Output folder missing',
                            $this->error_class, true
                        );
            return false;
        }
        $dest_dir   = $options['outputfolder'];
        if (isset($options['xml'])) {
            $mode       = $options['xml'][0]=='<'?
                            XML_XSLT_MODE_STRING:XML_XSLT_MODE_FILE;
            if (!$this->setXML($options['xml'],$mode)){
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
            foreach($xsl_files as $xslt_file => $xslt){
                if (!$this->setXSL($xslt['filepath'],XML_XSLT_MODE_FILE)){
                    $error = true;
                    break;
                }
                if (!$this->_initXSL()){
                    $error = true;
                    break;
                }
                if (!$this->process()){
                    $error = true;
                    break;
                }
                if (!$this->ResultDumpFile($dest_dir.'' . $xslt['outputfile'],false)){
                    $error = true;
                    break;
                }
            }
        }
        // TODO Add error if xslt_files missing
        $this->free();
        return !$error;
    }

    // }}}
    // {{{ batchXSL

    /**
     * Transform multiple XML data with a single XSL files
     *
     * TODO Add errors in the loop process
     *
     * @access public
     * @return mixed return
     * @
     */
    function batchXSL($options)
    {
        $error = false;
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
            $this->error = PEAR::raiseError(null,
                            XML_XSLT_ERROR_MISSEDDIR_FAILED,
                            null, null,
                           'Output folder missing',
                            $this->error_class, true
                        );
            return false;
        }
        $dest_dir   = $options['outputfolder'];
        if (isset($options['xslt'])) {
            if (!$this->setXSL($options['xslt'],XML_XSLT_MODE_FILE)){
                    return false;
            }
            if (!$this->_initXSL()){
                return false;
            }
        }
        if (isset($options['xml_datas']) && is_array($options['xml_datas'])) {
            $xml_files = $options['xml_datas'];
            $xml_args = '';
            foreach($xml_files as $xml_file => $xml){
                $mode       = $xml['data'][0]=='<'?
                            XML_XSLT_MODE_STRING:XML_XSLT_MODE_FILE;
                if (!$this->setXML($xml['data'],$mode)){
                    $error = true;
                    break;
                }
                if (!$this->_initXML()){
                    $error = true;
                    break;
                }
                if (!$this->process()){
                    $error = true;
                    break;
                }
                if (!$this->ResultDumpFile($dest_dir.'/' . $xml['outputfile'],false)) {
                    $error = true;
                    break;
                }
            }
        }
        // TODO Add error if xml_datas missing
        return !$error;
    }

    // }}}
    // {{{ free

    /**
     * Free all ressources
     *
     * @param  string  $backend name of the backend
     * @access public
     * @return mixed return
     * @
     */
    function free()
    {
        xslt_free($this->hXSLT);
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
