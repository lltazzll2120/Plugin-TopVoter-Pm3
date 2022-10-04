<?php
declare(strict_types = 1);

namespace Tazz\TopVoter\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;
use Tazz\TopVoter\TopVoter;

class DataUpdateEvent extends PluginEvent implements Cancellable {

	private $voteData;

	public function __construct(TopVoter $plugin, array $voteData){
		parent::__construct($plugin);
		$this->voteData = $voteData;
	}

	public function getVoteData(): array{
		return $this->voteData;
	}

	public function setVoteData(array $voteData): void{
		$this->voteData = $voteData;
	}
}
