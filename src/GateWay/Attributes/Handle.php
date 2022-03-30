<?php namespace Atomino\Mosaic\GateWay\Attributes;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Handle extends \Atomino\Neutrons\Attr {
	public function __construct(public string $path, public int $cache = -1) {
		$this->path = trim($this->path, "/");
	}
}