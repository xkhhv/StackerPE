<?php

namespace StackerPE\xkhhv;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use StackerPE\xkhhv\item\Saddle;
use StackerPE\xkhhv\Listener\StackerListener;

class Loader extends PluginBase{

    const DATA_RIDE_POSITION = 56;

    public $rider = array();


    /** @var self $instance */
    private static $instance;

    public function onEnable(){
        self::$instance = $this;
        ItemFactory::registerItem(new Saddle());
        Item::initCreativeItems();
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "StackerPE Enabled By GitHub: xkhhv");
        $this->getServer()->getPluginManager()->registerEvents(new StackerListener(), $this);
    }

    public static function getInstance(): self{
        return self::$instance;
    }


    /**
     * @return bool
     */
    public function sitOnPlayer(Player $damager, Player $entity): bool{
        if (!$damager->isSneaking() && $damager->isSurvival()) {
            $damager->setMotion(new Vector3(0, 0.2, 0));

            $pk = new AddEntityPacket();
            $pk->type = 95;
            $pk->entityRuntimeId = $entity->getId() * 1000;
            $pk->position = new Vector3($entity->x, $entity->y, $entity->z);
            $pk->motion = new Vector3(0, 0, 0);
            $pk->metadata = [
                Loader::DATA_RIDE_POSITION => [Entity::DATA_TYPE_VECTOR3F, new Vector3(0, 1.5, 0)]

            ];
            $size = 0.7;
            $size_ = array(30 => 2, 37 => 2, 42 => 2, 105 => 1.2, 107 => 2);
            if (isset($size_[95])) $size = $size_[95];
            $pk->metadata[Entity::DATA_SCALE] = [Entity::DATA_TYPE_FLOAT, $size];
            $pk->links[] = new EntityLink($entity->getId(), $pk->entityRuntimeId, 2, true);
            foreach ($this->getServer()->getOnlinePlayers() as $all) {
                $all->dataPacket($pk);
            }

            $link = new SetEntityLinkPacket;
            $link->link = new EntityLink($entity->getId() * 1000, $damager->getId(), 1, true);
            foreach ($this->getServer()->getOnlinePlayers() as $all) {
                $all->dataPacket($link);
            }
            $this->rider[strtolower($damager->getName())] = strtolower($damager->getName());
            return true;
        }else{
            $damager->sendMessage(TextFormat::RED . "You should be survival and not sneaking");
        }
    }

    /**
     * @return bool
     */
    public function dismountFromPlayer(Player $entity): bool {
        $link = new SetEntityLinkPacket;
        $link->link = new EntityLink($entity->getId(), 1, false);
        foreach ($this->getServer()->getOnlinePlayers() as $all) {
            $all->dataPacket($link);
        }
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = $entity->getId() * 1000;
        foreach($this->getServer()->getOnlinePlayers() as $all){
            $all->dataPacket($pk);
        }
        unset($this->rider[strtolower($entity->getName())]);
        return true;
    }
}
