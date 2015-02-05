<?php 
if (!class_exists('MetaSeo_Dashboard')) {
    require_once( WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-dashboard.php' );
}

$site_name = preg_replace('/(^(http|https):\/\/[w]*\.*)/', '', get_site_url());
//$site_name = 'testdev-united.com';
$url = 'http://www.alexa.com/siteinfo/' . $site_name;
$dashboard = new MetaSeo_Dashboard();

?>
<h1><?php echo __('DASH BOARD', 'wp-meta-seo') ?></h1>
<div class="dashboard">
    <div class="left">
        <div class="dashboard-left" id='dashboard-left'>
            <header>
                <p>
                <div class="title-seo"><?php echo __('Image Meta :', 'wp-meta-seo'); ?> <span id="imgs_metas_statis"></span></div>
                <div class="noload" id="imgs_metas_statis_noload">
                    <span class="loadtext" id="imgs_metas_statis_value"></span>
                    <div class="load" id="imgs_metas_statis_load">
                    </div>
                </div>
                </p>
                <p>
                <div class="title-seo"><?php echo __('Content Meta :', 'wp-meta-seo'); ?> <span id="metacontent"></span></div>
                <div class="noload" id="imgs_metas_statis_noload">
                    <span class="loadtext" id="metacontent_value"></span>
                    <div class="load" id="metacontent_load">
                    </div>
                </div>
                </p>
                <p>
                <div class="title-seo"><?php echo __('Image optimization :', 'wp-meta-seo'); ?> <span id="imgs_statis"></span></div>
                <div class="noload" id="imgs_metas_statis_noload">
                    <span class="loadtext" id="imgs_statis_value"></span>
                    <div class="load" id="imgs_statis_load">
                    </div>
                </div>
                </p>
            </header>            
            <div id="canvas-holder">
                <div id="chart-container">
                    <h2><?php echo __('Total', 'wp-meta-seo'); ?></h2>
                    <div style="width: 300px; margin: 0px auto; position: relative;">
                        <div id="avera">
                        </div>
                        <canvas id="chart-area" width="500" height="500"/>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="right">
	<div class="dashboard-right">
		<div id="alexa-ranking">
		<?php $dashboard->displayRank($url) ?>
		</div>
		
		<div style="clear:left"></div>
        <div id="wpmetaseo-update-version">
        	<h4><?php echo __('Latest WP Meta SEO News', 'wp-meta-seo') ?></h4>
        	<ul>
        		<li>&ndash;&nbsp<?php echo __('Version 1.0') ?></li>
        		<li>&ndash;&nbsp<?php echo __('Version Beta') ?></li>
        	</ul>
        </div>
   </div>
</div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            optimizationChecking(updateChart);
            var height_left = jQuery("#dashboard-left").height();
            jQuery(".dashboard-right").css('height',height_left);
        });
        
                   
        function updateChart(response) {
         //   console.log(response);
          
            var columns = {
                meta_desc_statis: 0,
                meta_title_statis: 0,
                imgs_metas_statis: 0,
                imgs_statis: 0,
                metacontent: 0
            };
            
            var background_color = {
                imgs_metas_statis: "#FFC870",
                imgs_statis: "#5AD3D1",
                metacontent: "#7eb5e8"
            };
            for (key in response) {
               // console.log(response[key]);
                if(response[key][0]>0 && response[key][1]>0){
                    columns[key] = (response[key][0] / response[key][1]) * 100;
                }else{
                    columns[key] = 0;
                }
                    if(columns[key]>5){
                        if(columns[key] %1 !=0){
                            jQuery('#' + key + '_value').text(columns[key].toFixed(2) + '%').css("left", (columns[key] - 5) / 2 + "%");
                        }else{
                            jQuery('#' + key + '_value').text(columns[key] + '%').css("left", (columns[key] - 5) / 2 + "%");
                        }
                    }else{
                        jQuery('#' + key + '_value').text(columns[key] + '%').css("left", 0 + "%");
                    }
                jQuery('#' + key + '_load').css({"width":columns[key] + '%','background':background_color[key]});
                
                
            }
            var count = columns.metacontent + columns.imgs_metas_statis + columns.imgs_statis;
            var metacontent= (columns.metacontent/3);  
            var imgs_metas_statis= (columns.imgs_metas_statis/3);
            var imgs_statis= (columns.imgs_statis/3);
            
            var notmetacontent= 100/3 - metacontent;
            var notimgs_metas_statis= 100/3 - imgs_metas_statis;
            var notimgs_statis= 100/3 - imgs_statis;

            var avera = ((count)/3);
            jQuery('#avera').text(avera.toFixed(2) + '%').addClass('avera');
            
            drawChart(metacontent,notmetacontent, imgs_metas_statis,notimgs_metas_statis, imgs_statis,notimgs_statis);
            
        }
    </script>