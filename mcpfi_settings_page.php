<?php
	if(!defined( 'ABSPATH' )) { exit();	}
	if(!current_user_can( 'manage_options' )) { exit();	}
	
	if( isset( $_POST['mcpfiFeedUrl'] ) ) {
		update_option( "mcpfiFeedUrl", esc_url($_POST['mcpfiFeedUrl']));
		$mcpfiFeedUrl = get_option( 'mcpfiFeedUrl' );
	}
	if( isset( $_POST['mcpfiItemId'] ) ) {
		update_option( "mcpfiItemId", sanitize_text_field($_POST['mcpfiItemId']));
	}
	
	if( isset( $_POST['mcpfiItemCat'] ) ) {
		update_option( "mcpfiItemCat", sanitize_text_field($_POST['mcpfiItemCat']));
	}
	if( isset( $_POST['mcpfiCacheLive'] ) ) {
		update_option( "mcpfiCacheLive", mcpfi_m2s(intval($_POST['mcpfiCacheLive'] )));
	}
	//UTM
	if( isset( $_POST['mcpfiUTMsource'] ) ) {
		update_option( "mcpfiUTMsource", sanitize_text_field($_POST['mcpfiUTMsource']));
	}
	if( isset( $_POST['mcpfiUTMmedium'] ) ) {
		update_option( "mcpfiUTMmedium", sanitize_text_field($_POST['mcpfiUTMmedium']));
	}
	if( isset( $_POST['mcpfiUTMcampagin'] ) ) {
		update_option( "mcpfiUTMcampagin", sanitize_text_field($_POST['mcpfiUTMcampagin']));
	}
	if( isset( $_POST['mcpfiColor1'] ) ) {
		update_option( "mcpfiColor1", sanitize_hex_color($_POST['mcpfiColor1']));
	}
	if( isset( $_POST['mcpfiImgHeight'] ) ) {
		update_option( "mcpfiImgHeight", intval($_POST['mcpfiImgHeight']));
	}
	if( isset( $_POST['mcpfiImgWidth'] ) ) {
		update_option( "mcpfiImgWidth", intval($_POST['mcpfiImgWidth']));
	}
	
	
	$mcpfiItemCat = get_option('mcpfiItemCat');
	$mcpfiItemId = get_option('mcpfiItemId');
	
?>


<script type="text/javascript">
	(function($) {
		$(function(ready) {
			
			$(".mcpfiColor1").wpColorPicker({
				change: function (event, ui) {
					var mcpfiColor1 = $(this).val();
					$(".mcpfiProduct").css('border-color', mcpfiColor1);
					$(".mcpfiPrice").css('background', mcpfiColor1);
				}
			});
			
			$(".categorySelect").change(function() {
				console.log($(this).val());
			    var valueid = $( this ).val();
				
				$.ajax({ url: ajaxurl,
					async: true,
					data: {categorysel: valueid},
					type: 'post',
					success: function(output) {
						$(".accessorySelect").html(output);
						$(".accessorySelect").change();
						$(".accessorySelectRow").fadeIn();
					}
				});
				
			});
			$(function() {
				$('.color-field').wpColorPicker();
			});
			
			$(".accessorySelect").change(function() {
				console.log($(this).val());
			    var valueid = $( this ).val();
				$( ".accessoryId" ).val(valueid);
				
				$.ajax({ url: ajaxurl,
					async: true,
					data: {product: valueid},
					type: 'post',
					success: function(output) {
						
						$("#previewdiv").html(output);
						$(".mcpfiProduct").hide();
						$(".mcpfiProduct").fadeIn();
					}
				});
			});
			
			
			$(".categorySelect").val("<?php echo $mcpfiItemCat; ?>");
			$(".accessorySelect").val("<?php echo $mcpfiItemId; ?>");
			$(".accessorySelectRow").hide();
		});			
	})(jQuery);
	
	function copyToClipboard(elementId) {
		
		var cpscode = document.createElement("input");
		cpscode.setAttribute("value", document.getElementById(elementId).value);
		document.body.appendChild(cpscode);
		cpscode.select();
		document.execCommand("copy");
		document.body.removeChild(cpscode);
	}
	
</script>


<div class="wrap">
	<h1><?php _e("Merchant Center product feed importer", "mcpfi"); ?></h1>
	<h2><?php _e("Settings", "mcpfi"); ?></h2>
	<hr />
	<form method="post" action="">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e("Feed url", "mcpfi"); ?></th>
				<td>
					<strong><?php _e("Feed title:", "mcpfi"); ?></strong> <?php echo mcpfi_feed_title();?><br />
					<input type="text" name="mcpfiFeedUrl" id="mcpfiFeedUrl" size="80" value="<?php echo get_option( 'mcpfiFeedUrl' ); ?>"/><br />
					<label for="mcpfiFeedUrl"><small><?php _e("Warning! Changing main feed URL will probably break existing shortcodes in frontend.", "mcpfi"); ?></small></label>
				</td>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Cache lifespan", "mcpfi"); ?></th>
				<td><input type="number" min="0" step="1" style="width: 80px;" class="mcpfiCacheLive" name="mcpfiCacheLive" value="<?php echo mcpfi_s2m(get_option('mcpfiCacheLive')); ?>"/>  <?php _e("minutes", "mcpfi"); ?><p>
				<small><?php _e("Last synced", "mcpfi"); ?> <?php echo mcpfi_feed_age(); ?> ago</small></p></td>
			</tr>
			<tr>	
				<td colspan="2">
					<h4><?php _e("Product card display settings", "mcpfi"); ?></h4>
					<hr />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Price background color", "mcpfi"); ?></th>
				<td><input type="text" class="mcpfiColor1 color-field" name="mcpfiColor1" value="<?php echo get_option('mcpfiColor1'); ?>"/></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Image height", "mcpfi"); //Image height input ?></th>
				<td><input type="number" min="0" step="1" style="width: 80px;" class="mcpfiImgHeight" name="mcpfiImgHeight" value="<?php echo get_option('mcpfiImgHeight'); ?>" /></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Product card width", "mcpfi"); //Image height input ?></th>
				<td><input type="number" min="0" step="1" style="width: 80px;" class="mcpfiImgWidth" name="mcpfiImgWidth" value="<?php echo get_option('mcpfiImgWidth'); ?>" /></td>
			</tr>
			<tr>	
				<td colspan="2">
					<h4><?php _e("Product selection", "mcpfi"); ?></h4>
					<hr />
				</td>
			</tr>			
			<tr valign="top">
				<th scope="row"><?php _e("Category list", "mcpfi"); ?></th>
				<td>
					<select class="categorySelect" name="mcpfiItemCat">
						<?php 
							foreach(array_unique(mcpfi_get_category_list()) as $category) {
								echo  '<option value="'.$category.'">'.$category."</option>\n";
							}
						?>
					</select>	
				</td>
			</tr>
			<tr valign="top" style="display: none;" class="accessorySelectRow">
				<th scope="row"><?php _e("Products in category", "mcpfi"); ?></th>
				<td>
					<select class="accessorySelect" name="accessorySelect"></select>	
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Item id", "mcpfi"); ?> </th>
				<td><input type="text" class="accessoryId" name="mcpfiItemId" value="<?php echo get_option( 'mcpfiItemId' ); ?>"/></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Preview and shortcode", "mcpfi"); ?></th>
				<td><div id="previewdiv"></div></td>
			</tr>
			<!--UTM-->
			<tr>	
				<td colspan="2">
					<h4><?php _e("UTM (Campaign URL settings)", "mcpfi"); ?></h4>
					<hr />
				</td>
			</tr>
			<!--UTM source-->
			<tr valign="top">
				<th scope="row"><?php _e("UTM source:", "mcpfi"); ?></th>
				<td><input type="text" style="width: 210px" class="mcpfiUTMsource" name="mcpfiUTMsource" value="<?php echo get_option( 'mcpfiUTMsource' ); ?>"/></td>
			</tr>
			<!--UTM medium-->
			<tr valign="top">
				<th scope="row"><?php _e("UTM medium:", "mcpfi"); ?></th>
				<td><input type="text" style="width: 210px" class="mcpfiUTMmedium" name="mcpfiUTMmedium" value="<?php echo get_option( 'mcpfiUTMmedium' ); ?>"/></td>
			</tr>
			<!--UTM campagin-->
			<tr valign="top">
				<th scope="row"><?php _e("UTM campagin:", "mcpfi"); ?></th>
				<td><input type="text" style="width: 210px" class="mcpfiUTMcampagin" name="mcpfiUTMcampagin" value="<?php echo get_option( 'mcpfiUTMcampagin' ); ?>"/><br />
					<label for="mcpfiUTMsource"><small><?php _e("Note: UTM term and content are set automatically.", "mcpfi"); ?></small></label>	
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>