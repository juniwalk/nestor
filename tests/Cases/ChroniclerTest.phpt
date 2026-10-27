<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use JuniWalk\Tests\Files\AbstractChroniclerCase;
use JuniWalk\Tests\Files\TestIdentity;
use JuniWalk\Tests\Files\TestRecord;
use JuniWalk\Tests\Files\TestDatabase;

class ChroniclerTest extends AbstractChroniclerCase
{
	protected function createDatabase(): TestDatabase
	{
		return new TestDatabase(TestRecord::class, TestIdentity::class);
	}
}

(new ChroniclerTest)->run();
