<?php namespace Atomino\Mosaic\GateWay\Attributes;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Subscribe extends \Atomino\Neutrons\Attr {
	public function __construct(public string $event) { }
}