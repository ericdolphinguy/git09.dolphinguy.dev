<?php
require_once  dirname(__FILE__) . '/../../../utils/Helper.php';
require_once ABSPATH . 'wp-admin/includes/template.php';
require_once( ABSPATH . 'wp-admin/includes/screen.php' );
require_once 'ListTable.php';

class Booki_ULList extends Booki_ListTable {
	public $uniqueNamespace = '';
	public $pagedKey;
	public $orderByKey;
	public $orderKey;
	protected $fullPager = true;
	function __construct( $args = array(), $fullPager = true ) {
		$this->pagedKey = $this->uniqueNamespace . 'paged';
		$this->orderByKey = $this->uniqueNamespace . 'orderby';
		$this->orderKey = $this->uniqueNamespace . 'order';
		$this->fullPager = $fullPager;
		//All overrides necessary for adding a unique namespace.
		//Namespacing allows us to use more than one list in a single page.
		parent::__construct($args);
	}
	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function display_tablenav( $which ) {
		extract( $this->_pagination_args, EXTR_SKIP );

		if(!isset($total_pages) || (int)$total_pages <= 1){
			return;
		}

		if ( 'top' == $which )
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
?>
	<div class="<?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
<?php
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>
	</div>
	<div class="clearfix"></div>
<?php
	}
	
	/**
	 * Display the pagination.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function pagination( $which ) {
		if ( empty( $this->_pagination_args ) )
			return;

		extract( $this->_pagination_args, EXTR_SKIP );
		
		$output = '';
		if($this->fullPager){
			$output = '<li><span>' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span></li>';
		}
		
		$current = $this->get_pagenum();

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first'), $current_url );

		$page_links = array();
		
		
		$page_links[] = sprintf( "<li><a title='%s' href='%s' class='booki-first-page'>%s</a></li>",
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( $this->pagedKey, $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<li><a title='%s' href='%s' class='booki-prev-page'>%s</a></li>",
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( $this->pagedKey, max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which ){
			$html_current_page = '<span class="pager-indicator-textbox">' . $current . '</span>';
		}else{
			$html_current_page = sprintf( '<input class="form-control pager-indicator-textbox" type="text" name="paged" value="%s" />',
				$current
			);
		}
		
		if($this->fullPager){
			$page_links[] = sprintf('<li><span>%1$s <span class="pager-indicator-text">%2$s</span></span></li>'
					, $html_current_page
					, sprintf( _x( 'of %1$s', 'paging' ), number_format_i18n( $total_pages ) )
			);
		}
		
		$page_links[] = sprintf( "<li><a title='%s' href='%s' class='booki-next-page'>%s</a></li>",
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( $this->pagedKey, min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<li><a title='%s' href='%s' class='booki-last-page'>%s</a></li>",
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( $this->pagedKey, $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = '';
		if ( ! empty( $infinite_scroll ) )
			$pagination_links_class = ' hide-if-js';
		$output .= join( "\n", $page_links );

		$this->_pagination = "<ul class='booki pagination'>$output</ul>";

		echo $this->_pagination;
	}
	
	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	function print_column_headers( $with_id = true ) {

		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$current_url = remove_query_arg( $this->pagedKey, $current_url );

		if ( isset( $_GET[$this->orderByKey] ) )
			$current_orderby = $_GET[$this->orderByKey];
		else
			$current_orderby = '';

		if ( isset( $_GET[$this->orderKey] ) && 'desc' == $_GET[$this->orderKey] )
			$current_order = 'desc';
		else
			$current_order = 'asc';

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			$style = '';
			if ( in_array( $column_key, $hidden ) )
				$style = 'display:none;';

			$style = ' style="' . $style . '"';

			if ( 'cb' == $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}
				$column_display_name = '<a href="' . esc_url( add_query_arg( array( $this->orderByKey=>$orderby, $this->orderKey=>$order ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<th scope='col' $id $class $style>$column_display_name</th>";
		}
	}
	/**
	 * Get the current page number
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return int
	 */
	function get_pagenum() {
		$pagenum = isset( $_REQUEST[$this->pagedKey] ) ? absint( $_REQUEST[$this->pagedKey] ) : 0;

		if( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}
	
	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @param array $args An associative array with information about the pagination
	 * @access protected
	 */
	function set_pagination_args( $args ) {
		$args = wp_parse_args( $args, array(
			'total_items' => 0,
			'total_pages' => 0,
			'per_page' => 0,
		) );

		if ( !$args['total_pages'] && $args['per_page'] > 0 )
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

		// redirect if page number is invalid and headers are not already sent
		if ( ! headers_sent() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
			wp_redirect( add_query_arg( $this->pagedKey, $args['total_pages'] ) );
			exit;
		}

		$this->_pagination_args = $args;
	}
	
		/**
	 * Display the search box.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param string $text The search button text
	 * @param string $input_id The search input id
	 */
	function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST[$this->orderByKey] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST[$this->orderByKey] ) . '" />';
		if ( ! empty( $_REQUEST[$this->orderKey] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST[$this->orderKey] ) . '" />';
?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
	<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
	<?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
</p>
<?php
	}
	
	/**
	 * Generate the <tbody> part of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			list( $columns, $hidden ) = $this->get_column_info();
			echo '<li class="no-items">';
			$this->no_items();
			echo '</li>';
		}
	}

	/**
	 * Generate the table rows
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	function display_rows() {
		foreach ( $this->items as $item )
			$this->single_row( $item );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		$this->single_row_columns( $item );
	}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	function single_row_columns( $item ) {
		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			if ( 'cb' == $column_name ) {
				echo '<li class="check-column">';
				echo $this->column_cb( $item );
				echo '</li>';
			}
			elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<li $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo "</li>";
			}
			else {
				echo "<li $attributes>";
				echo $this->column_default( $item, $column_name );
				echo "</li>";
			}
		}
	}
	
	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function display() {
		extract( $this->_args );

		$this->display_tablenav( 'top' );

?>

	<ul class="<?php echo implode( ' ', $this->get_table_classes() ); ?>" id="the-list"<?php if ( $singular ) echo " data-wp-lists='list:$singular'"; ?>>
		<?php $this->display_rows_or_placeholder(); ?>
	</ul>
<?php
		$this->display_tablenav( 'bottom' );
	}
	
	function get_table_classes() {
		return array( 'booki', 'booki-ul-list');
	}
}
?>