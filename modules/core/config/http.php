<?php

return array (
	'default' => array (
		'driver' => 'socket',
	),
	'socket' => array (
		'driver' => 'socket',
	),
	'curl' => array (
		'driver' => 'curl',
		// CURLOPT_PROXY - The HTTP proxy to tunnel requests through
		// CURLOPT_PROXYUSERPWD - A username and password formatted as "[username]:[password]" to use for the connection to the proxy
		// CURLOPT_PROXYAUTH - The HTTP authentication method(s) to use for the proxy connection. Use the same bitmasks as described in CURLOPT_HTTPAUTH. For proxy authentication, only CURLAUTH_BASIC  and CURLAUTH_NTLM  are currently supported.
		// CURLOPT_PROXYPORT - The port number of the proxy to connect to. This port number can also be set in CURLOPT_PROXY.
		// CURLOPT_PROXYTYPE - Either CURLPROXY_HTTP (default) or CURLPROXY_SOCKS5.
		// CURLOPT_HTTPPROXYTUNNEL - TRUE to tunnel through a given HTTP proxy.
		'options' => array(),
	)
);