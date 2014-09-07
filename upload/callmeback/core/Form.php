<?php namespace phpgear\callmeback\core;

/**
 * Responsible for being the middle man
 * between the html form and dispatching
 * the e-mail. This class will perform
 * various validation rules and data
 * cleansing on HTTP POST using rules
 * set in the config.php file.
 *
 * @package callmeback
 **/

class Form {

	/**
	 * Configuration container
	 *
	 * @var array
	 */
	public $config;

	/**
	 * Holds all thrown
	 * errors ready to be
	 * sent back to the user.
	 *
	 * @var array
	 */
	public $errors = array();

	/**
	 * Contains everything
	 * sent in $_POST
	 *
	 * @var array
	 */
	public $post = array();

	/**
	 * When iterating over
	 * each field its nice
	 * to know which field
	 * currently being
	 * worked on.
	 *
	 * @var
	 */
	private $currentField;

	/**
	 * Holds all the fields
	 * set in the config.php
	 * These are the fields
	 * we expect to get from
	 * the form POST.
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * @param array $config
	 */
	public function __construct(Array $config)
	{
		$this->config = $config;
	}

	/**
	 * Controls how the posted fields
	 * are cleaned and prepared for email.
	 *
	 * @param $post
	 * @return bool
	 */
	public function validate(Array $post)
	{
		if (count($post) < 1) {
			$this->logError($this->config['responses']['no_posts']);
			return false;
		}

		$this->post = $this->trimPost($post);
		$this->fields = $this->getFields();

		if ($this->checkThatAllFieldsWerePosted() === false) return false;

		try
		{
			if ($this->isCsrfEnabled()) $this->validateCsrfPost();
			if ($this->isCaptchaEnabled()) $this->validateCaptchaPost();
			if ($this->isBotProtectionEnabled()) $this->validateBotPost();
		}
		catch (\Exception $e)
		{
			$this->logError($e->getMessage());
		}

		$this->validatesFieldsByRules();
	}

	/**
	 * Confirm any errors with bool
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		if (count($this->errors) > 0)
			return true;
	}

	/**
	 * Here we need to check that all
	 * form fields declared in the
	 * config file are present in
	 * the POST array. If not then
	 * stop else we will get errors
	 * further down the line.
	 *
	 * @return bool
	 */
	private function checkThatAllFieldsWerePosted()
	{
		foreach ($this->config['fields'] as $k => $v) {
			if (isset($this->post[$k]) == false) {
				$this->logError($this->config['responses']['missing_fields']);
				return false;
			}
		}
	}

	/**
	 * @return mixed
	 * @throws \Exception
	 */
	private function getFields()
	{
		if (count($this->config['fields']) < 1) {
			throw new \Exception('No fields specified');
		}
		return $this->config['fields'];
	}

	/**
	 * Iterate over all rules
	 * set in the config.php
	 * for each field and
	 * passes the rule to a
	 * new method which chooses
	 * the relevant validation.
	 */
	private function validatesFieldsByRules()
	{
		foreach ($this->fields as $field => $rules) {
			if (!empty($rules)) {
				$rules = explode('|', $rules);
				$this->currentField = $field;
				foreach ($rules as $rule) {
					$this->runValidationRule($rule);
				}
			}
		}
	}

	/**
	 * Chooses which type of
	 * rule to execute. There
	 * is currently 3 formats
	 * of rules.
	 *
	 * @param $rule
	 * @return mixed
	 */
	private function runValidationRule($rule)
	{
		try {

			/** First check for a char length rule **/
			if (strpos($rule, '[') !== false)
				return $this->runLengthValidationRule($rule);

			/** Next check for an _ rule **/
			if (strpos($rule, '_') !== false)
				return $this->runUnderscoreRule($rule);

			/** All other rules **/
			return $this->runSingleNameRule($rule);

		} catch (\Exception $e) {
			$this->logError($e->getMessage());
		}
	}

	/**
	 * Runs methods that check for lengths
	 * e.g. max_length[4]
	 *
	 * @param $rule
	 * @return mixed
	 */
	private function runLengthValidationRule($rule)
	{
		$r = explode('[', $rule);
		$method = 'rule'.str_replace('_length', '', ucfirst($r[0])).'Length';
		$length = str_replace(']', '', $r[1]);
		if (method_exists($this, $method)) {
			return $this->$method($length);
		}
	}

	/**
	 * Returns "rule" methods that contain
	 * an underscore. E.g. xss_clean
	 *
	 * @param $rule
	 * @return mixed
	 */
	private function runUnderscoreRule($rule)
	{
		$r = explode('_', $rule);
		$method = 'rule'.ucfirst($r[0]).ucfirst($r[1]);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
	}

	/**
	 * Runs rule methods that are single
	 * words only. E.g. required
	 *
	 * @param $rule
	 * @return mixed
	 */
	private function runSingleNameRule($rule)
	{
		$method = 'rule'.ucfirst($rule);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
	}

	/**
	 * @return bool
	 */
	private function isCsrfEnabled()
	{
		if (strtolower($this->config['enable_csrf']) == 'on')
			return true;
	}

	/**
	 * @return bool
	 */
	private function isCaptchaEnabled()
	{
		if (strtolower($this->config['enable_captcha']) == 'on')
			return true;
	}

	/**
	 * @return bool
	 */
	private function isBotProtectionEnabled()
	{
		if (strtolower($this->config['enable_bot_protection']) == 'on')
			return true;
	}

	/**
	 * Makes sure that the
	 * CSRF field value matches
	 * the original token
	 * generated when the page loaded.
	 *
	 * @throws \Exception
	 */
	private function validateCsrfPost()
	{
		if (!isset($this->post[$this->config['csrf_field_name']]))
			throw new \Exception($this->config['responses']['csrf']);

		if (\Callmeback_Security::getCsrf() !== $this->post[$this->config['csrf_field_name']])
			throw new \Exception($this->config['responses']['csrf']);
	}

	/**
	 * Checks to see if the
	 * captcha answer matches
	 * the answer expected.
	 *
	 * @throws \Exception
	 */
	private function validateCaptchaPost()
	{
		if (!isset($this->post[$this->config['captcha_field_name']]))
			throw new \Exception($this->config['responses']['captcha']);

		if ((int)\Callmeback_Security::getCaptchaAnswer() !== (int)$this->post[$this->config['captcha_field_name']])
			throw new \Exception($this->config['responses']['captcha']);
	}

	/**
	 * The hidden bot field
	 * should never have a value.
	 * If it does then we know
	 * this is a bot and can
	 * stop the e-mail being sent.
	 *
	 * @throws \Exception
	 */
	private function validateBotPost()
	{
		if (!isset($this->post[$this->config['bot_field_name']]))
			throw new \Exception($this->config['responses']['missing_bot']);

		if (!empty($this->post[$this->config['bot_field_name']]))
			throw new \Exception($this->config['responses']['message_to_bot']);
	}

	/**
	 * The required rule makes sure
	 * the field has a value.
	 *
	 * @throws \Exception
	 */
	private function ruleRequired()
	{
		if (empty($this->post[$this->currentField])) {
			$e = Form::placeHolderParser($this->config['responses']['required'], $this->currentField);
			throw new \Exception($e);
		}
	}

	/**
	 * The minimum length rule will
	 * make sure the post is bigger
	 * than the required length.
	 *
	 * @param $length
	 * @throws \Exception
	 */
	private function ruleMinLength($length)
	{
		if (strlen($this->post[$this->currentField]) < $length) {
			$e = Form::placeHolderParser($this->config['responses']['min_length'], $this->currentField);
			throw new \Exception($e);
		}
	}

	/**
	 * The maximum length rule will
	 * make sure the post is smaller
	 * than the required length.
	 *
	 * @param $length
	 * @throws \Exception
	 */
	private function ruleMaxLength($length)
	{
		if (strlen($this->post[$this->currentField]) > $length) {
			$e = Form::placeHolderParser($this->config['responses']['max_length'], $this->currentField);
			throw new \Exception($e);
		}
	}

	/**
	 * Numbers only errors when
	 * the post contains a non
	 * numeric character.
	 *
	 * @throws \Exception
	 */
	private function ruleNumbersOnly()
	{
		if (is_numeric($this->post[$this->currentField]) === false) {
			$e = Form::placeHolderParser($this->config['responses']['numbers_only'], $this->currentField);
			throw new \Exception($e);
		}
	}

	/**
	 * The letters only rule will
	 * error when a post contains
	 * none alpha characters.
	 *
	 * @throws \Exception
	 */
	private function ruleLettersOnly()
	{
		if (ctype_alpha($this->post[$this->currentField]) === false) {
			$e = Form::placeHolderParser($this->config['responses']['letters_only'], $this->currentField);
			throw new \Exception($e);
		}
	}

	/**
	 * Takes care of any cross
	 * site scripting attack chars
	 * in the post e.g. <script>
	 *
	 * @return string
	 */
	private function ruleXssClean()
	{
		return htmlspecialchars($this->post[$this->currentField], ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Replaces a single value in a
	 * string surrounded by { }
	 *
	 * @param $placeholder
	 * @param $replacement
	 * @return mixed
	 */
	public static function placeHolderParser($placeholder, $replacement)
	{
		$response = preg_replace('#\{.*?\}#s', $replacement, $placeholder);
		return $response;
	}

	/**
	 * Simple removes whitespace from
	 * the front and end of each post.
	 *
	 * @param $post
	 * @return array
	 */
	private function trimPost($post)
	{
		$trimmed = array();
		if (is_array($post)) {
			foreach ($post as $p => $v) {
				$trimmed[$p] = trim($v);
			}
		}
		return $trimmed;
	}

	/**
	 * @param $error
	 */
	private function logError($error)
	{
		$this->errors['response'][] = $error;
		$this->errors['status'] = 'error';
	}
}
