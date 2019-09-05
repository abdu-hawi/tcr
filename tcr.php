<?php

define('DB_NAME' , 'last_invoice_test');
define('DB_HOST' , 'localhost');
define('DB_USER' , 'root');
define('DB_PASSWORD' , '');

// connect with siver

$tf_handle = @mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die('Could not connect...');

$response = array();

$user_no = strip_tags(trim($_POST['user_id']));;
$shop_name = strip_tags(trim($_POST['shop_name']));
$shop_desc = strip_tags(trim($_POST['shop_desc']));
$order_date = strip_tags(trim($_POST['order_date']));
$total_amount = strip_tags(trim($_POST['final_total_amt']));

$date = date("Y-m-d H:i:s" ) ;
try{
    $tf_handle -> query("Begain Transaction"); 
                
    $qry = $tf_handle->query("INSERT INTO `tbl_order_system`
        (`order_id`, `com_id`, `bra_id`, `user_id`, `invoice_no`, `date_invoice`, `total_amount`, `date`, `invoice_status`, `shop_name`, `shop_desc` )
        VALUES (NULL, 12, 16, '$user_no', 1 , '$order_date', '$total_amount', '$date', 0 , '$shop_name' , '$shop_desc')");
    if($qry) {
        $last_id = $tf_handle->insert_id; // $last_id -> use to save item array with forgin key in item_table
        
        $js = json_decode($_POST["json_items"]);
        for($count=0 ; $count< count($js->recent) ; $count++){
            
            //$item_name = trim($_POST['item_name'][$count]);
            $item_name = trim($js->recent[$count]->PRODUCT);
            $item_qty = trim($js->recent[$count]->QTY);
            $item_price = trim($js->recent[$count]->PRICE);
            
            $qry_item = $tf_handle->query("INSERT INTO `tbl_order_system_item`
                (`ord_prod_id`, `order_id`, `prod_name`, `prod_qty`, `prod_price`)
                VALUES (NULL, '$last_id', '$item_name', '$item_qty', '$item_price')");
            
            if($qry_item){
                $tf_handle->query("Commit");
                $response['error'] = false;
                $response['msg'] = 'OK INSERT';
            }else{
                $tf_handle->query("DELETE FROM `tbl_order_system` WHERE `order_id` = ".$last_id);
                $tf_handle->query("Rollback Transaction");
                $response['error'] = true;
                $response['msg'] = 'Do not insert items';
            }
            
        } // end for loop count
    } // end if $qry
    else{
        $response['error'] = true;
        $response['msg'] = 'Do not insert head';
    }
}catch(Exception $e){
    $tf_handle->query("Rollback Transaction");
    $response['error'] = true;
    $response['msg'] = 'catch';
}
echo json_encode($response);
?>
