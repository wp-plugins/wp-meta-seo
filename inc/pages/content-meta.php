<?php
/*
Meta tags content 
 */

if (!class_exists('MetaSeo_Content_List_Table')) {
    require_once( WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-content-list-table.php' );
}

$metaseo_list_table = new MetaSeo_Content_List_Table();
$metaseo_list_table->process_action();
$metaseo_list_table->prepare_items();

if (!empty($_REQUEST['_wp_http_referer'])) {
    wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
    exit;
}

?>

<div class="wrap seo_extended_table_page">
    <div id="icon-edit-pages" class="icon32 icon32-posts-page"></div>

    <?php echo '<h2>' . __('Content Meta', 'wp-meta-seo') . '</h2>'; ?>
	
	<form id="wp-seo-meta-form" action="" method="post">
    <?php $metaseo_list_table->display(); ?>
    </form>

</div>
<script type="text/javascript">
jQuery(document).ready(function($){
    $('.metaseo-metatitle').each(function() {
        metaseo_updateTitle(this.id, false, false);
    });
    $('.metaseo-metadesc').each(function() {
        metaseo_updateDesc(this.id,false);
    });

    $('.metaseo-metatitle').bind('input propertychange', function() {
        metaseo_updateTitle(this.id, true);
    });

    $('.metaseo-metadesc').bind('input propertychange', function() {
        metaseo_updateDesc(this.id, true);
    });
    
    $('.metaseo-metadesc, .metaseo-metatitle').bind('input propertychange', function(){
    	var idNumber = this.id.substr(this.id.lastIndexOf('-')+1);
    	if(this.id == 'metaseo-metatitle-'+idNumber){
    		if(!$(this).val()){
    			var post_title = $('#post-title-'+idNumber).text(); 
    			$('#snippet_title'+idNumber).text(post_title);
    		}
    	}
    	
    });
});
</script>