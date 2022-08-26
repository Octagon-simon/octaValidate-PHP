# <img align="center" src="https://octagon-simon.github.io/assets/img/octavalidate-php.png" width="25px"> octaValidate-PHP V1.4

This is a feature-rich Library that helps to validate your forms server-side using sophisticated regular expressions, PHP's inbuilt validation, and validation rules.

We have included a demo folder containing some forms with validation rules applied to each of them. Open any of the files in your local server and submit the form.

This Library also helps to validate your frontend forms using JavaScript. [Visit the repository](https://github.com/Octagon-simon/octaValidate)

## DOCUMENTATION

Visit the [DOCUMENTATION](https://octagon-simon.github.io/projects/octavalidate/php/) to learn more about this GREAT Library, and play with the forms there!

## INSTALL

### COMPOSER

```
$ composer require simon-ugorji/octavalidate-php
```

### LOCAL

- Download and import the latest release to your project.
- In your project, use the require keyword & include the file **Validate.php**
- Now with the `use` keyword, link the class to your project and create a new instance of the class by passing in the form id as the **first argument** and any configuration as the **second argument**.

```php
require 'src/Validate.php';

use Validate\octaValidate;

$myForm = new octaValidate('FORM_ID', 'CONFIG_OPTIONS');
```

## How to Use

- Define validation rules for the form inputs
- Invoke the `validate()` method and pass in the rules as an argument.
  
```php
//require the library
require 'src/Validate.php';

use Validate\octaValidate;

//create new instance
$myForm = new octaValidate('FORM_ID', 'CONFIG_OPTIONS');

//syntax for defining validation rules
$valRules = array(
  "FORM_INPUT_NAME" => array(
    ["RULE_TITLE", "CUSTOM_ERROR_MESSAGE"]
  )
);

/* If you don't provide a custom error message, 
the script will use the default one available
*/

//define rules for each form input name
$valRules = array(
  "uname" => array(
    ["R", "Your username is required"]
 ),
  "email" => array(
    ["R", "Your Email Address is required"],
    ["EMAIL", "Your Email Address is invalid!"]
) );

//begin validation
if ($myForm->validate($valRules) === true){
    //process form data here
}else{
  //return errors
  print_r(json_encode($myForm->getErrors()));
}
```
The validate method returns a `boolean`.

- `true` means there are no validation errors
- `false` means there are validation errors

> Make sure that all input elements have a **unique name**.

## VALIDATION RULES

Here are the inbuilt validation rules.

- R - A value is required.
- ALPHA_ONLY - The value must be letters only! (lower-case or upper-case).
- LOWER_ALPHA - The value must be lower-case letters only.
- UPPER_ALPHA - The value must be upper-case letters only.
- ALPHA_SPACES - The value must contain letters or Spaces only!
- ALPHA_NUMERIC - The value must contain letters and numbers.
- DATE_MDY - The value must be a valid date with the format mm/dd/yyyy.
- DIGITS - The value must be valid digits or numbers. 
- PWD - The value must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters. 
- EMAIL - The value must be a valid Email Address.
- URL - The value must be a valid URL
- URL_QP - The value must be a valid URL and may contain Query parameters.
- USERNAME - The value may contain letters, numbers, a hyphen or an underscore.
- TEXT - The value may contain any of these special characters (. , / () [] & ! '' "" : ; ?)

Didn't see the validation rule that you need for your form? Don't worry!

With this library, you have the power to define your custom rule and it will be processed as if it were an inbuilt rule.
  
## CUSTOM VALIDATION RULES

Syntax for defining a custom rule

```php
$myForm->customRule($RULE_TITLE, $REG_EXP, $ERROR_TEXT);
```
Here's a custom rule to validate a password.

```php
//require the library
require 'src/Validate.php';

use Validate\octaValidate;

//create new instance
$myForm = new octaValidate('my_form');

//custom password validation
$rule_title = 'PASS';
$reg_exp = '/12345/';
$err_txt = "Please enter 12345";
//build the rule
$myForm->customRule($rule_title, $reg_exp, $err_txt);

//provide the rule title when defining validation rules
$valRules = array(
    "password" => array(
        ["PASS"]
    )
);
```

## MORE CUSTOM RULES

What if you want to define more validation rules?

All you have to do is to create an array that will contain the rule title as the array's property, and its value is another array containing the rule's regular expression, and error text separated by a comma.

Here's the syntax
```php
$RULES = array(
    "RULE_TITLE" => ['REG_EXP', 'CUSTOM_ERROR_MESSAGE']
);
$myForm->moreCustomRules($RULES);
```
Here's a sample custom username & password validation rule

```php
//require the library
require 'src/Validate.php';

use Validate\octaValidate;

$myForm = new octaValidate('my_form');

//custom username & password validation
$rules = array(
    "PASS" => ['/12345/', "Please enter 12345"],
    "UNAME" => ['/simon/', "Please enter simon"]
);

//build the rule
$myForm->moreCustomRules($rules);

//provide the rule title when defining validation
$valRules = array(
    "username" => array( 
        ["UNAME"] 
    ),
    "password" => array( 
        ["PASS"] 
    )
);
```
> Note that: All Rule Titles are **case-sensitive!**

## ATTRIBUTES VALIDATION

Currently we have 4 types of attribute validation:

- length validation
- EqualTo validation
- Size validation
- File Validation
  
All attribute validation follows the syntax below

```php
//syntax
$valRules = array(
  "FORM_INPUT_NAME" => array(
    ["ATTRIBUTE_TITLE", "VALUE", "CUSTOM_ERROR_MESSAGE"]
  )
);
```
### LENGTH VALIDATION

You can check the number of characters provided by the user using this validation.

- maxlength (5) - This means that value must be 5 characters or less.
- minlength (5) - This means that value must be up to 5 characters or more.
- length (5) - This means that value must be equal to 5 characters.

For Example;

```php
//sample validation
$valRules = array(
  "username" => array(
    ["R", "Your username is required"],
    ["MINLENGTH", "5", "Your username must contain 5 characters or more"]
 ),
  "age" => array(
    ["R", "Your age is required"],
    ["LENGTH", "2", "Your Age must contain 2 digits!"]
  )
);
```
### EQUALTO VALIDATION

You can check if two inputs contain the same values using the rule **EQUALTO**. The value of this validation rule must be the other **input name** you wish to check against.

```php
//sample validation
$valRules = array(
  "password" => array(
    ["R", "Your password is required"],
    ["EQUALTO", "confirm_password", "Both passwords do not match"]
  )
);
```
### FILE VALIDATION

Within File validation, we have rules such as;

- ACCEPT - Use this rule to list out the file extensions allowed for upload. Eg. .png, .jpeg.
- ACCEPT-MIME - Use this rule to list out the file MIME types allowed for upload. It supports a wildcard. Eg audio/*, image/*
- SIZE - This rule makes sure that the size of the file or files provided must be equal to the specified value.
- MINSIZE - This rule makes sure that the size of the file or files provided must be up to the specified value or more.
- MAXSIZE  - This rule makes sure that the size of the file or files provided must be the specified value or less.
- FILES, MINFILES, MAXFILES

> Note that **size, minsize & maxsize** works on both single or multiple file upload.

For example;

```php
//sample validation
$formRules = array(
    //single file upload
    "file" => array(
        ["R"],
        ["ACCEPT", ".mp3, .ogg"],
        ["MAXSIZE", "5mb"]
    ),
    //multiple files upload
    "files" => array(
        ["R"],
        ["ACCEPT-MIME", "image/*"],
        ["MAXFILES", "5"],
        ["MAXSIZE", "50mb"]
    )
);
```
Please refer to the [documentation](https://octagon-simon.github.io/projects/octavalidate/php/file.html) to learn more file validation rules.

## API METHODS

### STATUS

Invoke the **getStatus()** method to return the number of validation errors on the form.

```php
//require the library
require 'src/Validate.php';

use Validate\octaValidate;

//create new instance
$myForm = new octaValidate('my_form');

//return error count
$myForm->getStatus();
```

## CONFIGURATION

We have 3 configuration options:

- stripTags: <code>Boolean</code>
  
  Just like PHP's inbuilt `stripTags` function, this option loops through the POST Array and removes anything enclosed within a tag. Default value is `false`.

- strictMode: <code>Boolean</code>
  
  This option removes extra white space from the start and at the end of a form input and also prevents the user from providing reserved keywords as values. Default value is **false**.
- strictWords: <code>Array</code>
  
   This option alows you to provide words that users are not supposed to submit. For eg ["null", "error", "false"]. In order to use this option, you must set **strictMode** to **true**.

To use any of these options, provide it as an array and pass it as the second argument when creating an instance of octaValidate.

```php
//require the library
require 'src/Validate.php';

use Validate\octaValidate;

//set configuration
$options = array(
  "stripTags" => true,
  "strictMode" => true,
  "strictWords" => ["null", "undefined"]
);
//create new instance
$myForm = new octaValidate('FORM_ID', $options);
```

## REFERENCE METHODS
After creating a new instance of the function, the methods below becomes available for use.

```php
//create instance of the function
$myForm = new octaValidate('FORM_ID');
```

- **validate()**
  
  Invoke this method to begin validation
- **getStatus()** 
  
  Invoke this method to see the number of validation errors on a form
- **getForm()** 
  
  This method returns the form ID.
- **customRule(RULE_TITLE, REG_EXP, ERROR_TEXT)**
  
   Invoke this method to define your custom validation rule.
- **moreCustomRules(RULES)**
  
    Invoke this method to define more custom validation rules.
- **getVersion()**
  
  Invoke this method to retrieve the library's version number.

> There are more methods in the documentation, Please refer to the [documentation](https://octagon-simon.github.io/projects/octavalidate/php/api.html) to learn more.


## DEMO

- Open any file within the demo folder and submit the form or visit the [documentation](https://octagon-simon.github.io/projects/octavalidate/php/) and submit the forms there.

## Author

[Simon Ugorji](https://twitter.com/ugorji_simon)

## Support Me

[Donate with PayPal](https://www.paypal.com/donate/?hosted_button_id=ZYK9PQ8UFRTA4)

## Contributors

[Simon Ugorji](https://twitter.com/ugorji_simon)