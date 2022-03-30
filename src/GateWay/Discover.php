<?php

namespace Atomino\Mosaic\GateWay;

use Atomino\Mosaic\GateWay\Attributes\ApiBase;
use Atomino\Mosaic\GateWay\Attributes\Forward;
use Atomino\Mosaic\GateWay\Attributes\Handle;
use Atomino\Mosaic\GateWay\Attributes\Subscribe;
use Atomino\Neutrons\CodeFinder;
use Composer\Autoload\ClassLoader;

class Discover {
	public function __construct(private ClassLoader $classLoader, private string $namespace, private string $output) {

	}

	private function url($url, $baseUrl) {
		$scheme = parse_url($url, PHP_URL_SCHEME);
		if (is_null($scheme)) $url = trim($baseUrl, '/') . "/" . trim($url, '/');
		return $url;
	}

	public function discover() {
		$routes = [
			"event" => [
				"fqn"     => [],
				"pattern" => [],
			],
			"api"   => [],
		];

		$codefinder = new CodeFinder($this->classLoader);
		$classes = $codefinder->Psr4ClassSeeker($this->namespace);

		foreach ($classes as $class) {
			if (is_subclass_of($class, App::class)) {

				$classReflection = new \ReflectionClass($class);
				$methods = $classReflection->getMethods();

				$apiUrlBase = ApiBase::get($classReflection)->url;

				$subscriptions = Subscribe::all($classReflection);
				foreach ($subscriptions as $subscription) {
					$event = $subscription->event;
					if (str_ends_with($event, '*')) {
						if (!array_key_exists($event, $routes["event"]["pattern"])) $routes["event"]["pattern"][$event] = [];
						$routes["event"]["pattern"][$event][$class] = true;
					} else {
						if (!array_key_exists($event, $routes["event"]["fqn"])) $routes["event"]["fqn"][$event] = [];
						$routes["event"]["fqn"][$event][$class] = true;
					}
				}

				foreach ($methods as $method) {
					$subscriptions = Subscribe::all($method);
					foreach ($subscriptions as $subscription) {
						$event = $subscription->event;

						if (str_ends_with($event, '*')) {
							if (!array_key_exists($event, $routes["event"]["pattern"])) $routes["event"]["pattern"][$event] = [];
							$routes["event"]["pattern"][$event][$class] = $method->getName();
						} else {
							if (!array_key_exists($event, $routes["event"]["fqn"])) $routes["event"]["fqn"][$event] = [];
							$routes["event"]["fqn"][$event][$class] = $method->getName();
						}
					}
				}

				$forwards = Forward::all($classReflection);
				foreach ($forwards as $forward) {
					$routes["api"][$forward->path] = [
						"url"   => $this->url($forward->url, $apiUrlBase),
						"cache" => $forward->cache,
					];
				}

				foreach ($methods as $method) {
					$route = Handle::get($method);
					if (!is_null($route)) {
						$routes["api"][$route->path] = [
							"class"  => $class,
							"method" => $method->getName(),
							"cache"  => $route->cache,
						];
					}
				}
			}
		}

		file_put_contents($this->output, "<?php return " . var_export($routes, true) . ";");
	}

}