<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use DateTimeInterface;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use JuniWalk\Nestor\Interfaces\ParamsProvider;
use JuniWalk\Nestor\Interfaces\TargetRelatedProvider;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Strings;
use Nette\Security\IIdentity as Identity;
use Throwable;

final class RecordBuilder
{
	private array $record = [];

	public function __construct(
		private readonly Chronicler $chronicler,
	) {}


	/**
	 * @throws RecordNotValidException
	 */
	public function create(): Record
	{
		$entityName = $this->chronicler->getEntityName();
		$record = new $entityName(
			$this->record['event'],
			$this->record['message'],
		);

		foreach ($this->record as $key => $value) {
			if (!method_exists($record, 'set'.$key)) {
				throw new RecordNotValidException;
			}

			$record->{'set'.$key}($value);
		}

		return $record;
	}


	public function record(string $period = null): void
	{
		$this->chronicler->record($this, $period);
	}


	public function withType(Type $type): static
	{
		$this->record['type'] = $type;
		return $this;
	}


	public function withLevel(Color $level): static
	{
		$this->record['level'] = $level;
		return $this;
	}


	public function withMessage(string $message): static
	{
		$this->record['message'] = $message;
		return $this;
	}


	public function withNote(?string $note): static
	{
		$this->record['note'] = $note;
		return $this;
	}


	public function withError(?Throwable $exception, bool $isFinished = null): static
	{
		$this->record['finished'] ??= $isFinished ?? !isset($exception);

		if (!$exception instanceof Throwable) {
			return $this;
		}

		$className = Format::className($exception);
		$className = Format::camelCase($className);
		$className = ucfirst($className);

		$this->record['note'] = $className.': '.$exception->getMessage();
		return $this;
	}


	public function withEvent(?string $event): static
	{
		$this->record['event'] = $event;
		return $this;
	}


	public function withTarget(object $target): static
	{
		if ($target instanceof ParamsProvider) {
			$this->withParams($target->getRecordParams());
		}

		if ($target instanceof TargetProvider) {
			$target = $target->getRecordTarget();
		}

		$this->record['target'] = $target;
		return $this;
	}


	public function withDate(DateTimeInterface $date): static
	{
		$this->record['date'] = $date;
		return $this;
	}


	public function withOwner(?Identity $owner): static
	{
		$this->record['owner'] = $owner;
		return $this;
	}


	public function withAuthor(Identity $owner): static
	{
		return $this->withOwner($owner);
	}


	public function withFinished(bool $isFinished): static
	{
		$this->record['finished'] = $isFinished;
		return $this;
	}


	public function withParams(array $params): static
	{
		foreach ($params as $key => $value) {
			if (!$matches = Strings::match($key, '/record\.(\w+)/i')) {
				continue;
			}

			if (!method_exists($this, 'with'.$matches[1])) {
				continue;
			}

			$this->{'with'.$matches[1]}($value);
			unset($params[$key]);
		}

		$this->record['params'] ??= [];
		$this->record['params'] += $params;
		return $this;
	}


	public function withParam(string $name, mixed $value): static
	{
		$this->record['params'][$name] = $value;
		return $this;
	}


	public function clearParams(): static
	{
		unset($this->record['params']);
		return $this;
	}
}
