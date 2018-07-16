<?php declare(strict_types = 1);

namespace Apitte\Core\Mapping;

use Apitte\Core\Exception\Logical\InvalidStateException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\RequestAttributes;
use Apitte\Core\Mapping\Request\IRequestEntity;
use Apitte\Core\Mapping\Validator\IEntityValidator;
use Apitte\Core\Schema\Endpoint;
use Apitte\Core\Schema\EndpointRequestMapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestEntityMapping
{

	/** @var IEntityValidator|null */
	protected $validator;

	public function setValidator(?IEntityValidator $validator): void
	{
		$this->validator = $validator;
	}

	/**
	 * @param ApiRequest $request
	 */
	public function map(ServerRequestInterface $request, ResponseInterface $response): ServerRequestInterface
	{
		/** @var Endpoint|null $endpoint */
		$endpoint = $request->getAttribute(RequestAttributes::ATTR_ENDPOINT);

		// Validate that we have an endpoint
		if (!$endpoint) {
			throw new InvalidStateException(sprintf('Attribute "%s" is required', RequestAttributes::ATTR_ENDPOINT));
		}

		// If there's no request mapper, then skip it
		if (!($requestMapper = $endpoint->getRequestMapper())) return $request;

		// Create entity
		$entity = $this->createEntity($requestMapper, $request);

		if ($entity) {
			$request = $request->withAttribute(RequestAttributes::ATTR_REQUEST_ENTITY, $entity);
		}

		return $request;
	}

	/**
	 * @param ApiRequest $request
	 */
	protected function createEntity(EndpointRequestMapper $mapper, ServerRequestInterface $request): ?IRequestEntity
	{
		$entityClass = $mapper->getEntity();
		$entity = new $entityClass();

		// Validate entity type
		if (!($entity instanceof IRequestEntity)) {
			throw new InvalidStateException(sprintf('Instantiated entity "%s" does not implement "%s"', get_class($entity), IRequestEntity::class));
		}

		// Allow modify entity in extended class
		$entity = $this->modify($entity, $request);
		if (!$entity) return null;

		// Try to validate entity only if its enabled
		if ($mapper->isValidation() === true) {
			$this->validate($entity);
		}

		return $entity;
	}

	/**
	 * @param ApiRequest $request
	 */
	protected function modify(IRequestEntity $entity, ServerRequestInterface $request): ?IRequestEntity
	{
		return $entity->fromRequest($request);
	}

	protected function validate(IRequestEntity $entity): void
	{
		if (!$this->validator) return;
		$this->validator->validate($entity);
	}

}
