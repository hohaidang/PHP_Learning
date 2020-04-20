<?php
require('../../config.inc.php');

// Must be included BEFORE common.php
require_once('../../third_party/codeplex/PHPExcel.php');

require_once('common.php');
require_once('displayMgr.php');
require_once('exttable.class.php');

$templateCfg = templateConfiguration();

$smarty = new TLSmarty;

list($tplan_mgr,$args) = initArgsForReports($db);
$metricsMgr = new tlTestPlanMetrics($db);
$tplan_mgr  = &$metricsMgr;

list($gui,$tproject_info,$labels,$cfg) = initializeGui($db,$args,$smarty->getImages(),$tplan_mgr);
$args->cfg = $cfg;
$tpl = $templateCfg->default_template;


$smarty->assign('gui',$gui);
displayReport($templateCfg->template_dir . $tpl, $smarty, FORMAT_HTML);


/**
 *
 *
 */
function checkRights(&$db,&$user,$context = null)
{
    if (is_null($context)) {
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
        'test_result_matrix_filters' => null, 'too_much_data' => null,'too_much_builds' => null,
        'result_on_last_build' => null, 'versionTag' => 'tcversion_indicator') );

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
    $guiObj->mailCfg = buildMailCfg($guiObj);

    return array($guiObj,$tproject_info,$l18n,$cfg);
}
