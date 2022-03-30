<?php namespace Atomino\Mosaic\Client;

use Symfony\Component\HttpClient\HttpClient;

class ApiRequest {

	private bool $noCache = false;

	static public function create($url): static { return new static($url); }
	private function __construct(private string $url) { }

	public function noCache(): static {
		$this->noCache = true;
		return $this;
	}

	public function send(mixed $data = [], bool $rawbody = false): ApiRequestResult {
		$body = $rawbody ? $data : json_encode($data);
		$headers = [];
		$headers["content-type"] = "application/json";
		$headers["accept"] = "application/json";
		$query = $this->noCache ? ["nocache" => "nocache"] : [];
		return new ApiRequestResult(HttpClient::create()->request("POST", $this->url, [
			"headers" => $headers,
			"body"    => $body,
			"query"   => $query,
		]));
	}
}