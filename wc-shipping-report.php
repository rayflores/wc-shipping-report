<?php
/* Plugin Name:  WooCommerce Shipping Report 
*/

/*File For Woocommerce Shipping Report*/

function register_my_custom_submenu_page() {
    add_submenu_page( 'woocommerce', 'Reports', 'Reports', 'manage_options', 'report', 'my_custom_submenu_page_callback' ); 
}
function my_custom_submenu_page_callback() {
	
	/*for month and year*/
	
	  
	/*ends m&y*/
	
	$report_provider = array(
		'UPS OHIO' => 'ohio-ups',
		'UPS AZ' => 'az-ups',
		'USPS OHIO' => 'ohio-usps',
		'USPS AZ' => 'az-usps',
		'FEDEX OHIO' => 'fedex-ohio',
		'FEDEX AZ' => 'fedex-az',
		'GSO' => 'gso'
	);
    echo '<h3>Report</h3><br>';
	echo '<form id="pro" method="get" action=""><div class=post-filters">
	<input type="hidden" name="page" value="report">';
  echo '<input type="date" placeholder="From Date" id="dfatepicker" name="fdate" class="datepicker"  value="'.$_GET['fdate'].'" />';
   echo '<input type="date" id="tdatepicker" placeholder="To Date" name="tdate" class="datepicker" value="'.$_GET['tdate'].'" />';
   
  
   
    echo'<select style="text-transform:uppercase;vertical-align:top;" name="provider">';
	echo '	<option value="">All</option>';	
	foreach($report_provider as $key=>$value){
		echo '	<option ';
		if( $_GET["provider"] == $value )
		{
			echo 'selected="selected"';
		}
		echo' value="'.$value.'">'.$value.'</option>';	
	}	
	echo '</select>	';

	echo'<input name="filter_action" id="post-query-submit" class="button" value="Filter" type="submit">
</div></form>';	
	echo '<div class="wrap"><table class="wp-list-table widefat fixed striped pages sprovider">';
		echo '<tr>
				<th><h3>Shipping Zones</h3></th>
				<th><h3>Order Shipped</h3></th>
				<th align="right"><h3 style="text-align:right;padding-right:10px">Total</h3></th>
			</tr>';		
			
	$cnt=1;
	//echo $_GET['provider'];
	$fdate = $_GET['fdate'];
	$tdate = $_GET['tdate'];
	$csv_data = array();
	$csv_data[] = array('Shipping Zone','No of Order','Total');
	foreach($report_provider as $key=>$value){
		
		$metaQuery = array();
		if(isset($_GET['provider']) && $_GET['provider']!='' && in_array($_GET['provider'],$report_provider)) {
			
			$value = $_GET['provider'];
			$key = array_search($value, $report_provider);
			
		}
		
echo '<pre>';		
$tracking_items =  get_post_meta(41658,'_wc_shipment_tracking_items', true);
print_r(date('m-d-y', 1501545600) );
echo '<br/>';
$fdate = date('m-d-y', strtotime($_GET['fdate']) );
print_r($fdate);
echo '</pre>';
		$filters = array(
    		'post_status' => 'any',
    		'post_type' => 'shop_order',
    		'posts_per_page' => -1,
    		'paged' => 1,
    		'orderby' => 'ID',
    		'order' => 'DESC',
			'meta_query' => array(
				array(
					'key' => '_wc_shipment_tracking_items',
					'value' => '"'.$value.'"',
			 		'compare' => 'LIKE'  
			 		//'type' => 'CHAR'
				),
				
				
				
			
   		 )
			//'date_query' =>
		);
		$cntt=0;
		$loop = new WP_Query($filters);

		/**/
		$price = 0;
		$order_array=array();
		if($loop->have_posts() ) :
		echo 'have posts';
			while ($loop->have_posts()) {
				$loop->the_post();
				$order = new WC_Order($loop->post->ID);

				$date_from_check = get_post_meta($order->id,'_wc_shipment_tracking_items',true);
				$shipdate = $date_from_check[0]['date_shipped'];
			if((isset($_GET['fdate']) && $_GET['fdate'] != '') || (isset($_GET['tdate']) && $_GET['tdate'] != '')){
				if(isset($_GET['fdate']) && $_GET['fdate'] != '') {
					$fdate = strtotime($_GET['fdate']);
					if($shipdate >= $fdate ){
						if(isset($_GET['tdate']) && $_GET['tdate'] != ''){
							$tdate = strtotime($_GET['tdate']);
							if($shipdate <= $tdate ){
								$order_array[] = array('order' => $order,'shipdate'=>$shipdate);
								$price = $price + $order->get_total();
								$cntt++;
							}
						}
						else{
							$order_array[] = array('order' => $order,'shipdate'=>$shipdate);
							$price = $price + $order->get_total();
							$cntt++;
						}
					}
					else{
						if($shipdate >= $fdate ){
							$order_array[] = array('order' => $order,'shipdate'=>$shipdate);
							$price = $price + $order->get_total();
							$cntt++;
						}
					}
				}
				elseif(isset($_GET['tdate']) && $_GET['tdate'] != ''){
					$tdate = strtotime($_GET['tdate']);
					if($shipdate <= $tdate ){
						$order_array[] = array('order' => $order,'shipdate'=>$shipdate);
						$price = $price + $order->get_total();
						$cntt++;
					}
				}
				}
				else{
					
					$order_array[] = array('order' => $order,'shipdate'=>$shipdate);
					$price = $price + $order->get_total();
					$cntt++;
				}						
		}
		endif;
		$csv_data[] = array($key,$cntt,$price);
		echo '<tr class="main"><td>';
		if($cntt > 0){
			echo '<a data="block'.$cnt.'" class="toggle"><label style="vertical-align: top;"><span class="dashicons dashicons-arrow-down"></span>';
			$csv_data[] = array('Order#','Shipping Date','Total');
		}
		else{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		echo $key.'</label></a></td>';
		
		if($cntt > 0){
			echo '<td>'.$cntt.'</td>';		
		}
		else{
			echo '<td>No Orders So Far</td>';		
		}
			echo '<td align="right" style="padding-right:20px;"> '.wc_price($price).'</td>';
		
			echo '</tr>';
			echo '<tr id="block'.$cnt.'" style="display:none">';
			echo '<td colspan="3">';
			echo '<table style="width:100%">';
			echo '<tr style="background:#eee">';
			echo '<th>Order Number</th>';
			echo '<th>Shipped Date</th>';
			echo '<th>Edit</th>';
			echo '<th align="right" style="text-align: right;">Total</th>';
			echo '</tr>';

		foreach($order_array as $oid){
				//print_r($oid);
		//$billing_address = $oid->get_billing_address();
	//echo $billing_address_html = $oid->get_formatted_billing_address(); // for printing or displaying on web page
	//number,date,price and link
			
			echo '<tr>';
			echo '<td>#';
			echo  $oid['order']->get_order_number().'</td>';
			echo '<td>';
			echo date('d-m-Y',$oid['shipdate']).'</td>';			
			echo '<td> <a target="_blank" href="'.get_edit_post_link($oid['order']->id).'">Edit Order</a>';
			echo '<td align="right">';
			echo $oid['order']->get_formatted_order_total().'</td>';
			echo '</td>';
			echo '</tr>';
			$csv_data[]=array($oid['order']->get_order_number(),date('d-m-Y',$oid['shipdate']),$oid['order']->get_total());
			
			//print_r($oid);
		}
		echo '</table>';
		echo '</td>';		
		echo '</tr>';
		$csv_data[] = array('','',''); 
		$cnt++;
		$tp = $tp + $price;
		if(isset($_GET['provider']) && $_GET['provider']!='' && in_array($_GET['provider'],$report_provider)) {
			break;
		}
	}
		echo '<tr style="background:#F9F9F9"><td colspan="2"></td><td align="right" style="padding-right:20px">Total : '.wc_price($tp).'</td></tr>';
		echo '</table>';
		
		?>
		<style>
		.main{
			background:#ddd;	
		}
		.main > td{
			border-bottom:1px solid #fff;	
		}
		th h3{
			margin:0;
		}
		</style>
		<script>
        jQuery(document).ready(function($){
    		$(".toggle").click(function(){
				var id = $(this).attr('data');
        		$("#"+id).toggle('slow');
    	});
			  
			  //var $j = jQuery.noConflict();
			$(".datepicker").datepicker({ dateFormat: 'dd-mm-yy' });
			//$(".t-datepicker").datepicker();
			//$('.example-datepicker').datepicker();
			
			
		});
    </script>
    <div style="float:right; margin:15px 0 0 0;">
   <form id="pro" method="get" action=""><input type="hidden" name="page" value="report">
  <input type="hidden"  name="fdate"  value="<?=$_GET['fdate']?>" />
  <input type="hidden"  name="tdate" value="<?=$_GET['tdate']?>" />
 
  <input type="hidden"  name="provider" value="<?=$_GET["provider"]?>" /><input type="submit" name="download_csv" class="button-primary" value="Download Report" /></form></div>
  <?php 

/*csv start*/

if(isset($_GET['download_csv'])){
/*print "<pre>";
print_r($csv_data);
print "</pre>";;*/

$filename = "report.csv";
ob_end_clean();
$fp = fopen('php://output', 'w');

header('Content-Encoding: utf-16');
header('Content-type: application/csv;charset=utf-8');
print "\xEF\xBB\xBF"; 
header('Content-Disposition: attachment; filename='.$filename);


//fputcsv($fp, array('Shipping Zone','No of Order','Total'));

	foreach($csv_data as $cv){
		fputcsv($fp,$cv);	
	}

exit;
}


				
	
/*ends*/
	
		
}
add_action('admin_menu', 'register_my_custom_submenu_page',99);
//for filter