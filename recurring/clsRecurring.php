<?
/*
================================================================================
Adyen Recurring example
================================================================================

1.0: 111021 
*/


class Recurring {

	var $client;    //SOAP client
	var $response;  //SOAP response
	var $out;       //debug output
	var $logdir;
	private $DEBUG;	

	
function __construct($debug=false) {
	if (!defined("MERCHANTCODE") || !defined("SOAP_USER") || !defined("SOAP_PW")) {
		exit("Missing info for Adyen");
	}
	//enable logging
	$this->DEBUG = $debug; if ($this->DEBUG) { ob_start(); print "<PRE>"; }
}

function __destruct() {
	//enable logging
	if ($this->DEBUG) { 
		echo "<hr>Debug output<hr><br>";
		echo "REQUEST:\n" . $this->client->__getLastRequest() . "\n";
		echo "RESPONSE:\n" . $this->client->__getLastResponse() . "\n"; 
		$this->out = ob_get_clean();
		print $this->out;
	}
}
									
public function startSOAP($operation="Payment") {
	$host = "test";
	ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache      
																																 
	$this->client = new SoapClient( "https://pal-$host.adyen.com/pal/{$operation}.wsdl",
			array(
				"location" => "https://pal-$host.adyen.com/pal/servlet/soap/{$operation}",
				"login" => SOAP_USER,
				"password" => SOAP_PW,
				'trace' => 1,
				'soap_version' => SOAP_1_1,
				'style' => SOAP_DOCUMENT,
				'encoding' => SOAP_LITERAL
			)
		);
}

public function authorise( $amount,$orderCode,$shopRef,$userEmail,$RDref="LATEST") {      //,
	if (empty($shopRef)) {
			exit("no shopRef for payment $orderCode");  
	}

	try { 
		$this->response = $this->client->authorise( 
			array(
				"paymentRequest" => 
					array (
					"amount" => array ("value" => $amount,"currency" => "EUR"),
					"merchantAccount" => MERCHANTCODE,
					"reference" => "REC".$orderCode,
					"shopperReference" => $shopRef,
					"shopperEmail" => $userEmail,
					"recurring"=>array("contract"=>"RECURRING"),
					"selectedRecurringDetailReference"=>$RDref,
					"shopperInteraction"=>"ContAuth"
					) 
			)
		);
	} 
	catch ( Exception $e ) {
		$errorMessage = $e->getMessage();
		if ($this->DEBUG) { echo "SOAP Error \n" . print_r( $e, true ); }
	} 

 
	# Check the response.
	if ($this->response->paymentResult->resultCode == "Authorised" ) {
		$status="AUTHORISED";   
	} 
	elseif ( $this->response->paymentResult->resultCode == "Received" ) {        //iDeal
		$status="RECEIVED";      
	}
	elseif($this->response->paymentResult->refusalReason) {
		$status="REFUSED";  
	} 
	else {
		$status="ERROR";  
	}

	if (!empty($errorMessage)) 
		return $errorMessage;
	else 
		return $status;
}

public function getRecurringDetails($shopRef) { 
	try {
			$this->response = $this->client->listRecurringDetails  ( 
				array(
				"request" => array ("merchantAccount" => MERCHANTCODE,  "shopperReference" => $shopRef,"recurring"=>array("contract"=>"RECURRING") )
				)
			);
	} 
	catch ( Exception $e ) {
		//$errorMessage = $e->getMessage();
		if ($this->DEBUG) { echo "SOAP Error \n" . print_r( $e, true ); }
	} 
}

// SOAP debug
function printHeaders($client)  {
	print("<pre>");
	print("Request Headers:<br />");
	print($client->__getLastRequestHeaders());
	#print(htmlspecialchars($client->__getLastRequestHeaders()));
	print("<br /><br />Request:<br />");
	print($client->__getLastRequest());
	#print(htmlspecialchars($client->__getLastRequest()));
	print("<br /><br />Response Headers:<br />");
	print($client->__getLastResponseHeaders());
	#print(htmlspecialchars($client->__getLastResponseHeaders()));
	print("<br /><br />Response:<br />");
	print($client->__getLastResponse());
	#print(htmlspecialchars($client->__getLastResponse()));
				print("<pre>");
}


// class ============================  
}	


//helper classes for SOAP
class RecurringRequest {
		public $amount;
		public $merchantAccount;
		public $recurringReference;
		public $reference;
		public $shopperReference;
		public $shopperEmail;
}

class RecurringDetailsRequest {
		public $merchantAccount;
		public $shopperReference;
		public $recurring = "RECURRING";
}

class RecurringResult {
		public $pspReference; 
		public $response; 
}

class Amount {
		public $currency;
		public $value;
}


?>
