<?php declare(strict_types = 1);

/**
 * Test: DI\Loader\DoctrineAnnotationLoader
 */

require_once __DIR__ . '/../../../bootstrap.php';

use Apitte\Core\DI\Loader\DoctrineAnnotationLoader;
use Apitte\Core\Exception\Logical\InvalidStateException;
use Apitte\Core\Schema\Builder\SchemaBuilder;
use Apitte\Core\UI\Controller\IController;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;
use Tester\Assert;
use Tests\Fixtures\Controllers\FoobarController;
use Tests\Fixtures\Controllers\InvalidGroupAnnotationController;
use Tests\Fixtures\Controllers\InvalidGroupPathAnnotationController;
use Tests\Fixtures\Controllers\PrefixedAnnotationController;

// Check if controller is found and add as dependency to DIC
test(function (): void {
	$builder = Mockery::mock(ContainerBuilder::class);
	$builder->shouldReceive('findByType')
		->once()
		->with(IController::class)
		->andReturnUsing(function (): array {
			$controllers = [];
			$controllers[] = $c1 = new ServiceDefinition();
			$c1->setClass(FoobarController::class);

			return $controllers;
		});

	$builder->shouldReceive('addDependency')
		->once()
		->with(FoobarController::class);

	$loader = new DoctrineAnnotationLoader($builder);
	$schemaBuilder = $loader->load(new SchemaBuilder());

	Assert::type(SchemaBuilder::class, $schemaBuilder);

	Mockery::close();
});

// Parse annotations
test(function (): void {
	$builder = new ContainerBuilder();
	$builder->addDefinition('foobar')
		->setClass(FoobarController::class);

	$loader = new DoctrineAnnotationLoader($builder);
	$schemaBuilder = $loader->load(new SchemaBuilder());

	Assert::type(SchemaBuilder::class, $schemaBuilder);
	Assert::count(1, $schemaBuilder->getControllers());

	$controllers = $schemaBuilder->getControllers();
	$controller = array_pop($controllers);

	Assert::equal(FoobarController::class, $controller->getClass());
	Assert::equal('/foobar', $controller->getPath());

	Assert::count(3, $controller->getMethods());

	Assert::equal('baz1', $controller->getMethods()['baz1']->getName());
	Assert::equal('/baz1', $controller->getMethods()['baz1']->getPath());
	Assert::equal(['GET'], $controller->getMethods()['baz1']->getMethods());

	Assert::equal('baz2', $controller->getMethods()['baz2']->getName());
	Assert::equal('/baz2', $controller->getMethods()['baz2']->getPath());
	Assert::equal(['GET', 'POST'], $controller->getMethods()['baz2']->getMethods());

	Assert::equal(['testapi'], $controller->getGroupIds());
	Assert::equal(['/api', '/v1'], $controller->getGroupPaths());
});

// Invalid annotation (@Controller + @Group)
test(function (): void {
	Assert::exception(function (): void {
		$builder = new ContainerBuilder();
		$builder->addDefinition('invalid')
			->setClass(InvalidGroupAnnotationController::class);

		$loader = new DoctrineAnnotationLoader($builder);
		$loader->load(new SchemaBuilder());
	}, InvalidStateException::class, sprintf('Annotation @GroupId cannot be on non-abstract "%s"', InvalidGroupAnnotationController::class));
});

// Invalid annotation (@Controller + @GroupPath)
test(function (): void {
	Assert::exception(function (): void {
		$builder = new ContainerBuilder();
		$builder->addDefinition('invalid')
			->setClass(InvalidGroupPathAnnotationController::class);

		$loader = new DoctrineAnnotationLoader($builder);
		$loader->load(new SchemaBuilder());
	}, InvalidStateException::class, sprintf('Annotation @GroupPath cannot be on non-abstract "%s"', InvalidGroupPathAnnotationController::class));
});

// Test prefixed annotation (@API\Controller)
test(function (): void {
	$builder = new ContainerBuilder();
	$builder->addDefinition('prefixedAnnotation')
		->setClass(PrefixedAnnotationController::class);

	$loader = new DoctrineAnnotationLoader($builder);
	$schemaBuilder = $loader->load(new SchemaBuilder());

	Assert::type(SchemaBuilder::class, $schemaBuilder);
	Assert::count(1, $schemaBuilder->getControllers());
});
