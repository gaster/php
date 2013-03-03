<?php 
 
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 

class NotificationRequest {
    public $notificationItems; 
    public $live; 
}

class NotificationRequestItem {
    public $amount;
    public $eventCode;
    public $eventDate;
    public $merchantAccountCode;
    public $merchantReference;
    public $originalReference;
    public $pspReference;
    public $reason;
    public $success;
    public $paymentMethod;
    public $operations;
    public $additionalData;
}

class Amount {
    public $currency;
    public $value;
}

function sendNotification($request) { 

  if($request->live) {
  	$fp = fopen('/tmp/out_live.txt', 'w');
  } else {
  	$fp = fopen('/tmp/out_test.txt', 'w');
  }
  
  $output = var_export($request,true);
  fprintf($fp, '%s', "BEGIN\n". $output . "\nEND\n");

  # For some reason NotificationRequestItem is an array for multiple notifications
  # and a scalar for a single notification (rather than an array with length = 1) 
  if (is_array($request->notification->notificationItems->NotificationRequestItem)) {
    foreach( $request->notification->notificationItems->NotificationRequestItem as $item) {
	  storeItem($item, $fp);
    }
  } else {
    $item = $request->notification->notificationItems->NotificationRequestItem;
	  storeItem($item, $fp);
  }

  return array("notificationResponse" => "[accepted]");
} 

/* 
   Store the notification here. 
   We don't attempt to process them here, leave that for an offline process which 
   can deal with processing errors for individual notificatons.
 */
function storeItem($item, $fp) { 

  $output = $output . "\nNotification: \n" . 
    "Amount = " . $item->amount->currency . " " . $item->amount->value . "\n" .
    "Event Code = " . $item->eventCode . "\n" .
    "Event Date = " . $item->eventDate . "\n" .
    "Merchant Account Code = " . $item->merchantAccountCode . "\n" .
    "Merchant Reference = " . $item->merchantReference . "\n" .
    "Original Reference = " . $item->originalReference . "\n" .
    "Psp Reference = " . $item->pspReference . "\n" .
    "Reason = " . $item->reason . "\n" .
    "Success = " . $item->success . "\n".
    "Payment Method = " . $item->paymentMethod . "\n\n".
	
    "Available Operations: \n";

    if(is_array($item->operations->string)) {
      foreach($item->operations->string as $operation) {
        $output = $output . "\t" . $operation . "\n" ;
      }
    } else {
      $output = $output . "\t" . $item->operations->string . "\n" ;
    }

    $output = $output . "\n\nAdditional Data:\n";
    if(is_array($item->additionalData->entry)) {
      foreach($item->additionalData->entry as $entry) {
        $output = $output . "\t" . $entry->key ."=".$entry->value."\n" ;
      }
    } else {
      $output = $output . "\t" . $item->additionalData->entry->key ."=".$item->additionalData->entry->value."\n" ;
    }

    $output = $output . "\n";

    fprintf($fp, '%s', $output);
}
 
$classmap = array('NotificationRequest' => 'NotificationRequest', 
    'NotificationRequestItem' => 'NotificationRequestItem',
    'Amount' => 'Amount');

# Use a locally cached version of the Notification.wsdl to guarantee service uptime
# However, Notification.wsdl should be regularly updated from here: 
#      https://ca-live.adyen.com/ca/services/Notification?wsdl

$server = new SoapServer("Notification.wsdl", array('classmap' => $classmap)); 
$server->addFunction("sendNotification"); 
$server->handle();

?> 
