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
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
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
use pocketmine\event\inventory\CraftItemEvent;

class TryJump extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "TryJump" . Color::WHITE . "] ";
	public $arenaname = "";
	public $mode = 0;
	public $players = 0;
	
    public function onEnable() {
    	
	    if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
        
        if (is_dir("/home/EnderCloud/TryJump/players") !== true) {
			
            mkdir("/home/EnderCloud/TryJump/players");
            
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
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new sendSuperBar($this), 1);
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
        $uuid = $player->getClientID();
        if (!is_file("/home/EnderCloud/TryJump/players/" . $player->getName() . ".yml")) {
        	
        	$playerfile = new Config("/home/EnderCloud/TryJump/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Slot", 0);
            $playerfile->set("Team", false);
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
        $pf = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
        $pf->set("Team", false);
        $pf->save();
        if ($config->get("ingame") === true) {
        	
        	$event->setJoinMessage("");
        	$player->getInventory()->clearAll();
            $player->setGamemode(3);
            $level = $this->getServer()->getLevelByName($config->get("Arena"));
            $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
            $player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
        
        } else {
        	
        if ($this->players === 0) {
        	
        	$this->players++;
        	$config->set("ingame", false);
            $config->set("time", 60);
            $config->set("playtime", 3600);
            $config->set("waittime", 60);
            $config->set("deathtime", 3600);
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
                
                $config->set("player1", $p2);
                $config->set("player2", $p3);
                $config->set("player3", $p4);
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", "");
                $config->set("p8", false);
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
                
                $config->set("player2", $p3);
                $config->set("player3", $p4);
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", "");
                $config->set("p8", false);
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
                
                $config->set("player3", $p4);
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", "");
                $config->set("p8", false);
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
                
                $config->set("player4", $p5);
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", "");
                $config->set("p8", false);
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
                
                $config->set("player5", $p6);
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", "");
                $config->set("p8", false);
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
                
                $config->set("player6", $p7);
                $config->set("player7", $p8);
                $config->set("player8", "");
                $config->set("p8", false);
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
                
                $config->set("player7", $p8);
                $config->set("player8", "");
                $config->set("p8", false);
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
                
                $config->set("player8", "");
                $config->set("p8", false);
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
        
            }
            
            }
        	
        }
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "TryJump":
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
        if ($item->getCustomName() === Color::RED . "Zur�ck") {
        	
        	
            
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
            
            $player->sendMessage($this->prefix . "Jetzt den Shop Spawn");
            $this->mode++;
            
        } else if ($this->mode === 2 && $player->isOp()) {
        	
        	$af->set("s2x", $block->getX() + 0.5);
            $af->set("s2y", $block->getY() + 1);
            $af->set("s2z", $block->getZ() + 0.5);
            $af->save();
            
            $player->sendMessage($this->prefix . "Die Arena ist nun Spielbereit");
            $this->mode = 0;
            
            $this->copymap($this->getServer()->getDataPath() . "/worlds/" . $player->getLevel()->getFolderName(), $this->getDataFolder() . "/maps/" . $player->getLevel()->getFolderName());
            $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
            $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
            $player->teleport($spawn, 0, 0);
            
        }
        
    }
    
    public function onMove(PlayerMoveEvent $event) {
    	
    	$player = $event->getPlayer();
        $level = $event->getPlayer()->getLevel();
        $l = $level->getName();
        $pos = $player->getPosition();
        $x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
        $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));
        if ($block->getId() === Block::GOLD_BLOCK) {
        	
        	$player->getLevel()->setBlock(new Vector3($player->getX(),($player->getY()-1),$player->getZ()), Block::get(Block::STONE));
        	$player->getLevel()->setBlock(new Vector3($player->getX()+2,($player->getY()),$player->getZ()), Block::get(Block::END_STONE));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+5,($player->getY()),$player->getZ()-1), Block::get(Block::END_STONE));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+9,($player->getY()+1),$player->getZ()+1), Block::get(Block::END_STONE));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+12,($player->getY()),$player->getZ()), Block::get(Block::END_STONE));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()+1),$player->getZ()-2), Block::get(Block::END_STONE));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+23,($player->getY()+3),$player->getZ()+1), Block::get(Block::END_STONE));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+34,($player->getY()+1),$player->getZ()), Block::get(Block::END_STONE));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+37,($player->getY()),$player->getZ()), Block::get(Block::GOLD_BLOCK));

                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()-1),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()+1),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()-1),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()+1),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()-1),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()+1),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+15,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()-1),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()+1),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()-1),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()+1),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()-1),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()+1),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+19,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                //Seiten wand 1-2
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()-1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+2),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()-1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+2),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()-1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+2),$player->getZ()-2), Block::get(Block::OBSIDIAN));

                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()-1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+2),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()-1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+2),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()-1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+2),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                //Oben1
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));

                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()-1),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()+1),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()-1),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()+1),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()-1),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()+1),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+26,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()-1),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()+1),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()-1),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()+1),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()-1),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()+1),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+30,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                //Seiten wand 1-2
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()-1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+2),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()-1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+2),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()-1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+1),$player->getZ()-2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+2),$player->getZ()-2), Block::get(Block::OBSIDIAN));

                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()-1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+2),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()-1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+2),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()-1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+1),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+2),$player->getZ()+2), Block::get(Block::OBSIDIAN));
                                //Oben1
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+2),$player->getZ()), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+2),$player->getZ()-1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+2),$player->getZ()+1), Block::get(Block::OBSIDIAN));

                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+3),$player->getZ()), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+3),$player->getZ()), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+3),$player->getZ()-1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+3),$player->getZ()-1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+3),$player->getZ()-1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+27,($player->getY()+3),$player->getZ()+1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+28,($player->getY()+3),$player->getZ()+1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+29,($player->getY()+3),$player->getZ()+1), Block::get(101));

                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+3),$player->getZ()), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+3),$player->getZ()), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+3),$player->getZ()-1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+3),$player->getZ()-1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+3),$player->getZ()-1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+16,($player->getY()+3),$player->getZ()+1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+17,($player->getY()+3),$player->getZ()+1), Block::get(101));
                                $player->getLevel()->setBlock(new Vector3($player->getX()+18,($player->getY()+3),$player->getZ()+1), Block::get(101));
        	
        }
    	
    }
    
    public function onDamage(EntityDamageEvent $event) {
    	
    	$player = $event->getEntity();
        $config = $this->getConfig();
        if ($config->get("ingame") === false) {
        	
        	$event->setCancelled(true);
        
        } else {
        	
        	if ($event instanceof EntityDamageByEntityEvent) {
        	
        	    $damager = $event->getDamager();
                if ($damager instanceof Player) {
                	
                	if ($damager->getName() === $config->get("blau1")) {
                	
                	    if ($player->getName() === $config->get("blau3")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("blau5")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("blau7")) {
                        	
                            $event->setCancelled(true);
                        
                        }
                        
                    } else if ($damager->getName() === $config->get("blau3")) {
                	
                	    if ($player->getName() === $config->get("blau1")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("blau5")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("blau7")) {
                        	
                            $event->setCancelled(true);
                        
                        }
                        
                    } else if ($damager->getName() === $config->get("blau5")) {
                	
                	    if ($player->getName() === $config->get("blau1")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("blau3")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("blau7")) {
                        	
                            $event->setCancelled(true);
                        
                        }
                        
                    } else if ($damager->getName() === $config->get("blau7")) {
                	
                	    if ($player->getName() === $config->get("blau1")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("blau5")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("blau3")) {
                        	
                            $event->setCancelled(true);
                        
                        }
                        
                    } else if ($damager->getName() === $config->get("rot2")) {
                	
                	    if ($player->getName() === $config->get("rot4")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("rot6")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("rot8")) {
                        	
                            $event->setCancelled(true);
                        
                        }
                        
                    } else if ($damager->getName() === $config->get("rot4")) {
                	
                	    if ($player->getName() === $config->get("rot2")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("rot6")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("rot8")) {
                        	
                            $event->setCancelled(true);
                        
                        }
                        
                    } else if ($damager->getName() === $config->get("rot6")) {
                	
                	    if ($player->getName() === $config->get("rot2")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("rot4")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("rot8")) {
                        	
                            $event->setCancelled(true);
                        
                        }
                        
                    } else if ($damager->getName() === $config->get("rot8")) {
                	
                	    if ($player->getName() === $config->get("rot2")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("rot6")) {
                        	
                            $event->setCancelled(true);
                        
                        } else if ($player->getName() === $config->get("rot4")) {
                        	
                            $event->setCancelled(true);
                        
                        }
                        
                    }
                    
                }
                
            }
        	
        }
        
    }
    
    public function onBedEnter(PlayerBedEnterEvent $event) {
    
        $event->setCancelled(true);
        
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
                    
                } else if ($block->getId() === Block::BED_BLOCK) {
                	
                	$event->setDrops(array());
                    if ($x === $af->get("sb1x") && $y === $af->get("sb1y") && $z === $af->get("sb1z")) {
                	
                	    if ($player->getName() === $config->get("blau1")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("blau3")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("blau5")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("blau7")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::WHITE . "Das " . Color::BLUE . "BLAUE" . Color::WHITE . " Bett wurde zerstoert!");
                            $config->set("block1", false);
                            $config->save();
                            $p2 = $this->getServer()->getPlayerExact($config->get("blau1"));	
                            if (!$p2 == null) {
                            	
                            	$p2->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p4 = $this->getServer()->getPlayerExact($config->get("blau3"));	
                            if (!$p4 == null) {
                            	
                            	$p4->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p6 = $this->getServer()->getPlayerExact($config->get("blau5"));	
                            if (!$p6 == null) {
                            	
                            	$p6->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p8 = $this->getServer()->getPlayerExact($config->get("blau7"));	
                            if (!$p8 == null) {
                            	
                            	$p8->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                        }
                        
                    } else if ($x === $af->get("sb1x1") && $y === $af->get("sb1y1") && $z === $af->get("sb1z1")) {
                	
                	    if ($player->getName() === $config->get("blau1")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("blau3")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("blau5")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("blau7")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::WHITE . "Das " . Color::BLUE . "BLAUE" . Color::WHITE . " Bett wurde zerstoert!");
                            $config->set("block1", false);
                            $config->save();
                            $p2 = $this->getServer()->getPlayerExact($config->get("blau1"));	
                            if (!$p2 == null) {
                            	
                            	$p2->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p4 = $this->getServer()->getPlayerExact($config->get("blau3"));	
                            if (!$p4 == null) {
                            	
                            	$p4->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p6 = $this->getServer()->getPlayerExact($config->get("blau5"));	
                            if (!$p6 == null) {
                            	
                            	$p6->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p8 = $this->getServer()->getPlayerExact($config->get("blau7"));	
                            if (!$p8 == null) {
                            	
                            	$p8->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                        }
                        
                    } else if ($x === $af->get("sb2x") && $y === $af->get("sb2y") && $z === $af->get("sb2z")) {
                	
                	    if ($player->getName() === $config->get("rot2")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("rot4")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("rot6")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("rot8")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::WHITE . "Das " . Color::RED . "ROTE" . Color::WHITE . " Bett wurde zerstoert!");
                            $config->set("block2", false);
                            $config->save();
                            $p2 = $this->getServer()->getPlayerExact($config->get("rot2"));	
                            if (!$p2 == null) {
                            	
                            	$p2->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p4 = $this->getServer()->getPlayerExact($config->get("rot4"));	
                            if (!$p4 == null) {
                            	
                            	$p4->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p6 = $this->getServer()->getPlayerExact($config->get("rot6"));	
                            if (!$p6 == null) {
                            	
                            	$p6->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p8 = $this->getServer()->getPlayerExact($config->get("rot8"));	
                            if (!$p8 == null) {
                            	
                            	$p8->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                        }
                        
                    } else if ($x === $af->get("sb2x1") && $y === $af->get("sb2y1") && $z === $af->get("sb2z1")) {
                	
                	    if ($player->getName() === $config->get("rot2")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("rot4")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("rot6")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else if ($player->getName() === $config->get("rot8")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::WHITE . "Das " . Color::RED . "ROTE" . Color::WHITE . " Bett wurde zerstoert!");
                            $config->set("block2", false);
                            $config->save();
                            $p2 = $this->getServer()->getPlayerExact($config->get("rot2"));	
                            if (!$p2 == null) {
                            	
                            	$p2->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p4 = $this->getServer()->getPlayerExact($config->get("rot4"));	
                            if (!$p4 == null) {
                            	
                            	$p4->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p6 = $this->getServer()->getPlayerExact($config->get("rot6"));	
                            if (!$p6 == null) {
                            	
                            	$p6->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                            $p8 = $this->getServer()->getPlayerExact($config->get("rot8"));	
                            if (!$p8 == null) {
                            	
                            	$p8->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                        }
                        
                    }
                
                } else if ($block->getId() === Block::RED_SANDSTONE) {
                	
                	$event->setCancelled(false);
                
                } else if ($block->getId() === 20) {
                	
                	$event->setCancelled(false);
                
                } else if ($block->getId() === 54) {
                	
                	$event->setCancelled(false);
                
                } else if ($block->getId() === 121) {
                	
                	$event->setCancelled(false);
                
                } else if ($block->getId() === 130) {
                	
                	$event->setCancelled(false);
                    $event->setDrops(array(Item::get(130, 0, 1)));
                
                } else if ($block->getId() === 65) {
                	
                	$event->setCancelled(false);
                
                } else if ($block->getId() === 30) {
                	
                	$event->setCancelled(false);
                    $event->setDrops(array());
                    $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
                	
                } else {
                	
                	$event->setCancelled(true);
                
                }
                
            }
        
    }
    
    public function onDeath(PlayerDeathEvent $event) {
    	
    	$player = $event->getPlayer();
        $event->setDeathMessage($this->prefix . Color::GREEN . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
        $event->setDrops(array());
        $config = $this->getConfig();
        if ($player->getName() === $config->get("blau1")) {
                	
                	if ($config->get("block1") === false) {
                    	
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        
                    }
                    
                } else if ($player->getName() === $config->get("rot2")) {
                	
                	if ($config->get("block2") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        
                    }
                    
                } else if ($player->getName() === $config->get("blau3")) {
                	
                	if ($config->get("block1") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        
                    }
                    
                } else if ($player->getName() === $config->get("rot4")) {
                	
                	if ($config->get("block2") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        
                    }
                    
                } else if ($player->getName() === $config->get("blau5")) {
                	
                	if ($config->get("block1") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        
                    }
                    
                } else if ($player->getName() === $config->get("rot6")) {
                	
                	if ($config->get("block2") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        
                    }
                    
                } else if ($player->getName() === $config->get("blau7")) {
                	
                	if ($config->get("block1") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        
                    }
                    
                } else if ($player->getName() === $config->get("rot8")) {
                	
                	if ($config->get("block2") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        
                    }
                    
                }
    }
    
    public function onRespawn(PlayerRespawnEvent $event) {
    	
    	$player = $event->getPlayer();
        $pos = $player->getPosition();
        $config = $this->getConfig();
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        if ($player->getName() === $config->get("blau1")) {
                	
                	if ($config->get("block1") === true) {
                	
                	    $this->giveKit($player);
                        $player->teleport(new Position($af->get("s1x"), $af->get("s1y"), $af->get("s1z"), $level));
                
                    } else if ($config->get("block1") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        $this->players--;
                        $this->delPlayer($player);
                        $config->set("p1", false);
                        $config->save();
                        
                    }
                    
                } else if ($player->getName() === $config->get("rot2")) {
                	
                	if ($config->get("block2") === true) {
                	
                	    $this->giveKit($player);
                         $player->teleport(new Position($af->get("s2x"), $af->get("s2y"), $af->get("s2z"), $level));
                
                    } else if ($config->get("block2") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        $this->players--;
                        $this->delPlayer($player);
                        $config->set("p2", false);
                        $config->save();
                        
                    }
                    
                } else if ($player->getName() === $config->get("blau3")) {
                	
                	if ($config->get("block1") === true) {
                	
                	    $this->giveKit($player);
                         $player->teleport(new Position($af->get("s1x"), $af->get("s1y"), $af->get("s1z"), $level));
                
                    } else if ($config->get("block1") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        $this->players--;
                        $this->delPlayer($player);
                        $config->set("p3", false);
                        $config->save();
                        
                    }
                    
                } else if ($player->getName() === $config->get("rot4")) {
                	
                	if ($config->get("block2") === true) {
                	
                	    $this->giveKit($player);
                        $player->teleport(new Position($af->get("s2x"), $af->get("s2y"), $af->get("s2z"), $level));
                
                    } else if ($config->get("block2") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        $this->players--;
                        $this->delPlayer($player);
                        $config->set("p4", false);
                        $config->save();
                        
                    }
                    
                } else if ($player->getName() === $config->get("blau5")) {
                	
                	if ($config->get("block1") === true) {
                	
                	    $this->giveKit($player);
                         $player->teleport(new Position($af->get("s1x"), $af->get("s1y"), $af->get("s1z"), $level));
                
                    } else if ($config->get("block1") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        $this->players--;
                        $this->delPlayer($player);
                        $config->set("p5", false);
                        $config->save();
                        
                    }
                    
                } else if ($player->getName() === $config->get("rot6")) {
                	
                	if ($config->get("block2") === true) {
                	
                	    $this->giveKit($player);
                        $player->teleport(new Position($af->get("s2x"), $af->get("s2y"), $af->get("s2z"), $level));
                
                    } else if ($config->get("block2") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        $this->players--;
                        $this->delPlayer($player);
                        $config->set("p6", false);
                        $config->save();
                        
                    }
                    
                } else if ($player->getName() === $config->get("blau7")) {
                	
                	if ($config->get("block1") === true) {
                	
                	    $this->giveKit($player);
                         $player->teleport(new Position($af->get("s1x"), $af->get("s1y"), $af->get("s1z"), $level));
                
                    } else if ($config->get("block1") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        $this->players--;
                        $this->delPlayer($player);
                        $config->set("p7", false);
                        $config->save();
                        
                    }
                    
                } else if ($player->getName() === $config->get("rot8")) {
                	
                	if ($config->get("block2") === true) {
                	
                	    $this->giveKit($player);
                        $player->teleport(new Position($af->get("s2x"), $af->get("s2y"), $af->get("s2z"), $level));
                
                    } else if ($config->get("block2") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        $this->players--;
                        $this->delPlayer($player);
                        $config->set("p8", false);
                        $config->save();
                        
                    }
                    
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
    
    public function setArenaPlayer(Player $player)
    {

        $config = $this->getConfig();
        $pf = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
        if ($config->get("blau1") === "") {
        	
        	if ($config->get("player1") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau1", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player2") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau1", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player3") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau1", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player4") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau1", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player5") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau1", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player6") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau1", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player7") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau1", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player8") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau1", $player->getName());
                    $config->save();
                    
                }
                
            }
        	
        } else if ($config->get("rot2") === "") {
        	
        	if ($config->get("player1") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot2", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player2") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot2", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player3") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot2", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player4") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot2", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player5") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot2", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player6") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot2", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player7") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot2", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player8") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot2", $player->getName());
                    $config->save();
                    
                }
                
            }
        	
        } else if ($config->get("blau3") === "") {
        	
        	if ($config->get("player1") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau3", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player2") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau3", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player3") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau3", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player4") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau3", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player5") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau3", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player6") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau3", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player7") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau3", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player8") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau3", $player->getName());
                    $config->save();
                    
                }
                
            }
        	
        } else if ($config->get("rot4") === "") {
        	
        	if ($config->get("player1") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot4", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player2") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot4", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player3") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot4", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player4") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot4", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player5") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot4", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player6") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot4", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player7") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot4", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player8") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot4", $player->getName());
                    $config->save();
                    
                }
                
            }
        	
        } else if ($config->get("blau5") === "") {
        	
        	if ($config->get("player1") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau5", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player2") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau5", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player3") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau5", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player4") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau5", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player5") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau5", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player6") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau5", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player7") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau5", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player8") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau5", $player->getName());
                    $config->save();
                    
                }
                
            }
        	
        } else if ($config->get("rot6") === "") {
        	
        	if ($config->get("player1") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot6", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player2") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot6", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player3") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot6", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player4") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot6", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player5") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot6", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player6") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot6", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player7") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot6", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player8") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot6", $player->getName());
                    $config->save();
                    
                }
                
            }
        	
        } else if ($config->get("blau7") === "") {
        	
        	if ($config->get("player1") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau7", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player2") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau7", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player3") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau7", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player4") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau7", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player5") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau7", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player6") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau7", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player7") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau7", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player8") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("blau7", $player->getName());
                    $config->save();
                    
                }
                
            }
        	
        } else if ($config->get("rot8") === "") {
        	
        	if ($config->get("player1") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot8", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player2") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot8", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player3") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot8", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player4") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot8", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player5") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot8", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player6") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot8", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player7") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot8", $player->getName());
                    $config->save();
                    
                }
                
            } else if ($config->get("player8") === $player->getName()) {
        	
        	    if ($pf->get("Team") === false) {
        	
        	        $pf->set("Team", true);
                    $pf->save();
                    $config->set("rot8", $player->getName());
                    $config->save();
                    
                }
                
            }
        	
        }

    }
    
    public function giveKit(Player $player)
    {

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

    }
    
    public function spawn(Player $player) {
    	
    	$pos = $player->getPosition();
        $player->setSpawn($pos);
        
    }
    
    public function teleportIngame(Player $player) {
    	
    	$config = $this->getConfig();
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
        $level->setTime(0);
        $level->stopTime();
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        if (!$this->getServer()->getLevelByName($config->get("Arena")) instanceof Level) {
        	
            $this->getServer()->loadLevel($config->get("Arena"));
            
        }
        
        if ($player->getName() === $config->get("blau1")) {
        	
        	$player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
            $player->setDisplayName(Color::WHITE . "[" . Color::BLUE . "Blau" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
            $player->setNameTag(Color::WHITE . "[" . Color::BLUE . "Blau" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
        
        } else if ($player->getName() === $config->get("rot2")) {
            
        	$player->teleport(new Position($af->get("s2x"), $af->get("s2y")+1, $af->get("s2z"), $level));
            $player->setDisplayName(Color::WHITE . "[" . Color::RED . "Rot" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
            $player->setNameTag(Color::WHITE . "[" . Color::RED . "Rot" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
        
        } else if ($player->getName() === $config->get("blau3")) {
        	
        	$player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
            $player->setDisplayName(Color::WHITE . "[" . Color::BLUE . "Blau" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
            $player->setNameTag(Color::WHITE . "[" . Color::BLUE . "Blau" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
        
        } else if ($player->getName() === $config->get("rot4")) {
            
        	$player->teleport(new Position($af->get("s2x"), $af->get("s2y")+1, $af->get("s2z"), $level));
            $player->setDisplayName(Color::WHITE . "[" . Color::RED . "Rot" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
            $player->setNameTag(Color::WHITE . "[" . Color::RED . "Rot" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
        
        } else if ($player->getName() === $config->get("blau5")) {
        	
        	$player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
            $player->setDisplayName(Color::WHITE . "[" . Color::BLUE . "Blau" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
            $player->setNameTag(Color::WHITE . "[" . Color::BLUE . "Blau" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
        
        } else if ($player->getName() === $config->get("rot6")) {
            
        	$player->teleport(new Position($af->get("s2x"), $af->get("s2y")+1, $af->get("s2z"), $level));
            $player->setDisplayName(Color::WHITE . "[" . Color::RED . "Rot" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
            $player->setNameTag(Color::WHITE . "[" . Color::RED . "Rot" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
        
        } else if ($player->getName() === $config->get("blau7")) {
        	
        	$player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
            $player->setDisplayName(Color::WHITE . "[" . Color::BLUE . "Blau" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
            $player->setNameTag(Color::WHITE . "[" . Color::BLUE . "Blau" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
        
        } else if ($player->getName() === $config->get("rot8")) {
            
        	$player->teleport(new Position($af->get("s2x"), $af->get("s2y")+1, $af->get("s2z"), $level));
            $player->setDisplayName(Color::WHITE . "[" . Color::RED . "Rot" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
            $player->setNameTag(Color::WHITE . "[" . Color::RED . "Rot" . Color::WHITE . "] " . $player->getName() . Color::WHITE);
        
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
        
        if ($config->get("ingame") === true) {
        	
        	if ($config->get("p1") === false) {
        	
        	    if ($config->get("p3") === false) {
        	
        	        if ($config->get("p5") === false) {
        	
        	            if ($config->get("p7") === false) {
        	
        	                $config->set("Blau", false);
                            $config->save();
                            
                        }
                        
                    }
                    
                }
                
            }
            
            if ($config->get("p2") === false) {
        	
        	    if ($config->get("p4") === false) {
        	
        	        if ($config->get("p6") === false) {
        	
        	            if ($config->get("p8") === false) {
        	
        	                $config->set("Rot", false);
                            $config->save();
                            
                        }
                        
                    }
                    
                }
                
            }
        	
        }
        
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

            if ($this->plugin->players < 4) {

                foreach ($all as $player) {

                    $player->sendPopup(Color::GRAY . ">> Warten auf weitere Spieler <<");

                }

            }

            if ($this->plugin->players >= 4) {

                $config->set("time", $config->get("time") - 1);
                $config->save();
                $time = $config->get("time") + 1;
                foreach ($all as $player) {
                	
                	$player->sendPopup(
                       Color::YELLOW . "Spieler: " . Color::WHITE . $this->plugin->players . Color::YELLOW . "/" . Color::WHITE . "8" . "\n" .
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
                	
                	$config->set("Blau", true);
                    $config->set("Rot", true);
                    $config->set("ingame", true);
                    $config->set("state", true);
                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . "60" . Color::WHITE . " Minuten!");
                        $player->setHealth(20);
                        $player->setFood(20);
                        $this->plugin->setArenaPlayer($player);
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
                    $config->set("player3", "");
                    $config->set("player4", "");
                    $config->set("player5", "");
                    $config->set("player6", "");
                    $config->set("player7", "");
                    $config->set("player8", "");
                    $config->save();
                    $this->plugin->players = 0;
                    $levelname = $config->get("Arena");
                    $lev = $this->plugin->getServer()->getLevelByName($levelname);
                    $this->plugin->getServer()->unloadLevel($lev);
                    $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->getServer()->loadLevel($levelname);

                }

            } else if ($this->plugin->players > 0) {
            	
                if ($config->get("Blau") === false) {
                	
                	foreach ($all as $player) {
                	
                	   $player->getInventory()->clearAll();
                       $player->getArmorInventory()->clearAll();
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
                       $pff = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
                       $pff->set("Team", false);
                       $pff->save();
                       
                    }
                    
                    $this->plugin->getServer()->broadcastMessage($this->plugin->prefix . "Das Team: " . Color::RED . "ROT" . Color::WHITE . " hat das Spiel gewonnen!");
                    $config->set("ingame", false);
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 3600);
                    $config->set("block1", false);
                    $config->set("block2", false);
                    $config->set("Blau", false);
                    $config->set("Rot", false);
                    $config->set("player1", "");
                    $config->set("player2", "");
                    $config->set("player3", "");
                    $config->set("player4", "");
                    $config->set("player5", "");
                    $config->set("player6", "");
                    $config->set("player7", "");
                    $config->set("player8", "");
                    $config->save();
                    $this->plugin->players = 0;
                    $levelname = $config->get("Arena");
                    $lev = $this->plugin->getServer()->getLevelByName($levelname);
                    $this->plugin->getServer()->unloadLevel($lev);
                    $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->getServer()->loadLevel($levelname);
                	
                }
                
                if ($config->get("Rot") === false) {
                	
                	foreach ($all as $player) {
                	
                	   $player->getInventory()->clearAll();
                       $player->getArmorInventory()->clearAll();
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
                       $pff = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
                       $pff->set("Team", false);
                       $pff->save();
                       
                    }
                    
                    $this->plugin->getServer()->broadcastMessage($this->plugin->prefix . "Das Team: " . Color::BLUE . "BLAU" . Color::WHITE . " hat das Spiel gewonnen!");
                    $config->set("ingame", false);
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 3600);
                    $config->set("block1", false);
                    $config->set("block2", false);
                    $config->set("Blau", false);
                    $config->set("Rot", false);
                    $config->set("player1", "");
                    $config->set("player2", "");
                    $config->set("player3", "");
                    $config->set("player4", "");
                    $config->set("player5", "");
                    $config->set("player6", "");
                    $config->set("player7", "");
                    $config->set("player8", "");
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
                	
                    if ($time % 60 === 0 && $time > 60 && $time < 3600) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . $time / 60 . Color::WHITE . " Minuten!");

                    } else if ($time === 60) {

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
                        $pff = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
                    $pff->set("Team", false);
                    $pff->save();
                        $config->set("ingame", false);
                        $config->set("reset", true);
                        $config->set("rtime", 10);
                        $config->set("time", 60);
                        $config->set("playtime", 3600);
                        $config->set("block1", false);
                    $config->set("block2", false);
                    $config->set("Blau", false);
                    $config->set("Rot", false);
                    $config->set("player1", "");
                    $config->set("player2", "");
                    $config->set("player3", "");
                    $config->set("player4", "");
                    $config->set("player5", "");
                    $config->set("player6", "");
                    $config->set("player7", "");
                    $config->set("player8", "");
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

class DropBronze extends PluginTask {
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick) {
    	
        $config = $this->plugin->getConfig();
        $all = $this->plugin->getServer()->getOnlinePlayers();
        if ($config->get("ingame") === true) {
        	
        	if (!$this->plugin->getServer()->getLevelByName($config->get("Arena")) instanceof Level) {
        	
                $this->plugin->getServer()->loadLevel($config->get("Arena"));
            
            }
            
        	$levelname = $config->get("Arena");
            $level = $this->plugin->getServer()->getLevelByName($levelname);
            $tiles = $level->getTiles();
            foreach ($tiles as $tile) {
               	
            	if ($tile instanceof Sign) {
               	
               	 $text = $tile->getText();
                    if ($text[0] === "Bronze") {
                    	
                       $level->dropItem(new Vector3($tile->getX() + 0.5, $tile->getY() + 2, $tile->getZ() + 0.5), Item::get(336, 0, 1));
                    
                   }
               	
               }
               
            }
        	
        }
    	
    }
	
}

class DropIron extends PluginTask {
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick) {
    	
        $config = $this->plugin->getConfig();
        $all = $this->plugin->getServer()->getOnlinePlayers();
        if ($config->get("ingame") === true) {
        	
        	$levelname = $config->get("Arena");
            $level = $this->plugin->getServer()->getLevelByName($levelname);
            $tiles = $level->getTiles();
            foreach ($tiles as $tile) {
               	
            	if ($tile instanceof Sign) {
               	
               	 $text = $tile->getText();
                    if ($text[0] === "Iron") {
                    	
                       $level->dropItem(new Vector3($tile->getX() + 0.5, $tile->getY() + 2, $tile->getZ() + 0.5), Item::get(265, 0, 1));
                    
                   }
               	
               }
               
            }
        	
        }
    	
    }
	
}

class DropGold extends PluginTask {
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick) {
    	
        $config = $this->plugin->getConfig();
        $all = $this->plugin->getServer()->getOnlinePlayers();
        if ($config->get("ingame") === true) {
        	
        	$levelname = $config->get("Arena");
            $level = $this->plugin->getServer()->getLevelByName($levelname);
            $tiles = $level->getTiles();
            foreach ($tiles as $tile) {
               	
            	if ($tile instanceof Sign) {
               	
               	 $text = $tile->getText();
                    if ($text[0] === "Gold") {
                    	
                       $level->dropItem(new Vector3($tile->getX() + 0.5, $tile->getY() + 2, $tile->getZ() + 0.5), Item::get(266, 0, 1));
                    
                   }
               	
               }
               
            }
        	
        }
    	
    }
	
}

class sendSuperBar extends PluginTask
{
	
	public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick)
    {
    	
    	$all = $this->plugin->getServer()->getOnlinePlayers();
        $t = str_repeat(" ", 87);
        $config = $this->plugin->getConfig();
        if ($config->get("Blau") === true) {
        	
        	$config->set("BlauA", "+");
            $config->save();
        	
        } else if ($config->get("Blau") === false) {
        	
        	$config->set("BlauA", "-");
            $config->save();
        	
        }
        
        if ($config->get("Rot") === true) {
        	
        	$config->set("RotA", "+");
            $config->save();
        	
        } else if ($config->get("Rot") === false) {
        	
        	$config->set("RotA", "-");
            $config->save();
        	
        }
        
        foreach ($all as $player) {
        	
            if ($config->get("ingame") === true) {
            	
            	if ($player->getName() === $config->get("blau1")) {
            	
            	    $player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . Color::BLUE . "Blau" . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                                            
                } else if ($player->getName() === $config->get("blau3")) {
            	
            	    $player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . Color::BLUE . "Blau" . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                                            
                } else if ($player->getName() === $config->get("blau5")) {
            	
            	    $player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . Color::BLUE . "Blau" . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                                            
                } else if ($player->getName() === $config->get("blau7")) {
            	
            	    $player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . Color::BLUE . "Blau" . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                                            
                } else if ($player->getName() === $config->get("rot2")) {
            	
            	    $player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . Color::RED . "Rot" . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                                            
                } else if ($player->getName() === $config->get("rot4")) {
            	
            	    $player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . Color::RED . "Rot" . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                                            
                } else if ($player->getName() === $config->get("rot6")) {
            	
            	    $player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . Color::RED . "Rot" . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                                            
                } else if ($player->getName() === $config->get("rot8")) {
            	
            	    $player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . Color::RED . "Rot" . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                                            
                } else {
                	
                	$player->sendTip(
                                            "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Dein Team: " . "\n" .
                                            $t . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . "Teams: " . "\n" .
                                            $t . "\n" .
                                            $t . Color::BLUE . "Blau: " . Color::WHITE . "[" . Color::BLUE . $config->get("BlauA") . Color::WHITE . "]" . "\n" .
                                            $t . Color::RED . "Rot: " . Color::WHITE . "[" . Color::RED . $config->get("RotA") . Color::WHITE . "]" . "\n" .
                                            $t . "\n" .
                                            $t . Color::GRAY . ">> " . Color::AQUA . "BedWars" . Color::GRAY . " <<" . str_repeat("\n", 28));
                	
                }
                  
        	}
        
        }
    	
     }
     
}