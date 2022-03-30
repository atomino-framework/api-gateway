<?php namespace Atomino\Mosaic\GateWay\Attributes;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS)]
class Forward extends \Atomino\Neutrons\Attr {
	public function __construct(public string $path, public string $url, public int $cache = -1) {
		$this->path = trim($this->path, "/");
	}
}