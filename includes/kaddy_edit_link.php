<?php 
  global $wpdb;
    $table_name = $wpdb->prefix . 'track_back_link'; // do not forget about tables prefix
    $message = '';
    $notice = '';
    // this is default $item which will be used for new records
    $default = array(
        'id' =>isset($_REQUEST['id']) ? sanitize_text_field($_REQUEST['id']) : null,
        'refer_url' => isset($_REQUEST['refer_url']) ? sanitize_text_field($_REQUEST['refer_url']) : '',
        'status' => isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '',
        'redirect_url' => isset($_REQUEST['redirect_url']) ? sanitize_text_field($_REQUEST['redirect_url']) : '',
        'is_active' => 1,
    );
    // here we are verifying does this request is post back and have correct nonce
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, isset($_REQUEST));
          //print_r($item);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = kaddy_backlins_validate_links($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'kaddy_backlins');
                } else {
                    $notice = __('There was an error while saving item', 'kaddy_backlins');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'kaddy_backlins');
                } else {
                    $notice = __('There was an error while updating item', 'kaddy_backlins');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $id = ( isset( $_REQUEST['id'] ) ) ? intval($_REQUEST['id']) : '';
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($id)), ARRAY_A);
             //print_r($item);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'kaddy_backlins');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('kaddy_form_meta_box', 'Add Links', 'kaddy_backlins_form_meta_box_handler', 'edit_link', 'normal', 'default');
    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php _e('Links', 'kaddy_backlins')?> <a class="add-new-h2"
                                    href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=manage_link');?>"><?php _e('back to list', 'kaddy_backlins')?></a>
        </h2>

        <?php if (!empty($notice)): ?>
        <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif;?>
        <?php if (!empty($message)): ?>
        <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif;?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
            <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>
     
            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php /* And here we call our custom meta box */ ?>
                        <?php do_meta_boxes('edit_link', 'normal', $item); ?>
                        <input type="submit" value="<?php _e('Save', 'kaddy_backlins')?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
    </div>