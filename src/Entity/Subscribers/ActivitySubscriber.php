<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity\Subscribers;

use Closure;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Proxy\Proxy;
use JuniWalk\Nestor\Chronicler;
use JuniWalk\Nestor\Entity\Attributes\ActivityOverride;
use JuniWalk\Nestor\Entity\Attributes\TargetIgnore;
use JuniWalk\Nestor\Enums\Action;
use JuniWalk\Nestor\Interfaces\ParamsProvider;
use JuniWalk\Nestor\Interfaces\TargetProvider;
use JuniWalk\Utils\Format;
use Nette\Security\IIdentity as Identity;
use Nette\Security\User as LoggedInUser;
use ReflectionClass;

class ActivitySubscriber implements EventSubscriber
{
	private bool $isFlushing = false;
	private array $items = [];
	private ?Identity $user;

	final public function __construct(
		private readonly Chronicler $chronicler,
		private readonly LoggedInUser $loggedInUser,
		private readonly EntityManager $entityManager,
	) {
		$this->user = $loggedInUser->getIdentity();
	}


	final public function getSubscribedEvents()
	{
		return [
			Events::onFlush,
			Events::postFlush,
		];
	}


	public function onFlush(EventArgs $event): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		if ($this->isFlushing) {
			return;
		}

		foreach ($uow->getScheduledEntityInsertions() as $entity) {
			$this->process(Action::Create, $entity);
		}

		foreach ($uow->getScheduledEntityUpdates() as $entity) {
			$this->process(Action::Update, $entity);
		}

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			$this->process(Action::Delete, $entity);
		}

		foreach ($uow->getScheduledCollectionUpdates() as $collection) {
			$this->process(Action::Update, $collection);
		}

		foreach ($uow->getScheduledCollectionDeletions() as $collection) {
			$this->process(Action::Delete, $collection);
		}
	}


	public function postFlush(EventArgs $event): void
	{
		if ($this->isFlushing || empty($this->items)) {
			return;
		}

		$this->isFlushing = true;

		foreach ($this->items as [$target, $action, $params, $targetId]) {
			$message = 'web.activity.'.Format::className($target).'-'.$action->value;

			if ($target instanceof ParamsProvider) {
				$params = array_merge($params, $target->getRecordParams($action));
			}

			$record = $this->chronicler->createRecord($action, $message)->create();
			$record->setTarget($target, $targetId);
			$record->setLevel($action->color());
			$record->setOwner($this->user);
			$record->setParams($params);

			if ($target instanceof TargetProvider) {
				$record->setTarget($target->getRecordTarget());
			}

			$this->entityManager->persist($record);
		}

		$this->entityManager->flush();
		$this->isFlushing = false;
		$this->items = [];
	}


	private function process(Action $action, object $target): void
	{
		$changes = $this->findChanges($action, $target);

		if ($target instanceof Collection) {
			$target = $target->getOwner();
		}

		$reflection = new ReflectionClass($target);

		if ($reflection->getAttributes(TargetIgnore::class)) {
			return;
		}

		if (!$changes || $target instanceof Activity) {
			return;
		}

		$this->items[spl_object_id($target)] = [
			$target,
			$action,
			$changes,
			$target->getId(),
		];
	}


	private function findChanges(Action $action, object $target): array
	{
		$uow = $this->entityManager->getUnitOfWork();
		$changes = [];

		if ($target instanceof Collection) {
			$fieldName = $target->getMapping()['fieldName'];
			$changes[$fieldName] = [
				$target->getSnapshot(),
				$target->getValues(),
			];

			$target = $target->getOwner();
		}

		$changes = array_merge($changes, $uow->getEntityChangeSet($target));

		foreach ($this->findOverrides($target) as $fieldName => $override) {
			$changes = $override->process($changes, $fieldName);
		}

		if ($action == Action::Create) foreach ($changes as $key => [$old, $new]) {
			$changes[$key] = $new;
		}

		if ($action == Action::Delete && !$changes) {
			$changes = Closure::fromCallable(fn() => get_object_vars($this))
				->call($target);

			if ($target instanceof Proxy) {
				$changes = array_diff_key($changes, [
					'lazyPropertiesDefaults' => null,
					'lazyPropertiesNames' => null,
					'__isInitialized__' => null,
					'__initializer__' => null,
					'__cloner__' => null,
				]);
			}
		}

		return $changes;
	}


	private function findOverrides(object $target): array
	{
		$reflection = new ReflectionClass($target);
		$overrides = [];

		if ($target instanceof Proxy) {
			$reflection = $reflection->getParentClass();
		}

		foreach ($reflection->getProperties() as $property) {
			foreach ($property->getAttributes(ActivityOverride::class) as $attribute) {
				$overrides[$property->getName()] = $attribute->newInstance();
			}
		}

		return $overrides;
	}
}
