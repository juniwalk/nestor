<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity;

use Doctrine\ORM\EntityManagerInterface as EntityManager;
use JuniWalk\ORM\AbstractRepository;
use JuniWalk\ORM\Exceptions\EntityNotFoundException;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;

abstract class RecordRepository extends AbstractRepository
{
	/**
	 * @throws RecordNotValidException
	 * @throws EntityNotFoundException
	 */
	public function __construct(EntityManager $entityManager)
	{
		parent::__construct($entityManager);

		if (!is_subclass_of($this->entityName, Record::class)) {
			throw new RecordNotValidException;
		}
	}
}
