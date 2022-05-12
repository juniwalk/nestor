<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\DI;

use JuniWalk\Nestor\Chronicler;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Exceptions\EntityNotValidException;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class NestorExtension extends CompilerExtension
{
	/**
	 * @return Schema
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'entityName' => Expect::string()->required(),
		]);
	}


	/**
	 * @return void
	 * @throws EntityNotValidException
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		if (!is_subclass_of($config->entityName, Record::class)) {
			throw new EntityNotValidException;
		}

		$builder->addDefinition($this->prefix('chronicler'))
			->setFactory(Chronicler::class, $config);
	}
}
