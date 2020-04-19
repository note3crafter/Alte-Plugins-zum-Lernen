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
use pocketmine\level\sound\EndermanTeleportSound;
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

class BedWars extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::AQUA . "BedWars" . Color::WHITE . "] ";
	public $arenaname = "";
	public $mode = 0;
	public $players = 0;
	
    public function onEnable() {
    	
	    if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
        
        if (is_dir("/home/EnderCloud/BedWars/players") !== true) {
			
            mkdir("/home/EnderCloud/BedWars/players");
            
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
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new DropBronze($this), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new DropIron($this), 250);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new DropGold($this), 600);
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
        if (!is_file("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml")) {
        	
        	$playerfile = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Slot", 0);
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
        	
        	$pff = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        	$event->setJoinMessage(Color::GRAY . "> " . Color::DARK_GRAY . "> " . $player->getDisplayName() . Color::GRAY . " hat den Server Betreten!");
        
        if ($this->players === 0) {
        	
        	$this->players++;
        	$config->set("ingame", false);
            $config->set("time", 20);
            $config->set("playtime", 3600);
            $config->set("player1", $player->getName());
            $config->set("block1", true);
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 1) {
        	
        	$this->players++;
            $config->set("player2", $player->getName());
            $config->set("block2", true);
            $player->setGamemode(0);
            $config->save();
            
        } else if ($this->players === 2) {
        	
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
                
                $config->set("player1", $p2);
                $config->set("player2", "");
                $config->save();
                
            } else if ($player->getName() === $config->get("player2")) {
            	
            	$this->players--;
                
                $config->set("player2", "");
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
        
            }
            
            }
        	
        }
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "BedWars":
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
        	
        	    if (file_exists("/home/EnderCloud/BedWars/players/" . $args[0] . ".yml")) {
        	
        	        $pf = new Config("/home/EnderCloud/BedWars/players/" . $args[0] . ".yml", Config::YAML);
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
            	
            	$pf = new Config("/home/EnderCloud/BedWars/players/" . $sender->getName() . ".yml", Config::YAML);
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
        $block = $event->getBlock();
        $tile = $player->getLevel()->getTile($block);
        $config = $this->getConfig();
        $item = $player->getInventory()->getItemInHand();
        $gf = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        if ($item->getCustomName() === Color::DARK_PURPLE . "Einstellungen") {
        	
        	if ($gf->get("NickP") === true) {
        	
        	$player->getInventory()->clearAll();
            $pokemon = Item::get(395, 0, 1);
            $pokemon->setCustomName(Color::AQUA . "Pokemon");
            $player->getInventory()->setItem(4, $pokemon);
            
            }
        } else if ($item->getCustomName() === Color::GOLD . "Rettungs Plattform") {
        	
            $player->getInventory()->removeItem($item);
            $x = $player->getX();
            $y = $player->getY();
            $z = $player->getZ();
            $player->getLevel()->setBlock(new Vector3($x,($y-4),$z), Block::get(30));
            $player->getLevel()->setBlock(new Vector3($x,($y-5),$z), Block::get(179));
        	
        } else if ($item->getCustomName() === Color::AQUA . "Pokemon") {
        	
        	$config->set("Arena", "Pokemon");
            $config->save();
            $player->sendMessage(Color::WHITE . "Die Map " . Color::GOLD . "Pokemon" . Color::WHITE . " wurde erfolgreich ausgesucht!");
            $player->getInventory()->clearAll();
            $set = Item::get(395, 0, 1);
            $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
            $player->getInventory()->setItem(4, $set);
        	
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
            
            $player->sendMessage($this->prefix . "Jetzt den 1. Block");
            $this->mode++;
            
        }  else if ($this->mode === 3 && $player->isOp()) {
        	
        	if ($player->getLevel()->getBlock(new Vector3($block->getX() + 1, $block->getY(), $block->getZ()))->getId() == 26) {
        	
        	    $block2 = $player->getLevel()->getBlock(new Vector3($block->getX() + 1, $block->getY(), $block->getZ()));
        	    $af->set("sb1x", $block->getX());
                $af->set("sb1y", $block->getY());
                $af->set("sb1z", $block->getZ());
                $af->set("sb1x1", $block2->getX());
                $af->set("sb1y1", $block2->getY());
                $af->set("sb1z1", $block2->getZ());
                $af->save();
                
            }
            
            if ($player->getLevel()->getBlock(new Vector3($block->getX() - 1, $block->getY(), $block->getZ()))->getId() == 26) {
        	
        	    $block2 = $player->getLevel()->getBlock(new Vector3($block->getX() - 1, $block->getY(), $block->getZ()));
        	    $af->set("sb1x", $block->getX());
                $af->set("sb1y", $block->getY());
                $af->set("sb1z", $block->getZ());
                $af->set("sb1x1", $block2->getX());
                $af->set("sb1y1", $block2->getY());
                $af->set("sb1z1", $block2->getZ());
                $af->save();
                
            }
            
            if ($player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $block->getZ() + 1))->getId() == 26) {
        	
        	    $block2 = $player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $block->getZ() + 1));
        	    $af->set("sb1x", $block->getX());
                $af->set("sb1y", $block->getY());
                $af->set("sb1z", $block->getZ());
                $af->set("sb1x1", $block2->getX());
                $af->set("sb1y1", $block2->getY());
                $af->set("sb1z1", $block2->getZ());
                $af->save();
                
            }
            
            if ($player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $block->getZ() - 1))->getId() == 26) {
        	
        	    $block2 = $player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $block->getZ() - 1));
        	    $af->set("sb1x", $block->getX());
                $af->set("sb1y", $block->getY());
                $af->set("sb1z", $block->getZ());
                $af->set("sb1x1", $block2->getX());
                $af->set("sb1y1", $block2->getY());
                $af->set("sb1z1", $block2->getZ());
                $af->save();
                
            }
            
            $player->sendMessage($this->prefix . "Jetzt den 2. Block");
            $this->mode++;
            
        } else if ($this->mode === 4 && $player->isOp()) {
        	
        	if ($player->getLevel()->getBlock(new Vector3($block->getX() + 1, $block->getY(), $block->getZ()))->getId() == 26) {
        	
        	    $block2 = $player->getLevel()->getBlock(new Vector3($block->getX() + 1, $block->getY(), $block->getZ()));
        	    $af->set("sb2x", $block->getX());
                $af->set("sb2y", $block->getY());
                $af->set("sb2z", $block->getZ());
                $af->set("sb2x1", $block2->getX());
                $af->set("sb2y1", $block2->getY());
                $af->set("sb2z1", $block2->getZ());
                $af->save();
                
            }
            
            if ($player->getLevel()->getBlock(new Vector3($block->getX() - 1, $block->getY(), $block->getZ()))->getId() == 26) {
        	
        	    $block2 = $player->getLevel()->getBlock(new Vector3($block->getX() - 1, $block->getY(), $block->getZ()));
        	    $af->set("sb2x", $block->getX());
                $af->set("sb2y", $block->getY());
                $af->set("sb2z", $block->getZ());
                $af->set("sb2x1", $block2->getX());
                $af->set("sb2y1", $block2->getY());
                $af->set("sb2z1", $block2->getZ());
                $af->save();
                
            }
            
            if ($player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $block->getZ() + 1))->getId() == 26) {
        	
        	    $block2 = $player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $block->getZ() + 1));
        	    $af->set("sb2x", $block->getX());
                $af->set("sb2y", $block->getY());
                $af->set("sb2z", $block->getZ());
                $af->set("sb2x1", $block2->getX());
                $af->set("sb2y1", $block2->getY());
                $af->set("sb2z1", $block2->getZ());
                $af->save();
                
            }
            
            if ($player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $block->getZ() - 1))->getId() == 26) {
        	
        	    $block2 = $player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $block->getZ() - 1));
        	    $af->set("sb2x", $block->getX());
                $af->set("sb2y", $block->getY());
                $af->set("sb2z", $block->getZ());
                $af->set("sb2x1", $block2->getX());
                $af->set("sb2y1", $block2->getY());
                $af->set("sb2z1", $block2->getZ());
                $af->save();
                
            }
            
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
        
        } else {
        	
        	if ($event instanceof EntityDamageByEntityEvent) {
        	
        	    $damager = $event->getDamager();
                if ($damager instanceof Player) {
                	
                	$pf = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
                    $pf->set("Damager", $damager->getName());
                    $pf->save();
                	
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
                	
                	    if ($player->getName() === $config->get("player1")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::WHITE . "Das " . Color::BLUE . "BLAUE" . Color::WHITE . " Bett wurde zerstoert!");
                            $config->set("block1", false);
                            $config->save();
                            $p2 = $this->getServer()->getPlayerExact($config->get("player1"));
                            $player->getLevel()->addSound(new EndermanTeleportSound($player), $player->getLevel()->getPlayers());
                            if (!$p2 == null) {
                            	
                            	$p2->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                        }
                        
                    } else if ($x === $af->get("sb1x1") && $y === $af->get("sb1y1") && $z === $af->get("sb1z1")) {
                	
                	    if ($player->getName() === $config->get("player1")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::WHITE . "Das " . Color::BLUE . "BLAUE" . Color::WHITE . " Bett wurde zerstoert!");
                            $config->set("block1", false);
                            $config->save();
                            $p2 = $this->getServer()->getPlayerExact($config->get("player1"));
                            $player->getLevel()->addSound(new EndermanTeleportSound($player), $player->getLevel()->getPlayers());
                            if (!$p2 == null) {
                            	
                            	$p2->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                        }
                        
                    } else if ($x === $af->get("sb2x") && $y === $af->get("sb2y") && $z === $af->get("sb2z")) {
                	
                	    if ($player->getName() === $config->get("player2")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::WHITE . "Das " . Color::RED . "ROTE" . Color::WHITE . " Bett wurde zerstoert!");
                            $config->set("block2", false);
                            $config->save();
                            $p2 = $this->getServer()->getPlayerExact($config->get("player2"));
                            $player->getLevel()->addSound(new EndermanTeleportSound($player), $player->getLevel()->getPlayers());
                            if (!$p2 == null) {
                            	
                            	$p2->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
                            }
                            
                        }
                        
                    } else if ($x === $af->get("sb2x1") && $y === $af->get("sb2y1") && $z === $af->get("sb2z1")) {
                	
                	    if ($player->getName() === $config->get("player2")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst dein Bett nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::WHITE . "Das " . Color::RED . "ROTE" . Color::WHITE . " Bett wurde zerstoert!");
                            $config->set("block2", false);
                            $config->save();
                            $p2 = $this->getServer()->getPlayerExact($config->get("player2"));
                            $player->getLevel()->addSound(new EndermanTeleportSound($player), $player->getLevel()->getPlayers());
                            if (!$p2 == null) {
                            	
                            	$p2->addTitle(Color::RED . "Dein Bett wurde", Color::RED . "zerstoert!", 20, 40, 20);
                            	
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
        $pff = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        $event->setDeathMessage($this->prefix . Color::GREEN . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
        $event->setDrops(array());
        $config = $this->getConfig();
        if ($player->getName() === $config->get("player1")) {
                	
                	if ($config->get("block1") === false) {
                    	
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        $pf = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
                        $pff = new Config("/home/EnderCloud/BedWars/players/" . $pf->get("Damager") . ".yml", Config::YAML);
                        $pff->set("Kills", $pff->get("Kills")+1);
                        $pff->save();
                        $pf->set("Deaths", $pf->get("Deaths")+1);
                        $pf->save();
                        $this->delPlayer($player);
                        $this->players--;
                        
                    }
                    
                } else if ($player->getName() === $config->get("player2")) {
                	
                	if ($config->get("block2") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        $pf = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
                        $pff = new Config("/home/EnderCloud/BedWars/players/" . $pf->get("Damager") . ".yml", Config::YAML);
                        $pff->set("Kills", $pff->get("Kills")+1);
                        $pff->save();
                        $pf->set("Deaths", $pf->get("Deaths")+1);
                        $pf->save();
                        $this->delPlayer($player);
                        $this->players--;
                        
                    }
                    
                } else if ($player->getName() === $config->get("player3")) {
                	
                	if ($config->get("block3") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        $pf = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
                        $pff = new Config("/home/EnderCloud/BedWars/players/" . $pf->get("Damager") . ".yml", Config::YAML);
                        $pff->set("Kills", $pff->get("Kills")+1);
                        $pff->save();
                        $pf->set("Deaths", $pf->get("Deaths")+1);
                        $pf->save();
                        $this->delPlayer($player);
                        $this->players--;
                        
                    }
                    
                } else if ($player->getName() === $config->get("player4")) {
                	
                	if ($config->get("block4") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->setGamemode(3);
                        $pf = new Config("/home/EnderCloud/BedWars/players/" . $player->getName() . ".yml", Config::YAML);
                        $pff = new Config("/home/EnderCloud/BedWars/players/" . $pf->get("Damager") . ".yml", Config::YAML);
                        $pff->set("Kills", $pff->get("Kills")+1);
                        $pff->save();
                        $pf->set("Deaths", $pf->get("Deaths")+1);
                        $pf->save();
                        $this->delPlayer($player);
                        $this->players--;
                        
                    }
                    
                }
    }
    
    public function onRespawn(PlayerRespawnEvent $event) {
    	
    	$player = $event->getPlayer();
        $pos = $player->getPosition();
        $config = $this->getConfig();
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        if ($player->getName() === $config->get("player1")) {
                	
                	if ($config->get("block1") === true) {
                	
                	    $this->giveKit($player);
                        $player->teleport(new Position($af->get("s1x"), $af->get("s1y"), $af->get("s1z"), $level));
                
                    } else if ($config->get("block1") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        
                    }
                    
                } else if ($player->getName() === $config->get("player2")) {
                	
                	if ($config->get("block2") === true) {
                	
                	    $this->giveKit($player);
                         $player->teleport(new Position($af->get("s2x"), $af->get("s2y"), $af->get("s2z"), $level));
                
                    } else if ($config->get("block2") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        
                    }
                    
                } else if ($player->getName() === $config->get("player3")) {
                	
                	if ($config->get("block3") === true) {
                	
                	    $this->giveKit($player);
                         $player->teleport(new Position($af->get("s3x"), $af->get("s3y"), $af->get("s3z"), $level));
                
                    } else if ($config->get("block3") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        
                    }
                    
                } else if ($player->getName() === $config->get("player4")) {
                	
                	if ($config->get("block4") === true) {
                	
                	    $this->giveKit($player);
                        $player->teleport(new Position($af->get("s4x"), $af->get("s4y"), $af->get("s4z"), $level));
                
                    } else if ($config->get("block4") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        
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
        if (!$this->getServer()->getLevelByName($config->get("Arena")) instanceof Level) {
        	
            $this->getServer()->loadLevel($config->get("Arena"));
            
        }
        
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        if ($player->getName() === $config->get("player1")) {
        	
        	$player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
        
        } else if ($player->getName() === $config->get("player2")) {
            
        	$player->teleport(new Position($af->get("s2x"), $af->get("s2y")+1, $af->get("s2z"), $level));
        
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
                $config->set("time", 20);
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
                	
                	$player->sendPopup(
                       Color::YELLOW . "Spieler: " . Color::WHITE . $this->plugin->players . Color::YELLOW . "/" . Color::WHITE . "2" . "\n" .
                       Color::GREEN . "Map: " . Color::WHITE . $config->get("Arena")
                    );
                	
                }
                if ($time % 5 === 0 && $time > 0) {

                    foreach ($all as $player) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match startet in " . Color::AQUA . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 15) {
                
                	$config->set("state", true);
                    $config->save();
                	foreach ($all as $player) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match startet in " . Color::AQUA . $time . Color::WHITE . " Sekunden!");

                    }
                	
                } else if ($time === 4 || $time === 3 || $time === 2 || $time === 1) {

                    foreach ($all as $player) {
                    	
                        $level = $this->plugin->getServer()->getLevelByName($config->get("Arena"));
                        $level->setTime(0);
                        $level->stopTime();
                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match startet in " . Color::AQUA . $time . Color::WHITE . " Sekunden!");

                    }

                } else if ($time === 0) {

                    $config->set("ingame", true);
                    $config->set("state", true);
                    foreach ($all as $player) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match startet in " . Color::AQUA . "60" . Color::WHITE . " Minuten!");
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
                	
                    if ($config->get("player1") === "") {
                    	
                    	$p = $config->get("player2");
                        $p2 = $this->plugin->getServer()->getPlayerExact($p);
                        if (!$p2 == null) {
                        	
                        	$player->addTitle(Color::GRAY . $p, Color::GREEN . "hat Gewonnen", 20, 40, 20);
                            $pc = new Config("/home/EnderCloud/eCoins/" . $p2->getName() . ".yml", Config::YAML);
                            $pc->set("coins", $pc->get("coins")+100);
                            $pc->save();
                            $pf = new Config("/home/EnderCloud/BedWars/players/" . $p2->getName() . ".yml", Config::YAML);
                            $pf->set("Wins", $pf->get("Wins") + 1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                    if ($config->get("player2") === "") {
                    	
                    	$p = $config->get("player1");
                        $p2 = $this->plugin->getServer()->getPlayerExact($p);
                        if (!$p2 == null) {
                        	
                        	$player->addTitle(Color::GRAY . $p, Color::GREEN . "hat Gewonnen", 20, 40, 20);
                            $pc = new Config("/home/EnderCloud/eCoins/" . $p2->getName() . ".yml", Config::YAML);
                            $pc->set("coins", $pc->get("coins")+100);
                            $pc->save();
                            $pf = new Config("/home/EnderCloud/BedWars/players/" . $p2->getName() . ".yml", Config::YAML);
                            $pf->set("Wins", $pf->get("Wins") + 1);
                            $pf->save();
                        	
                        }
                    	
                    }
                    
                    $player->getInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->removeAllEffects();
                    $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                    $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                    $player->teleport($spawn, 0, 0);
                    $config->set("ingame", false);
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 20);
                    $config->set("playtime", 3600);
                    $config->set("block1", false);
                    $config->set("block2", false);
                    $config->set("player1", "");
                    $config->set("player2", "");
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
                	
                    if ($time === 60) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match endet in " . Color::AQUA . $time . Color::WHITE . " Sekunden!");

                    } else if ($time === 1 || $time === 2 || $time === 3 || $time === 4 || $time === 5 || $time === 15 || $time === 30) {

                        $player->sendMessage($this->plugin->prefix . Color::WHITE . "Das Match endet in " . Color::AQUA . $time . Color::WHITE . " Sekunden!");

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
                        $config->set("time", 20);
                        $config->set("playtime", 3600);
                        $config->set("block1", false);
                    $config->set("block2", false);
                    $config->set("player1", "");
                    $config->set("player2", "");
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