<?php

namespace Apitte\Core\Decorator;

use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Mapping\RequestParameterMapping;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestParametersDecorator implements IDecorator
{

	/** @var RequestParameterMapping */
	protected $mapping;

	/**
	 * @param RequestParameterMapping $mapping
	 */
	public function __construct(RequestParameterMapping $mapping)
	{
		$this->mapping = $mapping;
	}

	/**
	 * @param ServerRequestInterface|ApiRequest $request
	 * @param ResponseInterface $response
	 * @param array $context
	 * @return ServerRequestInterface
	 */
	public function decorate(ServerRequestInterface $request, ResponseInterface $response, array $context = [])
	{
		return $this->mapping->map($request, $response);
	}

}
