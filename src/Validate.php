<?php
namespace Validate;

/**
 * OctaValidate Main PHP V1.4
 * author: Simon Ugorji
 * Last Edit : 26th August 2022
 */

//include rules library
require('RulesLib.php');
class octaValidate
{
    //store errors
    private static $errors = [];
    //version
    private static $version = '1.4';
    //author
    private static $author = 'Simon Ugorji';
    //form id
    private static $form;
    //store error counter
    private static $continueValidation = 0;
    //store custom rules
    private static $customRules = [];
    //config options
    private static $configOptions = array(
        "stripTags" => false,
        "strictMode" => false,
        "strictWords" => ["null", "undefined"]
    );

    function __construct($form_id = null, $config = null)
    {
        if ($form_id === null)
            throw new \InvalidArgumentException("You have to initialize octavalidate with a valid form ID as the first argument");
        //set form id
        self::$form = $form_id;

        if ($config !== null) {
            if (!is_array($config))
                throw new \InvalidArgumentException("You have to initialize octavalidate with a valid array containing configuration options as the second argument");
            //strip tags
            self::$configOptions["stripTags"] = (!empty($config["stripTags"]) && is_bool($config["stripTags"])) ? $config["stripTags"] : false;
            //strict Mode
            self::$configOptions["strictMode"] = (!empty($config["strictMode"]) && is_bool($config["strictMode"])) ? $config["strictMode"] : false;
            //strict words
            if (self::$configOptions["strictMode"]) {
                if (!empty($config["strictWords"]) && is_array($config["strictWords"])) {
                    //merge default strict words with dev defined one
                    self::$configOptions["strictWords"] = array_merge(self::$configOptions["strictWords"], $config["strictWords"]);
                }
            }
        }

        //load inbuilt rules
        rulesLibrary();
    }

    //throw exception
    private static function ovDoException($msg)
    {
        throw new \Exception($msg);
    }

    //custom rule
    public function customRule($ruleTitle = '', $regExp = '', $message = '')
    {
        if (!$ruleTitle || !$regExp || !$message)
            self::ovDoException('To build a custom Rule, you need the "RULE TITLE, REGULAR EXPRESSION & ERROR MESSAGE" as the first, second and third arguments respectively');
        //store rules
        self::$customRules[$ruleTitle] = [$regExp, $message];
    }

    //more custom rules
    public function moreCustomRules($rules)
    {
        if (!is_array($rules))
            throw new \InvalidArgumentException('To Build multiple custom rules, you need to provide an Array which will contain the "RULE TITLE, REGULAR EXPRESSION & ERROR MESSAGE". Please refer to the documentation');
        //loop through and store rules
        foreach ($rules as $title => $data) {
            self::$customRules[$title] = [$data[0], $data[1]];
        }
    }

    //new error
    private static function ovNewError($input, $msg)
    {
        self::$errors[$input] = $msg;
    }
    //new multiple file input error
    private static function ovNewMultiFileError($input, $msg)
    {
        self::$errors[$input . '[]'] = $msg;
    }
    //remove error
    private static function ovRemoveError($input)
    {
        unset(self::$errors[$input]);
    }
    //remove multiple file input error
    private static function ovRemoveMultiFileError($input)
    {
        unset(self::$errors[$input . '[]']);
    }
    //get file size in bytes
    private static function getSizeInBytes($fileSize)
    {
        $prevSize = $fileSize;
        //convert to lowercase
        $fileSize = strtolower(str_replace(' ', '', $fileSize));
        //check size
        if (!(preg_match('/[0-9]+(bytes|kb|mb|gb|tb|pb)/', $fileSize))) {
            throw new \Exception('The size ' . $prevSize . ' you provided is Invalid. Please check for typos or make sure that you are providing a size from bytes up to Petabytes');
        }
        //get the size as a number
        $sizeNum = implode('',
            array_map(
            function ($sn) {
            return ((is_numeric($sn)) ? $sn : '');
        }, str_split($fileSize))
        );

        //get the digital storage, using call_user_func as fallback to support PHP 5.x 
        $sizeExt = call_user_func(function ($fileSize) {
            //retrieve an array containing boolean which shows true for not numbers or false for numbers
            $res = array_map(
                function ($se) {
                return (is_numeric($se));
            }
                , str_split($fileSize));
            //find the fist position of true and extract remaining string
            return (substr($fileSize, array_search(false, $res)));
        }, $fileSize);

        //do conversion here
        switch ($sizeExt) {
            case "bytes":
                return ($sizeNum);
            case "kb":
                return ($sizeNum * 1024);
            case "mb":
                return ($sizeNum * 1024 * 1024);
            case "gb":
                return ($sizeNum * 1024 * 1024 * 1024);
            case "tb":
                return ($sizeNum * 1024 * 1024 * 1024 * 1024);
            case "pb":
                return ($sizeNum * 1024 * 1024 * 1024 * 1024 * 1024);
            default:
                return (0);
        }
    }

    private static function doStrictMode()
    {
        $configOptions = self::$configOptions;
        foreach ($_POST as $inp => $val) {
            //check and strip tags if enabled
            if ($configOptions["stripTags"]) {
                //reassign local variable
                $val = strip_tags($val);
            }

            //check and trim inputs if enabled
            if ($configOptions["strictMode"]) {
                //reassign local variable
                $val = trim($val);
            }
            //reassign value
            $_POST[$inp] = $val;
        }
    }
    public static function validate($userValidations)
    {
        if (!is_array($userValidations))
            throw new \InvalidArgumentException("The validate method needs a valid Array to begin validation");
        //load custom rules
        $customRules = self::$customRules;
        //config optio s
        $configOptions = self::$configOptions;
        //handle strict mode
        self::doStrictMode();
        //loop through POST DATA
        foreach ($_POST as $inputName => $inputValue) {
            //check for strict words
            if ($configOptions["strictMode"] && $configOptions["strictWords"]) {
                $errMsg = "This value is not allowed";
                $res = array_filter(self::$configOptions["strictWords"],
                    function ($word) use ($inputValue) {
                    return (preg_match('/(' . $word . ')/', $inputValue));
                });
                if (count($res) !== 0) {
                    self::$continueValidation = 0;
                    self::ovNewError($inputName, $errMsg);
                }
                else {
                    self::$continueValidation++;
                    self::ovRemoveError($inputName);
                }
            }
            else {
                self::$continueValidation++;
                self::ovRemoveError($inputName);
            }
            //check if current input has a validation
            if (self::$continueValidation && !empty($userValidations[$inputName])) {
                //check if an array was provided
                if (!is_array($userValidations[$inputName]))
                    throw new \InvalidArgumentException("The validate method needs a valid Array to begin validation");
                //loop through validations
                foreach ($userValidations[$inputName] as $valData) {
                    //validation rule
                    $rt = $valData[0];
                    //do required
                    if ($rt === "R") {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "This field is required";
                        if (!$inputValue) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && count($customRules) !== 0 && !empty($customRules[$rt]) && $inputValue) {
                        $pattern = $customRules[$rt][0];
                        $errMsg = $customRules[$rt][1];
                        if (!preg_match($pattern, $inputValue)) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "EMAIL" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "This Email Address is Invalid";
                        if (filter_var($inputValue, FILTER_VALIDATE_EMAIL) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "ALPHA_ONLY" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Please enter only Letters";
                        if (Validate_ALPHA_ONLY($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "ALPHA_SPACES" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Only letters or spaces is allowed";
                        if (Validate_ALPHA_SPACES($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "ALPHA_NUMERIC" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Only letters or numbers is allowed";
                        if (Validate_ALPHA_NUMERIC($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "LOWER_ALPHA" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Only lowercase letters is allowed";
                        if (Validate_LOWER_ALPHA($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "UPPER_ALPHA" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Only uppercase letters is allowed";
                        if (Validate_UPPER_ALPHA($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "PWD" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Password Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters";
                        if (Validate_PWD($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "DIGITS" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "This value contains characters that are not digits";
                        if (!is_numeric($inputValue)) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "URL" && $inputValue) {
                        //error message
                        $errMsg = "Please provide a valid URL that begins with http or https!";
                        if (filter_var($inputValue, FILTER_VALIDATE_URL) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "URL_QP" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Please provide a valid URL with a query parameter";
                        if (Validate_Url_QP($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "DATE_MDY" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Please provide a date with the format mm/dd/yyyy";
                        if (Validate_Date_MDY($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "USERNAME" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "Your username is invalid";
                        if (Validate_UserName($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "TEXT" && $inputValue) {
                        //error message
                        $errMsg = (!empty($valData[1])) ? $valData[1] : "This field contains invalid characters";
                        if (Validate_TEXT($inputValue) === false) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    //ATTRIBUTES VALIDATION HERE
                    }
                    /* ATTRIBUTES VALIDATION*/
                    else if (self::$continueValidation && $rt === "LENGTH" && $inputValue) {
                        //attribute value
                        $attrVal = (!empty($valData[1])) ? intval($valData[1]) : self::ovDoException("You must provide a value for the length attribute");
                        //error message
                        $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please enter ' .
                            $attrVal . ' number of characters';

                        if (strlen($inputValue) !== $attrVal) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "MINLENGTH" && $inputValue) {
                        //attribute value
                        $attrVal = (!empty($valData[1])) ? intval($valData[1]) : self::ovDoException("You must provide a value for the Min-length attribute");
                        //error message
                        $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please enter ' .
                            $attrVal . ' or more characters';

                        if (!(strlen($inputValue) >= $attrVal)) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "MAXLENGTH" && $inputValue) {
                        //attribute value
                        $attrVal = (!empty($valData[1])) ? intval($valData[1]) : self::ovDoException("You must provide a value for the Max-length attribute");
                        //error message
                        $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please enter ' .
                            $attrVal . ' characters or less';

                        if (!(strlen($inputValue) <= $attrVal)) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                    else if (self::$continueValidation && $rt === "EQUALTO" && $inputValue) {
                        //attribute value
                        $attrVal = (!empty($valData[1])) ? $valData[1] : self::ovDoException("You must provide the name of the input element whose value be compared with");
                        //error message
                        $errMsg = (!empty($valData[2])) ? $valData[2] : 'Both Values do not match';
                        //check if input name is contained in POST Array
                        if (!isset($_POST[$attrVal]))
                            self::ovDoException('The input element "' . $attrVal . '" does not exist in the POST Array');

                        if ($inputValue !== $_POST[$attrVal]) {
                            self::$continueValidation = 0;
                            self::ovNewError($inputName . ":" . $attrVal, $errMsg);
                        }
                        else {
                            self::$continueValidation++;
                            self::ovRemoveError($inputName);
                        }
                    }
                }
            }
        }
        /*FILE VALIDATION */
        //loop through FILE ARRAY
        foreach ($_FILES as $inputName => $fileData) {
            //check if current input has a validation
            if (!empty($userValidations[$inputName])) {
                //check if an array was provided
                if (!is_array($userValidations[$inputName]))
                    throw new \InvalidArgumentException("The validate method needs a valid Array to begin File validation");
                //loop through validations
                foreach ($userValidations[$inputName] as $valData) {
                    //added break keyword to prevent the loop from checking the next file if current file contains errors
                    //validation rule
                    $rt = $valData[0];
                    //handle multiple file upload
                    if (is_array($fileData['name'])) {
                        //loop through all files
                        $currentFileInd = 0;
                        while ($currentFileInd < count($fileData['name'])) {
                            //-------------------
                            $currentFileName = $fileData['name'][$currentFileInd];
                            //get current mime type
                            $currentFileType = $fileData['type'][$currentFileInd];
                            //get current file size
                            $currentFileSize = $fileData['size'][$currentFileInd];
                            //-------------------

                            //do required
                            if ($rt === "R") {
                                //error message
                                $errMsg = (!empty($valData[1])) ? $valData[1] : "A valid file is required";
                                if (empty($currentFileName) || empty($currentFileType) || empty($currentFileSize)) {
                                    self::ovNewMultiFileError($inputName, $errMsg);
                                    break;
                                }
                            }
                            else if ($rt === "ACCEPT" && $currentFileName) {
                                //attribute value
                                $requiredExts = (!empty($valData[1])) ? explode(",", str_replace(" ", "", $valData[1])) : self::ovDoException("The file extensions to check must be provided after the \"ACCEPT\" rule");
                                //error message
                                $errMsg = (!empty($valData[2])) ? $valData[2] : 'This file ' . $currentFileName . ' is not supported';
                                //get current file extension
                                $currentExt = strtolower(substr($currentFileName, strrpos($currentFileName, ".")));

                                //------------------

                                //loop through required Extensions to check & compare
                                // it's becoming complex & fun :( :)
                                //preg_match('#\.{1}.#', $rext) [.png]
                                //preg_match('#./+[^*]#', $rext) [image/jpeg]
                                //preg_match('#.\/\*{1}#', $rext) [image/*]

                                if (!in_array($currentExt, $requiredExts)) {
                                    self::ovNewMultiFileError($inputName, $errMsg);
                                    break;
                                }
                            }
                            else if ($rt === "ACCEPT-MIME" && $currentFileName) {
                                //attribute value
                                $requiredMime = (!empty($valData[1])) ? explode(",", str_replace(" ", "", $valData[1])) : self::ovDoException("The MIME types to check must be provided after the \"ACCEPT-MIME\" rule");
                                //error message
                                $errMsg = (!empty($valData[2])) ? $valData[2] : 'This file ' . $currentFileName . ' is not supported';
                                //check if current type is contained within array
                                if (!in_array(explode(substr($currentFileType, strrpos($currentFileType, "/")), $currentFileType)[0] . "/*", $requiredMime) && !in_array($currentFileType, $requiredMime)) {
                                    self::ovNewMultiFileError($inputName, $errMsg);
                                    break;
                                }
                            }

                            //check for next file
                            $currentFileInd++;
                        }
                        //the section below is removed from the loop because it needs to work on all files at once not each of them
                        if ($rt === "SIZE") {
                            //attribute value
                            $fileSize = (!empty($valData[1])) ? strtolower($valData[1]) : self::ovDoException('A valid file size must be provided after the "SIZE" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please select files that is equal to ' . $fileSize . '';
                            //save total size
                            $totalSize = 0;
                            foreach ($fileData['size'] as $fds) {
                                $totalSize += $fds;
                            }
                            if (self::getSizeInBytes($fileSize) !== $totalSize) {
                                self::ovNewMultiFileError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "MINSIZE") {
                            //attribute value
                            $fileSize = (!empty($valData[1])) ? strtolower($valData[1]) : self::ovDoException('A valid file size must be provided after the "MINSIZE" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please select files that is more than or equal to ' . $fileSize . '';
                            //save total size
                            $totalSize = 0;
                            foreach ($fileData['size'] as $fds) {
                                $totalSize += $fds;
                            }
                            if (!($totalSize >= self::getSizeInBytes($fileSize))) {
                                self::ovNewMultiFileError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "MAXSIZE") {
                            //attribute value
                            $fileSize = (!empty($valData[1])) ? strtolower($valData[1]) : self::ovDoException('A valid file size must be provided after the "MAXSIZE" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please select files that is less than or equal to ' . $fileSize . '';
                            //save total size
                            $totalSize = 0;
                            foreach ($fileData['size'] as $fds) {
                                $totalSize += $fds;
                            }
                            //do total files, totalmaxfiles
                            if (!($totalSize <= self::getSizeInBytes($fileSize))) {
                                self::ovNewMultiFileError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "FILES") {
                            //attribute value
                            $filesNum = (!empty($valData[1])) ? intval($valData[1]) : self::ovDoException('You must provide the number of files after the "FILES" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please upload ' . $filesNum . ' files';
                            //save total files
                            $totalFiles = 0;
                            //use their sizes to compare it
                            foreach ($fileData['size'] as $fd) {
                                if ($fd !== 0) {
                                    $totalFiles += 1;
                                }
                            }
                            //do total files, totalmaxfiles
                            if ($totalFiles != $filesNum) {
                                self::ovNewMultiFileError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "MINFILES") {
                            //attribute value
                            $filesNum = (!empty($valData[1])) ? intval($valData[1]) : self::ovDoException('You must provide the number of files after the "MINFILES" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please upload ' . $filesNum . ' files or more';
                            //save total files
                            $totalFiles = 0;
                            //use their sizes to compare it
                            foreach ($fileData['size'] as $fd) {
                                if ($fd !== 0) {
                                    $totalFiles += 1;
                                }
                            }
                            //do total files, totalmaxfiles
                            if (!($totalFiles >= $filesNum)) {
                                self::ovNewMultiFileError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "MAXFILES") {
                            //attribute value
                            $filesNum = (!empty($valData[1])) ? intval($valData[1]) : self::ovDoException('You must provide the number of files after the "FILES" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please upload ' . $filesNum . ' files or less';
                            //save total files
                            $totalFiles = 0;
                            //use their sizes to compare it
                            foreach ($fileData['size'] as $fd) {
                                if ($fd !== 0) {
                                    $totalFiles += 1;
                                }
                            }
                            //do total files, totalmaxfiles
                            if (!($totalFiles <= $filesNum)) {
                                self::ovNewMultiFileError($inputName, $errMsg);
                                break;
                            }
                        }
                    }
                    //handle single file upload
                    else {
                        if ($rt === "R") {
                            //error message
                            $errMsg = (!empty($valData[1])) ? $valData[1] : "A valid file is required";
                            if (empty($fileData['name']) || empty($fileData['type']) || empty($fileData['size'])) {
                                self::ovNewError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "ACCEPT" && $fileData['name']) {
                            //attribute value
                            $requiredExts = (!empty($valData[1])) ? explode(",", str_replace(" ", "", $valData[1])) : self::ovDoException("The file extensions / MIME types to check must be provided after the \"ACCEPT\" rule");
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'This file ' . $fileData['name'] . ' is not supported';
                            //get current file extension
                            $currentExt = strtolower(substr($fileData['name'], strrpos($fileData['name'], ".")));
                            //------------------

                            if (!in_array($currentExt, $requiredExts)) {
                                self::ovNewError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "ACCEPT-MIME" && $fileData['name']) {
                            //attribute value
                            $requiredMime = (!empty($valData[1])) ? explode(",", str_replace(" ", "", $valData[1])) : self::ovDoException("The MIME types to check must be provided after the \"ACCEPT-MIME\" rule");
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'This file ' . $fileData['name'] . ' is not supported';
                            //check if current type is contained within array
                            if (!in_array(explode(substr($fileData['type'], strrpos($fileData['type'], "/")), $fileData['type'])[0] . "/*", $requiredMime) && !in_array($fileData['type'], $requiredMime)) {
                                self::ovNewError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "SIZE" && $fileData['name']) {
                            //attribute value
                            $fileSize = (!empty($valData[1])) ? strtolower($valData[1]) : self::ovDoException('A valid file size must be provided after the "SIZE" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please select a file that is equal to ' . $fileSize . '';
                            //get current file size
                            $currentFileSize = $fileData['size'];

                            if (self::getSizeInBytes($fileSize) !== $currentFileSize) {
                                self::ovNewError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "MINSIZE" && $fileData['name']) {
                            //attribute value
                            $fileSize = (!empty($valData[1])) ? strtolower($valData[1]) : self::ovDoException('A valid file size must be provided after the "MINSIZE" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please select a file that is greater than or equal to ' . $fileSize . '';
                            //get current file size
                            $currentFileSize = $fileData['size'];

                            if (!($currentFileSize >= self::getSizeInBytes($fileSize))) {
                                self::ovNewError($inputName, $errMsg);
                                break;
                            }
                        }
                        else if ($rt === "MAXSIZE" && $fileData['name']) {
                            //attribute value
                            $fileSize = (!empty($valData[1])) ? strtolower($valData[1]) : self::ovDoException('A valid file size must be provided after the "MINSIZE" rule');
                            //error message
                            $errMsg = (!empty($valData[2])) ? $valData[2] : 'Please select a file that is less than or equal to ' . $fileSize . '';
                            //get current file size
                            $currentFileSize = $fileData['size'];
                            if (!($currentFileSize <= self::getSizeInBytes($fileSize))) {
                                self::ovNewError($inputName, $errMsg);
                                break;
                            }
                        }
                    }
                }
            }
        }
        if (count(self::$errors) !== 0) {
            return false;
        }

        return true;
    }

    public static function getErrors()
    {
        $retval = array(
            self::$form => self::$errors
        );
        return ($retval);
    }
    //return error count
    public static function getStatus()
    {
        return (count(self::$errors));
    }

    //return version number
    public static function getVersion()
    {
        return (self::$version);
    }

    //return form id
    public static function getForm()
    {
        return (self::$form);
    }
    //return contributors
    public static function getCredits()
    {
        $retval = array(
            "author" => self::$author
        );
        return (json_encode($retval));
    }
}
//What a GREAT LIBRARY! :)

//----------------
?>