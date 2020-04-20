<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource   resultsTCFlat.php
 * @author       Francisco Mancardi <francisco.mancardi@gmail.com>
 *
 * Test Results on simple spreadsheet format
 *
 *
 * @internal revisions
 * @since 1.9.15
 */

require('../../config.inc.php');
require_once('../../third_party/codeplex/PHPExcel.php');   // Must be included BEFORE common.php
require_once('common.php');
require_once('displayMgr.php');
require_once ('cus_common.php');

$timerOn = microtime(true);   // will be used to compute elapsed time
$templateCfg = templateConfiguration();

$smarty = new TLSmarty;
$args = init_args($db);

$metricsMgr = new tlTestPlanMetrics($db);
$tplan_mgr  = &$metricsMgr; // displayMemUsage('START' . __FILE__);

list($gui,$labels,$cfg) = initializeGui($db,$args,$smarty->getImages(),$tplan_mgr);
$args->cfg = $cfg;
$mailCfg = buildMailCfg($gui);


// We have faced a performance block due to an environment with
// 700 Builds and 1300 Test Cases on Test Plan
// This created a block on NOT RUN QUERY, but anyway will produce an enormous and
// unmanageable matrix on screen
//
// New way to process:
// ACTIVE Build Qty > 20 => Ask user to select builds he/she wants to use
// Cell Qty = (ACTIVE Build Qty x Test Cases on Test plan) > 2000 => said user I'm sorry
//
if( ($gui->activeBuildsQty <= $gui->matrixCfg->buildQtyLimit) ||
    $args->do_action == 'result')
{
    setUpBuilds($args,$gui);

    $tpl = $templateCfg->default_template;
    $opt = null;
    $buildSet = array('buildSet' => $args->builds->idSet);

    $opt = array('getExecutionNotes' => true, 'getTester' => true,
        'getUserAssignment' => true, 'output' => 'cumulative',
        'getExecutionTimestamp' => true, 'getExecutionDuration' => true);

    $execStatus = $metricsMgr->getExecStatusMatrixFlat($args->tplan_id,$buildSet,$opt);


    $metrics = $execStatus['metrics'];
    $latestExecution = $execStatus['latestExec'];

    // Every Test suite a row on matrix to display will be created
    // One matrix will be created for every platform that has testcases
    $tcols = array('tsuite', 'tcase','version');
    if($gui->show_platforms)
    {
        $tcols[] = 'platform';
    }
    $tcols[] = 'priority';
    $cols = array_flip($tcols);
    $args->cols = $cols;

    if( !is_null($execStatus['metrics']) )
    {
        buildSpreadsheetData($db,$args,$gui,$execStatus,$labels);
    }
    createSpreadsheet($gui,$args, $db);
    $args->format = FORMAT_XLS;
}
else
{
    // We need to ask user to do a choice
    $tpl = 'resultsTCFlatLauncher.tpl';
    $gui->pageTitle = $labels['test_result_flat_filters'];
    if($gui->matrixCfg->buildQtyLimit > 0)
    {
        $gui->userFeedback = $labels['too_much_data'] . '<br>' .
            sprintf($labels['too_much_builds'],$gui->activeBuildsQty,$gui->matrixCfg->buildQtyLimit);
    }
    $args->format = FORMAT_HTML;
}


$timerOff = microtime(true);
$gui->elapsed_time = round($timerOff - $timerOn,2);

$smarty->assign('gui',$gui);
displayReport($templateCfg->template_dir . $tpl, $smarty, $args->format, $mailCfg);

/**
 *
 *
 */
function init_args(&$dbHandler)
{
    $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,64),
        "tproject_id" => array(tlInputParameter::INT_N),
        "tplan_id" => array(tlInputParameter::INT_N),
        "build_id" => array(tlInputParameter::INT_N),
        "do_action" => array(tlInputParameter::STRING_N,5,10),
        "build_set" => array(tlInputParameter::ARRAY_INT),
        "buildListForExcel" => array(tlInputParameter::STRING_N,0,100),
        "format" => array(tlInputParameter::INT_N));


    $args = new stdClass();
    R_PARAMS($iParams,$args);

    $args->addOpAccess = true;
    if( !is_null($args->apikey) )
    {
        //var_dump($args);
        $cerbero = new stdClass();
        $cerbero->args = new stdClass();
        $cerbero->args->tproject_id = $args->tproject_id;
        $cerbero->args->tplan_id = $args->tplan_id;

        if(strlen($args->apikey) == 32)
        {
            $cerbero->args->getAccessAttr = true;
            $cerbero->method = 'checkRights';
            $cerbero->redirect_target = "../../login.php?note=logout";
            setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
        }
        else
        {
            $args->addOpAccess = false;
            $cerbero->method = null;
            setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
        }
    }
    else
    {
        testlinkInitPage($dbHandler,false,false,"checkRights");
        $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
    }

    if($args->tproject_id <= 0)
    {
        $msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test Project ID ({$args->tproject_id})";
        throw new Exception($msg);
    }

    switch($args->format)
    {
        case FORMAT_XLS:
            if($args->buildListForExcel != '')
            {
                $args->build_set = explode(',',$args->buildListForExcel);
            }
            break;
    }


    $args->user = $_SESSION['currentUser'];
    $args->basehref = $_SESSION['basehref'];

    return $args;
}

/**
 *
 *
 */
function checkRights(&$db,&$user,$context = null)
{
    if(is_null($context))
    {
        $context = new stdClass();
        $context->tproject_id = $context->tplan_id = null;
        $context->getAccessAttr = false;
    }

    $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
    return $check;
}

/**
 *
 *
 */
function buildMailCfg(&$guiObj)
{
    $labels = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));
    $cfg = new stdClass();
    $cfg->cc = '';
    $cfg->subject = $guiObj->title . ' : ' . $labels['testproject'] . ' : ' . $guiObj->tproject_name .
        ' : ' . $labels['testplan'] . ' : ' . $guiObj->tplan_name;

    return $cfg;
}

/**
 *
 *
 */
function initializeGui(&$dbHandler,&$argsObj,$imgSet,&$tplanMgr)
{

    $cfg = array('results' => config_get('results'), 'urgency' => config_get('urgency'),
        'tcase' => config_get('testcase_cfg'));

    $guiObj = new stdClass();
    $guiObj->map_status_css = null;
    $guiObj->title = lang_get('title_test_report_all_builds');
    $guiObj->printDate = '';
    $guiObj->matrix = array();

    $guiObj->platforms = $tplanMgr->getPlatforms($argsObj->tplan_id,array('outputFormat' => 'map'));
    $guiObj->show_platforms = !is_null($guiObj->platforms);

    $guiObj->img = new stdClass();
    $guiObj->img->exec = $imgSet['exec_icon'];
    $guiObj->img->edit = $imgSet['edit_icon'];
    $guiObj->img->history = $imgSet['history_small'];

    $guiObj->tproject_id = $argsObj->tproject_id;
    $guiObj->tplan_id = $argsObj->tplan_id;

    $guiObj->apikey = $argsObj->apikey;


    $tproject_mgr = new testproject($dbHandler);
    $tproject_info = $tproject_mgr->get_by_id($argsObj->tproject_id);
    $argsObj->prefix = $tproject_info['prefix'];
    $argsObj->tcPrefix = $tproject_info['prefix'] . $cfg['tcase']->glue_character;
    $argsObj->tprojectOpt = $tproject_info['opt'];

    $guiObj->options = new stdClass();
    $guiObj->options->testPriorityEnabled = $tproject_info['opt']->testPriorityEnabled;
    unset($tproject_mgr);

    $tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
    $guiObj->tplan_name = $tplan_info['name'];
    $guiObj->tproject_name = $tproject_info['name'];

    $l18n = init_labels(array('design' => null, 'execution' => null, 'history' => 'execution_history',
        'test_result_flat_filters' => null, 'too_much_data' => null,'too_much_builds' => null,
        'result_on_last_build' => null, 'versionTag' => 'tcversion_indicator',
        'execution_type_manual' => null,
        'execution_type_auto' => null) );

    $l18n['not_run']=lang_get($cfg['results']['status_label']['not_run']);


    $guiObj->matrixCfg  = config_get('resultMatrixReport');
    $guiObj->buildInfoSet = $tplanMgr->get_builds($argsObj->tplan_id, testplan::ACTIVE_BUILDS,null,
        array('orderBy' => $guiObj->matrixCfg->buildOrderByClause));
    $guiObj->activeBuildsQty = count($guiObj->buildInfoSet);


    // hmm need to understand if this can be removed
    if ($guiObj->matrixCfg->buildColumns['latestBuildOnLeft'])
    {
        $guiObj->buildInfoSet = array_reverse($guiObj->buildInfoSet);
    }
    // -------------------------------------------------------------------------------


    foreach($cfg['results']['code_status'] as $code => $verbose)
    {
        if( isset($cfg['results']['status_label'][$verbose]))
        {
            $l18n[$code] = lang_get($cfg['results']['status_label'][$verbose]);
            $guiObj->map_status_css[$code] = $cfg['results']['code_status'][$code] . '_text';
        }
    }

    $xxx = config_get('urgency');
    foreach ($xxx['code_label'] as $code => $label)
    {
        $cfg['priority'][$code] = lang_get($label);
    }

    return array($guiObj,$l18n,$cfg);
}

function addNewTester($tester_name, &$dest)
{
    $tester = new cust_Tester();
    $tester->name = $tester_name;
    array_push($dest, $tester);
}

function getRqmtTable($db, $gui)
{
    $rqmt_table = array();
    foreach ($gui->matrix as $suite)
    {
        if(empty($rqmt_table)) {
            array_push($rqmt_table, array("rqmt" => $suite[0], "count" => 1, "id" => null));
            continue;
        }
        $isNewRqmt = 1;
        for ($i = 0; $i < count($rqmt_table); $i++)  {
            if(strcmp($rqmt_table[$i]["rqmt"], $suite[0])  == 0) {
                $isNewRqmt = 0;
                ++$rqmt_table[$i]["count"];
                break;
            }
        }
        if($isNewRqmt){
            array_push($rqmt_table, array("rqmt" => $suite[0], "count" => 1, "id" => null));
        }
    }

    $suite_data = new testsuite($db);
    // Get rqmt ID
    for($i = 0; $i < count($rqmt_table); $i++) {
        $data = $suite_data->get_by_name($rqmt_table[$i]["rqmt"]);
        $rqmt_table[$i]["id"] = $data[0]["id"];
    }
    return $rqmt_table;
}

function findIdx($nameFind, $matrix)
{
    for ($i = 0; $i < count($matrix); $i++)
    {
        if(strcmp($nameFind, $matrix[$i]->name) == 0)
        {
            return $i;
        }
    }
    return -1;
}

function prepareData($gui, $args, $db)
{
    // Query data of tester name
    $tester_table = array();
    foreach ($gui->matrix as $suite) {
        $newTester = 1;
        for ($i = 0; $i < count($tester_table); $i++) {
            // compare tester name
            if (strcmp($suite[5], $tester_table[$i]->name) == 0) {
                // tester existed
                $newTester = 0;
                break;
            }
        }
        if ($newTester) {
            addNewTester($suite[5], $tester_table);
        }
    }

    // Assign testsuite, testcase, to Tester
    foreach ($gui->matrix as $suite) {
        $name_idx = findIdx($suite[5], $tester_table);
        $testCase_temp = new cust_TestCase($suite[1], $suite[10]); // name, exe time
        if(empty($tester_table[$name_idx]->tbl_suite)) {
            // Tester has no suite
            $testSuite_temp = new cust_TestSuite($suite[0]);
            $testSuite_temp->addTestCase($testCase_temp);
            $tester_table[$name_idx]->addTestSuite($testSuite_temp);
        }
        else {
            // Tester has pre-suite
            $suite_idx = findIdx($suite[0], $tester_table[$name_idx]->tbl_suite);
            if ($suite_idx == -1) {
                // New suite
                $testSuite_temp = new cust_TestSuite($suite[0]);
                $testSuite_temp->addTestCase($testCase_temp);
                $tester_table[$name_idx]->addTestSuite($testSuite_temp);
            }
            else {
                // suite existed
                $tester_table[$name_idx]->tbl_suite[$suite_idx]->addTestCase($testCase_temp);
            }
        }
    }
    $rqmt_table = getRqmtTable($db, $gui);
    return array($tester_table, $rqmt_table);
}

function write_excel($gui, $tester_table, $rqmt_table, $args) {
    $lbl = init_labels(array('custom_suite_name' => null,'custom_num_item' => null,
        'custom_total_number_of_testcase' => null,  'custom_average_speed' => null,
        'custom_total_time_hour' => null, 'testproject' => null,
        'generated_by_TestLink_on' => null,'testplan' => null));

    // contribution to have more than 26 columns
    $cellRange = range('A','Z');
    $cellRangeLen = count($cellRange);
    for($idx = 0; $idx < $cellRangeLen; $idx++)
    {
        for($jdx = 0; $jdx < $cellRangeLen; $jdx++)
        {
            $cellRange[] = $cellRange[$idx] . $cellRange[$jdx];
        }
    }

    $styleReportContext = array('font' => array('bold' => true));
    $styleDataHeader = array('font' => array('bold' => true),
        'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
            'vertical' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
        'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array( 'argb' => 'FF9999FF'))
    );

    $dummy = '';
    date_default_timezone_set('Asia/Tokyo');
    $lines2write = array(array($lbl['testproject'],$gui->tproject_name),
        array($lbl['testplan'],$gui->tplan_name),
        array($lbl['generated_by_TestLink_on'],
            localize_dateOrTimeStamp(null,$dummy,'timestamp_format',time())),
        array("Build", $gui->buildInfoSet[$args->build_id]['name'])
        );

    $objPHPExcel = new PHPExcel();
    $cellArea = "A1:";
    foreach($lines2write as $zdx => $fields)
    {
        $cdx = $zdx+1;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A{$cdx}", current($fields))
            ->setCellValue("B{$cdx}", end($fields));
    }
    $cellArea .= "A{$cdx}";
    $objPHPExcel->getActiveSheet()->getStyle($cellArea)->applyFromArray($styleReportContext);


    //  suite
    $objPHPExcel->setActiveSheetIndex(0)->mergeCells("A6:A7")->setCellValue("A6", $lbl['custom_suite_name']);
    $objPHPExcel->getActiveSheet()->getStyle("A6:A7")->applyFromArray($styleDataHeader);
    // Num item
    $cur_row = 6;
    $numOfTester = count($tester_table);
    $col_start_idx = 1;
    $merge_col_start = $cellRange[$col_start_idx];
    $merge_col_end = $cellRange[$col_start_idx++ + $numOfTester];
    $merge_range = "{$merge_col_start}{$cur_row}:{$merge_col_end}{$cur_row}";
    $objPHPExcel->getActiveSheet()->mergeCells($merge_range)->setCellValue("{$merge_col_start}{$cur_row}", $lbl['custom_num_item']);

    // Average Time
    $col_start_idx += $numOfTester;
    $merge_col_start = $cellRange[$col_start_idx];
    $merge_col_end = $cellRange[$col_start_idx++ + $numOfTester];
    $merge_range = "{$merge_col_start}{$cur_row}:{$merge_col_end}{$cur_row}";
    $objPHPExcel->getActiveSheet()->mergeCells($merge_range)->setCellValue("{$merge_col_start}{$cur_row}", $lbl['custom_average_speed']);

    // Total execute time
    $col_start_idx += $numOfTester;
    $merge_col_start = $cellRange[$col_start_idx];
    $merge_col_end = $cellRange[$col_start_idx++ + $numOfTester];
    $merge_range = "{$merge_col_start}{$cur_row}:{$merge_col_end}{$cur_row}";
    $objPHPExcel->getActiveSheet()->mergeCells($merge_range)->setCellValue("{$merge_col_start}{$cur_row}", $lbl['custom_total_time_hour']);

    $objPHPExcel->getActiveSheet()->getStyle("B{$cur_row}:{$merge_col_end}{$cur_row}")->applyFromArray($styleDataHeader);
    $cur_row += 1;
    // Number of testcase -> Tester Name
    $col_start_idx = 1; // start from B
    foreach ($tester_table as $tester)
    {
        $col_cell = $cellRange[$col_start_idx++];
        $objPHPExcel->getActiveSheet()->setCellValue("{$col_cell}{$cur_row}", $tester->name);
    }
    $objPHPExcel->getActiveSheet()->setCellValue("{$cellRange[$col_start_idx++]}{$cur_row}", "Overall");

    // Average execute time -> Tester name
    foreach ($tester_table as $tester)
    {
        $col_cell = $cellRange[$col_start_idx++];
        $objPHPExcel->getActiveSheet()->setCellValue("{$col_cell}{$cur_row}", $tester->name);
    }
    $objPHPExcel->getActiveSheet()->setCellValue("{$cellRange[$col_start_idx++]}{$cur_row}", "Overall");

    // Total execute time -> Tester name
    foreach ($tester_table as $tester)
    {
        $col_cell = $cellRange[$col_start_idx++];
        $objPHPExcel->getActiveSheet()->setCellValue("{$col_cell}{$cur_row}", $tester->name);
    }
    $objPHPExcel->getActiveSheet()->setCellValue("{$cellRange[$col_start_idx++]}{$cur_row}", "Overall");

    $cur_row += 1;

    // Fill row for each Requirement (Test Suite)
    foreach ($rqmt_table as $i => $rqmt)
    {
        $col_start_idx = 1;
        $objPHPExcel->getActiveSheet()->setCellValue("A{$cur_row}", $rqmt["rqmt"]);
        // ---------- Add number of testcase for each Requirement (TestSuite)---------
        $overall = 0;
        foreach ($tester_table as $tester)
        {
            $col = $cellRange[$col_start_idx++];
            $suite_idx = findIdx($rqmt["rqmt"], $tester->tbl_suite);
            if($suite_idx != -1) {
                // current tester has no checking testsuite
                $numOfTestCase = count($tester->tbl_suite[$suite_idx]->tbl_cases);
            }
            else {
                $numOfTestCase = 0;
            }
            $objPHPExcel->getActiveSheet()->setCellValue("{$col}{$cur_row}", $numOfTestCase);
            $overall += $numOfTestCase;
        }
        $objPHPExcel->setActiveSheetIndex()->setCellValue("{$cellRange[$col_start_idx++]}{$cur_row}", $overall);

        // ---------- Add Average time for each Requirement --------------------
        $overall = 0;
        foreach ($tester_table as $tester)
        {
            $average = 0;
            $col = $cellRange[$col_start_idx++];
            $suite_idx = findIdx($rqmt["rqmt"], $tester->tbl_suite);
            if ($suite_idx != -1) {
                $average = $tester->tbl_suite[$suite_idx]->getArverageExecuteTime();
            }
            else {
                $average = 0;
            }
            $objPHPExcel->getActiveSheet()->setCellValue("{$col}{$cur_row}", $average);
            $overall += $average;
        }
        $objPHPExcel->setActiveSheetIndex()->setCellValue("{$cellRange[$col_start_idx++]}{$cur_row}", $overall);

        // ------------Add total time for each requirement -----------
        $overall = 0;
        foreach ($tester_table as $tester)
        {
            $total_time = 0;
            $col = $cellRange[$col_start_idx++];
            $suite_idx = findIdx($rqmt["rqmt"], $tester->tbl_suite);
            if ($suite_idx != -1) {
                $total_time = $tester->tbl_suite[$suite_idx]->getTotalExePerSuite();
            }
            else {
                $total_time = 0;
            }
            $objPHPExcel->getActiveSheet()->setCellValue("{$col}{$cur_row}", $total_time);
            $overall += $total_time;
        }
        $objPHPExcel->setActiveSheetIndex()->setCellValue("{$cellRange[$col_start_idx++]}{$cur_row}", $overall);
        $cur_row += 1;
    }

    $objPHPExcel->getActiveSheet()->setCellValue("A{$cur_row}", $lbl["custom_total_number_of_testcase"]);
    // ------------Write SUM---------
    // Total testcase
    $col_start_idx = 1;
    $overall = 0;
    foreach ($tester_table as $tester)
    {
        // Total testcase
        $col = $cellRange[$col_start_idx++];
        $total = $tester->getTotalTestCase();
        $objPHPExcel->getActiveSheet()->setCellValue("{$col}{$cur_row}", $total);
        $overall += $total;
    }
    $objPHPExcel->getActiveSheet()->setCellValue("{$cellRange[$col_start_idx++]}{$cur_row}", $overall);

    $col_start_idx += $numOfTester + 1; // NOTE -> Remove if Total Average is active

    // Total execute time in hour
    $overall = 0;
    foreach ($tester_table as $tester)
    {
        // Total testcase
        $col = $cellRange[$col_start_idx++];
        $total = $tester->getTotalExePerTester();
        $objPHPExcel->getActiveSheet()->setCellValue("{$col}{$cur_row}", $total);
        $overall += $total;
    }
    $objPHPExcel->getActiveSheet()->setCellValue("{$cellRange[$col_start_idx++]}{$cur_row}", $overall);

    // Final step
    $objPHPExcel->setActiveSheetIndex(0);
    $settings = array();
    $settings['Excel2007'] = array('ext' => '.xlsx',
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $settings['Excel5'] = array('ext' => '.xls',
        'Content-Type' => 'applic   ation/vnd.ms-excel');

    $xlsType = 'Excel5';
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $xlsType);
    $tmpfname = tempnam(config_get('temp_dir'),"result.tmp");
    $objWriter->save($tmpfname);
    return array($tmpfname, $xlsType, $settings);
}

/**
 *
 *
 */
function createSpreadsheet($gui, $args, $db)
{
    // ------------- Collecting Data-------------
    global $tlCfg;
    // Query data of rqmts
    list($tester_table, $rqmt_table) = prepareData($gui, $args, $db);

    // ------------Write Excel---------------
    list($tmpfname, $xlsType, $settings) = write_excel($gui, $tester_table, $rqmt_table, $args);

    // -----------Download feature---------------
    $content = file_get_contents($tmpfname);
    unlink($tmpfname);

    $f2d = 'TestSuite_'. $gui->tproject_name . '_' . date("Y/m/d") . '_' . date("H:i") . $settings[$xlsType]['ext'];
    downloadContentsToFile($content,$f2d,array('Content-Type' =>  $settings[$xlsType]['Content-Type']));
    exit();
}


/**
 *
 */
function setUpBuilds(&$args,&$gui)
{
    $args->builds = new stdClass();

    if( is_null($args->build_set) )
    {
        $args->builds->idSet = null;

        $gui->buildListForExcel = '';
        $gui->filterApplied = false;
        if( !is_null($gui->buildInfoSet) )
        {
            $args->builds->idSet = array_keys($gui->buildInfoSet);
        }
    }
    else
    {
        $args->builds->idSet = array_keys(array_flip($args->build_set));
        $gui->filterApplied = true;
        $gui->buildListForExcel = implode(',',$args->builds->idSet);
    }

    $args->builds->latest = new stdClass();
    $args->builds->latest->id = end($args->builds->idSet);
    $args->builds->latest->name = $gui->buildInfoSet[$args->builds->latest->id]['name'];
}


/**
 *
 *
 */
function buildSpreadsheetData(&$db,&$args,&$gui,&$exec,$labels)
{
    $userSet = getUsersForHtmlOptions($db,null,null,null,null,
        array('userDisplayFormat' => '%first% %last%'));

    $det = array(TESTCASE_EXECUTION_TYPE_MANUAL =>
        $labels['execution_type_manual'],
        TESTCASE_EXECUTION_TYPE_AUTO =>
            $labels['execution_type_auto']);

    $metrics = $exec['metrics'];
    $latestExecution = $exec['latestExec'];
    $cols = $args->cols;

    /*
    tsuite_id 741
    tcase_id  742  => name  TC-1A
    tcversion_id  743
    platform_id 16  => NEED TO DECODE
    build_id  19    => NEED TO DECODE
    version 1
    external_id 1
    executions_id 64
    status  f       => NEED TO DECODE
    execution_notes [empty string]
    tester_id 1     => NEED TO DECODE
    execution_ts  2015-05-23 16:38:22
    execution_duration  NULL
    user_id 1       => NEED TO DECODE
    urg_imp 4       => NEED TO DECODE
    execution_type => NEED TO DECODE
    */

    $loop2do = count($metrics);

    $uk2 = array('user_id','tester_id');

    for($ix=0; $ix < $loop2do; $ix++)
    {
        if($metrics[$ix]['build_id'] != $args->build_id) {
            continue;
        }


        $rows = array();

        $rows[$cols['tsuite']] = $metrics[$ix]['suiteName'];
        $eid = $args->tcPrefix . $metrics[$ix]['external_id'];
        $rows[$cols['tcase']] =
            htmlspecialchars("{$eid}:{$metrics[$ix]['name']}",ENT_QUOTES);

        $rows[$cols['version']] = $metrics[$ix]['version'];

        if ($gui->show_platforms)
        {
            $rows[$cols['platform']] = $gui->platforms[$metrics[$ix]['platform_id']];
        }

        if($gui->options->testPriorityEnabled)
        {
            $rows[$cols['priority']] = $args->cfg['priority'][$metrics[$ix]['priority_level']];
        }

        // build,assigned to,exec result,data,tested by,notes,duration
        $rows[] = $gui->buildInfoSet[$metrics[$ix]['build_id']]['name'];

        $u = "";
        if(isset($userSet,$metrics[$ix]['user_id']))
        {
            $u = $userSet[$metrics[$ix]['user_id']];
        }
        $rows[] = $u;

        // $rows[] = $args->cfg['results']['code_status'][$metrics[$ix]['status']];
        $rows[] = $labels[$metrics[$ix]['status']];
        $rows[] = $metrics[$ix]['execution_ts'];

        $u = "";
        if(isset($userSet,$metrics[$ix]['tester_id']))
        {
            $u = $userSet[$metrics[$ix]['tester_id']];
        }
        $rows[] = $u;

        $rows[] = $metrics[$ix]['execution_notes'];
        $rows[] = $metrics[$ix]['execution_duration'];

        $rows[] =
            isset($det[$metrics[$ix]['exec_type']]) ?
                $det[$metrics[$ix]['exec_type']] : 'not configured';

        $gui->matrix[] = $rows;
    }
}