<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Exceptions;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\Nestor\Exceptions\PeriodNotValidException;
use JuniWalk\Nestor\Exceptions\RecordExistsException;
use JuniWalk\Nestor\Exceptions\RecordFailedException;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use JuniWalk\Tests\Files\TestRecord;
use RuntimeException;
use Tester\Assert;
use Tester\TestCase;

class ExceptionsTest extends TestCase
{
	public function testPeriodNotValid(): void
	{
		$e = PeriodNotValidException::fromPeriod('1 day');
		Assert::same('Given period should modify date into past, "1 day" given.', $e->getMessage());
	}


	public function testRecordExists(): void
	{
		$record = new TestRecord('event', 'message');
		$e = RecordExistsException::fromRecord($record, '1 day');

		Assert::same('Record already exists in period of 1 day', $e->getMessage());
		Assert::same($record, $e->getRecord());
		Assert::same((string) $record, $e->createLogFromRecord());
	}


	public function testRecordFailed(): void
	{
		$record = new TestRecord('event', 'message');
		$previous = new RuntimeException('Boom', 12);
		$e = RecordFailedException::fromRecord($record, $previous);

		Assert::same('Boom', $e->getMessage());
		Assert::same(12, $e->getCode());
		Assert::same($previous, $e->getPrevious());
		Assert::same($record, $e->getRecord());
		Assert::same((string) $record, $e->createLogFromRecord());
	}


	public function testRecordNotValid(): void
	{
		$e = RecordNotValidException::fromRecord('event', ['message' => 'Hi']);
		Assert::same('Missing field "event" in record structure: {"message":"Hi"}', $e->getMessage());

		$e = RecordNotValidException::fromMethod('foo', new TestRecord('event', 'message'));
		Assert::same('Method setFoo is not available in '.TestRecord::class, $e->getMessage());
	}
}

(new ExceptionsTest)->run();
