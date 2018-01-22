<?php
	/*
		Plugin Name: Merchant center product feed importer
		Plugin URI: https://github.com/l4red0/mcpfi/
		Description: Fetch and display product cards from Google Merchant Center XML feed on your wordpress site.
		Author: Leszek Sołtys
		Author URI: http://soltys.biz
		Version: 1.15
		Text Domain: mcpfi
		Domain Path: ./languages
		License: GPLv3
	*/
	
	if(!defined( 'ABSPATH' )) { exit();	}
	define('MCPFI_PLUGIN_DIR',plugin_dir_path(__FILE__)); //Get plugin path
	libxml_use_internal_errors(false); //True, for xml debugging
	
	add_action('admin_menu', 'mcpfi_admin_menu');
	
	$mcpfiHomeUrl = esc_url( home_url( '/' ) );
	
	//Wordpress internal options table
	add_option( "mcpfiFeedUrl", "", "", 'yes' );
	add_option( "mcpfiItemId", "", "", 'yes' );
	add_option( "mcpfiItemCat", "", "", 'yes' );
	add_option( "mcpfiCacheLive", "3600", "", 'yes' );
	add_option( "mcpfiUTMsource", $mcpfiHomeUrl, "", 'yes' );
	add_option( "mcpfiUTMmedium", "banner", "", 'yes' );
	add_option( "mcpfiUTMcampagin", "mcpfi", "", 'yes' );
	add_option( "mcpfiUTM", "0", "", 'yes' );
	add_option( "mcpfiColor1", "#00a0d2", "", 'yes' );
	add_option( "mcpfiImgHeight", "130", "", 'yes' );
	add_option( "mcpfiImgWidth", "150", "", 'yes' );
	

	add_action( 'wp_enqueue_scripts', 'mcpfi_style' );
	add_action( 'admin_enqueue_scripts', 'mcpfi_style' );
	
	$mcpfiFeedUrl = get_option('mcpfiFeedUrl');

	
	//Attach style
	function mcpfi_style(){
		wp_register_style('mcpfi-styles', plugins_url( 'mcpfi-style.css' , __FILE__ )); 
		wp_enqueue_style('mcpfi-styles');
	}
	
	//Create backend menu link
	function mcpfi_admin_menu(){
        add_menu_page( 'Merchant Center products feed importer', 'MC feed importer', 'manage_options', 'mcpfi', 'mcpfi_settings_page', 'dashicons-cart', 80 );
	}
	
	//Fetch XML from given url and save it locally. If no url load sample.xml
	function mcpfi_get_xml($url, $max_age)
	{
		if(isset($url) && $url != ""){
			$file = MCPFI_PLUGIN_DIR."xmlcache/". md5($url) .'cached.xml';
			if (!file_exists($file) || filemtime($file) < time() - $max_age)
			{
				if(@copy($url, $file)) {
					update_option( "mcpfiItemId", NULL);
					update_option( "mcpfiItemCat", NULL);
					} else { //Feed URL is broken - display message on admin page 
					if(is_admin()) {
						echo "<div class='error notice'><h2>Error while copying remote file</h2><p>Please check if feed url exist.</p></div>";
					}
					$file = MCPFI_PLUGIN_DIR.'xmlcache/sample.xml';
				}
			} else {  }
		}
		else { $file = MCPFI_PLUGIN_DIR.'xmlcache/sample.xml'; }
		return $file;
	}	
	
	//Get feed title
	function mcpfi_feed_title() {
		$xml=simplexml_load_file(mcpfi_get_xml((get_option( 'mcpfiFeedUrl' )), get_option('mcpfiCacheLive')));
		if (false === $xml) {
			$mcpfiFeedTitle = NULL;
			update_option( "mcpfiFeedUrl", NULL);
			if(is_admin()) {
				echo "<div class='error notice'><h2>Error while reading XML</h2><p>Please validate your feed.</p></div>";
				foreach(libxml_get_errors() as $error) {
					echo "<div class='error notice'>".$error->message."</div>";
				}
			}
			} else {
			$mcpfiFeedTitle = $xml->children()->channel->title;
		}
		libxml_clear_errors();
		
		return $mcpfiFeedTitle;
	}
	
	//Color picker
	add_action( 'admin_enqueue_scripts', 'wptuts_add_color_picker' );
	function wptuts_add_color_picker( $hook ) {
		if( is_admin() ) {    
			wp_enqueue_style( 'wp-color-picker' ); 
			wp_enqueue_script( array( 'wp-color-picker' ), false, true ); 
		}
	}
	
	//Get feed file age (last sync)
	function mcpfi_feed_age() {
		$mcpfiFileAge = mcpfi_get_xml((get_option( 'mcpfiFeedUrl' )), get_option('mcpfiCacheLive'));
		$mcpfiFileAge = time() - filemtime($mcpfiFileAge);
		$mcpfiFileAge = date("H:i:s", $mcpfiFileAge);
		
		return $mcpfiFileAge;
	}
	
	//Get product by id
	function mcpfi_get_product($productId=NULL) {
		$xml=simplexml_load_file(mcpfi_get_xml((get_option('mcpfiFeedUrl')), get_option('mcpfiCacheLive')));
		
		if(!isset($productId)) {$productId = get_option('mcpfiItemId');} else {$mcpfiSingleProduct = NULL;}
		foreach($xml->children()->channel->item as $product) {
		
		(isset($product->children('g', true)->id) ? $prIDcase = 'id' : $prIDcase = 'ID');

			if ($product->children('g', true)->$prIDcase == $productId) {
				$mcpfiSingleProduct['prId'] = esc_attr($product->children('g', true)->$prIDcase->__toString()); 
				$mcpfiSingleProduct['prTitle'] = esc_attr($product->title->__toString());
				$mcpfiSingleProduct['prCat'] = esc_html($product->children('g', true)->product_type->__toString()); 
				$mcpfiSingleProduct['prLink'] = esc_url($product->link->__toString()); 
				$mcpfiSingleProduct['prImage'] = esc_url($product->children('g', true)->image_link->__toString()); 
				$mcpfiSingleProduct['prDescription'] = esc_html($product->description->__toString()); 
				$mcpfiSingleProduct['prGtin'] = esc_attr($product->children('g', true)->gtin->__toString()); 
				$mcpfiSingleProduct['prPrice'] = esc_attr($product->children('g', true)->price->__toString());
				$mcpfiSingleProduct['prSalePrice'] = esc_attr($product->children('g', true)->sale_price->__toString());
				$mcpfiSingleProduct['prInStock'] = esc_attr($product->children('g', true)->availability->__toString()); 
				$mcpfiSingleProduct['prCondition'] = esc_attr($product->children('g', true)->condition->__toString());
				$mcpfiSingleProduct['prDate'] = esc_attr(date("y-m-d"));
			}
			
		} 
		return $mcpfiSingleProduct;
	}
	
	//Get array of products
	function mcpfi_get_product_list() {
		$xml=simplexml_load_file(mcpfi_get_xml((get_option( 'mcpfiFeedUrl' )), get_option('mcpfiCacheLive')));
		$i = 0;
		
		foreach($xml->children()->channel->item as $products) {
			(isset($products->children('g', true)->id) ? $prIDcase = 'id' : $prIDcase = 'ID');
			
			$mcpfiProductList[] = array('prLink' => (string)$products->link,);
			
			$mcpfiProductList[$i]['prId'] = esc_html($products->children('g', true)->$prIDcase->__toString());
			$mcpfiProductList[$i]['prCat'] = $products->children('g', true)->product_type->__toString();
			$mcpfiProductList[$i]['prImage'] = esc_url($products->children('g', true)->image_link->__toString());
			$mcpfiProductList[$i]['prGtin'] = esc_html($products->children('g', true)->gtin->__toString());
			$mcpfiProductList[$i]['prPrice'] = esc_html($products->children('g', true)->price->__toString());
			$mcpfiProductList[$i]['prSalePrice'] = esc_html($products->children('g', true)->sale_price->__toString());
			$mcpfiProductList[$i]['prInStock'] = esc_attr($products->children('g', true)->availability->__toString());
			$mcpfiProductList[$i]['prTitle'] = esc_attr($products->title->__toString());

			$i++;;
		}
		return $mcpfiProductList;	
	}
	
	//Get array of product categories
	function mcpfi_get_category_list() {
		$xml=simplexml_load_file(mcpfi_get_xml((get_option( 'mcpfiFeedUrl' )), get_option('mcpfiCacheLive')));
		
		foreach($xml->children()->channel->item as $products) {
			$mcpfiCategoryList[] = (string)$products->children('g', true)->product_type;
		}
		return array_unique($mcpfiCategoryList);
	}
	
	//Product list based on category
	function mcpfi_get_products_from_category($mcpfi_product_cat) {
		$mcpfi_get_product_list = mcpfi_get_product_list();
		
		if(isset($mcpfi_get_product_list)){
			
			foreach ($mcpfi_get_product_list as $product) {
				if($product['prCat'] == $mcpfi_product_cat) {
					$products[] = $product;
				}
			}
		} else { $products = NULL; }
		return $products;
	}
	
	//Seconds to minutes
	function mcpfi_s2m($seconds) {
		$minutes = $seconds/60;
		return $minutes;
	}
	
	//Minutes to seconds
	function mcpfi_m2s($minutes) {
		$seconds = $minutes*60;
		return $seconds;
	}
	
	//Shortcode for product in frontend
	function mcpfi_tag_id( $mcpfiTagId ) {
		$mcpfiTagData = mcpfi_get_product($mcpfiTagId['pid']);
		$mcpfiUTMsource = urlencode(parse_url(get_option('mcpfiUTMsource'), PHP_URL_HOST));
		$mcpfiUTMmedium = urlencode(get_option('mcpfiUTMmedium'));
		$mcpfiUTMcampagin = urlencode(get_option('mcpfiUTMcampagin'));
		$mcpfiUTM = get_option('mcpfiUTM');
		$mcpfiColor1 = get_option('mcpfiColor1');
		$mcpfiImgHeight = get_option('mcpfiImgHeight');
		$mcpfiImgWidth = get_option('mcpfiImgWidth');
		
		$mcpfi_salePrice = $mcpfiTagData['prPrice'];
		
		if ($mcpfiTagData['prSalePrice'] != "") {
			$mcpfi_salePrice = "<sup><del>".$mcpfiTagData['prPrice']."</del></sup> ".$mcpfiTagData['prSalePrice'];
			} else {
			$mcpfi_salePrice = $mcpfiTagData['prPrice'];
		}
		
		//Check if UTM is on and construct link
		if ($mcpfiUTM != 0) {
			$mcpfiUTMlink = "<a href=".$mcpfiTagData['prLink']."?utm_source=".$mcpfiUTMsource."&utm_medium=".$mcpfiUTMmedium."&utm_campaign=".$mcpfiUTMcampagin."&utm_term=".urlencode($mcpfiTagData['prCat'])."&utm_content=".urlencode($mcpfiTagData['prTitle'])." title='".urlencode($mcpfiTagData['prTitle'])."' class='mcpfiLink'>";
			} else {
			$mcpfiUTMlink = "<a href=".$mcpfiTagData['prLink'].">";
		}
		
		if (isset($mcpfiTagData)) {
			return <<<HTML
			{$mcpfiUTMlink}
			<span class="mcpfiProduct" style="width: {$mcpfiImgWidth}px">
			<span class="mcpfiPrTitle">
			<span class="prTitle">{$mcpfiTagData['prTitle']}</span>
			</span>
			<span class="mcpfiImageContainer">
			<img src="{$mcpfiTagData['prImage']}" class="mcpfiProductImg" alt="{$mcpfiTagData['prTitle']}" style="height: {$mcpfiImgHeight}px" />
			</span>
			<span class="mcpfiPrice" style="background: {$mcpfiColor1}">{$mcpfi_salePrice}</span>
			</span>
			</a>
HTML;
		} else {return null;}
	}
	add_shortcode( 'mcpfiid', 'mcpfi_tag_id' );
	
	//Product preview and shortcode copy box
	if(isset($_POST['product']) && !empty($_POST['product'])) {
	
		$product = sanitize_text_field($_POST['product']);
		$mcpfi_product = mcpfi_get_product($product);
		$mcpfiColor1 = get_option('mcpfiColor1');
		$mcpfiImgHeight = get_option('mcpfiImgHeight');
		
		$mcpfi_salePrice = $mcpfi_product['prPrice'];
		if ($mcpfi_product['prSalePrice'] != "") {
			$mcpfi_salePrice = "<sup><del>".$mcpfi_product['prPrice']."</del></sup> ".$mcpfi_product['prSalePrice'];
			} else {
			$mcpfi_salePrice = $mcpfi_product['prPrice'];
		}

		echo <<<HTML
		<span class="mcpfiProduct" style="width: 250px;">
		<span class="mcpfiPrTitle mcpfiprev">
		<span class="prTitle">{$mcpfi_product['prTitle']}</span>
		</span>
		<a href="{$mcpfi_product['prLink']}" title="{$mcpfi_product['prTitle']}">
		<img src="{$mcpfi_product['prImage']}" class="mcpfiProductImg" alt="{$mcpfi_product['prTitle']}" style="height: {$mcpfiImgHeight}px" />
		</a>
		<span class="mcpfiPrice mcpfiprev" style="background: {$mcpfiColor1}">{$mcpfi_salePrice}</span>
		</div>
		<div style="clear:both; margin: 8px;">
		<input type="text" id="mcpfiItemIdshortcode" size="25" name="mcpfiItemIdshortcode" value='[mcpfiid pid="{$mcpfi_product['prId']}"]' readonly/>
		<button onclick="copyToClipboard('mcpfiItemIdshortcode')" type="button">Copy</button>
		</div>
HTML;
		exit();
	}
	
	//Print product list from given category
	if(isset($_POST['categorysel']) && !empty($_POST['categorysel'])) {
		$categorysel = sanitize_text_field($_POST['categorysel']);
		print_r($categorysel);
		foreach((mcpfi_get_products_from_category($categorysel)) as $product) {
			echo  '<option value="'.$product['prId'].'">'.$product['prTitle']."</option>\n";	
		}
		exit();
	}
	
	//Settings page in backend
	function mcpfi_settings_page(){
		require_once( MCPFI_PLUGIN_DIR . 'mcpfi_settings_page.php');
 } 
 ?>