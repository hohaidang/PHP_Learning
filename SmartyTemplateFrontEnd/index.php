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
$smarty->assign('name', 'geogre smit');
$smarty->assign('address', '45th & Harris');

$smarty->assign('myOptions', array(
    1800 => 'Joe Schmoe',
    9904 => 'Jack Smith',
    2003 => 'Char'
));
$smarty->assign('mySelect', 9904);


$smarty->display('index.tpl');