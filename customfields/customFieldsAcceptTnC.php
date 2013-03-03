<?php 
 
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 

class CustomFieldRequest {
    public $merchantAccount;
    public $merchantReference;
    public $fields; 
}

class CustomFieldResponse {
    public $fields; 
    public $response;
}

class CustomField {
    public $name;
    public $value;
}

function check( $request ) { 
  $response = new CustomFieldResponse();
  $response->fields = array();

  #### debug ####
  $fp = fopen('out.txt', 'w');
  $output = var_export($request,true);
  ###############

  $response->response = '[accepted]';

  $truefields = array('termsandconditions');

  $fields = array();

  foreach( $request->customFieldRequest->fields->CustomField as $field ) {
       $fields[$field->name] = $field->value;
  }

  foreach( $truefields as $rf ) {

	if($fields[$rf] != "true" ) {
       $response->response = '[invalid]';
       $invalidField = new CustomField();
       $invalidField->name=$rf;
       $invalidField->value="customField.error.".$rf;
  	   $output .= var_export($invalidField,true);
       array_push($response->fields,$invalidField);
  	}
  }

  $output .= var_export($fields,true);
  $output .= var_export($response,true);

  if($response->response == '[invalid]') {
    fprintf($fp, '%s', "BEGIN\n". $output . "\nEND\n");
    return array("customFieldResponse" => $response );
  }

  return array("customFieldResponse" => $response );
} 

$classmap = array('customFieldRequest' => 'CustomFieldRequest', 
    'customFieldResponse' => 'CustomFieldResponse',
    'CustomField' => 'CustomField');


# Use a locally cached version of the CustomFields.wsdl to guarantee service uptime
# However, CustomFields.wsdl should be regularly updated from here: 
#    https://pal-live.adyen.com/pal/CustomFields.wsdl

$server = new SoapServer("CustomFields.wsdl", array('classmap' => $classmap)); 
$server->addFunction("check"); 
$server->handle();

?> 
