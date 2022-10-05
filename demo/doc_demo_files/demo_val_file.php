<?php
/**
 *These are the server-side scripts used on the documentation page
 **/

//require the library
require '../validate.php';
//set configuration
$options = array(
  "stripTags" => true,
  "strictMode" => true
);
//create new instance
$myForm = new octaValidate('form_demo', $options);
//define rules for each form input name
$valRules = array(
    "profile" => array(
      ["R", "Your profile picture is required"],
      ["MAXSIZE", "2Mb", "Your profile must be 2MB or less"],
      ["ACCEPT-MIME", "image/png, image/jpg, image/jpeg", "File type is not supported!"]
    )
  );
//begin validation
if ($myForm->validateFiles($valRules) === true){
  //process form data here
  http_response_code(200);
  $retval= array(
    "success" => true
  );
  print_r(json_encode($retval));
}else{
  //return errors
  http_response_code(400);
  print_r(json_encode($myForm->getErrors()));
}
?>