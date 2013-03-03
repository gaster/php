<?
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 

class ModificationRequest {
    public $modificationAmount;
    public $merchantAccount;
    public $originalReference;
}

class ModificationResult {
    public $pspReference; 
    public $response; 
}

class Amount {
    public $currency;
    public $value;
}

$amount = new Amount();
$amount->currency = "EUR";
$amount->value = "100";

$rr = new ModificationRequest();
$rr->modificationAmount = $amount;
$rr->merchantAccount = "YourMerchant"; // replace with your merchant account
$rr->originalReference = "9912077488530053"; // for example

$ro = new ModificationResult();

# 'cancel' => 'cancel', 
# 'refund' => 'refund', 
$classmap = array(
	'Amount' => 'Amount', 
	'capture' => 'capture', 
	'ModificationRequest' => 'ModificationRequest', 
	'ModificationResult' => 'ModificationResult'
);

# Replace "YourCompany" in the login with the name of your company account
# Set the password credential in the backoffice
$soapClient = new SoapClient('Payment.wsdl', array('login' => "ws@Company.YourCompany",
    'password' => "VeRYSeeecret",
    'soap_version' => SOAP_1_1,
    'style' => SOAP_DOCUMENT,
    'encoding' => SOAP_LITERAL,
    'location' => "https://pal-test.adyen.com/pal/servlet/soap/Payment",
    'trace' => 1,
    'classmap' => $classmap));

#print("<pre>");
#print_r($soapClient->__getFunctions());
#print("</pre>");
#print("<hr />");
	
#print("<pre>");
#print_r($soapClient->__getTypes());
#print("</pre>");
#print("<hr />");

try {
	$result = $soapClient->capture(array('modificationRequest' => $rr, 'captureResponse' => $ro));
} catch (SoapFault $exception) {
	print("<pre>");
	print($exception);
	print("<pre>");
	print("<hr />");
    printHeaders($soapClient);
}

print("\nresult = " . $result->captureResult->response);
print("\npspreference = " . $result->captureResult->pspReference);
print("\n\n");

//====================== Debug Functions ======================
function printHeaders($client)
{
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

?>
