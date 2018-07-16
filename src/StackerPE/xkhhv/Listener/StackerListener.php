<?php

declare(strict_types=1);

namespace StackerPE\xkhhv\Listener;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use StackerPE\xkhhv\Loader;

class StackerListener implements Listener{

    public function onDamageForRide(EntityDamageEvent $event){
        if ($event instanceof EntityDamageByEntityEvent){
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if ($entity instanceof Player && $damager instanceof Player){
                if ($damager->getInventory()->getItemInHand()->getId() == 329) {
                    if (in_array(strtolower($damager->getName()), Loader::getInstance()->rider)) {
                        $damager->sendMessage(TextFormat::RED . "This Player is already ridden!");
                    } else {
                        Loader::getInstance()->sitOnPlayer($damager, $entity);
                        $damager->sendTip(TextFormat::GRAY . "Jump to dismount...!");
                    }
                }
            }
        }
    }

    public function DataPacketReceive(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        if($packet instanceof PlayerActionPacket) {
            if($packet->action === $packet::ACTION_JUMP) {
                if (in_array(strtolower($player->getName()), Loader::getInstance()->rider)) {
                    Loader::getInstance()->dismountFromPlayer($player);
                    $player->sendMessage("You dismount successfully!");
                    $player->teleport(new Vector3($player->x, $player->y -1, $player->z));
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        if (in_array(strtolower($player->getName()), Loader::getInstance()->rider)) {
            Loader::getInstance()->dismountFromPlayer($player);
            $player->teleport(new Vector3($player->x, $player->y - 1, $player->z));
        }
    }

    public function onChangeWorld(EntityLevelChangeEvent $event){
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if (in_array(strtolower($player->getName()), Loader::getInstance()->rider)) {
                Loader::getInstance()->dismountFromPlayer($player);
                $player->teleport(new Vector3($player->x, $player->y - 1, $player->z));
            }
        }
    }

    public function onTeleport(EntityTeleportEvent $event){
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if (in_array(strtolower($player->getName()), Loader::getInstance()->rider)) {
                Loader::getInstance()->dismountFromPlayer($player);
            }
        }
    }

    public function onSneak(PlayerToggleSneakEvent $event){
        $player = $event->getPlayer();
        if (in_array(strtolower($player->getName()), Loader::getInstance()->rider)) {
            Loader::getInstance()->dismountFromPlayer($player);
        }
    }
}
