<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Enums;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum Action: string implements LabeledEnum
{
	use Labeled;

	case Create = 'create';
	case Update = 'update';
	case Delete = 'delete';


	public function label(): string
	{
		return match($this) {
			self::Create => 'nestor.enum.action.create',
			self::Update => 'nestor.enum.action.update',
			self::Delete => 'nestor.enum.action.delete',
		};
	}


	public function color(): Color
	{
		return match($this) {
			self::Create => Color::Success,
			self::Update => Color::Primary,
			self::Delete => Color::Danger,
		};
	}
}
