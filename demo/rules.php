<?php
//require library
require '../src/Validate.php';

use Validate\octaValidate;

//initialize new instance of the class
$validate = new octaValidate('form_demo');

//check if post array contains uname
if (isset($_POST['uname'])) {
    //validation rules
    $formRules = array(
        "uname" => array(
            ["R", "Your username is required"],
            ["USERNAME"]
        ),
        "fname" => array(
            ["R", "Your first name is required"],
            ["ALPHA_ONLY"]
        )
    );

    //validate form
    if ( $validate->validateFields($formRules) ) {
        echo "FORM SUBMITTED";
    }
    else {
        //retrieve & display errors
        print('<script>
            window.addEventListener(\'load\', function(){
                showErrors(' . json_encode($validate->getErrors()) . ');
            })
        </script>');
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>octavalidate PHP Demo File</title>
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <form id="form_demo" novalidate method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Username</label>
            <input type="text" id="inp_uname" name="uname"
                value="<?php ($_POST && $_POST['uname']) ? print($_POST['uname']) : '' ?>">
        </div>
        <div class="form-group">
            <label>First Name</label>
            <input type="text" id="inp_fname" name="fname"
                value="<?php ($_POST && $_POST['fname']) ? print($_POST['fname']) : '' ?>">
        </div>
        <button type="submit">Submit</button>
    </form>
    <script src="../frontend/helper.js"></script>
</body>

</html>