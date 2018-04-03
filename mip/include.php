<?php
#注册插件
RegisterPlugin("mip","ActivePlugin_mip");

function ActivePlugin_mip() {
  Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'mip_CheckIndex');
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'mip_Zbp_LoadTemplate');
}
function InstallPlugin_mip() {}
function UninstallPlugin_mip() {}

function mip_CheckIndex() {
  $components = array('comment');
  if (!isset($_GET['mip'])) return;
  $component = GetVars('component', 'GET');
  if (!in_array($component, $components)) return;
  require_once(dirname(__FILE__) . '/components/' . $component . '/' . $component . '.php');
  $function_name = $component . '_index';
  $function_name();
}

function mip_Zbp_LoadTemplate(&$templates) {
  $templateList = array(
    'mip-comment' => '/components/comment/mip-comment.php'
  );
  foreach ($templateList as $key => $template) {
    if (!isset($templates[$key])) {
      $templates[$key] = file_get_contents(dirname(__FILE__) . $template);
    }
  }
}
