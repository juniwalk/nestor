<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity\Attributes;

use Attribute;
use JuniWalk\Nestor\Enums\Strategy;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ActivityOverride
{
	public function __construct(
		private readonly Strategy $strategy,
		private readonly mixed $value = null,
	) {
	}


	public function process(array $changes, string $fieldName): array
	{
		switch ($this->strategy) {
			case Strategy::Conceal:
				$changes[$fieldName] = $this->value;
				break;

			case Strategy::Ignore:
				unset($changes[$fieldName]);
				break;
		}

		return $changes;
	}
}
