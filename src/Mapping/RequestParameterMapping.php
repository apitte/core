<?php declare(strict_types = 1);

namespace Apitte\Core\Mapping;

use Apitte\Core\Exception\Logical\InvalidArgumentException;
use Apitte\Core\Exception\Logical\InvalidStateException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\RequestAttributes;
use Apitte\Core\Mapping\Parameter\ITypeMapper;
use Apitte\Core\Schema\Endpoint;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestParameterMapping
{

	/** @var string[]|ITypeMapper[] */
	protected $types = [];

	/**
	 * @param string|ITypeMapper $mapper
	 */
	public function addMapper(string $type, $mapper): void
	{
		if (!is_subclass_of($mapper, ITypeMapper::class)) {
			throw new InvalidArgumentException(sprintf('Mapper must be string representation or instance of %s.', ITypeMapper::class));
		}

		$this->types[$type] = $mapper;
	}

	/**
	 * @param ServerRequestInterface|ApiRequest $request
	 */
	public function map(ServerRequestInterface $request, ResponseInterface $response): ServerRequestInterface
	{
		/** @var Endpoint $endpoint */
		$endpoint = $request->getAttribute(RequestAttributes::ATTR_ENDPOINT);

		// Validate that we have an endpoint
		if (!$endpoint) {
			throw new InvalidStateException(sprintf('Attribute "%s" is required', RequestAttributes::ATTR_ENDPOINT));
		}

		// Get all parameters
		$parameters = $endpoint->getParameters();

		// Skip, if there are no parameters
		if (!$parameters) return $request;

		$headerParameters = $request->getHeaders();
		$cookieParams = $request->getCookieParams();
		// Get request parameters from attribute
		$requestParameters = $request->getAttribute(RequestAttributes::ATTR_PARAMETERS);

		// Iterate over all parameters
		foreach ($parameters as $parameter) {
			$mapper = $this->getMapper($parameter->getType());

			// If it's unsupported type, skip it
			if (!$mapper) continue;

			switch ($parameter->getIn()) {
				case $parameter::IN_PATH:
				case $parameter::IN_QUERY:
					// Logical check
					if (!array_key_exists($parameter->getName(), $requestParameters)) {
						if (!$parameter->isRequired()) {
							break;
						}

						throw new InvalidStateException(sprintf('Parameter "%s" should be provided in request attributes', $parameter->getName()));
					}

					// Obtain request parameter values
					$value = $requestParameters[$parameter->getName()];

					if ($value === null && $parameter->isRequired()) {
						throw new InvalidStateException(sprintf('Parameter "%s" should be provided in request attributes', $parameter->getName()));
					}
					if ($value === '' && !$parameter->isAllowEmpty()) {
						throw new InvalidStateException(sprintf('Parameter "%s" should not be empty', $parameter->getName()));
					}

					// Normalize value
					$normalizedValue = $mapper->normalize($value);

					// Update requests
					$requestParameters[$parameter->getName()] = $normalizedValue;
					$request = $request->withAttribute(RequestAttributes::ATTR_PARAMETERS, $requestParameters);

					break;
				case $parameter::IN_COOKIE:
					// Logical check
					if (!array_key_exists($parameter->getName(), $cookieParams)) {
						if (!$parameter->isRequired()) {
							break;
						}

						throw new InvalidStateException(sprintf('Parameter "%s" should be provided in request cookies', $parameter->getName()));
					}

					// Obtain request parameter values
					$value = $cookieParams[$parameter->getName()];

					if ($value === null && $parameter->isRequired()) {
						throw new InvalidStateException(sprintf('Parameter "%s" should be provided in request attributes', $parameter->getName()));
					}
					if ($value === '' && !$parameter->isAllowEmpty()) {
						throw new InvalidStateException(sprintf('Parameter "%s" should not be empty', $parameter->getName()));
					}

					// Normalize value
					$normalizedValue = $mapper->normalize($value);

					// Update requests
					$cookieParams[$parameter->getName()] = $normalizedValue;
					$request = $request->withCookieParams($cookieParams);

					break;
				case $parameter::IN_HEADER:
					$headerParameterName = strtolower($parameter->getName());
					// Logical check
					if (!array_key_exists($headerParameterName, $headerParameters)) {
						if (!$parameter->isRequired()) {
							break;
						}

						throw new InvalidStateException(sprintf('Parameter "%s" should be provided in request header', $parameter->getName()));
					}

					// Obtain request parameter values
					$values = $headerParameters[$headerParameterName];
					$normalizedValues = [];

					// Normalize value
					foreach ($values as $index => $value) {
						if ($value === '' && !$parameter->isAllowEmpty()) {
							throw new InvalidStateException(sprintf('Parameter "%s" should not be empty', $parameter->getName()));
						}

						$normalizedValues[$index] = $mapper->normalize($value);
					}

					// Update requests
					$headerParameters[$headerParameterName] = $normalizedValues;
					$request = $request->withHeader($headerParameterName, $normalizedValues);

					break;
			}
		}

		return $request;
	}

	protected function getMapper(string $type): ?ITypeMapper
	{
		if (!isset($this->types[$type])) return null;

		// Initialize mapper
		if (!is_object($this->types[$type])) {
			$this->types[$type] = new $this->types[$type]();
		}

		return $this->types[$type];
	}

}
