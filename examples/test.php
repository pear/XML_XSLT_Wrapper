<?php
error_reporting(E_ALL);
ini_set('include_path','.:/usr/local/lib/php');
//require_once 'System/Command.php';
require_once 'XSLT/XSLT_Wrapper.php';

// test console

//define('XSLT_XT_CMD','~/xslt_wrapper/xt-20020428a-src/demo/xt.sh');

$xml = '<?xml version="1.0"?>

<items>
  <item>
    this is 1
  </item>
  <item>
    this is 2
  </item>
  <item>
    this is 3
  </item>
  <item>
    this is 4
  </item>
  <item>
    this is 5
  </item>
</items>
';

$xslt   = XSLT_Wrapper::XSLT_Wrapper(XSLT_DOM);
$xslt->setXML($xml,PEAR_XSLT_MODE_STRING);
$xslt->setXSL('table.xsl',PEAR_XSLT_MODE_FILE);
$xslt->initXML();
$xslt->initXSL();
$xslt->setParam('numberofcols',4);
$xslt->process();
$xslt_result = $xslt->ResultDumpOut();

$options = array();
$options['outputfolder'] = './outputbatch2/';
$options['xml'] = $xml;
$options['xslt_files'] = array(
                                array(
                                    'filepath'=>'table.xsl',
                                    'outputfile'=>'t1'
                                ),
                                array(
                                    'filepath'=>'table2.xsl',
                                    'outputfile'=>'t2'
                                )
                            );

$xslt->batchXML($options);

$options['xslt']         = './samples/table.xsl';
$options['xml_datas']   = array(
                                array(
                                    'data'=>'items2.xml',
                                    'outputfile'=>'t1'
                                ),
                                array(
                                    'data'=>'items2.xml',
                                    'outputfile'=>'t2'
                                )
                            );
$options['outputfolder'] = './outputbatch4312/';
$xslt->batchXSL($options);
?>
