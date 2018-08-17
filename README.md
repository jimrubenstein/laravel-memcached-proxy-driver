# Laravel Memcached Proxy Driver

A cache driver for Laravel that allows you to write to multiple memcached clusters simmultaneously.

This allows for zero-downtime migrations to new memcached clusters with no cache data lost. In the land of PHP, this is helpful for applications that utilize memcached as their session stores. Migrating cache clusters, or adding nodes to an existing cluster, causes cache keys to be redistributed. This causes some percentage of existing session keys to be "lost" as they're now rotated to a different server (that is to say, their expected location is different -- the data itself doesn't actually move).

By writing data to multiple cluster configurations, you can season the "new" cluster with the cache data for your active sessions and caches, and prevent session termination or cold-cache re-build on a new cluster.

# Usage

This package adds an additional driver to the cache manager in Laravel. To use, add `jrub\LaravelMemcachedProxyDriver\MemcachedProxyServiceProvider::class` to your `providers' array in `config/app.php`. Then, add your cluster configuration(s) to `config/cache.php` using the `memcached-proxy` driver.

Here is an example configuration:

** Keep in mind, only 1 cluster can be used to READ data, so that cluster will have a mode of `rw` while all the other clusters will have a mode of `w` only.**

**config/cache.php**
```
return [

	/*
		Other Laravel Configurations
	*/

	'memcached-cluster' => [
		'driver' => 'memcached-proxy',
		'clusters' => [
			// Cluster A
			[
				'mode' => 'rw',
				'servers' => [
					['host' => 'server-a.cluster-1.example.com', 'port' => 11211, 'weight' => 100],
					['host' => 'server-b.cluster-1.example.com', 'port' => 11211, 'weight' => 100],
				]
			],

			// Cluster B
			[
				'mode' => 'w', // Write only -- can't read from 2 clusters
				'servers' => [
					['host' => 'server-a.cluster-2.example.com', 'port' => 11211, 'weight' => 100],
					['host' => 'server-b.cluster-2.example.com', 'port' => 11211, 'weight' => 100],
				]
			],
		]
	],
];
```

