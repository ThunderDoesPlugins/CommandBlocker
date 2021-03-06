<?php
/** Created By Thunder33345 **/
namespace Thunder33345\CommandBlocker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\plugin\PluginBase;

class EventListener extends PluginBase implements Listener
{
  private $getOwner,$cmdTrigger;

  public function __construct(Main $plugin)
  {
    $this->getOwner = $plugin;
    $this->cmdTrigger = $plugin->getConfig()->get('cmd-trigger');
  }

  public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
  {
    $name = $event->getPlayer()->getDisplayName();
    $playerIP = $event->getPlayer()->getAddress();
    $message = $event->getMessage();
    $words = explode(" ", $message);
    $cmd = strtolower(substr(array_shift($words), 1));
    if ($message[0] !== $this->cmdTrigger) return;
    if ($this->getOwner->isBlocked($cmd)) {
      if ($this->getOwner->isWhiteListed($event->getPlayer())) $state = "Allowed"; else {
        $state = "Blocked";
        $event->setCancelled(true);
      }
      $msg = "/$cmd " . implode(" ", $words);
      $this->getOwner->alertPlayers($event->getPlayer(), $state, $msg);
      $this->getOwner->logToFile($event->getPlayer(), $state, $msg);
      $this->log()->info("[$state] $name($playerIP) : " . $msg);
    }
  }

  private function log()
  {
    return $this->getOwner->getLogger();
  }

  public function onServerCommand(ServerCommandEvent $event)
  {
    $message = $event->getCommand();
    $words = explode(" ", $message);
    $cmd = array_shift($words);
    $words = implode(" ", $words);
    if ($this->getOwner->isBlocked($cmd)) {
      if (!$this->getOwner->blockConsole) $state = "Allowed"; else {
        $event->setCancelled(true);
        $state = "Blocked";
      }
      $msg = "/$cmd $words";// . implode(" ", $words);
      $this->getOwner->alertPlayers($event->getSender(), $state, $msg);
      $this->getOwner->logToFile($event->getSender(), $state, $msg);
      $this->log()->info("[$state] {$event->getSender()->getName()} : " . $msg);
    }
  }

  public function onRemoteCommand(RemoteServerCommandEvent $event)
  {
    $message = $event->getCommand();
    $words = explode(" ", $message);
    $cmd = array_shift($words);
    $words = implode(" ", $words);
    if ($this->getOwner->isBlocked($cmd)) {
      if (!$this->getOwner->blockRemote) $state = "Allowed"; else {
        $event->setCancelled(true);
        $state = "Blocked";
      }
      $msg = "/$cmd " . implode(" ", $words);
      $this->getOwner->alertPlayers($event->getSender(), $state, $msg);
      $this->getOwner->logToFile($event->getSender(), $state, $msg);
      $this->log()->info("[$state] {$event->getSender()->getName()} : " . $msg);
    }
  }
}