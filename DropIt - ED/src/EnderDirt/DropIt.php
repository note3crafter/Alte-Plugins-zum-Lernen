<?php

namespace EnderDirt;

//Base
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
//Utils
use pocketmine\utils\TextFormat as Color;
use pocketmine\utils\Config;
//EventListener
use pocketmine\event\Listener;
//PlayerEvents
use pocketmine\Player;
use pocketmine\event\player\PlayerHungerChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
//ItemUndBlock
use pocketmine\block\Block;
use pocketmine\item\Item;
//BlockEvents
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
//EntityEvents
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Effect;
//Level
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
//Sounds
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\GhastShootSound;
//Commands
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
//Tile
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
//Nbt
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
//Inventar
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\Inventory;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;

class DropIt extends PluginBase implements Listener {
	
	public function onEnable() {
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
    
    }
    
    public function onLogin(PlayerLoginEvent $event) {
    	
    	$player = $event->getPlayer();
        $uuid = $player->getClientID();
        if (!is_file("/home/EnderCloud/Pets/" . $player->getName() . ".yml")) {
        	
        	$playerfile = new Config("/home/EnderCloud/Pets/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("dia", false);
            $playerfile->save();
            
        }
        
    }
    
    public function onMove(PlayerMoveEvent $event) {
    	
    	$player = $event->getPlayer();
        $this->createDia($player);
    	
    }
    
    public function createDia(Player $player) {
    	
    	$level = $player->getLevel();
    	$x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
        $level->dropItem(new Vector3($x, $y + 2, $z), Item::get(Item::BRICK, 0, 1));
    	
    }
    
}