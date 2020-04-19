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
use pocketmine\event\player\PlayerBedEnterEvent;
//ItemUndBlock
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
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
use pocketmine\level\sound\GhastShootSound;
use pocketmine\level\sound\GhastSound;
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
use pocketmine\event\inventory\CraftItemEvent;

class QuickSurvivalGames extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::GREEN . "QSG" . Color::WHITE . "] ";
	public $arenaname = "";
	public $mode = 0;
	public $players = 0;
	
	private static $instance;

    public function onEnable() {
    	
	    if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
        
        if (is_dir("/home/EnderCloud/QSG/players") !== true) {
			
            mkdir("/home/EnderCloud/QSG/players");
            
        }
    	
        if(is_dir($this->getDataFolder() . "/maps") !== true) {
        
            mkdir($this->getDataFolder() . "/maps");
            
        }
        
        self::$instance = $this;

        $this->saveDefaultConfig();
        $this->reloadConfig();

        $config = $this->getConfig();
        
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new GameSender($this), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new PlayerSender($this), 10);
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
        
    }
    
    public static function getInstance() : Main {
    	
		return self::$instance;
		
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
    
    public function onLogin(PlayerLoginEvent $event) {
    
        $player = $event->getPlayer();
        if (!is_file("/home/EnderCloud/QSG/players/" . $player->getName() . ".yml")) {
        
            $playerfile = new Config("/home/EnderCloud/QSG/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Kills", 0);
            $playerfile->set("Deaths", 0);
            $playerfile->set("KD", 0);
            $playerfile->save();
            
        }
        
    }
    
    public function onJoin(PlayerJoinEvent $event)
    {

        $player = $event->getPlayer();
        $config = $this->getConfig();
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $player->setGamemode(0);
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->clearAll();
        $set = Item::get(395, 0, 1);
        $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
        $player->getInventory()->setItem(4, $set);
        $player->removeAllEffects();
        $player->setAllowFlight(false);
        $all = $this->getServer()->getOnlinePlayers();
        if ($config->get("ingame") === true) {
        	
        	$event->setJoinMessage("");
        	$player->getInventory()->clearAll();
            $player->setGamemode(3);
            $level = $this->getServer()->getLevelByName($config->get("Arena"));
            $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
            $player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
        
        } else {
        	
        	$event->setJoinMessage(Color::GRAY . "> " . Color::DARK_GRAY . "> " . $player->getDisplayName() . Color::GRAY . " hat den Server Betreten!");
        
        if ($this->players === 0) {
        	
        	$this->players++;
        	$config->set("ingame", false);
            $config->set("death", false);
            $config->set("time", 60);
            $config->set("playtime", 180);
            $config->set("deathtime", 300);
            $config->set("player1", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 1) {
        	
        	$this->players++;
            $config->set("player2", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 2) {
        	
        	$this->players++;
            $config->set("player3", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 3) {
        	
        	$this->players++;
            $config->set("player4", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 4) {
        	
        	$this->players++;
            $config->set("player5", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 5) {
        	
        	$this->players++;
            $config->set("player6", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 6) {
        	
        	$this->players++;
            $config->set("player7", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 7) {
        	
        	$this->players++;
            $config->set("player8", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 8) {
        	
        	$this->players++;
            $config->set("player9", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 9) {
        	
        	$this->players++;
            $config->set("player10", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 10) {
        	
        	$this->players++;
            $config->set("player11", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 11) {
        	
        	$this->players++;
            $config->set("player12", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 12) {
        	
        	$this->players++;
            $config->set("player13", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 13) {
        	
        	$this->players++;
            $config->set("player14", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 14) {
        	
        	$this->players++;
            $config->set("player15", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 15) {
        	
        	$this->players++;
            $config->set("player16", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 16) {
        	
        	$this->players++;
            $config->set("player17", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 17) {
        	
        	$this->players++;
            $config->set("player18", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 18) {
        	
        	$this->players++;
            $config->set("player19", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 19) {
        	
        	$this->players++;
            $config->set("player20", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 20) {
        	
        	$player->transfer("84.200.84.61", 19132);
        	
        }
        
        }
        
    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$player = $event->getPlayer();
        $event->setQuitMessage(Color::GRAY . "< " . Color::DARK_GRAY . "< " . $player->getDisplayName() . Color::GRAY . " hat den Server verlassen!");
        $config = $this->getConfig();
        if ($config->get("ingame") === false) {
        	
        	if ($player->getName() === $config->get("player1")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player1", $p2);
                $config->set("player2", $p3);
                $config->set("player3", $p4);
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player2")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player2", $p3);
                $config->set("player3", $p4);
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player3")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player3", $p4);
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player4")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player5")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player6")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player7")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player8")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player9")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player10")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player11")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player12")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player13")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player14")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player15")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player15", $p16);
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player16")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player16", $p17);
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player17")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player17", $p18);
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player18")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player18", $p19);
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player19")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player19", $p20);
                $config->set("player20", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player20")) {
        	
        	    $this->players--;
        	    $p2 = $config->get("player2");
                $p3 = $config->get("player3");
                $p4 = $config->get("player4");
                $p5 = $config->get("player5");
                $p6 = $config->get("player6");
                $p7 = $config->get("player7");
                $p8 = $config->get("player8");
                $p9 = $config->get("player9");
                $p10 = $config->get("player10");
                $p11 = $config->get("player11");
                $p12 = $config->get("player12");
                $p13 = $config->get("player13");
                $p14 = $config->get("player14");
                $p15 = $config->get("player15");
                $p16 = $config->get("player16");
                $p17 = $config->get("player17");
                $p18 = $config->get("player18");
                $p19 = $config->get("player19");
                $p20 = $config->get("player20");
                
                $config->set("player20", "");
                $config->save();
                
            }
        	
        } else {
        	
        	if ($this->players < 1) {
        	
        	    $this->players = 0;
        
            } else {
        	
        	if ($player->getName() === $config->get("player1")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player2")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player3")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player4")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player5")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player6")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player7")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player8")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player9")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player10")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player11")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player12")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player13")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player14")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player15")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player16")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player17")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player18")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player19")) {
        	
        	    $this->players--;
        
            } else if ($player->getName() === $config->get("player20")) {
        	
        	    $this->players--;
        
            }
            
            }
        	
        }
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "QuickSurvivalGames":
            if (isset($args[0])) {
            	
            	if (strtolower($args[0]) === "lobby") {
            	
            	    if ($sender->isOp()) {
            	
            	        if (isset($args[1])) {
            	
            	            $config = $this->getConfig();
                            $config->set("Server", $args[1]);
                            $config->save();
                            $sender->sendMessage($this->prefix . "Der " . Color::GOLD . "Server Name " . Color::WHITE . "wurde gesetzt!");
                            
                        }
            	
                    }
                    
                } else if (strtolower($args[0]) === "make") {
                	
                	if ($sender->isOp()) {
                	
                        if (isset($args[1])) {
                        	
                        	if (file_exists($this->getServer()->getDataPath() . "/worlds/" . $args[1])) {
                        	
                        	   if (!$this->getServer()->getLevelByName($args[1]) instanceof Level) {
                                    	
                                        $this->getServer()->loadLevel($args[1]);
                                        
                                    }
                                    
                                    $spawn = $this->getServer()->getLevelByName($args[1])->getSafeSpawn();
                                    $this->getServer()->getLevelByName($args[1])->loadChunk($spawn->getX(), $spawn->getZ());
                                    $sender->teleport($spawn, 0, 0);
                                    $config = $this->getConfig();
                                    $config->set("Arena", $args[1]);
                                    $config->save();
                                    $sender->sendMessage($this->prefix . "Du hast die Arena " . Color::RED . $args[1] . Color::WHITE . " ausgewaehlt. Jetzt musst du auf den Spawn fuer den ersten Spieler tippen");
                                    $this->mode++;
                                    return true;
                                    
                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        if ($command->getName() === "Start") {
        	
        	$pg = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
            if ($pg->get("NickP") === true) {
            	
            	$config = $this->getConfig();
                if ($config->get("ingame") === false) {
                	
                	$config->set("time", 5);
                    $config->save();
                	
                } else {
                	
                	$sender->sendMessage(Color::RED . "Die Runde hat schon begonnen!");
                
                }
            	
            } else {
            	
            	$sender->sendMessage(Color::RED . "Du hast keine Berechtigung fÃ¼r diesen befehl!");
            
            }
            
        }
        
        if ($command->getName() === "Stats") {
        	
        	if (isset($args[0])) {
        	
        	    if (file_exists("/home/EnderCloud/QSG/players/" . $args[0] . ".yml")) {
        	
        	        $pf = new Config("/home/EnderCloud/QSG/players/" . $args[0] . ".yml", Config::YAML);
                    $deaths = $pf->get("Deaths");
                    $kills = $pf->get("Kills");
                    if ($deaths === 0) {
              	
                  	$kd = $kills;
              
                   } else {
              	
                  	$kd = $kills/$deaths;
              
                   }
              
                  $sender->sendMessage(Color::DARK_PURPLE . ">>>>>>>>> " . $this->prefix . Color::DARK_PURPLE . "<<<<<<<<<");
                  $sender->sendMessage($this->prefix . "Deine Stats:");
                  $sender->sendMessage($this->prefix . "Deine Kills: " . Color::YELLOW . $kills);
                  $sender->sendMessage($this->prefix . "Deine Deaths: " . Color::YELLOW . $deaths);
                  $sender->sendMessage($this->prefix . "Deine KD: " . Color::YELLOW . $kd);
                  $sender->sendMessage(Color::DARK_PURPLE . ">>>>>>>>> " . $this->prefix . Color::DARK_PURPLE . "<<<<<<<<<");
              
                } else {
                	
                	$sender->sendMessage($this->prefix . Color::RED . "Diesen Spieler gibt es nicht");
                	
                }
                
            } else {
            	
            	$pf = new Config("/home/EnderCloud/QSG/players/" . $sender->getName() . ".yml", Config::YAML);
                $deaths = $pf->get("Deaths");
                $kills = $pf->get("Kills");
                if ($deaths === 0) {
              	
              	$kd = $kills;
              
              } else {
              	
              	$kd = $kills/$deaths;
              
              }
              
              $sender->sendMessage(Color::DARK_PURPLE . ">>>>>>>>> " . $this->prefix . Color::DARK_PURPLE . "<<<<<<<<<");
              $sender->sendMessage($this->prefix . "Deine Stats:");
              $sender->sendMessage($this->prefix . "Deine Kills: " . Color::YELLOW . $kills);
              $sender->sendMessage($this->prefix . "Deine Deaths: " . Color::YELLOW . $deaths);
              $sender->sendMessage($this->prefix . "Deine KD: " . Color::YELLOW . $kd);
              $sender->sendMessage(Color::DARK_PURPLE . ">>>>>>>>> " . $this->prefix . Color::DARK_PURPLE . "<<<<<<<<<");
            	
            }
        	
        }
        
        return true;
        
    }
    
    public function onInteract(PlayerInteractEvent $event) {
    	
    	$player = $event->getPlayer();
        $block = $event->getBlock();
        $tile = $player->getLevel()->getTile($block);
        $config = $this->getConfig();
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        if ($block->getId() === 54) {
        	
        	$event->setCancelled(true);
            $x = $block->getX();
            $y = $block->getY();
            $z = $block->getZ();
            $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
        	if (!InvMenuHandler::isRegistered()){
        	
                 InvMenuHandler::register($this);
               
            }
            
            $menu = InvMenu::create(InvMenu::TYPE_CHEST);
            for ($n = 0; $n < 26; $n++) {
                    	
                 $rand = mt_rand(1, 7);
                 $rand2 = mt_rand(1, count($this->getConfig()->get("items")));
                 if ($rand === 1) {
                        	
                      $menu->setItem($n ,Item::get($this->getConfig()->getNested("items." . $rand2 . ".id"), $this->getConfig()->getNested("items." . $rand2 . ".meta"), $this->getConfig()->getNested("items." . $rand2 . ".amount")));
                            
                 }
                        
            }
            
            $menu->send($player);
        	
        }
        
        if ($this->mode === 1 && $player->isOp()) {
        	
        	$af->set("s1x", $block->getX() + 0.5);
            $af->set("s1y", $block->getY() + 1);
            $af->set("s1z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 2. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 2 && $player->isOp()) {
        	
        	$af->set("s2x", $block->getX() + 0.5);
            $af->set("s2y", $block->getY() + 1);
            $af->set("s2z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 3. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 3 && $player->isOp()) {
        	
        	$af->set("s3x", $block->getX() + 0.5);
            $af->set("s3y", $block->getY() + 1);
            $af->set("s3z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 4. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 4 && $player->isOp()) {
        	
        	$af->set("s4x", $block->getX() + 0.5);
            $af->set("s4y", $block->getY() + 1);
            $af->set("s4z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 5. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 5 && $player->isOp()) {
        	
        	$af->set("s5x", $block->getX() + 0.5);
            $af->set("s5y", $block->getY() + 1);
            $af->set("s5z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 6. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 6 && $player->isOp()) {
        	
        	$af->set("s6x", $block->getX() + 0.5);
            $af->set("s6y", $block->getY() + 1);
            $af->set("s6z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 7. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 7 && $player->isOp()) {
        	
        	$af->set("s7x", $block->getX() + 0.5);
            $af->set("s7y", $block->getY() + 1);
            $af->set("s7z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 8. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 8 && $player->isOp()) {
        	
        	$af->set("s8x", $block->getX() + 0.5);
            $af->set("s8y", $block->getY() + 1);
            $af->set("s8z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 9. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 9 && $player->isOp()) {
        	
        	$af->set("s9x", $block->getX() + 0.5);
            $af->set("s9y", $block->getY() + 1);
            $af->set("s9z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 10. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 10 && $player->isOp()) {
        	
        	$af->set("s10x", $block->getX() + 0.5);
            $af->set("s10y", $block->getY() + 1);
            $af->set("s10z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 11. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 11 && $player->isOp()) {
        	
        	$af->set("s11x", $block->getX() + 0.5);
            $af->set("s11y", $block->getY() + 1);
            $af->set("s11z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 12. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 12 && $player->isOp()) {
        	
        	$af->set("s12x", $block->getX() + 0.5);
            $af->set("s12y", $block->getY() + 1);
            $af->set("s12z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 13. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 13 && $player->isOp()) {
        	
        	$af->set("s13x", $block->getX() + 0.5);
            $af->set("s13y", $block->getY() + 1);
            $af->set("s13z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 14. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 14 && $player->isOp()) {
        	
        	$af->set("s14x", $block->getX() + 0.5);
            $af->set("s14y", $block->getY() + 1);
            $af->set("s14z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 15. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 15 && $player->isOp()) {
        	
        	$af->set("s15x", $block->getX() + 0.5);
            $af->set("s15y", $block->getY() + 1);
            $af->set("s15z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 16. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 16 && $player->isOp()) {
        	
        	$af->set("s16x", $block->getX() + 0.5);
            $af->set("s16y", $block->getY() + 1);
            $af->set("s16z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 17. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 17 && $player->isOp()) {
        	
        	$af->set("s17x", $block->getX() + 0.5);
            $af->set("s17y", $block->getY() + 1);
            $af->set("s17z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 18. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 18 && $player->isOp()) {
        	
        	$af->set("s18x", $block->getX() + 0.5);
            $af->set("s18y", $block->getY() + 1);
            $af->set("s18z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 19. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 19 && $player->isOp()) {
        	
        	$af->set("s19x", $block->getX() + 0.5);
            $af->set("s19y", $block->getY() + 1);
            $af->set("s19z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 20. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 20 && $player->isOp()) {
        	
        	$af->set("s20x", $block->getX() + 0.5);
            $af->set("s20y", $block->getY() + 1);
            $af->set("s20z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den Deathmatch Spawn");
            $this->mode++;
            $spawn = $this->getServer()->getLevelByName("DeathMatch");
            $this->getServer()->getLevelByName($spawn)->loadChunk($spawn->getX(), $spawn->getZ());
            $player->teleport($spawn, 0, 0);
            
        } else if ($this->mode === 21 && $player->isOp()) {
        	
        	$af->set("sdx", $block->getX() + 0.5);
            $af->set("sdy", $block->getY() + 1);
            $af->set("sdz", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Die Arena ist nun Spielbereit");
            $this->mode = 0;
            
            $this->copymap($this->getServer()->getDataPath() . "/worlds/" . $player->getLevel()->getFolderName(), $this->getDataFolder() . "/maps/" . $player->getLevel()->getFolderName());
            $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
            $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
            $player->teleport($spawn, 0, 0);
            
        }
        
    }
    
    public function onDamage(EntityDamageEvent $event) {
    
    	$player = $event->getEntity();
        $config = $this->getConfig();
        if ($config->get("ingame") === true) {
        
        } else if ($config->get("death") === true) {
        
        } else {
        	
        	$event->setCancelled(true);
        
        }
        
    }
    
    public function onDeath(PlayerDeathEvent $event) {
    	
    	$player = $event->getEntity();
        $player->setGamemode(3);
        $this->players--;
        if ($player instanceof Player) {
        	
        	$cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
            	
            	$killer = $cause->getDamager();
                if ($killer instanceof Player) {
                	
                	$event->setDeathMessage($this->prefix . Color::GREEN . $player->getName() . Color::WHITE . " wurde von " . Color::GREEN . $killer->getName() . Color::WHITE . " getoetet!");
                    $killer->sendMessage(Color::WHITE . "[" . Color::DARK_PURPLE . "+" . Color::WHITE . "] 50 Coins");
                    $pc = new Config("/home/EnderCloud/eCoins/" . $killer->getName() . ".yml", Config::YAML);
                    $pc->set("coins", $pc->get("coins")+50);
                    $pc->save();
                    $pf = new Config("/home/EnderCloud/QSG/players/" . $player->getName() . ".yml", Config::YAML);
                    $pf->set("Deaths", $pf->get("Deaths")+1);
                    $pf->save();
                    $kf = new Config("/home/EnderCloud/QSG/players/" . $killer->getName() . ".yml", Config::YAML);
                    $kf->set("Kills", $pf->get("Kills")+1);
                    $kf->save();
                
                } else {
                	
                	$event->setDeathMessage($this->prefix . Color::GREEN . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
                    $pf = new Config("/home/EnderCloud/QSG/players/" . $player->getName() . ".yml", Config::YAML);
                    $pf->set("Deaths", $pf->get("Deaths")+1);
                    $pf->save();
                
                }
                
            }
            
        }
    
    }
    
    public function onRespawn(PlayerRespawnEvent $event) {
    	
    	$player = $event->getPlayer();
        $this->delPlayer($player);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        
    }
    
    public function delPlayer(Player $player) {
    	
    	$config = $this->getConfig();
        if ($player->getName() === $config->get("player1")) {
        	
        	$config->set("player1", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player2")) {
        	
        	$config->set("player2", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player3")) {
        	
        	$config->set("player3", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player4")) {
        	
        	$config->set("player4", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player5")) {
        	
        	$config->set("player5", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player6")) {
        	
        	$config->set("player6", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player7")) {
        	
        	$config->set("player7", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player8")) {
        	
        	$config->set("player8", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player9")) {
        	
        	$config->set("player9", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player10")) {
        	
        	$config->set("player10", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player11")) {
        	
        	$config->set("player11", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player12")) {
        	
        	$config->set("player12", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player13")) {
        	
        	$config->set("player13", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player14")) {
        	
        	$config->set("player14", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player15")) {
        	
        	$config->set("player15", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player16")) {
        	
        	$config->set("player16", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player17")) {
        	
        	$config->set("player17", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player18")) {
        	
        	$config->set("player18", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player19")) {
        	
        	$config->set("player19", "");
            $config->save();
        	
        } else if ($player->getName() === $config->get("player20")) {
        	
        	$config->set("player20", "");
            $config->save();
        	
        }
    	
    }
    
    public function giveKit(Player $player) {
    	
    	$player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->addItem(Item::get(271, 0, 1));
        
    }
    
    public function onBreak(BlockBreakEvent $event) {
    	
        $event->setCancelled(true);
        
    }
    
    public function onPlace(BlockPlaceEvent $event) {
    	
        $event->setCancelled(true);
        
    }
    
    public function spawn(Player $player) {
    	
    	$pos = $player->getPosition();
        $player->setSpawn($pos);
        
    }
    
    public function teleportIngame(Player $player) {
    	
    	$config = $this->getConfig();
        if (!$this->getServer()->getLevelByName($config->get("Arena")) instanceof Level) {
        	
            $this->getServer()->loadLevel($config->get("Arena"));
            $this->getServer()->loadLevel("DeathMatch");
            
        }
        
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
        $level->setTime(0);
        $level->stopTime();
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        $player->setHealth(20);
        $player->setFood(20);
        if ($player->getName() === $config->get("player1")) {
        	
        	$player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
        
        } else if ($player->getName() === $config->get("player2")) {
            
        	$player->teleport(new Position($af->get("s2x"), $af->get("s2y")+1, $af->get("s2z"), $level));
        
        } else if ($player->getName() === $config->get("player3")) {
            
        	$player->teleport(new Position($af->get("s3x"), $af->get("s3y")+1, $af->get("s3z"), $level));
        
        } else if ($player->getName() === $config->get("player4")) {
            
        	$player->teleport(new Position($af->get("s4x"), $af->get("s4y")+1, $af->get("s4z"), $level));
        
        } else if ($player->getName() === $config->get("player5")) {
        	
        	$player->teleport(new Position($af->get("s5x"), $af->get("s5y")+1, $af->get("s5z"), $level));
        
        } else if ($player->getName() === $config->get("player6")) {
            
        	$player->teleport(new Position($af->get("s6x"), $af->get("s6y")+1, $af->get("s6z"), $level));
        
        } else if ($player->getName() === $config->get("player7")) {
            
        	$player->teleport(new Position($af->get("s7x"), $af->get("s7y")+1, $af->get("s7z"), $level));
        
        } else if ($player->getName() === $config->get("player8")) {
            
        	$player->teleport(new Position($af->get("s8x"), $af->get("s8y")+1, $af->get("s8z"), $level));
        
        } else if ($player->getName() === $config->get("player9")) {
            
        	$player->teleport(new Position($af->get("s9x"), $af->get("s9y")+1, $af->get("s9z"), $level));
        
        } else if ($player->getName() === $config->get("player10")) {
            
        	$player->teleport(new Position($af->get("s10x"), $af->get("s10y")+1, $af->get("s10z"), $level));
        
        } else if ($player->getName() === $config->get("player11")) {
            
        	$player->teleport(new Position($af->get("s11x"), $af->get("s11y")+1, $af->get("s11z"), $level));
        
        } else if ($player->getName() === $config->get("player12")) {
        	
        	$player->teleport(new Position($af->get("s12x"), $af->get("s12y")+1, $af->get("s12z"), $level));
        
        } else if ($player->getName() === $config->get("player13")) {
            
        	$player->teleport(new Position($af->get("s13x"), $af->get("s13y")+1, $af->get("s13z"), $level));
        
        } else if ($player->getName() === $config->get("player14")) {
            
        	$player->teleport(new Position($af->get("s14x"), $af->get("s14y")+1, $af->get("s14z"), $level));
        
        } else if ($player->getName() === $config->get("player15")) {
            
        	$player->teleport(new Position($af->get("s15x"), $af->get("s15y")+1, $af->get("s15z"), $level));
        
        } else if ($player->getName() === $config->get("player16")) {
            
        	$player->teleport(new Position($af->get("s16x"), $af->get("s16y")+1, $af->get("s16z"), $level));
        
        } else if ($player->getName() === $config->get("player17")) {
            
        	$player->teleport(new Position($af->get("s17x"), $af->get("s17y")+1, $af->get("s17z"), $level));
        
        } else if ($player->getName() === $config->get("player18")) {
        	
        	$player->teleport(new Position($af->get("s18x"), $af->get("s18y")+1, $af->get("s18z"), $level));
        
        } else if ($player->getName() === $config->get("player19")) {
            
        	$player->teleport(new Position($af->get("s19x"), $af->get("s19y")+1, $af->get("s19z"), $level));
        
        } else if ($player->getName() === $config->get("player20")) {
            
        	$player->teleport(new Position($af->get("s20x"), $af->get("s20y")+1, $af->get("s20z"), $level));
        
        }
        
    }
    
    public function teleportDeath(Player $player) {
    	
    	$config = $this->getConfig();
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
    	$levl = $this->getServer()->getLevelByName("DeathMatch");
        $levl->setTime(0);
        $levl->stopTime();
        $player->teleport(new Position($af->get("sdx"), $af->get("sdy")+1, $af->get("sdz"), $levl));
        
    }
	
}

class PlayerSender extends PluginTask
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
        $config->set("players", $this->plugin->players);
        $config->save();
        if (count($all) === 0) {

            if ($config->get("state") === true) {

                $config->set("ingame", false);
                $config->set("death", false);
                $config->set("state", false);
                $config->set("reset", false);
                $config->set("rtime", 10);
                $config->set("time", 60);
                $config->set("playtime", 180);
                $config->set("deathtime", 300);
                $config->set("players", 0);
                $config->save();

            }

        }
    	
    }
	
}

class GameSender extends PluginTask
{

    public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick)
    {

        $level = $this->plugin->getServer()->getDefaultLevel();
        $config = $this->plugin->getConfig();
        $all = $this->plugin->getServer()->getOnlinePlayers();
        if ($config->get("ingame") === false) {

            if ($this->plugin->players < 2) {

                foreach ($all as $player) {

                    $player->sendPopup(Color::GRAY . ">> Warten auf weitere Spieler <<");

                }

            }

            if ($this->plugin->players >= 2) {

                $config->set("time", $config->get("time") - 1);
                $config->save();
                $time = $config->get("time") + 1;
                foreach ($all as $player) {
                	
                	$player->sendPopup(
                       Color::YELLOW . "Spieler: " . Color::WHITE . $this->plugin->players . Color::YELLOW . "/" . Color::WHITE . "20" . "\n" .
                       Color::GREEN . "Map: " . Color::WHITE . $config->get("Arena")
                    );
                	
                }
                
                if ($time % 5 === 0 && $time > 0) {

                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match startet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 15) {
                
                	$config->set("state", true);
                    $config->save();
                	foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match startet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    }
                	
                } else if ($time === 4 || $time === 3 || $time === 2 || $time === 1) {

                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match startet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 0) {
                	
                	$config->set("death", false);
                    $config->set("ingame", true);
                    $config->set("state", true);
                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Deathmatch startet in " . Color::DARK_PURPLE . "3" . Color::WHITE . " Minuten!");
                        $player->setHealth(20);
                        $player->setFood(20);
                        $this->plugin->teleportIngame($player);
                        $this->plugin->spawn($player);
                        $this->plugin->giveKit($player);

                    }

                    $config->save();

                }

            }

        } else if ($config->get("ingame") === true) {

            $all = $this->plugin->getServer()->getOnlinePlayers();
            if ($this->plugin->players <= 1) {

                foreach ($all as $player) {

                    $player->getInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->setGamemode(0);
                    $player->removeAllEffects();
                    $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                    $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                    $player->teleport($spawn, 0, 0);
                    $player->sendMessage(Color::WHITE . "[" . Color::DARK_PURPLE . "+" . Color::WHITE . "] 100 Coins");
                    $pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                    $pc->set("coins", $pc->get("coins")+100);
                    $pc->save();
                    $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                    $pf->set("wins", $pf->get("wins") + 1);
                    $pf->save();
                    $config->set("ingame", false);
                    $config->set("death", false);
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 180);
                    $config->set("deathtime", 300);
                    $config->set("player1", "");
                    $config->set("player2", "");
                    $config->set("player3", "");
                    $config->set("player4", "");
                    $config->set("player5", "");
                    $config->set("player6", "");
                    $config->set("player7", "");
                    $config->set("player8", "");
                    $config->set("player9", "");
                    $config->set("player10", "");
                    $config->set("player11", "");
                    $config->set("player12", "");
                    $config->set("player13", "");
                    $config->set("player14", "");
                    $config->set("player15", "");
                    $config->set("player16", "");
                    $config->set("player17", "");
                    $config->set("player18", "");
                    $config->set("player19", "");
                    $config->set("player20", "");
                    $config->save();
                    $this->plugin->players = 0;
                    $levelname = $config->get("Arena");
                    $lev = $this->plugin->getServer()->getLevelByName($levelname);
                    $lev->setTime(0);
                    $lev->stopTime();

                }

            } elseif ($this->plugin->players >= 2) {

                $config->set("playtime", $config->get("playtime") - 1);
                $config->save();
                $time = $config->get("playtime") + 1;
                foreach ($all as $player) {
                	
                    if ($time === 60) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das DeathMatch startet in " . Color::DARK_PURPLE . $time / 60 . "er" . Color::WHITE . " Minute!");

                    } else if ($time === 1 || $time === 2 || $time === 3 || $time === 4 || $time === 5 || $time === 15 || $time === 30) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das DeathMatch startet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    } else if ($time === 0) {
                    	
                    	$config->set("ingame", false);
                        $config->set("death", true);
                        $config->save();
                        $this->plugin->teleportDeath($player);
                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das DeathMatch startet in " . Color::DARK_PURPLE . "5" . Color::WHITE . " Minuten!");

                    }

                }

            }

        }
        
        if ($config->get("death") === true) {
        	
        	$all = $this->plugin->getServer()->getOnlinePlayers();
        	if ($this->plugin->players <= 1) {
        	
        	    foreach ($all as $player) {

                    $player->getInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->setGamemode(0);
                    $player->removeAllEffects();
                    $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                    $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                    $player->teleport($spawn, 0, 0);
                    $player->sendMessage(Color::WHITE . "[" . Color::DARK_PURPLE . "+" . Color::WHITE . "] 100 Coins");
                    $pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                    $pc->set("coins", $pc->get("coins")+100);
                    $pc->save();
                    $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                    $pf->set("wins", $pf->get("wins") + 1);
                    $pf->save();
                    $config->set("ingame", false);
                    $config->set("death", false);
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 180);
                    $config->set("deathtime", 300);
                    $config->set("player1", "");
                    $config->set("player2", "");
                    $config->set("player3", "");
                    $config->set("player4", "");
                    $config->set("player5", "");
                    $config->set("player6", "");
                    $config->set("player7", "");
                    $config->set("player8", "");
                    $config->set("player9", "");
                    $config->set("player10", "");
                    $config->set("player11", "");
                    $config->set("player12", "");
                    $config->set("player13", "");
                    $config->set("player14", "");
                    $config->set("player15", "");
                    $config->set("player16", "");
                    $config->set("player17", "");
                    $config->set("player18", "");
                    $config->set("player19", "");
                    $config->set("player20", "");
                    $config->save();
                    $this->plugin->players = 0;
                    $levelname = $config->get("Arena");
                    $lev = $this->plugin->getServer()->getLevelByName($levelname);
                    $lev->setTime(0);
                    $lev->stopTime();

                }
                
            } else if ($this->plugin->players >= 2) {
            	
            	$config->set("deathtime", $config->get("deathtime") - 1);
                $config->save();
                $time = $config->get("deathtime") + 1;
                foreach ($all as $player) {
                	
                    if ($time === 60) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das DeathMatch startet in " . Color::DARK_PURPLE . $time / 60 . "er" . Color::WHITE . " Minute!");

                    } else if ($time === 1 || $time === 2 || $time === 3 || $time === 4 || $time === 5 || $time === 15 || $time === 30) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das DeathMatch startet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    } else if ($time === 0) {

                        $player->getInventory()->clearAll();
                        $player->setHealth(20);
                        $player->setFood(20);
                        $player->setGamemode(0);
                        $player->removeAllEffects();
                        $player->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast das Match gewonnen!");
                        $this->plugin->getServer()->broadcastMessage($this->plugin->prefix . $player->getName() . Color::GREEN . " hat das Match in " . Color::WHITE . $config->get("Arena") . Color::GREEN . " Gewonnen!");
                        $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->teleport($spawn, 0, 0);
                        $config->set("ingame", false);
                    $config->set("death", false);
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 180);
                    $config->set("deathtime", 300);
                    $config->set("player1", "");
                    $config->set("player2", "");
                    $config->set("player3", "");
                    $config->set("player4", "");
                    $config->set("player5", "");
                    $config->set("player6", "");
                    $config->set("player7", "");
                    $config->set("player8", "");
                    $config->set("player9", "");
                    $config->set("player10", "");
                    $config->set("player11", "");
                    $config->set("player12", "");
                    $config->set("player13", "");
                    $config->set("player14", "");
                    $config->set("player15", "");
                    $config->set("player16", "");
                    $config->set("player17", "");
                    $config->set("player18", "");
                    $config->set("player19", "");
                    $config->set("player20", "");
                        $config->save();
                        $this->plugin->players = 0;
                        $levelname = $config->get("Arena");
                        $lev = $this->plugin->getServer()->getLevelByName($levelname);
                        $lev->setTime(0);
                        $lev->stopTime();

                    }

                }
            	
            }
        	
        }
        
        if ($config->get("reset") === true) {

            $config->set("rtime", $config->get("rtime") - 1);
            $config->save();
            $time = $config->get("rtime") + 1;
            if ($time === 10) {
            	
            	$clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
                $clouddata->set("ServerMessage", "Der Server: " . $config->get("Server") . " wird heruntergefahren!");
                $clouddata->set("ServerMessageStatus", true);
                $clouddata->set($config->get("Server"), false);
                $clouddata->save();
            	$this->plugin->getServer()->broadcastMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
            	
            } else if ($time === 5) {
            	
            	$this->plugin->getServer()->broadcastMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
            	
            } else if ($time === 0) {
            	
            	$clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
                $clouddata->set("ServerMessage", "Der Server: " . $config->get("Server") . " wird hochgefahren!");
                $clouddata->set("ServerMessageStatus", true);
                $clouddata->set($config->get("Server"), true);
                $clouddata->save();
                foreach ($all as $player) {
                	
                	$player->transfer("84.200.84.61", 19132);
                	
                }
                
                $config->set("reset", false);
                $config->set("rtime", 10);
                $config->set("state", false);
                $config->save();
                $this->plugin->players = 0;
            	
            }

        }

        foreach ($all as $player) {

            $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            if ($playerfile->get("teleport") === true) {

                $playerfile->set("teleportt", $playerfile->get("teleportt") - 1);
                $playerfile->save();
                $time = $playerfile->get("teleportt") + 1;
                if ($time === 0) {

                    $playerfile->set("teleport", false);
                    $playerfile->set("teleportt", 2);
                    $playerfile->save();
                    $this->plugin->teleportIngame($player);

                }


            }

        }

    }

}