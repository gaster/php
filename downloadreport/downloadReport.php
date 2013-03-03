<?
// *********************************************************
// RETRIEVE A REPORT FROM THE REPORT DOWNLOAD SITE WITH PHP5
// *********************************************************

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
	$options = array(
		'http'=> array(
			'method'=>'GET',
			'header'=>$login
			)
		);
	$context = stream_context_create($options);
	// Open the remote file
	$remoteResource = fopen($reportLocationRemote, 'r', false, $context);
	// Open the locale file
	$localeResource = fopen($reportLoactionLocale, 'w');
	
	if ($remoteResource===false || $localeResource===false) {
		return false;
	}
	// Download the file
	while (!feof($remoteResource)) {
		if (fwrite($localeResource, fread($remoteResource, 1024)) === FALSE) {
			// Could not write to locale file!
			fclose($remoteResource);
			fclose($localeResource);
			return false;
               }
	}
	// Close the resources
	fclose($remoteResource);
	fclose($localeResource);
	return true;
}

function getLocaleFilename($dir, $remoteFileName) {
	$index = strrpos($remoteFileName,"/");
	return $dir.substr($remoteFileName,$index);
}
?>
