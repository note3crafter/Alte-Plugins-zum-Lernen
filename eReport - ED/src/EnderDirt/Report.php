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

class Report extends PluginBase implements Listener
{

    public $prefix = Color::WHITE . "[" . Color::YELLOW . "eReport" . Color::WHITE . "] ";

    public function onEnable()
    {
    	
    	$rf = new Config("/home/EnderCloud/Reports.yml", Config::YAML);
        $rf->set("Reports", array("Steve"));
        $rf->save();
    	
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new resetReportList($this), 36000);
        $this->getLogger()->info($this->prefix . Color::GREEN . "die Player Cloud wurde erfolgreich geladen!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::DARK_PURPLE . " EnderDirt!");

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {

        if ($command->getName() === "Report") {

            if (isset($args[0])) {
            	
            	if (strtolower($args[0]) === "list") {
            	
            	    $pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                    if ($pf->get("Supporter") === true) {
                    	
            	        $rf = new Config("/home/EnderCloud/Reports.yml", Config::YAML);
                        $sender->sendMessage($this->prefix . "Alle Reports:");
                        foreach ($rf->get("Reports") as $player) {
                    	
                        	$sender->sendMessage(Color::YELLOW . "- " . Color::WHITE . $player);
                    	
                        }
                        
                     } else if ($pf->get("Moderator") === true) {
                    	
            	        $rf = new Config("/home/EnderCloud/Reports.yml", Config::YAML);
                        $sender->sendMessage($this->prefix . "Alle Reports:");
                        foreach ($rf->get("Reports") as $player) {
                    	
                        	$sender->sendMessage(Color::YELLOW . "- " . Color::WHITE . $player);
                    	
                        }
                        
                     } else if ($sender->isOp()) {
                    	
            	        $rf = new Config("/home/EnderCloud/Reports.yml", Config::YAML);
                        $sender->sendMessage($this->prefix . "Alle Reports:");
                        foreach ($rf->get("Reports") as $player) {
                    	
                        	$sender->sendMessage(Color::YELLOW . "- " . Color::WHITE . $player);
                    	
                        }
                        
                     }
            	    
                } else if (strtolower($args[0]) === "accept") {
                	
                	$pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                    if ($pf->get("Supporter") === true) {
                    	
                    	if (isset($args[1])) {
                	    
                	        if (!is_file("/home/EnderCloud/players/" . $args[1] . ".yml")) {
                	
                            } else {
                        	
                           	$ppf = new Config("/home/EnderCloud/players/" . $args[1] . ".yml", Config::YAML);
                           	$port = $ppf->get("Port");
                               $sender->transfer("84.200.84.61", $port);
                        	
                           }
                	    
                       }
                       
                    } else if ($pf->get("Moderator") === true) {
                    	
                    	if (isset($args[1])) {
                	    
                	        if (!is_file("/home/EnderCloud/players/" . $args[1] . ".yml")) {
                	
                            } else {
                        	
                           	$ppf = new Config("/home/EnderCloud/players/" . $args[1] . ".yml", Config::YAML);
                           	$port = $ppf->get("Port");
                               $sender->transfer("84.200.84.61", $port);
                        	
                           }
                	    
                       }
                       
                    } else if ($sender->isOp()) {
                    	
                    	if (isset($args[1])) {
                	    
                	        if (!is_file("/home/EnderCloud/players/" . $args[1] . ".yml")) {
                	
                            } else {
                        	
                           	$ppf = new Config("/home/EnderCloud/players/" . $args[1] . ".yml", Config::YAML);
                           	$port = $ppf->get("Port");
                               $sender->transfer("84.200.84.61", $port);
                        	
                           }
                	    
                       }
                    	
                    }
                	
                } else {
                	
                	if (!is_file("/home/EnderCloud/players/" . $args[0] . ".yml")) {
            	    
                    } else {
                	    
                        $rf = new Config("/home/EnderCloud/Reports.yml", Config::YAML);
                        $rp = $rf->get("Reports");
                        $rp[] = $args[0];
                        $rf->set("Reports", $rp);
                        $rf->save();
                        $sender->sendMessage($this->prefix . Color::GREEN . "Der Report wurde erfolgreich abgesendet");
                	
                    }
                    
                }
            	
            } else {
            	
            	$sender->sendMessage(Color::YELLOW . "-> /report <" . Color::WHITE . "PlayerName" . Color::YELLOW . ">");
                $sender->sendMessage(Color::YELLOW . "-> /report " . Color::WHITE . "list");
                $sender->sendMessage(Color::YELLOW . "-> /report " . Color::WHITE . "accept " . Color::YELLOW . "<" . Color::WHITE . "PlayerName" . Color::YELLOW . ">");
            	
            }

        }

        return true;

    }

}

class resetReportList extends PluginTask
{
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick)
    {
    	
    	$rf = new Config("/home/EnderCloud/Reports.yml", Config::YAML);
        $rf->set("Reports", array("Steve"));
        $rf->save();
    	
    }
	
}