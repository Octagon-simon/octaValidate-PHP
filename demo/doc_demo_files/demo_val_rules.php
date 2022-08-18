<?php
/**
 *These are the server-side scripts used on the documentation page
 **/

//require the library
require 'octavalidate-php/validate.php';
//set configuration
$options = array(
  "stripTags" => true,
  "strictMode" => true
);
//create new instance
$myForm = new octaValidate('form_demo', $options);
//define rules for each form input name
$valRules = array(
  "username" => array(
    ["R", "Your username is required"]
 ),
  "email" => array(
    ["R", "Your Email Address is required"],
    ["EMAIL", "Your Email Address is invalid!"]
) );
//begin validation
if ($myForm->validate($valRules) === true){
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