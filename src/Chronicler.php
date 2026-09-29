<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use DateTime;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\PeriodNotValidException;
use JuniWalk\Nestor\Exceptions\RecordExistsException;
use JuniWalk\Nestor\Exceptions\RecordFailedException;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use Throwable;

use function assert;
use function is_subclass_of;
use function ltrim;

final class Chronicler
{
	private readonly string $entityName;
	private readonly EntityManager $entityManager;

	/**
	 * @param  class-string<Record> $entityName
	 * @throws RecordNotValidException
	 */
	public function __construct(string $entityName, EntityManagerInterface $entityManager)
	{
		if (!is_subclass_of($entityName, Record::class)) {
			throw new RecordNotValidException;
		}

		$eventManager = $entityManager->getEventManager();
		assert($eventManager instanceof EventManager);

		$this->entityName = $entityName;
		$this->entityManager = new EntityManager(
			$entityManager->getConnection(),
			$entityManager->getConfiguration(),
			$eventManager,
		);
	}


	/**
	 * @return class-string<Record>
	 */
	public function getEntityName(): string
	{
		return $this->entityName;
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function log(string $event, string $message, array $params = []): void
	{
		$record = $this->createRecord($event, $message, $params)->withType(Type::Log);
		$this->record($record);
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function todo(string $event, string $message, array $params = []): void
	{
		$record = $this->createRecord($event, $message, $params)->withType(Type::Todo);
		$this->record($record);
	}


	/**
	 * @throws RecordExistsException
	 * @throws RecordFailedException
	 */
	public function record(Record|RecordBuilder $record, ?string $period = null, bool $ignoreFinished = true): void
	{
		if ($record instanceof RecordBuilder) {
			$record = $record->create();
		}

		if ($period && $this->isRecorded($record, $period, $ignoreFinished)) {
			throw RecordExistsException::fromRecord($record, $period);
		}

		try {
			$this->entityManager->persist($record);
			$this->entityManager->flush();

		} catch (Throwable $e) {
			throw RecordFailedException::fromRecord($record, $e);
		}
	}


	/**
	 * @throws PeriodNotValidException
	 */
	public function isRecorded(Record $record, ?string $period = null, bool $ignoreFinished = true): bool
	{
		$qb = $this->entityManager->createQueryBuilder()
			->select('e')->from($this->entityName, 'e')
			->where('e.hash = :hash');

		if ($ignoreFinished === true) {
			$qb->andWhere('e.isFinished = false');
		}

		if (isset($period)) {
			$dateStart = (new DateTime('midnight'))->modify('-'.ltrim($period, '+-'));
			$dateEnd = new DateTime('midnight next day');

			if ($dateStart > $dateEnd) {
				throw PeriodNotValidException::fromPeriod($period);
			}

			$qb->andWhere('e.date >= :dateStart AND e.date <= :dateEnd')
				->setParameter('dateStart', $dateStart)
				->setParameter('dateEnd', $dateEnd);
		}

		try {
			$qb->getQuery()->setMaxResults(1)
				->setParameter('hash', $record->getHash())
				->getSingleResult();

			return true;

		} catch (NoResultException) {
		}

		return false;
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function createRecord(string $event, string $message, array $params = []): RecordBuilder
	{
		return (new RecordBuilder($this))
			->withMessage($message)
			->withEvent($event)
			->withParams($params);
	}
}
