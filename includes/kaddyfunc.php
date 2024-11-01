<?php 
/*
* inclde external files here 
* 
*/
/*
 * Get Backlink List table
*/
if ( !function_exists( 'kaddy_backlink_list' ) ) {
	function kaddy_backlink_list() {
		if ( is_admin() ) {
	      require_once(KADDY_BACKLINK_PATH.'includes/kaddy_backlink_list.php');
	    } 
	}
}
/*
 * Edit backlink list 
*/
if ( !function_exists( 'kaddy_edit_linkdetail' ) ) {
	function kaddy_edit_linkdetail(){
	   if ( is_admin() ) {
	    require_once(KADDY_BACKLINK_PATH.'includes/kaddy_edit_link.php');
	  }
	}
}

function kaddy_backlins_validate_links($item){ 
    $messages = array();
    if (empty($item['refer_url'])) $messages[] = __('Refer url is required', 'kaddy_backlins');
    if (empty($item['status'])) $messages[] = __('Status is required', 'kaddy_backlins');
    if (empty($item['redirect_url'])) $messages[] = __('Redirect Url is required', 'kaddy_backlins');
    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

function kaddy_backlins_form_meta_box_handler($item){
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="refer_url"><?php _e('Refer Url', 'kaddy_backlins')?></label>
        </th>
        <td>
            <input id="refer_url" name="refer_url" type="text" style="width: 95%" value="<?php echo esc_attr($item['refer_url'])?>"
                   size="50" class="code" placeholder="<?php _e('Refer Url', 'kaddy_backlins')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="status"><?php _e('Status', 'kaddy_backlins')?></label>
        </th>
        <td>
              <select name="status" id="status" class="statusbox">
              <option value="301" <?php if($item['status'] == '301'){echo 'selected="selected"';}?>>301 Redirect</option>
              <option value="404" <?php if($item['status'] == '404'){echo 'selected="selected"';}?>>404 Redirect</option>
               <option value="custom" <?php if($item['status'] == 'custom'){echo 'selected="selected"';}?>>Custom Page</option></select>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="redirect_url"><?php _e('Redirect URL', 'kaddy_backlins')?></label>
        </th>
        <td>
            <input id="redirect_url" name="redirect_url" type="text" style="width: 95%" value="<?php echo esc_attr($item['redirect_url'])?>" placeholder="<?php _e('Redirect URL', 'kaddy_backlins')?>" required>
        </td>
    </tr>
    </tbody>
</table>
<?php
}
