<?php

//Main plugin functions here
class MetaSeo_Admin {

    function __construct() {
        
        add_action('admin_menu', array($this, 'register_menu_page'));

        /** Load admin js * */
        add_action('admin_enqueue_scripts', array($this, 'loadAdminScripts'));

        /** Load admin css  * */
        add_action('admin_init', array($this, 'addAdminStylesheets'));        
		
		$this->ajaxHandle();
        
        //register ajax update meta handler...
        add_action( 'wp_ajax_updateContentMeta', array($this, 'updateContentMeta_callback') );
        add_action( 'admin_init', array($this, 'stop_heartbeat') , 1 );


    }
    
    function stop_heartbeat() {
         global $pagenow;
         if ( 'post.php' != $pagenow && 'post-new.php' != $pagenow )        
            wp_deregister_script('heartbeat');
    }

    function updateContentMeta_callback() {
         global $wpdb;
         $_POST = stripslashes_deep( $_POST );
		 $response = new stdClass();
		 
		 if( !empty( $_POST['metakey'] ) && !empty( $_POST['postid'] ) && !empty( $_POST['value'] ) );
         $metakey = strtolower(trim($_POST['metakey']));
         $postID =  intval($_POST['postid']);
		 $value = trim($_POST['value']);
         
//		 if(preg_match('/[<>\/\'\"]+/', $value)){
//			$response->updated = false;
//			$response->msg = 'Meta content should not contains html tag or special char';
//			
//			echo json_encode($response);
//			wp_die();
//		}
         
         $response->msg = __('Modification was saved', 'wp-meta-seo') ;
         if($metakey == 'metatitle') {           
             if(!update_post_meta($postID, '_metaseo_metatitle', $value)) {
                 $response->updated = false;
                 $response->msg = __('Meta title was not saved', 'wp-meta-seo') ;
             }
			 else{
			 	$response->updated = true;
                $response->msg = __('Meta title was saved', 'wp-meta-seo') ;
			 }          
		 }
		 
         if($metakey =='metadesc') {
             if(!update_post_meta($postID, '_metaseo_metadesc', $value)) {
                  $response->updated = false;
                  $response->msg = __('Meta description was not saved', 'wp-meta-seo') ;
             }
			 else{
			 	$response->updated = true;
                $response->msg = __('Meta description was saved', 'wp-meta-seo') ;
			 }        
		}
           
         echo json_encode($response);
         wp_die();
    }
      
    /**
     * Loads js/ajax scripts
     * 
     */
    public function loadAdminScripts($hook) {

        wp_enqueue_script('jquery');

        wp_enqueue_script(
                'wpmetaseoAdmin', plugins_url('js/metaseo_admin.js', dirname(__FILE__)), array('jquery'), '0.1', true
        );
        
        wp_enqueue_script('Chart', plugins_url('js/Chart.js', dirname(__FILE__)), array('jquery'), '0.1', true);
        wp_enqueue_script('dashboard-chart', plugins_url('js/dashboard-chart.js', dirname(__FILE__)), array('jquery'), '0.1', true);
        
        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'wpmetaseoAdmin', 'myAjax',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }

    /**
     * Load additional admin stylesheets
     * of jquery-ui
     *
     */
    function addAdminStylesheets() {

        wp_enqueue_style('wpmetaseoAdmin', plugins_url('css/metaseo_admin.css', dirname(__FILE__)));        
        wp_enqueue_style('tooltip-metaimage', plugins_url('/css/tooltip-metaimage.css',dirname(__FILE__)));
        wp_enqueue_style('style', plugins_url('/css/style.css', dirname(__FILE__) ) );
        wp_enqueue_style('chart', plugins_url('/css/chart.css', dirname(__FILE__) ) );
        
    }

    function register_menu_page() {

        // Add main page
        $admin_page = add_menu_page(__('WP Meta SEO:', 'wp-meta-seo') . ' ' . __('Dashboard', 'wp-meta-seo'), __('WP Meta SEO', 'wp-meta-seo'), 'manage_options', 'metaseo_dashboard', array(
            $this,
            'load_page',
                ), plugins_url('/img/icon.png', dirname(__FILE__)) );

        /**
         * Filter: 'metaseo_manage_options_capability' - Allow changing the capability users need to view the settings pages
         *
         * @api string unsigned The capability
         */
        $manage_options_cap = apply_filters('metaseo_manage_options_capability', 'manage_options');

        // Sub menu pages
        $submenu_pages = array(
            array(
                'metaseo_dashboard',
                '',
                __('Content meta', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_content_meta',
                array($this, 'load_page'),
                null,
            ),
            array(
                'metaseo_dashboard',
                '',
                __('Image meta', 'wp-meta-seo'),
                $manage_options_cap,
                'metaseo_image_meta',
                array($this, 'load_page'),
                null,
            ),
        );



        // Allow submenu pages manipulation
        $submenu_pages = apply_filters('metaseo_submenu_pages', $submenu_pages);

        // Loop through submenu pages and add them
        if (count($submenu_pages)) {
            foreach ($submenu_pages as $submenu_page) {

                // Add submenu page
                $admin_page = add_submenu_page($submenu_page[0], $submenu_page[2] . ' - ' . __('WP Meta SEO:', 'wp-meta-seo'), $submenu_page[2], $submenu_page[3], $submenu_page[4], $submenu_page[5]);

                // Check if we need to hook
                if (isset($submenu_page[6]) && null != $submenu_page[6] && is_array($submenu_page[6]) && count($submenu_page[6]) > 0) {
                    foreach ($submenu_page[6] as $submenu_page_action) {
                        add_action('load-' . $admin_page, $submenu_page_action);
                    }
                }
            }
        }

        global $submenu;
        if (isset($submenu['metaseo_dashboard']) && current_user_can($manage_options_cap)) {
            $submenu['metaseo_dashboard'][0][0] = __('Dashboard', 'wp-meta-seo');
        }
    }

    /**
     * Load the form for a WPSEO admin page
     */
    function load_page() {
        if (isset($_GET['page'])) {
            switch ($_GET['page']) {
                case 'metaseo_content_meta':
                    require_once( WPMETASEO_PLUGIN_DIR . 'inc/pages/content-meta.php' );
                    break;


                case 'metaseo_image_meta':
                    require_once( WPMETASEO_PLUGIN_DIR . 'inc/pages/image-meta.php' );
                    break;

                case 'metaseo_image_optimize':
                    require_once( WPMETASEO_PLUGIN_DIR . 'inc/pages/image-optimize.php' );
                    break;

                case 'metaseo_dashboard':
                default:
                    require_once( WPMETASEO_PLUGIN_DIR . 'inc/pages/dashboard.php' );
                    break;
            }
        }
    }
	
	private function ajaxHandle(){
	//
	add_action( 'wp_ajax_scanPosts', array('MetaSeo_Image_List_Table', 'scan_posts_callback') );
	add_action( 'wp_ajax_load_posts', array('MetaSeo_Image_List_Table', 'load_posts_callback') );
	add_action( 'wp_ajax_optimize_imgs', array('MetaSeo_Image_List_Table', 'optimizeImages') );
	add_action( 'wp_ajax_updateMeta', array('MetaSeo_Image_List_Table', 'updateMeta_callback') );
	add_action( 'wp_ajax_opt_checking', array('MetaSeo_Dashboard', 'optimizationChecking') );
	//
	add_action( 'wp_ajax_import_meta_data', array('MetaSeo_Content_List_Table', 'importMetaData') );
	add_action( 'wp_ajax_dismiss_import_meta', array('MetaSeo_Content_List_Table', 'dismissImport') );
	//
	add_action( 'added_post_meta' , array( 'MetaSeo_Content_List_Table', 'updateMetaSync' ), 99, 4);
	add_action( 'updated_post_meta', array( 'MetaSeo_Content_List_Table', 'updateMetaSync' ), 99, 4);
	add_action( 'deleted_post_meta', array( 'MetaSeo_Content_List_Table', 'deleteMetaSync' ), 99, 4);
	
	}

}