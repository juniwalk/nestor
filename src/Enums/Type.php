<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Enums;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\LabelledEnum;

enum Type: string implements LabelledEnum
{
	case Log = 'log';
	case Todo = 'todo';


	public function label(): string
	{
		return match($this) {
			self::Log => 'nestor.enum.type.log',
			self::Todo => 'nestor.enum.type.todo',
		};
	}


	public function color(): Color
	{
		return match($this) {
			self::Log => Color::Warning,
			self::Todo => Color::Info,
		};
	}


	public function icon(): ?string
	{
		return match($this) {
			self::Log => 'fa-plus-square',
			self::Todo => 'fa-minus-square',
		};
	}
}
