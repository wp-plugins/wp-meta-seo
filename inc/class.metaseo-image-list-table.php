<?php
/*
 * Comments to come later
 *
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( !class_exists( 'ImageHelper' ) ) {
    require_once( 'class.image-helper.php' );
}

class MetaSeo_Image_List_Table extends WP_List_Table {
	
    function __construct() {
        parent::__construct(array(
            'singular' => 'metaseo_image',
            'plural' => 'metaseo_images',
            'ajax' => true
        ));
    }

    function display_tablenav($which) {
       ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

            <?php if($which=='top'):   ?>
            <input type="hidden" name="page" value="metaseo_image_meta" />
            <div class="alignleft actions bulkactions">
                <?php $this->months_fillter('attachment','sldate','filter_date_action'); ?>
            </div>
            <?php elseif($which=='bottom'):?>
            <input type="hidden" name="page" value="metaseo_image_meta" />
            <div class="alignleft actions bulkactions">
                <?php $this->months_fillter('attachment','sldate1','filter_date_action'); ?>
            </div>
            <?php endif   ?>

            <input type="hidden" name="page" value="metaseo_image_meta" />
            <?php if (!empty($_REQUEST['post_status'])): ?> 
                <input type="hidden" name="post_status" value="<?php echo esc_attr($_REQUEST['post_status']); ?>" />
            <?php endif ?>

            <?php //$this->extra_tablenav($which); ?>
            
			<div style="float:right;margin-left:8px;">
                <input type="number" required min="1" value="<?php echo $this->_pagination_args['per_page'] ?>" maxlength="3" name="metaseo_imgs_per_page" class="metaseo_imgs_per_page screen-per-page" max="999" min="1" step="1">
                <input type="submit" name="btn_perpage" class="button_perpage button" id="button_perpage" value="Apply" >
            </div>
			
            <?php $this->pagination($which); ?>                
            <br class="clear" />
        </div>

        <?php
    }

    function get_views() {
        global $wpdb;


        $status_links = array();

        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        $post_types = "'" . implode("', '", $post_types) . "'";

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $states['trash'] = 'trash';
        $all_states = "'" . implode("', '", $states) . "'";

        $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ($all_states) AND post_type IN ($post_types)");

        $class = empty($_REQUEST['post_status']) ? ' class="current"' : '';
        $status_links['all'] = "<a href='admin.php?page=metaseo_image_meta'$class>" . sprintf(_nx('All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts'), number_format_i18n($total_posts)) . '</a>';

        foreach (get_post_stati(array('show_in_admin_all_list' => true), 'objects') as $status) {

            $status_name = $status->name;

            $total = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ('$status_name') AND post_type IN ($post_types)");

            if ($total == 0) {
                continue;
            }

            if (isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status']) {
                $class = ' class="current"';
            } else {
                $class = '';
            }

            $status_links[$status_name] = "<a href='admin.php?page=metaseo_image_meta&amp;post_status=$status_name'$class>" . sprintf(translate_nooped_plural($status->label_count, $total), number_format_i18n($total)) . '</a>';
        }
        $trashed_posts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status IN ('trash') AND post_type IN ($post_types)");
        $class = ( isset($_REQUEST['post_status']) && 'trash' == $_REQUEST['post_status'] ) ? 'class="current"' : '';
        $status_links['trash'] = "<a href='admin.php?page=metaseo_image_meta&amp;post_status=trash'$class>" . sprintf(_nx('Trash <span class="count">(%s)</span>', 'Trash <span class="count">(%s)</span>', $trashed_posts, 'posts'), number_format_i18n($trashed_posts)) . '</a>';

        return $status_links;
    }

    function extra_tablenav($which) {

        #if ('top' == $which) {
        echo '<div class="alignleft actions">';
        global $wpdb;

        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        $post_types = "'" . implode("', '", $post_types) . "'";

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $states['trash'] = 'trash';
        $all_states = "'" . implode("', '", $states) . "'";

        $query = "SELECT DISTINCT post_type FROM $wpdb->posts WHERE post_status IN ($all_states) AND post_type IN ($post_types) ORDER BY 'post_type' ASC";
        $post_types = $wpdb->get_results($query);

        $selected = !empty($_REQUEST['post_type_filter']) ? $_REQUEST['post_type_filter'] : -1;

        $options = '<option value="-1">Show All Post Types</option>';

        foreach ($post_types as $post_type) {
            $obj = get_post_type_object($post_type->post_type);
            $options .= sprintf('<option value="%2$s" %3$s>%1$s</option>', $obj->labels->name, $post_type->post_type, selected($selected, $post_type->post_type, false));
        }

        echo "</div>";
        #echo "</form>";
        #}
    }

    function get_columns() {
        return $columns = array(
            'col_id' => __('ID', 'wp-meta-seo'),
            'col_image' => __('Image', 'wp-meta-seo'),
            'col_image_name' => __('Name', 'wp-meta-seo'),
            'col_image_info' => __('Optimization Info', 'wp-meta-seo'),
            'col_image_alternative' => __('Alternative text', 'wp-meta-seo'),
            'col_image_title' => __('Title', 'wp-meta-seo'),
            'col_image_legend' => __('Legend', 'wp-meta-seo'),
            'col_image_desc' => __('Description', 'wp-meta-seo'),
        );
    }

    function get_sortable_columns() {
        return $sortable = array(
            'col_image_name' => array('post_name', true),
            'col_image_title' => array('post_title', true),
        );
    }

	
	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) )
			$current_orderby = $_GET['orderby'];
		else
			$current_orderby = '';

		if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] )
			$current_order = 'desc';
		else
			$current_order = 'asc';

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

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

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";
			
			if($column_key === 'col_id'){
				echo "<th scope='col' $id $class $style colspan=\"1\">$column_display_name</th>";
			}
			elseif($column_key === 'col_image_name'){
				echo "<th scope='col' $id $class $style colspan=\"4\">$column_display_name</th>";
			}
			elseif($column_key === 'col_image_info'){
				echo "<th scope='col' $id $class $style colspan=\"5\">$column_display_name</th>";
			}
			else{
				echo "<th scope='col' $id $class $style colspan=\"3\">$column_display_name</th>";
			}
		}
	}
	
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
        
        $where=array();
        $post_type='attachment';
        $where[] = " post_type='$post_type' ";
        $where[] = " ((post_mime_type='image/jpeg') OR (post_mime_type='image/jpg') OR (post_mime_type='image/png') OR (post_mime_type='image/gif')) ";
       
        if (!empty($_REQUEST["search"])) {
            if (!empty($_REQUEST["txtkeyword"])){
                $_REQUEST["txtkeyword"] = stripslashes($_REQUEST["txtkeyword"]);
                $_REQUEST["txtkeyword"] = $wpdb->esc_like( $_REQUEST["txtkeyword"] );
                $where[] = $wpdb->prepare("  (post_title Like %s  or post_name Like %s)",  "%" . $_REQUEST["txtkeyword"] . "%",  "%" . $_REQUEST["txtkeyword"] . "%" );
        
            }
        }
        
        if(!empty($_REQUEST['sldate'])){
            $where[] =$wpdb->prepare("  post_date Like %s","%" .$_REQUEST['sldate']. "%");
        }
                               
        $orderby = !empty($_GET["orderby"]) ? ($_GET["orderby"]) : 'post_name';
        $order = !empty($_GET["order"]) ? ($_GET["order"]) : 'ASC';
        if (!empty($orderby) & !empty($order)) {
            $orderStr =$wpdb->prepare(' ORDER BY %s %s',$orderby,$order);
            $orderStr = str_replace("'","",$orderStr);
        }
		
		
        
        $query = "SELECT ID, post_title as title, post_name as name, post_content as des, post_excerpt as legend, guid, post_type , post_mime_type, post_status, mt.meta_value AS alt
                FROM $wpdb->posts as posts
                LEFT JOIN (SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_wp_attachment_image_alt') mt ON mt.post_id = posts.ID
                WHERE ". implode(" and ", $where) .$orderStr;
        
        $total_items = $wpdb->query($query);
		
		if(!empty($_REQUEST['metaseo_imgs_per_page'])){
			$_per_page = intval($_REQUEST['metaseo_imgs_per_page']);
		}
		else {
			$_per_page = 0;
		}
		$per_page = get_user_option('metaseo_imgs_per_page');                
		if( $per_page !== false) {
			if($_per_page && $_per_page !== $per_page ){
				$per_page = $_per_page;
				update_user_option(get_current_user_id(), 'metaseo_imgs_per_page', $per_page);
			}
		}
		else{
            if($_per_page > 0) { 
                $per_page = $_per_page; 
            }
			else { $per_page = 10; }                        
			add_user_meta(get_current_user_id(), 'metaseo_imgs_per_page', $per_page);
		}

        $paged = !empty($_GET["paged"]) ? ($_GET["paged"]) : '';

        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

        $total_pages = ceil($total_items / $per_page);

        if (!empty($paged) && !empty($per_page)) {
            $offset = ($paged - 1) * $per_page;
            $query .= ' LIMIT ' . (int) $offset . ',' . (int) $per_page;
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'per_page' => $per_page
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $wpdb->get_results($query);
              
    }

    function search_box1( ) {
        if (empty($_REQUEST['txtkeyword']) && !$this->has_items())
            return;
        $txtkeyword = (!empty($_REQUEST['txtkeyword'])) ? urldecode(stripslashes($_REQUEST['txtkeyword']) ) : "";
        if (!empty($_REQUEST['orderby']))
            echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
        if (!empty($_REQUEST['order']))
            echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
        if (!empty($_REQUEST['post_mime_type']))
            echo '<input type="hidden" name="post_mime_type" value="' . esc_attr($_REQUEST['post_mime_type']) . '" />';
        if (!empty($_REQUEST['detached']))
            echo '<input type="hidden" name="detached" value="' . esc_attr($_REQUEST['detached']) . '" />';
        ?>
        <p class="search-box">
            
            <input type="search" id="image-search-input" name="txtkeyword" value="<?php echo esc_attr(stripslashes($txtkeyword)); ?>" />
            <?php submit_button('Search', 'button', 'search', false, array('id' => 'search-submit')); ?>
        </p>
        <?php
    }
    
    
    function months_fillter($post_type, $name,$namebutton) {
        global $wpdb, $wp_locale;
        
        $where = " AND ((post_mime_type='image/jpeg') OR (post_mime_type='image/jpg') OR (post_mime_type='image/png') OR (post_mime_type='image/gif')) ";
        $months = $wpdb->get_results($wpdb->prepare("
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s".$where."   
			ORDER BY post_date DESC 
		", $post_type));

        $months = apply_filters('months_dropdown_results', $months, $post_type);

        $month_count = count($months);

        if (!$month_count || ( 1 == $month_count && 0 == $months[0]->month ))
            return;

        $m = isset($_REQUEST['sldate']) ? $_REQUEST['sldate'] : 0;
        ?>
            <label for="filter-by-date" class="screen-reader-text"><?php _e('Filter by date'); ?></label>
            <select name="<?php echo $name ?>" id="filter-by-date" class="metaseo-filter">
            <option<?php selected($m, 0); ?> value="0"><?php _e('All dates'); ?></option>
        <?php
        foreach ($months as $arc_row) {
            
            if (0 == $arc_row->year) continue;
            $month = zeroise($arc_row->month, 2);
            $year = $arc_row->year;
            printf("<option %s value='%s' >%s</option>\n",
                    selected($m, "$year-$month", false), 
                    esc_attr("$arc_row->year-$month"),
                    sprintf(__('%1$s %2$d'), $wp_locale->get_month($month), $year)
            );
        }
        ?>
            </select>
                        
        <?php
        submit_button(__('Filter'), 'button', $namebutton, false, array('id' => 'image-submit'));
    }

    function display_rows() {
        $url = URL;
        $url = preg_replace('/(^(http|https):\/\/[w]*\.*)/', '', $url);
        $records = $this->items;
        $i = 0;
        $alternate = "";

        list( $columns, $hidden ) = $this->get_column_info();

        if (!empty($records)) {
            foreach ($records as $rec) {
                $alternate = 'alternate' == $alternate ? '' : 'alternate';
                $i++;
                $classes = $alternate;
                $img_meta = get_post_meta($rec->ID, '_wp_attachment_metadata', TRUE);
                 $thumb = wp_get_attachment_image_src($rec->ID, 'thumbnail' );
                 if(!$thumb) {
                    $thumb_url = $rec->guid;
                 }else {
                     $thumb_url = $thumb['0'];                  
                 }
				 
                if (strrpos($img_meta['file'], '/') !== false) {
                    $img_name = substr($img_meta['file'], strrpos($img_meta['file'], '/') + 1);
                } else {
                    $img_name = $img_meta['file'];
                }
                $type = substr($img_meta['file'], strrpos($img_meta['file'], '.'));
                $img_name = str_replace($type, '', $img_name);
                
                $upload_dir = wp_upload_dir();
                $img_path = $upload_dir['basedir'] . '/' . $img_meta['file'];
                if (is_readable($img_path)) {
                    //Get image attributes including width and height
                    list($img_width, $img_height,$img_type) = getimagesize($img_path);
                    //Get image size
                    if (($size = filesize($img_path) / 1024) > 1024) {
                        $img_size = ($size / 1024);
                        $img_sizes = ' MB';
                    } else {
                        $img_size = ($size);
                        $img_sizes = ' KB';
                    }
                    $img_size=round($img_size,1);
                    //Get the date that image was uploaded
                    $img_date = get_the_date("", $rec->ID);
                }

                echo '<tr id="record_' . $rec->ID . '" class="' . $classes . '" >';
                
                foreach ($columns as $column_name => $column_display_name) {

                    $class = sprintf('class="%1$s column-%1$s"', $column_name);
                    $style = "";

                    if (in_array($column_name, $hidden)) {
                        $style = ' style="display:none;"';
                    }

                    $attributes = $class . $style;

                    switch ($column_name) {
                        case 'col_id':
                            echo '<td class="col_id" colspan="1">';
                            echo $i;
                            echo '</td>';
                            break;

                        case 'col_image':
                            $img = sprintf("<img src='$thumb_url' width='100px' height='100px' class=\"metaseo-image\"  data-name=\"$img_name$type\" data-img-post-id=\"$rec->ID\" />");
                            
                            echo sprintf('<td %2$s colspan="3">%1$s</td>', $img, $attributes);
                            break;

                        case 'col_image_name':
                            $info = '<div class="img-name-wrapper">';
                            $info .= '<input type="text" name="name_image['.$rec->ID.']" class="metaseo-img-meta metaseo-img-name" data-meta-type="change_image_name" id="img-name-'.$rec->ID.'" data-post-id="'.$rec->ID.'" size="12" value="'.$img_name.'" data-extension="'.$type.'" /><span class="img_type">'.$type.'</span>';
                            #$info .= '<p class="savedInfo" style="display:none;"></p>';
                            $info .= '<p>size: ' . $img_size . $img_sizes. '</p>';
                            $info .= '<p>' . $img_width . 'x' . $img_height . '</p>';
                            $info .= '<p>' . $img_date . '</p>';
							$info .= '<span class="saved-info" style="position:relative">
							<span class="meta-update" style="position:absolute"></span>
							</span>';
							$info .= '</div>';
                            echo sprintf('<td %2$s colspan="4">%1$s</td>', $info, $attributes);
                            break;

                        case 'col_image_info':
                          	$icon = '<img src= "' . WPMETASEO_PLUGIN_URL . 'img/view.png" />';
							$icon = '';
							$info = "<div class=\"opt-info\" id=\"opt-info-$rec->ID\"></div>";
							$info .= '<span class="metaseo-loading"></span>';
                            $info .= '
							<div class="popup-bg"></div>
							<div class="popup post-list">
									<span class="popup-close" title="Close">x</span>
                                    <div class="popup-content"></div>
                             </div>       
				';
                                  
                            echo sprintf('<td %2$s colspan="5" style="position:relative">%1$s</td>', $info, $attributes);
                            break;

                        case 'col_image_alternative':
                            $input = ("<input name='img_alternative[$rec->ID]' class='metaseo-img-meta' data-meta-type='alt_text' id='img-alt-$rec->ID' data-post-id='$rec->ID' size='13' value='".  esc_attr($rec->alt)."' type='text'>");
							$input .= ('<span class="saved-info" style="position:relative">
							<span class="meta-update" style="position:absolute"></span>
							</span>');
                            echo sprintf('<td %2$s colspan="3">%1$s</td>', $input, $attributes);
                            break;

                        case 'col_image_title':
                            $input = ("<input name='img_title[$rec->ID]' class='metaseo-img-meta' data-meta-type='image_title' id='img-title-$rec->ID' data-post-id='$rec->ID' size='13' value='".esc_attr($rec->title)."' type='text'>");
                            $input .= ('<span class="saved-info" style="position:relative">
							<span class="meta-update" style="position:absolute"></span>
							</span>');
                            echo sprintf('<td %2$s colspan="3">%1$s</td>', $input, $attributes);
                            break;

                        case 'col_image_legend':
                            $input = ("<input name='img_legend[$rec->ID]' class='metaseo-img-meta' data-meta-type='image_caption' id='img-legend-$rec->ID' data-post-id='$rec->ID' value='".esc_attr($rec->legend)."' size='13' type='text'>");
							$input .= ('<span class="saved-info" style="position:relative">
							<span class="meta-update" style="position:absolute"></span>
							</span>');
                            echo sprintf('<td %2$s colspan="3">%1$s</td>', $input, $attributes);
                            break;

                        case 'col_image_desc':
                            $input = ("<input name='img_desc[$rec->ID]' class='metaseo-img-meta' data-meta-type='image_description' id='img-desc-$rec->ID' data-post-id='$rec->ID' size='30' value='".esc_attr($rec->des)."' type='text'>");
							$input .= ('<span class="saved-info" style="position:relative">
							<span class="meta-update" style="position:absolute"></span>
							</span>');
                            echo sprintf('<td %2$s colspan="3">%1$s</td>', $input, $attributes);
                            break;
                    }
                    
                }

                echo '</tr>';
            }
        }
    }
	
	public static function add_more_attachment_sizes_js($response, $attachment){
		$metaseo_imgs_sizes = get_post_meta($attachment->ID, '_metaseo_sizes_optional', true);
		
		if(!empty($metaseo_imgs_sizes)){
			foreach($metaseo_imgs_sizes as $key => $size){
				$response['sizes'][$key] = $size;
			}
		}
		
		return $response;
	}
	
	public static function add_more_attachment_sizes_choose($sizes){
		global $wpdb;
		$query = "SELECT `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = '_metaseo_sizes_optional' AND `meta_value` <> ''";
		
		$metaseo_imgs_sizes = $wpdb->get_results($query);
		if(!empty($metaseo_imgs_sizes)){
			$_sizes = array();
			foreach($metaseo_imgs_sizes as $metaseo_img_sizes){
				$metaseo_img_sizes = @unserialize($metaseo_img_sizes->meta_value);
				if(!empty($metaseo_img_sizes)){
					foreach($metaseo_img_sizes as $key => $size){
						add_image_size($key, $size['width'], $size['height'], false);
					}
				}
			}
			
		}
		
		$new_sizes = array();
	
		$added_sizes = get_intermediate_image_sizes();
	
		// $added_sizes is an indexed array, therefore need to convert it
		// to associative array, using $value for $key and $value
		foreach( $added_sizes as $value) {
			if(strpos($value, '-metaseo') !== false){
				$_value = substr($value, 0, strrpos($value, '-metaseo'));
			}else{
				$_value = $value;
			}
			$new_sizes[$value] = ucwords(str_replace(array('-','_'), ' &ndash; ', $_value));
		}
		
		// This preserves the labels in $sizes, and merges the two arrays
		$new_sizes = array_merge( $new_sizes, $sizes );
		
		return $new_sizes;
	}
	
	private static function display_fix_metas_list($img_post_id, $posts, $meta_counter, $p, $im){
		if($meta_counter){
			$header = __('We found ' . $meta_counter . $im . $p . 'which needed to add or change meta information', 'wp-meta-seo');
		}else{
			$header = __('We found 0 image which needed to add or change meta information', 'wp-meta-seo');
		}
		
		//Get default meta information of the image
		$img_post = get_post($img_post_id);
		$alt = get_post_meta($img_post_id, '_wp_attachment_image_alt', true);
		$title = $img_post->post_title;
	?>
		<h3 class="content-header"><?php echo $header ?></h3>
		<div class="content-box">
			<table class="wp-list-table widefat fixed posts">
				<thead></thead>
				<tbody>
					<?php $alternate = '';?>
					<?php if(count($posts) < 1): ?>
					<tr><td colspan="10" style="height:95%"><?php echo __('This image has still not been inserted in any post!', 'wp-meta-seo') ?></td></tr>
					<?php else: ?>
					<tr class="metaseo-border-bottom">
					<td colspan="1">ID</td>
					<td colspan="2">Title</td>
					<td colspan="2">Image</td>
					<td colspan="5">Image Meta</td>
					</tr>
					<?php foreach($posts as $post): ?>
						<?php foreach($post['meta'] as $k => $meta): ?>
						<?php $alternate = 'alternate' == $alternate ? '' : 'alternate'; 
							  $file_name = substr($meta['img_src'], strrpos($meta['img_src'], '/')+1);
						?>
						<tr class="<?php echo $alternate ?>">
							<td colspan="1"><?php echo $post['ID'] ?></td>
							<td colspan="2">
								<p><?php echo $post['title'] ?></p>
							</td>
							<td colspan="2">
								<div class="metaseo-img-wrapper">
									<img src="<?php echo $meta['img_src'] ?>" />
								</div>
							</td>
							<td colspan="5">
								<?php foreach($meta['type'] as $type => $value): ?>
								<div class="metaseo-img-wrapper">
									<?php 
										$specialChr = array('"', '\'');
										foreach($specialChr as $chr){
											$value = str_replace($chr, htmlentities2($chr), $value);
										}
									?>
									<input type="text" value="<?php echo ($value != '' ? $value : ''); ?>" id="metaseo-img-<?php echo $type. '-' .$post['ID'] ?>" class="metaseo-fix-meta metaseo-img-<?php echo $type ?>" data-meta-key="_metaseo_fix_metas" data-post-id="<?php echo $post['ID'] ?>" data-img-post-id="<?php echo $img_post_id ?>" data-meta-type="<?php echo $type ?>" data-meta-order="<?php echo $k ?>" data-file-name="<?php echo $file_name; ?>" placeholder="<?php echo ($value == '' ? __(ucfirst($type) . ' is empty', 'wp-meta-seo') : '') ?>" onfocus="metaseo_fix_meta(this);" onblur="updateInputBlur(this)" onkeydown="return checkeyCode(event,this)" />
									<span class="meta-update"></span>
									<?php if(trim($$type) != '' && trim($$type) != $value): ?>
                                    <a class="button meta-default" href="#" data-default-value="<?php echo esc_attr($$type) ?>" title="Add to input box" onclick="add_meta_default(this)"> <?php echo '<img src= "' . WPMETASEO_PLUGIN_URL . 'img/img-arrow.png" />' ?> Copy </a>																			
                                                                        <span class="img_seo_type"><?php echo $$type; ?></span>
									<?php endif ?>
								</div>
								<?php endforeach ?>
								<span class="saved-info"></span>
							</td>
						</tr>
						<?php endforeach ?>
					<?php endforeach ?>
					<?php endif ?>
				</tbody>
				<tfoot></tfoot>
			</table>
		</div>
		<div style="padding:5px"></div>		
	<?php
	
	}
	
    private static function display_resize_image_list($img_post_id,$posts, $img_counter, $p, $im){
		
		$header = __('We found ' . $img_counter . $im . $p . ' which needed to optimize', 'wp-meta-seo');
	?>
		<h3 class="content-header"><?php echo $header ?></h3>
		<div class="content-box">
			<table class="wp-list-table widefat fixed posts">
				<thead></thead>
				<tbody>
					<tr class="metaseo-border-bottom">
						<td colspan="1">ID</td>
						<td colspan="3">Title</td>
						<td colspan="4">Current Images</td>
						<td colspan="2" class="metaseo-action">Action</td>
						<td colspan="4">After Replacing</td>
					</tr>
		<?php $alternate ="";
	            foreach($posts as $post): ?>
					<?php $alternate = 'alternate' == $alternate ? '' : 'alternate'; ?>
					<tr class="<?php echo $alternate ?>">
						<td colspan="1"><?php echo $post['ID'] ?></td>
						<td colspan="3">
							<p><?php echo $post['title'] ?></p>
						</td>
						<td colspan="4" style="overflow: hidden;">	
							<?php foreach($post['img_before_optm'] as $key => $src):?>
								<div class="metaseo-img-wrapper">
									<div class="metaseo-img">
										<img  width="<?php echo @$src['width'] ;?>"  height="<?php #echo @$src['height'] ;?>" src="<?php echo $src['src'] ?>" />
										<div class="img-choosen">
											<input type="checkbox" checked="true" class="metaseo-checkin checkin-<?php echo $post['ID'] ?>" value="<?php echo $key ?>" id="checkin-<?php echo $post['ID'].'-'.$key ?>" onclick="uncheck(this)" />
										</div>
										<p class="metaseo-msg"></p>
									</div>
									<div class="dimension">
										Orig. </br>
										<span>Dimensions</span>: <?php echo $src['dimension'] ?></br>
										<span>File size</span>: <?php echo $src['size'].' '.$src['sizes']  ?>
									</div>
								</div>
							<?php endforeach ?>
						</td>
						<td colspan="2" class="metaseo-action">
							<a href="javascript:void(0);" class="metaseo-optimize button" data-img-post-id="<?php echo $img_post_id ?>" data-post-id="<?php echo $post['ID'] ?>" onclick="optimize_imgs(this)"><?php echo __('Replace?', 'wp-meta-seo') ?></a>
							<span class="optimizing spinner"></span>
						</td>
						<td colspan="4">
						<?php foreach($post['img_after_optm'] as $src):?>
							<div class="metaseo-img-wrapper">
								<div class="metaseo-img">
									<img src="<?php echo $src['src'] ?>" />
								</div>
								<div class="dimension">
										OPT </br>
										<span>Dimensions</span>: <?php echo $src['dimension'] ?></br>
										<span>File size</span>: <?php echo $src['size'].' '.$src['sizes']  ?>
								</div>
							</div>
						<?php endforeach ?>
						</td>
					</tr>
					
		<?php endforeach ?>
					<tr class="metaseo-border-top">
						<td colspan="8"></td>
						<td colspan="2">
							<a href="javascript:void(0);" id="metaseo-replace-all" class="button button-primary" onclick="optimize_imgs_group(this)">
								<?php echo __('Replace All') ?>
							</a>
							<span class="optimizing spinner"></span>
						</td>
						<td colspan="4"></td>
					</tr>
				</tbody>
				<tfoot></tfoot>
			</table>
		</div>	
		<div style="padding:5px"></div>
	<?php
	}
	
    public static function optimizeImages(){
	   if(!empty($_POST['post_id']) && !empty($_POST['img_post_id'])){
			$post_id = intval($_POST['post_id']);
			$img_post_id = intval($_POST['img_post_id']);
			if(!empty($_POST['img_exclude'])){
				$img_exclude = $_POST['img_exclude'];
			}
			else{
				$img_exclude = array();
			}
			
			$ret = ImageHelper::_optimizeImages($post_id, $img_post_id, $img_exclude);
		}
		else{
			$ret = array(
				'success' => false,
				'msg' => __('The post is not existed, please choose one another!', 'wp-meta-seo')
			);	
		}
		
		echo json_encode($ret);
		wp_die();
		
	}
	
    public function process_action() {
        global $wpdb;
		$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		$redirect = false;

        if (isset($_POST['search']) and $_POST['search'] === 'Search') {
            $current_url = add_query_arg(array("search" => "Search", "txtkeyword" =>  urlencode(stripslashes($_POST["txtkeyword"]))  ) , $current_url);
            $redirect = true;
        }
        
        if (isset($_POST['filter_date_action']) and $_POST['filter_date_action'] === 'Filter') {
            $current_url = add_query_arg(array("sldate" => $_POST["sldate"]), $current_url);
			$redirect = true;
        }
        
		if(!empty($_POST['paged'])){
			$current_url = add_query_arg(array( "paged" => intval($_POST['paged'])), $current_url);
			$redirect = true;
		}
     	
		if(!empty($_POST['metaseo_imgs_per_page'])){
			$current_url = add_query_arg(array( "metaseo_imgs_per_page" => intval($_POST['metaseo_imgs_per_page'])), $current_url);
			$redirect = true;
		}
		
		if($redirect === true){
			wp_redirect($current_url);
			ob_end_flush();
            exit();
		}
    }
    
	public static function load_posts_callback(){
		global $wpdb;
		$_POST = stripslashes_deep( $_POST );	
		$post_id = intval($_POST['post_id']);
		$img = trim($_POST['img_name']);
		$opt_key = strtolower(trim($_POST['opt_key']));
		$btn = preg_replace('/[_]+/i', ' ',$opt_key);
		$btn = ucwords($btn);
		$gotIt = false;
		
		if($post_id && !empty($img) && !empty($opt_key)){
			$fn = "display_{$opt_key}_list";
			if(method_exists('MetaSeo_Image_List_Table', $fn)){
				//Get list of posts contain this image and its clones
				$posts = ImageHelper::_get_post_list($post_id, $opt_key);
				
				if(count($posts) > 0){
					$img_counter = 0;
					//Now the time to resize the images
					if($opt_key === 'resize_image'){
						$upload_dir = wp_upload_dir();
						$metaseo_sizes_optional = get_post_meta($post_id, '_metaseo_sizes_optional', TRUE);
					if(!is_array($metaseo_sizes_optional)){ $metaseo_sizes_optional = array(); }
						$attachment_meta_data = wp_get_attachment_metadata($post_id);
						
						foreach($posts as &$post){
							foreach($post['img_after_optm'] as &$img){
								$img_counter++;
								$destination = $upload_dir['basedir'] . '/' . $img['path'];
								if(@ImageHelper::IResize($img['src_origin'], $img['width'], $img['height'], $destination)){
																	
									$size = (filesize($destination)/1024);
	                                if($size>1024){
	                                    $size=$size/1024;
	                                    $sizes = 'MB';
	                                }else{
	                                    $sizes = 'KB';
	                                }
									$size=@round($size,1);
									$img['size'] = $size;
	                                $img['sizes'] = $sizes;
								}
								
								$kpart = ImageHelper::IGetPart($img['path']);
						$key = preg_replace('/\-(\d+)x(\d+)$/i', '-metaseo${1}${2}', $kpart->name);
								$key = strtolower($key);
								$file = substr($img['path'], strrpos($img['path'], '/') + 1);
								if(!in_array($key, array_keys($metaseo_sizes_optional))){
									$metaseo_sizes_optional[$key] = array(
										'url' => $img['src'],
										'width' => $img['width'], 
										'height' => $img['height'],
										'orientation' => 'landscape',
										);
								}	
									
								if(!isset($attachment_meta_data['sizes'][$key])){
									$attachment_meta_data['sizes'][$key] = array(
										'file' => $file,
										'width' => $img['width'], 
										'height' => $img['height'],
										'mime-type' => 'image/jpeg'
										);
								}
								
							}
						}
						
						wp_update_attachment_metadata($post_id, $attachment_meta_data);
				update_post_meta($post_id, '_metaseo_sizes_optional', $metaseo_sizes_optional);
					}
					elseif($opt_key === 'fix_metas'){
						$toEdit = false;
						$pIDs = array();
						foreach($posts as $ID => &$post){
							$img_counter += count($post['meta']);
							foreach($post['meta'] as $order => $meta){
								if($meta['type']['alt'] == '' || $meta['type']['title'] == ''){
									$toEdit = true;
								}
								
								if($meta['type']['alt'] != '' && $meta['type']['title'] != ''){
									$pIDs[$ID][] = $order;
								}
							}
						}
						
						if($toEdit === true){
							foreach($pIDs as $ID => $orders){
								foreach($orders as $order){
									unset($posts[$ID]['meta'][$order]);
									if($img_counter > 0){ $img_counter--; }
								}
								
								if(empty($posts[$ID]['meta'])){ unset($posts[$ID]); }
							}
						}
					}
					//-----------------------------
				}
				
			}
		}
		
		//This is a bit crazy but could give more exact information
		
		if(count($posts) > 1){
			$p = ' in ' . count($posts) . ' posts ';
		}
		else{
			$p = '';
		}
		
		if(isset($img_counter) and $img_counter > 1){
			$im = ' images ';
		}
		else{
			if(!isset($img_counter)) { $img_counter = 0; }
			$im = ' image ';
		}

		self::$fn($post_id, $posts, $img_counter, $p, $im);
		wp_die();
		
	}
	
	public static function scan_posts_callback(){
		$_POST = stripslashes_deep( $_POST );
		$imgs = $_POST['imgs'];
		if(!empty($imgs)){
			if(!is_array($imgs)){
				$ret['success'] = false;
				$ret['msg'] = 'No images are available, please check again!';
				return $ret;
			}
			
			$_imgs = array();
			foreach($imgs as $key => $img){
				if(empty($img['img_post_id']) || empty($img['name'])){
					continue;
				}
				
				$_imgs[trim($img['name'])] = $img['img_post_id'];
			}
			unset($imgs);
			
			if(!count($_imgs)){
				$ret['success'] = false;
				$ret['msg'] = 'No images are available, please check again!';
				return $ret;
			}
			
			$msg = ImageHelper::IScanPosts($_imgs, true);
			$ret['msg'] = $msg;
			$ret['success'] = true;
		}
		
		else{
			$ret['success'] = false;
			$ret['msg'] = 'No images are available, please check again!';
		}
		
		echo json_encode($ret);
		wp_die();
	}

	public static function updateMeta_callback(){
		global $wpdb;
		$response = new stdClass();
		$response->updated = false;
		
		if(!empty($_POST['addition']['meta_key'])){
	     self::updateImgMeta_call_back($_POST['addition']);
	    }
	   
	   if(!empty($_POST['meta_type']) and $_POST['meta_type'] == 'change_image_name'){
	   	 self::updateImageName_callback($_POST);
	   }
	   
	   if(!empty($_POST['meta_type']) && !empty($_POST['post_id'])){
	   	 $meta_type = strtolower(trim($_POST['meta_type']));
		 $post_id = intval($_POST['post_id']);
		
		 if(!isset($_POST['meta_value'])){
		 	$meta_value = '';
		 }
		 else{
			$meta_value = trim($_POST['meta_value']);
			if(preg_match('/[<>\/\'\"]+/', $meta_value)){
				$response->updated = false;
				$response->message = 'Should not html tag or special char';
				
				echo json_encode($response);
				wp_die();
			}
		 }
		
		 $label = str_replace('_', ' ', $meta_type);
		 $label = ucfirst($label);
		 
		 $aliases = array('image_title' => 'post_title', 'image_caption' => 'post_excerpt', 'image_description' => 'post_content', 'alt_text' => '_wp_attachment_image_alt');
		 
		 if($meta_type != 'alt_text'){
		 	$data = array('ID' => $post_id, $aliases[$meta_type] => $meta_value);
			 
		 	if(wp_update_post($data)){
		 		$response->updated = true;
				$response->msg = __($label . ' was saved', 'wp-meta-seo');
		 	}
		 }
		 else{
		 	update_post_meta($post_id, $aliases[$meta_type], $meta_value);
			$response->updated = true;
			$response->msg = __($label . ' was saved', 'wp-meta-seo');
		 }
		 
	   }
		else{
            $response->msg = __('There is a problem when update image meta!', 'wp-meta-seo');
		}
	   
	    echo json_encode($response);
        wp_die();
	}
    
	public static function updateImageName_callback() {

        global $wpdb;
        $postID = (int) $_POST['post_id'];
        $name = trim($_POST['meta_value']);
        $iname = preg_replace('/(\s{1,})/', '-', $name);
        $img_meta = get_post_meta($postID, '_wp_attachment_metadata', TRUE);
        $linkold = $img_meta['file'];
        $response = new stdClass();
        $response->updated = FALSE;
        $response->msg = __('There is a problem when update image name', 'wp-meta-seo');

        $upload_dirs = wp_upload_dir();
        $upload_dir = $upload_dirs['basedir'];
		$oldpart = ImageHelper::IGetPart($linkold);
		$old_name = $oldpart->name;
		
        if ($name !== "") {
            if (file_exists($upload_dir . "/" . $linkold)) {
                $newFileName = $oldpart->base_path . $iname . $oldpart->ext;
                #if ((!file_exists($upload_dir . "/" . $newFileName)) && $check==0) {
                 if(!file_exists($upload_dir . "/" . $newFileName)){
                    if (rename($upload_dir . "/" . $linkold, $upload_dir . "/" . $newFileName)) {
						$post_title = get_the_title($postID);
                        $data_post = array('ID' => $postID, 'post_name' => $name);
                        //if (wp_update_post($data_post)) {
                            $where = array('ID' => $postID);
                            $guid = $upload_dirs['baseurl'] . "/" . $newFileName;
                            if(!$post_title){
                            	$id = $wpdb->update($wpdb->posts, array('guid' => $guid, 'post_title' => $name, 'post_name' => strtolower($iname)), $where);
                            }
							else{
								$id = $wpdb->update($wpdb->posts, array('guid' => $guid), $where);
							}
							
                            if($id){
                            $attached_metadata = get_post_meta($postID, "_wp_attachment_metadata", true);
                            $attached_metadata["file"] = $newFileName;
							
							$images_to_rename = array($oldpart->name . $oldpart->ext => $iname . $oldpart->ext);
							$old_path = $upload_dir . "/" . $linkold;

							foreach($attached_metadata['sizes'] as &$clone){
								$clone_file_new = ImageHelper::IReplace($iname, $clone['file']);
								$clone_path = $upload_dir . '/' . $oldpart->base_path . $clone['file'];
								$clone_path_new = $upload_dir . '/' . $oldpart->base_path . $clone_file_new;
								
								if( @rename($clone_path, $clone_path_new) ){
									$images_to_rename[$clone['file']] = $clone_file_new;
									$clone['file'] = $clone_file_new;
								}
								
							}
							
						/** Update source of this image or its clones in post contains them **/
						$query = "SELECT `ID`,`post_title`,`post_content`,`post_type`,`post_date`
									FROM $wpdb->posts
									WHERE (`post_type` = 'post' or `post_type` = 'page')
									AND `post_content` <> ''
									AND `post_content` LIKE '%<img%>%' 
									ORDER BY ID";
									
							$posts = $wpdb->get_results($query);
							$imgs = array($old_name . $oldpart->ext => $postID);
							$posts_contain_img = array();
							foreach($posts as $post){
								$ifound = ImageHelper::IScan($imgs, $post->post_content);
								if(count($ifound) > 0){
									$posts_contain_img[] = $post->ID;
								}
							}
							
							foreach($posts_contain_img as $id){
								if($post = get_post($id)){
									foreach($images_to_rename as $src_before => $src_after){
										$src_before = '/' . $src_before;
										$src_after = '/' . $src_after;
										$post->post_content = str_replace($src_before, $src_after, $post->post_content);
									}
									
									wp_update_post(array(
													'ID' => $post->ID, 
													'post_content' => $post->post_content)
													);
									unset($post, $posts_contain_img);
									//---------------------------------
											
								}
							}
						/*****************************************************/
						/** Update Image registered to Attachment sizes on Add media page**/
						$sizeOptional = get_post_meta($postID, '_metaseo_sizes_optional', true);
						$newOptional = array();
						if(!empty($sizeOptional) && is_array($sizeOptional)){
							foreach($sizeOptional as $key => $detail){
								$pattern = '/^'.strtolower($old_name).'(-metaseo\d+)$/';
								$key = preg_replace($pattern, strtolower($iname).'${1}', $key);
								$detail['url'] = ImageHelper::IReplace($iname, $detail['url']);
								$newOptional[$key] = $detail;
							}
							
							update_post_meta($postID, '_metaseo_sizes_optional', $newOptional);
							unset($sizeOptional, $newOptional);
						}
						/****************************************************/
							//Need to update optimization info of this image
						ImageHelper::IScanPosts(array($iname.$oldpart->ext => $postID),true);
						
                            update_post_meta($postID, '_wp_attached_file', $newFileName);
                            update_post_meta($postID, '_wp_attachment_metadata', $attached_metadata);
							
                            $response->updated = true;
                            $response->msg = __('Image name was changed', 'wp-meta-seo');
                        } else {
                        	$response->iname = $old_name;
                            $response->msg = __('There is a problem when update image name', 'wp-meta-seo');
                        }
                    }
                } else {

                    $response->msg = __('Name is existing', 'wp-meta-seo');
					$response->iname = $old_name;
                }
            } else {
            	$response->iname = $old_name;
                $response->msg = __('File is not existed', 'wp-meta-seo');
            }
        } else {
        	$response->iname = $old_name;
            $response->msg = __('Should not be empty', 'wp-meta-seo');
        }
        echo json_encode($response);
        wp_die();
    }
	
	public static function updateImgMeta_call_back($_post){
		global $wpdb;
		$response = new stdClass();
		$response->updated = false;
		foreach($_post as $k => $v){
			if(!$v && !in_array($k, array('meta_value', 'meta_order'))){
				$response->msg = __('There is a problem when update image meta!', 'wp-meta-seo') ;
				
				echo json_encode($response);
				wp_die();
			}
		}
		
		$meta_key = strtolower(trim($_post['meta_key']));
		$meta_type = strtolower(trim($_post['meta_type']));
		$meta_value = htmlspecialchars(trim($_post['meta_value']));
		$meta_order = intval($_post['meta_order']);
		$img_post_id = intval($_post['img_post_id']);
		$post_id = intval($_post['post_id']);
		
		$meta = get_post_meta($img_post_id, $meta_key, true);
		//Update new value for meta info of this image in wp_postmeta
		$meta[$post_id]['meta'][$meta_order]['type'][$meta_type] = metaseo_utf8($meta_value);
		update_post_meta($img_post_id, $meta_key, $meta);
		$meta = get_post_meta($img_post_id, $meta_key, true);
		
		//Then we must update this meta info in the appropriate post content
		if(!$post = get_post($post_id))
		{
			$response->msg = __('The post has been deleted before, please check again!', 'wp-meta-seo');
		}
		else{
			if($post->post_content !== ''){
				//Split content part that do not contain img tag
				$post_content_split = preg_split('/<img [^<>]+ \/>/i',  $post->post_content);
				//Get all img tag from the content
				preg_match_all('/<img [^<>]+ \/>/i', $post->post_content, $matches);
				$img_tags = $matches[0];
				
				if(isset($img_tags[$meta_order])){
					//&& strpos($img_tags[$meta_order], $img_src)){
					$pattern = '/'. $meta_type .'\s*?\=?\"[^\"]*\"/i';
					$replacement = $meta_type .'="'. $meta_value .'"';
					if(!preg_match($pattern, $img_tags[$meta_order], $match)){
						$pattern = '/\/>/i';
						$replacement = $meta_type .'="'. $meta_value .'" />';
					}
					
					$img_tags[$meta_order] = preg_replace($pattern, $replacement, $img_tags[$meta_order]);
					
					$post_content = '';
					foreach($post_content_split as $key => $split){
						if(isset($img_tags[$key])){
							$img_tag = $img_tags[$key];
						}
						else{
							$img_tag = '';
						}
						
						$post_content .= $split . $img_tag; 
					}
					
					//Update content of this post.
					if(!wp_update_post(array('ID' => $post->ID, 'post_content' => $post_content))){
						$response->msg = __('The post haven\'t been updated, please check again!', 'wp-meta-seo');
					}
					else{
						$response->updated = true;
						$response->msg = __(ucfirst($meta_type) . ' was saved','wp-meta-seo') ;
					}
				}
				else{
					$response->msg = __('This image has been removed from the post, please check again!', 'wp-meta-seo');
				}
			}
			else{
				$response->msg = __('Content of the post is empty, please check again', 'wp-meta-seo!');
			}
		}
		
		
		echo json_encode($response);
		wp_die();
	}
	        
}