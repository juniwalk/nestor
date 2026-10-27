<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use DateTime;
use JuniWalk\Nestor\Chronicler;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\PeriodNotValidException;
use JuniWalk\Nestor\Exceptions\RecordExistsException;
use JuniWalk\Nestor\Exceptions\RecordFailedException;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

abstract class AbstractChroniclerCase extends TestCase
{
	private TestDatabase $db;


	abstract protected function createDatabase(): TestDatabase;


	public function setUp(): void
	{
		$this->db = $this->createDatabase();
	}


	private function newRecord(string $event, string $message): Record
	{
		return new ($this->db->recordClass)($event, $message);
	}


	public function testInvalidEntityName(): void
	{
		Assert::exception(
			fn() => new Chronicler(stdClass::class, $this->db->entityManager),
			RecordNotValidException::class,
		);
	}


	public function testEntityName(): void
	{
		Assert::same($this->db->recordClass, $this->db->chronicler->getEntityName());
	}


	public function testLogAndTodo(): void
	{
		$this->db->chronicler->log('user.login', 'Hello {name}', ['name' => 'John']);
		$this->db->chronicler->todo('user.check', 'Check');

		[$log, $todo] = $this->db->fetchRecords();

		Assert::same(Type::Log, $log->getType());
		Assert::same('user.login', $log->getEvent());
		Assert::same('Hello John', $log->getMessageFormatted());
		Assert::same(Type::Todo, $todo->getType());
		Assert::true($todo->isFinishable());
	}


	public function testBuilderRecord(): void
	{
		$this->db->chronicler->createRecord('e', 'm')->withNote('Note')->record();

		$records = $this->db->fetchRecords();
		Assert::count(1, $records);
		Assert::same('Note', $records[0]->getNote());
		Assert::same(8, strlen($records[0]->getHash()));
	}


	public function testIsRecorded(): void
	{
		$chronicler = $this->db->chronicler;
		$record = $this->newRecord('e', 'm');

		Assert::false($chronicler->isRecorded($record));
		$chronicler->record($this->newRecord('e', 'm'));

		Assert::true($chronicler->isRecorded($record));
		Assert::true($chronicler->isRecorded($record, '1 day'));
		Assert::false($chronicler->isRecorded($this->newRecord('e', 'other')));
	}


	public function testIsRecordedIgnoreFinished(): void
	{
		$finished = $this->newRecord('e', 'm');
		$finished->setFinished(true);
		$this->db->chronicler->record($finished);

		$probe = $this->newRecord('e', 'm');
		Assert::false($this->db->chronicler->isRecorded($probe));
		Assert::true($this->db->chronicler->isRecorded($probe, null, false));
	}


	public function testIsRecordedOutsidePeriod(): void
	{
		$old = $this->newRecord('e', 'm');
		$old->setDate(new DateTime('-10 days'));
		$this->db->chronicler->record($old);

		$probe = $this->newRecord('e', 'm');
		Assert::false($this->db->chronicler->isRecorded($probe, '2 days'));
		Assert::true($this->db->chronicler->isRecorded($probe, '20 days'));
	}


	public function testRecordExistsInPeriod(): void
	{
		$this->db->chronicler->record($this->newRecord('e', 'm'));

		Assert::exception(
			fn() => $this->db->chronicler->record($this->newRecord('e', 'm'), '1 day'),
			RecordExistsException::class,
			'Record already exists in period of 1 day',
		);

		Assert::count(1, $this->db->fetchRecords());
	}


	public function testPeriodNotValid(): void
	{
		Assert::exception(
			fn() => $this->db->chronicler->isRecorded($this->newRecord('e', 'm'), '2 days ago'),
			PeriodNotValidException::class,
		);
	}


	public function testRecordWithOwner(): void
	{
		$em = $this->db->entityManager;
		$identity = new ($this->db->identityClass);
		$em->persist($identity);
		$em->flush();

		$record = $this->newRecord('e', 'm');
		$record->setOwner($identity);
		$this->db->chronicler->record($record);

		$records = $this->db->fetchRecords();
		Assert::equal($identity->getId(), $records[0]->getOwner()->getId());
	}


	public function testRecordFailed(): void
	{
		$record = $this->newRecord('e', 'm');
		$record->setTarget(new stdClass, fopen('php://memory', 'r'));	// not JSON serializable

		Assert::exception(
			fn() => $this->db->chronicler->record($record),
			RecordFailedException::class,
		);
	}
}
