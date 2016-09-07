<?php
/**
 * Created By Thunder33345
 **/
namespace Thunder33345\CommandBlocker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\plugin\PluginBase;

class EventListener extends PluginBase implements Listener
{
	private $getOwner, $logger;

	public function __construct(Main $plugin)
	{
		$this->getOwner = $plugin;
		$this->logger = $this->getOwner->getLogger();
	}

	public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
	{
		$playername = $event->getPlayer()->getDisplayName();
		$playerIP = $event->getPlayer()->getAddress();
		$message = $event->getMessage();
		$words = explode(" ", $message);
		$cmd = strtolower(substr(array_shift($words), 1));
		if ($message[0] !== '/') {
			return;
		}
		if ($this->getOwner->isBlocked($cmd)) {
			if ($this->getOwner->isWhiteListed($event->getPlayer())) {
				$this->getOwner->logFile($event->getPlayer(), 'Notice', "/$cmd " . implode(" ", $words));
				$this->log()->info("[Allowed] $playername($playerIP) try to execute " . "/$cmd " . implode(" ", $words));
			} else {
				$event->setCancelled(true);
				$this->getOwner->logFile($event->getPlayer(), 'Alert', "/$cmd " . implode(" ", $words));
				$this->log()->alert("[Blocked] $playername($playerIP) try to execute " . "/$cmd " . implode(" ", $words));
			}
		}
	}

	private function log()
	{
		if (isset($this->logger)) {
			return $this->logger;
		} else {
			return $this->getOwner->getLogger();
		}
	}

	public function onServerCommand(ServerCommandEvent $event)
	{
		$message = $event->getCommand();
		$words = explode(" ", $message);
		$cmd = array_shift($words);
		$words = implode(" ", $words);
		if ($this->getOwner->isBlocked($cmd)) {
			if (!$this->getOwner->blockConsole) {
				$this->getOwner->logCFile('CONSOLE', 'Notice', "/$cmd $words");
				$this->log()->info("[Allowed] CONSOLE try to execute " . "/$cmd $words");
			} else {
				$event->setCancelled(true);
				$this->getOwner->logCFile('CONSOLE', 'Alert', "/$cmd $words");
				$this->Log()->alert("[Blocked] CONSOLE try to execute " . "/$cmd $words");
			}
		}
	}

	public function onRemoteCommand(RemoteServerCommandEvent $event)
	{
		$message = $event->getCommand();
		$words = explode(" ", $message);
		$cmd = array_shift($words);
		$words = implode(" ", $words);
		if ($this->getOwner->isBlocked($cmd)) {
			if (!$this->getOwner->blockConsole) {
				$this->getOwner->logCFile('REMOTE', 'Notice', "/$cmd $words");
				$this->log()->info("[Allowed] REMOTE try to execute " . "/$cmd $words");
			} else {
				$event->setCancelled(true);
				$this->getOwner->logCFile('REMOTE', 'Alert', "/$cmd $words");
				$this->Log()->alert("[Blocked] REMOTE try to execute " . "/$cmd $words");
			}
		}
	}
}