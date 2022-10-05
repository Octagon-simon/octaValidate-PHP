<?php
/**
 *These are the server-side scripts used on the documentation page
 **/

//require the library
require '../validate.php';
//set configuration
$options = array(
    "stripTags" => true,
    "strictMode" => true,
    "strictWords" => ["admin"]
);
//create new instance
$myForm = new octaValidate('form_demo', $options);
//define rules for each form input name
$valRules = array(
    "username" => array(
        ["R", "Your username is required"],
        ["USERNAME", "Your username is invalid"],
        ["MAXLENGTH", "10", "Your username should contain 10 characters or less"]
    ),
    "age" => array(
        ["R", "Your Age is required"],
        ["DIGITS", "Your Age must be in digits"],
        ["LENGTH", "2", "Your Age must be 2 digits"]
    ),
    "password" => array(
        ["R", "Your Password is required"],
        ["LENGTH", "5", "Your Password must be 5 characters!"],
        ["EQUALTO", "confirm_password", "Both passwords do not match"]    
));
//begin validation
if ($myForm->validateFields($valRules) === true) {
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