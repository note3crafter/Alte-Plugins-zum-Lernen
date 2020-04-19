<?php

namespace EnderDirt;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;

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

class MainInv extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "Inventory" . Color::WHITE . "] ";
	
	private static $instance;
	
	public function onEnable() {
		
		self::$instance = $this;
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
		
    }
    
    public static function getInstance() : Main {
    	
		return self::$instance;
		
	}
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "Inventory") {
    	
           if (!InvMenuHandler::isRegistered()){
        	
               InvMenuHandler::register($this);
               
            }
            
        	$menu = InvMenu::create(InvMenu::TYPE_CHEST);
            $menu->readOnly();
            $blocke = Item::get(24, 0, 1);
            $stick = Item::get(280, 0, 1);
            $pickaxe = Item::get(285, 0, 1);
            $save = Item::get(351, 10, 1);
            $glas = Item::get(241, 10, 1);
            $blocke->setCustomName(Color::YELLOW . "Bloecke");
            $stick->setCustomName(Color::RED . "Waffen");
            $pickaxe->setCustomName(Color::AQUA . "Spitzhacken");
            $save->setCustomName(Color::GREEN . "Speichern");
            $menu->getInventory()->setItem(9, $blocke);
            $menu->getInventory()->setItem(10, $stick);
            $menu->getInventory()->setItem(11, $pickaxe);
            
            $menu->getInventory()->setItem(22, $save);
            
            $menu->getInventory()->setItem(0, $glas);
            $menu->getInventory()->setItem(1, $glas);
            $menu->getInventory()->setItem(2, $glas);
            $menu->getInventory()->setItem(3, $glas);
            $menu->getInventory()->setItem(4, $glas);
            $menu->getInventory()->setItem(5, $glas);
            $menu->getInventory()->setItem(6, $glas);
            $menu->getInventory()->setItem(7, $glas);
            $menu->getInventory()->setItem(8, $glas);
            
            $menu->getInventory()->setItem(18, $glas);
            $menu->getInventory()->setItem(19, $glas);
            $menu->getInventory()->setItem(20, $glas);
            $menu->getInventory()->setItem(21, $glas);
            $menu->getInventory()->setItem(23, $glas);
            $menu->getInventory()->setItem(24, $glas);
            $menu->getInventory()->setItem(25, $glas);
            $menu->getInventory()->setItem(26, $glas);
            $menu->send($sender);
            $menu->setListener([new MainInvListener($this), "onTransaction"]);
           
        }
        
    	return true;
    	
    }
    
}