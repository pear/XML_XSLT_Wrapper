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

class XML_XSLT_Backend_Sablotron extends XML_XSLT_Common{

    /**
     * _hXSLT
     *
     * object  DOM XML object
     * @access privat
     */
    var $_hXSLT;


    /**
     * _XML
     *
     * string  filepath or 'arg:' format
     * @access privat
     */
    var $_XML;

    /**
     * _XSL
     *
     * string  filepath or 'arg:' format
     * @access privat
     */
    var $_XSL;


    // {{{ Backend_Saxon

    /**
     * Constructor
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function XML_XSLT_Backend_Sablotron (){
        if(!defined( 'XSLT_SAXON_CMD' )){
            include_once 'System/Command.php';
            $cmd = escapeshellcmd(System_Command::which('sabcmd'));
            if($cmd!=''){
                define('XSLT_SAXON_CMD', $cmd);
            } else {
                return PEAR::raiseError();
            }
        }
        $this->console_mode = true;
    }

    // }}}
    // {{{ _buildParams

    /**
     * Set the parameters for the active XSL sheet
     *
     * @param  string  $backend name of the backend
     * @access privat
     * @return mixed return
     * @see backend
     */
    function _buildParams(){
        $arg_params = '';
        if( !is_null($this->params) ){
            $parms = $this->params;
            foreach($parms as $name=>$value){
                if(is_string($value)){
                    $value = "'".escapeshellcmd($value)."'";
                }
                $arg_params .= '$'.$name."=$value ";
            }
        }
        return $arg_params;
    }


    // }}}
    // {{{ initXSL

    /**
     * Set the XSLT data
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function initXSL(){
        switch( $this->XSL_Mode ){
            case XML_XSLT_MODE_STRING:
                    $this->_arguments['/_xsl'] = $this->xslt;
                    $this->arg_xsl  = 'arg:/_xsl';
                break;
            case XML_XSLT_MODE_FILE:
                    $this->arg_xsl  = $this->xslt;
                    if ( isset($this->_arguments['_xsl']) ){
                        unset($this->_arguments['_xsl']);
                    }
                break;
            case XML_XSLT_MODE_URI:
                     $this->arg_xsl  = $this->xslt;
                    if ( isset($this->_arguments['_xsl']) ){
                        unset($this->_arguments['_xsl']);
                    }
                break;
            default:
                $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_UNKNOWN_MODE,
                                    null, null,
                                    'Unknown XSL mode',
                                    $this->error_class, true
                                );
                return false;
        }
        $this->_initXSL_Done = true;
        return true;
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
    function initXML(){
        switch( $this->XML_Mode ){
            case XML_XSLT_MODE_STRING:
                    $this->arg_xml    = $this->_saveTempData($this->xml);
                break;
            case XML_XSLT_MODE_FILE:
                    $this->arg_xml  = $this->xml;
                break;
            case XML_XSLT_MODE_URI:
                    $this->arg_xml  = $this->xml;
                    break;
            default:
                $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_UNKNOWN_MODE,
                                    null, null,
                                    'Unknown XML mode',
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
    function process(){
        if(!$this->_initXML_Done){
            if ( !$this->initXML() ){
                return false;
            }
        }
        if( !$this->_initXSL_Done){
            if ( !$this->initXSL() ){
                return false;
            }
        }
        return true;
    }

    // }}}
    // {{{ ResultDumpMem

    /**
     * Return the result of the transformation
     *
     * @param  boolean  $free   Free the ressources
     *                          after the transformation
     *
     * @access public
     * @return mixed return
     */
    function ResultDumpMem($free=true){
        if($this->_initXSL_Done && $this->_initXSL_Done){
            exec(   XSLT_SAXON_CMD.' '.$this->xslt.' '.' '.$this->arg_xml.' '.
                    " '".$this->_buildParams()."'",
                    $result,$return_code
                );
            if($free){
                $this->free();
            }
            if($return_code==0){
                return( implode("\n",$result) );
            } else {
                $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_XSLEXEC_ERROR,
                                    null, null,
                                    'Command returned: '.$return_code,
                                    $this->error_class, true
                                );
                return '';
            }
        } else {
            return '';
        }
    }

    // }}}
    // {{{ ResultDumpFile

    /**
     * Dump the result of the transformation to a file
     *
     * @param  string   $outputFile output filepath
     *
     * @param  boolean  $free       Free the ressources
     *                              after the transformation
     *
     * @access public
     * @return mixed return
     */
    function ResultDumpFile($outputFile='',$free=true){
        if($this->_initXSL_Done && $this->_initXSL_Done){
            if($outputFile==''){
                $outputFile = escapeshellarg('pxslt_'.time());
            }
            exec( XSLT_SAXON_CMD.' '.$this->xslt ." " . $this->arg_xml .
                    " '".$this->_buildParams()."' >".$outputFile,
                    $messages,$return_code
                );
            if($free){
                $this->free();
            }
            if($return_code==0){
                return $outputFile;
            } else {
                $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_XSLEXEC_ERROR,
                                    null, null,
                                    'Command returned: '.$return_code,
                                    $this->error_class, true
                                );
                return false;
            }
        } else {
            return false;
        }
    }

    // }}}
    // {{{ ResultDumpOut

    /**
     * Dumps the result of the transformation to stdout
     *
     * @param  boolean  $free   Free the ressources or not
     *                          after the transformation
     *
     * @access public
     * @return mixed return
     */
    function ResultDumpOut($free=true){
        if($this->_initXSL_Done && $this->_initXSL_Done){
            if( $this->XML_Mode==XML_XSLT_MODE_STRING ){
                $command_pipe = popen ("sabcmd ". $this->xslt ." ".'file://stdin $numberofcols=3',"w");
                fwrite ($command_pipe,$this->xml);
                pclose ($command_pipe);
            } else {
                passthru(   XSLT_SAXON_CMD.' '. $this->xslt ." " . $this->xml  .
                            " '".$this->_buildParams()."' "

                        );
            }
            if($free){
                $this->free();
            }
            return true;
        } else {
            return false;
        }
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
    function batchXML($options=null){
        if(is_null($options)){
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_NOOPTIONS,
                                null, null,
                                ' missing XML data',
                                $this->error_class, true
                            );
            return false;
        }
        if( isset($options['outputfolder']) ){
            if(!is_dir($options['outputfolder'])){
                if(!$this->_mkdir_p($options['outputfolder'])){
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
        if(isset($options['xml'])){
            $mode       = $options['xml'][0]=='<'?
                            XML_XSLT_MODE_STRING:XML_XSLT_MODE_FILE;
            if (!$this->setXML($options['xml'],$mode)){
                return false;
            }
            $this->initXML();
        } else {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_XML_EMPTY,
                                null, null,
                                ' missing XML data',
                                $this->error_class, true
                            );
            return false;
        }
        if(isset($options['xslt_files']) && is_array($options['xslt_files'])){
            $xsl_files = $options['xslt_files'];
            $xslt_args = '';
            foreach($xsl_files as $xslt_file=>$xslt){
                $xslt_args .= ' '.escapeshellarg($xslt['filepath']) . ' '.
                              $dest_dir.$xslt['outputfile'];
            }
            exec( XSLT_SAXON_CMD.' -x '.$this->arg_xml ." " . $xslt_args .
                    " '".$this->_buildParams()."' ",
                    $messages,$return_code
                );
        }
        $this->free();
        if($return_code==0){
            return true;
        } else {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_XSLEXEC_ERROR,
                                null, null,
                                'Command returned: '.$return_code,
                                $this->error_class, true
                            );
            return false;
        }
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
    function batchXSL($options=null){
        if(is_null($options)){
            return false;
        }
        if( isset($options['outputfolder']) ){
            if(!is_dir($options['outputfolder'])){
                if(!$this->_mkdir_p($options['outputfolder'])){
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
        if(isset($options['xslt'])){
            if (!$this->setXSL($options['xslt'],XML_XSLT_MODE_FILE)){
                return false;
            }
            $this->initXSL();
        } else {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_XSL_EMPTY,
                                null, null,
                                'missing XSL data in batchXSL',
                                $this->error_class, true
                            );
            return false;
        }
        if(isset($options['xml_datas']) && is_array($options['xml_datas'])){
            $xml_files = $options['xml_datas'];
            $xml_args = '';
            foreach($xml_files as $xml_file=>$xml){
                $mode       = $xml['data'][0]=='<'?
                            XML_XSLT_MODE_STRING:XML_XSLT_MODE_FILE;
                if($xml['data'][0]=='<'){
                    $xmlfile    = $this->_saveTempData($xml['data']);
                } else {
                    $xmlfile    = $xml['data'];
                }
                $xml_args   .= ' '.escapeshellarg($xmlfile) . ' '.
                                $dest_dir.$xml['outputfile'];
            }
            exec( XSLT_SAXON_CMD.' -s '.$this->arg_xsl ." " . $xml_args .
                    " '".$this->_buildParams()."' ",
                    $messages,$return_code
                );
        }
        $this->free();
        if($return_code==0){
            return true;
        } else {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_XSLEXEC_ERROR,
                                null, null,
                                'Command returned: '.$return_code,
                                $this->error_class, true
                            );
            return false;
        }
    }

    // }}}
    // {{{ free

    /**
     * Free all ressources
     *
     * @access public
     * @return mixed return
     * @
     */
    function free(){
        $this->_removeTempData();
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