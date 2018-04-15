<?php
#注册插件
RegisterPlugin("mip", "ActivePlugin_mip");
DefinePluginFilter('Filter_Plugin_MIP_Template');
DefinePluginFilter('Filter_Plugin_MIP_ViewIndex_Begin');

$mip_allow_self_theme_start = true;
function ActivePlugin_mip() {
  global $zbp;
  Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'mip_Index_Begin_For_API');
  Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'mip_Index_Begin_For_Switch_To_MIP');
  Add_Filter_Plugin('Filter_Plugin_Post_Call', 'mip_Call_Get_MIP_URL');
  $zbp->header .= '<link rel="canonical" href="' . htmlspecialchars(mip_theme_get_mip_url($zbp->fullcurrenturl)) . '" />';
}
function InstallPlugin_mip() {}
function UninstallPlugin_mip() {}

function mip_active ($allow_other_mip_template = false) {
  global $zbp;
  $zbp->header = ''; // 此处必须强制清空，以避免其他插件造成的影响
  $zbp->footer = '';
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'mip_Zbp_LoadTemplate');
  Add_Filter_Plugin('Filter_Plugin_ViewList_Template', 'mip_ViewList_Template');
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'mip_ViewPost_Template');
  $GLOBALS['mip_allow_self_theme_start'] = $allow_other_mip_template;
  if ($allow_other_mip_template) {
    $zbp->header .= '<link rel="canonical" href="' . htmlspecialchars(mip_theme_get_original_url($zbp->fullcurrenturl)) . '" />';
  } else {
    $zbp->header .= '<link rel="canonical" href="' . htmlspecialchars($zbp->fullcurrenturl) . '" />';
  }
}

function mip_theme_get_original_url ($url) {
  return str_replace('mip/', '', $url);
}

function mip_theme_get_mip_url ($url) {
  global $zbp;
  return str_replace($zbp->host, $zbp->host . 'mip/', $url);
}

function mip_Call_Get_MIP_URL(&$clazz, $method, $args) {
  if ($method === 'MIPUrl') {
    $GLOBALS['hooks']['Filter_Plugin_Post_Call']['mip_Call_Get_MIP_URL'] = PLUGIN_EXITSIGNAL_RETURN;
    return mip_theme_get_mip_url($clazz->Url);
  } else if ($method === 'OrigUrl') {
    $GLOBALS['hooks']['Filter_Plugin_Post_Call']['mip_Call_Get_MIP_URL'] = PLUGIN_EXITSIGNAL_RETURN;
    return mip_theme_get_original_url($clazz->Url);
  }

}

function mip_Index_Begin_For_Switch_To_MIP() {
  global $zbp, $mip_allow_self_theme_start;
  if (!$mip_allow_self_theme_start) return;
  $uri = GetVars('REQUEST_URI', 'SERVER');
  $host = parse_url($zbp->host);
  $checkUri = str_replace($host['path'], '', $uri);
  if (preg_match("/^(index.php\/mip|mip)/", $checkUri)) {
    mip_initialize_mip_page();
  }
}

function mip_initialize_mip_page() {
  global $zbp, $bloghost;
  $bloghost .= 'mip/';
  $zbp->theme = 'mip';
  $zbp->template = $zbp->PrepareTemplate();

  $files = GetFilesInDir($zbp->path . 'zb_system/defend/default/', 'php');
  foreach ($files as $sortname => $fullname) {
    $zbp->template->templates[$sortname] = file_get_contents($fullname);
  }

  $files = GetFilesInDir(dirname(__FILE__) . '/template', 'php');
  foreach ($files as $sortname => $fullname) {
      $zbp->template->templates[$sortname] = file_get_contents($fullname);
  }

  foreach ($GLOBALS['hooks']['Filter_Plugin_MIP_Template'] as $fpname => &$fpsignal) {
    $fpname($zbp->template);
  }

  if (isset($zbp->option['ZC_DEBUG_MODE']) && $zbp->option['ZC_DEBUG_MODE']) {
    $zbp->template->BuildTemplate();
  }

  Add_Filter_Plugin('Filter_Plugin_ViewIndex_Begin', 'mip_ViewIndex_Begin');
}

function mip_viewlist_template_force_set_template(&$template) {
  $template->SetTemplate('index');
}

function mip_viewpost_template_force_set_template(&$template) {
  $template->SetTemplate('single');
}

function mip_ViewIndex_Begin (&$url) {
  global $zbp;
  Add_Filter_Plugin('Filter_Plugin_ViewList_Template', 'mip_viewlist_template_force_set_template');
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'mip_viewpost_template_force_set_template');

  $url = mip_theme_get_original_url($url);
  if (preg_match('/mip.css$/', $url)) {
    header('Content-Type: text/css');
    echo file_get_contents(dirname(__FILE__) . '/template/mip.css');
    exit;
  }
  // Register all static template here
  foreach ($GLOBALS['hooks']['Filter_Plugin_MIP_Template'] as $fpname => &$fpsignal) {
    $fpname($url);
  }

  mip_active(true);
}

function mip_Index_Begin_For_API() {
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
