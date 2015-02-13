<?php
require_once  dirname(__FILE__) . '/base/List.php';
class Booki_ImageList extends Booki_List {
  
  function __construct( ){
        global $status, $page;
        $this->uniqueNamespace = 'booki_image_';        
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'image', 
            'plural'    => 'images', 
            'ajax'      => false    
        ) );
    }
    
    function column_default($item, $column_name){
        switch($column_name){
            case 'url':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    function column_url($item){
        
        //Build row actions
        $actions = array(
            'select'      => sprintf('<a class="image-item-selected" href="#%1$s" data-dismiss="modal">Select image</a>', $item['url'])
        );
        
        //Return the thumbnail
        return sprintf('<a class="image-item-selected" href="#%1$s" data-dismiss="modal">
				<img src="%1$s" alt="%2$s" title="Format: %3$s, Name: %2$s" rel="tooltip" class="img-thumbnail" style="width: 140px; height: 140px;">
			</a>
			%4$s',
            /*$1%s*/ $item['url'],
            /*$2%s*/ $item['name'],
            /*$3%s*/ $item['mime_type'],
            /*$2%s*/ $this->row_actions($actions)
        );
    }
    
    function get_columns(){
        $columns = array(
            'url'=>'Image',
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'url'    => array('url', true),//true means its already sorted
        );
        return $sortable_columns;
    }
    
    
    function prepare_items() {
        
        $per_page = 5;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $current_page = $this->get_pagenum();
        
        $orderby = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'mime_type';
        $order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'asc'; 
        
        $result = new WP_Query(
            array(
                'post_mime_type' => array('image/jpg', 'image/jpeg', 'image/png', 'image/gif'),
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'orderby' => $orderby,
                'order' => $order,
                'posts_per_page' => $per_page,
                'paged' => $current_page
            )
        );
        
        $total_pages = $result->max_num_pages;
        $total_items = $result->found_posts;

        $data = array();
        foreach ($result->posts as $post) {
            array_push($data, array( 
                    'url' => wp_get_attachment_url($post->ID),
                    'mime_type' => $post->post_mime_type,
                    'name' => $post->post_name));
        }
        
        $total_items = count($data);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => $total_pages
        ) );
    }
}
?>