<?php

namespace SVega9848\VegaHCFElevator;

use pocketmine\block\BaseSign;
use pocketmine\block\utils\SignText;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase implements Listener {

    public Config $config;

    public function onEnable(): void {
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);

        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
    }

    public function onTouchSign(PlayerInteractEvent $event) {
        if($event->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
          $block = $event->getBlock();
          if($block instanceof BaseSign) {
                $lines = $block->getText()->getLines();
                if($lines[0] == TextFormat::colorize($this->getConfigValue("prefix"))) {
                    $this->checkAvailableSign($event->getPlayer(), $block, $lines[1]);
                }
            }
        }
    }

    public function onTextChange(SignChangeEvent $event) {
        $lines = $event->getNewText()->getLines();

        if(strtolower($lines[0]) == "[elevator]") {
          if($event->getBlock()->getPosition()->getY() < 2) {
              $event->setNewText(new SignText([TextFormat::colorize($this->getConfigValue("prefix")), TextFormat::RED. "Error"]));
              $event->getPlayer()->sendMessage(TextFormat::colorize($this->getConfigValue("error.y-message")));
          } elseif(strtolower($lines[1]) == "up") {
              $event->setNewText(new SignText([TextFormat::colorize($this->getConfigValue("prefix")), "Up"]));
          } elseif(strtolower($lines[1]) == "down") {
              $event->setNewText(new SignText([TextFormat::colorize($this->getConfigValue("prefix")), "Down"]));
          } else{
              $event->setNewText(new SignText([TextFormat::colorize($this->getConfigValue("prefix")), TextFormat::RED. "Error"]));
              $event->getPlayer()->sendMessage(TextFormat::colorize($this->getConfigValue("error.message")));
          }
        }
    }

    public function checkAvailableSign(Player $player, BaseSign $sign, string $typeCheck) {
        $signX = $sign->getPosition()->getX();
        $signZ = $sign->getPosition()->getZ();
        if($typeCheck == "up") {
            for($count = $sign->getPosition()->getY() + 1; $count < 256; $count++) {
                $block = $player->getWorld()->getBlockAt($signX, $count, $signZ);
                if($block instanceof BaseSign) {
                    $lines = $block->getText()->getLines();
                    if($lines[0] == TextFormat::colorize($this->getConfigValue("prefix")) && $lines[1] == "Down") {
                        $player->teleport(new Vector3($signX + 0.5, $count, $signZ + 0.5));
                    }
                }
            }
        }

        if($typeCheck == "down") {
            for($count = $sign->getPosition()->getY() - 1; $count > 1; $count--) {
                $block = $player->getWorld()->getBlockAt($signX, $count, $signZ);
                if($block instanceof BaseSign) {
                    $lines = $block->getText()->getLines();
                    if($lines[0] == TextFormat::colorize($this->getConfigValue("prefix")) && $lines[1] == "Up") {
                        $player->teleport(new Vector3($signX + 0.5, $count, $signZ + 0.5));
                    }
                }
            }
        }
    }

    public function getConfigValue(string $key) : string {
        return $this->config->get($key);
    }

}
