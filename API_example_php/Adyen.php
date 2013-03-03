<?
ini_set( 'soap.wsdl_cache_enabled', '0');

class Adyen {
	var $client;
	var $DEBUG;
	var $last_response;

	function Adyen($login, $password, $host ="live", $debug=FALSE ) {
		$this->DEBUG = $debug;

		$this->client = new SoapClient( "https://pal-$host.adyen.com/pal/Payment.wsdl",
		  array(
		    "location" => "https://pal-$host.adyen.com/pal/servlet/soap/Payment",
		    "login" => $login,
		    "password" => $password,
		    'trace' => 1,
		    'soap_version' => SOAP_1_1,
		    'style' => SOAP_DOCUMENT,
		    'encoding' => SOAP_LITERAL
		  )
		);
	}


	function authorise( $amount,$currencyCode,$cardHolder,$cardNumber,$expm,$expy,$cvc,$reference) {
	    	global $merchantAccount;

		$response = $this->client->authorise( array(
		  "paymentRequest" => array 
		  (
		    "amount" => array (
			"value" => $amount,
			"currency" => $currencyCode),
			"card" => array (
			"cvc" => $cvc,
			"expiryMonth" => $expm,
			"expiryYear" => $expy,
			"holderName" => $cardHolder,
			"number" => $cardNumber,
		    ),
		  "merchantAccount" => $merchantAccount,
		  "reference" => $reference,
		)
	      )
	    );

	    if ($this->DEBUG) echo var_dump($response);

	    # Check the response.
  	    if ( $response->paymentResult->resultCode == "Authorised" ) {
		echo "Authorised. Your authorisation code is : " . $response->paymentResult->authCode . "\n";
		$authorised = true;
	    } elseif($response->paymentResult->refusalReason) {
		echo "Refused: " . $response->paymentResult->refusalReason . "\n";
		$authorised = false;
	    } else {
		echo "Error\n";
	    }
	      
	    if ($this->DEBUG) echo "REQUEST:\n" . $this->client->__getLastRequest() . "\n";
	    if ($this->DEBUG) echo "RESPONSE:\n" . $this->client->__getLastResponse() . "\n";

	    return $authorised;
	}
};

?>
