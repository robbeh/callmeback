<?php namespace phpgear\callmeback\core;
/**
 * A simple class that makes
 * use of the excellent swiftmailer
 * to transport the e-mail.
 *
 * Class Email
 * @package phpgear
 */

class Email {

	/**
	 * Configuration container
	 *
	 * @var array
	 */
	private $config;

	/**
	 * @var
	 */
	private $transport;

	/**
	 * Form class container
	 * allows us access to the
	 * posted fields.
	 *
	 * @var \Form
	 */
	private $form;

	/**
	 * Swift mailer instance
	 *
	 * @var \Swift_Message
	 */
	private $message;

	/**
	 * @var array
	 */
	public $errors = array();

	/**
	 * @param Form $form
	 * @param array $config
	 */
	public function __construct(Form $form, Array $config)
	{
		$this->form = $form;
		$this->config = $config;
		$this->message = \Swift_Message::newInstance();
	}

	/**
	 * Builds and sends the e-mail
	 */
	public function send()
	{
		try
		{
			$this->message
				->setSubject($this->config['subject'])
				->setFrom($this->config['from'])
				->setTo($this->config['recipients'])
				->setBody($this->getEmailBody(), $this->config['email_format']);

			if ($this->isSmtpRequired()) {
				$this->transport = $this->getSmtpTransport();
			} else {
				$this->transport = $this->getMailTransport();
			}

			$mailer = \Swift_Mailer::newInstance($this->transport);
			$mailer->send($this->message);
		}
		catch (\Exception $e)
		{
			$this->errors['response'][] = $e->getMessage();
			$this->errors['status'] = 'error';
		}
	}

	/**
	 * Returns a parsed version of
	 * the e-mail template only
	 * if there are any fields
	 * setup in the config.
	 *
	 * @return mixed
	 */
	private function getEmailBody()
	{
		if (is_array($this->config['fields'])) {
			$template_contents = file_get_contents('email_template.php');

			/** Quick fix to remove the php exit from the template file **/
			$template = $this->stripFirstLineFromData($template_contents);

			return $this->parseTemplate($template);
		}
	}

	/**
	 * Determine if smtp is
	 * to be used. If any
	 * setting is not set
	 * then smtp will not
	 * be used.
	 *
	 * @return bool
	 */
	private function isSmtpRequired()
	{
		if (
				empty($this->config['smtp_host'])
			||  empty($this->config['smtp_username'])
			||  empty($this->config['smtp_password'])
			||  empty($this->config['smtp_port'])) {
			return false;
		}
		return true;
	}

	/**
	 * Create the transport instance
	 * using the smtp settings.
	 *
	 * If we get an error here then
	 * there is no fall back and
	 * the error will be displayed.
	 *
	 * @return Swift_SmtpTransport
	 */
	private function getSmtpTransport()
	{
		return \Swift_SmtpTransport::newInstance($this->config['smtp_host'], $this->config['smtp_port'])
			->setUsername($this->config['smtp_username'])
			->setPassword($this->config['smtp_password']);
	}

	/**
	 * No smtp bing used so work
	 * with PHP mail()
	 *
	 * @return \Swift_SmtpTransport
	 */
	private function getMailTransport()
	{
		return \Swift_SmtpTransport::newInstance();
	}

	/**
	 * A simple yet affective template
	 * parser. Replaces anything in
	 * between {} with the same name
	 * as a field in the config.
	 *
	 * @param $template
	 * @return mixed
	 */
	private function parseTemplate($template)
	{
		/** Match fields to their values **/
		foreach ($this->config['fields'] as $name => $value) {
			$v[$name] = (isset($this->form->post[$name]))?$this->form->post[$name]:'['.$name.': Not posted]';
		}

		/** Replace fields and remove template brackets **/
		$parsed = str_replace(array_keys($v), array_values($v), $template);
		$rendered = str_replace(array('{','}'), '', $parsed);

		return $rendered;
	}

	/**
	 * The template file contains an
	 * exit on line 1 to prevent
	 * external access to the file.
	 * This is a quick solution
	 * that removes the first line
	 * from the returned contents
	 * so that its excluded in
	 * the e-mail.
	 *
	 * @param $data
	 * @return string
	 */
	private function stripFirstLineFromData($data)
	{
		$t = explode("\n", $data);
		array_shift($t);
		return implode('', $t);
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
}