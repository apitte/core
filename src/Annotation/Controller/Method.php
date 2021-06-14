<?php declare(strict_types = 1);

namespace Apitte\Core\Annotation\Controller;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Target("METHOD")
 * @NamedArgumentConstructor()
 */
final class Method
{

	/** @var string[] */
	private $methods = [];

	/**
	 * @param string[]|string $methods
	 */
	public function __construct($methods)
	{
		if (empty($methods)) {
			throw new AnnotationException('Empty @Method given');
		}

		// Wrap single given method into array
		if (! is_array($methods)) {
			$methods = [$methods];
		}

		$this->methods = $methods;
	}

	/**
	 * @return string[]
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

}
