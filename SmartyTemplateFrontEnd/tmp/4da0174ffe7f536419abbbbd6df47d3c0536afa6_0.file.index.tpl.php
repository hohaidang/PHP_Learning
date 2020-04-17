<?php
/* Smarty version 3.1.34-dev-7, created on 2020-04-17 07:47:03
  from 'E:\Learning_Coding\PHP\SmartyTemplateFrontEnd\views\index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.34-dev-7',
  'unifunc' => 'content_5e9942d71eec18_94136008',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '4da0174ffe7f536419abbbbd6df47d3c0536afa6' => 
    array (
      0 => 'E:\\Learning_Coding\\PHP\\SmartyTemplateFrontEnd\\views\\index.tpl',
      1 => 1587102411,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5e9942d71eec18_94136008 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'E:\\Learning_Coding\\PHP\\SmartyTemplateFrontEnd\\smarty\\libs\\plugins\\function.cycle.php','function'=>'smarty_function_cycle',),));
?>
Hello

<br />


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
</table><?php }
}
