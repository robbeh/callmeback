<?php if (!defined('CALLMEBACK')) exit('Forbidden.');

return array(

	/*
	|--------------------------------------------------------------------------
	| Subject
	|--------------------------------------------------------------------------
	|
	| The E-mail subject sent from the user.
	|
	*/

	'subject' => "Please call me back",

	/*
	|--------------------------------------------------------------------------
	| From
	|--------------------------------------------------------------------------
	|
	| The e-mail address that will show up as the sender.
	|
	*/

	'from' => "callmebackform@mywebsite.com",

	/*
	|--------------------------------------------------------------------------
	| Recipients
	|--------------------------------------------------------------------------
	|
	| Call back allows you to send the email to multiple addresses. To add
	| more simply add each address surrounded by single quotes and end with a
	| comma. Example..
	|
	| 'recipients' => array(
	| 		'my_email@google.com',
	| 		'my_other_address@hotmail.com';
	| 		'yet_another_otheraddress@yahoo.com';
	| ),
	|
	*/

	'recipients' => array(
		"myemailaddress@mywebsite.com",
	),

	/*
	|--------------------------------------------------------------------------
	| Form fields and validation
	|--------------------------------------------------------------------------
	|
	| With flexibility in mind, Call Back allows you to add or remove fields
	| to be sent. Not only that, you can specify what kind of validation
	| each field uses.
	|
	| The first value is the name of the field. The second value is the
	| validation rules Call Back supports which must be separated with a |
	|
	| *** Please note ***
	| If you add more fields then the "name" of the input must match the name
	| here in the config else it will not be used in the email.
	|
	| Example...
	|
	| <input name="my_field" type="text" />
	|
	| 'fields' => array(
	|	'my_field' => 'required|xss_clean',
	| ),
	|
	| Available validation rules:
	| required 			- User cannot submit the form with this field empty.
	| min_length[x] 	- The minimum length of characters where x is the number .
	| max_length[x] 	- The maximum length of characters where x is the number.
	| numbers_only 		- Only allow numbers (0-9).
	| letters_only 		- Only allow letters (a-z).
	| xss_clean 		- Removes malicious cross site scripting data.
	|
	*/

	'fields' => array(
		"name" 				=> "required|min_length[2]|max_length[20]|letters_only|xss_clean",
		"number" 			=> "required|min_length[10]|max_length[13]|numbers_only|xss_clean",
		"time" 				=> "required|min_length[5]|max_length[7]|xss_clean",
	),

	/*
	|--------------------------------------------------------------------------
	| Cross Site Request Forgery
	|--------------------------------------------------------------------------
	| Protect your form from being used by external sources. This will generate
	| a unique token that only your server knows about. Once posted, if the
	| token posted matches the server token then we know the form has been
	| sent from your server and nobody else's.
	|
	| values 'on' or 'off'
	|
	| The csrf_field is the 'name' of the hidden csrf field in your form.
	|
	| Example:
	| <input type="hidden" name="csrf" value="<?php print callmeback_Security::generateCsrf(); ?>" />
	|
	*/

	'enable_csrf'		=> "on",
	'csrf_field_name'	=> "csrf",

	/*
	|--------------------------------------------------------------------------
	| Captcha for humans
	|--------------------------------------------------------------------------
	| A easy to read captcha that displays an easy to understand challenge.
	|
	| Example:
	| What is the sum of 3 and 5?
	|
	| values 'on' or 'off'
	|
	| Usage example..
	| <label for="captcha"><?php print Callmeback_Security::generateCaptchaQuestion(); ?></label>
	| <input type="text" name="captcha" value="" />
	|
	*/

	'enable_captcha'		 => "on",
	'captcha_field_name'	 => "captcha",

	/*
	|--------------------------------------------------------------------------
	| Bot protection
	|--------------------------------------------------------------------------
	|
	| One way to help prevent auto form submissions from bots it to have a
	| hidden input field. If this field gets filled in by anybody then call back
	| will not send the e-mail.
	|
	| The advantage to this is that the form is hidden from humans but not from bots.
	|
	| values 'on' or 'off'
	|
	| To add the field to your form use an input tag similar to the following.
	| (make sure the name matches your 'bot_field_name' setting)

	| <input type="text" name="bot" id="bot" style="display: none;"  />
	|
	*/

	'enable_bot_protection' => "on",
	'bot_field_name'	 	=> "bot",

	/*
	|--------------------------------------------------------------------------
	| SMTP settings
	|--------------------------------------------------------------------------
	|
	| If you would like to use SMTP to transport your emails then fill in
	| the four SMTP settings below. Alternatively, if you "any" leave these
	| settings blank then the default PHP mail method will be used.
	|
	*/

	'smtp_username' => "",
	'smtp_password' => "",
	'smtp_port' 	=> "",
	'smtp_host' 	=> "",

	/*
	|--------------------------------------------------------------------------
	| Email format
	|--------------------------------------------------------------------------
	|
	| If you would like the E-mail to be sent as HTML then leave this set
	| to the default option. If you prefer plain text then choose the
	| other option.
	|
	| Options:
	| 'text/html'
	| 'text/plain'
	|
	*/

	'email_format' => "text/html",

	/*
	|--------------------------------------------------------------------------
	| Response messages
	|--------------------------------------------------------------------------
	|
	| Customise each message however you like. If you would like to output
	| which field was given an error then just add {field} to the message.
	|
	*/

	'responses' => array(
		'success' 			=> "Thank you. We will give you a call back in a short moment.",
		'required' 			=> "Field {field} was not filled in.",
		'csrf' 				=> "Failed to match csrf token.",
		'captcha'			=> "Captcha answer incorrect",
		'min_length'		=> "Field {field} did not contain enough characters",
		'max_length'		=> "Field {field} contained too many characters",
		'numbers_only'		=> "Field {field} can only contain numbers.",
		'letters_only'		=> "Field {field} can only contain letters.",
		'no_posts'			=> "The form was not submitted.",
		'missing_bot'		=> "Bot protection is enabled but the field was not posted.",
		'missing_fields'	=> "Some fields were not posted. Check their names match the fields config.",
		'message_to_bot'	=> "Thank you for submitting your details.",
	),

);

