<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use DateTime;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\NoResultException;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\PeriodNotValidException;
use JuniWalk\Nestor\Exceptions\RecordExistsException;
use JuniWalk\Nestor\Exceptions\RecordFailedException;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use Throwable;

final class Chronicler
{
	/**
	 * @param  class-string<Record> $entityName
	 * @throws RecordNotValidException
	 */
	public function __construct(
		private readonly string $entityName,
		private readonly EntityManager $entityManager,
	) {
		if (!is_subclass_of($entityName, Record::class)) {	// @phpstan-ignore function.alreadyNarrowedType (Let's not treat this as certain)
			throw new RecordNotValidException;
		}
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
	public function record(Record|RecordBuilder $record, ?string $period = null): void
	{
		if ($record instanceof RecordBuilder) {
			$record = $record->create();
		}

		if ($period && $this->isRecorded($record, $period)) {
			throw RecordExistsException::fromRecord($record, $period);
		}

		try {
			$this->entityManager->persist($record);
			$this->entityManager->flush($record);	// @phpstan-ignore-line

		} catch (Throwable $e) {
			throw RecordFailedException::fromRecord($record, $e);
		}
	}


	/**
	 * @throws PeriodNotValidException
	 */
	public function isRecorded(Record $record, ?string $period = null): bool
	{
		$qb = $this->entityManager->createQueryBuilder()
			->select('e')->from($this->entityName, 'e')
			->where('e.hash = :hash AND e.isFinished = false');

		if (isset($period)) {
			$dateStart = new DateTime('midnight next day');
			$dateEnd = (new DateTime)->modify('-'.ltrim($period, '+-'))
				->modify('midnight');

			if ($dateEnd > $dateStart) {
				throw PeriodNotValidException::fromPeriod($period);
			}

			$qb->andWhere('e.date < :dateStart AND e.date > :dateEnd')
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
