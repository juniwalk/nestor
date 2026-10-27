<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use JuniWalk\Tests\Files\AbstractChroniclerCase;
use JuniWalk\Tests\Files\TestIdentityUuid;
use JuniWalk\Tests\Files\TestRecordUuid;
use JuniWalk\Tests\Files\TestDatabase;

class ChroniclerUuidTest extends AbstractChroniclerCase
{
	protected function createDatabase(): TestDatabase
	{
		return new TestDatabase(TestRecordUuid::class, TestIdentityUuid::class);
	}
}

(new ChroniclerUuidTest)->run();
