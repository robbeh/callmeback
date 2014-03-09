<?php
/**
 * A simple, yet affective security resource
 * to generate csrf tokens and captcha
 * questions.
 *
 * Class Security
 */

class Callback_Security {

	/**
	 * Creates a unique md5 hash
	 * used in a hidden input.
	 * If the posted input matches
	 * The generated hash then we
	 * know that the form was submitted
	 * from our server and not externally.
	 *
	 * @return string
	 */
	public static function generateCsrf()
	{
		if (isset($_SESSION) === false) session_start();

		$_SESSION['token'] = md5(uniqid(rand(), true));

		return $_SESSION['token'];
	}

	/**
	 * Creates a very simple
	 * captcha question.
	 *
	 * @return string
	 */
	public static function generateCaptchaQuestion()
	{
		if (isset($_SESSION) === false) session_start();

		$x = rand(0,5);
		$y = rand(0,5);
		$_SESSION['human_captcha'] = $x + $y;

		return 'What is the sum of '. $x . ' and ' . $y . '?';
	}

	/**
	 * Internal use only
	 *
	 * @return mixed
	 */
	public static function getCsrf()
	{
		if (isset($_SESSION) === false) session_start();

		if (isset($_SESSION['token']) === true) return $_SESSION['token'];
	}

	/**
	 * Internal use only
	 *
	 * @return mixed
	 */
	public static function getCaptchaAnswer()
	{
		if (isset($_SESSION) === false) session_start();

		if (isset($_SESSION['human_captcha']) === true) return $_SESSION['human_captcha'];
	}
}