<?php

//require library
require '../src/Validate.php';
use Validate\octaValidate;

//create new instance of the class
$DemoForm = new octaValidate('form_demo');

//custom rules
$customRules = array(
    "UNAME" => ['/simon/', "You must enter simon"],
    "PASS" => ['/12345/', "You must enter 12345"]
);

$DemoForm->moreCustomRules($customRules);

//define validation rules
$valRules = array(
    "username" => array(
        ["R", "Your username is required"],
        ["UNAME"]
    ),
    "email" => array(
        ["R", "Your Email is required"],
        ["EMAIL", "Your Email is invalid"]
    ),
    "age" => array(
        ["R", "Your Age is required"],
        ["DIGITS", "Your Age must be in digits"],
        ["LENGTH", "2", "Your age must be 2 digits"]
    ),
    "password" => array(
        ["R", "Your Password is required"],
        ["PASS"]
    )
);

if ($_POST) {

    //begin validation    
    if ($DemoForm->validateFields($valRules) === true) {

        //process form data here
        print('<script> alert("NO VALIDATION ERROR") </script>');    }
    else {
        //retrieve & display errors
        print('<script>
            window.addEventListener(\'load\', function(){
                showErrors(' . json_encode($DemoForm->getErrors()) . ');
            })
        </script>');    
    }
}
?>
<html>

<head>
    <meta charset="utf-8">
    <title>octavalidate PHP Demo File</title>
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <form id="form_demo" method="post" novalidate>
        <div class="form-group">
            <label>Username</label>
            <input name="username" type="text" id="inp_uname"
                value="<?php ($_POST && $_POST['username']) ? print($_POST['username']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input name="email" type="email" id="inp_email"
                value="<?php ($_POST && $_POST['email']) ? print($_POST['email']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Age</label>
            <input name="age" type="number" id="inp_age"
                value="<?php ($_POST && $_POST['age']) ? print($_POST['age']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input name="password" type="password" id="inp_pass"
                value="<?php ($_POST && $_POST['password']) ? print($_POST['password']) : '' ?>">
        </div>
        <button type="submit">Run Test</button>
    </form>
    <script src="../frontend/helper.js"></script>
</body>

</html>