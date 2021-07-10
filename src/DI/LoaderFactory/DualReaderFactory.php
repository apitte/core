<?php declare(strict_types = 1);

namespace Apitte\Core\DI\LoaderFactory;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;

/**
 * @see https://github.com/koriym/Koriym.Attributes
 */
final class DualReaderFactory
{

	public function create(): Reader
	{
		$annotationReader = new AnnotationReader();
		AnnotationRegistry::registerUniqueLoader('class_exists');

		$attributeReader = new AttributeReader();
		return new DualReader($annotationReader, $attributeReader);
	}

}
