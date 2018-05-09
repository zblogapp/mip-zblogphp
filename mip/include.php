<?php
#注册插件
RegisterPlugin("mip", "ActivePlugin_mip");
DefinePluginFilter('Filter_Plugin_MIP_Template');
DefinePluginFilter('Filter_Plugin_MIP_ViewIndex_Begin');

/**
 * 是否允许内嵌的MIP主题运行
 * 当检测到目前的主题启用了mip_active函数，则自动关闭此功能
 */
$mip_allow_self_theme_start = true;
/**
 * 当前运行模式是否为内嵌的MIP主题
 */
$mip_in_self_theme_mode = false;
/**
 * 当前是否运行于MIP模式下
 */
$mip_start = false;

function ActivePlugin_mip() {
  global $zbp;
  Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'mip_Index_Begin_For_API');
  Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'mip_Index_Begin_For_Switch_To_MIP');
  Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'mip_Index_Begin_For_Header');
  Add_Filter_Plugin('Filter_Plugin_Post_Call', 'mip_Call_Get_MIP_URL');
}
function InstallPlugin_mip() {
  global $zbp;
  $zbp->Config('mip')->enable_header_canonical = 1;
  $zbp->Config('mip')->remove_all_plugin_headers = 1;
  $zbp->SaveConfig('mip');
  mip_initialize_mip_page();
  $zbp->template->BuildTemplate();
}
function UninstallPlugin_mip() {}

/**
 * MIP功能激活函数
 * 进入此函数，即代表目前启用了MIP主题
 */
function mip_active ($allow_other_mip_template = false) {
  global $zbp, $mip_start;
  $mip_start = true;
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'mip_Zbp_LoadTemplate');
  Add_Filter_Plugin('Filter_Plugin_ViewList_Template', 'mip_ViewList_Template');
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'mip_ViewPost_Template');
  $GLOBALS['mip_allow_self_theme_start'] = $allow_other_mip_template;
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

function mip_get_canonical_html ($url) {
  return '<link rel="canonical" href="' . htmlspecialchars($url) . '" />';
}

function mip_get_miphtml_html ($url) {
  return '<link rel="miphtml" href="' . htmlspecialchars($url) . '" />';
}

function mip_Index_Begin_For_Header () {
  global $zbp, $mip_start, $mip_in_self_theme_mode;
  if ($mip_start) {
    if ($zbp->Config('mip')->remove_all_plugin_headers == '1') {
      $zbp->header = ''; // 此处必须强制清空，以避免其他插件造成的影响
      $zbp->footer = '';
    }
    if ($mip_in_self_theme_mode) {
      $zbp->header .= mip_get_canonical_html(mip_theme_get_original_url($zbp->fullcurrenturl));
    } else {
      if ($zbp->Config('mip')->enable_header_canonical == '1') {
        $zbp->header .= mip_get_canonical_html($zbp->fullcurrenturl);
      }
    }
  } else {
    if ($zbp->Config('mip')->enable_header_canonical == '1') {
      $zbp->header .= mip_get_miphtml_html(mip_theme_get_mip_url($zbp->fullcurrenturl));
    }
  }
}

function mip_Index_Begin_For_Switch_To_MIP() {
  global $zbp, $mip_allow_self_theme_start, $mip_in_self_theme_mode;
  if (!$mip_allow_self_theme_start) return;
  $mip_in_self_theme_mode = true;
  $uri = GetVars('REQUEST_URI', 'SERVER');
  $host = parse_url($zbp->host);
  $checkUri = str_replace($host['path'], '', $uri);
  if (preg_match("/^(index.php\/mip|mip)/", $checkUri)) {
    mip_initialize_mip_page();
  }
}

function mip_initialize_mip_page() {
  global $zbp, $bloghost;
  mip_active(true);
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
  foreach ($GLOBALS['hooks']['Filter_Plugin_MIP_ViewIndex_Begin'] as $fpname => &$fpsignal) {
    $fpname($url);
  }
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
  $copyrights = mip_html_format($template->GetTags('copyright'), $styles);
  $styles = $copyrights['style'];
  $copyright = $copyrights['text'];
  mip_format_sidebars($template, $styles);

  $template->SetTags('mipstyle', stylearray_to_css($styles));
  $template->SetTags('copyright', $copyright);
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
  $copyrights = mip_html_format($template->GetTags('copyright'), $styles);
  $styles = $copyrights['style'];
  $copyright = $copyrights['text'];
  $template->SetTags('mipstyle', stylearray_to_css($styles));
  $template->SetTags('copyright', $copyright);
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
