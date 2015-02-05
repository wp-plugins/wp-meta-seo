<?php

/*
 * Comments to come later
 *
 *
 */

class MetaSeo_Dashboard {

    public static function optimizationChecking() {
        global $wpdb;
        $imgs = 0;
        #$imgs_metas = 0;
        $imgs_metas = array('alt' => 0, 'title' => 0);
        $imgs_are_good = 0;
        $imgs_metas_are_good = array();
        $meta_keys = array('alt', 'title');
        $response = array(
            'imgs_statis' => array(0, 0),
            'imgs_metas_statis' => array(0, 0),
            'meta_title_statis' => array(0, 0),
            'meta_desc_statis' => array(0, 0),
            'metacontent' => array(0, 0),
        );
        foreach ($meta_keys as $meta_key) {
            $imgs_metas_are_good[$meta_key] = 0;
            $imgs_metas_are_not_good[$meta_key] = 0;
        }
		
		$post_types = MetaSeo_Content_List_Table::get_post_types();
        $query = "SELECT `ID`, `post_title`, `post_content`, `post_type`, `post_date`
					FROM $wpdb->posts
					WHERE `post_type` IN ($post_types)
					AND `post_content` <> ''
					AND `post_content` LIKE '%<img%>%' 
					ORDER BY ID";

        $posts = $wpdb->get_results($query);
        if (count($posts) > 0) {
            $doc = new DOMDocument();
            $upload_dir = wp_upload_dir();

            foreach ($posts as $post) {
                $dom = $doc->loadHTML($post->post_content);
                $tags = $doc->getElementsByTagName('img');
                foreach ($tags as $tag) {
                    $img_src = $tag->getAttribute('src');
					
					if(!preg_match('/\.(jpg|png|gif)$/i', $img_src, $matches)){
						continue;
					}
					
                    $img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img_src);
					if(!file_exists($img_path)){
						continue;
					}
					
                    $width = $tag->getAttribute('width');
                    $height = $tag->getAttribute('height');
                    if (list($real_width, $real_height) = @getimagesize($img_path)) {
                        $ratio_origin = $real_width / $real_height;
                        //Check if img tag is missing with/height attribute value or not
                        if (!$width && !$height) {
                            $width = $real_width;
                            $height = $real_height;
                        } elseif ($width && !$height) {
                            $height = $width * (1 / $ratio_origin);
                        } elseif ($height && !$width) {
                            $width = $height * ($ratio_origin);
                        }

                        if ($real_width <= $width && $real_height <= $height) {
                            $imgs_are_good++;
                        }

                        foreach ($meta_keys as $meta_key) {
                        	
                            if (trim($tag->getAttribute($meta_key))) {
                                $imgs_metas_are_good[$meta_key] ++;
                            }
							
                        }
                    }
					
					$imgs++;
                }
            }

            //Report analytic of images optimization
            $response['imgs_statis'][0] = $imgs_are_good;
            $response['imgs_statis'][1] = $imgs;
            $response['imgs_metas_statis'][0] = ceil(($imgs_metas_are_good['alt'] + $imgs_metas_are_good['title'])/2 ) ;
            $response['imgs_metas_statis'][1] = $imgs;
        }
        
        //Get number of post/page and number of images inserted into them
        $posts_counter = wp_count_posts('post');
        $pages_counter = wp_count_posts('page');
        $posts_pages_total = $posts_counter->publish + $pages_counter->publish;
        $response['meta_title_statis'][1] = $posts_pages_total;
        $response['meta_desc_statis'][1] = $posts_pages_total;

        $query = "SELECT `meta_key`, count( `meta_value` ) as total
                                      FROM $wpdb->postmeta
                                      WHERE `meta_key` = '_metaseo_metatitle' AND `meta_value` <> ''
                                      UNION (
                                      SELECT `meta_key`, count( `meta_value` ) as total
                                      FROM $wpdb->postmeta
                                      WHERE `meta_key` = '_metaseo_metadesc' AND `meta_value` <> ''
                                      )";

        $alias_names = array('_metaseo_metatitle' => 'meta_title_statis', '_metaseo_metadesc' => 'meta_desc_statis');

        $results = $wpdb->get_results($query);
        if (count($results) > 0) {
            $count_tt_desc = 0;
            foreach ($results as $result) {
                if ($result->meta_key === NULL) {
                    continue;
                }
                //Report analytic of content meta
                $count_tt_desc +=(int) $result->total;
                $response[$alias_names[$result->meta_key]][0] = (int) $result->total;
                $response[$alias_names[$result->meta_key]][1] = $posts_pages_total;
            }
            $response['metacontent'][0] = ceil($count_tt_desc/2);
            $response['metacontent'][1] = $posts_pages_total ;
        }

        echo json_encode($response);
        wp_die();
    }
    
    public function displayRank($url){
		$rank = $this->getRank($url);
		if($rank !== ''){
			echo $rank;
		}
		else{
			echo __('We can\'t get rank of this site from Alexa.com!', 'wp-meta-seo');
		}
	}
	
	public function getRank($url){
		if(!function_exists('curl_version')){
			if(!$content = @file_get_contents($url)){
				return '';
			}
		}
		else{
			if(!is_array($url)){ $url = array($url); }
			$contents = $this->get_contents($url);
			$content = $contents[0];
		}
		
		$doc = new DOMDocument();
		@$doc->loadHTML($content);
		$doc->preserveWhiteSpace = false;
		
		$finder = new DOMXPath($doc);
		$classname = 'note-no-data';
		$nodes = $finder->query("//section[contains(@class, '$classname')]");
		if($nodes->length < 1) {
			$classname = 'rank-row';
			$nodes = $finder->query("//div[contains(@class, '$classname')]");
		}
		
		$tmp_dom = new DOMDocument();
		foreach($nodes as $key => $node){
			$tmp_dom->appendChild($tmp_dom->importNode($node,true));
		}
		
		$html = trim($tmp_dom->saveHTML());
		$html = str_replace('We don\'t have', 'Alexa doesn\'t has', $html);
		$html = str_replace('Get Certified', '', $html);
		$html = str_replace('"/topsites/countries', '"http://www.alexa.com/topsites/countries', $html);
		return $html;
	} 
	
	public function get_contents($urls){
		$mh = curl_multi_init();
        $curl_array = array();
		$useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';
        foreach($urls as $i => $url)
        {
			$curl_array[$i] = curl_init($url);
			curl_setopt($curl_array[$i], CURLOPT_URL, $url);
			curl_setopt($curl_array[$i], CURLOPT_USERAGENT, $useragent); // set user agent
			curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl_array[$i], CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($curl_array[$i], CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curl_array[$i], CURLOPT_ENCODING ,"UTF-8");
			curl_multi_add_handle($mh, $curl_array[$i]);
			
        }
		
        $running = NULL;
        do {
            usleep(10000);
            curl_multi_exec($mh,$running);
        } while($running > 0);
       
        $contents = array();
        foreach($urls as $i => $url)
        {
            $content = curl_multi_getcontent($curl_array[$i]);
			$contents[$i] = $content;
        }
       
        foreach($urls as $i => $url){
            curl_multi_remove_handle($mh, $curl_array[$i]);
        }
        curl_multi_close($mh);
        return $contents;
	}

}