<?php
/**
 * php-prowl
 *
 * This class provides a simple mechanism for interacting with the prowlapp.com
 * API service for pushing notifications to iOS devices.
 * @author Dan Chen <dan@djc.me>
 * @author Scott Wilcox <scott@dor.ky>
 * @version 0.1
 * @package prowl
 */

class Prowl {

	private $config = array(
		'apiUrl' => 'https://api.prowlapp.com/publicapi/',
		'userAgent' => 'php-prowl 0.1',
		'apiKey' => null,
		'apiProviderKey' => null,
		'requestMethod' => 'GET',
		'debug' => false
	);

	private $remainingCalls = 0;
	private $resetDate = 0;

	public function __construct($settings) {
		foreach ($settings as $setting => $value) {
			$this->config[$setting] = $value;
		}
	}

	public function getConfig() {
		return $this->config;
	}

	private function buildQuery($params) {
		$query = '';
		if ($this->config['apiKey'] !== null) {
			$query .= 'apikey=' . $this->config['apiKey'] . '&';
		} else if ($this->config['apiProviderKey'] !== null) {
			$query .= 'providerkey=' . $this->config['apiProviderKey'] . '&';
		}

		if (count($params)) {
			foreach ($params as $key => $value) {
				$query .= $key . '=' . urlencode($value) . '&';
			}
			rtrim($query, '&');
		}

		return substr($query, 0, -1);
	}

	public function add($fields) {
		if (empty($this->config['apiKey'])) {
			throw new Exception('No API key set.');
		}

		return $this->request('add', $fields);
	}
	
	private function request($method, $params = null) {
		// Push the request out to the API
		$url = $this->config['apiUrl'] . $method;		
		$params = $this->buildQuery($params);

		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $params);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_HTTPHEADER, array("Expect:"));
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);		
		curl_setopt($c, CURLINFO_HEADER_OUT, true);
		curl_setopt($c, CURLOPT_USERAGENT, $this->config['userAgent']);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($c, CURLOPT_TIMEOUT, 6);

		$response = curl_exec($c);
		curl_close($c);
		if ($this->config['debug'] === true) {
			print_r($response);
		}
		
		$data = simplexml_load_string($response);
		if (!$data) {
			throw new Exception('Could not parse response: ' . var_export($response));
		}
		
		if (isset($data->error)) {
			throw new Exception($data->error);
		} else if (isset($data->success)) {
			$this->remainingCalls = $data->success["remaining_calls"];
			$this->resetDate = $data->success["resetdate"];
			return true;
		} else {
			throw new Exception('Unknown response: ' . var_export($response));
		}
	}

}
