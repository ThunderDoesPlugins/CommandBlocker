<?php
/**
 * Created By Thunder33345
 **/
namespace Thunder33345\CommandBlocker;

use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
  public $blockConsole = true, $blockRemote = true;
  private $whiteListedPlayer = [], $blockedCommands = [], $logToFile = true, $alertedPlayers = [];

  public function onEnable()
  {
    @mkdir($this->getDataFolder());
    $this->saveDefaultConfig();
    $this->blockConsole = $this->getConfig()->get("block-console");
    $this->blockRemote = $this->getConfig()->get("block-remote");
    $this->blockedCommands = explode(",", $this->getConfig()->get("blocked-command"));
    $this->whiteListedPlayer = explode(",", $this->getConfig()->get("whitelisted-players"));
    $this->logToFile = $this->getConfig()->get("log-to-file");
    $this->alertedPlayers = explode(",", $this->getConfig()->get("alerted-players"));

    if ($this->logToFile === true) {
      if (!file_exists($this->getDataFolder() . "logs/"))
        mkdir($this->getDataFolder() . "logs/", 0777, true);

    }

    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
  }

  public function onDisable()
  {
  }

  public function isBlocked($cmd)
  {
    if (isset($this->blockedCommands)) {
      $cmd = strtolower($cmd);
      foreach ($this->blockedCommands as $bcmd) {
        $bcmd = strtolower($bcmd);
        if ($cmd === $bcmd) {
          return true;
        }
      }
    }

    return false;
  }

  public function isWhiteListed(Player $p)
  {
    if (isset($this->whiteListedPlayer)) {
      $p = $p->getName();
      $p = strtolower($p);
      foreach ($this->whiteListedPlayer as $wlp) {
        $wlp = strtolower($wlp);
        if ($p === $wlp) {
          return true;
        }
      }
    }

    return false;
  }

  public function alertPlayers(CommandSender $sender, $state, $massage)
  {
    if (count($this->alertedPlayers) <= 0) return;
    foreach ($this->alertedPlayers as $player) {
      $player = $this->getServer()->getPlayerExact($player);
      if (!$player instanceof Player) continue;
      $player->sendMessage("[$state] {$sender->getName()} : $massage");
    }
  }

  public function logToFile(CommandSender $sender, $state, $massage)
  {
    if ($this->logToFile == false) return;
    $handler = fopen($this->getDataFolder() . "logs/CommandLogs_" . date("Y-m-d") . ".log", "a");
    if (is_resource($handler)) {
      $msg = gmdate('Y-m-d h:i:s \G\M\T') . " [$state] {$sender->getName()} ";
      if ($sender instanceof Player) $msg .= "({$sender->getAddress()}) ";
      $msg .= ": " . $massage;
      fwrite($handler, $msg);
    } elseif (!is_resource($handler) AND $this->logToFile === true) {
      $this->getLogger()->error("Fail to write to log!");
    }
  }
}