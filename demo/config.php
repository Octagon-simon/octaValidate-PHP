<?php
//require library
require '../src/Validate.php';

use Validate\octaValidate;
//config options
$valOptions = array(
    "stripTags" => true,
    "strictMode" => true,
    "strictWords" => ["cpanel", "admin", "class"]
);
//initialize new instance of the class
$validate = new octaValidate('form_demo', $valOptions);

//check if post array contains uname
if (isset($_POST['test'])) {
    //validation rules
    $formRules = array(
        "test" => array(
            ["R", "A value is required"]
        )
    );

    //validate form
    if ( $validate->validateFields($formRules, $_POST) ) {
        var_dump("Submitted value = ".$_POST['test']);
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
    <p style="color:#000;max-width:500px">Submit a value with HTML tags, and try to provide the words <b>null,
            undefined, cpanel, admin, class</b> in any of your sentence. Also try to provide spaces at the start of your
        sentence.</p>
    <form id="form_demo" novalidate method="POST">
        <div class="form-group">
            <label>Test</label>
            <textarea id="inp_test"
                name="test"><?php ($_POST && $_POST['test']) ? print($_POST['test']) : '' ?></textarea>
        </div>
        <button type="submit">Submit</button>
    </form>
    <script src="../frontend/helper.js"></script>
</body>

</html>