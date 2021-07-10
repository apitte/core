<?php declare(strict_types = 1);

namespace Apitte\Core\Schema;

final class EndpointParameter
{

	public const TYPE_STRING = 'string';
	public const TYPE_INTEGER = 'integer';
	public const TYPE_FLOAT = 'float';
	public const TYPE_BOOLEAN = 'boolean';
	public const TYPE_DATETIME = 'date-time';
	
	public const TYPES = [
		self::TYPE_STRING,
		self::TYPE_INTEGER,
		self::TYPE_FLOAT,
		self::TYPE_BOOLEAN,
		self::TYPE_DATETIME,
	];

	public const IN_QUERY = 'query';
	public const IN_COOKIE = 'cookie';
	public const IN_HEADER = 'header';
	public const IN_PATH = 'path';

	public const IN = [
		self::IN_QUERY,
		self::IN_COOKIE,
		self::IN_HEADER,
		self::IN_PATH,
	];

	/** @var string */
	private $name;

	/** @var string */
	private $type;

	/** @var string|null */
	private $description;

	/** @var string */
	private $in = self::IN_PATH;

	/** @var bool */
	private $required = true;

	/** @var bool */
	private $deprecated = false;

	/** @var bool */
	private $allowEmpty = false;

	public function __construct(string $name, string $type = self::TYPE_STRING)
	{
		$this->name = $name;
		$this->type = $type;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}

	public function getIn(): string
	{
		return $this->in;
	}

	public function setIn(string $in): void
	{
		$this->in = $in;
	}

	public function isRequired(): bool
	{
		return $this->required;
	}

	public function setRequired(bool $required): void
	{
		$this->required = $required;
	}

	public function isDeprecated(): bool
	{
		return $this->deprecated;
	}

	public function setDeprecated(bool $deprecated): void
	{
		$this->deprecated = $deprecated;
	}

	public function isAllowEmpty(): bool
	{
		return $this->allowEmpty;
	}

	public function setAllowEmpty(bool $allowEmpty): void
	{
		$this->allowEmpty = $allowEmpty;
	}

}
