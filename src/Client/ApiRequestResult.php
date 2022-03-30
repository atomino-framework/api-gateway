<?php namespace Atomino\Mosaic\Client;

use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiRequestResult {
	public function __construct(protected ResponseInterface $response) { }
	public function getResponse(): ResponseInterface { return $this->response; }
	public function getStatusCode(): mixed { return $this->response->getStatusCode(false); }
	public function getHeaders(): array { return array_map(fn($v) => $v[0], $this->response->getHeaders(false)); }
	public function getHeader(string $header): mixed { return $this->getHeaders()[$header]; }
	public function getBody(): string { return $this->response->getContent(false); }
	public function getData(): mixed { return json_decode($this->getBody()); }
}