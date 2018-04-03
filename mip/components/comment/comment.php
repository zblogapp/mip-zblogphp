<?php
function comment_index() {
  global $zbp;
  $zbp->footer .= file_get_contents(dirname(__FILE__) . '/mip-header.php');
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'mip_comment_viewpost_template');
  ViewPost(GetVars('id', 'GET'), '');
  exit;
}

function mip_comment_viewpost_template(&$template) {
  $template->SetTemplate('mip-comment');
}
