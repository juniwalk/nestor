<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\DI;

use JuniWalk\Nestor\Chronicler;
use JuniWalk\Nestor\Entity\Subscribers\ActivitySubscriber;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class NestorExtension extends CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'entityName' => Expect::string()->required(),
			'watchActivity' => Expect::bool(false),
		]);
	}


	/**
	 * @throws RecordNotValidException
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('chronicler'))
			->setFactory(Chronicler::class, [$config->entityName]);

		if (!$config->watchActivity) {
			return;
		}

		$builder->addDefinition($this->prefix('activitySubscriber'))
			->setFactory(ActivitySubscriber::class);
	}
}
