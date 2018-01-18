<?php

namespace Apitte\Core\Dispatcher;

use Contributte\Middlewares\Exception\InvalidStateException;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JsonDispatcher extends CoreDispatcher
{

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	protected function handle(ServerRequestInterface $request, ResponseInterface $response)
	{
		$result = $this->handler->handle($request, $response);

		// Convert array and scalar into JSON
		// Or just pass response
		if (is_array($result) || is_scalar($result)) {
			$response = $response->withStatus(200)
				->withHeader('Content-Type', 'application/json');
			$response->getBody()->write(Json::encode($result));
		} else {
			$response = $result;
		}

		// Validate if response is ResponseInterface
		if (!($response instanceof ResponseInterface)) {
			throw new InvalidStateException(sprintf('Handler returned response must implement "%s"', ResponseInterface::class));
		}

		return $response;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	public function fallback(ServerRequestInterface $request, ResponseInterface $response)
	{
		$response = $response->withStatus(404)
			->withHeader('Content-Type', 'application/json');
		$response->getBody()->write(Json::encode(['error' => 'No matched route by given URL']));

		return $response;
	}

}
