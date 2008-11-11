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
 * MSXML2 implemention
 *
 * @category XML
 * @package  XML_XSLT_Wrapper
 * @author   Pierre-Alain Joye <pajoye@pearfr.org>
 * @license  PHP 2.02 http://www.php.net/license/2_02.txt
 * @link     http://pear.php.net/packages/XML_XSLT_Wrapper
 */
class XML_XSLT_Backend_MSXSL_Com extends XML_XSLT_Common
{
    /**
     * _xsldom
     *
     * ressource XSL sheet DOM document object
     * @access private
     */
    var $_xsldom;


    /**
     * _xmldom
     *
     * ressource XML sheet DOM document object
     * @access private
     */
    var $_xmldom;

    /**
     * _xslt
     *
     * ressource MSXML2.XSLTemplate Object
     * @access private
     */
    var $_xsltcom;


    // {{{ Backend_MSXSL_Com

    /**
     * Constructor
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function XML_XSLT_Backend_MSXSL_Com()
    {
        if (!(OS_WINDOWS && $this->_xsltcom = new COM("MSXML2.XSLTemplate.4.0"))) {
            return PEAR::raiseError(null, XML_XSLT_ERROR_BACKEND_FAILED,
                        null, null, 'You need the COM extension to run this Backend', 'XML_XSLT_Error', true);
        }
    }

    // }}}
    // {{{ buildParams

    /**
     * Set the parameters for the active XSL sheet
     *
     * @access private
     * @see backend
     * @return null
     */
    function _buildParams()
    {
        $arg_params = '';
        if (!is_null($this->params)) {
            $parms = $this->params;
            foreach ($parms as $name => $value) {
                $this->xslproc->addParameter($name, $value);
            }
        }
    }

    // }}}
    // {{{ _loadDOM

    /**
     * Set the xml data from a file or a string variable
     *
     * @param unknown $data Data
     * @param unknown $mode Mode
     *
     * @access private
     * @return mixed return
     * @
     */
    function &_loadDOM($data, $mode)
    {
        $dom = new COM("MSXML2.FreeThreadedDOMDocument.4.0");
        if ($mode==XML_XSLT_MODE_STRING) {
            $result = $dom->loadXML($data);
            if (!$dom->loadXML($data)) {
                $this->_error($dom);
                $this->error = PEAR::raiseError(null,
                        XML_XSLT_ERROR_XSMPARSER_ERROR,
                        null, null,
                        'Error: ' . $this->error_code .':'.
                        $this->error_message,
                        $this->error_class, true);
                return null;
            }
        } else {
            if (!$dom->load($data)) {
                $this->_error($dom);
                $this->error = PEAR::raiseError(null,
                        XML_XSLT_ERROR_XMLPARSER_ERROR,
                        null, null,
                        'Error: ' . $this->error_code .':'.
                        $this->error_message,
                        $this->error_class, true);
                return null;
            }
        }
        return $dom;
    }

    // }}}
    // {{{ _initXSL

    /**
     * Set the XSLT sheet
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function _initXSL()
    {
        if ($this->_xsldom = $this->_loadDOM($this->xslt, $this->XSL_Mode)) {
            $this->_xsldom->async = false;
            $this->_initXSL_Done  = true;
            return true;
        } else {
            $this->_initXSL_Done = false;
            return false;
        }
    }

    // }}}
    // {{{ _initXML

    /**
     * Set the XML DATA
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function _initXML()
    {
        if ($this->_xmldom = $this->_loadDOM($this->xml, $this->XML_Mode)) {
            $this->_xmldom->async = false;
            $this->_initXML_Done  = true;
            return true;
        } else {
            $this->_initXSL_Done = false;
            return null;
        }
    }

    // }}}
    // {{{ process

    /**
     * Do the transformation and store the result
     * to the result property
     *
     * @access public
     * @return mixed return
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
        if (is_null($this->params)) {
            $this->result = $xmldoc->transformNode($this->_xsldom);
        } else {
            $this->_xsltcom->stylesheet = $this->_xsldom;

            $this->xslproc = $this->_xsltcom->createProcessor();

            $this->xslproc->input = $this->_xmldom;

            $this->_buildParams();
            if ($this->xslproc->transform()) {
                $this->result = $this->xslproc->output;
                return true;
            } else {
                $this->_error($this->xslproc);
                $this->error = PEAR::raiseError(null,
                        XML_XSLT_ERROR_XSLPARSER_ERROR,
                        null, null,
                        'Error: ' . $this->error_code .':'.
                        $this->error_message,
                        $this->error_class, true);
                return false;
            }
        }
    }

    // }}}
    // {{{ ResultDumpMem

    /**
     * Return the transformation result
     *
     * @param boolean $free Free the ressources or not
     *                          after the transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpMem($free=true)
    {
        if ($this->_initXSL_Done && $this->_initXSL_Done) {
            if ($free) {
                $this->free();
            }
            return $this->result;
        }

        return '';
    }

    // }}}
    // {{{ ResultDumpFile

    /**
     * Dumps the transformation result to a file
     *
     * @param string  $output_file File
     * @param boolean $free        Free the ressources or not
     *                             after the transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpFile($output_file='', $free=true)
    {
        $result = $this->_saveResult($output_file);
        if ($free) {
            $this->free();
        }
        return $result;
    }

    // }}}
    // {{{ saveResult

    /**
     * Dumps the transformation result to stdout
     *
     * @param boolean $free Free the ressources or not
     *                          after the transformation
     *
     * @access public
     * @return mixed return
     * @see backend
     */
    function ResultDumpOut($free=true)
    {
        if ($this->_initXSL_Done && $this->_initXSL_Done) {
            echo $this->result;
            if ($free) {
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
     * @param array $options options
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
                                'missing options',
                                $this->error_class, true);
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
                            $this->error_class, true);
            return false;
        }
        $dest_dir = $options['outputfolder'];
        if (isset($options['xml'])) {
            $xmldoc = new COM("Msxml2.DOMDocument.4.0");
            if ($options['xml'][0]=='<') {
                $mode = XML_XSLT_MODE_STRING;
                $xmldoc->loadXML($options['xml']);
            } else {
                $mode = XML_XSLT_MODE_FILE;
                $xmldoc->load($options['xml']);
            }
            $xmldoc->async = false;

            if ($xmldoc->parseError->errorCode) {
                $this->_error($xmldoc);
                return false;
            }
        } else {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_XML_EMPTY,
                                null, null,
                                ' missing XML data',
                                $this->error_class, true);
        }
        if (isset($options['xslt_files']) && is_array($options['xslt_files'])) {
            $xsl_files = $options['xslt_files'];
            $xslt_args = '';

            foreach ($xsl_files as $xslt_file => $xslt) {
                $xsldoc        = new COM("Msxml2.FreeThreadedDOMDocument.4.0");
                $xsldoc->async = false;
                $xsldoc->load($xslt['filepath']);
                if ($xsldoc->parseError->errorCode) {
                    $this->_error($xsldoc);
                    $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_XSLEXEC_ERROR,
                                    null, null,
                                    $this->error_message,
                                    $this->error_class, true);
                    return false;
                }
                $this->result = $xmldoc->transformNode($xsldoc);
                $this->ResultDumpFile($dest_dir.'/' . $xslt['outputfile'], false);
            }
        } else {
            $this->error = PEAR::raiseError(null,
                            XML_XSLT_ERROR_XSL_EMPTY,
                            null, null,
                            'Missing XSL data',
                            $this->error_class, true);
            return false;
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
     * @
     */
    function batchXSL($options=null)
    {
        $error = false;
        if (is_null($options)) {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_NOOPTIONS,
                                null, null,
                                'missing options',
                                $this->error_class, true);
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
                            $this->error_class, true);
            return false;
        }
        $dest_dir = $options['outputfolder'];
        if (isset($options['xslt'])) {
            $xsldoc = new COM("Msxml2.DOMDocument.4.0");

            $xsldoc->async = false;
            if ($options['xslt'][0]=='<') {
                $xsldoc->loadXML($options['xslt']);
            } else {
                $xsldoc->load($options['xslt']);
            }
        }

        if (isset($options['xml_datas']) && is_array($options['xml_datas'])) {
            $xml_files = $options['xml_datas'];
            $xml_args  = '';
            foreach ($xml_files as $xml_file => $xml) {
                $xmldoc = new COM("Msxml2.DOMDocument.4.0");

                $xmldoc->async = false;
                if ($xml['data'][0]=='<') {
                    $xmldoc->loadXML($xml['data']);
                } else {
                    $xmldoc->load($xml['data']);
                }
                if (!$this->result = $xmldoc->transformNode($xsldoc)) {
                    $this->_error($xmldoc);
                    return false;
                }
                $this->ResultDumpFile($dest_dir.'/' . $xml['outputfile'], false);
            }
        } else {
            $this->error = PEAR::raiseError(null,
                            XML_XSLT_ERROR_XSL_EMPTY,
                            null, null,
                           'Missed XML data',
                            $this->error_class, true);
            return false;
        }
        $this->free();
        return true;
    }

    // }}}
    // {{{ _error

    /**
     * return an error object build from xml error
     *
     * @param object &$domxml DOMXML object
     *
     * @access _private
     * @return mixed return
     * @see backend
     */
    function _error(&$domxml)
    {
        $msg  = 'Error Code:' . $domxml->parseError->errorCode."\n";
        $msg .= 'Reason: ' . $domxml->parseError->reason."";
        $msg .= 'URL: ' . $domxml->parseError->url."\n";
        $msg .= 'Line: ' . $domxml->parseError->line."\n";
        $msg .= 'Col: ' . $domxml->parseError->linepos."\n";
        $msg .= 'File Pos:' . $domxml->parseError->filepos."\n";
        $msg .= 'Src Text: ' . $domxml->parseError->srcText;

        $this->error_message = $msg;
        $this->error_code    = $domxml->parseError->errorCode;
    }

    // }}}
    // {{{ free

    /**
     * Free all ressources
     *
     * @access public
     * @return mixed return
     */
    function free()
    {
        unset($this->_xsltcom);
        unset($this->_xmlcom);
        unset($this->xslproc);
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
