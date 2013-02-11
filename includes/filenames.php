<?php
/*
  filenames.php,v 1.4 2003/06/11 17:38:00 hpdl Exp 

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

// look at end of file for BCE additions
// define the filenames used in the project
  define('FILENAME_ACCOUNT', 'account.php');
  define('FILENAME_ACCOUNT_EDIT', 'account_edit.php');
  define('FILENAME_ACCOUNT_HISTORY', 'account_history.php');
  define('FILENAME_ACCOUNT_HISTORY_INFO', 'account_history_info.php');
  define('FILENAME_ACCOUNT_NEWSLETTERS', 'account_newsletters.php');
  define('FILENAME_ACCOUNT_NOTIFICATIONS', 'account_notifications.php');
  define('FILENAME_ACCOUNT_PASSWORD', 'account_password.php');
  define('FILENAME_ADDRESS_BOOK', 'address_book.php');
  define('FILENAME_ADDRESS_BOOK_PROCESS', 'address_book_process.php');
  define('FILENAME_ADVANCED_SEARCH', 'advanced_search.php');
  define('FILENAME_ADVANCED_SEARCH_RESULT', 'advanced_search_result.php');
  define('FILENAME_ALSO_PURCHASED_PRODUCTS', 'also_purchased_products.php');
  define('FILENAME_CHECKOUT_CONFIRMATION', 'checkout_confirmation.php');
  define('FILENAME_CHECKOUT_PAYMENT', 'checkout_payment.php');
  define('FILENAME_CHECKOUT_PAYMENT_ADDRESS', 'checkout_payment_address.php');
  define('FILENAME_CHECKOUT_PROCESS', 'checkout_process.php');
  define('FILENAME_CHECKOUT_SHIPPING', 'checkout_shipping.php');
  define('FILENAME_CHECKOUT_SHIPPING_ADDRESS', 'checkout_shipping_address.php');
  define('FILENAME_CHECKOUT_SUCCESS', 'checkout_success.php');
  define('FILENAME_CONTACT_US', 'contact_us.php');
  define('FILENAME_CONDITIONS', 'conditions.php');
  define('FILENAME_COOKIE_USAGE', 'cookie_usage.php');
  define('FILENAME_CREATE_ACCOUNT', 'create_account.php');
  /* GLUON Code Starts */
  define('FILENAME_CREATE_ACCOUNT_INVITE', 'create_account_invite.php');
  define('FILENAME_CREATE_ACCOUNT_SUCCESS', 'create_account_success.php');
  define('FILENAME_CREATE_ACCOUNT_INVITE_SUCCESS', 'create_account_invite_success.php');
  define('FILENAME_WORKSPACE', 'workspace.php');
  define('FILENAME_WORKSPACE_ORDER_HISTORY', 'workspace_order_history.php');
  define('FILENAME_WORKSPACE_STATIC', 'workspace_static.php');
  define('FILENAME_UPLOAD_DETAILS', 'upload_details.php');
  define('FILENAME_UPLOAD_PROCESS', 'upload_process.php');
  define('FILENAME_UPLOAD_SUCCESS', 'upload_success.php');
  
  /* GLUON Code Ends */
  define('FILENAME_DEFAULT', 'index.php');
  define('FILENAME_DOWNLOAD', 'download.php');
  define('FILENAME_INFO_SHOPPING_CART', 'info_shopping_cart.php');
  define('FILENAME_LOGIN', 'login.php');
  define('FILENAME_LOGOFF', 'logoff.php');
  define('FILENAME_NEW_PRODUCTS', 'new_products.php');
  define('FILENAME_PASSWORD_FORGOTTEN', 'password_forgotten.php');
  define('FILENAME_POPUP_IMAGE', 'popup_image.php');
  define('FILENAME_POPUP_SEARCH_HELP', 'popup_search_help.php');
  define('FILENAME_PRIVACY', 'privacy.php');
  define('FILENAME_PRODUCT_INFO', 'product_info.php');
  define('FILENAME_PRODUCT_INFO_AJAX', 'product_info_ajax.php');
  define('FILENAME_PRODUCT_LISTING', 'product_listing.php');
  define('FILENAME_CATEGORY_LISTING', 'category_listing.php');
  define('FILENAME_PRODUCT_REVIEWS', 'product_reviews.php');
  define('FILENAME_PRODUCT_REVIEWS_INFO', 'product_reviews_info.php');
  define('FILENAME_PRODUCT_REVIEWS_WRITE', 'product_reviews_write.php');
  define('FILENAME_PRODUCTS_NEW', 'products_new.php');
  define('FILENAME_REDIRECT', 'redirect.php');
  define('FILENAME_REVIEWS', 'reviews.php');
  define('FILENAME_SHIPPING', 'shipping.php');
  define('FILENAME_SHOPPING_CART', 'shopping_cart.php');
  define('FILENAME_SHOPPING_CART_V2', 'shopping_cart_v2.php');
  define('FILENAME_SPECIALS', 'specials.php');
  define('FILENAME_SSL_CHECK', 'ssl_check.php');
  define('FILENAME_TELL_A_FRIEND', 'tell_a_friend.php');
  define('FILENAME_UPCOMING_PRODUCTS', 'upcoming_products.php');
  define('FILENAME_POPUP_CVV_HELP', 'cvv_help.php');
   
  /* GLUON Code Starts */
  define('FILENAME_ENTRY', 'entry.php'); 
	
  define('FILENAME_EDIT_PRODUCTION', 'batch_production.php');  
  define('FILENAME_LIVE_PRODUCTION', 'live_production.php');  
  define('FILENAME_WORKSPACES_GROUP', 'workspaces_group.php');
  define('FILENAME_CUSTOM_PRODUCTION', 'custom_production.php');
  define('FILENAME_HPS_PRODUCTION', 'hps_production.php');  
  define('FILENAME_HPS_APPROVAL', 'hps_approval.php');  
  define('FILENAME_HPS_ADMIN_APPROVAL', 'hps_admin_approval.php');  

  //HyperPublishing links
  define('HYPER_PUBLISHING_URL', 'http://' . $_SERVER['SERVER_NAME'] . '/bceonline/HyperPublishing/');

  define('FILENAME_BATCH_PRODUCTION','batch_production.php');
  define('FILENAME_PREVIEW_PRODUCTION','preview_production.php');
  define('FILENAME_POP_PREVIEW_PRODUCTION','pop_preview_production.php');
  define('FILENAME_DIRECT_PRODUCTION','direct_production.php');
  define('FILENAME_SAVE_PRODUCTION', 'save_production.php');
  define('FILENAME_WORKSPACE_CONFIRM','workspace_confirm.php');
  define('FILENAME_APPROVAL_PRODUCTION','approval_production.php');
  
  define('FILENAME_RECIPIENT_EMAIL','recipient_email.php');
  define('FILENAME_RECIPIENT_INFO','recipient_info.php');
  define('FILENAME_PRODUCTS_DETAILS','product_details.php');
  define('FILENAME_PRODUCTS_DETAILS_QUANTITY_APPROVAL','product_details_qa.php');
  define('FILENAME_ORDER_APPROVAL_EMAIL','order_approval_email.php');
  define('FILENAME_ORDER_APPROVAL','order_approval.php'); 
  define('FILENAME_ORDER_APPROVAL_LIST','order_approval_list.php');

  define('FILENAME_DOWNLOAD_PRODUCTION', 'download_production.php');
  define('FILENAME_ADMIN_PRODUCTION','admin_production.php');
  define('FILENAME_CLIP_ART_GALLERY', 'image_gallery.php');
  define('FILENAME_CLIP_ART_GALLERY_RESULTS', 'image_gallery_results.php');
  define('FILENAME_CHOOSE_IMAGE_SOURCE', 'choose_image_source_type.php');
  /* GLUON Code Ends */
  
  define('FILENAME_UPLOAD_FILES', 'upload_files.php');
  define('FILENAME_SHIPPING_ESTIMATOR', 'shipping_estimator.php');
  define('FILENAME_CUSTOM_PAGE', 'template.php'); 
  
  /* BCE Code Starts */
  define('FILENAME_DEALER_NEWS', 'dealer_news.php');
  define('FILENAME_BECOME_A_DEALER', 'become_a_dealer.php'); 
  define('FILENAME_BECOME_A_DEALER_SUCCESS', 'become_a_dealer_success.php'); 
  define('FILENAME_BARCODE', 'barcode.php'); 
  define('FILENAME_ASA_PACKAGES', 'asa_packages.php');
  define('FILENAME_ASA_PACKAGES_REGULAR', 'asa_packages_regular.php');
  define('FILENAME_ASA_PACKAGES_ECONOMY', 'asa_packages_economy.php');
  define('FILENAME_PRICE_CALCULATOR', 'price_calculator.php');
  define('FILENAME_FEEDBACK', 'feedback.php');
  define('FILENAME_REQUESTS', 'requests.php');
  define('FILENAME_AFT_INVOICES', 'aftinvoices.php');
  define('FILENAME_EDDM', 'eddm.php');
  define('FILENAME_BILLING_HISTORY', 'account_advanced_search.php');
  define('FILENAME_BILLING_HISTORY_TRACKING', 'account_advanced_search_tracking.php');
  define('FILENAME_USPS_RATES_POPUP', 'usps_rates_popup.php');
  define('FILENAME_ASA_HTML_UPLOAD', 'asa_html_upload.php');
  define('FILENAME_NO_FLASH', 'no_flash.php');
  define('FILENAME_NO_FLASH_NOTICE', 'no_flash_notice.php');
  define('FILENAME_DETECT_FLASH', 'detect_flash.php');
  define('FILENAME_UPLOADIFY_CODE_JS', 'uploadify_code_js.php');
  define('FILENAME_UPLOAD_HELPER', 'upload_helper.php');
  define('FILENAME_UPLOAD_FILES_V3', 'upload_files_V3.php');
  define('FILENAME_REORDER_FORM', 'reorder_form.php');
  define('FILENAME_REQUEST_ENVELOPE_FORM', 'request_envelope_pricing.php');
  define('FILENAME_TEAM_ADVICE', 'team_advice.php');
  define('FILENAME_HELP_CENTER', 'help_center.php');
  define('FILENAME_HELP_CENTER2', 'help_center2.php');
  define('FILENAME_HELP_CENTER8', 'help_center8.php');
  define('FILENAME_HELP_CENTER9', 'help_center9.php');
  define('FILENAME_HELP_CENTER10', 'help_center10.php');
  define('FILENAME_HELP_CENTER11', 'help_center11.php');
  define('FILENAME_HELP_CENTER13', 'help_center13.php');
  define('FILENAME_HELP_CENTER16', 'help_center16.php');
  define('FILENAME_HELP_CENTER17', 'help_center17.php');
  define('FILENAME_HELP_CENTER18', 'help_center18.php');
  define('FILENAME_PROCESS_STATUS', 'process-status.php');
  define('FILENAME_CONTINUE_SHOPPING', 'index.php');
