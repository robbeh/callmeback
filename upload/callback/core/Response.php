<?php namespace phpgear\callback\core;

class Response {

	/**
	 * Return an array as
	 * json to the browser.
	 *
	 * @param array $data
	 */
	public static function json(Array $data)
	{
		header('Cache-Control: no-cache, must-revalidate');
		header("Expires: 0");
		header('Content-type: application/json');
		print json_encode($data);
		exit;
	}
}