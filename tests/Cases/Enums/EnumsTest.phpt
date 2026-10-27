<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Enums;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\Nestor\Enums\Action;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Utils\Enums\Color;
use Tester\Assert;
use Tester\TestCase;

class EnumsTest extends TestCase
{
	public function testTypeValues(): void
	{
		Assert::same(Type::Log, Type::from('log'));
		Assert::same(Type::Todo, Type::from('todo'));
	}


	public function testTypeLabelColorIcon(): void
	{
		Assert::same('nestor.enum.type.log', Type::Log->label());
		Assert::same('nestor.enum.type.todo', Type::Todo->label());
		Assert::same(Color::Secondary, Type::Log->color());
		Assert::same(Color::Warning, Type::Todo->color());
		Assert::same('fa-plus-square', Type::Log->icon());
		Assert::same('fa-minus-square', Type::Todo->icon());
	}


	public function testActionLabelAndColor(): void
	{
		Assert::same('nestor.enum.action.create', Action::Create->label());
		Assert::same('nestor.enum.action.update', Action::Update->label());
		Assert::same('nestor.enum.action.delete', Action::Delete->label());
		Assert::same(Color::Success, Action::Create->color());
		Assert::same(Color::Primary, Action::Update->color());
		Assert::same(Color::Danger, Action::Delete->color());
	}
}

(new EnumsTest)->run();
