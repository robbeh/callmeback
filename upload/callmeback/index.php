<?php if (phpversion() < '5.3.0') exit('Please upgrade PHP to 5.3 or above');

	require	dirname(__FILE__) . '/core/Form.php';
	require	dirname(__FILE__) . '/core/Email.php';
	require	dirname(__FILE__) . '/core/Response.php';
	require	dirname(__FILE__) . '/inc/Security.php';
	require	dirname(__FILE__) . '/vendors/swiftmailer/lib/swift_required.php';

	use phpgear\callmeback\core\Form as CallMeBackForm;
	use phpgear\callmeback\core\Email as CallMeBackEmail;
	use phpgear\callmeback\core\Response as CallMeBackResponse;

	/** Used to help prevent direct access to some files. **/
	define('CALLMEBACK', true);

	/** Load the configuration file **/
	$configuration = require dirname(__FILE__) . '/config.php';

	/** Check that the form posted is ok **/
	$form = new CallMeBackForm($configuration);
	$form->validate($_POST);

	if ($form->hasErrors()) CallMeBackResponse::json($form->errors);

	/** Dispatch e-mail **/
	$email = new CallMeBackEmail($form, $configuration);
	$email->send();

	if ($email->hasErrors()) Response::json($email->errors);

    CallMeBackResponse::json(array('status'=>'success', 'response'=>$configuration['responses']['success']));
