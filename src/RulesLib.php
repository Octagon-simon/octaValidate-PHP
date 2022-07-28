<?php
/*
* octavalidate - PHP Rules Library
* This script helps the library to perform validations based on the rules
*/
function rulesLibrary()
{
    //check email
    function Validate_Email($email)
    {
        if (preg_match("/^[a-zA-Z0-9.!#$%&'*+\=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$/", $email)) {
            return true;
        }
        return false;
    }

    //check Alphabets only
    function Validate_ALPHA_ONLY($text)
    {
        if (preg_match("/^[a-zA-Z]+$/", $text)) {
            return true;
        }
        return false;
    }

    //check lowercase alphabets only
    function Validate_LOWER_ALPHA($text)
    {
        if (preg_match("/^[a-z]+$/", $text)) {
            return true;
        }
        return false;
    }

    //check uppercase alphabets only
    function Validate_UPPER_ALPHA($text)
    {
        if (preg_match("/^[A-Z]+$/", $text)) {
            return true;
        }
        return false;
    }

    //check Alphabets and spaces
    function Validate_ALPHA_SPACES($text)
    {
        if (preg_match("/^[a-zA-Z\s]+$/", $text)) {
            return true;
        }
        return false;
    }

    //check Alpha Numberic strings
    function Validate_ALPHA_NUMERIC($text)
    {
        if (preg_match("/^[a-zA-Z0-9]+$/", $text)) {
            return true;
        }
        else {
            return false;
        }
    }

    //check DATE mm/dd/yyyy
    //source https://stackoverflow.com/a/15196623
    function Validate_Date_MDY($date)
    {
        if (preg_match("/^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/", $date)) {
            return true;
        }
        return false;
    }

    //url 
    function Validate_Url($url)
    {
        if (preg_match("/^((?:http:\/\/)|(?:https:\/\/))(www.)?((?:[a-zA-Z0-9]+\.[a-z]{3})|(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1(?::\d+)?))([\/a-zA-Z0-9\.]*)$/i", $url)) {
            return true;
        }
        else {
            return false;
        }
    }

    //validate url with query params
    function Validate_Url_QP($url)
    {
        if (preg_match("/^((?:http:\/\/)|(?:https:\/\/))(www.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)$/i", $url)) {
            return true;
        }
        else {
            return false;
        }
    }

    //username
    function Validate_UserName($uname)
    {
        if (preg_match("/^[a-zA-Z][a-zA-Z0-9-_]+$/", $uname)) {
            return true;
        }
        else {
            return false;
        }
    }

    //password - 8 characters or more
    function Validate_PWD($password)
    {
        if (preg_match("/^((?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,})+$/", $password)) {
            return true;
        }
        else {
            return false;
        }
    }

    //Validates general text
    function Validate_TEXT($text)
    {
        if (preg_match("/^[a-zA-Z0-9\s\,\.\'\"\-\_\)\(\[\]\?\!\&\:\;\/]+$/", $text)) {
            return true;
        }
        else {
            return false;
        }
    }
}

?>