<?php
/*
 * The MIT License (MIT)
 * 
 * Copyright (c) 2013 Daniel Corn
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
 
/*
 * rest
 * @author daniel
 * Date: 21.09.13
 * Time: 20:42
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



	function __construct($port = NULL, $host = NULL) {
		$bootstrap = new \Cundd\Rest\Bootstrap;
		$bootstrap->init();

		if ($port) {
			$this->port = $port;
		}
		if ($host) {
			$this->host = $host;
		}
	}


	/**
	 * Starts the server
	 */
	public function start() {
		$app = new \Cundd\Rest\App;
		$restServer = $this;

		/**
		 * @param \React\Http\Request $request
		 * @param \React\Http\Response $response
		 */
		$serverCallback = function ($request, $response) use ($app, $restServer) {
			/** @var \Cundd\Rest\Request $restRequest */
			$restRequest = new \Cundd\Rest\Request($request->getMethod(), $request->getPath());

			/** @var \Bullet\Response $restResponse */
			$restResponse = NULL;

			ob_start();
			$app->dispatch($restRequest, $restResponse);
			ob_end_clean();

			// Spy the headers
			$headers = $restServer->spyHeadersOfResponse($restResponse);

			$response->writeHead(200, $headers);
			$response->end($restResponse->content());

			unset($restRequest);
			unset($restResponse);
		};


		$loop = Factory::create();
		$socketServer = new SocketServer($loop);
		$httpServer = new HttpServer($socketServer);

		$httpServer->on('request', $serverCallback);

		$socketServer->listen($this->port, $this->host);

		fwrite(STDOUT, 'Starting server at ' . $this->host . ':' . $this->port);

		$loop->run();
	}

	/**
	 * Returns the headers
	 *
	 * @param \Bullet\Response $response
	 * @return array
	 */
	public function spyHeadersOfResponse($response) {
		static $reflectionProperty = NULL;
		if ($reflectionProperty === NULL) {
			$reflectionClass = new \ReflectionClass('\\Bullet\\Response');
			$reflectionProperty = $reflectionClass->getProperty('_headers');
			$reflectionProperty->setAccessible(TRUE);
		}
		return $reflectionProperty->getValue($response);
	}
}

