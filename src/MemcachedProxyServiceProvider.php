<?php namespace jrub\LaravelMemcachedProxyDriver;

use Illuminate\Cache\MemcachedStore;
use Illuminate\Support\ServiceProvider;

class MemcachedProxyServiceProvider extends ServiceProvider {
	public function boot() {
		$this->registerMemcachedDriver();
	}

	public function register() {
		$this->bindSessionManager();
	}

	protected function bindSessionManager() {
		$this->app->singleton('session', function($app) {
			return new SessionManager($app);
		});
	}

	protected function registerMemcachedDriver() {
		$this->app['cache']->extend('memcached-proxy', function($app, $config) {
			$prefix = $this->getCachePrefix($config);

			$memcacheds = collect($config['clusters'])->map(function($cluster_config) use ($app, $prefix) {
				$memcached_connection = $app['memcached.connector']->connect($cluster_config['servers']);

				return [
					'store' => new MemcachedStore($memcached_connection, $prefix),
					'mode' => $cluster_config['mode'],
				];
			});

			$MemcachedProxy = new MemcachedProxyStore($prefix);
			$memcacheds->map(function($memcached_cluster) use ($MemcachedProxy) {
				$MemcachedProxy->addConnection($memcached_cluster['store'], $memcached_cluster['mode']);
			});

			return $app['cache']->repository($MemcachedProxy);
		});
	}

	protected function getCachePrefix(array $config) {
		return Arr::get($config, 'prefix') ?: $this->app['config']['cache.prefix'];
	}
}