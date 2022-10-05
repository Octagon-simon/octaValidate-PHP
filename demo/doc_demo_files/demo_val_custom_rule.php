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

//custom password validation
$rule_title = 'PASS';
$reg_exp = '/12345/';
$err_txt = "Please enter 12345";
//build the rule
$myForm->customRule($rule_title, $reg_exp, $err_txt);

//define rules for each form input name
$valRules = array(
  "password" => array(
    ["R", "Your password is required"],
    ["PASS"]
  )
);
//begin validation
if ($myForm->validateFields($valRules) === true) {
  //process form data here
  http_response_code(200);
  $retval = array(
    "success" => true
  );
  print_r(json_encode($retval));
}
else {
  //return errors
  http_response_code(400);
  print_r(json_encode($myForm->getErrors()));
}
?>