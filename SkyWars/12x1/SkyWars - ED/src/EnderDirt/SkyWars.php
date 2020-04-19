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

class SkyWars extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "SkyWars" . Color::WHITE . "] ";
	public $arenaname = "";
	public $mode = 0;
	public $players = 0;

    public function onEnable() {
    	
	    if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
        
        if (is_dir($this->getDataFolder() . "/players") !== true) {
			
            mkdir($this->getDataFolder() . "/players");
            
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
        $kit = Item::get(505, 0, 1);
        $set = Item::get(395, 0, 1);
        $kit->setCustomName(Color::DARK_PURPLE . "Kits");
        $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
        $player->getInventory()->setItem(0, $kit);
        $player->getInventory()->setItem(4, $set);
        $player->removeAllEffects();
        $player->setAllowFlight(false);
        $all = $this->getServer()->getOnlinePlayers();
        if ($config->get("ingame") === true) {
        	
        	$event->setJoinMessage("");
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->setGamemode(3);
            $level = $this->getServer()->getLevelByName($config->get("Arena"));
            $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
            $player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
        
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
            
        } else if ($this->players > 11) {
        	
        	$player->transfer("84.200.84.61", 19132);
            
        }
        
        }
        
    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$player = $event->getPlayer();
        $event->setQuitMessage(Color::GRAY . "< " . Color::DARK_GRAY . "< " . $player->getName() . Color::GRAY . " hat den Server verlassen!");
        $config = $this->getConfig();
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
                $config->set("player12", "");
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
                $config->set("player12", "");
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
                
                $config->set("player3", $p4);
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player7", $p8);
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player8", $p9);
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player9", $p10);
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player10", $p11);
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player11", $p12);
                $config->set("player12", "");
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
                
                $config->set("player12", "");
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
        
            }
            
            }
        	
        }
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "SkyWars":
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
                    
                } else if (strtolower($args[0]) === "spawn") {
            	
            	    if ($sender->isOp()) {
            	
            	        $sender->sendMessage($this->prefix . "Die " . Color::GOLD . "Lobby " . Color::WHITE . "wurde auf deine Position gesetzt!");
                        $config = $this->getConfig();
                        $config->set("ingame", false);
                        $config->set("state", false);
                        $config->set("schutz", false);
                        $config->set("time", 60);
                        $config->set("playtime", 3600);
                        $config->set("players", 0);
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
                        $config->save();
                        
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
            	
            	$sender->sendMessage(Color::RED . "Du hast keine Berechtigung fÃÂ¼r diesen befehl!");
            
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
        $it = $event->getItem();
        $x = $block->getX();
        $y = $block->getY();
        $z = $block->getZ();
        if ($block->getId() === Block::CHEST) {
        	
        	$i = mt_rand(1, 20);
            if ($i === 1) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(46, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 2) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(30, 0, 10));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 3) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(261, 0, 1));
                $player->getInventory()->addItem(Item::get(262, 0, 32));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 4) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(272, 0, 1));
                $player->getInventory()->addItem(Item::get(307, 0, 1));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 5) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(302, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(43, 4, 42));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 6) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(303, 0, 1));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 7) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(304, 0, 1));
                $player->getInventory()->addItem(Item::get(257, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 8) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(305, 0, 1));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getInventory()->addItem(Item::get(279, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 9) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(298, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(43, 4, 42));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 10) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(299, 0, 1));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 11) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(300, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 12) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(301, 0, 1));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getInventory()->addItem(Item::get(393, 0, 8));
                $player->getInventory()->addItem(Item::get(279, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 13) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(306, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(43, 4, 42));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 14) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(307, 0, 1));
                $player->getInventory()->addItem(Item::get(257, 0, 1));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getInventory()->addItem(Item::get(46, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 15) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(308, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 16) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(309, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 17) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(310, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(393, 0, 8));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 18) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(311, 0, 1));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getInventory()->addItem(Item::get(43, 4, 42));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 19) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(312, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(279, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 20) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(313, 0, 1));
                $player->getInventory()->addItem(Item::get(320, 0, 10));
                $player->getInventory()->addItem(Item::get(5, 0, 32));
                $player->getInventory()->addItem(Item::get(393, 0, 8));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 2) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(314, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(279, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 3) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(315, 0, 1));
                $player->getInventory()->addItem(Item::get(257, 0, 1));
                $player->getInventory()->addItem(Item::get(43, 4, 42));
                $player->getInventory()->addItem(Item::get(46, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 4) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(316, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(393, 0, 8));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 5) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(317, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 6) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 7) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(5, 0, 32));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 8) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 9) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 10) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(393, 0, 8));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 11) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(5, 0, 32));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 12) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(272, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(257, 0, 1));
                $player->getInventory()->addItem(Item::get(43, 4, 42));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 13) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(278, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 14) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(267, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(5, 0, 32));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 15) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(283, 0, 1));
                $player->getInventory()->addItem(Item::get(279, 0, 1));
                $player->getInventory()->addItem(Item::get(354, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 16) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(322, 0, 1));
                $player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(354, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 17) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(320, 0, 15));
                $player->getInventory()->addItem(Item::get(257, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 18) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 19) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(354, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 20) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(5, 0, 32));
                $player->getInventory()->addItem(Item::get(354, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 1) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(1, 0, 64));
                $player->getInventory()->addItem(Item::get(257, 0, 1));
                $player->getInventory()->addItem(Item::get(43, 4, 42));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            }
        	
        }
        
        if ($item->getId() === 282) {
        	
        	$player->getInventory()->removeItem($it);
            $player->setHealth($player->getHealth() + 5);
            $player->setFood(20);
        	
        }
        
        if ($item->getId() === 46) {
        	
        	$event->setCancelled(true);
            $player->getInventory()->removeItem($it);
            $yaw = $player->getYaw();
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
        
        if ($item->getCustomName() === Color::DARK_PURPLE . "Einstellungen") {
        	
        	if ($gf->get("NickP") === true) {
        	
        	$player->getInventory()->clearAll();
            $skytown = Item::get(395, 0, 1);
            $western = Item::get(395, 0, 1);
            $skytown->setCustomName(Color::AQUA . "SkyTown");
            $western->setCustomName(Color::AQUA . "Western");
            $player->getInventory()->setItem(3, $skytown);
            $player->getInventory()->setItem(5, $western);
            
            }
            
        } else if ($item->getCustomName() === Color::AQUA . "SkyTown") {
        	
        	$config->set("Arena", "SkyTown");
            $config->save();
            $player->sendMessage(Color::WHITE . "Die Map " . Color::GOLD . "SkyTown" . Color::WHITE . " wurde erfolgreich ausgesucht!");
            $player->getInventory()->clearAll();
            $kit = Item::get(505, 0, 1);
            $set = Item::get(395, 0, 1);
            $kit->setCustomName(Color::DARK_PURPLE . "Kits");
            $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
            $player->getInventory()->setItem(0, $kit);
            $player->getInventory()->setItem(4, $set);
        	
        } else if ($item->getCustomName() === Color::AQUA . "Western") {
        	
        	$config->set("Arena", "Western");
            $config->save();
            $player->sendMessage(Color::WHITE . "Die Map " . Color::GOLD . "Western" . Color::WHITE . " wurde erfolgreich ausgesucht!");
            $player->getInventory()->clearAll();
            $kit = Item::get(505, 0, 1);
            $set = Item::get(395, 0, 1);
            $kit->setCustomName(Color::DARK_PURPLE . "Kits");
            $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
            $player->getInventory()->setItem(0, $kit);
            $player->getInventory()->setItem(4, $set);
        	
        } else if ($item->getCustomName() === Color::DARK_PURPLE . "Kits") {
        	
        	$player->getInventory()->clearAll();
            $sm = Item::get(505, 0, 1);
            $soup = Item::get(505, 0, 1);
            $assasine = Item::get(505, 0, 1);
            $maurer = Item::get(505, 0, 1);
            $archer = Item::get(505, 0, 1);
            $tank = Item::get(505, 0, 1);
            $sm->setCustomName(Color::YELLOW . "Sprengmeister");
            $soup->setCustomName(Color::YELLOW . "Souper");
            $assasine->setCustomName(Color::YELLOW . "Assassine");
            $maurer->setCustomName(Color::YELLOW . "Maurer");
            $archer->setCustomName(Color::YELLOW . "Archer");
            $tank->setCustomName(Color::YELLOW . "Tank");
            $player->getInventory()->setItem(0, $sm);
            $player->getInventory()->setItem(1, $soup);
            $player->getInventory()->setItem(2, $assasine);
            $player->getInventory()->setItem(3, $maurer);
            $player->getInventory()->setItem(4, $archer);
            $player->getInventory()->setItem(5, $tank);
        	
        } else if ($item->getCustomName() === Color::YELLOW . "Sprengmeister") {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
             if ($pf->get("VIP") === true) {
             	
             	$pf->set("MLGKit", "SprengMeister");
                 $pf->save();
                 $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "SprengMeister" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                 $player->getInventory()->clearAll();
                 $kit = Item::get(505, 0, 1);
                 $set = Item::get(395, 0, 1);
                 $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                 $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                 $player->getInventory()->setItem(0, $kit);
                 $player->getInventory()->setItem(4, $set);
             	
             } else {
             	
             	if ($pf->get("SprengMeister") === true) {
             	
             	    $pf->set("MLGKit", "SprengMeister");
                     $pf->save();
                     $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "SprengMeister" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                     $player->getInventory()->clearAll();
                     $kit = Item::get(505, 0, 1);
                     $set = Item::get(395, 0, 1);
                     $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                     $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                     $player->getInventory()->setItem(0, $kit);
                     $player->getInventory()->setItem(4, $set);
                     
                 } else {
                 	
                 	$pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                     if ($pc->get("coins") >= 1500) {
                        	
                         $pf->set("SprengMeister", true);
                         $pf->save();
                         $pc->set("coins", $pc->get("coins")-1500);
                         $pc->save();
                         $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "SprengMeister" . Color::WHITE . " wurde erfolgreich gekauft");
                         $player->getInventory()->clearAll();
                         $kit = Item::get(505, 0, 1);
                         $set = Item::get(395, 0, 1);
                         $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                         $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                         $player->getInventory()->setItem(0, $kit);
                         $player->getInventory()->setItem(4, $set);
                            
                     } else {
                        	
                     	$player->sendMessage(Color::RED . "Du brauchst 1500 Coins");
                        	
                     }
                 	
                 }
             	
             }
        	
        } else if ($item->getCustomName() === Color::YELLOW . "Souper") {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
             if ($pf->get("VIP") === true) {
             	
             	$pf->set("MLGKit", "Souper");
                 $pf->save();
                 $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Souper" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                 $player->getInventory()->clearAll();
                 $kit = Item::get(505, 0, 1);
                 $set = Item::get(395, 0, 1);
                 $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                 $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                 $player->getInventory()->setItem(0, $kit);
                 $player->getInventory()->setItem(4, $set);
             	
             } else {
             	
             	if ($pf->get("Souper") === true) {
             	
             	    $pf->set("MLGKit", "Souper");
                     $pf->save();
                     $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Souper" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                     $player->getInventory()->clearAll();
                     $kit = Item::get(505, 0, 1);
                     $set = Item::get(395, 0, 1);
                     $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                     $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                     $player->getInventory()->setItem(0, $kit);
                     $player->getInventory()->setItem(4, $set);
                     
                 } else {
                 	
                 	$pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                     if ($pc->get("coins") >= 2000) {
                        	
                         $pf->set("Souper", true);
                         $pf->save();
                         $pc->set("coins", $pc->get("coins")-2000);
                         $pc->save();
                         $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Souper" . Color::WHITE . " wurde erfolgreich gekauft");
                         $player->getInventory()->clearAll();
                         $kit = Item::get(505, 0, 1);
                         $set = Item::get(395, 0, 1);
                         $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                         $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                         $player->getInventory()->setItem(0, $kit);
                         $player->getInventory()->setItem(4, $set);
                            
                     } else {
                        	
                     	$player->sendMessage(Color::RED . "Du brauchst 2000 Coins");
                        	
                     }
                 	
                 }
             	
             }
        	
        } else if ($item->getCustomName() === Color::YELLOW . "Assassine") {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
             if ($pf->get("VIP") === true) {
             	
             	$pf->set("MLGKit", "Assassine");
                 $pf->save();
                 $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Assassine" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                 $player->getInventory()->clearAll();
                 $kit = Item::get(505, 0, 1);
                 $set = Item::get(395, 0, 1);
                 $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                 $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                 $player->getInventory()->setItem(0, $kit);
                 $player->getInventory()->setItem(4, $set);
             	
             } else {
             	
             	if ($pf->get("Assassine") === true) {
             	
             	    $pf->set("MLGKit", "Assassine");
                     $pf->save();
                     $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Assassine" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                     $player->getInventory()->clearAll();
                     $kit = Item::get(505, 0, 1);
                     $set = Item::get(395, 0, 1);
                     $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                     $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                     $player->getInventory()->setItem(0, $kit);
                     $player->getInventory()->setItem(4, $set);
                     
                 } else {
                 	
                 	$pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                     if ($pc->get("coins") >= 1000) {
                        	
                         $pf->set("Assassine", true);
                         $pf->save();
                         $pc->set("coins", $pc->get("coins")-1000);
                         $pc->save();
                         $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Assassine" . Color::WHITE . " wurde erfolgreich gekauft");
                         $player->getInventory()->clearAll();
                         $kit = Item::get(505, 0, 1);
                         $set = Item::get(395, 0, 1);
                         $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                         $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                         $player->getInventory()->setItem(0, $kit);
                         $player->getInventory()->setItem(4, $set);
                            
                     } else {
                        	
                     	$player->sendMessage(Color::RED . "Du brauchst 1000 Coins");
                        	
                     }
                 	
                 }
             	
             }
        	
        } else if ($item->getCustomName() === Color::YELLOW . "Maurer") {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
             if ($pf->get("VIP") === true) {
             	
             	$pf->set("MLGKit", "Maurer");
                 $pf->save();
                 $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Maurer" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                 $player->getInventory()->clearAll();
                 $kit = Item::get(505, 0, 1);
                 $set = Item::get(395, 0, 1);
                 $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                 $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                 $player->getInventory()->setItem(0, $kit);
                 $player->getInventory()->setItem(4, $set);
             	
             } else {
             	
             	if ($pf->get("Maurer") === true) {
             	
             	    $pf->set("MLGKit", "Maurer");
                     $pf->save();
                     $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Maurer" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                     $player->getInventory()->clearAll();
                     $kit = Item::get(505, 0, 1);
                     $set = Item::get(395, 0, 1);
                     $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                     $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                     $player->getInventory()->setItem(0, $kit);
                     $player->getInventory()->setItem(4, $set);
                     
                 } else {
                 	
                 	$pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                     if ($pc->get("coins") >= 500) {
                        	
                         $pf->set("Maurer", true);
                         $pf->save();
                         $pc->set("coins", $pc->get("coins")-500);
                         $pc->save();
                         $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Maurer" . Color::WHITE . " wurde erfolgreich gekauft");
                         $player->getInventory()->clearAll();
                         $kit = Item::get(505, 0, 1);
                         $set = Item::get(395, 0, 1);
                         $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                         $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                         $player->getInventory()->setItem(0, $kit);
                         $player->getInventory()->setItem(4, $set);
                            
                     } else {
                        	
                     	$player->sendMessage(Color::RED . "Du brauchst 500 Coins");
                        	
                     }
                 	
                 }
             	
             }
        	
        } else if ($item->getCustomName() === Color::YELLOW . "Archer") {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
             if ($pf->get("VIP") === true) {
             	
             	$pf->set("MLGKit", "Archer");
                 $pf->save();
                 $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Archer" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                 $player->getInventory()->clearAll();
                 $kit = Item::get(505, 0, 1);
                 $set = Item::get(395, 0, 1);
                 $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                 $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                 $player->getInventory()->setItem(0, $kit);
                 $player->getInventory()->setItem(4, $set);
             	
             } else {
             	
             	if ($pf->get("Archer") === true) {
             	
             	    $pf->set("MLGKit", "Archer");
                     $pf->save();
                     $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Archer" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                     $player->getInventory()->clearAll();
                     $kit = Item::get(505, 0, 1);
                     $set = Item::get(395, 0, 1);
                     $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                     $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                     $player->getInventory()->setItem(0, $kit);
                     $player->getInventory()->setItem(4, $set);
                     
                 } else {
                 	
                 	$pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                     if ($pc->get("coins") >= 500) {
                        	
                         $pf->set("Archer", true);
                         $pf->save();
                         $pc->set("coins", $pc->get("coins")-500);
                         $pc->save();
                         $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Archer" . Color::WHITE . " wurde erfolgreich gekauft");
                         $player->getInventory()->clearAll();
                         $kit = Item::get(505, 0, 1);
                         $set = Item::get(395, 0, 1);
                         $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                         $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                         $player->getInventory()->setItem(0, $kit);
                         $player->getInventory()->setItem(4, $set);
                            
                     } else {
                        	
                     	$player->sendMessage(Color::RED . "Du brauchst 500 Coins");
                        	
                     }
                 	
                 }
             	
             }
        	
        } else if ($item->getCustomName() === Color::YELLOW . "Tank") {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
             if ($pf->get("VIP") === true) {
             	
             	$pf->set("MLGKit", "Tank");
                 $pf->save();
                 $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Tank" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                 $player->getInventory()->clearAll();
                 $kit = Item::get(505, 0, 1);
                 $set = Item::get(395, 0, 1);
                 $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                 $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                 $player->getInventory()->setItem(0, $kit);
                 $player->getInventory()->setItem(4, $set);
             	
             } else {
             	
             	if ($pf->get("Tank") === true) {
             	
             	    $pf->set("MLGKit", "Tank");
                     $pf->save();
                     $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Tank" . Color::WHITE . " wurde erfolgreich ausgewaehlt");
                     $player->getInventory()->clearAll();
                     $kit = Item::get(505, 0, 1);
                     $set = Item::get(395, 0, 1);
                     $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                     $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                     $player->getInventory()->setItem(0, $kit);
                     $player->getInventory()->setItem(4, $set);
                     
                 } else {
                 	
                 	$pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                     if ($pc->get("coins") >= 5000) {
                        	
                         $pf->set("Tank", true);
                         $pf->save();
                         $pc->set("coins", $pc->get("coins")-5000);
                         $pc->save();
                         $player->sendMessage(Color::WHITE . "Das Kit: " . Color::YELLOW . "Tank" . Color::WHITE . " wurde erfolgreich gekauft");
                         $player->getInventory()->clearAll();
                         $kit = Item::get(505, 0, 1);
                         $set = Item::get(395, 0, 1);
                         $kit->setCustomName(Color::DARK_PURPLE . "Kits");
                         $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                         $player->getInventory()->setItem(0, $kit);
                         $player->getInventory()->setItem(4, $set);
                            
                     } else {
                        	
                     	$player->sendMessage(Color::RED . "Du brauchst 5000 Coins");
                        	
                     }
                 	
                 }
             	
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
        
        if ($config->get("schutz") === true) {
        	
        	$event->setCancelled(true);
        
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
        $event->setDeathMessage($this->prefix . Color::DARK_GRAY . $player->getName() . Color::GRAY . " ist Gestorben!");
        $player->setGamemode(3);
    
    }
    
    public function onRespawn(PlayerRespawnEvent $event) {
    	
    	$this->players--;
    	$player = $event->getPlayer();
        $this->delPlayer($player);
        $player->getInventory()->clearAll();
        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        $playerfile->set("teleportt", 2);
        $playerfile->set("teleport", true);
        $playerfile->save();
        
    }
    
    public function teleportIngame(Player $player) {
    	
    	$config = $this->getConfig();
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
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
        
        }
        
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
        $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        if ($pf->get("MLGKit") === "SprengMeister") {
        	
        	$player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(46, 0, 1));
            $player->getInventory()->addItem(Item::get(30, 0, 8));
        	
        } else if ($pf->get("MLGKit") === "Souper") {
        	
        	$player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(267, 0, 1));
            $player->getInventory()->addItem(Item::get(281, 0, 64));
            $player->getInventory()->addItem(Item::get(39, 0, 64));
            $player->getInventory()->addItem(Item::get(40, 0, 64));
        	
        } else if ($pf->get("MLGKit") === "Assassine") {
        	
        	$boots = Item::get(309, 0, 1);
        	
        	$player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(267, 0, 1));
            $player->getInventory()->addItem(Item::get(325, 8, 1));
            $player->getArmorInventory()->setBoots($boots);
        	
        } else if ($pf->get("MLGKit") === "Maurer") {
        	
        	$player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(268, 0, 1));
            $player->getInventory()->addItem(Item::get(43, 4, 64));
            $player->getInventory()->addItem(Item::get(43, 4, 64));
            $player->getInventory()->addItem(Item::get(43, 4, 64));
        	
        } else if ($pf->get("MLGKit") === "Archer") {
        	
        	$player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(261, 0, 1));
            $player->getInventory()->addItem(Item::get(262, 0, 32));
        	
        } else if ($pf->get("MLGKit") === "Tank") {
        	
        	$player->getInventory()->clearAll();
        
            $helm = Item::get(306, 0, 1);
            $chest = Item::get(311, 0, 1);
            $leg = Item::get(308, 0, 1);
            $boots = Item::get(309, 0, 1);
            
            $player->getArmorInventory()->setHelmet($helm);
            $player->getArmorInventory()->setChestplate($chest);
            $player->getArmorInventory()->setLeggings($leg);
            $player->getArmorInventory()->setBoots($boots);
        	
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

            if ($this->plugin->players < 3) {

                foreach ($all as $player) {

                    $player->sendPopup(Color::GRAY . ">> Warten auf weitere Spieler <<");

                }

            }

            if ($this->plugin->players >= 3) {

                $config->set("time", $config->get("time") - 1);
                $config->save();
                $time = $config->get("time") + 1;
                foreach ($all as $player) {
                	
                	$pfk = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                	$player->sendPopup(
                       Color::YELLOW . "Kit: " . Color::WHITE . $pfk->get("MLGKit") . "\n" .
                       Color::YELLOW . "Spieler: " . Color::WHITE . $this->plugin->players . Color::YELLOW . " / " . Color::WHITE . "12" . "\n" .
                       Color::GREEN . "Map: " . Color::WHITE . $config->get("Arena")
                    );
                	
                }
                
                if ($time % 5 === 0 && $time > 0) {

                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match startet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 5) {
                
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
                    $config->set("schutz", true);
                    $config->set("state", true);
                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . "60" . Color::WHITE . " Minuten!");
                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "30" . Color::WHITE . " Sekunden!");
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
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 3600);
                    $config->save();
                    $this->plugin->players = 0;
                    $levelname = $config->get("Arena");
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
                	
                	$pfk = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                	$player->sendPopup(
                         Color::YELLOW . "Kit: " . Color::WHITE . $pfk->get("MLGKit") . "\n" .
                         Color::YELLOW . "Spieler: " . Color::WHITE . $this->plugin->players . Color::YELLOW . " / " . Color::WHITE . "12"
                     );
                	
                   if ($time === 3590) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "20" . Color::WHITE . " Sekunden!");

                    } else if ($time === 3580) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "10" . Color::WHITE . " Sekunden!");

                    } else if ($time === 3575) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "5" . Color::WHITE . " Sekunden!");

                    } else if ($time === 3573) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "3" . Color::WHITE . " Sekunden!");

                    } else if ($time === 3572) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "2" . Color::WHITE . " Sekunden!");

                    } else if ($time === 3571) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "1er" . Color::WHITE . " Sekunde!");

                    } else if ($time === 3570) {
                    	
                    	$player->sendMessage(Color::DARK_PURPLE . "> > ". Color::WHITE . "Die Schutz Zeit ist nun Vorrueber!");
                    	$config->set("schutz", false);
                        $config->save();
                    
                    } else if ($time % 60 === 0 && $time > 60 && $time < 3600) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . $time / 60 . Color::WHITE . " Minuten!");

                    } else if ($time === 60) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . $time / 60 . Color::WHITE . " Minuten!");

                    } else if ($time === 1 || $time === 2 || $time === 3 || $time === 4 || $time === 5 || $time === 15 || $time === 30) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    } else if ($time === 0) {

                        $player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
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
                        $levelname = $config->get("Arena");
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
                $clouddata->save();
            	$this->plugin->getServer()->broadcastMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
            	
            } else if ($time === 5) {
            	
            	$this->plugin->getServer()->broadcastMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
            	
            } else if ($time === 0) {
            	
            	$clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
                $clouddata->set("ServerMessage", "Der Server: " . $config->get("Server") . " wird hochgefahren!");
                $clouddata->set("ServerMessageStatus", true);
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