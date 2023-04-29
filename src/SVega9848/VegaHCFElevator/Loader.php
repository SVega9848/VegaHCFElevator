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
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if($event->getAction() == PlayerInteractEvent::LEFT_CLICK_BLOCK) {
        }

        if($event->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            if($block instanceof BaseSign) {
                $lines = $block->getText()->getLines();

                if($lines[0] == TextFormat::colorize($this->getConfigValue("prefix")) && $lines[1] == "Up") {
                    $this->checkAvailableSign($player, $block, "up");
                }

                if($lines[0] == TextFormat::colorize($this->getConfigValue("prefix")) && $lines[1] == "Down") {
                    $this->checkAvailableSign($player, $block, "down");
                }
            }
        }
    }

    public function onTextChange(SignChangeEvent $event) {
        $text = $event->getNewText();
        $lines = $text->getLines();

        if(strtolower($lines[0]) == "[elevator]" && strtolower($lines[1]) == "up") {
            if($event->getBlock()->getPosition()->getY() < 2) {
                $event->setNewText(new SignText([TextFormat::colorize($this->getConfigValue("prefix")), TextFormat::RED. "Error"]));
                $event->getPlayer()->sendMessage(TextFormat::colorize($this->getConfigValue("error-message")));
            } else {
                $event->setNewText(new SignText([TextFormat::colorize($this->getConfigValue("prefix")), "Up"]));
            }
        }

        if(strtolower($lines[0]) == "[elevator]" && strtolower($lines[1]) == "down") {
            if($event->getBlock()->getPosition()->getY() < 2) {
                $event->setNewText(new SignText([TextFormat::colorize($this->getConfigValue("prefix")), TextFormat::RED. "Error"]));
                $event->getPlayer()->sendMessage(TextFormat::colorize($this->getConfigValue("error-message")));
            } else {
                $event->setNewText(new SignText([TextFormat::colorize($this->getConfigValue("prefix")), "Down"]));
            }
        }
    }

    public function checkAvailableSign(Player $player, BaseSign $sign, string $typeCheck) {
        if($typeCheck == "up") {
            for($count = $sign->getPosition()->getY() + 1; $count < 256; $count++) {
                $block = $player->getWorld()->getBlockAt($sign->getPosition()->getX(), $count, $sign->getPosition()->getZ());
                if($block instanceof BaseSign) {
                    $lines = $block->getText()->getLines();
                    if($lines[0] == TextFormat::colorize($this->getConfigValue("prefix")) && $lines[1] == "Down") {
                        $player->teleport(new Vector3($sign->getPosition()->getX() + 0.5, $count, $sign->getPosition()->getZ() + 0.5));
                    }
                }
            }
        }

        if($typeCheck == "down") {
            for($count = $sign->getPosition()->getY() - 1; $count > 1; $count--) {
                $block = $player->getWorld()->getBlockAt($sign->getPosition()->getX(), $count, $sign->getPosition()->getZ());
                if($block instanceof BaseSign) {
                    $lines = $block->getText()->getLines();
                    if($lines[0] == TextFormat::colorize($this->getConfigValue("prefix")) && $lines[1] == "Up") {
                        $player->teleport(new Vector3($sign->getPosition()->getX() + 0.5, $count, $sign->getPosition()->getZ() + 0.5));
                    }
                }
            }
        }
    }

    public function getConfigValue(string $key) : string {
        return $this->config->get($key);
    }

}