<?
/*
================================================================================
Adyen Recurring example
================================================================================
This example assumes you already have a payment on the test server with a recurring contract
Refer to this payment in the code below

It will show :
	how to get the details of a recurring payment
	how to create a payment using a recurring contract 
	how to get the details of a recurring payment and then use these for a subsequent payment

1.0: 111026 
*/

//select what should happen
define ("MODE","DETAILS");  //get details of a Recurring payment
//define ("MODE","DETAILSAUTH");  //get details of a Recurring payment then do a payment using these details
//define ("MODE","AUTH");  //do a RECURRING payment using the most recent recurring details (LATEST)

//credentials
define ("MERCHANTCODE","ACME");    
define("SOAP_USER","ws@Company.ACME");  
define("SOAP_PW","123456789");

//These are the details needed to find a recurring contract which will be used here
define("shopperRef","e8f46a1e58d9e1cbf9785e3a40fe6640");
define("shopperEemail", "john@doe.com");
	

error_reporting(E_ALL ^ E_NOTICE);
ini_set('html_errors',TRUE);
ini_set('display_errors',TRUE);

require "clsRecurring.php";

$debug = true;  //enable debug info
$oREC = new Recurring($debug);  
$oREC->logdir = "data";  //save debug info in this dir (write enable it) 

switch (MODE) :
	case "AUTH": auth(); break;
	case "DETAILS": details(); break;
	case "DETAILSAUTH": 
		$oRes = details();
		$ref = $oRes->details->RecurringDetail->recurringDetailReference;
		print "Recurring reference : $ref"."<BR>"; 
		auth($ref);
	break;
endswitch;


function auth($ref="LATEST") {
	global $oREC;
	$amount = 100; 
	$merchantRef = date("His");   
		//do a recurring Payment - get Status or errorMessage
	$oREC->startSOAP("Payment");  
	print "Result returned from call : ". $oREC->authorise(100,$merchantRef,shopperRef,shopperEemail,$ref)."<BR>";
	print "<br>Result in SOAP object : ".$oREC->response->paymentResult->resultCode;
		
}

function details() {
	global $oREC;

	$oREC->startSOAP("Recurring");  //or Payment
	$oREC->getRecurringDetails(shopperRef);
	//get the Result object
	$oRes = $oREC->response->result;
	//example of getting one of the properties
	//print "Card used : " . $oRes->details->RecurringDetail->variant;
	return $oRes;	
}


//dump Response object
	print "<PRE>Dump of SOAP object : <br>";print_r( $oREC->response);

?>
