<?php namespace Atomino\Mosaic\GateWay;

use Atomino\Mosaic\Client\ApiRequest;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;

class GateWay {

	public function __construct(private Request $request, private array $routes, private FilesystemAdapter $cache) { }

	public function route() {
		if ($this->request->query->has("event")) {
			$this->handleEvent();
		} elseif ($this->request->query->has("invalidate-cache")) {
			$this->invalidate();
		} else {
			$this->handleApiRequest();
		}
	}

	private function handleEvent() {
		$event = $this->request->query->get("event");
		$handlers = array_key_exists($event, $this->routes["event"]["fqn"]) ? $this->routes["event"]["fqn"][$event] : [];
		(new Response("", 200, ["content-length" => 0, "connection" => "close"]))->send();
		flush();
		foreach ($this->routes["event"]["pattern"] as $pattern => $patternHandlers) {
			if (fnmatch($pattern, $event)) {
				foreach ($patternHandlers as $class => $handler) {
					if (!array_key_exists($class, $handlers)) $handlers[$class] = $handler;
				}
			}
		}
		foreach ($handlers as $class => $handler) {
			/** @var App $app */
			$app = new $class();
			if ($handler === true) {
				$app->handleEvent($event, $this->request->getContent(), true);
			} else {
				$app->$handler($event, json_decode($this->request->getContent()));
			}
		}
	}

	private function invalidate() {
		$prefix = md5(trim($this->request->query->get("invalidate-cache"), '/'));
		$this->cache->clear($prefix);
		(new Response("", 200))->send();
	}

	private function handleApiRequest() {

		$path = trim($this->request->getPathInfo(), '/');

		$prefix = md5($path);
		$key = $prefix . '-' . md5($this->request->getContent());
		if ($this->request->query->has("nocache")) $this->cache->deleteItem($key);

		$response = $this->cache->get(
			$key,
			function (ItemInterface $item) use ($path): Response {
				if (array_key_exists($path, $this->routes["api"])) {
					$handler = $this->routes["api"][$path];
					$item->expiresAfter($handler["cache"]);
					if (array_key_exists("url", $handler)) {
						$response = ApiRequest::create($handler["url"])->send($this->request->getContent(), true);
						return new Response($response->getBody(), $response->getStatusCode(), ["content-type" => $response->getHeader("content-type")]);
					} else {
						$class = $handler["class"];
						$method = $handler["method"];
						$app = new $class();
						return $app->$method(json_decode($this->request->getContent()), $this->request);
					}
				} else {
					return new Response("404", 404);
				}
			}
		);
		$response->send();
	}

}