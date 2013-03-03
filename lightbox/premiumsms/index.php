<?
	/*
	 * This class provides a JSON response to an AJAX HTTP call requesting the payment form given the order 
	 */
	require 'common/security/HMAC.php'; 	# PEAR Crypt_HMAC
	//Personal Data
	
	
	//Product Data	
	$amount 		= 150; //convert to cents
	$currency 		= "EUR";
	$shopperLocale 		= "en_GB"; //language
	$countryCode		= "NL"; //payment methods for countryCode are shown and used in fraud scores
	
	
	$shipBeforeDate 	= date("Y-m-d"		, mktime(date("H"), date("i"), date("s"), date("m"), date("j"), date("Y")+1)); //one year (Not related to specific products)
	$sessionValidity 	= date(DATE_ATOM	, mktime(date("H"), date("i"), date("s"), date("m"), date("j"), date("Y")+1)); //one year (Not related to specific products)
	//$sessionValidity 	= date(DATE_ATOM	, mktime(date("H")+1, date("i"), date("s"), date("m"), date("j"), date("Y"))); //only one hour (Not related to specific products)
	
	$skinCode 		= "rE8g5ONG"; //Your SkinCode
	$merchantAccount 	= "YourMerchantAccount"; //Your Merchant Code
	$allowedMethods 	= "sms";
	$blockedMethods 	= "";
	$skipSelection  	= "true";
			
	$orderData			 = ""; //no order data at this moment.
		
	$order_id  = 0;
		
	if($order_id == 0){
		$order_id = "flashdemo: ".rand(0, 1000000);
	}
	
	$merchantref 		= $order_id;
	
	//Generate HMAC encrypted merchant signature
	//Instantiate a HMAC object and provide private key
	//Key also specified in Skin in the Adyen backoffice
	$Crypt_HMAC = new Crypt_HMAC("YourSecretKey", 'sha1');
	
	//the data that needs to be signed is a concatenated string of the form data (except the order data)
	//paymentAmount + currencyCode + shipBeforeDate +  merchantReference + skinCode  + 
	//merchantAccount + sessionValidity + shopperEmail + shopperReference + 
	//allowedMethods + blockedMethods
	$sign = $amount . $currency . $shipBeforeDate .  $merchantref . $skinCode .  $merchantAccount . $sessionValidity . $allowedMethods . $blockedMethods;
	

	
	//base64 encoding is necessary because the string needs to be send over the internet and 
	//the hexadecimal result of the HMAC encryption could include escape characters
	//first get the hex string from the HMAC encryption -> convert back to binary data (and pack / zip) -> base64 encode
	$merchantsig 		=  base64_encode(pack('H*',$Crypt_HMAC->hash($sign)));
	
	
	
	$url = "https://test.adyen.com/hpp/select.shtml?merchantReference=".urlencode($merchantref)."&paymentAmount=".urlencode($amount)."&currencyCode=".urlencode($currency)."&shipBeforeDate=".urlencode($shipBeforeDate)."&skinCode=".urlencode($skinCode)."&merchantAccount=".urlencode($merchantAccount)."&shopperLocale=".urlencode($shopperLocale)."&orderData=".urlencode($orderData)."&sessionValidity=".urlencode($sessionValidity)."&shopperEmail=&shopperReference=&recurringContract=&allowedMethods=".urlencode($allowedMethods)."&blockedMethods=".urlencode($blockedMethods)."&skipSelection=".urlencode($skipSelection)."&countryCode=".urlencode($countryCode)."&merchantSig=".urlencode($merchantsig);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<style type="text/css">
body {
	background-color:#5B423D;
	width: 100%;
	height: 100%;
	margin: 0px;
	padding: 0px;
}
#smsdemo {
	text-align: center;
}
</style>

<script type="text/javascript" src="javascript/prototype.js"></script>
<script type="text/javascript" src="javascript/scriptaculous.js?load=effects"></script>
<script type="text/javascript" src="javascript/lightwindow.js"></script>



<link rel="stylesheet" href="css/lightwindow.css" type="text/css" media="screen" />

</head>
<body>
<div id="smsdemo">
	<a params="lightwindow_width=669,lightwindow_height=600" href="<?=$url?>" class="lightwindow"><img style="border: 0px" src="smsdemo.png" /></a>
</div>

</body>
</html>
