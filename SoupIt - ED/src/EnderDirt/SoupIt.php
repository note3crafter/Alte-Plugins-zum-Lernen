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

class SoupIt extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::GOLD . "SoupIt" . Color::WHITE . "] ";
	public $arenaname = "";
	public $mode = 0;
	
	public $s1x = 0;
    public $s1y = 0;
    public $s1z = 0;

    public $s2x = 0;
    public $s2y = 0;
    public $s2z = 0;

    public $s3x = 0;
    public $s3y = 0;
    public $s3z = 0;

    public $s4x = 0;
    public $s4y = 0;
    public $s4z = 0;
    
    public $s5x = 0;
    public $s5y = 0;
    public $s5z = 0;

    public $s6x = 0;
    public $s6y = 0;
    public $s6z = 0;

    public $s7x = 0;
    public $s7y = 0;
    public $s7z = 0;

    public $s8x = 0;
    public $s8y = 0;
    public $s8z = 0;
    
    public $s9x = 0;
    public $s9y = 0;
    public $s9z = 0;

    public $s10x = 0;
    public $s10y = 0;
    public $s10z = 0;

    public $s11x = 0;
    public $s11y = 0;
    public $s11z = 0;

    public $s12x = 0;
    public $s12y = 0;
    public $s12z = 0;
    
    public $s13x = 0;
    public $s13y = 0;
    public $s13z = 0;

    public $s14x = 0;
    public $s14y = 0;
    public $s14z = 0;

    public $s15x = 0;
    public $s15y = 0;
    public $s15z = 0;

    public $s16x = 0;
    public $s16y = 0;
    public $s16z = 0;
    
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
        if (!is_file($this->getDataFolder() . "/players/" . $player->getName() . ".yml")) {
        
            $playerfile = new Config($this->getDataFolder() . "/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("teleportt", "2");
            $playerfile->set("teleport", false);
            $playerfile->save();
            
        }
        
    }
    
    public function onJoin(PlayerJoinEvent $event)
    {

        $player = $event->getPlayer();
        $config = $this->getConfig();
        $af = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/" . $config->get("Arena") . ".yml", Config::YAML);
        $a = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/Daten.yml", Config::YAML);
        $a->set("players", $a->get("players") + 1);
        $a->save();
        $event->setJoinMessage(Color::GRAY . "> " . Color::DARK_GRAY . "> " . $player->getDisplayName() . Color::GRAY . " hat den Server Betreten!");
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $player->setGamemode(0);
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->clearAll();
        $forcemap = Item::get(395, 0, 1);
        $forcemap->setCustomName(Color::DARK_PURPLE . "Forcemap");
        $player->getInventory()->setItem(4, $forcemap);
        $player->removeAllEffects();
        $player->setAllowFlight(false);
        $all = $this->getServer()->getOnlinePlayers();
        if (count($all) === 1) {
        	
        	$a->set("ingame", false);
            $a->set("time", 45);
            $a->set("playtime", 3600);
            $a->set("player1", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 2) {
        	
            $a->set("player2", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 3) {
        	
            $a->set("player3", $player->getName());
            $player->setGamemode(0);         
            $a->save();
            
        } else if (count($all) === 4) {
        	
            $a->set("player4", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 5) {
        	
            $a->set("player5", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 6) {
        	
            $a->set("player6", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 7) {
        	
            $a->set("player7", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 8) {
        	
            $a->set("player8", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 9) {
        	
            $a->set("player9", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 10) {
        	
            $a->set("player10", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 11) {
        	
            $a->set("player11", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 12) {
        	
            $a->set("player12", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 13) {
        	
            $a->set("player13", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 14) {
        	
            $a->set("player14", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 15) {
        	
            $a->set("player15", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        } else if (count($all) === 16) {
        	
            $a->set("player16", $player->getName());
            $player->setGamemode(0);
            $a->save();
            
        }

    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$player = $event->getPlayer();
        $config = $this->getConfig();
        $event->setQuitMessage(Color::GRAY . "< " . Color::DARK_GRAY . "< " . $player->getDisplayName() . Color::GRAY . " hat den Server verlassen!");
        $a = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/Daten.yml", Config::YAML);
        $a->set("players", $a->get("players") + 1);
        $a->save();
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "SoupIt":
            if (isset($args[0])) {
            	
            	if (strtolower($args[0]) === "lobby") {
            	
            	    if ($sender->isOp()) {
            	
            	        $sender->sendMessage($this->prefix . "Die " . Color::GOLD . "Lobby " . Color::WHITE . "wurde auf deine Position gesetzt!");
                        $config = $this->getConfig();
                        $af = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/Daten.yml", Config::YAML);
                        $x = $sender->getX();
                        $y = $sender->getY();
                        $z = $sender->getZ();
                        $af->set("ingame", false);
                        $af->set("time", 45);
                        $af->set("playtime", 3600);
                        $af->save();
                        
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
            	$af = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/Daten.yml", Config::YAML);
                if ($af->get("ingame") === false) {
                	
                	$af->set("time", 1);
                    $af->save();
                	
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
        $af = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/" . $config->get("Arena") . ".yml", Config::YAML);
        if ($item->getCustomName() === Color::DARK_PURPLE . "Forcemap") {
        	
        	if ($gf->get("NickP") === true) {
        	
        	    $player->getInventory()->clearAll();
                $cubes = Item::get(395, 0, 1);
                $house = Item::get(395, 0, 1);
                $ufo = Item::get(395, 0, 1);
                $cubes->setCustomName(Color::AQUA . "Cubes");
                $house->setCustomName(Color::AQUA . "House");
                $ufo->setCustomName(Color::AQUA . "Ufo");
                $player->getInventory()->setItem(3, $cubes);
                $player->getInventory()->setItem(4, $house);
                $player->getInventory()->setItem(5, $ufo);
                
            } else {
            	
            	$player->sendMessage(Color::RED . "Du hast keine Berechtigung für dieses Item!");
            
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
            
            $player->sendMessage($this->prefix . "Jetzt den 15. Spawn");
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
        $af = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/Daten.yml", Config::YAML);
        if ($af->get("ingame") === false) {
        	
        	$event->setCancelled();
        
        }
        
    }
    
    public function onPlace(BlockPlaceEvent $event) {
    
        $player = $event->getPlayer();
        $config = $this->getConfig();
        $af = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/Daten.yml", Config::YAML);
        if ($af->get("ingame") === false) {
        	
        	$event->setCancelled();
        
        }
        
    }
    
    public function onBreak(BlockBreakEvent $event) {
    
        $player = $event->getPlayer();
        $config = $this->getConfig();
        $af = new Config("/home/EnderCloud/SchwitzerWars/" . $config->get("Server") . "/Daten.yml", Config::YAML);
        if ($af->get("ingame") === false) {
        	
        	$event->setCancelled();
        
        }
        
    }
    
    public function onDeath(PlayerDeathEvent $event) {
    	
    	$player = $event->getPlayer();
        $event->setDeathMessage($this->prefix . Color::GREEN . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
        $event->setDrops(array());
    
    }
    
    public function onRespawn(PlayerRespawnEvent $event) {
    	
    	$player = $event->getPlayer();
        $pos = $player->getPosition();
        $config = $this->getConfig();
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $player->getInventory()->clearAll();
        $playerfile = new Config($this->getDataFolder() . "/players/" . $player->getName() . ".yml", Config::YAML);
        $playerfile->set("teleportt", 2);
        $playerfile->set("teleport", true);
        $playerfile->save();
        
    }
    
    public function giveKit(Player $player) {   	
        	
        $player->getInventory()->clearAll();
        $player->getInventory()->addItem(Item::get(267, 0, 1));
        $player->getInventory()->addItem(Item::get(459, 0, 1));
        $player->getInventory()->setHelmet(Item::get(310, 0, 1));
        $player->getInventory()->setChestplate(Item::get(311, 0, 1));
        $player->getInventory()->setLeggings(Item::get(312, 0, 1));
        $player->getInventory()->setBoots(Item::get(313, 0, 1));
        
    }
    
    public function teleportIngame(Player $player) {
    	$config = $this->getConfig();
        $af = new Config("/home/EnderCloud/SoupIt/" . $config->get("Server") . "/" . $config->get("Arena") . ".yml", Config::YAML);
        $a = new Config("/home/EnderCloud/SoupIt/" . $config->get("Server") . "/Daten.yml", Config::YAML);
        $levelname = $config->get("Arena");
        $level = $this->getServer()->getLevelByName($levelname);
        if ($player->getName() === $a->get("player1")) {
        	
        	$player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
        
        } else if ($player->getName() === $a->get("player2")) {
        	
        	$player->teleport(new Position($af->get("s2x"), $af->get("s2y")+1, $af->get("s2z"), $level));
        
        } else if ($player->getName() === $a->get("player3")) {
        	
        	$player->teleport(new Position($af->get("s3x"), $af->get("s3y")+1, $af->get("s3z"), $level));
        
        } else if ($player->getName() === $a->get("player4")) {
        	
        	$player->teleport(new Position($af->get("s4x"), $af->get("s4y")+1, $af->get("s4z"), $level));
        
        } else if ($player->getName() === $a->get("player5")) {
        	
        	$player->teleport(new Position($af->get("s5x"), $af->get("s5y")+1, $af->get("s5z"), $level));
        
        } else if ($player->getName() === $a->get("player6")) {
        	
        	$player->teleport(new Position($af->get("s6x"), $af->get("s6y")+1, $af->get("s6z"), $level));
        
        } else if ($player->getName() === $a->get("player7")) {
        	
        	$player->teleport(new Position($af->get("s7x"), $af->get("s7y")+1, $af->get("s7z"), $level));
        
        } else if ($player->getName() === $a->get("player8")) {
        	
        	$player->teleport(new Position($af->get("s8x"), $af->get("s8y")+1, $af->get("s8z"), $level));
        
        } else if ($player->getName() === $a->get("player9")) {
        	
        	$player->teleport(new Position($af->get("s9x"), $af->get("s9y")+1, $af->get("s9z"), $level));
        
        } else if ($player->getName() === $a->get("player10")) {
        	
        	$player->teleport(new Position($af->get("s10x"), $af->get("s10y")+1, $af->get("s10z"), $level));
        
        } else if ($player->getName() === $a->get("player11")) {
        	
        	$player->teleport(new Position($af->get("s11x"), $af->get("s11y")+1, $af->get("s11z"), $level));
        
        } else if ($player->getName() === $a->get("player12")) {
        	
        	$player->teleport(new Position($af->get("s12x"), $af->get("s12y")+1, $af->get("s12z"), $level));
        
        } else if ($player->getName() === $a->get("player13")) {
        	
        	$player->teleport(new Position($af->get("s13x"), $af->get("s13y")+1, $af->get("s13z"), $level));
        
        } else if ($player->getName() === $a->get("player14")) {
        	
        	$player->teleport(new Position($af->get("s14x"), $af->get("s14y")+1, $af->get("s14z"), $level));
        
        } else if ($player->getName() === $a->get("player15")) {
        	
        	$player->teleport(new Position($af->get("s15x"), $af->get("s15y")+1, $af->get("s15z"), $level));
        
        } else if ($player->getName() === $a->get("player16")) {
        	
        	$player->teleport(new Position($af->get("s16x"), $af->get("s16y")+1, $af->get("s16z"), $level));
        
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
        $af = new Config("/home/EnderCloud/SoupIt/" . $config->get("Server") . "/" . $config->get("Arena") . ".yml", Config::YAML);
        $a = new Config("/home/EnderCloud/SoupIt/" . $config->get("Server") . "/Daten.yml", Config::YAML);
        $all = $this->plugin->getServer()->getOnlinePlayers();
        if ($a->get("ingame") === false) {

            if (count($all) < 2) {

                foreach ($all as $player) {

                    $player->sendPopup(Color::GRAY . ">> Warten auf weitere Spieler <<");

                }

            }

            if (count($all) >= 2) {

                $a->set("time", $a->get("time") - 1);
                $a->save();
                $time = $a->get("time") + 1;
                if ($time % 5 === 0 && $time > 0) {

                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match startet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 4 || $time === 3 || $time === 2 || $time === 1) {

                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match startet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 0) {

                    $a->set("ingame", true);
                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . "60" . Color::WHITE . " Minuten!");
                        $player->setHealth(20);
                        $player->setFood(20);
                        $this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);

                    }

                    $a->save();

                }

            }

        } else if ($a->get("ingame") === true) {

            $all = $this->plugin->getServer()->getOnlinePlayers();
            if (count($all) <= 1) {

                foreach ($all as $player) {

                    $player->getInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->removeAllEffects();
                    $player->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast das Match gewonnen!");
                    $this->plugin->getServer()->broadcastMessage($this->plugin->prefix . $player->getName() . Color::GREEN . " hat das Match in " . Color::WHITE . $config->get("Arena") . Color::GREEN . " Gewonnen!");
                    $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                    $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                    $player->teleport($spawn, 0, 0);
                    $this->plugin->spawn($player);
                    $a->set("ingame", false);
                    $a->set("reset", true);
                    $a->set("rtime", 15);
                    $a->set("time", 45);
                    $a->set("playtime", 3600);
                    $a->save();
                    $levelname = $config->get("Arena");
                    $lev = $this->plugin->getServer()->getLevelByName($levelname);
                    $this->plugin->getServer()->unloadLevel($lev);
                    $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->getServer()->loadLevel($levelname);

                }

            } elseif (count($all) >= 2) {

                $a->set("playtime", $a->get("playtime") - 1);
                $a->save();
                $time = $a->get("playtime") + 1;
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
                        $this->plugin->spawn($player);
                        $a->set("ingame", false);
                        $a->set("reset", true);
                        $a->set("rtime", 15);
                        $a->set("time", 45);
                        $a->set("playtime", 3600);
                        $a->save();
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
        
        if ($a->get("reset") === true) {

            $a->set("rtime", $a->get("rtime") - 1);
            $a->save();
            $time = $a->get("rtime") + 1;
            foreach ($all as $player) {

                if ($time === 15) {

                    $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

                } else if ($time === 10) {
                	
                	$player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
                
                } else if ($time === 5) {
                	
                	$player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
                
                } else if ($time === 0) {

                    $a->set("reset", false);
                    $a->set("rtime", 15);
                    $a->save();
                    $player->transfer("212.224.125.160", 19132);

                }

            }

        }

        foreach ($all as $player) {

            $playerfile = new Config($this->plugin->getDataFolder() . "/players/" . $player->getName() . ".yml", Config::YAML);
            if ($playerfile->get("teleport") === true) {

                $playerfile->set("teleportt", $playerfile->get("teleportt") - 1);
                $playerfile->save();
                $time = $playerfile->get("teleportt") + 1;
                if ($time === 0) {

                    $playerfile->set("teleport", false);
                    $playerfile->set("teleportt", 2);
                    $playerfile->save();
                    $player->transfer("212.224.125.160", 19132);

                }


            }

        }

    }

}