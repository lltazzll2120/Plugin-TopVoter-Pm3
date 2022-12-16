<?php
declare(strict_types = 1);

namespace Tazz\TopVoter;

use pocketmine\world\World;
use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use Tazz\TopVoter\Tasks\{UpdateVotesTask, PocketVoteUpdateTask};

class TopVoter extends PluginBase {

	private $updateTask;
	private $particles = [];

	private $voters = [];

	public function onEnable(): void{
		$this->saveResource('config.yml');
		$this->initParticles();

		// Vérifiez si nous voulons activer le support PocketVote.
		if(empty($this->getConfig()->get('API-Key')) || $this->getConfig()->get('Use-PocketVote')) {
			// Si la clé n’est pas définie et que PocketVote est chargé, utilisez PocketVote.
			// Si Use-PocketVote est défini sur true et que le plugin est chargé, utilisez PocketVote.
			if($this->getServer()->getPluginManager()->getPlugin('PocketVote') !== null) {
				$this->getScheduler()->scheduleRepeatingTask($this->updateTask = new PocketVoteUpdateTask($this), max(1, $this->getConfig()->get('Update-Interval')) * 20);
			}
		}

		if(!$this->updateTask) {
			$this->getScheduler()->scheduleRepeatingTask($this->updateTask = new UpdateVotesTask($this), max(180, $this->getConfig()->get('Update-Interval')) * 20);
		}

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	private function initParticles(): void{
		foreach((array) $this->getConfig()->get('Positions') as $pos){
			if(($world = $this->getServer()->getWorldByName($pos['world'])) instanceof World){
				$particle = new FloatingTextParticle(new Vector3($pos['x'], $pos['y'], $pos['z']), '', $this->getConfig()->get('Header'));
				$particle->encode(); // prevent empty batch error
				$this->particles[$world->getFolderName()][] = $particle;
			}
		}
	}

	public function getParticles(): array{
		return $this->particles;
	}

	public function sendParticles(World $world = null, array $players = null){
		if($world === null){
			foreach(array_keys($this->particles) as $world){
				if(($world = $this->getServer()->getWorldByName($world)) instanceof World){
					$this->sendParticles($world);
				}
			}

			return;
		}

		if($players === null){
			$players = $worlf->getPlayers();
		}

		foreach($this->particles[$world->getFolderName()] ?? [] as $particle){
			$particle->setInvisible(false);
			$world->addParticle($particle, $players);
		}
	}

	public function removeParticles(World $world, array $players = null){
		if($players === null){
			$players = $level->getPlayers();
		}

		foreach($this->particles[$world->getFolderName()] ?? [] as $particle){
			$particle->setInvisible();
			$world->addParticle($particle, $players);
			$particle->setInvisible(false);
		}
	}

	public function updateParticles(): void{
		$text = '';

		foreach($this->voters as $voter){
			$text .= str_replace(['{player}', '{votes}'], [$voter['nickname'], $voter['votes']], $this->getConfig()->get('Text'))."\n";
		}

		foreach($this->particles as $worldParticles){
			foreach($worldParticles as $particle){
				$particle->setText($text);
			}
		}
	}

	public function setVoters(array $voters): void{
		$this->voters = $voters;
	}

	public function getVoters(): array{
		return $this->voters;
	}

	public function onDisable(): void{
		foreach($this->particles as $world => $particles){
			$world = $this->getServer()->getWorldByName($world);

			if($level instanceof World){
				foreach($particles as $particle){
					$particle->setInvisible();
					$world->addParticle($particle);
				}
			}
		}

		$this->particles = [];
	}
}
