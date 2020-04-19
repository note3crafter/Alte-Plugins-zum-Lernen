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

class UHCMeetup extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "UHC" . Color::GOLD . "Meetup" . Color::WHITE . "] ";
	public $arenaname = "";
	public $mode = 0;
	public $players = 0;
	
    public function onEnable() {
    	
	    if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
        
        if (is_dir("/home/EnderCloud/UHCMeetup/players") !== true) {
			
            mkdir("/home/EnderCloud/UHCMeetup/players");
            
        }
    	
        if(is_dir($this->getDataFolder() . "/maps") !== true) {
        
            mkdir($this->getDataFolder() . "/maps");
            
        }

        $this->saveDefaultConfig();
        $this->reloadConfig();

        $config = $this->getConfig();
        
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new GameSender($this), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new PlayerSender($this), 10);
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
    
    public function onLogin(PlayerLoginEvent $event) {
    
        $player = $event->getPlayer();
        if (!is_file("/home/EnderCloud/UHCMeetup/players/" . $player->getName() . ".yml")) {
        
            $playerfile = new Config("/home/EnderCloud/UHCMeetup/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Kills", 0);
            $playerfile->set("Deaths", 0);
            $playerfile->set("KD", 0);
            $playerfile->set("Wins", 0);
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
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();
        $player->setAllowFlight(false);
        $all = $this->getServer()->getOnlinePlayers();
        if ($config->get("ingame") === true) {
        	
        	$event->setJoinMessage("");
        	$player->getInventory()->clearAll();
            $player->setGamemode(3);
            $levl = $this->getServer()->getLevelByName("world");
            $player->teleport(new Position($config->get("s1x"), $config->get("s1y")+1, $config->get("s1z"), $levl));
        
        } else {
        	
        	$event->setJoinMessage(Color::GRAY . "> " . Color::DARK_GRAY . "> " . $player->getName() . Color::GRAY . " hat den Server Betreten!");
        	
        if ($this->players === 0) {
        	
        	$this->players++;
        	$config->set("ingame", false);
            $config->set("time", 60);
            $config->set("playtime", 3600);
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
            
        } else if ($this->players > 16) {
        	
        	$player->transfer("84.200.84.61", 19132);
            
        }
        
        }

    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$player = $event->getPlayer();
        $config = $this->getConfig();
        $event->setQuitMessage(Color::GRAY . "< " . Color::DARK_GRAY . "< " . $player->getDisplayName() . Color::GRAY . " hat den Server verlassen!");
        if ($config->get("state") === false) {
        	
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
                $config->set("player16", "");
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
                $config->set("player16", "");
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
                $config->set("player16", "");
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
                $config->set("player16", "");
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
                $config->set("player16", "");
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
                $config->set("player16", "");
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
                
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player11", $p12);
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player12", $p13);
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player13", $p14);
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player14", $p15);
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player15", $p16);
                $config->set("player16", "");
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
                
                $config->set("player16", "");
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
        
            }
            
            }
        	
        }
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "UHCMeetup":
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
        	
        	    if (file_exists("/home/EnderCloud/UHCMeetup/players/" . $args[0] . ".yml")) {
        	
        	        $pf = new Config("/home/EnderCloud/UHCMeetup/players/" . $args[0] . ".yml", Config::YAML);
                    $deaths = $pf->get("Deaths");
                    $kills = $pf->get("Kills");
                    $wins = $pf->get("Wins");
                    if ($deaths === 0) {
              	
                  	$kd = $kills;
              
                   } else {
              	
                  	$kd = $kills/$deaths;
              
                   }
              
                  $sender->sendMessage($this->prefix . "Stats:");
                  $sender->sendMessage(Color::GOLD . "Deine Kills: " . Color::WHITE . $kills);
                  $sender->sendMessage(Color::GOLD . "Deine Deaths: " . Color::WHITE . $deaths);
                  $sender->sendMessage(Color::GOLD . "Deine K/D: " . Color::WHITE . $kd);
                  $sender->sendMessage(Color::GOLD . "Deine Wins: " . Color::WHITE . $wins);
              
                } else {
                	
                	$sender->sendMessage($this->prefix . Color::RED . "Diesen Spieler gibt es nicht");
                	
                }
                
            } else {
            	
            	$pf = new Config("/home/EnderCloud/UHCMeetup/players/" . $sender->getName() . ".yml", Config::YAML);
                $deaths = $pf->get("Deaths");
                $kills = $pf->get("Kills");
                $wins = $pf->get("Wins");
                if ($deaths === 0) {
              	
              	$kd = $kills;
              
              } else {
              	
              	$kd = $kills/$deaths;
              
              }
              
              $sender->sendMessage($this->prefix . "Stats:");
              $sender->sendMessage(Color::GOLD . "Deine Kills: " . Color::WHITE . $kills);
              $sender->sendMessage(Color::GOLD . "Deine Deaths: " . Color::WHITE . $deaths);
              $sender->sendMessage(Color::GOLD . "Deine K/D: " . Color::WHITE . $kd);
              $sender->sendMessage(Color::GOLD . "Deine Wins: " . Color::WHITE . $wins);
            	
            }
        	
        }
        
        return true;
        
    }
    
    public function onInteract(PlayerInteractEvent $event) {
    	
    	$player = $event->getPlayer();
        $player->setFood(20);
        $block = $event->getBlock();
        $tile = $player->getLevel()->getTile($block);
        $config = $this->getConfig();
        $item = $player->getInventory()->getItemInHand();
        $gf = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        if ($this->mode === 1 && $player->isOp()) {
        	
        	$config->set("s1x", $block->getX() + 0.5);
            $config->set("s1y", $block->getY() + 1);
            $config->set("s1z", $block->getZ() + 0.5);
            $config->save();
            
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
        if ($config->get("ingame") === false) {
        	
        	$event->setCancelled(true);
        
        } else if ($config->get("schutz") === true) {
        	
        	$event->setCancelled(true);
        
        } else {
        	
        	if ($event instanceof EntityDamageByEntityEvent) {
        	
        	    $damager = $event->getDamager();
                if ($damager instanceof Player) {
                	
                	$pf = new Config("/home/EnderCloud/UHCMeetup/players/" . $player->getName() . ".yml", Config::YAML);
                    $pf->set("Damager", $damager->getName());
                    $pf->save();
                	
                }
                
            }
        	
        }
        
    }
    
    public function onPlace(BlockPlaceEvent $event) {
    
        $player = $event->getPlayer();
        $config = $this->getConfig();
        if ($config->get("ingame") === false) {
        	
        	$event->setCancelled();
        
        }
        
    }
    
    public function onBreak(BlockBreakEvent $event) {
    
        $player = $event->getPlayer();
        $config = $this->getConfig();
        if ($config->get("ingame") === false) {
        	
        	$event->setCancelled();
        
        }
        
    }
    
    public function onDeath(PlayerDeathEvent $event) {
    	
    	$player = $event->getEntity();
        $player->setGamemode(3);
        if ($player instanceof Player) {
        	
        	$cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
            	
            	$killer = $cause->getDamager();
                if ($killer instanceof Player) {
                	
                	$event->setDeathMessage($this->prefix . $player->getName() . Color::GOLD . " wurde von " . Color::WHITE . $killer->getName() . Color::GOLD . " getoetet!");
                    $killer->sendMessage(Color::WHITE . "[" . Color::DARK_PURPLE . "+" . Color::WHITE . "] 50 Coins");
                    $pc = new Config("/home/EnderCloud/eCoins/" . $killer->getName() . ".yml", Config::YAML);
                    $pc->set("coins", $pc->get("coins")+50);
                    $pc->save();
                    $pf = new Config("/home/EnderCloud/UHCMeetup/players/" . $player->getName() . ".yml", Config::YAML);
                    $pff = new Config("/home/EnderCloud/UHCMeetup/players/" . $pf->get("Damager") . ".yml", Config::YAML);
                    $pff->set("Kills", $pff->get("Kills")+1);
                    $pff->save();
                    $pf->set("Deaths", $pf->get("Deaths")+1);
                    $pf->save();
                    $this->delPlayer($player);
                    $this->players--;
                
                } else {
                	
                	$pf = new Config("/home/EnderCloud/UHCMeetup/players/" . $player->getName() . ".yml", Config::YAML);
                    $event->setDeathMessage($this->prefix . $player->getName() . Color::GOLD . " wurde von " . Color::WHITE . $pf->get("Damager") . Color::GOLD . " getoetet!");
                    $pff = new Config("/home/EnderCloud/UHCMeetup/players/" . $pf->get("Damager") . ".yml", Config::YAML);
                    $pff->set("Kills", $pff->get("Kills")+1);
                    $pff->save();
                    $pf->set("Deaths", $pf->get("Deaths")+1);
                    $pf->save();
                    $this->delPlayer($player);
                    $this->players--;
                
                }
                
            }
            
        }
    
    }
    
    public function onRespawn(PlayerRespawnEvent $event) {
    	
    	$player = $event->getPlayer();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        $playerfile->set("teleportt", 2);
        $playerfile->set("teleport", true);
        $playerfile->save();
        
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
        	
        }
    	
    }
    
    public function giveKit(Player $player) {
    	
    	$player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $sword = Item::get(267, 0, 1);
        $helm = Item::get(310, 0, 1);
        $chest = Item::get(311, 0, 1);
        $hose = Item::get(312, 0, 1);
        $boots = Item::get(313, 0, 1);
        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem(Item::get(261, 0, 1));
        $player->getInventory()->addItem(Item::get(4, 0, 64));
        $player->getInventory()->addItem(Item::get(4, 0, 64));
        $player->getInventory()->addItem(Item::get(322, 0, 15));
        $player->getInventory()->addItem(Item::get(320, 0, 16));
        $player->getInventory()->addItem(Item::get(325, 8, 1));
        $player->getInventory()->addItem(Item::get(325, 10, 1));
        $player->getInventory()->addItem(Item::get(278, 0, 1));
        $player->getInventory()->addItem(Item::get(262, 0, 16));
        $player->getArmorInventory()->setHelmet($helm);
        $player->getArmorInventory()->setChestplate($chest);
        $player->getArmorInventory()->setLeggings($hose);
        $player->getArmorInventory()->setBoots($boots);
        
    }
    
    public function teleportIngame(Player $player) {
    	
    	$config = $this->getConfig();
    	$levl = $this->getServer()->getLevelByName("world");
        if (!$this->getServer()->getLevelByName("world") instanceof Level) {
        	
            $this->getServer()->loadLevel("world");
            
        }
        
        $player->teleport(new Position($config->get("s1x"), $config->get("s1y")+1, $config->get("s1z"), $levl));
        
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
                $config->set("state", false);
                $config->set("reset", false);
                $config->set("rtime", 10);
                $config->set("time", 60);
                $config->set("playtime", 3600);
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
                	
                	$player->sendPopup(Color::YELLOW . "Spieler: " . Color::WHITE . $this->plugin->players . Color::YELLOW . "/" . Color::WHITE . "16");
                	
                }
                
                if ($time % 5 === 0 && $time > 0) {

                    foreach ($all as $player) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match startet in " . Color::GOLD . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 15) {
                
                	$config->set("state", true);
                    $config->save();
                	foreach ($all as $player) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match startet in " . Color::GOLD . $time . Color::WHITE . " Sekunden!");

                    }
                	
                } else if ($time === 4 || $time === 3 || $time === 2 || $time === 1) {

                    foreach ($all as $player) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match startet in " . Color::GOLD . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 0) {

                    $config->set("ingame", true);
                    $config->set("schutz", true);
                    $config->set("state", true);
                    foreach ($all as $player) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match endet in " . Color::GOLD . "60" . Color::WHITE . " Minuten!");
                        $player->setHealth(20);
                        $player->setFood(20);
                        $this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);

                    }

                    $config->save();

                }

            }

        } else if ($config->get("ingame") === true) {

            $all = $this->plugin->getServer()->getOnlinePlayers();
            if ($this->plugin->players <= 1) {
            	
                foreach ($all as $player) {
                	
                	if ($player->getGamemode() === 0) {
                	
                	    $config->set("Win", $player->getName());
                        $config->save();
                        $pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                        $pc->set("coins", $pc->get("coins")+100);
                        $pc->save();
                        $pf = new Config("/home/EnderCloud/UHCMeetup/players/" . $player->getName() . ".yml", Config::YAML);
                        $pf->set("Wins", $pf->get("Wins") + 1);
                        $pf->save();
                	    
                    }
                    
                    $player->addTitle(Color::GRAY . $config->get("Win"), Color::GREEN . "hat Gewonnen", 20, 40, 20);
                    
                    $player->getInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->setGamemode(0);
                    $player->removeAllEffects();
                    $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                    $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                    $player->teleport($spawn, 0, 0);
                    $config->set("ingame", false);
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 3600);
                    $config->save();
                    $this->plugin->players = 0;
                    $levelname = "world";
                    $lev = $this->plugin->getServer()->getLevelByName($levelname);
                    $this->plugin->getServer()->unloadLevel($lev);
                    $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->getServer()->loadLevel($levelname);

                }

            } elseif ($this->plugin->players >= 2) {

                $config->set("playtime", $config->get("playtime") - 1);
                $config->save();
                $time = $config->get("playtime") + 1;
                foreach ($all as $player) {
                	
                	$player->sendPopup(Color::YELLOW . "Spieler: " . Color::WHITE . $this->plugin->players . Color::YELLOW . "/" . Color::WHITE . "16");
                	
                   if ($time === 3585) {
                    	
                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Die " . Color::GOLD . "Schutzzeit" . Color::WHITE . " ist nun Vorueber!");
                    	$config->set("schutz", false);
                        $config->save();
                    
                    } else if ($time === 3300) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 3000) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 2700) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 2400) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 2100) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 1800) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 1500) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 1200) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 900) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 600) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 300) {
                    	
                    	$this->plugin->teleportIngame($player);
                    
                    } else if ($time === 60) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match endet in " . Color::GOLD . $time . Color::WHITE . " Sekunden!");

                    } else if ($time === 1 || $time === 2 || $time === 3 || $time === 4 || $time === 5 || $time === 15 || $time === 30) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match endet in " . Color::GOLD . $time . Color::WHITE . " Sekunden!");

                    } else if ($time === 0) {

                        $player->getInventory()->clearAll();
                        $player->setHealth(20);
                        $player->setFood(20);
                        $player->removeAllEffects();
                        $player->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast das Match gewonnen!");
                        $this->plugin->getServer()->broadcastMessage($this->plugin->prefix . $player->getName() . Color::GREEN . " hat das Match in " . Color::WHITE . $config->get("Arena") . Color::GREEN . " Gewonnen!");
                        $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->teleport($spawn, 0, 0);
                        $config->set("ingame", false);
                        $config->set("reset", true);
                        $config->set("rtime", 10);
                        $config->set("time", 60);
                        $config->set("playtime", 3600);
                        $config->save();
                        $this->plugin->players = 0;
                        $levelname = "world";
                        $lev = $this->plugin->getServer()->getLevelByName($levelname);
                        $this->plugin->getServer()->unloadLevel($lev);
                        $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                        $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                        $this->plugin->getServer()->loadLevel($levelname);

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
                	
                	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                	if ($pf->get("Lobby") === 1) {
        	
                       $player->transfer("84.200.84.61", 19191);
        	
                    } else if ($pf->get("Lobby") === 2) {
        	
                  	$player->transfer("84.200.84.61", 19192);
        	
                    } else if ($pf->get("Lobby") === 3) {
        	
                  	$player->transfer("84.200.84.61", 19193);
        	
                    } else if ($pf->get("Lobby") === 4) {
        	  
                 	$player->transfer("84.200.84.61", 19194);
        	
                    } else if ($pf->get("Lobby") === 5) {
        	
                 	$player->transfer("84.200.84.61", 19195);
        	
                    } else if ($pf->get("Lobby") === "VIP") {
        	
                 	$player->transfer("84.200.84.61", 19196);
        	
                   } else {
        	
                 	$pf->set("Lobby", 5);
                     $pf->save();
                     $player->transfer("84.200.84.61", 19195);
        	
                 }
                 
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