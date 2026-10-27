<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Entity;

require __DIR__ . '/../../bootstrap.php';

use DateTime;
use DateTimeImmutable;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Tests\Files\TestRecord;
use JuniWalk\Utils\Enums\Color;
use Nette\Localization\Translator;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

class RecordTest extends TestCase
{
	public function testDefaults(): void
	{
		$record = new TestRecord('user.login', 'Hello');

		Assert::same('user.login', $record->getEvent());
		Assert::same('Hello', $record->getMessage());
		Assert::same(Type::Log, $record->getType());
		Assert::same(Color::Secondary, $record->getLevel());
		Assert::null($record->getNote());
		Assert::null($record->getTarget());
		Assert::null($record->getTargetId());
		Assert::false($record->isFinished());
		Assert::false($record->isFinishable());
	}


	public function testSetters(): void
	{
		$record = new TestRecord('a', 'b');
		$record->setEvent('c');
		$record->setMessage('d');
		$record->setType(Type::Todo);
		$record->setLevel(Color::Danger);

		Assert::same('c', $record->getEvent());
		Assert::same('d', $record->getMessage());
		Assert::same(Type::Todo, $record->getType());
		Assert::same(Color::Danger, $record->getLevel());
	}


	public function testNote(): void
	{
		$record = new TestRecord('a', 'b');
		$record->setNote('Note');
		Assert::same('Note', $record->getNote());

		$record->setNote('');
		Assert::null($record->getNote());
	}


	public function testDate(): void
	{
		$record = new TestRecord('a', 'b');
		$record->setDate(new DateTimeImmutable('2025-05-05 10:00:00'));

		Assert::type(DateTime::class, $record->getDate());
		Assert::same('2025-05-05 10:00:00', $record->getDate()->format('Y-m-d H:i:s'));

		$record->getDate()->modify('+1 day');	// returned date is a clone
		Assert::same('2025-05-05', $record->getDate()->format('Y-m-d'));
	}


	public function testFinishable(): void
	{
		$record = new TestRecord('a', 'b');
		$record->setType(Type::Todo);
		Assert::true($record->isFinishable());

		$record->setFinished(true);
		Assert::false($record->isFinishable());
	}


	public function testTarget(): void
	{
		$record = new TestRecord('a', 'b');
		$record->setTarget(new stdClass, 15);
		Assert::same(stdClass::class, $record->getTarget());
		Assert::same(15, $record->getTargetId());

		$record->setTarget(null);
		Assert::null($record->getTarget());
		Assert::null($record->getTargetId());
	}


	public function testCreateTargetWithoutTarget(): void
	{
		$record = new TestRecord('a', 'b');
		$em = (new \ReflectionClass(\Doctrine\ORM\EntityManager::class))->newInstanceWithoutConstructor();

		Assert::null($record->createTarget($em));
	}


	public function testParams(): void
	{
		$record = new TestRecord('a', 'b');
		$record->setParams(['name' => 'John', 'empty' => null, 'nested' => ['id' => 5]]);

		Assert::same(['name' => 'John', 'nested' => ['id' => 5]], $record->getParams());
		Assert::same(['name' => 'John', 'nested.id' => 5], $record->getParamsUnified());

		$record->addParams(['name' => 'Jenna', 'age' => 30]);
		Assert::same('John', $record->getParams()['name']);	// existing params take precedence
		Assert::same(30, $record->getParams()['age']);

		$record->setParams(['x' => 1]);
		Assert::same(['x' => 1], $record->getParams());
	}


	public function testMessageFormatted(): void
	{
		$record = new TestRecord('a', 'Hello {name}');
		$record->setParams(['name' => 'John']);

		Assert::same('Hello John', $record->getMessageFormatted());
	}


	public function testMessageTranslated(): void
	{
		$record = new TestRecord('a', 'key');
		$record->setParams(['name' => 'John']);

		$translator = new class implements Translator {
			public function translate(mixed $message, mixed ...$parameters): string
			{
				return $message.':'.json_encode($parameters);
			}
		};

		Assert::same('key:[{"name":"John"}]', $record->getMessageTranslated($translator));
	}


	public function testToString(): void
	{
		$record = new TestRecord('user.login', 'Hello {name}');
		$record->setParams(['name' => 'John']);
		$record->setTarget(new stdClass, 3);

		Assert::same('[log, secondary] stdClass(3) user.login: Hello John ({"name":"John"})', (string) $record);
	}


	public function testHash(): void
	{
		$first = new TestRecord('a', 'b');
		$second = new TestRecord('a', 'b');
		$other = new TestRecord('a', 'c');

		Assert::same(8, strlen($first->getHash()));
		Assert::same($first->getHash(), $second->getHash());
		Assert::notSame($first->getHash(), $other->getHash());

		$first->handleHashCreation();
		Assert::same($second->getHash(), $first->getHash());
	}
}

(new RecordTest)->run();
