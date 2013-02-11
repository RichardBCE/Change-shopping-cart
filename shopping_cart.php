<?php 
require("includes/application_top.php");
if ($_SERVER['HTTPS'] != 'on' && !$dev_server) {
header( "Location: ".HTTPS_SERVER.DIR_WS_HTTPS_CATALOG.FILENAME_SHOPPING_CART ); 
exit; 
}?><?php

if ($_POST['error_msg']) {
	$_GET['payment_error'] = $_POST['payment_error_msg'];
	$_GET['error_message'] = $_POST['error_msg'];

}

   if ($_GET['testing']) {
   	$_SESSION['testing'] = true;
   }

  	// asa auto login script	
  	if (!tep_session_is_registered('customer_id') && !tep_session_is_registered('guest_id')) {  	
		 require_once(DIR_WS_CLASSES . 'asa_auto_login.php');
		 $auto_login = new ASAAutoLogin();
		 $customer_id = $auto_login->process();
		 $cart->restore_contents();		
		 $workspace->restore_contents();
		// tep_redirect(FILENAME_SHOPPING_CART);		 
	  } 
	// end asa auto login script	  

	
/*
  $Id: shopping_cart.php,v 1.73 2003/06/09 23:03:56 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

// if nothing selected clear all shipping data so they have to choose on fresh cart reload
if ((!$_POST['sid'] || $_POST['sid'] == '') && !$_GET['editing_shipping']) {
	unset($_POST['sid']);
	unset($_SESSION['shipping_weight_cart']);
	unset($_SESSION['shipping_weight_cart']);
	unset($_SESSION['rate_type']);
	unset($_SESSION['shipping_method']);
	unset($_SESSION['shipping_cost']);
	unset($_SESSION['shipping_quotes']);
	unset($_SESSION['shipping_chosen']);
	
	unset($_SESSION['packages']);
	unset($_SESSION['packages_1']);
	unset($_SESSION['shipping_price']);
	
	unset($_SESSION['cart_sid']);
	unset($_SESSION['cart_sid_alt']);
	$_SESSION['cart_visited'] = false;
} else {
	$_SESSION['cart_visited'] = true;
}




  

 $shopping_cart = true;
 error_reporting(0);
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_SHOPPING_CART);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SHOPPING_CART));
  //echo $_POST['proof_approval'];
  
   // if no shipping destination address was selected, use the customers own address as default
  if (!tep_session_is_registered('sendto')) {
    tep_session_register('sendto');
    $sendto = $customer_default_address_id;
  } else {
// verify the selected shipping address
    $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
    $check_address = tep_db_fetch_array($check_address_query);

    if ($check_address['total'] != '1') {
      $sendto = $customer_default_address_id;
      if (tep_session_is_registered('shipping')) tep_session_unregister('shipping');
    }
  }
  
    if (!$ship_type_selection) {
  	// clear all data about ups	
	  	unset($_POST['sid']);
	  	unset($cart_sid_alt);
	  	unset($_SESSION['sid']);
	  	unset($_SESSION['packages']['global']['shipping_total_price']);
  }

  if ($_POST['sid']) {
  	$sid_def = tep_db_prepare_input($_POST['sid']);
  }

    if ($_POST['sid_alt']) {  
  	$_SESSION['cart_sid_alt'] = $_POST['sid_alt'];
  	$cart_sid_alt = $_POST['sid_alt'];
  	$_SESSION['cart_sid_alt'] = $_POST['sid_alt']; 
  } else {
  	$cart_sid_alt = $_SESSION['cart_sid_alt'];
  }

  $defaults_query = tep_db_query("select customers_preference_payment_method, customers_preference_delivery_method from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
  $defaults = tep_db_fetch_array($defaults_query);

//if ($_SERVER['REMOTE_ADDR'] == '71.196.121.196') {	
//print_r($defaults);		
//exit;
//}

  //
 
require(DIR_WS_CLASSES . 'asa_usps_rates.php');	 	
$usps_rates_2 = new ASAUspsRates($customer_id);
$usps_rates_2->set_zone_prices();

$chosen = $_POST['us_priority'];
$us_priority_price = $usps_rates_2->get_chosen_price($chosen);
$us_priority_title = $usps_rates_2->get_chosen_title($chosen);




// handle fuel surcharge

// check default delivery methods  
if (!$customer_delivery_method) {
	if (file_exists('../includes/classes/asa_custom_code.php')) {
		require_once('../includes/classes/asa_custom_code.php');
		$customer_delivery_method = ASACustomCode::get_customer_delivery_method($customer_id);
	} 
}
$_SESSION['customer_delivery_method_assigned'] = $customer_delivery_method;

		$_SESSION['sid_alt_aft_code'] = '';		
		$default_ship_to_dealer = false;
		$default_send_on_route = false;
		if ($customer_delivery_method == 'D' && $_POST['sid_alt'] == 'flat') {	   	 
	   		$default_send_on_route = true;	
	   		$_SESSION['sid_alt_aft_code'] = 'D';   		
	   	} elseif ($_POST['sid_alt'] == 'flat') {
	   		$default_ship_to_dealer = true;
	   		// get aft delivery code from database
	   		
	   		$_SESSION['sid_alt_aft_code'] = $customer_delivery_method;   
	   	} 
  	
  	// remove fuel surcharge since we are no longer using it here
  	tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
  			where customers_id = '" . (int)$customer_id . "' 
  			and products_options_id = '31'");
  	
// end handle fuel surcharge

  	
//  		 $hook_data = '<link media="screen" rel="stylesheet" href="js/colorbox/colorbox.css" />';
//		 $template_hooks->add_hook('header_css_addin', $hook_data);
//
//  		 $hook_data = '<script src="js/colorbox/jquery.colorbox-min.js"></script>';
//		 $template_hooks->add_hook('header_js_addin', $hook_data);		 
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Cache-Control" content="no-cache; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><?php echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_SHOPPING_CART, 'action=delete_product', SSL)); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <!--
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_cart.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            -->
            <td class="titleHeading" width="150" align="center" ><?php echo HEADING_TITLE; ?></td>
            <td class="lineHeading">&nbsp</td>

          </tr>
        </table></td>
      </tr>
<?php
  if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo tep_output_string_protected($error['title']); ?></b></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBoxNotice">
          <tr class="infoBoxNoticeContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main" width="100%" valign="top"><?php echo tep_output_string_protected($error['error']); ?></td>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  }
?>      
      <tr>
        <td>
        <script>
function delete_this() {

	if (confirm('<?php echo SURE_TO_DELETE; ?>')) {
		document.cart_quantity.submit();
	}

}
        </script>
<?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php

  if ($cart->count_contents() > 0) {
?>
      <tr>
        <td>
<?php

    $info_box_contents = array();
    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => TABLE_HEADING_REMOVE);

    $info_box_contents[0][] = array('params' => 'class="productListing-heading"',
                                    'text' => TABLE_HEADING_PRODUCTS);

   
    /* GLUON Code Starts */
//    $info_box_contents[0][] = array('align' => 'center',
//                                    'params' => 'class="productListing-heading"',
//                                    'text' => TABLE_HEADING_PUBLICATION_NO);
    /* GLUON Code Ends */
    
    $info_box_contents[0][] = array('align' => 'left',
                                    'params' => 'class="productListing-heading"',
                                    'text' => TABLE_HEADING_TOTAL);
    
    
    $any_out_of_stock = 0;
    $products = $cart->get_products();

 // asa addin quantity checkbox option calculations
 $checkbox_options_values_prices = ASACustomCode::get_option_values_id_prices();
 // asa addin quantity checkbox option calculations
     
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
   
// Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
      	     
      	$discount_code_facade = new ASADiscountCouponFacade();
      	$replacement_products_id = $discount_code_facade->get_replacement_products_id($products[$i]['id'], $products[$i]['publication_id']); 
      	$mimic_product = false;
      	if ($replacement_products_id != $products_id) {
      		$mimic_product = true;
      	}
      	
      	
        while (list($option, $value) = each($products[$i]['attributes'])) {
         
        // OTF contrib begins
		//echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
		// OTF contrib ends
          $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      where pa.products_id = '" . (int)$replacement_products_id . "'
                                       and pa.options_id = '" . (int)$option . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . (int)$value . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . (int)$languages_id . "'
                                       and poval.language_id = '" . (int)$languages_id . "'");
          $attributes_values = tep_db_fetch_array($attributes);
          
          // OTF contrib begins
          if ($value == PRODUCTS_OPTIONS_VALUE_TEXT_ID) {                       
            
            $attr_value = $products[$i]['attributes_values'][$option] . 
              tep_draw_hidden_field('id[' . $products[$i]['id'] . '+++' .
              $i . '][' . TEXT_PREFIX . $option . ']',  
              $products[$i]['attributes_values'][$option]);
            $attr_name_sql_raw = 'SELECT po.products_options_name FROM ' .
              TABLE_PRODUCTS_OPTIONS . ' po, ' .
              TABLE_PRODUCTS_ATTRIBUTES . ' pa WHERE ' .
              ' pa.products_id="' . tep_get_prid($products[$i]['id']) . '" AND ' .
              ' pa.options_id="' . $option . '" AND ' .
              ' pa.options_id=po.products_options_id AND ' .
              ' po.language_id="' . $languages_id . '" ';
            $attr_name_sql = tep_db_query($attr_name_sql_raw);
            if ($arr = tep_db_fetch_array($attr_name_sql)) {
              $attr_name  = $arr['products_options_name'];
            }
            
          } else {
            
            
            $attr_value = $attributes_values['products_options_values_name'] . 
              tep_draw_hidden_field('id[' . $products[$i]['id'] . '+++' . 
              $i. '][' . $option . ']', $value);
            $attr_name  = $attributes_values['products_options_name'];
            
          }
          // OTF contrib ends
          
          
////////// customer price levels          
// get prices for this option if it is quantity
if ((int)$option == 1) {
	global $languages_id;
// $products_options_query = tep_db_query(" 
//          
//					SELECT popt.products_options_type, pov.products_options_values_id, pov.products_options_values_name, 
//					pa.options_values_price, pa.price_prefix , pase.sort_order, pas2pa.main_set,
//					pase.options_values_price_2, 
//					pase.options_values_price_3, 
//					pase.options_values_price_4, 
//					pase.options_values_price_5, 
//					pase.options_values_price_6, 
//					pase.options_values_price_7, 
//					pase.options_values_price_8, 
//					pase.options_values_price_9, 
//					pase.options_values_price_10 
//					FROM  
//					" . TABLE_PRODUCTS_ATTRIBUTES . " pa 
//					LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov 
//					ON (pov.products_options_values_id = pa.options_values_id AND pov.language_id = '" . (int)$languages_id . "' ),  
//					" . TABLE_PRODUCTS_ATTRIBUTES_SETS_TO_PRODUCTS . " pas2pa, 
//					" . TABLE_PRODUCTS_ATTRIBUTES_SETS . " pas, 
//					" . TABLE_PRODUCTS_ATTRIBUTES_SETS_ELEMENTS . " pase, 
//					" . TABLE_PRODUCTS_OPTIONS . " popt 
//					WHERE pa.products_id = '" . (int)$products[$i]['id'] . "' 
//					AND pa.options_id = '" . $option . "' 
//					AND pa.options_values_id = '".(int)$value."'
//					AND pas2pa.products_id = pa.products_id 
//					AND pas.products_attributes_sets_id = pas2pa.products_attributes_sets_id 
//					AND pas.products_options_id = pa.options_id 
//					AND pase.products_attributes_sets_id = pas.products_attributes_sets_id 
//					AND pase.options_values_id = pa.options_values_id 
//					AND pa.options_id = popt.products_options_id 
//					AND popt.language_id = '" . (int)$languages_id . "' 
//          ");           
//          
//$products_options = tep_db_fetch_array($products_options_query);
//        	
			////////// customer price levels
        		global $customers_group;
        		if (!is_object($asa_price_levels)) {
        			require_once(DIR_WS_CLASSES . 'asa_customers_price_levels.php');	
        		}        		
        		
        		$asa_price_levels = new ASACustomersPriceLevel($customers_group);
				       		
        		$products_options = $asa_price_levels->get_price_values($languages_id, $replacement_products_id, $option, $value);
//				if ($_SERVER['REMOTE_ADDR'] == '71.57.181.208') { 
//        			print_r($products_options);
//        		}
         		$attributes_values['options_values_price'] = $asa_price_levels->get_price($products_options, $mimic_product);         		  
        	////////// end customer price levels
        	
}
////////// end customer price levels          
		 
          // OTF contrib begins
          //// OTF contrib begins
          //$products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
          $products[$i][$option]['products_options_name'] = $attr_name;
          // OTF contrib ends
          
          $products[$i][$option]['products_options_name'] = $attr_name;
          // OTF contrib ends          
          $products[$i][$option]['options_values_id'] = $value;
          // OTF contrib begins
          //$products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
          $products[$i][$option]['products_options_values_name'] = $attr_value ;
          // OTF contrib ends
          
          $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
          $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];         
          
          
        }
      }
    }
    
$_SESSION['shipping_weight_cart'] = 0;
  require_once DIR_WS_CLASSES.FILENAME_ASA_PACKAGES;
       	if (!is_object($asa_packages_cart)) {
       		$asa_packages_cart = new ASAPackages($cart);
       		$asa_packages_cart->get_package_count();
       	}
       	      	
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {    	
    	
$is_upload_only = ASACustomCode::is_file_upload_only((int)$products[$i]['id']);    	
      if (($i/2) == floor($i/2)) {
        $info_box_contents[] = array('params' => 'class="productListing-even"');
      } else {
        $info_box_contents[] = array('params' => 'class="productListing-odd"');
      }

      $cur_row = sizeof($info_box_contents) - 1;
	  
	  
      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data" valign="top"',
                                             'text' => tep_draw_checkbox_field('cart_delete[]', $products[$i]['id'], '', 'onclick="delete_this()"'));

	  //product image path
	  if(is_file(PREVIEW_THUMBS_DIR . $products[$i]['publication_no'] . '_Preview.jpg')) {
	  $product_image_path = PREVIEW_THUMBS_DIR . $products[$i]['publication_no'] . '_Preview.jpg';
	  } else {
	  $product_image_path = DIR_WS_IMAGES . $products[$i]['image'];
	  }
	  
	  
	  // check if this is custom upload so we can give the correct edit product link
	  $custom_upload = false;
	 // $custom_upload = is_custom_upload_from_products_id($products[$i]['id']);
	  $custom_upload = $is_upload_only;
	  
	
	  $categories_name = '';
	  $categories_id = get_categories_id_from_products_id($products[$i]['id']);
 	  $categories_id = explode('_', $categories_id);
 	  
      if ($categories_id[0]) {
	  	$categories_name .= get_categories_name($categories_id[0]);
	  }
	  
    if ($categories_id[1]) {    	
	  	$categories_name .= ' > '.get_categories_name($categories_id[1]);
	  }

    if ($categories_id[2]) {
	  	$categories_name .= ' > '.get_categories_name($categories_id[2]);
	  }
	  // edit products link
	  $products_id_for_edit = htmlspecialchars(stripslashes($products[$i]['id']), ENT_QUOTES);;
	  $edit_product_url = '';
	  if ($custom_upload) {
	  	$edit_product_url = 'product_info.php?publication_id='.$products[$i]['publication_id'].'&products_id='.$products_id_for_edit.'&editing_product=1&publication_no='.$products[$i]['publication_no'];
	  } else {
	  	//$edit_product_url = 'hps_production.php?action=edit_product&old_products_id='.$products[$i]['id'].'&publication_id='.$products[$i]['publication_id'].'&product_name='.$products[$i]['name'].'&customer_id='.$customer_id.'&customers_workspace_id=&direct_edit=&products_id='.$products[$i]['id'];
		$edit_product_url = 'product_info.php?publication_id='.$products[$i]['publication_id'].'&products_id='.$products_id_for_edit.'&editing_product=1&publication_no='.$products[$i]['publication_no'];	 
 }
	  
	  $is_upload_text_addin = '';
	  if ($is_upload_only) {
	  	//$is_upload_text_addin = '<br>';
	  }
	  
      $products_name = '<table border="0" cellspacing="2" cellpadding="2">' .
                       '  <tr>' .
                       '    <td class="productListing-data" align="center">' . tep_image_sized($product_image_path, $products[$i]['name'], 200, 0, '', '?' . rand()) . '</td>' .
                       '    <td class="productListing-data" valign="top"><b>'. $categories_name . ' > ' . $products[$i]['name'] . $is_upload_text_addin.'</b>'.
      				   '	<br><a class="cart_edit_product" href="'.HTTP_SERVER.DIR_WS_HTTP_CATALOG.$edit_product_url.'">(edit order)</a>';	  
      
      
      if (STOCK_CHECK == 'true') {
        $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
        if (tep_not_null($stock_check)) {
          $any_out_of_stock = 1;

          $products_name .= $stock_check;
        }
      }

/* gluon code starts */
      
      $att_array = $products[$i]['attributes'];
      
      if (isset($att_array) && $att_array != '') {
	      while (list($option, $value) = each($att_array)) {
			
		  		if(get_main_price( $option,$products[$i]['products_id'],$products[$i][$option]['options_values_id'] ) > 0) $main_price = get_main_price( $option,$products[$i]['products_id'],$products[$i][$option]['options_values_id'] );
		  		
		  }	  
      }

/* gluon code ends */	
		$number_of_sets = 1;
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        reset($products[$i]['attributes']);
        
		$number_of_sets_id = 17;
		$number_of_sets = (int)ASACustomCode::get_custom_text_value_from_basket_attributes($products[$i]['id'], $number_of_sets_id);        
		
       	$quantity_values_id = $products[$i]['attributes'][1];
		$quantity = ASACustomCode::get_quantity_from_values_id($quantity_values_id);;
		$quantity = ASACustomCode::clean_product_quantity($quantity);
		
        while (list($option, $value) = each($products[$i]['attributes'])) {
/* gluon code starts */
        // asa addin quantity checkbox option calculations
        if ($option == 29) { // shrinkwrap calculations          	 
          	        			// get quantity 			

			 	$final_shrink_wrap_price = ASACustomCode::calculate_shrinkwrap($quantity, 
			 																	 $value, 
			 																	 $replacement_products_id,
			 																	 $checkbox_options_values_prices);
			 																	 
		 $products[$i][$option]['options_values_price'] = $final_shrink_wrap_price; 
          	             	    
          } else if (array_key_exists((int)$value, $checkbox_options_values_prices)) {
          	
 			$final_quantity_price = ASACustomCode::get_checkbox_calculations($quantity, $value, $checkbox_options_values_prices);  

			if ($option == 33) {
				if ($number_of_sets >= 1) {
					$final_quantity_price = $final_quantity_price * $number_of_sets;
				}
			}
		
			$products[$i][$option]['options_values_price'] = $final_quantity_price;  
          	
          } 
          
           else if ($option == 7) { // request a proof
          	// get request a prooof price
          	$request_a_proof_price = $products[$i][$option]['options_values_price'];          	
          	$products[$i][$option]['options_values_price'] = ASACustomCode::get_request_a_proof_price($request_a_proof_price, $number_of_sets);          	
          	
           }           
            else if ($option == 1) {
			
      			
            	$products[$i][$option]['options_values_price'] = ASACustomCode::get_number_of_sets_price($is_upload_only, $number_of_sets, $products[$i][$option]['options_values_price']);
          }
          else if ($option == 31 && !$fuel_surcharge) {
          	continue;
          }
          else {
          	//if ($number_of_sets > 0) {
          	//$products[$i][$option]['options_values_price'] = $products[$i][$option]['options_values_price'] * $number_of_sets;
          	//} else {
          	//$products[$i][$option]['options_values_price'] = $products[$i][$option]['options_values_price'];
          	//}
         }  
         // asa addin quantity checkbox option calculations      	
		  $att_price = get_att_price($products[$i]['price'], $main_price, $products[$i][$option]['price_prefix'], $products[$i][$option]['options_values_price']);

		  
		  // check if the price is under the minimum
		  $attribute_minimum_price_query = tep_db_query("select * from " . TABLE_VALUES_PRICES_OPTIONS . " where values_id = '" . (int)$value . "'");
		  $attribute_minimum_price = tep_db_fetch_array($attribute_minimum_price_query);
		   
		  $minimum_price_allowed = (float)$attribute_minimum_price['minimum'];
		  if ($att_price < $minimum_price_allowed) {
		  	$att_price = $minimum_price_allowed;
		  }
		  
      if ($option != 21) { // ignore pub id	
			/// discount code line item addin
			$discount_code_addin = $discount_code = $discount_code_details = '';
			if ($option == 1) {
				if ($asa_discount_code->has_stored_discount_code($products[$i]['publication_id'])) {
					$discount_code = $asa_discount_code->get_stored_discount_code($products[$i]['publication_id']);	
					$discount_code_details = $asa_discount_code->get_discount_code_details($discount_code, $products[$i]['id']);
					if ($discount_code_details) {
						$discount_code_facade = new ASADiscountCouponFacade();
						$discount_code_addin = $discount_code_facade->get_attribute_line_item_html($discount_code_details);
					}
				}
			}
			/// end discount code line item addin
	  
		  if ($att_price == 0) { 
		  	$products_name .= '<br> - ' . $products[$i][$option]['products_options_name'] . ': ' . $products[$i][$option]['products_options_values_name'] . $discount_code_addin;
		  } else {
		  	$products_name .= '<br> - ' . $products[$i][$option]['products_options_name'] . ': ' . $products[$i][$option]['products_options_values_name'] . ": " . $currencies->display_price($att_price,tep_get_tax_rate($products[$i]['tax_class_id'])) . '' . $discount_code_addin;
		  }
      }
/* gluon code ends */
        }
      }
      
     if ($number_of_sets >= 1) {
     	$products[$i]['weight'] = $products[$i]['weight'] * $number_of_sets;
     }
      $_SESSION['shipping_weight_cart'] += $products[$i]['weight'];
      $_SESSION['packages'][$products[$i]['id']]['products_weight'] = $products[$i]['weight'];
      $products_name .= '<br> - Weight: '.$products[$i]['weight'].' lbs    </td>' .
                        '  </tr>' .
                        '</table>';

      $info_box_contents[$cur_row][] = array('params' => 'class="productListing-data"',
                                             'text' => $products_name . tep_draw_hidden_field('products_id[]', $products[$i]['id']));      
      
       			$package_count = $asa_packages_cart->get_products_package_count($products[$i]['id']);
       			$package_count_text = $package_count.' Package';
       			$package_count_text .= ($package_count == 1) ? '' : 's';
      		 
       			?>  
       			<script type="text/javascript">
       			$(document).ready(function() {
					
       			});
       			</script>
       			<?php       
      $info_box_contents[$cur_row][] = array('align' => 'left',
                                             'params' => 'class="productListing-data" valign="top"',
                                             'text' => '<b>' . $currencies->display_price($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</b>');      
      
   }
    new productListingBox($info_box_contents);
    
?>
        </td>
      </tr><!--
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td align="right" class="main"><b><?php echo SUB_TITLE_SUB_TOTAL; ?> <?php echo $currencies->format($cart->show_total()); ?></b></td>
      </tr>
--><?php
    if ($any_out_of_stock == 1) {
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?>
      <tr>
        <td class="stockWarning" align="center"><br><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></td>
      </tr>
<?php
      } else {
?>
      <tr>
        <td class="stockWarning" align="center"><br><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></td>
      </tr>
<?php
      }
    }
?>
 
<?php
  } else {
?>
      <tr>
        <td align="center" class="main"><?php new infoBox(array(array('text' => TEXT_CART_EMPTY))); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td align="right" class="main"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      
<?php
  }
?>

</form><tr>
        <td align="left">
<style>
.cart_one_page_checkout a {
	color: blue;
}
.cart_footer_headings { 
	background-color: #8bb2dd;
	padding: 0 5px;
	font-weight: bold;
}

#checkout_button {
	
	cursor: pointer;
}

</style>
        <br>
        <table border="1" width="100%" style="border-collapse: collapse;' width="100%" class="cart_one_page_checkout">
        	<tr>
        		<td valign="top" width="33%">
        	<?php 
        	       	
    if (!tep_session_is_registered('sendto')) {
    tep_session_register('sendto');
    $sendto = $customer_default_address_id;
  } else {
// verify the selected shipping address
    $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
    $check_address = tep_db_fetch_array($check_address_query);

    if ($check_address['total'] != '1') {
      $sendto = $customer_default_address_id;
      if (tep_session_is_registered('shipping')) tep_session_unregister('shipping');
    }
  }
  
 	?>        	
     <script>
      	  function shipincart_submit(sid){          	           	  
  		    if(sid){
  		      document.estimator.sid.value=sid;
  		    }
  		    // bypass submit  		    
  		    document.estimator.submit();
  		    return false;
  		  }
     </script>	
<?php 

  if(!$free_shipping && $cart->get_content_type() !== 'virtual'){
  	
 if ($_POST['sid_alt'] == 'pickup') {
 	$_SESSION['sid_alt_aft_code'] = 'P';
 	//echo 'here'.$_SESSION['sid_alt_aft_code'];
 	$_POST['sid'] = $_POST['sid_alt'];
 	$sendto = $customer_default_address_id; 	
 } 
 
 if ($_POST['sid_alt'] == 'flat') {
 	$sendto = $customer_default_address_id;
 }
 
 // handler residential / commercial rates

 ///////
 
 if ($_POST['sid_alt'] == 'dropship') {
 	$_SESSION['sid_alt_aft_code'] = 'DSHP'; 	
 	$_SESSION['rate_type'] = 'Residential';
  } else if ($customer_delivery_method == 'UPSR' && $_POST['sid_alt'] != 'pickup') {
  	$_SESSION['sid_alt_aft_code'] = 'UPSR'; 	
 	$_SESSION['rate_type'] = 'Residential';
  } else if ($customer_delivery_method == 'UPS') {
  	$_SESSION['sid_alt_aft_code'] = 'UPS'; 	
 	$_SESSION['rate_type'] = 'Commercial';
 } else if ($_POST['sid_alt'] == 'flat') { 	
 	$_SESSION['rate_type'] = 'Commercial';
 } 

 	// overide and make all rates commercial for now.
 	$_SESSION['rate_type'] = 'Commercial';
 

 ////////	
    if (tep_not_null($_POST['sid'])){    	
       list($module, $method) = explode('_', $_POST['sid']);
      $cart_sid = $_POST['sid'];
      tep_session_register('cart_sid');     
    } else if (tep_not_null($_POST['sid_alt'])){    	
       list($module, $method) = explode('_', $_POST['sid_alt']);
      $cart_sid = $_POST['sid_alt'];
      tep_session_register('cart_sid');
    } elseif (tep_session_is_registered('cart_sid')){
      list($module, $method) = explode('_', $cart_sid);
    }
    
  }

  require_once(DIR_WS_CLASSES . 'order.php');
  $order = new order;


  // Discount Code 2.9 - start
  $discount = $_GET['discount_code'];
  if (MODULE_ORDER_TOTAL_DISCOUNT_STATUS == 'true') {
  	if (!empty($discount)) {
  		// 		$discount_codes_query = tep_db_query("select discount_codes_id from " . TABLE_DISCOUNT_CODES .
  		// 				" where discount_codes = '" . tep_db_input($sess_discount_code) . "'");
  		// 		$discount_codes = tep_db_fetch_array($discount_codes_query);
  
  
  		//include_once(DIR_WS_MODULES . 'order_total/ot_discount.php');
  		//$discount_module = new ot_discount();
  		//echo '</td><td align="right">';
  		// order total code
  		//$discount_module->process();
  
  
  		$sess_discount_code = $discount;
  		require_once(DIR_WS_CLASSES . 'order_total.php');
  
  		$order_total_modules = new order_total;
  		$order_total_modules->process();
  	}
  }
  // Discount Code 2.9 - end
    
    
    
    
    
    
 //////
    
// shipping calculator addin
//  if(!$free_shipping && $cart->get_content_type() !== 'virtual'){     	
//    if (tep_not_null($module)){
//      $selected_quote = $shipping_modules->quote($method, $module);
//      echo '<pre>';
//      print_r($selected_quote);
//      echo '</pre>';
//      
//      if($selected_quote[0]['error'] || !tep_not_null($selected_quote[0]['methods'][0]['cost'])){
//        $selected_shipping = $shipping_modules->cheapest();
//        $order->info['shipping_method'] = $selected_shipping['title'];
//        $order->info['shipping_cost'] = $selected_shipping['cost'];
//    $_SESSION['shipping_method'] = $selected_shipping['title'];
//    $_SESSION['shipping_cost'] = $selected_shipping['cost'];
//        $order->info['total']+= $selected_shipping['cost'];
//      }else{
//        $order->info['shipping_method'] = $selected_quote[0]['module'].' ('.$selected_quote[0]['methods'][0]['title'].')';
//        $order->info['shipping_cost'] = $selected_quote[0]['methods'][0]['cost'];
//        $order->info['total']+= $selected_quote[0]['methods'][0]['cost'];
//        $selected_shipping['title'] = $order->info['shipping_method'];
//        $selected_shipping['cost'] = $order->info['shipping_cost'];
// 	$_SESSION['shipping_method'] = $order->info['shipping_method'];
//    $_SESSION['shipping_cost'] = $order->info['shipping_cost'];        
//        $selected_shipping['id'] = $selected_quote[0]['id'].'_'.$selected_quote[0]['methods'][0]['id'];
//      }
//    }else{
//      $selected_shipping = $shipping_modules->cheapest();
//      $order->info['shipping_method'] = $selected_shipping['title'];
//      $order->info['shipping_cost'] = $selected_shipping['cost'];
//      $order->info['total']+= $selected_shipping['cost'];
//    $_SESSION['shipping_method'] = $selected_shipping['title'];
//    $_SESSION['shipping_cost'] = $selected_shipping['cost'];        
//      
//    }
//  }
//
// $order->info['shipping_cost'] = $_SESSION['shipping_cost'];
 //echo $_SESSION['shipping_quotes'][$quotes[$i]['methods'][$j]['id']]; 

// 
// load all enabled shipping modules

  
  
 
  
  
  
  

   require(DIR_WS_CLASSES . 'shipping.php');
   $total_weight = $_SESSION['shipping_weight_cart']; // set weight
    $shipping_modules = new shipping();
  
    $free_shipping = false;

    if ($_POST['sid_alt'] != 'pickup') {    	
 		$quotes = $shipping_modules->quote(); 	
    }
    
  
    
  ////
 	$ship_title = '';
	if (tep_not_null($module) && $module == 'flat' && $default_send_on_route) {	 
	
	$ship_title = 'Send on Route To Dealer';
		
 	$order->info['shipping_method'] = $ship_title.' ('.$ship_title.')';
    $order->info['total']+= 0;
    $selected_shipping['title'] = '-'.$ship_title;
    $selected_shipping['cost'] = $_SESSION['shipping_cost'] = $order->info['shipping_cost'] = $shipping_cost = 0;
 	$_SESSION['shipping_method'] = $order->info['shipping_method'];       
    $selected_shipping['id'] = $module.'_'.$module;  
    
	}    
	
	
	if (tep_not_null($module) && $module == 'pickup') {	 
	
	$ship_title = 'Pick Up';
	
 	$order->info['shipping_method'] = $ship_title.' ('.$ship_title.')';
    $order->info['total']+= 0;
    $selected_shipping['title'] = '-'.$ship_title;
    $selected_shipping['cost'] = $_SESSION['shipping_cost'] = $order->info['shipping_cost'] = $shipping_cost = 0;
 	$_SESSION['shipping_method'] = $order->info['shipping_method'];       
    $selected_shipping['id'] = $module.'_'.$module;  
    
	}    	
     
    if ($_SERVER['REMOTE_ADDR'] == '71.57.181.208') {	
    		
    	//echo $_SESSION['packages']['packages']['total_weight'];
    	//echo $_SESSION['packages']['packages']['package_count'];
    	
    }
  ////
	if (tep_not_null($module) && ($module == 'dropship' || $module == 'upsxml') && $_POST['sid']) {	 
	$title_info = $_SESSION['packages']['packages']['package_count'].' pkg(s) x '.$_SESSION['packages']['packages']['total_weight'].' lbs total';
	//United Parcel Service
    $shipping_cost = $_SESSION['packages']['global']['shipping_total_price'];
	$order->info['shipping_method'] = $method.' ('.$title_info.')';
    $order->info['total']+= $_SESSION['packages']['global']['shipping_total_price'];
    $selected_shipping['title'] = '-'.$method;
    $selected_shipping['cost'] = $_SESSION['shipping_cost'] = $order->info['shipping_cost'] = $shipping_cost;
 	$_SESSION['shipping_method'] = $order->info['shipping_method'];       
    $selected_shipping['id'] = $module.'_'.$module;  
    
	} 

	// calculate shipping models
	if ($chosen && $us_priority_price) {	 
		$_SESSION['packages']['global']['shipping_total_price'] = $us_priority_price;
		$shipping_cost = $us_priority_price; 
		$box_chosen = strip_tags($_POST['us_priority_box_chosen']);
		$method = $box_chosen;
		$title_info = '1 pkg x '.$_SESSION['packages']['packages']['total_weight'].' lbs total';		
		$order->info['shipping_method'] = $method.' ('.$title_info.')';
		
		$selected_shipping['title'] = '-'.$method;
		$order->info['total']+= $shipping_cost;
		$selected_shipping['cost'] = $_SESSION['shipping_cost'] = $order->info['shipping_cost'] = $shipping_cost;
		$_SESSION['shipping_method'] = $order->info['shipping_method']; 
		$selected_shipping['id'] = 'us_priority_us_priority';  
	}

  $shipping_options_array = array();
  
  if (!$cart_sid) {
  	
  	if ($defaults['customers_preference_delivery_method']) {
  		//$cart_sid = strtolower($defaults['customers_preference_delivery_method']);
  		$defaults_array['D'] = 'pickup';
  		$cart_sid_alt = $defaults_array[$defaults['customers_preference_delivery_method']];
  		$cart_sid = $defaults_array[$defaults['customers_preference_delivery_method']];
  		$_SESSION['cart_sid_alt']  = $cart_sid;		
  	} else {  	
  		$cart_sid_alt = 'flat'; // global default
  	}	
  } 
  
  if ($_SERVER['REMOTE_ADDR'] == '71.57.181.208') {	  		
  	//echo ;  
  	//echo $cart_sid;	
  	//$_SESSION['shipping_method'] = $cart_sid;
  }
     
$sanity_check = array(); 

$shipping_option_default = 'Please choose one';
$shipping_options_array[] = array('id' => '100000', 
												  'text' => $shipping_option_default);

for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
	
	for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {		
		
		if (preg_match("/UPS/", $quotes[$i]['methods'][$j]['id'])) {	// numeric would only be fedex	
						
			if (!array_key_exists('upsxml_'.$quotes[$i]['methods'][$j]['id'], $sanity_check)) { // check for duplicates
				$shipping_options_array[] = array('id' => 'upsxml_'.$quotes[$i]['methods'][$j]['id'], 
												  'text' => $quotes[$i]['methods'][$j]['title'].' &nbsp; '.$currencies->format($quotes[$i]['methods'][$j]['cost']));
				$sanity_check['upsxml_'.$quotes[$i]['methods'][$j]['id']] = $quotes[$i]['methods'][$j]['title'];
			$_SESSION['shipping_quotes']['upsxml_'.$quotes[$i]['methods'][$j]['id']] = $quotes[$i]['methods'][$j]['title'];
			}			

			
			
		} else { // not fedex or ups
			//$shipping_options_alt_array[] = array('id' => $quotes[$i]['methods'][$j]['id'], 
			//									  'text' => $quotes[$i]['methods'][$j]['title'].' &nbsp; '.$currencies->format($quotes[$i]['methods'][$j]['cost']));
			//$_SESSION['shipping_quotes'][$quotes[$i]['methods'][$j]['id']] = $quotes[$i]['methods'][$j]['title'];	
		}
	}
	
}

$shipping_options_alt_array[] = array('id' => 'dropship', 
									  'text' => 'Drop Ship');

$shipping_options_alt_array[] = array('id' => 'flat', 
									  'text' => MODULE_SHIPPING_FLAT_TEXT_TITLE);

$shipping_options_alt_array[] = array('id' => 'pickup', 
									  'text' => 'Pickup '.$currencies->format(0));

$_SESSION['shipping_quotes']['dropship'] = $quotes[$i]['methods'][$j]['title'];
$_SESSION['shipping_quotes']['flat'] = $quotes[$i]['methods'][$j]['title'];
$_SESSION['shipping_quotes']['pickup'] = $quotes[$i]['methods'][$j]['title'];

//dropship
$_SESSION['shipping_chosen'] = $cart_sid;

//$shipping_options_alt_array[] = array('id' => 'testing_1', 
//												  'text' => 'Testing');
if (!$_SESSION['shipping_chosen']) {
	$_SESSION['shipping_chosen'] = $cart_sid_alt;
}
$shipping_options_alt = tep_draw_pull_down_menu('sid_alt', $shipping_options_alt_array, $cart_sid_alt, 'id="alt_shipping_menu"').' <span id="loading_shipping_options"><img src="images_extra/ajax_loading.gif" width="16" height="16"></span><br><br>';

$shipping_options = tep_draw_pull_down_menu('sid', $shipping_options_array, $cart_sid, 'id="shipping_menu"');
	
	
?>
<script>
//payment_inputs
$(document).ready(function() {
	var ship_to_dealer = false;
	<?php if ($default_ship_to_dealer || $_SESSION['cart_sid_alt'] == 'flat') { ?>
		ship_to_dealer = true;		
	<?php } ?>
	$('#alt_shipping_menu').change(function() {
		$('#loading_shipping_options').css('display', 'inline');
		shipincart_submit();
	});

	$('#shipping_menu').change(function() {
		if ($('#shipping_menu').val() != 100000) {			
			$('#loading_shipping_rates').css('display', 'inline');
			shipincart_submit();
		}
	});

	if ($('#alt_shipping_menu').val() == 'pickup' || $('#alt_shipping_menu').val() == 'flat') {
		if (!ship_to_dealer) {
			$('#shipping_menu').fadeOut();
		}
		$('#edit_shipping_address_link').fadeOut();		
	}	

$('#checkout_payment').submit(function() {
	if ($('#shipping_price_estimate').val() == '') {
		alert('Please choose a shipping method and click the calculate shipping button');
		$('#alt_shipping_menu_methods').focus();
		$('#alt_shipping_menu_methods').css('border-color', 'red'); 
		 return false;
	} 

	var payment_method_count = parseInt($('input[name="payment_count"]').val());
	var single_payment_method = payment_method_count <= 1;	
	var multiple_payment_method = payment_method_count > 1;
	
	var using_authorize_net = false;
	if (single_payment_method) {
		var using_authorize_net = $('input[name="payment"]').val() == 'authorizenet_aim';
	} else if (multiple_payment_method) {
		var using_authorize_net = $("input[@name=payment]:checked").val() == 'authorizenet_aim';
	}

	if (using_authorize_net) {
		if ($('input[name="authorizenet_aim_cc_number"]').val() == '') {	
			alert('Please enter a credit card number');
			$('input[name="authorizenet_aim_cc_number"]').focus();
			$('input[name="authorizenet_aim_cc_number"]').css('border-color', 'red'); 
			 return false;
		}
		
		if ($('input[name="authorizenet_aim_cc_cvv"]').val() == '') {	
			alert('Please enter a CCV number');
			$('input[name="authorizenet_aim_cc_cvv"]').focus();
			$('input[name="authorizenet_aim_cc_cvv"]').css('border-color', 'red'); 
			 return false;
		}	 
	}
		
});	

//$(window)._scrollable();
//$.scrollTo( '#shipping_section', 800);

<?php if ($_POST['sid']) { ?>
	$('#shipping_price_estimate').focus();
<?php } ?>
});	


</script>
<?php 
//$_SESSION['customer_delivery_method'] = $customer_delivery_method;
?>
 	
		<table border="0" width="100%" cellspacing="1" cellpadding="2">
          <tr >
            <td colspan="2" class="main">
            	<div class="cart_footer_headings" id="shipping_section"><?php echo TITLE_ESTIMATE_SHIPPING; ?></div>            	
            	<strong><?php echo TEXT_PRODUCTS_QUANTITY; ?>: <?php echo $cart->count_contents(); ?></strong>
            </td>
         </tr>
         <tr>
            <td class="main" valign="top">   
            <strong><?php echo TITLE_SHIPPING_ADDRESS; ?></strong>
            <br>  
            <span id="edit_shipping_address_link">      
            <?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, 'editing_shipping=1', 'SSL') . '">' . TEXT_EDIT . '</a>'; ?>
            </span>   
            <br>       
          
          	</td>
			<td class="main" valign="top"><?php echo tep_address_label($customer_id, $sendto, true, ' ', '<br>'); ?></td>
          </tr>
          <tr>
          	<td colspan="2">
  <?php 
  $ShipTxt= tep_draw_form('estimator', tep_href_link(FILENAME_SHOPPING_CART, '#footer', 'SSL'), 'post', 'id="estimator"'); //'onSubmit="return check_form();"'
  $ShipTxt.=tep_draw_hidden_field('sid', $selected_shipping['id']);
  echo $ShipTxt; 
  ?>
 <input type="hidden" name="payment_error_msg" value="">
 <input type="hidden" name="error_msg" value="">
<?php 
//echo $cart_sid.'<br>';
//  echo $_POST['sid_alt'];
//if ($_SERVER['REMOTE_ADDR'] == '71.196.121.196') {			
//	print_r($_POST);
//	echo $cart_sid_alt;
//	echo $shipping_options_alt;
//	exit;
//}
?>    
<?php 

$shipping_options_array = array(
								'100000' => 'Please choose one',
								'upsxml_UPS Ground' => 'UPS Ground',
								'upsxml_UPS 3 Day Select' => 'UPS 3 Day Select',
								//'upsxml_UPS 2nd Day Air A.M.' => 'UPS 2nd Day Air A.M.',
								'upsxml_UPS Next Day Air Saver' => 'UPS Next Day Air Saver',
								'upsxml_UPS Next Day Air' => 'UPS Next Day Air',
								'upsxml_UPS Next Day Air Early A.M.' => 'UPS Next Day Air Early A.M.'
);

// 

if ($usps_rates_2->is_usps_customer() && $cart_sid_alt == 'flat') {
	$shipping_options_array[1000000] = '--------------';
	$shipping_options_array[usps_priority] = 'US Priority Mail';
		?>
		<script type="text/javascript">
		var us_priority_dropdown_seperator = '--------------';
		var us_priority_dropdown_text = 'US Priority Mail'; 
		
		$(function() {
			var shipping_type_selected = $('input[name=ship_type_selection]:checked').val();
			if (shipping_type_selected == 'regular') {			
					//$("#alt_shipping_menu_methods").append('<option value="1000000">'+us_priority_dropdown_seperator+'</option>');
					//$("#alt_shipping_menu_methods").append('<option value="usps_priority">'+us_priority_dropdown_text+'</option>');
				$("#alt_shipping_menu_methods option[value='1000000']").remove();
				$("#alt_shipping_menu_methods option[value='usps_priority']").remove();		
			}
		});
		
		</script>
		
		<?php 	
}


foreach ($shipping_options_array as $k => $v) {	

$shipping_options_static_array[] = array('id' => $k, 
								  'text' => $v);

}
?> 	
<style>
.transparent {
        zoom: 1;
        filter: alpha(opacity=80);
        opacity: 0.8;
}
.non_transparent {
        zoom: 1;
        filter: alpha(opacity=100);
        opacity: 1;
}
</style> 
<div id="us_priority_rates_bg"  class="transparent" id="us_priority_bg_wrapper" style="display: none; position: absolute; top: 0; left: 0; z-index: 100; width: 100%; height: 100%; background-color: #ccc; text-align: center; border: solid 5px #999" >&nbsp;</div>
<div id="us_priority_rates" style="z-index: 101; display: none; position: absolute; top: 0; left: 0; text-align: center;">
<?php 
$load_app_top = false;
include "usps_rates_popup.php"; ?>
</div> 

<input type="hidden" name="us_priority" value="0">
<script type="text/javascript">


$(function() {

	var position_valign = {
		      sTop : function() {
		        return window.pageYOffset
		        || document.documentElement && document.documentElement.scrollTop
		        ||  document.body.scrollTop;
		      },
		      wHeight : function() {
		        return window.innerHeight
		        || document.documentElement && document.documentElement.clientHeight
		        || document.body.clientHeight;
		      }
		    };

	 $('.shipping_type').click(function(){		 
		 $('#calculate_shipping_wrapper').slideDown();
		 $('select#alt_shipping_menu_methods').val(100000);		
		 $('#shipping_price_estimate').val('');

		if ($(this).val() != 'economy') {
			$("#alt_shipping_menu_methods option[value='1000000']").remove();
			$("#alt_shipping_menu_methods option[value='usps_priority']").remove();			
		} else {
			$("#alt_shipping_menu_methods option[value='1000000']").remove();
			$("#alt_shipping_menu_methods option[value='usps_priority']").remove();		
			$("#alt_shipping_menu_methods").append('<option value="1000000">'+us_priority_dropdown_seperator+'</option>');
			$("#alt_shipping_menu_methods").append('<option value="usps_priority">'+us_priority_dropdown_text+'</option>');
		}		 		
	 });

	
	$('#button_cancel').click(function() {
		$("#us_priority_rates").fadeOut();;
		$("#us_priority_rates_bg").fadeOut();;   
	});

	$('input[name=usps_choice]').click(function() {		
		$("#us_priority_rates").fadeOut();
		$("#us_priority_rates_bg").fadeOut();	
		document.forms['estimator'].us_priority.value = $(this).val();	
		$('#loading_shipping_rates').css('display', 'inline');
		$('input[name=us_priority_box_chosen]').val($(this).attr('title'));
		setTimeout(function() { submit_estimator(); },500);				
	});

	
	 $('#calculate_shipping_button').click(function(e){
		e.preventDefault();

		// check the shipping method drop down	
		var shipping_methods_value = $('select#alt_shipping_menu_methods option:selected').val();	
		if (shipping_methods_value == '100000' || shipping_methods_value == '1000000') {
			alert('Please choose a shipping method');
			$('#alt_shipping_menu_methods').focus();
			$('#alt_shipping_menu_methods').css('border-color', 'red');
			return false;
	    }
		
		// check the radio buttons
	    if ($('#ship_type_1').attr('checked')!==true && $('#ship_type_2').attr('checked')!==true) {
	        alert('Please select a type of shipping');
	        $('.ship_type_cart').css('color', 'red');
	        $('.ship_type_cart').css('border-color', 'red');	        
	        return false; 
	     }	 

		 // US Priority Mail 
	    if (shipping_methods_value == 'usps_priority') {
		    
		    var p = $("#calculate_shipping_button");
		    var position = p.position();		    
		   // $("#us_priority_rates").css( {"left": position.left+'px', "top": (position.top-500)+'px'} );
		    $('#us_priority_rates_bg').css({width: '2000px', height: '2000px'});
		    var myElement = $('#us_priority_rates');
	    	myElement.css({
	    	    position: 'absolute', 
	    	    left: '50%',
	    	    'margin-left': 0 - (myElement.width() / 2)
	    	});	

	        var elHeight = myElement.height();
	        var elTop = position_valign.sTop() + (position_valign.wHeight() / 2) - (elHeight / 2);
	        myElement.css({
	          marginTop: '0',
	          top: elTop
	        });	    	
	    	
			$("#us_priority_rates").fadeIn();
			$("#us_priority_rates_bg").fadeIn();
			return;
		}
	    $('#loading_shipping_rates').css('display', 'inline');
	    submit_estimator(); 
		//$('#loading_shipping_rates').css('display', 'inline');
	    //$('#estimator').submit(); 
	 });

});



function submit_estimator() {
    $('#estimator').submit(); 	
}
</script>
<input type="hidden" name="us_priority_box_chosen" value="">
   <?php echo $shipping_options_alt; ?> 
   <?php 
   $show_ups_methods = false;
   if ($cart_sid_alt == 'dropship') {
   		$show_ups_methods = true;
   }
   
   if (($customer_delivery_method != 'D' || $show_ups_methods) && $cart_sid_alt != 'pickup') { ?>
   <p>
   <span class="ship_type_cart_radio"><?php echo tep_draw_radio_field('ship_type_selection', 'economy', '', 'id="ship_type_2" class="shipping_type"'); ?></span><span class="ship_type_cart"><?php echo TEXT_ECONOMY_SHIP; ?></span><br style="clear: both; margin-bottom: 20px;">
   <span class="ship_type_cart_radio"><?php echo tep_draw_radio_field('ship_type_selection', 'regular', '', 'id="ship_type_1" class="shipping_type"'); ?></span> <span class="ship_type_cart"><?php echo TEXT_REGULAR_SHIP; ?></span>
   </p><br> 
   <div id="calculate_shipping_wrapper">
   <?php 
   if ($_SESSION['testing']) {
   echo 'Show Xml: '. tep_draw_checkbox_field('show_xml');
   }
   ?><br>
   <span style="float: right">
   <?php   
   $rand = mt_rand(10000, 1000000);   
   $shipping_exists = $_SESSION['packages']['global']['shipping_total_price'] != '';
   $shipping_final_price_estimate = ($shipping_exists) ? $currencies->format($_SESSION['packages']['global']['shipping_total_price']) : '';
   echo tep_draw_input_field('shipping_price_estimate['.$rand.']', $shipping_final_price_estimate, 'AUTOCOMPLETE=OFF style="width: 50px;" id="shipping_price_estimate"'); ?>
   </span>
   <span style="float: left">
   <?php echo tep_draw_pull_down_menu('sid', $shipping_options_static_array, $sid_def, 'id="alt_shipping_menu_methods"'); //echo $shipping_options; ?> <span id="loading_shipping_rates"><img src="images_extra/ajax_loading.gif" width="16" height="16"></span>
   </span>
    <br style="clear: both">
   <?php echo tep_image_submit('button_calculate_shipping.gif', IMAGE_BUTTON_CALCULATE_SHIPPING, 'id="calculate_shipping_button"'); ?>
   <?php } ?>
   </div>
    
   <script type="text/javascript">
   <?php if (!$ship_type_selection) { ?>
	$('#calculate_shipping_wrapper').hide();
	<?php  } ?>
   $('#loading_shipping_options').css('display', 'none');
   $('#loading_shipping_rates').css('display', 'none');
   </script>
   <?php //echo substr($_SESSION['rate_type'], 0, 1); ?>
   </form>
          	</td> 
          </tr>
        </table>        		
        		<br><?php //if (CARTSHIP_ONOFF == 'Enabled') { require(DIR_WS_MODULES . 'shipping_estimator.php'); } else {}; ?>
        		</td>
        		<td valign="top" width="33%"> 
<?php 
        		// if no billing destination address was selected, use the customers own address as default
  if (!tep_session_is_registered('billto')) {
    tep_session_register('billto');
      
    if(tep_address_label($customer_id, $customer_default_address_id, false, '', '') == ", ") $messageStack->add('billing', "Please provide billing address");
    else 
    $billto = $customer_default_address_id;

  } else {
// verify the selected billing address
    $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$billto . "'");
    $check_address = tep_db_fetch_array($check_address_query);
  
    if ($check_address['total'] != '1') {
     
      if(tep_address_label($customer_id, $customer_default_address_id, false, '', '') == ", ") $messageStack->add('billing', "Please provide billing address");
      else 
      $billto = $customer_default_address_id;
      

      if (tep_session_is_registered('payment')) tep_session_unregister('payment');
    }
  }
?>        		
        		
        <table border="0" width="100%" cellspacing="1" cellpadding="2">
          <tr>
            <td class="main" colspan="3"  class="cart_footer_content">
            <div class="cart_footer_headings"><?php echo TITLE_BILL_TO_ADDRESS; ?></div>      
    		<strong><?php echo TITLE_BILLING_ADDRESS; ?></strong>
    		</td>
    		</tr>
    		<tr>
    		<td class="main" valign="top">
				<?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . TEXT_EDIT . '</a>'; ?>
			</td>
                
			<td class="main">
                    <td class="main" valign="top"><?php echo tep_address_label($customer_id, $billto, true, ' ', '<br>'); ?></td>
                   
</td>
          </tr>
        </table>        		
        		<br>
        		</td>
        		<td valign="top" class="main" width="33%">
        			<table border="0" width="100%" cellspacing="1" cellpadding="2">
			          <tr>
			            <td class="main" colspan="3">
			        		<div class="cart_footer_headings"><?php echo TITLE_PAYMENT_METHOD; ?></div>   
			        		  <?php //echo TITLE_PAYMENT_METHOD_INVOICE; ?>
			        		 
<?php 
// load all enabled payment modules
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;
?>
   <?php 
    //echo tep_draw_form('checkout_payment', tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'onsubmit="return check_invoice(checkout_payment);"'); 
    
    echo tep_draw_form('checkout_payment', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'), 'post', 'id="checkout_payment"'); 
    echo tep_draw_hidden_field('sid', $selected_shipping['id']);
    ?>      
<table>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" >
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  $selection = $payment_modules->selection();

 
  $radio_buttons = 0;
  for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
  	
?>
              <tr>               
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php

if ($radio_buttons >= 1) {
	?>
	<tr>
		<td colspan="5" align="center">
			<b><?php echo TITLE_PAYMENT_METHOD_OR; ?></b>
		</td>
	</td>
	
	<?php 	
}
 
if ($selection[$i]['title_visible'] != '') {
	$module_title = $selection[$i]['title_visible'];	
} else {
	$module_title = $selection[$i]['module'];
}


if (!$first_module) {
	// check for defaults
	if ($defaults['customers_preference_payment_method']) {
		$first_module = strtolower($defaults['customers_preference_payment_method']);	
	} else {
		$first_module = strtolower(str_replace('.', '', $selection[$i]['module']));	
	}
}


$selection[$i]['module'] = str_replace(' ', '_', $selection[$i]['module']);
    if ( ($selection[$i]['id'] == $payment) || ($n == 1) || !$payment) {
      echo '                  <tr id="payment_inputs_'.strtolower(str_replace('.', '', $selection[$i]['module'])).'" class="moduleRowSelected radiobutton" >' . "\n";
    } else {
      echo '                  <tr id="payment_inputs_'.strtolower(str_replace('.', '', $selection[$i]['module'])).'" class="moduleRow radiobutton">' . "\n";
    }
?>
                    <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    <td id="cell_title_<?php echo strtolower(str_replace('.', '', $selection[$i]['module'])); ?>" class="main" colspan="3"><b><?php echo $module_title; ?></b></td>
                    <td class="main" align="right">
<?php
	$selected_radio = false;
    if (sizeof($selection) > 1) {
    	if (!$chosen_radio) {
      		echo tep_draw_radio_field('payment', $selection[$i]['id'], 1, 'id="payment_inputs_'.strtolower(str_replace('.', '', $selection[$i]['module'])).'_radio"');
    	} else {
    		echo tep_draw_radio_field('payment', $selection[$i]['id'], 0, 'id="payment_inputs_'.strtolower(str_replace('.', '', $selection[$i]['module'])).'_radio"');	
    	}      
      $chosen_radio = 1;
    } else {
      
      echo tep_draw_hidden_field('payment', $selection[$i]['id']);
    }
    // this tells us if we have radio buttons to choose payment method or if we are using just a hidden variable
    echo tep_draw_hidden_field('payment_count', sizeof($selection));
?>
                    </td>
                    <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                  </tr>
<?php
    if (isset($selection[$i]['error'])) {
?>
                  <tr>
                    <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    <td class="main" colspan="4"><?php echo $selection[$i]['error']; ?></td>
                    <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                  </tr>
<?php
    } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
?>
                  <tr>
                    <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    <td colspan="4">
                    <table  class="payment_inputs" border="0" cellspacing="0" cellpadding="2" id="payment_inputs_<?php echo strtolower(str_replace('.', '', $selection[$i]['module'])); ?>_cell">
<?php
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>
                      <tr>
                        
                        <td class="main"><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
                        <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main"><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
                        
                      </tr>
<?php
      }
?>
                    </table></td>
                  
                  </tr>
<?php
    }
?>
                </table></td>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
<?php
    $radio_buttons++;
  }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
</table>

</form>


<script>
//payment_inputs
$(document).ready(function() {

	$('.payment_inputs').hide();
	<?php 
	$payment = str_replace(' ', '_', $payment);
	
	//if (!$payment) {
		//$payment = 'invoice';	
	//}
	$first_module = str_replace(' ', '_', $first_module);
	?>
	$('#payment_inputs_<?php echo $first_module; ?>_cell').show();
	$('#payment_inputs_credit_card_cell').show();
	$('#payment_inputs_<?php echo $first_module; ?>_radio').attr('checked', 'checked');	
	$('#cell_title_authorizenet').html('<b><?php echo TITLE_PAYMENT_METHOD_CREDIT_CARD_2; ?></b>');
	$('#cell_title_credit_card').html('<b><?php echo TITLE_PAYMENT_METHOD_CREDIT_CARD_2; ?></b>');

	$('.radiobutton').click(function() {
		$('.payment_inputs').hide();	
		$('#'+$(this).attr('id')+'_cell').show();
		
	});

	$('#checkout_button').click(function() {
		$('#checkout_payment').submit();
	});

	<?php 
	 if ($cart->count_contents() < 1) {
	 	?>
	 	$('.cart_one_page_checkout').hide();	
	 	$('#cart_order_total').hide();	
	 	$('#cart_order_checkout_button').hide();
	 	<?php 
	 }	
	?>

	<?php if (!$_SESSION['shipping_method'] && !$_POST['sid_alt'] && !$_SESSION['cart_sid_alt'] && $cart->count_contents() >= 1) { ?>
	//document.forms['estimator'].sid_alt.value = 'flat';
	//document.forms['estimator'].submit(); 
	<?php } ?>

	<?php if ($customer_delivery_method == 'D' && $cart_sid_alt == 'flat' && !$_POST['sid_alt']) { ?>
	//document.forms['estimator'].sid_alt.value = 'flat';
	//document.forms['estimator'].submit();		
	<?php } ?>
	<?php if ($customer_delivery_method == 'P' && !$_SESSION['cart_visited']) { ?>
	document.forms['estimator'].sid_alt.value = 'pickup';
	document.forms['estimator'].payment_error_msg.value = '<?php echo $_GET['payment_error']; ?>';
	document.forms['estimator'].error_msg.value = '<?php echo $_GET['error_message']; ?>';
	document.forms['estimator'].submit();		
	<?php } ?>
	
	<?php if ($customer_delivery_method == 'D' && !$_SESSION['cart_visited']) { ?>
		document.forms['estimator'].sid_alt.value = 'flat';
		document.forms['estimator'].payment_error_msg.value = '<?php echo $_GET['payment_error']; ?>';
		document.forms['estimator'].error_msg.value = '<?php echo $_GET['error_message']; ?>';
		document.forms['estimator'].submit();		
	<?php } ?>
	
	
});

</script>
<?php 
require_once 'HyperPublishing/configuration.php';
// update publications to make sure the tables are populated correctly
// asa web addin 6/3/11
$data_query = tep_db_query("select publication_id, publication_product_type, product_id, date_created from publication where output_request_path = ''");

		while ($data = tep_db_fetch_array($data_query)){
			
			$date = str_replace('-', '', $data['date_created']);
			
			$output_path = ALT_OUTPUT_PATH.$date.'/'.$data['product_id'].'_Output.pdf';
			tep_db_query("update publication set output_request_path = '".$output_path."' 
						  where publication_id = '".$data['publication_id']."' ");
			
		} 
		$data_query = tep_db_query("select publication_id, publication_product_type, product_id, date_created from publication where output_request_url = ''");

		while ($data = tep_db_fetch_array($data_query)){
			//print_r($data);
			$date = str_replace('-', '', $data['date_created']);
			
			$hyper_url = HYPERRENDER_URL; 
			$template_name = '?Template_Name='.urlencode(DOC_POOL_CHILD.DOC_POOL_CART.$data['product_id'].'_Output.qxp');			
			$output_type = '&P_Output_Type=stat';
			$file_output = '&P_File_Output=Y';
			$file_output_type = '&P_File_Output_Type=.pdf';
			$output_path = '&P_Path_Output='.urlencode(ALT_OUTPUT_PATH.$date);
			$output_name = '&P_Name_Output='.$data['product_id'].'_Output';
			$output_style = '&PDF_Output_Style=';
			
			$output_request_url = $hyper_url;
			$output_request_url .= $template_name;
			$output_request_url .= $output_type;
			$output_request_url .= $file_output;
			$output_request_url .= $file_output_type;
			$output_request_url .= $output_path;
			$output_request_url .= $output_name;
			$output_request_url .= $output_style;
			 
		tep_db_query("update publication set output_request_url = '".$output_request_url."' 
						  where publication_id = '".$data['publication_id']."' ");
			
		}
		// end update publications to make sure the tables are populated correctly
		
?>
</td>
        			</tr>
        		</table>
        		</td>
        	</tr>
        </table>
<?php
  if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
?>
<br>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="headerError">
    <td class="headerError"><?php echo htmlspecialchars(urldecode(strip_tags($_GET['error_message']))); ?></td>
  </tr>
</table>
<?php
  }  
  ?>
        
       
        </td>
      </tr>
      
     <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td> 
        <table id="cart_order_total" border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td align="right" class="main">
             <?php 
			    // EOF get taxes if not logged in (seems like less code than in order class)
			    require_once(DIR_WS_CLASSES . 'order_total.php');
			    $order_total_modules = new order_total;
			    //echo '</td><td align="right">';
			    // order total code
			    $order_total_modules->process();
			    $otTxt='<table align="right">';
			    $otTxt.=$order_total_modules->output().'</table>';
				echo $otTxt;				
			    ?>
            </td>
          </tr>
        </table></td>
      </tr>

     <tr>
        <td align="right"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
      	<td>
      	</td>
      </tr>
      
      <tr>
        <td><table  id="cart_order_checkout_button" border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main"><?php echo tep_image_submit('button_update_cart.gif', IMAGE_BUTTON_UPDATE_CART); ?></td>
<?php
    $back = sizeof($navigation->path)-2;
    if (isset($navigation->path[$back])) {
?>
                <!--
                <td class="main"><?php echo '<a href="' . tep_href_link($navigation->path[$back]['page'], tep_array_to_string($navigation->path[$back]['get'], array('action')), $navigation->path[$back]['mode']) . '">' . tep_image_button('button_continue_shopping.gif', IMAGE_BUTTON_CONTINUE_SHOPPING) . '</a>'; ?></td>
                -->
                <?php /* GLUON Code Starts */ ?>
                <?php /* modified RP 2-11-13 */ ?>
                <?php //echo '<a href="' . tep_href_link(FILENAME_WORKSPACE) . '">' . tep_image_button('button_back_workspace_big.gif', IMAGE_BUTTON_CONTINUE_SHOPPING) . '</a>'; ?>
                <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_CONTINUE_SHOPPING) . '">' . tep_image_button('button_continue_shopping.gif', IMAGE_BUTTON_CONTINUE_SHOPPING) . '</a>'; ?></td>
				<?php /* GLUON Code Ends */ ?>
<?php
    }
?>
                <td align="right" class="main"><?php echo tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT, 'id="checkout_button"'); ?>
                <?php //echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL') . '">' . tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT) . '</a>'; ?></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table>
        </td>
      </tr>            
    </table></form>
    

    
    </td> 
<!-- body_text_eof //-->
    <!--
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
    -->
<!-- right_navigation //-->
<?php //require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    <!--
    </table></td>
    -->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
