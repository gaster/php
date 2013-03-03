<?
// **********************************************************
// RETRIEVE A REPORT FROM THE REPORT DOWNLOAD SITE USING CURL
// **********************************************************

// The username and password of your report user. This can be configured in the CA interface under Settings->Users
$adyenReportUsername = "report@Company.<YourCompanyAccount>";
$adyenReportPassword = "<YourReportUserPassword>";


// After you have received and stored a notification, you can create a server which calls the downloadReport function 
//  for all the REPORT_AVAILABLE notifications. This process must be separated from accepting notifications!

// The REPORT_AVAILABLE notification contains the url of the report in the reason field. 
$reason="https://ca-test.adyen.com/reports/download/MerchantAccount/<YourMerchantAccount>/payment_report_batch_1.csv";

// The location of the file where we have to store it.
$localeFileName = getLocaleFilename('/tmp',$reason);

// This will download the remote file and stores it under $localeFileName => /tmp/payment_report_batch_1.csv
$downloadResult = downloadReport($reason, $localeFileName) ;
echo "downloadResult: ".$downloadResult;

function downloadReport($reportLocationRemote, $reportLoactionLocale) {
	global $adyenReportUsername, $adyenReportPassword;

	$login = 'Authorization: Basic ' .base64_encode($adyenReportUsername.':'.$adyenReportPassword) ;
	$header = array($login);

	$ch = curl_init();

	curl_setopt ($ch, CURLOPT_URL, $reportLocationRemote);
	curl_setopt ($ch, CURLOPT_HEADER, false);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, $header); 

	// Optional settings if your curl is complaining about the certifcate!
	// curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	// curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

	ob_start();

	if(curl_exec($ch) === false) {
		echo "Error: " . curl_error($ch);
		curl_close ($ch);
		return false;
	} 
	curl_close ($ch);
	
	$reportContent = ob_get_contents();
	ob_end_clean();

	if(!$reportContent || $reportContent == "") {
		return false;
	}
	
	// Open the locale file
	$localeResource = fopen($reportLoactionLocale, 'w');
	fwrite($localeResource, $reportContent);
	fclose($localeResource);

	return true;
}

function getLocaleFilename($dir, $remoteFileName) {
	$index = strrpos($remoteFileName,"/");
	return $dir.substr($remoteFileName,$index);
}
?>
