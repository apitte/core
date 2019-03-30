<?php declare(strict_types = 1);

namespace Tests\Fixtures\Handler;

use Apitte\Core\Handler\IHandler;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;

class FakeNullHandler implements IHandler
{

	/**
	 * @return null
	 */
	public function handle(ApiRequest $request, ApiResponse $response)
	{
		return null;
	}

}
