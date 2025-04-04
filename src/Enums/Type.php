<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Enums;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum Type: string implements LabeledEnum
{
	use Labeled;

	case Log = 'log';
	case Todo = 'todo';


	public function label(): string
	{
		return 'nestor.enum.type.'.$this->value;
	}


	public function color(): Color
	{
		return match ($this) {
			self::Log => Color::Warning,
			self::Todo => Color::Info,
		};
	}


	public function icon(): string
	{
		return match ($this) {
			self::Log => 'fa-plus-square',
			self::Todo => 'fa-minus-square',
		};
	}
}
