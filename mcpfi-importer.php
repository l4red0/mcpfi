<?php
	/*
		Plugin Name: Merchant center product feed importer
		Plugin URI: https://github.com/l4red0/mcpfi/
		Description: Fetch and display product cards from Google Merchant Center XML feed on your wordpress site.
		Author: Leszek Sołtys
		Author URI: http://soltys.biz
		Version: 1.0
		Text Domain: mcpfi
		Domain Path: ./languages
		License: GPLv3
	*/
	
	if (!defined( 'ABSPATH' ) ) exit;
	define('MCPFI_PLUGIN_DIR',plugin_dir_path(__FILE__)); //Get plugin path
	
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
	add_option( "mcpfiColor1", "#00a0d2", "", 'yes' );
	
	function mcpfi_hook_css() {
	?>
	<style>.mcpfiPrice{background: <?php echo get_option('mcpfiColor1'); ?>;}</style>
    <?php
	}
	
	add_action('wp_head', 'mcpfi_hook_css');
	add_action('admin_head', 'mcpfi_hook_css');
	add_action( 'wp_enqueue_scripts', 'mcpfi_style' );
	add_action( 'admin_enqueue_scripts', 'mcpfi_style' );//For some reason wp_enqueue_scripts loads style only to frontend. This is workaround.
	
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
				if(copy($url, $file)) {
				update_option( "mcpfiItemId", NULL);
				update_option( "mcpfiItemCat", NULL);
			} else { 
			echo "<div class='error notice'>";
			echo "<h2>Error while copying remote file</h2>";
			echo "<p>Please check if feed url exist.</p></div>";
			$file = MCPFI_PLUGIN_DIR.'xmlcache/sample.xml';
			update_option( "mcpfiFeedUrl", plugins_url( 'xmlcache/sample.xml' , __FILE__ ));
				}
			} else {
			$file = MCPFI_PLUGIN_DIR.'xmlcache/sample.xml';
			}
		
		}
		else {
			$file = MCPFI_PLUGIN_DIR.'xmlcache/sample.xml';
			}
		return $file;
	}

	//echo plugins_url( 'xmlcache/sample.xml' , __FILE__ );
	
	
	//Get feed title
	function mcpfi_feed_title() {
		$xml=simplexml_load_file(mcpfi_get_xml((get_option( 'mcpfiFeedUrl' )), get_option('mcpfiCacheLive'))) or die("Error: Cannot create object");
		$mcpfiFeedTitle = $xml->children()->channel->title;
		
		return $mcpfiFeedTitle;
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
		$xml=simplexml_load_file(mcpfi_get_xml((get_option( 'mcpfiFeedUrl' )), get_option('mcpfiCacheLive'))) or die("Error: Cannot create object");
		
		if(!isset($productId)) {$productId = get_option('mcpfiItemId');} else {}
		foreach($xml->children()->channel->item as $product) { 
			if ($product->children('g', true)->id == $productId) {
				$mcpfiSingleProduct['prId'] = $product->children('g', true)->id; 
				$mcpfiSingleProduct['prTitle'] = $product->title; 
				$mcpfiSingleProduct['prCat'] = $product->children('g', true)->product_type; 
				$mcpfiSingleProduct['prLink'] = $product->link; 
				$mcpfiSingleProduct['prImage'] = $product->children('g', true)->image_link; 
				$mcpfiSingleProduct['prDescription'] = $product->description; 
				$mcpfiSingleProduct['prGtin'] = $product->children('g', true)->gtin; 
				$mcpfiSingleProduct['prPrice'] = $product->children('g', true)->price;
				$mcpfiSingleProduct['prSalePrice'] = $product->children('g', true)->sale_price;
				$mcpfiSingleProduct['prInStock'] = $product->children('g', true)->availability; 
				$mcpfiSingleProduct['prCondition'] = $product->children('g', true)->condition;
				$mcpfiSingleProduct['prDate'] = date("y-m-d");
			}
		} 
		return $mcpfiSingleProduct;
	}
	
	//Get array of products
	function mcpfi_get_product_list() {
		$xml=simplexml_load_file(mcpfi_get_xml((get_option( 'mcpfiFeedUrl' )), get_option('mcpfiCacheLive'))) or die("Error: Cannot create object");
		$i = 0;
		foreach($xml->children()->channel->item as $products) {
			
			$mcpfiProductList[] = array(
			'prLink' => (string)$products->link,
			//'prTitle' => (string)$products->title,
			);
			$mcpfiProductList[$i]['prId'] = (string)$products->children('g', true)->id;
			$mcpfiProductList[$i]['prCat'] = (string)$products->children('g', true)->product_type;
			$mcpfiProductList[$i]['prImage'] = (string)$products->children('g', true)->image_link;
			$mcpfiProductList[$i]['prGtin'] = (string)$products->children('g', true)->gtin;
			$mcpfiProductList[$i]['prPrice'] = (string)$products->children('g', true)->price;
			$mcpfiProductList[$i]['prSalePrice'] = (string)$products->children('g', true)->sale_price;
			$mcpfiProductList[$i]['prInStock'] = (string)$products->children('g', true)->availability;
			
			if(isset($products->title)) {
				$mcpfiProductList[$i]['prTitle'] = (string)$products->title;
				} else {
				$mcpfiProductList[$i]['prTitle'] = (string)$products->children('g', true)->title;
				}
			$i++;
		}
		return $mcpfiProductList;	
	}
	//print_r(mcpfi_get_product_list());

	//Get array of product categories
	function mcpfi_get_category_list() {
		$xml=simplexml_load_file(mcpfi_get_xml((get_option( 'mcpfiFeedUrl' )), get_option('mcpfiCacheLive'))) or die("Error: Cannot create object");
		
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
	
	//Color picker
	add_action( 'admin_enqueue_scripts', 'wptuts_add_color_picker' );
	function wptuts_add_color_picker( $hook ) {
		if( is_admin() ) {    
			wp_enqueue_style( 'wp-color-picker' ); 
			wp_enqueue_script( array( 'wp-color-picker' ), false, true ); 
		}
	}
	
	//Shortcode for product
	function mcpfi_tag_id( $mcpfiTagId ) {
		$mcpfiTagData = mcpfi_get_product($mcpfiTagId['pid']);
		$mcpfiUTMsource = get_option( 'mcpfiUTMsource' );
		$mcpfiUTMmedium = get_option( 'mcpfiUTMmedium' );
		$mcpfiUTMcampagin = get_option( 'mcpfiUTMcampagin' );
		
		$mcpfi_salePrice = $mcpfiTagData['prPrice'];
		if ($mcpfiTagData['prSalePrice'] != "") {
			$mcpfi_salePrice = "<sup><del>".$mcpfiTagData['prPrice']."</del></sup> ".$mcpfiTagData['prSalePrice'];
			} else {
			$mcpfi_salePrice = $mcpfiTagData['prPrice'];
		}
		
		if (isset($mcpfiTagData)) {
		return <<<HTML
		<a href="{$mcpfiTagData['prLink'][0]}?utm_source={$mcpfiUTMsource}&utm_medium={$mcpfiUTMmedium}&utm_campaign={$mcpfiUTMcampagin}&utm_term={$mcpfiTagData['prCat'][0]}&utm_content={$mcpfiTagData['prTitle'][0]}" title="{$mcpfiTagData['prTitle'][0]}" class="mcpfiLink">
		<div class="mcpfiProduct">
		<div class="mcpfiPrTitle">
		<span class="prTitle">{$mcpfiTagData['prTitle'][0]}</span>
		</div>
		<div class="mcpfiImageContainer">
		<img src="{$mcpfiTagData['prImage'][0]}" class="mcpfiProductImg" alt="{$mcpfiTagData['prTitle'][0]}" />
		</div>
		<span class="mcpfiPrice">{$mcpfi_salePrice}</span>
		</div>
		</a>
HTML;
		} else {return null;}
	}
	add_shortcode( 'mcpfiid', 'mcpfi_tag_id' );
	
	//Product preview and shortcode copy box
	if(isset($_POST['product']) && !empty($_POST['product'])) {
		$product = $_POST['product'];
		$mcpfi_product = mcpfi_get_product($product);
		
		$mcpfi_salePrice = $mcpfi_product['prPrice'];
		if ($mcpfi_product['prSalePrice'] != "") {
			$mcpfi_salePrice = "<sup><del>".$mcpfi_product['prPrice']."</del></sup> ".$mcpfi_product['prSalePrice'];
			} else {
			$mcpfi_salePrice = $mcpfi_product['prPrice'];
		}
		echo <<<HTML
		<div class="mcpfiProduct" style="width: 250px;">
		<div class="mcpfiPrTitle mcpfiprev">
		<span class="prTitle">{$mcpfi_product['prTitle'][0]}</span>
		</div>
		<a href="{$mcpfi_product['prLink'][0]}" title="{$mcpfi_product['prTitle'][0]}">
		<img src="{$mcpfi_product['prImage'][0]}" class="mcpfiProductImg" alt="{$mcpfi_product['prTitle'][0]}" />
		</a>
		<span class="mcpfiPrice mcpfiprev">{$mcpfi_salePrice}</span>
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
		$categorysel = $_POST['categorysel'];
		foreach((mcpfi_get_products_from_category($categorysel)) as $product) {
			echo  '<option value="'.$product['prId'].'">'.$product['prTitle']."</option>\n";	
		}
		exit();
	}
	
	//Settings page in backend
	function mcpfi_settings_page(){ ?> 
	<?php include( MCPFI_PLUGIN_DIR . 'mcpfi_settings_page.php'); ?> 
<?php } ?>