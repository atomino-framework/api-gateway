<?php namespace Atomino\Mosaic\Client;

class EventRequest {

	static public function create($url): static { return new static($url); }
	private function __construct(private string $url) { }

	public function send(string $event, mixed $data = [], bool $rawBody = false): void {

		$postData = $rawBody ? $data : json_encode($data);

		if (!is_string($postData)) throw new \Exception("Body must be a string");

		$endpointParts = parse_url($this->url);
		$endpointParts['path'] = ($endpointParts['path'] ?? '/') . "?event=" . $event;
		$endpointParts['port'] = $endpointParts['port'] ?? ($endpointParts['scheme'] === 'https' ? 443 : 80);

		$contentLength = strlen($postData);

		$request = "POST {$endpointParts['path']} HTTP/1.1\r\n";
		$request .= "Host: {$endpointParts['host']}\r\n";
		$request .= "Content-Length: {$contentLength}\r\n";
		$request .= "Content-Type: application/json\r\n\r\n";
		$request .= $postData;

		$prefix = str_starts_with($this->url, 'https://') ? 'tls://' : '';


		$socket = fsockopen($prefix . $endpointParts['host'], $endpointParts['port'], $e, $em, 5);
		if (!$socket) throw new \Exception("Event (" . $event . ") could not be sent to: " . $prefix . $endpointParts['host'] . ":" . $endpointParts['port'] . $endpointParts['path']);
		fwrite($socket, $request);
		fclose($socket);
	}
}