<?php if (phpversion() < '5.3.0') exit('Please upgrade PHP to 5.3 or above');

	require	dirname(__FILE__) . '/core/Form.php';
	require	dirname(__FILE__) . '/core/Email.php';
	require	dirname(__FILE__) . '/core/Response.php';
	require	dirname(__FILE__) . '/inc/Security.php';
	require	dirname(__FILE__) . '/vendors/swiftmailer/lib/swift_required.php';

	use phpgear\callback\core\Form;
	use phpgear\callback\core\Email;
	use phpgear\callback\core\Response;

	/** Used to help prevent direct access to some files. **/
	define('CALLBACK', true);

	/** Load the configuration file **/
	$configuration = require dirname(__FILE__) . '/config.php';

	/** Inject some test data **/
//	Callback_Security::generateCaptchaQuestion();
//	$_POST['name'] = 'rob';
//	$_POST['number'] = '12345';
//	$_POST['time'] = '    3434';
//	$_POST['bot_protection'] = '';
//	$_POST['csrf'] = Callback_Security::generateCsrf();
//	$_POST['captcha'] = Callback_Security::getCaptchaAnswer();
//	$_POST['captcha'] = '2';


	/** Check that the form posted is ok **/
	$form = new Form($configuration);
	$form->validate($_POST);

	if ($form->hasErrors()) Response::json($form->errors);

	/** Dispatch e-mail **/
	$email = new Email($form, $configuration);
	$email->send();

	if ($email->hasErrors()) Response::json($email->errors);

	Response::json(array('status'=>'success', 'response'=>$configuration['responses']['success']));
