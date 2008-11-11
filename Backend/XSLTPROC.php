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
 * Backend which manages
 * interaction with the xsltproc command.
 * 
 * This command is part of the libxslt library.
 *
 * @category XML
 * @package  XML_XSLT_Wrapper
 * @author   Pierre-Alain Joye <pajoye@pearfr.org>
 * @license  PHP 2.02 http://www.php.net/license/2_02.txt
 * @link     http://pear.php.net/packages/XML_XSLT_Wrapper
 * @see      http://xmlsoft.org/XSLT/
 */
class XML_XSLT_Backend_XSLTPROC extends XML_XSLT_Common
{
    /**
     * _arguments
     *
     * string  arguments console string format
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
     * _arg_xsl
     *
     * string  filepath to the xsl file
     * @access private
     */
    var $_arg_xsl;


    /**
     * _arg_xml
     *
     * string  filepath to the xml file
     * @access private
     */
    var $_arg_xml;

    // {{{ Backend_XSLTPROC

    /**
     * Constructor
     *
     * @access public
     * @return mixed  nothing or PEAR_Error object
     */
    function XML_XSLT_Backend_XSLTPROC()
    {
        if (!defined('XML_XSLT_XSLTPROC_CMD')) {
            include_once 'System.php';
            $cmd = escapeshellcmd(System::which('xsltproc'));
            if ($cmd != '') {
                define('XML_XSLT_XSLTPROC_CMD', $cmd);
            } else {
                return PEAR::raiseError('command xsltproc not found');
            }
        }
        $this->console_mode = true;
    }

    // }}}
    // {{{ _buildParams

    /**
     * Set the parameters for the active XSL sheet
     * pass a (parameter,value) pair
     *         value is an UTF8 XPath expression
     *
     * @access private
     * @return string   string containing the parameters 
     *                  to pass to the command
     */
    function _buildParams()
    {
        $arg_params = '';

        if (!is_null($this->params)) {
            $parms = $this->params;
            foreach ($parms as $name => $value) {
                if (is_string($value)) {
                    $arg_params .= ' ' . $name . " '" . escapeshellcmd($value) . "' ";
                } else {
                    if (is_numeric($value)) {
                        $arg_params .= ' ' . $name . '=' . $value;
                    }
                }
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
    function initXSL()
    {
        switch ($this->XSL_Mode) {
        case XML_XSLT_MODE_STRING:
            $this->_arg_xsl = $this->_saveTempData($this->xsl);
            break;
        case XML_XSLT_MODE_FILE:
            $this->_arg_xsl = $this->xslt;
            break;
        case XML_XSLT_MODE_URI:
            $this->_arg_xsl = $this->xslt;
            break;
        default:
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_UNKNOWN_MODE,
                                null, null,
                                'Unknown XSL mode',
                                $this->error_class, true);
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
     * @access public
     * @return mixed return
     * @see backend
     */
    function initXML()
    {
        switch ($this->XML_Mode) {
        case XML_XSLT_MODE_STRING:
            $this->_arg_xml = $this->_saveTempData($this->xml);
            break;
        case XML_XSLT_MODE_FILE:
            $this->_arg_xml = $this->xml;
            break;
        case XML_XSLT_MODE_URI:
            $this->_arg_xml = $this->xml;
            break;
        default:
            $this->error = $this->raiseError(XML_XSLT_ERROR_UNKNOWN_MODE,
                                            null, null,
                                            'Unknown XML mode');
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
     * @access public
     * @return mixed return
     * @see backend
     */
    function process()
    {
        if (!$this->_initXML_Done) {
            if (!$this->initXML()) {
                return false;
            }
        }
        if (!$this->_initXSL_Done) {
            if (!$this->initXSL()) {
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
     * @param boolean $free Free the ressources
     *                          after the transformation
     *
     * @access public
     * @return mixed return
     */
    function ResultDumpMem($free = true)
    {
        if ($this->_initXSL_Done && $this->_initXSL_Done) {
            exec(XML_XSLT_XSLTPROC_CMD . ' ' . $this->_buildParams() . ' ' .
                    $this->_arg_xsl . ' ' . $this->_arg_xml,
                    $result, $return_code);

            if ($free) {
                $this->free();
            }

            if (is_array($result)) {
                $string_result = implode("\n", $result);
            } else {
                $string_result = $result;
            }

            if ($return_code == 0 && is_string($string_result) && strlen($string_result) > 0) {
                return $string_result;
            } else {
                $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_XSLEXEC_ERROR,
                                    null, null,
                                    'Command returned: ' . $return_code.
                                    ' ' . $string_result,
                                    $this->error_class, true);
                return false;
            }
        }

        return false;
    }

    // }}}
    // {{{ ResultDumpFile

    /**
     * Dump the result of the transformation to a file
     *
     * @param boolean $free Free the ressources
     *                          after the transformation
     *
     * @access public
     * @return mixed return
     */
    function ResultDumpFile($free = true)
    {
        if ($this->_initXSL_Done && $this->_initXSL_Done) {
            exec(XML_XSLT_XSLTPROC_CMD . ' ' . $this->_buildParams() . ' ' .
                 ' -o ' . $this->outputFile . ' ' .
                 $this->_arg_xsl . ' ' . $this->_arg_xml,
                 $result, $return_code);

            if ($free) {
                $this->free();
            }

            if (is_array($result) && sizeof($result) > 0) {
                $string_result = implode("\n", $result);
            } else {
                $string_result = $result;
            }

            if ($return_code == 0) {
                return true;
            } else {
                $this->error = PEAR::raiseError(null,
                                    XML_XSLT_ERROR_XSLEXEC_ERROR,
                                    null, null,
                                    'Command returned: ' . $return_code.
                                    ' ' . $string_result,
                                    $this->error_class, true);
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
     * @param boolean $free Free the ressources
     *                          after the transformation
     *
     * @access public
     * @return mixed return
     */
    function ResultDumpOut($free = true)
    {
        if ($this->_initXSL_Done && $this->_initXSL_Done) {
            passthru(XML_XSLT_XSLTPROC_CMD . ' ' . $this->_buildParams() . ' ' .
                      $this->_arg_xsl . ' ' . $this->_arg_xml);

            if ($free) {
                $this->free();
            }
        } else {
            return false;
        }
    }

    // }}}
    // {{{ batchXML

    /**
     * Transform one single XML data with multiple XSL files
     *
     * @param array $options array as follows
     *                 $options['outputfolder'] = './outputbatch2/';
     *                 $options['xml'] = $xml_file_here;
     *                 $options['xslt_files'] = array(
     *                                                 array(
     *                                                     'filepath' => 'examples/table.xsl',
     *                                                     'outputfile' => 't1'
     *                                                 ),
     *                                                 array(
     *                                                     'filepath' => 'examples/table2.xsl',
     *                                                     'outputfile' => 't2'
     *                                                 )
     *                                             );
     *
     * @return mixed return
     */
    function batchXML($options = null)
    {
        if (is_null($options)) {
            $this->error = $this->raiseError(null,
                                XML_XSLT_ERROR_NOOPTIONS,
                                null, null,
                                ' missing XML data',
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
            $mode = $options['xml'][0] == '<' ?
                            XML_XSLT_MODE_STRING : XML_XSLT_MODE_FILE;

            if (!$this->setXML($options['xml'], $mode)) {
                $this->error = PEAR::raiseError(1002);
                return false;
            }
            $this->initXML();
        } else {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_XML_EMPTY,
                                null, null,
                                ' missing XML data',
                                $this->error_class, true);
            return false;
        }

        if (isset($options['xslt_files']) && is_array($options['xslt_files'])) {
            $xsl_files = $options['xslt_files'];
            $xslt_args = '';

            foreach ($xsl_files as $xslt_file => $xslt) {
                exec(XML_XSLT_XSLTPROC_CMD . ' ' . $this->_buildParams() . ' ' .
                        ' -o ' . $dest_dir . $xslt['outputfile'] .
                        escapeshellarg($xslt['filepath']) . ' ' .
                        $this->_arg_xml,
                        $messages, $return_code);

                if ($return_code==0) {
                    $error = false;
                } else {
                    $this->error = PEAR::raiseError(null,
                                        XML_XSLT_ERROR_XSLEXEC_ERROR,
                                        null, null,
                                        'Command returned: ' . $return_code.
                                        ' ' . $messages,
                                        $this->error_class, true);

                    $error = true;
                    break;
                }
            }
        }
        $this->free();
        return !$error;
    }

    // }}}
    // {{{ batchXSL

    /**
     * Transform multiple XML data with a single XSL files
     *
     * @param array $options associative array of the following form
     *     $options['xslt']         = './examples/table.xsl';
     *     $options['xml_datas']   = array(
     *                                     array(
     *                                         'data'=>'examples/items1.xml',
     *                                         'outputfile'=>'t1'
     *                                     ),
     *                                     array(
     *                                         'data'=>'examples/items2.xml',
     *                                         'outputfile'=>'t2'
     *                                     )
     *                                 );
     *     $options['outputfolder'] = './outputbatch4312/';
     *
     * @access public
     * @return mixed return
     */
    function batchXSL($options = null)
    {
        if (is_null($options)) {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_NOOPTIONS,
                                null, null,
                                ' missing XML data',
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

            if (!$this->setXSL($options['xslt'], XML_XSLT_MODE_FILE)) {
                $this->error = PEAR::raiseError(1002);
                return false;
            }
            $this->initXSL();
        } else {
            $this->error = PEAR::raiseError(null,
                                XML_XSLT_ERROR_XSL_EMPTY,
                                null, null,
                                'missing XSL data in batchXSL',
                                $this->error_class, true);
            return false;
        }

        if (isset($options['xml_datas']) && is_array($options['xml_datas'])) {
            $xml_files = $options['xml_datas'];
            $xml_args  = '';

            foreach ($xml_files as $xml_file => $xml) {
                $mode = $xml['data'][0] == '<'?
                            XML_XSLT_MODE_STRING : XML_XSLT_MODE_FILE;

                if ($xml['data'][0] == '<') {
                    $xmlfile = $this->_saveTempData($xml['data']);
                } else {
                    $xmlfile = $xml['data'];
                }

                exec(XML_XSLT_XSLTPROC_CMD . ' ' . $this->_buildParams() . ' ' .
                        ' -o ' . $dest_dir . $xml['outputfile'] .
                        $this->_arg_xsl . ' ' . $this->_arg_xml,
                        $messages,
                        $return_code);

                if ($return_code == 0) {
                    $error = false;
                } else {
                    $this->error = PEAR::raiseError(null,
                                        XML_XSLT_ERROR_XSLEXEC_ERROR,
                                        null, null,
                                        'Command returned: ' . $return_code.
                                        ' ' . $messages,
                                        $this->error_class, true);

                    $error = true;
                    break;
                }
            }
        }
        $this->free();
        return !$error;
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
    function free()
    {
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
