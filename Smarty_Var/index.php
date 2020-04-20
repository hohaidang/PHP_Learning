<?php
require_once ('../../smarty/libs/Smarty.class.php');

$smarty = new Smarty();
$tpl = new SMTemplate();
$smarty->template_dir = 'views';
$smarty->compile_dir = 'tmp';

$smarty->assign('foo', 5);

$smarty->display('index.tpl');


