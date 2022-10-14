<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use DateTime;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Strings;

final class RecordBuilder
{
	private iterable $record = [];


	public function __construct(
		private readonly Chronicler $chronicler
	) {}


	/**
	 * @throws RecordNotValidException
	 */
	public function create(): Record
	{
		$entityName = $this->chronicler->getEntityName();
		$record = new $entityName(
			$this->record['event'],
			$this->record['message']
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
		$this->chronicler->record($this->create(), $period);
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


	public function withEvent(?string $event): static
	{
		$this->record['event'] = $event;
		return $this;
	}


	public function withDate(DateTime $date): static
	{
		$this->record['date'] = $date;
		return $this;
	}


	public function withParams(iterable $params): static
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

		$this->record['params'] = $params;
		return $this;
	}


	public function withParam(string $name, $value): static
	{
		$this->record['params'][$name] = $value;
		return $this;
	}
}
