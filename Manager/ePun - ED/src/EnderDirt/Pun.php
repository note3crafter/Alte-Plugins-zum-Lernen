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

class Pun extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "e" . Color::WHITE . "Manager] ";
	
	public function onEnable() {
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix . Color::GREEN . "die Cloud wurde erfolgreich geladen!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::DARK_PURPLE . " EnderDirt!");
		
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "Pun") {
    	
           if (isset($args[0])) {
           	
           	if ($sender->isOp()) {
           	
              	if (file_exists("/home/EnderCloud/players/" . $args[0] . ".yml")) {
           	
           	        if (isset($args[1])) {
           	
           	            if (isset($args[2])) {
           	
           	                $cloudplayer = new Config("/home/EnderCloud/players/" . $args[0] . ".yml", Config::YAML);
                               $cloudplayer->set("Ban", true);
                               $cloudplayer->set("BanGrund", $args[1]);
                               $cloudplayer->set("BanTime", $args[2]);
                               $cloudplayer->save();
                               $sender->sendMessage(Color::RED . "Der Spieler wurde erfolgreich gebannt!");
                               $player = $this->getServer()->getPlayerExact($args[0]);
                               $player->kick(
                               Color::RED . "Du wurdest Gebannt\n" .
                               Color::GRAY . "Grund: " . Color::YELLOW . $cloudplayer->get("BanGrund") . "\n" .
                               Color::GRAY . "Dauer: " . Color::YELLOW . $cloudplayer->get("BanTime") . " Tag/e\n", false
                               );
                               
                           }
                           
                       }
           	
                   } else {
               	
                  	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht");
               	
                   }
               
               }
           	
           }
           
        }
        
    	return true;
    	
    }
    
}