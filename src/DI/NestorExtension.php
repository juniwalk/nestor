<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\DI;

use JuniWalk\Nestor\Chronicler;
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
	 * @throws RecordNotValidException
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('chronicler'))
			->setFactory(Chronicler::class, (array) $config);
	}
}
