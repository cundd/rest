<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest;

use React\EventLoop\Factory;
use React\Socket\Server as SocketServer;
use React\Http\Server as HttpServer;


class Server {
	/**
	 * Port to listen at
	 *
	 * @var integer
	 */
	protected $port;

	/**
	 * Host IP
	 *
	 * @var string
	 */
	protected $host = '127.0.0.1';

	/**
	 * Rest app
	 *
	 * @var \Cundd\Rest\Dispatcher
	 */
	protected $app;



	function __construct($port = NULL, $host = NULL) {
		$bootstrap = new \Cundd\Rest\Bootstrap;
		$bootstrap->init();

		if ($port) {
			$this->port = $port;
		}
		if ($host) {
			$this->host = $host;
		}

		$this->app = new \Cundd\Rest\Dispatcher();
	}

	/**
	 * Starts the server
	 */
	public function start() {
		$loop = Factory::create();
		$socketServer = new SocketServer($loop);
		$httpServer = new HttpServer($socketServer);
		$httpServer->on('request', array($this, 'serverCallback'));
		$socketServer->listen($this->port, $this->host);

		fwrite(STDOUT, 'Starting server at ' . $this->host . ':' . $this->port);

		$loop->run();
	}

	/**
	 * Handles the requests
	 *
	 * @param \React\Http\Request $request   Request to handle
	 * @param \React\Http\Response $response Prebuilt response object
	 */
	public function serverCallback($request, $response) {
		// Currently the PHP server is readonly
		if (!in_array(strtoupper($request->getMethod()), array('GET', 'HEAD'))) {
			$response->writeHead(405, array('Content-type' => 'text/plain'));
			$response->end('Writing is currently not supported');
			return;
		}

		/** @var \Cundd\Rest\Request $restRequest */
		$restRequest = new \Cundd\Rest\Request($request->getMethod(), $this->sanitizePath($request->getPath()));
		$this->setServerGlobals($request);

		/** @var \Bullet\Response $restResponse */
		$restResponse = NULL;

		ob_start();
		$this->app->dispatch($restRequest, $restResponse);
		ob_end_clean();

		$response->writeHead($restResponse->status(), $this->getHeadersFromResponse($restResponse));
		$response->end($restResponse->content());

		unset($restRequest);
		unset($restResponse);
	}

	/**
	 * Sanitize the given path as URL
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function sanitizePath($path) {
		return filter_var($path, FILTER_SANITIZE_URL);
	}

	/**
	 * Returns the headers from the response
	 *
	 * @param \Bullet\Response $restResponse
	 * @return array<mixed>
	 */
	public function getHeadersFromResponse($restResponse) {
		// Spy the headers
		$headers = $this->spyHeadersOfResponse($restResponse);

		// If no headers are defined guess at least the content type
		if (!$headers) {
			$contentType = '';
			$content = $restResponse->content();
			$xmlIndicatorPosition = strpos($content, '<');
			$jsonIndicatorPosition = strpos($content, '{');

			switch (TRUE) {
				case $xmlIndicatorPosition === FALSE && $jsonIndicatorPosition === FALSE:
					$contentType = 'text/plain';
					break;

				case $xmlIndicatorPosition === FALSE:
					$contentType = 'application/json; charset=UTF-8';
					break;

				case $jsonIndicatorPosition === FALSE:
					$contentType = 'application/xml; charset=UTF-8';
					break;

				case $jsonIndicatorPosition < $xmlIndicatorPosition:
					$contentType = 'application/json; charset=UTF-8';
					break;

				case $xmlIndicatorPosition < $jsonIndicatorPosition:
					$contentType = 'application/xml; charset=UTF-8';
					break;
			}
			$headers = array(
				'Content-type' => $contentType
			);
		}
		return $headers;
	}

	/**
	 * Returns the headers
	 *
	 * @param \Bullet\Response $restResponse
	 * @return array
	 */
	public function spyHeadersOfResponse($restResponse) {
		static $reflectionProperty = NULL;
		if ($reflectionProperty === NULL) {
			$reflectionClass = new \ReflectionClass('\\Bullet\\Response');
			$reflectionProperty = $reflectionClass->getProperty('_headers');
			$reflectionProperty->setAccessible(TRUE);
		}
		return $reflectionProperty->getValue($restResponse);
	}

	/**
	 * @param \React\Http\Request $request
	 */
	public function setServerGlobals($request) {
		$headers =  $request->getHeaders();
		if (isset($headers['Authorization']) && $headers['Authorization']) {
			$_SERVER['HTTP_AUTHENTICATION'] = $headers['Authorization'];
		}
	}
}
