<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use DateTimeImmutable;
use InvalidArgumentException;
use JuniWalk\Nestor\Enums\Action;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use JuniWalk\Nestor\Interfaces\ParamsProvider;
use JuniWalk\Nestor\Interfaces\TargetProvider;
use JuniWalk\Nestor\RecordBuilder;
use JuniWalk\Tests\Files\TestChronicler;
use JuniWalk\Tests\Files\TestRecord;
use JuniWalk\Utils\Enums\Color;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

class RecordBuilderTest extends TestCase
{
	private RecordBuilder $builder;


	public function setUp(): void
	{
		$this->builder = new RecordBuilder(TestChronicler::create());
	}


	public function testCreateMinimal(): void
	{
		$record = $this->builder->withEvent('e')->withMessage('m')->create();

		Assert::type(TestRecord::class, $record);
		Assert::same('e', $record->getEvent());
		Assert::same('m', $record->getMessage());
		Assert::same(Type::Log, $record->getType());
	}


	public function testCreateFull(): void
	{
		$target = new stdClass;
		$date = new DateTimeImmutable('2025-01-02 03:04:05');

		$record = $this->builder
			->withEvent('e')
			->withMessage('m')
			->withType(Type::Todo)
			->withLevel(Color::Danger)
			->withNote('note')
			->withDate($date)
			->withTarget($target)
			->withParam('key', 'value')
			->withFinished(true)
			->withOwner(null)
			->create();

		Assert::same(Type::Todo, $record->getType());
		Assert::same(Color::Danger, $record->getLevel());
		Assert::same('note', $record->getNote());
		Assert::same('2025-01-02 03:04:05', $record->getDate()->format('Y-m-d H:i:s'));
		Assert::same(stdClass::class, $record->getTarget());
		Assert::same(['key' => 'value'], $record->getParams());
		Assert::true($record->isFinished());
		Assert::null($record->getOwner());
	}


	public function testCreateMissingFields(): void
	{
		Assert::exception(
			fn() => $this->builder->withEvent('e')->create(),
			RecordNotValidException::class,
			'Missing field "message" in record structure: %a%',
		);

		Assert::exception(
			fn() => (new RecordBuilder(TestChronicler::create()))->withMessage('m')->create(),
			RecordNotValidException::class,
			'Missing field "event" in record structure: %a%',
		);
	}


	public function testWithError(): void
	{
		$record = $this->builder->withEvent('e')->withMessage('m')
			->withError(new InvalidArgumentException('Bad'))
			->create();

		Assert::same('InvalidArgument: Bad', $record->getNote());
		Assert::false($record->isFinished());
	}


	public function testWithErrorNull(): void
	{
		$record = $this->builder->withEvent('e')->withMessage('m')
			->withError(null)
			->create();

		Assert::null($record->getNote());
		Assert::true($record->isFinished());
	}


	public function testWithErrorKeepsFinished(): void
	{
		$record = $this->builder->withEvent('e')->withMessage('m')
			->withFinished(true)
			->withError(new InvalidArgumentException('Bad'))
			->create();

		Assert::true($record->isFinished());
	}


	public function testParamsRecordKeys(): void
	{
		$record = $this->builder->withEvent('e')->withMessage('m')
			->withParams([
				'record.level' => Color::Warning,
				'record.unknown' => 'x',
				'name' => 'John',
			])
			->create();

		Assert::same(Color::Warning, $record->getLevel());
		Assert::same(['record.unknown' => 'x', 'name' => 'John'], $record->getParams());
	}


	public function testClearParams(): void
	{
		$record = $this->builder->withEvent('e')->withMessage('m')
			->withParam('a', 1)
			->clearParams()
			->create();

		Assert::same([], $record->getParams());
	}


	public function testTargetProviders(): void
	{
		$real = new stdClass;
		$provider = new class($real) implements TargetProvider, ParamsProvider {
			public function __construct(private object $real) {}
			public function getRecordTarget(): ?object { return $this->real; }
			public function getRecordParams(?Action $action = null): array { return ['record.note' => 'from params', 'id' => 7]; }
		};

		$record = $this->builder->withEvent('e')->withMessage('m')
			->withTarget($provider)
			->create();

		Assert::same(stdClass::class, $record->getTarget());
		Assert::same('from params', $record->getNote());
		Assert::same(['id' => 7], $record->getParams());
	}
}

(new RecordBuilderTest)->run();
