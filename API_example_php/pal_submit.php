#!/usr/bin/php
<?
	// pal_submit.php
	// Submits a payment to the Adyen SOAP listener on the PAL systems
	
	ini_set( "include_path", ini_get("include_path") . ":" . ":." );
	include("Adyen.php");

	$system = "test"; // "live"
	$merchantAccount = "YourMerchantAccount";
	$login = "ws@Company.YourCompany";
	$password = "t0pS3cret";
	
	$cardHolder = "John Doe";
	$cardNumber = "4111111111111111";
	$expM = "12";
	$expY = "2012";
	$cvc = "737";

	$amount = 990;
	$currencyCode = "EUR";
	$reference = "Test Payment" . time();
	
	$debug = FALSE;

	try {
		$a = new Adyen( $login, $password, $system, $debug);
		$result = $a->authorise( $amount, $currencyCode, $cardHolder, $cardNumber, $expM, $expY, $cvc, $reference);
	} catch ( Exception $e ) {
		echo "SOAP Error on $system\n" . print_r( $e, true );
	}	

function var_dump_ret($mixed = null) 
{
	ob_start();
	var_dump($mixed);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

?>
