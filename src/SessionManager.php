<?php namespace jrub\LaravelMemcachedProxyDriver;

use Illuminate\Session\SessionManager as LaravelSessionManager;

class SessionManager extends LaravelSessionManager {
	protected function createMemcachedClusterDriver() {
		return $this->createCacheBased('memcached-proxy');
	}
}
