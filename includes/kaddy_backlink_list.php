<?php
/*
 * List all back links
*/

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class KADDY_WP_BACKLINK extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'Back Link',
            'plural' => 'Back Links',
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_status($item)
    {
        return '<em>' . $item['status'] . '</em>';
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_refer_url($item){
        $page = ( isset( $_REQUEST['page'] ) ) ? sanitize_text_field($_REQUEST['page']) : '';
        $actions = array(
            'edit' => sprintf('<a href="?page=edit_link&id=%s">%s</a>', $item['id'], __('Edit', 'kaddy_backlins')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>',  $page , intval($item['id']), __('Delete', 'kaddy_backlins')),
        );

        return sprintf('%s %s',
            $item['refer_url'],
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns(){
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'refer_url' => __('Refer Url', 'kaddy_backlins'),
            'redirect_url' => __('Redirect URl', 'kaddy_backlins'),
            'status' => __('Status', 'kaddy_backlins'),
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns(){
        $sortable_columns = array(
            'refer_url' => array('refer_url', true),
            'redirect_url' => array('redirect_url', false),
            'status' => array('status', false),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions(){
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'track_back_link'; // do not forget about tables prefix
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : array();
            if (is_array($ids)) $ids = implode(',', $ids);
            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'track_back_link'; // do not forget about tables prefix
        $per_page = 5; // constant, how much records will be shown per page
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);
        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();
        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}
    global $wpdb;

    $table = new KADDY_WP_BACKLINK();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $deltid = ( isset( $_REQUEST['id'] ) ) ? sanitize_text_field($_REQUEST['id']) : '';
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'kaddy_backlins'), count(esc_attr($deltid))) . '</p></div>';
    }
?> 
<div class="wrap">
           <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php _e('Back Links', 'kaddy_backlins')?> 
               <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=edit_link');?>"><?php _e('Add new', 'kaddy_backlins')?></a>
            </h2>
            <?php echo $message; ?>
            <form id="persons-table" method="GET">
                <?php $page = ( isset( $_REQUEST['page'] ) ) ? sanitize_text_field($_REQUEST['page']) : ''; ?>
                <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
                <?php $table->display() ?>
            </form>
</div>