<?php
#注册插件
RegisterPlugin("mip","ActivePlugin_mip");

function ActivePlugin_mip() {
}
function InstallPlugin_mip() {}
function UninstallPlugin_mip() {}

function mip_active () {
  Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'mip_CheckIndex');
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'mip_Zbp_LoadTemplate');
  Add_Filter_Plugin('Filter_Plugin_ViewList_Template', 'mip_ViewList_Template');
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'mip_ViewPost_Template');
}

function mip_CheckIndex() {
  $components = array('comment', 'article_viewnum');
  if (!isset($_GET['mip'])) return;
  $component = GetVars('component', 'GET');
  if (!in_array($component, $components)) return;
  require_once(dirname(__FILE__) . '/components/' . $component . '/' . $component . '.php');
  $function_name = $component . '_index';
  $function_name();
}

function mip_Zbp_LoadTemplate(&$templates) {
  $templateList = array(
    'mip-comment' => '/components/comment/mip-comment.php',
    'mip-comment-footer' => '/components/comment/mip-comment-footer.php'
  );
  foreach ($templateList as $key => $template) {
    if (!isset($templates[$key])) {
      $templates[$key] = file_get_contents(dirname(__FILE__) . $template);
    }
  }
}

function mip_ViewList_Template  (&$template) {
  global $zbp;
  $articles = $template->GetTags('articles');
  require_once dirname(__FILE__) . '/html_format.php';
  $styles = array();
  foreach ($articles as $article) {
    $intros = mip_html_format($article->Intro, $styles);
    $contents = mip_html_format($article->Content, $intros['style']);
    $article->Intro = $intros['text'];
    $article->Content = $contents['text'];
    $styles = $contents['style'];
  }
  mip_format_sidebars($template, $styles);
  $template->SetTags('mipstyle', stylearray_to_css($styles));
}



function mip_ViewPost_Template (&$template) {
  global $zbp;
  $article = $template->GetTags('article');
  $styles = array();
  require_once dirname(__FILE__) . '/html_format.php';
  $intros = mip_html_format($article->Intro);
  $contents = mip_html_format($article->Content, $intros['style']);
  $article->Intro = $intros['text'];
  $article->Content = $contents['text'];
  $styles = $contents['style'];
  mip_format_sidebars($template, $styles);
  $template->SetTags('mipstyle', stylearray_to_css($styles));
}

function mip_format_sidebars ($template, &$styles) {
  $sidebarNames = array('sidebar', 'sidebar2', 'sidebar3', 'sidebar4', 'sidebar5');
  foreach ($sidebarNames as $sidebarName) {
    $sidebars = $template->GetTags($sidebarName);
    foreach ($sidebars as $sidebar) {
      $ret = mip_html_format($sidebar->Content);
      $sidebar->Content = $ret['text'];
      array_merge($styles, $ret['style']);
    }
  }
}
