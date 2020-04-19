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
use pocketmine\entity\EffectInstance;
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

class BowFight extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "BowFight" . Color::WHITE . "] ";
	public $arenaname = "";
	public $mode = 0;
	public $players = 0;
	
    public function onEnable() {
    	
	    if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
        
        if(is_dir($this->getDataFolder() . "/maps") !== true) {
        
            mkdir($this->getDataFolder() . "/maps");
            
        }
        
        if (is_dir("/home/EnderCloud/BowFight/players") !== true) {
			
            mkdir("/home/EnderCloud/BowFight/players");
            
        }

        $this->saveDefaultConfig();
        $this->reloadConfig();

        $config = $this->getConfig();
        
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new GameSender($this), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new PlayerSender($this), 10);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new ResetPlayer($this), 5);
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
        if (!is_file("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml")) {
        
            $playerfile = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Kills", 0);
            $playerfile->set("Deaths", 0);
            $playerfile->set("KD", 0);
            $playerfile->set("EP", 9);
            $playerfile->set("TNT", 16);
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
        $perk = Item::get(388, 0, 1);
        $perk->setCustomName(Color::YELLOW . "Perks");
        $player->getInventory()->setItem(4, $perk);
        $player->removeAllEffects();
        $player->setAllowFlight(false);
        $all = $this->getServer()->getOnlinePlayers();
        $bf = new Config("/home/EnderCloud/CustomServer/BowFight.yml", Config::YAML);
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
            
        } else if ($this->players === 16) {
        	
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
    	
    	    case "BowFight":
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
        	
        	    if (file_exists("/home/EnderCloud/BowFight/players/" . $args[0] . ".yml")) {
        	
        	        $pf = new Config("/home/EnderCloud/BowFight/players/" . $args[0] . ".yml", Config::YAML);
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
            	
            	$pf = new Config("/home/EnderCloud/BowFight/players/" . $sender->getName() . ".yml", Config::YAML);
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
        $item = $player->getInventory()->getItemInHand();
        $gf = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        if ($item->getCustomName() === Color::YELLOW . "Perks") {
        	
        	$player->getInventory()->clearAll();
            $mlg = Item::get(289, 0, 1);
            $mlg->setCustomName(Color::BLUE . "MLGTnT");
        	$player->getInventory()->setItem(0, $mlg);
        
        } else if ($item->getCustomName() === Color::BLUE . "MLGTnT") {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            $pf->set("BFKit", "mlg");
            $pf->save();
            $player->getInventory()->clearAll();
            $perk = Item::get(388, 0, 1);
            $perk->setCustomName(Color::YELLOW . "Perks");
            $player->getInventory()->setItem(4, $perk);
            $player->sendMessage($this->prefix . "Du hast erfolgreich das Kit ausgewaehlt");
        
        }
        
        if ($item->getId() === 46) {
        	
        	$event->setCancelled(true);
            $player->getInventory()->removeItem($item);
            $yaw = $player->getYaw();
            $playerfile = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("TNT", 15);
            $playerfile->save();
            if ($yaw < 45 && $yaw > 0 || $yaw < 360 && $yaw > 315) {
            	
            	$player->setMotion(new Vector3(0, 3, 4));
            	
            } else if ($yaw < 135 && $yaw > 45) {
            	
            	$player->setMotion(new Vector3(-4, 3, 0));
            	
            } else if ($yaw < 225 && $yaw > 135) {
            	
            	$player->setMotion(new Vector3(0, 3, -4));
            	
            } elseif($yaw < 315 && $yaw > 225){
            	
                $player->setMotion(new Vector3(4, 3, 0));
               
            }
        	
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
        
        }
        
    }
    
    public function onDeath(PlayerDeathEvent $event) {
    	
    	$player = $event->getEntity();
    	$event->setDeathMessage("");
        $event->setKeepInventory(true);
    
    }
    
    public function onCraft(CraftItemEvent $event) {
    	
    	$event->setCancelled(true);
    	
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
        $block = $event->getBlock();
        $x = $block->getX();
        $y = $block->getY();
        $z = $block->getZ();
        $config = $this->getConfig();
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        foreach($player->getLevel()->getPlayers() as $p) {
        	
                if ($config->get("ingame") === false) {
                	
                    $event->setCancelled();
                    
                } else if ($block->getId() === 35) {
                	
                	if ($block->getDamage() === 0) {
                	
                    	if ($player->getName() === $config->get("player1")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 11, 2));
                        
                        } else if ($player->getName() === $config->get("player2")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 14, 2));
                        
                        } else if ($player->getName() === $config->get("player3")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 4, 2));
                        
                        } else if ($player->getName() === $config->get("player4")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 5, 2));
                        
                        } else if ($player->getName() === $config->get("player5")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 1, 2));
                        
                        } else if ($player->getName() === $config->get("player6")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 10, 2));
                        
                        } else if ($player->getName() === $config->get("player7")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 6, 2));
                        
                        } else if ($player->getName() === $config->get("player8")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 2, 2));
                        
                        } else if ($player->getName() === $config->get("player9")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 7, 2));
                        
                        } else if ($player->getName() === $config->get("player10")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 8, 2));
                        
                        } else if ($player->getName() === $config->get("player11")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 9, 2));
                        
                        } else if ($player->getName() === $config->get("player12")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 12, 2));
                        
                        } else if ($player->getName() === $config->get("player13")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 13, 2));
                        
                        } else if ($player->getName() === $config->get("player14")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 15, 2));
                        
                        } else if ($player->getName() === $config->get("player15")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 10, 3));
                        
                        } else if ($player->getName() === $config->get("player16")) {
                	
                	        $event->setCancelled(true);
                            $player->getInventory()->addItem(Item::get(35, 10, 2));
                        
                        }
                    
                    } else {
                    	
                    	$event->setCancelled(false);
                    	
                    }
                
                } else if ($block->getId() === 18) {
                	
                	$event->setCancelled(false);
                
                } else {
                	
                	$event->setCancelled(true);
                
                }
                
          }
        
    }
    
    public function onMove(PlayerMoveEvent $event) {
    	
        $player = $event->getPlayer();
        $player->setFood(20);
        $player->setHealth(20);
        
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
    
    public function giveKit(Player $player)
    {
    	
    	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        if ($pf->get("BFKit") === "mlg") {
        	
        	$player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
        
            $knock = Enchantment::getEnchantment(12);
            $bowknock = Enchantment::getEnchantment(20);
            $infinity = Enchantment::getEnchantment(22);
            $effy = Enchantment::getEnchantment(15);
        
           $bow = Item::get(Item::BOW);
           $scheere = Item::get(359, 0, 1);
           $arrow = Item::get(262, 0, 1);
           $steak = Item::get(320, 0, 32);
        
           $bow->addEnchantment(new EnchantmentInstance($knock, 2));
           $bow->addEnchantment(new EnchantmentInstance($bowknock, 2));
           $bow->addEnchantment(new EnchantmentInstance($infinity, 1));
        
           $scheere->addEnchantment(new EnchantmentInstance($knock, 2));
           $scheere->addEnchantment(new EnchantmentInstance($effy, 5));
        
           $player->getInventory()->setItem(0, $bow);
           $player->getInventory()->setItem(1, $scheere);
           $player->getInventory()->addItem(Item::get(46, 0, 1));
           $player->getInventory()->addItem(Item::get(368, 0, 1));
           $player->getInventory()->setItem(8, $steak);
           $player->getInventory()->setItem(9, $arrow);
        
           $player->removeAllEffects();
       	$effect = Effect::getEffect(Effect::JUMP);
           $duration = 2333333;
           $amplification = 2;
           $visible = false;
           $instance = new EffectInstance($effect, $duration, $amplification, $visible);
		   $player->addEffect($instance);
        	
        } else {
        	
        	$player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
        
            $knock = Enchantment::getEnchantment(12);
            $bowknock = Enchantment::getEnchantment(20);
            $infinity = Enchantment::getEnchantment(22);
            $effy = Enchantment::getEnchantment(15);
        
           $bow = Item::get(Item::BOW);
           $scheere = Item::get(359, 0, 1);
           $arrow = Item::get(262, 0, 1);
           $steak = Item::get(320, 0, 32);
        
           $bow->addEnchantment(new EnchantmentInstance($knock, 2));
           $bow->addEnchantment(new EnchantmentInstance($bowknock, 2));
           $bow->addEnchantment(new EnchantmentInstance($infinity, 1));
        
           $scheere->addEnchantment(new EnchantmentInstance($knock, 2));
           $scheere->addEnchantment(new EnchantmentInstance($effy, 5));
        
           $player->getInventory()->setItem(0, $bow);
           $player->getInventory()->setItem(1, $scheere);
           $player->getInventory()->addItem(Item::get(368, 0, 1));
           $player->getInventory()->setItem(8, $steak);
           $player->getInventory()->setItem(9, $arrow);
        
           $player->removeAllEffects();
       	$effect = Effect::getEffect(Effect::JUMP);
           $duration = 2333333;
           $amplification = 2;
           $visible = false;
           $instance = new EffectInstance($effect, $duration, $amplification, $visible);
		   $player->addEffect($instance);
        	
        }

    }
    
    public function spawn(Player $player) {
    	
    	$pos = $player->getPosition();
        $player->setSpawn($pos);
        
    }
    
    public function teleportIngame(Player $player) {
    	
    	$config = $this->getConfig();
        if (!$this->getServer()->getLevelByName($config->get("Arena")) instanceof Level) {
        	
            $this->getServer()->loadLevel($config->get("Arena"));
            
        }
        
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
        $level->setTime(0);
        $level->stopTime();
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
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
        
        }
        
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

            if ($this->plugin->players < 8) {

                foreach ($all as $player) {

                    $player->sendPopup(Color::GRAY . ">> Warten auf weitere Spieler <<");

                }

            }

            if ($this->plugin->players >= 8) {

                $config->set("time", $config->get("time") - 1);
                $config->save();
                $time = $config->get("time") + 1;
                foreach ($all as $player) {
                	
                	$player->sendPopup(
                       Color::YELLOW . "Spieler: " . Color::WHITE . $this->plugin->players . Color::YELLOW . "/" . Color::WHITE . "16" . "\n" .
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

                    $config->set("ingame", true);
                    $config->set("state", true);
                    $config->set("Leben1", 6);
                    $config->set("Leben2", 6);
                    $config->set("Leben3", 6);
                    $config->set("Leben4", 6);
                    $config->set("Leben5", 6);
                    $config->set("Leben6", 6);
                    $config->set("Leben7", 6);
                    $config->set("Leben8", 6);
                    $config->set("Leben9", 6);
                    $config->set("Leben10", 6);
                    $config->set("Leben11", 6);
                    $config->set("Leben12", 6);
                    $config->set("Leben13", 6);
                    $config->set("Leben14", 6);
                    $config->set("Leben15", 6);
                    $config->set("Leben16", 6);
                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . "60" . Color::WHITE . " Minuten!");
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
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 3600);
                    $config->set("block1", false);
                    $config->set("block2", false);
                    $config->set("player1", "");
                    $config->set("player2", "");
                    $config->set("Leben1", 6);
                    $config->set("Leben2", 6);
                    $config->set("Leben3", 6);
                    $config->set("Leben4", 6);
                    $config->set("Leben5", 6);
                    $config->set("Leben6", 6);
                    $config->set("Leben7", 6);
                    $config->set("Leben8", 6);
                    $config->set("Leben9", 6);
                    $config->set("Leben10", 6);
                    $config->set("Leben11", 6);
                    $config->set("Leben12", 6);
                    $config->set("Leben13", 6);
                    $config->set("Leben14", 6);
                    $config->set("Leben15", 6);
                    $config->set("Leben16", 6);
                    $config->save();
                    $this->plugin->players = 0;
                    $levelname = $config->get("Arena");
                    $lev = $this->plugin->getServer()->getLevelByName($levelname);
                    $this->plugin->getServer()->unloadLevel($lev);
                    $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->getServer()->loadLevel($levelname);
                    $lev->setTime(0);
                    $lev->stopTime();

                }

            } elseif ($this->plugin->players >= 2) {

                $config->set("playtime", $config->get("playtime") - 1);
                $config->save();
                $time = $config->get("playtime") + 1;
                foreach ($all as $player) {
                	
                   $playerfile = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                   if ($playerfile->get("TNT") === 15) {
                   	
                   	$playerfile->set("TNT", $playerfile->get("TNT") - 1);
                       $playerfile->save();
                   	
                   } else if ($playerfile->get("TNT") < 15) {
                   	
                   	$playerfile->set("TNT", $playerfile->get("TNT") - 1);
                       $playerfile->save();
                   	
                   } else if ($playerfile->get("TNT") === 0) {
                   	
                   	$playerfile->set("TNT", 16);
                       $playerfile->save();
                       $player->getInventory()->addItem(Item::get(46, 0, 1));
                   	
                   }
                   
                   if ($player->getName() === $config->get("player1")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben1") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player2")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben2") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player3")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben3") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player4")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben4") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player5")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben5") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player6")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben6") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player7")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben7") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player8")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben8") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player9")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben9") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player10")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben10") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player11")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben11") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player12")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben12") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player13")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben13") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player14")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben14") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player15")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben15") . " <3");
                   	
                   } else if ($player->getName() === $config->get("player16")) {
                   	
                   	$player->sendPopup(Color::RED . $config->get("Leben16") . " <3");
                   	
                   }
                   
                    if ($time === 60) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . $time / 60 . Color::WHITE . " Minuten!");

                    } else if ($time === 1 || $time === 2 || $time === 3 || $time === 4 || $time === 5 || $time === 15 || $time === 30) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

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
                        $config->set("block1", false);
                        $config->set("block2", false);
                        $config->set("player1", "");
                        $config->set("player2", "");
                        $config->set("Leben1", 6);
                    $config->set("Leben2", 6);
                    $config->set("Leben3", 6);
                    $config->set("Leben4", 6);
                    $config->set("Leben5", 6);
                    $config->set("Leben6", 6);
                    $config->set("Leben7", 6);
                    $config->set("Leben8", 6);
                    $config->set("Leben9", 6);
                    $config->set("Leben10", 6);
                    $config->set("Leben11", 6);
                    $config->set("Leben12", 6);
                    $config->set("Leben13", 6);
                    $config->set("Leben14", 6);
                    $config->set("Leben15", 6);
                    $config->set("Leben16", 6);
                        $config->save();
                        $this->plugin->players = 0;
                        $levelname = $config->get("Arena");
                        $lev = $this->plugin->getServer()->getLevelByName($levelname);
                        $this->plugin->getServer()->unloadLevel($lev);
                        $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                        $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                        $this->plugin->getServer()->loadLevel($levelname);
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

class ResetPlayer extends PluginTask {
	
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
        if ($config->get("ingame") === true) {
        	
        	foreach ($all as $player) {
        	
        	    if ($player->getName() === $config->get("player1")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben1") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben1", $config->get("Leben1")-1);
                            $config->save();
                            $p2 = $config->get("player2");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player2")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben2") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben2", $config->get("Leben2")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player3")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben3") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben3", $config->get("Leben3")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player4")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben4") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben4", $config->get("Leben4")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player5")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben5") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben5", $config->get("Leben5")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player6")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben6") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben6", $config->get("Leben6")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player7")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben7") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben7", $config->get("Leben7")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player8")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben8") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben8", $config->get("Leben8")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player9")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben9") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben9", $config->get("Leben9")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player10")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben10") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben10", $config->get("Leben10")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player11")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben11") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben11", $config->get("Leben11")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player12")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben12") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben12", $config->get("Leben12")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player13")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben13") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben13", $config->get("Leben13")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player14")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben14") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben14", $config->get("Leben14")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player15")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben15") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben15", $config->get("Leben15")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
                if ($player->getName() === $config->get("player16")) {
        	
        	        $y = $player->getY();
                    if ($y <= 0) {
                    	
                    	if ($config->get("Leben16") === 0) {
                    	
                    	    $this->plugin->players--;
                    
                        } else {
                        	
                        	$player->setHealth(20);
                            $player->setFood(20);
                            $this->plugin->teleportIngame($player);
                            $this->plugin->spawn($player);
                            $this->plugin->giveKit($player);
                        	$config->set("Leben16", $config->get("Leben16")-1);
                            $config->save();
                            $p2 = $config->get("player1");
                        	$p = $this->plugin->getServer()->getPlayerExact($p2);
                        
                            $pf = new Config("/home/EnderCloud/BowFight/players/" . $player->getName() . ".yml", Config::YAML);
                            $pf->set("Deaths", $pf->get("Deaths")+1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                }
                
            }
            
        }
    	
    }
	
}