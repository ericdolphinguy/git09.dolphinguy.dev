<?php
require_once  dirname(__FILE__) . '/base/ULList.php';
require_once  dirname(__FILE__) . '/../../utils/Helper.php';
require_once dirname(__FILE__) . '/../../utils/WPMLHelper.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/ProjectRepository.php';

class Booki_ProjectList extends Booki_ULList {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	public $tags;
	public $isWidget;
	public $projectId;
	public $headingLength;
	public $descriptionLength;
	private $dateFormat;
	private $shorthandDateFormat;
	private $listArgs;
  function __construct($listArgs ){
		$this->tags = $listArgs['tags'];
		$this->isWidget = isset($listArgs['widget']);
		$this->headingLength = isset($listArgs['headingLength']) ? intval($listArgs['headingLength']) : 0;
		$this->descriptionLength = isset($listArgs['descriptionLength']) ? intval($listArgs['descriptionLength']) : 0;
		$this->perPage = intval($listArgs['perPage']);
		$this->projectId = isset($listArgs['projectId']) ? $listArgs['projectId'] : -1;

		$fullPager = $listArgs['fullPager'];
		$this->listArgs = $listArgs;
		$globalSettings = Booki_Helper::globalSettings();
		$this->shorthandDateFormat = $globalSettings->getServerFormatShorthandDate();
		
		$this->dateFormat = get_option('date_format');
		//special treatment for projectlist namespace because multiple instance 
		//can exist in the same page due to it also being utilized as a widget
		//hence we expect tags to be unique if we want to have more 
		//than one widget and shortcode or multiple widgets in the same page.
		$this->uniqueNamespace = 'booki_project_' . implode('_', explode(',', $tags));
		$this->uniqueNamespace .= $isWidget ? 'widget_' : '_';

        parent::__construct( array(
            'singular'  => 'project', 
            'plural'    => 'projects', 
            'ajax'      => false
        ), $fullPager );
    }
	
	function print_column_headers( $with_id = true ) {
		return false;
	}

    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
	
	function column_name($item){
		return $this->get_full_layout($item);
	}
	
	function get_full_layout($item){
		$url = Booki_Helper::getUrl(Booki_PageNames::BOOKING_VIEW);
		$delimiter = Booki_Helper::getUrlDelimiter($url);
		$url .= 
			$delimiter . 'projectid=' . $item['id'] . 
			'&tags=' . $this->tags . 
			'&perpage=' . $this->perPage . 
			'&heading=' . $this->listArgs['heading'] . 
			'&fromlabel=' . $this->listArgs['fromLabel'] . 
			'&tolabel=' . $this->listArgs['toLabel'] . 
			'&enablesearch=' . $this->listArgs['enableSearch'] .
			'&fullpager=' . $this->listArgs['fullPager'] .
			'&enableitemheading=' . $this->listArgs['enableItemHeading'];
			
		$today = new Booki_DateTime();
		$startDate = new Booki_DateTime($item['startDate']);
		$endDate = new Booki_DateTime($item['endDate']);
		$bookingEnded = $today > $endDate;
		
		$result = '<div class="col-lg-12 booki-no-padding">';
		
		if($this->isWidget){
			$result .= '<div>';
		}else{
			$result .= '<div class="pull-left">';
		}
		if(!$bookingEnded){
			$result .= sprintf('<a href="%s">', $url);
		}
		
		if($item['previewUrl']){
			$result .= sprintf('<img class="img-thumbnail booki-project-list-thumbnail" src="%s">', $item['previewUrl']);
		}
		
		if(!$bookingEnded){
			$result .= '</a>';
		}
		$result .= '</div>';
		
		$result .= '<div>';
		
		$result .= sprintf('<div  class="booki-project-list-heading" title="%s">', $item['name']);
		if(!$bookingEnded){
			$result .= sprintf('<a href="%s">', $url);
		}
		$title = $item['name'];
		
		if($this->isWidget){
			if(($this->headingLength > 0 && strlen($title) > $this->headingLength) 
				&& preg_match(sprintf('/^.{1,%d}\b/s', $this->headingLength), $title, $match)){
				$title = $match[0];
				$title .= '...';
			}
		}
		$result .= $title;
		if(!$bookingEnded){
			$result .= '</a>';
		}
		$result .= '</div>';
		
		if($bookingEnded){
			$result .= sprintf('<p class="booki-project-list-closed"><span class="label label-danger">%s</span></p>', __('All bookings exhausted', 'booki'));
		}
		else{
			if(!$this->isWidget){
				$result .= sprintf('<p class="booki-project-list-date" title="%s"><span class="label label-primary">%s - %s</span></p>'
					, __('Booking available from', 'booki')
					, $this->formatLongDate($startDate)
					, $this->formatLongDate($endDate)
				);
				
				
			}
			else{
				$result .= sprintf('<p class="booki-project-list-date" title="%s"><div><span title="%s">%s</span> - <span title="%s">%s</span></div></p>'
					, __('Booking available from', 'booki')
					, __('From', 'booki')
					, $startDate->format($this->shorthandDateFormat)
					, __('To', 'booki')
					, $endDate->format($this->shorthandDateFormat)
				);
			}
		}
		$description = Booki_WPMLHelper::t('description_project' . $item['id'], $item['description']);
		if($this->isWidget){
			$description = strip_tags($description);
			if(($this->descriptionLength > 0 && strlen($description) > $this->descriptionLength) 
				&& preg_match(sprintf('/^.{1,%d}\b/s', $this->descriptionLength), $description, $match)){
				$description = $match[0];
				$description .= '...';
			}
		}
		
		$result .= sprintf('<p class="booki-list-desc">%s</p>', $description);
		$result .= '<div class="clearfix"></div>';
		$result .= '</div>';
		$result .= '</div>';
		return $result;
	}
	
    function get_columns(){
        return array( 'name'=>'');
    }
    
    /**
		@description binds to data
	*/
    function bind() {
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->currentPage = $this->get_pagenum() - 1;

		if( $this->currentPage ){
			$this->currentPage = $this->currentPage * $this->perPage;
		}

        $this->orderBy = 'c.endDate';
        $this->order = 'desc'; 
		$fromDate = null;
		$toDate = null;
		
		if (array_key_exists('controller', $_GET) && $_GET['controller'] == 'booki_searchcontrol'){
			if (array_key_exists('fromDate', $_GET) && array_key_exists('toDate', $_GET)){
				$fromDate = Booki_DateHelper::formattedDateTime($_GET['fromDate']);
				$toDate = Booki_DateHelper::formattedDateTime($_GET['toDate']);
			}
		}

		$projectRepository = new Booki_ProjectRepository();
		
        $result = $projectRepository->readByTag($this->tags, $fromDate, $toDate, $this->projectId, $this->currentPage, $this->perPage, $this->orderBy, $this->order);
		$total_items = (int)$result['total'];
        $this->items = $result['projects'];

		$this->totalPages = 0;
		if($this->totalPages){
			$this->totalPages = ceil($total_items / $this->perPage);
		}
        $this->set_pagination_args( array(
            'total_items' => $total_items
            , 'per_page'    => $this->perPage
            , 'total_pages' => $this->totalPages
        ) );
    }
	
	public function formatLongDate($date){
		return date_i18n('F j, Y', strtotime($date->format(DateTime::ISO8601)));
	}
}
?>