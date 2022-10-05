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

//custom username & password validation
$rules = array(
    "PASS" => ['/12345/', "Please enter 12345"],
    "UNAME" => ['/simon/', "Please enter simon"]
);
//build the rule
$myForm->moreCustomRules($rules);

//define rules for each form input name
$valRules = array(
    "username" => array(
        ["R", "Your username is required"],
        ["UNAME"]
    ),
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