<?php
/* Smarty version 3.1.34-dev-7, created on 2020-04-27 09:01:11
  from 'E:\Learning_Coding\PHP\SmartyTemplateFrontEnd\views\index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.34-dev-7',
  'unifunc' => 'content_5ea68337265178_31872095',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4da0174ffe7f536419abbbbd6df47d3c0536afa6' => 
    array (
      0 => 'E:\\Learning_Coding\\PHP\\SmartyTemplateFrontEnd\\views\\index.tpl',
      1 => 1587970863,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5ea68337265178_31872095 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'E:\\Learning_Coding\\smarty\\libs\\plugins\\function.cycle.php','function'=>'smarty_function_cycle',),1=>array('file'=>'E:\\Learning_Coding\\smarty\\libs\\plugins\\function.html_options.php','function'=>'smarty_function_html_options',),2=>array('file'=>'E:\\Learning_Coding\\smarty\\libs\\plugins\\function.html_radios.php','function'=>'smarty_function_html_radios',),));
?>
Hello


<table>
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['people']->value, 'p', false, 'k');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['p']->value) {
?>
    <tr style="background: <?php echo smarty_function_cycle(array('values'=>"silver, gray"),$_smarty_tpl);?>
;">
        <td><?php echo $_smarty_tpl->tpl_vars['k']->value;?>
</td>
        <td><?php echo $_smarty_tpl->tpl_vars['p']->value;?>
</td>
    </tr>
<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
</table>

<html>
<head>
    <title>Info</title>
</head>
<body>
<pre>
    User Information:
    Name: <?php echo $_smarty_tpl->tpl_vars['name']->value;?>

    Address: <?php echo $_smarty_tpl->tpl_vars['address']->value;?>

</pre>
</body>
</html>

<?php echo smarty_function_html_options(array('name'=>"Dang",'options'=>$_smarty_tpl->tpl_vars['myOptions']->value,'selected'=>$_smarty_tpl->tpl_vars['mySelect']->value),$_smarty_tpl);?>

<?php echo smarty_function_html_radios(array('name'=>'id','options'=>$_smarty_tpl->tpl_vars['cust_radios']->value,'selected'=>$_smarty_tpl->tpl_vars['customer_id']->value,'separator'=>'<br />'),$_smarty_tpl);?>


<html>
<body>
<!-- regular submit button -->
<form action="filecalled.php">
    <input type="submit" />
</form>
</html>

<html>
<form action = "filecalled.php" method="post">
    <p>
        <label class="input1" for="text1">Familiy name</label>
        <input id="text1" name="text1" value="<?php echo $_smarty_tpl->tpl_vars['varname']->value;?>
" size="60" maxlength="60" class="required" />
    </p>
    <input type=submit />
</form>
</html><?php }
}
