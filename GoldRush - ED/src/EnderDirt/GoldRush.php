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

class GoldRush extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "GoldRush" . Color::WHITE . "] ";
	public $arenaname = "";
	public $mode = 0;
    
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
    
    public function onLogin(PlayerLoginEvent $event) {
    
        $player = $event->getPlayer();
        if (!is_file("/home/EnderCloud/players/" . $player->getName() . ".yml")) {
        
            $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("teleportt", "1");
            $playerfile->set("teleport", false);
            $playerfile->save();
            
        }
        
    }
    
    public function onJoin(PlayerJoinEvent $event)
    {

        $player = $event->getPlayer();
        $config = $this->getConfig();
        $event->setJoinMessage(Color::GRAY . "> " . Color::DARK_GRAY . "> " . $player->getDisplayName() . Color::GRAY . " hat den Server Betreten!");
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $player->setGamemode(0);
        $player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->clearAll();
        $player->removeAllEffects();
        $player->setAllowFlight(false);
        $all = $this->getServer()->getOnlinePlayers();
        if (count($all) === 1) {
        	
        	$config->set("ingame", false);
            $config->set("time", 60);
            $config->set("playtime", 3600);
            $config->set("player1", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 2) {
        	
            $config->set("player2", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 3) {
        	
            $config->set("player3", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 4) {
        	
            $config->set("player4", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        }
        
    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$player = $event->getPlayer();
        $event->setQuitMessage(Color::GRAY . "< " . Color::DARK_GRAY . "< " . $player->getDisplayName() . Color::GRAY . " hat den Server verlassen!");
        $config = $this->getConfig();
       
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "SchwitzerWars":
            if (isset($args[0])) {
            	
            	if (strtolower($args[0]) === "lobby") {
            	
            	    if ($sender->isOp()) {
            	
            	        $sender->sendMessage($this->prefix . "Die " . Color::GOLD . "Lobby " . Color::WHITE . "wurde auf deine Position gesetzt!");
                        $config = $this->getConfig();
                        $config->set("ingame", false);
                        $config->set("state", false);
                        $config->set("schutz", false);
                        $config->set("time", 60);
                        $config->set("playtime", 3600);
                        $config->set("players", 0);
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
                	
                	$config->set("time", 1);
                    $config->save();
                	
                } else {
                	
                	$sender->sendMessage(Color::RED . "Die Runde hat schon begonnen!");
                
                }
            	
            } else {
            	
            	$sender->sendMessage(Color::RED . "Du hast keine Berechtigung fÃƒÂ¼r diesen befehl!");
            
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
        $rush = Item::get(395, 0, 1);
            $house = Item::get(395, 0, 1);
            $ufo = Item::get(395, 0, 1);
            $cubes = Item::get(395, 0, 1);
            $rush->setCustomName(Color::AQUA . "Rush");
            $house->setCustomName(Color::AQUA . "House");
            $ufo->setCustomName(Color::AQUA . "Ufo");
            $cubes->setCustomName(Color::AQUA . "Cubes");
            $player->getInventory()->setItem(2, $rush);
            $player->getInventory()->setItem(3, $house);
            $player->getInventory()->setItem(4, $ufo);
            $player->getInventory()->setItem(5, $cubes);
            }
        } else if ($item->getCustomName() === Color::AQUA . "House") {
        	
        	$config->set("Arena", "House");
            $config->save();
            $player->sendMessage(Color::WHITE . "Die Map " . Color::GOLD . "House" . Color::WHITE . " wurde erfolgreich ausgesucht!");
            $player->getInventory()->clearAll();
            $set = Item::get(395, 0, 1);
            $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
            $player->getInventory()->setItem(4, $set);
        	
        } else if ($item->getCustomName() === Color::AQUA . "Ufo") {
        	
        	$config->set("Arena", "ufo");
            $config->save();
            $player->sendMessage(Color::WHITE . "Die Map " . Color::GOLD . "Ufo" . Color::WHITE . " wurde erfolgreich ausgesucht!");
            $player->getInventory()->clearAll();
            $set = Item::get(395, 0, 1);
            $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
            $player->getInventory()->setItem(4, $set);
        	
        } else if ($item->getCustomName() === Color::AQUA . "Cubes") {
        	
        	$config->set("Arena", "Cubes");
            $config->save();
            $player->sendMessage(Color::WHITE . "Die Map " . Color::GOLD . "Cubes" . Color::WHITE . " wurde erfolgreich ausgesucht!");
            $player->getInventory()->clearAll();
            $set = Item::get(395, 0, 1);
            $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
            $player->getInventory()->setItem(4, $set);
        	
        } else if ($item->getCustomName() === Color::AQUA . "Rush") {
        	
        	$config->set("Arena", "Rush");
            $config->save();
            $player->sendMessage(Color::WHITE . "Die Map " . Color::GOLD . "Rush" . Color::WHITE . " wurde erfolgreich ausgesucht!");
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
            
        } else if ($this->mode === 3 && $player->isOp()) {
        	
        	$af->set("sb1x", $block->getX());
            $af->set("sb1y", $block->getY());
            $af->set("sb1z", $block->getZ());
            $af->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 2. Block");
            $this->mode++;
            
        } else if ($this->mode === 4 && $player->isOp()) {
        	
        	$af->set("sb2x", $block->getX());
            $af->set("sb2y", $block->getY());
            $af->set("sb2z", $block->getZ());
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
                    
                } else if ($block->getId() === Block::DIAMOND_BLOCK) {
                	
                    $event->setDrops(array());
                	if ($x === $af->get("sb1x") && $y === $af->get("sb1y") && $z === $af->get("sb1z")) {
                	
                	    if ($player->getName() === $config->get("player1")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst deinen RespawnBlock nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::GREEN . "Der " . Color::BLUE . "BLAUE" . Color::GREEN . " RespawnBlock wurde zerstört!");
                            $config->set("block1", false);
                            $config->save();
                            
                        }
                        
                    } else if ($x === $af->get("sb2x") && $y === $af->get("sb2y") && $z === $af->get("sb2z")) {
                	
                	    if ($player->getName() === $config->get("player2")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst deinen RespawnBlock nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::GREEN . "Der " . Color::RED . "ROTE" . Color::GREEN . " RespawnBlock wurde zerstört!");
                            $config->set("block2", false);
                            $config->save();
                            
                        }
                        
                    }
                    
                } else if ($block->getId() === Block::GOLD_BLOCK) {
                	
                    $event->setDrops(array());
                	if ($x === $af->get("sb1x") && $y === $af->get("sb1y") && $z === $af->get("sb1z")) {
                	
                	    if ($player->getName() === $config->get("player1")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst deinen RespawnBlock nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::GREEN . "Der " . Color::BLUE . "BLAUE" . Color::GREEN . " RespawnBlock wurde zerstört!");
                            $config->set("block1", false);
                            $config->save();
                            
                        }
                        
                    } else if ($x === $af->get("sb2x") && $y === $af->get("sb2y") && $z === $af->get("sb2z")) {
                	
                	    if ($player->getName() === $config->get("player2")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst deinen RespawnBlock nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::GREEN . "Der " . Color::RED . "ROTE" . Color::GREEN . " RespawnBlock wurde zerstört!");
                            $config->set("block2", false);
                            $config->save();
                            
                        }
                        
                    }
                    
                } else if ($block->getId() === Block::IRON_BLOCK) {
                	
                    $event->setDrops(array());
                	if ($x === $af->get("sb1x") && $y === $af->get("sb1y") && $z === $af->get("sb1z")) {
                	
                	    if ($player->getName() === $config->get("player1")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst deinen RespawnBlock nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::GREEN . "Der " . Color::BLUE . "BLAUE" . Color::GREEN . " RespawnBlock wurde zerstört!");
                            $config->set("block1", false);
                            $config->save();
                            
                        }
                        
                    } else if ($x === $af->get("sb2x") && $y === $af->get("sb2y") && $z === $af->get("sb2z")) {
                	
                	    if ($player->getName() === $config->get("player2")) {
                	
                	        $event->setCancelled(true);
                            $player->sendMessage($this->prefix . Color::RED . "Du kannst deinen RespawnBlock nicht abbauen!");
                            
                        } else {
                        	
                            $p->sendMessage($this->prefix . Color::GREEN . "Der " . Color::RED . "ROTE" . Color::GREEN . " RespawnBlock wurde zerstört!");
                            $config->set("block2", false);
                            $config->save();
                            
                        }
                        
                    }
                    
                } else if ($block->getId() === Block::RED_SANDSTONE) {
                	
                	$event->setCancelled(false);
                
                } else if ($block->getId() === 30) {
                	
                	$event->setCancelled(false);
                
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
        if ($player->getName() === $config->get("player1")) {
                	
                	if ($config->get("block1") === false) {
                    	
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        
                    }
                    
                } else if ($player->getName() === $config->get("player2")) {
                	
                	if ($config->get("block2") === false) {
                    	
                    	$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->setSpawn($spawn);
                        
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
                        $player->teleport($spawn, 0, 0);
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        
                    }
                    
                } else if ($player->getName() === $config->get("player2")) {
                	
                	if ($config->get("block2") === true) {
                	
                	    $this->giveKit($player);
                         $player->teleport(new Position($af->get("s2x"), $af->get("s2y"), $af->get("s2z"), $level));
                
                    } else if ($config->get("block2") === false) {
                    	
                    	$player->sendMessage($this->prefix . Color::RED . "Du konntest nicht mehr respawnen!");
                        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->teleport($spawn, 0, 0);
                        $player->setSpawn($spawn);
                        $player->getInventory()->clearAll();
                        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("teleportt", 2);
                        $playerfile->set("teleport", true);
                        $playerfile->save();
                        
                    }
                    
                }
                
    }
    
    public function giveKit(Player $player)
    {

        $config = $this->getConfig();
        if ($player->getName() === $config->get("player1")) {

            $helm = Item::get(298, 0, 1);
            $tempTag1 = new CompoundTag("", []);
            $tempTag1->customColor = new IntTag("customColor", 0x0000FF);
            $helm->setCompoundTag($tempTag1);

            $chest = Item::get(299, 0, 1);
            $tempTag2 = new CompoundTag("", []);
            $tempTag2->customColor = new IntTag("customColor", 0x0000FF);
            $chest->setCompoundTag($tempTag2);

            $leg = Item::get(300, 0, 1);
            $tempTag3 = new CompoundTag("", []);
            $tempTag3->customColor = new IntTag("customColor", 0x0000FF);
            $leg->setCompoundTag($tempTag3);

            $boots = Item::get(301, 0, 1);
            $tempTag4 = new CompoundTag("", []);
            $tempTag4->customColor = new IntTag("customColor", 0x0000FF);
            $boots->setCompoundTag($tempTag4);

            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(Item::STICK, 0, 1));
            $player->getInventory()->addItem(Item::get(268, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::DIAMOND_PICKAXE, 0, 1));
            $player->getInventory()->addItem(Item::get(261, 0, 1));
            $player->getInventory()->addItem(Item::get(179, 0, 64));
            $player->getInventory()->addItem(Item::get(179, 0, 64));
            $player->getInventory()->addItem(Item::get(262, 0, 12));
            $player->getInventory()->setHelmet($helm);
            $player->getInventory()->setChestplate($chest);
            $player->getInventory()->setLeggings($leg);
            $player->getInventory()->setBoots($boots);

        } else if ($player->getName() === $config->get("player2")) {

            $helm = Item::get(298, 0, 1);
            $tempTag1 = new CompoundTag("", []);
            $tempTag1->customColor = new IntTag("customColor", 0xFF0000);
            $helm->setCompoundTag($tempTag1);

            $chest = Item::get(299, 0, 1);
            $tempTag2 = new CompoundTag("", []);
            $tempTag2->customColor = new IntTag("customColor", 0xFF0000);
            $chest->setCompoundTag($tempTag2);

            $leg = Item::get(300, 0, 1);
            $tempTag3 = new CompoundTag("", []);
            $tempTag3->customColor = new IntTag("customColor", 0xFF0000);
            $leg->setCompoundTag($tempTag3);

            $boots = Item::get(301, 0, 1);
            $tempTag4 = new CompoundTag("", []);
            $tempTag4->customColor = new IntTag("customColor", 0xFF0000);
            $boots->setCompoundTag($tempTag4);

            $player->getInventory()->clearAll();
            $player->getInventory()->addItem(Item::get(Item::STICK, 0, 1));
            $player->getInventory()->addItem(Item::get(268, 0, 1));
            $player->getInventory()->addItem(Item::get(Item::DIAMOND_PICKAXE, 0, 1));
            $player->getInventory()->addItem(Item::get(261, 0, 1));
            $player->getInventory()->addItem(Item::get(179, 0, 64));
            $player->getInventory()->addItem(Item::get(179, 0, 64));
            $player->getInventory()->addItem(Item::get(262, 0, 12));
            $player->getInventory()->setHelmet($helm);
            $player->getInventory()->setChestplate($chest);
            $player->getInventory()->setLeggings($leg);
            $player->getInventory()->setBoots($boots);

        }

    }
    
    public function spawn(Player $player) {
    	
    	$pos = $player->getPosition();
        $player->setSpawn($pos);
        
    }
    
    public function teleportIngame(Player $player) {
    	
    	$config = $this->getConfig();
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
        $config->set("players", count($all));
        $config->save();
    	
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

            if (count($all) < 2) {

                foreach ($all as $player) {

                    $player->sendPopup(Color::GRAY . ">> Warten auf weitere Spieler <<");

                }

            }

            if (count($all) >= 2) {

                $config->set("time", $config->get("time") - 1);
                $config->save();
                $time = $config->get("time") + 1;
                if ($time % 5 === 0 && $time > 0) {

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
                    $player->sendMessage(Color::WHITE . "[" . Color::DARK_PURPLE . "+" . Color::WHITE . "] 200 Coins");
                    $pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
                    $pc->set("coins", $pc->get("coins")+200);
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
                    $config->save();
                    $levelname = $config->get("Arena");
                    $lev = $this->plugin->getServer()->getLevelByName($levelname);
                    $this->plugin->getServer()->unloadLevel($lev);
                    $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                    $this->plugin->getServer()->loadLevel($levelname);

                }

            } elseif (count($all) >= 2) {

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
                        $config->set("ingame", false);
                        $config->set("reset", true);
                        $config->set("rtime", 10);
                        $config->set("time", 60);
                        $config->set("playtime", 3600);
                        $config->set("block1", false);
                    $config->set("block2", false);
                    $config->set("player1", "");
                    $config->set("player2", "");
                        $config->save();
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
            foreach ($all as $player) {

               if ($time === 10) {
                	
                	$player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
                    $player->sendMessage(Color::GOLD . "Bitte Warte bis der Lobby Timer beendet wurde!");
                
                } else if ($time === 5) {
                	
                	$player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
                
                } else if ($time === 0) {

                    $config->set("reset", false);
                    $config->set("rtime", 10);
                    $config->set("state", false);
                    $config->save();
                    $player->transfer("5.230.146.16", 19132);

                }

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
                    $playerfile->set("teleportt", 1);
                    $playerfile->save();
                    $player->transfer("5.230.146.16", 19132);

                }


            }

        }

    }

}