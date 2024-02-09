<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity;

use DateTime;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Proxy\Proxy;
use JuniWalk\ORM\Traits as Tools;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Utils\Arrays;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Json;
use Nette\Application\UI\Control;
use Nette\Localization\Translator;

#[ORM\MappedSuperclass]
abstract class Record
{
	use Tools\Identifier;
	use Tools\Ownership;

	#[ORM\Column(type: 'string', length: 16, enumType: Type::class)]
	private Type $type = Type::Log;

	#[ORM\Column(type: 'string', length: 64)]
	private string $event;

	#[ORM\Column(type: 'string')]
	private string $message;

	#[ORM\Column(type: 'string', nullable: true, options: ['default' => null])]
	private ?string $target = null;

	#[ORM\Column(type: 'integer', nullable: true, options: ['default' => null])]
	private ?int $targetId = null;

	#[ORM\Column(type: 'datetimetz')]
	private DateTime $date;

	#[ORM\Column(type: 'string', length: 16, enumType: Color::class)]
	private Color $level = Color::Secondary;

	#[ORM\Column(type: 'boolean')]
	private bool $isFinished = false;

	#[ORM\Column(type: 'json', nullable: true)]
	private ?array $params = null;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $note = null;

	#[ORM\Column(type: 'string', length: 8, nullable: true)]
	private ?string $hash = null;


	final public function __construct(string $event, string $message)
	{
		$this->date = new DateTime;
		$this->message = $message;
		$this->event = $event;
	}


	public function __toString(): string
	{
		return strtr("[%type%, %level%] %target%(%targetId%) %event%: %message% (%params%)", [
			'%target%' => $this->getTarget(),
			'%targetId%' => $this->getTargetId(),
			'%type%' => $this->getType()->value,
			'%level%' => $this->getLevel()->value,
			'%event%' => $this->getEvent(),
			'%message%' => $this->getMessageFormatted(),
			'%params%' => Json::encode($this->getParams()),
		]);
	}


	public function setType(Type $type): void
	{
		$this->type = $type;
	}


	public function getType(): Type
	{
		return $this->type;
	}


	public function setEvent(string $event): void
	{
		$this->event = $event;
	}


	public function getEvent(): string
	{
		return $this->event;
	}


	public function setMessage(string $message): void
	{
		$this->message = $message;
	}


	public function getMessage(): string
	{
		return $this->message;
	}


	public function getMessageFormatted(): string
	{
		$replace = Arrays::flatten($this->getParams());
		$replace = Arrays::tokenize($replace);
		return strtr($this->message, $replace);
	}


	public function getMessageTranslated(Translator $translator): string
	{
		return $translator->translate($this->message, Arrays::flatten($this->params));
	}


	public function setTarget(object $target, ?int $targetId = null): void
	{
		if (method_exists($target, 'getId')) {
			$targetId ??= $target->getId();
		}

		$this->target = $target::class;
		$this->targetId = $targetId;

		if ($target instanceof Proxy) {
			$this->target = get_parent_class($target);
		}
	}


	public function getTarget(): ?string
	{
		return $this->target;
	}


	public function getTargetId(): ?int
	{
		return $this->targetId;
	}


	public function createTarget(EntityManager $entityManager): Proxy
	{
		return $entityManager->getReference($this->target, $this->targetId);
	}


	public function setDate(DateTime $date): void
	{
		$this->date = clone $date;
	}


	public function getDate(): DateTime
	{
		return clone $this->date;
	}


	public function setLevel(Color $level): void
	{
		$this->level = $level;
	}


	public function getLevel(): Color
	{
		return $this->level;
	}


	public function setFinished(bool $isFinished): void
	{
		$this->isFinished = $isFinished;
	}


	public function isFinished(): bool
	{
		return $this->isFinished;
	}


	public function isFinishable(): bool
	{
		return $this->type == Type::Todo && !$this->isFinished;
	}


	public function setParams(array $params): void
	{
		$this->params = null;
		$this->addParams($params);
	}


	public function addParams(array $params): void
	{
		$params = Arrays::map($params, fn($v) => Format::scalarize($v));
		$params = array_filter($params, fn($v) => !is_null($v));
		$params = array_merge($params, $this->params ?? []);

		$this->params = $params ?: null;
	}


	public function getParams(): array
	{
		return $this->params ?: [];
	}


	public function getParam(string $key): mixed
	{
		return $this->params[$key] ?? null;
	}


	public function getParamsUnified(): array
	{
		return Arrays::flatten($this->getParams());
	}


	public function setNote(?string $note): void
	{
		$this->note = $note ?: null;
	}


	public function getNote(): ?string
	{
		return $this->note;
	}


	public function getHash(): string
	{
		return $this->hash ?: $this->createUniqueHash();
	}


	abstract public function createLink(Control $control): ?string;


	protected function createUniqueHash(): string
	{
		return $this->hash ??= substr(sha1((string) $this), 0, 8);
	}


	#[ORM\PreFlush]
	public function onPreFlush(PreFlushEventArgs $event): void
	{
		$this->createUniqueHash();
	}
}
