<?php declare(strict_types = 1);

namespace Apitte\Core\DI\Plugin;

use Apitte\Core\Decorator\DecoratorManager;
use Apitte\Core\DI\ApiExtension;
use Apitte\Core\DI\Helpers;
use Apitte\Core\Dispatcher\DecoratedDispatcher;
use Apitte\Core\Exception\Logical\InvalidStateException;

class CoreDecoratorPlugin extends AbstractPlugin
{

	public const PLUGIN_NAME = 'decorator';

	public function __construct(PluginCompiler $compiler)
	{
		parent::__construct($compiler);
		$this->name = self::PLUGIN_NAME;
	}

	/**
	 * Register services
	 */
	public function loadPluginConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->getDefinition($this->extensionPrefix('core.dispatcher'))
			->setType(DecoratedDispatcher::class)
			->setFactory(DecoratedDispatcher::class);

		$builder->addDefinition($this->prefix('decorator.manager'))
			->setFactory(DecoratorManager::class);
	}

	/**
	 * Decorate services
	 */
	public function beforePluginCompile(): void
	{
		$this->compileTaggedDecorators();
	}

	protected function compileTaggedDecorators(): void
	{
		$builder = $this->getContainerBuilder();

		// Find all definitions by tag
		$definitions = $builder->findByTag(ApiExtension::CORE_DECORATOR_TAG);

		// Ensure we have at least 1 service or early terminate
		if (!$definitions) return;

		// Sort by priority
		$definitions = Helpers::sort($definitions);

		// Find all services by names
		$decorators = Helpers::getDefinitions($definitions, $builder);

		// Add decorators to dispatcher
		foreach ($decorators as $decorator) {
			$tag = $decorator->getTag(ApiExtension::CORE_DECORATOR_TAG);

			if (!isset($tag['type'])) {
				throw new InvalidStateException(sprintf('Missing "type" attribute in tag "%s" at service "%s"', ApiExtension::CORE_DECORATOR_TAG, $decorator->getType()));
			}

			foreach ((array) $tag['type'] as $type) {
				$builder->getDefinition($this->prefix('decorator.manager'))
					->addSetup('addDecorator', [$type, $decorator]);
			}
		}
	}

}
