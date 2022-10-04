<?php
declare(strict_types = 1);

namespace Tazz\TopVoter\Tasks;

use pocketmine\scheduler\Task;
use ProjectInfinity\PocketVote\PocketVote;
use Tazz\TopVoter\TopVoter;

class PocketVoteUpdateTask extends Task {

	private $owner;

	public function __construct(TopVoter $owner) {
		$this->owner = $owner;
	}

	public function onRun(int $currentTick): void {
		// Le tableau de PocketVote diffère de celui fourni par minecraftpocket-servers.com, nous devons donc le recréer.
		$voters = [];
		foreach(PocketVote::getAPI()->getTopVoters() as $topVoter) {
			$voters[] = ['nickname' => $topVoter['player'], 'votes' => $topVoter['votes']];
		}
		$this->owner->setVoters($voters);
		$this->owner->updateParticles();
		$this->owner->sendParticles();
	}
}