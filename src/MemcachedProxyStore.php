<?php namespace jrub\LaravelMemcachedProxyDriver;

use Illuminate\Contracts\Cache\Store;

class MemcachedProxyStore implements Store {
	protected $read_methods = ['get'];
	protected $write_methods = ['put', 'increment', 'decrement', 'forever', 'forget', 'flush'];

	protected $reader;
	protected $writers;

	protected $prefix;

	public function __construct($prefix = null) {
		$this->writers = collect();
		$this->prefix = $prefix;
	}

	public function addConnection(Store $Store, $mode = 'rw') {
		if ($this->isReader($mode)) {
			$this->reader = $Store;
		}

		if ($this->isWriter($mode)) {
			$this->writers->push($Store);
		}

		return $this;
	}

	public function get($key) {
		return $this->callOnReader(__FUNCTION__, func_get_args());
	}

	public function put($key, $value, $minutes) {
		return $this->callOnWriters(__FUNCTION__, func_get_args());
	}

	public function increment($key, $value = 1) {
		return $this->callOnWriters(__FUNCTION__, func_get_args());
	}

	public function decrement($key, $value = 1) {
		return $this->callOnWriters(__FUNCTION__, func_get_args());
	}

	public function forever($key, $value) {
		return $this->callOnWriters(__FUNCTION__, func_get_args());
	}

	public function forget($key) {
		return $this->callOnWriters(__FUNCTION__, func_get_args());
	}

	public function flush() {
		return $this->callOnWriters(__FUNCTION__, func_get_args());
	}

	public function getPrefix() {
		return $this->prefix;
	}

	public function getReader() {
		return $this->reader;
	}

	public function getWriters() {
		return $this->writers;
	}

	protected function isReader($mode) {
		return stristr($mode, 'r') !== false;
	}

	protected function isWriter($mode) {
		return stristr($mode, 'w') !== false;
	}

	protected function callOnReader($method, $args) {
		return $this->callOn($this->reader, $method, $args);
	}

	protected function callOnWriters($method, $args) {
		$this->writers->map(function($writer) use ($method, $args) {
			$this->callOn($writer, $method, $args);
		});
	}

	protected function callOn($instance, $method, $args) {
		switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array([$instance, $method], $args);
        }
	}
}
