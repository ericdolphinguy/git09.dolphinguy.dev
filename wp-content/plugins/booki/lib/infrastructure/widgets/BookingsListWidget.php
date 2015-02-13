<?php
require_once dirname(__FILE__) . '/../utils/ThemeHelper.php';
require_once dirname(__FILE__) . '/../utils/WPMLHelper.php';
add_action('widgets_init', 'Booki_InitBookingsListWidget');

function Booki_InitBookingsListWidget(){
	register_widget( 'Booki_BookingsListWidget' );
}
/**
	toDO: add the same label stuff using filters for minicart and some customizations eg: bg color, border
*/
class Booki_BookingsListWidget extends WP_Widget {
	private $options;
	function __construct() {
		parent::__construct( 
			false
			, __('Booki - Bookings listing', 'booki')
			, array('description'=>__('Booki listings --Allows you to list projects based on their tag. If no tag is supplied, all projects are listed.', 'booki'))
			, array('id_base'=>'booki_bookingslistwidget')
		);
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		/* Before widget (defined by themes). */
		echo $before_widget;
		/* Our variables from the widget settings. */
		$this->options = array(
			'widget'=>true
			, 'tags'=>$instance['tags']
			, 'heading'=>Booki_WPMLHelper::t('list_widget_heading', $instance['heading'])
			, 'fromLabel'=>Booki_WPMLHelper::t('list_widget_fromlabel', $instance['fromlabel'])
			, 'toLabel'=>Booki_WPMLHelper::t('list_widget_tolabel', $instance['tolabel'])
			, 'perPage'=>intval($instance['perpage']) ? intval($instance['perpage']) : 5
			, 'headingLength'=>intval($instance['headinglength']) ? intval($instance['headinglength']) : 25
			, 'descriptionLength'=>intval($instance['descriptionlength']) ? intval($instance['descriptionlength']) : 100
			, 'fullPager'=>isset($instance['fullpager']) ? $instance['fullpager'] : false
			, 'enableSearch'=>isset($instance['enablesearch']) ? $instance['enablesearch'] : true
			, 'enableItemHeading'=>isset($instance['enableitemheading']) ? $instance['enableitemheading'] : false
		);
		
		add_filter( 'booki_list', array($this, 'getOptions'));
		$templatePath = Booki_ThemeHelper::getTemplateFilePath('list.php');
		echo '<div class="booki-list-widget">' . @Booki_ThemeHelper::getTemplateRender($templatePath) . '</div>';
		/* After widget (defined by themes). */
		echo $after_widget;
	}
	
	function update( $newInstance, $oldInstance ) {
		$newInstance = (array) $newInstance;
		$instance = array( 'fullpager' => 0, 'enablesearch' => 0, 'enableitemheading'=> 0 );
		foreach ( $instance as $field => $val ) {
			if ( isset($newInstance[$field]) )
				$instance[$field] = 1;
		}

		$instance['tags'] =  $newInstance['tags'];
		$instance['heading'] = Booki_WPMLHelper::register('list_widget_heading', $newInstance['heading']);
		$instance['fromlabel'] = Booki_WPMLHelper::register('list_widget_fromlabel', $newInstance['fromlabel']);
		$instance['tolabel'] = Booki_WPMLHelper::register('list_widget_tolabel', $newInstance['tolabel']);
		$instance['perpage'] = $newInstance['perpage'];
		$instance['headinglength'] = $newInstance['headinglength'];
		$instance['descriptionlength'] = $newInstance['descriptionlength'];
		return $instance;
	}

	function getOptions(){
		return $this->options;
	}
	
	function form( $instance ) {
		$defaults = array(
			'tags'=>''
			, 'heading'=>Booki_WPMLHelper::t('list_widget_heading', __('Find a booking', 'booki'))
			, 'fromlabel'=>Booki_WPMLHelper::t('list_widget_fromlabel', __('Check-in', 'booki'))
			, 'tolabel'=>Booki_WPMLHelper::t('list_widget_tolabel', __('Check-out', 'booki'))
			, 'perpage'=>5
			, 'headinglength'=>25
			, 'descriptionlength'=>100
			, 'fullpager'=>false
			, 'enablesearch'=>true
			, 'enableitemheading'=>false
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		?>

		<p>
			<div>
				<label for="<?php echo $this->get_field_id( 'tags' ); ?>" 
					title="<?php _e('A comma separated list of tags. Leave blank to list all projects.', 'booki'); ?>">
					<?php _e('Tags', 'booki'); ?>
				</label> 
			</div>
			<div>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'tags' ); ?>"  
					name="<?php echo $this->get_field_name( 'tags' ); ?>" 
					value="<?php echo $instance['tags'] ?>" />
			</div>
		</p>
		<p>
			<div>
				<label for="<?php echo $this->get_field_id( 'heading' ); ?>">
					<?php _e('Heading', 'booki'); ?>
				</label> 
			</div>
			<div>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'heading' ); ?>"  
					name="<?php echo $this->get_field_name( 'heading' ); ?>" 
					value="<?php echo $instance['heading']?>" />
			</div>
		</p>
		<p>
			<div>
				<label for="<?php echo $this->get_field_id( 'fromlabel' ); ?>">
					<?php _e('From label', 'booki'); ?>
				</label> 
			</div>
			<div>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'fromlabel' ); ?>"  
					name="<?php echo $this->get_field_name( 'fromlabel' ); ?>" 
					value="<?php echo $instance['fromlabel']?>" />
			</div>
		</p>
		<p>
			<div>
				<label for="<?php echo $this->get_field_id( 'tolabel' ); ?>">
					<?php _e('To label', 'booki'); ?>
				</label> 
			</div>
			<div>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'tolabel' ); ?>"  
				name="<?php echo $this->get_field_name( 'tolabel' ); ?>" 
				value="<?php echo $instance['tolabel']?>" />
			</div>
		</p>
		<p>
			<div>
				<label for="<?php echo $this->get_field_id( 'headinglength' ); ?>" title="<?php _e('Heading field max length', 'booki') ?>">
					<?php _e('Heading max length', 'booki'); ?>
				</label> 
			</div>
			<div>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'headinglength' ); ?>"  
				name="<?php echo $this->get_field_name( 'headinglength' ); ?>" 
				value="<?php echo intval($instance['headinglength']) ? $instance['headinglength'] : 25  ?>" />
			</div>
		</p>
		<p>
			<div>
				<label for="<?php echo $this->get_field_id( 'descriptionlength' ); ?>" title="<?php _e('Description field character length', 'booki') ?>">
					<?php _e('Description max length', 'booki'); ?>
				</label> 
			</div>
			<div>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'descriptionlength' ); ?>"  
				name="<?php echo $this->get_field_name( 'descriptionlength' ); ?>" 
				value="<?php echo intval($instance['descriptionlength']) ? $instance['descriptionlength'] : 100  ?>" />
			</div>
		</p>
		<p>
			<div>
				<label for="<?php echo $this->get_field_id( 'perpage' ); ?>" title="<?php _e('Max records per page', 'booki') ?>">
					<?php _e('Records per page', 'booki'); ?>
				</label> 
			</div>
			<div>
				<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'perpage' ); ?>"  
				name="<?php echo $this->get_field_name( 'perpage' ); ?>" 
				value="<?php echo intval($instance['perpage']) ? $instance['perpage'] : 5  ?>" />
			</div>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'fullpager' ); ?>"  
				name="<?php echo $this->get_field_name( 'fullpager' ); ?>" 
				value="<?php echo $instance['fullpager']?>" <?php checked($instance['fullpager']) ?>  />
				<label for="<?php echo $this->get_field_id( 'fullpager' ); ?>"><?php _e('Show full pager', 'booki'); ?></label> 
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'enablesearch' ); ?>"  
				name="<?php echo $this->get_field_name( 'enablesearch' ); ?>" 
				value="<?php echo $instance['enablesearch']?>" <?php checked($instance['enablesearch']) ?> />
				<label for="<?php echo $this->get_field_id( 'enablesearch' ); ?>"><?php _e('Enable search', 'booki'); ?></label> 
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'enableitemheading' ); ?>"  
				name="<?php echo $this->get_field_name( 'enableitemheading' ); ?>" 
				value="<?php echo $instance['enableitemheading']?>" <?php checked($instance['enableitemheading']) ?> />
				<label for="<?php echo $this->get_field_id( 'enableitemheading' ); ?>"><?php _e('Enable heading in item', 'booki'); ?></label> 
		</p>
	<?php
	}
}