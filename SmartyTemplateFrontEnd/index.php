<?php
require_once ('smarty/libs/Smarty.class.php');

$smarty = new Smarty();
$smarty->template_dir = 'views';
$smarty->compile_dir = 'tmp';

$array = array(
    'Jesse' => 25,
    'Joey' => 26,
    'Jenny' =>  24,
    'Justine' => 23
);

$smarty->assign('people', $array);

$smarty->display('index.tpl');