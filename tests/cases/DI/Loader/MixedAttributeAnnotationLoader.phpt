<?php declare(strict_types = 1);

/**
 * Test: DI\Loader\DoctrineAnnotationLoader for combination of attribute and annotations
 *
 * @phpVersion 8.0
 */

require_once __DIR__ . '/../../../bootstrap.php';

use Apitte\Core\DI\Loader\DoctrineAnnotationLoader;
use Apitte\Core\Schema\SchemaBuilder;
use Apitte\Core\Schema\Validation\RequestParameterValidation;
use Nette\DI\ContainerBuilder;
use Tester\Assert;
use Tests\Fixtures\Controllers\Mixed\PathAndRequestParamsController;

// Parse annotations
test(function (): void {
	$builder = new ContainerBuilder();
//	$builder->addDefinition('first_controller')
//		->setType(AnnotationAttributeController::class);

	$builder->addDefinition('second_controller')
		->setType(PathAndRequestParamsController::class);

	$loader = new DoctrineAnnotationLoader($builder);
	$schemaBuilder = $loader->load(new SchemaBuilder());
	Assert::type(SchemaBuilder::class, $schemaBuilder);

//	$controllers = $schemaBuilder->getControllers();
//	Assert::count(2, $controllers);

	$requestParameterValidation = new RequestParameterValidation();
	$requestParameterValidation->validate($schemaBuilder);
});
