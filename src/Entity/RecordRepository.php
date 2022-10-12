<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity;

use Doctrine\ORM\EntityManagerInterface as EntityManager;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use JuniWalk\Utils\Exceptions\EntityNotFoundException;
use JuniWalk\Utils\ORM\AbstractRepository;

abstract class RecordRepository extends AbstractRepository
{
	/**
	 * @throws RecordNotValidException
	 * @throws EntityNotFoundException
	 */
	final public function __construct(EntityManager $entityManager)
	{
		parent::__construct($entityManager);

		if (!is_subclass_of($this->entityName, Record::class)) {
			throw new RecordNotValidException;
		}
	}
}
