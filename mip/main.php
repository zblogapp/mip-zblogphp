<?php
if (file_exists('../../../zb_system/function/c_system_base.php')) {
    require '../../../zb_system/function/c_system_base.php';
    require '../../../zb_system/function/c_system_admin.php';
} else { // for debugging, useless
    require '../../../website/zbphp/zb_system/function/c_system_base.php';
    require '../../../website/zbphp/zb_system/function/c_system_admin.php';
}

$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
    $zbp->ShowError(6);
    die();
}
if (!$zbp->CheckPlugin('mip')) {
    $zbp->ShowError(48);
    die();
}

$blogtitle = 'MIP支持插件';

if (count($_POST) > 0) {
    CheckIsRefererValid();
    $zbp->Config('mip')->enable_header_canonical = $_POST['enable_header_canonical'];
    $zbp->Config('mip')->remove_all_plugin_headers = $_POST['remove_all_plugin_headers'];

    mip_initialize_mip_page();
    $zbp->template->BuildTemplate();
    $zbp->SaveConfig('mip');
    $zbp->SetHint('good');
    Redirect('./main.php');
}

require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';

?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu"></div>
  <div id="divMain2">
    <form id="edit" name="edit" method="post" action="#">
        <?php echo '<input type="hidden" name="csrfToken" value="' . $zbp->GetCSRFToken() . '">';?>
        <p>如果您新安装了适配本插件的主题，点击“提交”即可激活。</p>
        <table border="1" class="tableFull tableBorder tableBorder-thcenter">
        <tr>
            <th class="td25"></th>
            <th>设置</th>
        </tr>
        <tr>
            <td><p><b>· 自动插入 Canonical 标签</b><br/><span class="note">如果您当前激活的主题<?php echo $zbp->theme; ?>不支持 MIP，请打开它。</span></p></td>
            <td><p><input id="enable_header_canonical" name="enable_header_canonical" class="checkbox" type="text" value='<?php echo $zbp->Config('mip')->enable_header_canonical ?>' /></p></td>
        </tr>
        <tr>
            <td><p><b>· MIP模式下禁用插件</b><br/><span class="note">如不禁用，可能导致MIP规则校验出现问题。</span></p></td>
            <td><p><input id="remove_all_plugin_headers" name="remove_all_plugin_headers" class="checkbox" type="text" value='<?php echo $zbp->Config('mip')->remove_all_plugin_headers ?>' /></p></td>
        </tr>
      </table>
      <hr/>
      <p>
        <input type="submit" class="button" value="<?php echo $lang['msg']['submit'] ?>" />
      </p>
    </form>
    <script type="text/javascript">ActiveLeftMenu("aPluginMng");</script>
    <script type="text/javascript">AddHeaderIcon("<?php echo $bloghost . 'zb_users/plugin/mip/logo.png'; ?>");</script>
  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';

RunTime();
?>
