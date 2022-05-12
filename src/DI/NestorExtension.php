<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\DI;

use JuniWalk\Nestor\Chronicler;
use JuniWalk\Nestor\Entity\Record;
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
			'entityName' => Expect::string()->required()->assert(function($e) {
				return $e instanceof Record;
			}),
		]);
	}


	/**
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('chronicler'))
			->setFactory(Chronicler::class, $config);
	}
}
