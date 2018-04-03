<?php
function article_viewnum_index () {
  global $zbp;
  $updates = explode(',', trim((string) GetVars('updates', 'GET')));
  $gets = explode(',', trim((string) GetVars('gets', 'GET')));
  $queries = array();
  $updatesForSQL = array();
  $referer = GetVars('HTTP_REFERER', 'SERVER');
  if (strpos($referer, $zbp->host) >= 0) { // 防止二次统计
    $updates = array();
  }

  foreach ($updates as $item) {
    $text = trim($zbp->db->EscapeString($item));
    if ($text !== '') {
      array_push($updatesForSQL, "'" . $text  . "'");
      array_push($queries, $text);
    }
  }
  foreach ($gets as $item) {
    $text = trim($item);
    if ($text !== '') {
      array_push($queries, $text);
    }
  }

  if (isset($zbp->option['ZC_VIEWNUMS_TURNOFF']) && $zbp->option['ZC_VIEWNUMS_TURNOFF'] == false && count($updatesForSQL) > 0) {
    $sql = 'UPDATE ' . $zbp->table['Post'];
    $sql .= ' SET log_ViewNums = log_ViewNums + 1';
    $sql .= ' WHERE log_ID in (' . implode(',', $updatesForSQL) . ')';
    $zbp->db->Update($sql);
  }

  $list = $zbp->GetArticleList('log_ID, log_ViewNums', array(array('IN', 'log_ID', $queries)), null, $zbp->option['ZC_DISPLAY_COUNT'], null, false);
  $ret = array();
  foreach ($list as $item) {
    $ret[$item->ID] = $item->ViewNums;
  }
  header('Access-Control-Allow-Origin: *');
  echo json_encode($ret, JSON_FORCE_OBJECT);
  exit;
}
