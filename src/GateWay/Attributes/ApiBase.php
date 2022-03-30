<?php namespace Atomino\Mosaic\GateWay\Attributes;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS)]
class ApiBase extends \Atomino\Neutrons\Attr {
	public function __construct(public string $url) {
		$this->url = trim($this->url, "/").'/';
	}
}