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

class FarmWelt extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "FarmWelt" . Color::WHITE . "] ";
	
	public function onEnable() {
		
		if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
        
        if (is_dir($this->getDataFolder() . "/maps") !== true) {
        
            mkdir($this->getDataFolder() . "/maps");
            
        }
		
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new MapsResetTask($this), 72000); //72000
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
		
    }
    
    public function copymap($src, $dst) {
    
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
        	
            if (($file != '.') && ($file != '..')) {
            	
                if (is_dir($src . '/' . $file)) {
                	
                    $this->copymap($src . '/' . $file, $dst . '/' . $file);
                    
                } else {
                	
                    copy($src . '/' . $file, $dst . '/' . $file);
                    
                }
                
            }
            
        }
        
        closedir($dir);
        
    }

    public function deleteDirectory($dirPath) {
    
        if (is_dir($dirPath)) {
        	
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
            	
                if ($object != "." && $object != "..") {
                	
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                    	
                        $this->deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                        
                    } else {
                    	
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                        
                    }
                    
                }
                
            }
            
            reset($objects);
            rmdir($dirPath);
            
        }
        
    }
    
    public function onJoin(PlayerJoinEvent $event) {
    	
    	$event->setJoinMessage("");
    
    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$event->setQuitMessage("");
    
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	$config = $this->getConfig();
    	
       if ($command->getName() === "Spawn") {
    	
    	    $sender->sendMessage("Teleportiere zum Spawn");
            $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
            $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
            $sender->teleport($spawn, 0, 0);
    
        } else if ($command->getName() === "CopyWorld") {
        	
        	if ($sender->isOp()) {
    	
    	        $sender->sendMessage("Welt Kopiert");
                $this->copymap($this->getServer()->getDataPath() . "/worlds/" . $sender->getLevel()->getFolderName(), $this->getDataFolder() . "/maps/" . $sender->getLevel()->getFolderName());
            
            }
            
        } else if ($command->getName() === "SetWorld") {
        	
        	if ($sender->isOp()) {
    	
    	        if (isset($args[0])) {
    	
    	            $config->set($args[0] . "X", $sender->getX());
                    $config->set($args[0] . "Y", $sender->getY());
                    $config->set($args[0] . "Z", $sender->getZ());
                    $config->save();
                    $sender->sendMessage("Erfolgreich");
                    
                }
            
            }
            
        } else if ($command->getName() === "Farmwelt") {
        	
        	$level = $this->getServer()->getLevelByName("world");
            $sender->teleport(new Position($config->get("worldX"), $config->get("worldY")+1, $config->get("worldZ"), $level));
        	
        } else if ($command->getName() === "Nether") {
        	
        	$level = $this->getServer()->getLevelByName("nether");
            $sender->teleport(new Position($config->get("netherX"), $config->get("netherY")+1, $config->get("netherZ"), $level));
        	
        } else if ($command->getName() === "End") {
        	
        	$level = $this->getServer()->getLevelByName("ender");
            $sender->teleport(new Position($config->get("enderX"), $config->get("enderY")+1, $config->get("enderZ"), $level));
        	
        } else if ($command->getName() === "Food") {
        	
        	$sender->getInventory()->addItem(Item::get(Item::STEAK, 0, 64));
        	
        } else if ($command->getName() === "Fly") {
        	
        	$playerfile = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
            if ($playerfile->get("VIP") === true) {
            	
            	$sender->setAllowFlight(true);
                $sender->sendMessage(Color::GREEN . "Fly wurde Aktiviert!");
            	
            } else {
            	
            	$sender->sendMessage(Color::RED . "Du hast keine Berechtigung auf diesen Befehl!");
            	
            }
        	
        } else if ($command->getName() === "Position") {
        	
        	$playerfile = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
            if ($playerfile->get("Pos") === false) {
            	
            	$playerfile->set("Pos", true);
                $playerfile->save();
                $sender->sendMessage(Color::GREEN . "Koordination wurden Aktiviert!");
            	
            } else {
            	
            	$playerfile->set("Pos", false);
                $playerfile->save();
                $sender->sendMessage(Color::RED . "Koordination wurden Deaktiviert!");
            	
            }
        	
        } else if ($command->getName() === "Tpa") {
        	
        	if (isset($args[0])) {
        	
           	 if (strtolower($args[0]) === "accept") {
           	
           	     $playerfile = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
                    $sender->teleport($playerfile->get("TP"));
                    
                } else {
                	
                	if (file_exists("/home/EnderCloud/players/" . $args[0] . ".yml")) {
                	
                	    $playerfile = new Config("/home/EnderCloud/players/" . $args[0] . ".yml", Config::YAML);
                        $playerfile->set("TP", $sender->getName());
                        $playerfile->save();
                        $v = $this->getServer()->getPlayerExact($args[0]);	
                        if (!$v == null) {
                        	
                        	$v->sendMessage("Der Spieler: " . Color::GOLD . $playerfile->get("TP") . Color::WHITE . " will sich zu dir Teleportieren");
                            $v->sendMessage("Du kannst diese mit: " . Color::GOLD . "/tpa accept" . Color::WHITE . " annehmen");
                            $sender->sendMessage("Die Teleport anfrage wurde erfolgreich versendet");
                            
                        }
                        
                    }
                	
                }
        	
            }
        	
        }
        
    	return true;
    
    }
    
    public function onMove(PlayerMoveEvent $event) {
    	
    	$player = $event->getPlayer();
        $x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        if ($playerfile->get("Pos") === true) {
        	
        	$player->sendPopup(Color::GOLD . "X: " . Color::WHITE . $x . Color::GOLD . " Y: " . Color::WHITE . $y . Color::GOLD . " Z: " . Color::WHITE . $z);
        	
        }
        
    }
	
}

class MapsResetTask extends PluginTask
{
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick)
    {
    	
    	$config = $this->plugin->getConfig();
        $all = $this->plugin->getServer()->getOnlinePlayers();
        foreach ($all as $player) {
        	
        	$this->plugin->getServer()->broadcastMessage($this->plugin->prefix . Color::GREEN . "Die farm Welten wurden nun resetet");
            $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
            $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
            $player->teleport($spawn, 0, 0);
            //Farmwelt
            $levelname = "world";
            $lev = $this->plugin->getServer()->getLevelByName($levelname);
            $this->plugin->getServer()->unloadLevel($lev);
            $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
            $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
            $this->plugin->getServer()->loadLevel($levelname);
            //End
            $levelname2 = "ender";
            $lev2 = $this->plugin->getServer()->getLevelByName($levelname2);
            $this->plugin->getServer()->unloadLevel($lev2);
            $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname2);
            $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname2, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname2);
            $this->plugin->getServer()->loadLevel($levelname2);
            //Nether
            $levelname3 = "nether";
            $lev3 = $this->plugin->getServer()->getLevelByName($levelname3);
            $this->plugin->getServer()->unloadLevel($lev3);
            $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname3);
            $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname3, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname3);
            $this->plugin->getServer()->loadLevel($levelname3);
            
        }
    	
    }
	
}