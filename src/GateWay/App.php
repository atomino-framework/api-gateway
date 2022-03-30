<?php namespace Atomino\Mosaic\GateWay;

use Atomino\Mosaic\Client\EventRequest;
use Atomino\Mosaic\GateWay\Attributes\ApiBase;
use Atomino\Mosaic\GateWay\Attributes\EventHandler;

abstract class App {
	protected function getEventHandlerUrl(): string {
		return EventHandler::get(static::class)->url;
	}
	protected function getApiUrl(string|null $path = null): string {
		return ApiBase::get(static::class)->url . (is_null($path) ? "" : "/" . trim($path, "/"));
	}
	public function handleEvent(string $event, mixed $data, bool $rawbody = true) {
		EventRequest::create($this->getEventHandlerUrl())->send($event, $data, $rawbody);
	}
}