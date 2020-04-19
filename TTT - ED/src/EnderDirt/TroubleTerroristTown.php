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
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
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
//Partikel
use pocketmine\level\particle\FloatingTextParticle;

class TroubleTerroristTown extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::RED . "TTT" . Color::WHITE . "] ";
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
        if (!is_file("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml")) {
        
            $playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("teleportt", "1");
            $playerfile->set("teleport", false);
            $playerfile->set("Karma", 500);
            $playerfile->set("Traitor", 1);
            $playerfile->set("Pass", false);
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
        $pass = Item::get(355, 10, 1);
        $set = Item::get(395, 0, 1);
        $pass->setCustomName(Color::DARK_PURPLE . "Paesse");
        $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
        $player->getInventory()->setItem(0, $pass);
        $player->getInventory()->setItem(4, $set);
        $player->removeAllEffects();
        $player->setAllowFlight(false);
        $all = $this->getServer()->getOnlinePlayers();
        if ($config->get("ingame") === true) {
        	
        	$player->transfer("84.200.84.61", 19132);
        
        } else {
        	
        if (count($all) === 1) {
        	
        	$config->set("ingame", false);
            $config->set("time", 60);
            $config->set("playtime", 1200);
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
            
        } else if (count($all) === 5) {
        	
            $config->set("player5", $player->getName());
            $player->setGamemode(0);         
            $config->save();
            
        } else if (count($all) === 6) {
        	
            $config->set("player6", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 7) {
        	
            $config->set("player7", $player->getName());
            $player->setGamemode(0);         
            $config->save();
            
        } else if (count($all) === 8) {
        	
            $config->set("player8", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 9) {
        	
            $config->set("player9", $player->getName());
            $player->setGamemode(0);         
            $config->save();
            
        } else if (count($all) === 10) {
        	
            $config->set("player10", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 11) {
        	
            $config->set("player11", $player->getName());
            $player->setGamemode(0);         
            $config->save();
            
        } else if (count($all) === 12) {
        	
            $config->set("player12", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) > 12) {
        	
        	$player->transfer("84.200.84.61", 19132);
        	
        }
        
        }
        
    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$player = $event->getPlayer();
        $event->setQuitMessage(Color::GRAY . "< " . Color::DARK_GRAY . "< " . $player->getDisplayName() . Color::GRAY . " hat den Server verlassen!");
        $config = $this->getConfig();
        $all = $this->getServer()->getOnlinePlayers();
        if ($player->getName() === $config->get("player1")) {

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
            $config->save();
            $config->set("player2", $p3);
            $config->save();
            $config->set("player3", $p4);
            $config->save();
            $config->set("player4", $p5);
            $config->save();
            $config->set("player5", $p6);
            $config->save();
            $config->set("player6", $p7);
            $config->save();
            $config->set("player7", $p8);
            $config->save();
            $config->set("player8", $p9);
            $config->save();
            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player2")) {

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
            $config->save();
            $config->set("player3", $p4);
            $config->save();
            $config->set("player4", $p5);
            $config->save();
            $config->set("player5", $p6);
            $config->save();
            $config->set("player6", $p7);
            $config->save();
            $config->set("player7", $p8);
            $config->save();
            $config->set("player8", $p9);
            $config->save();
            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player3")) {

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
            $config->save();
            $config->set("player4", $p5);
            $config->save();
            $config->set("player5", $p6);
            $config->save();
            $config->set("player6", $p7);
            $config->save();
            $config->set("player7", $p8);
            $config->save();
            $config->set("player8", $p9);
            $config->save();
            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player4")) {

            $p5 = $config->get("player5");
            $p6 = $config->get("player6");
            $p7 = $config->get("player7");
            $p8 = $config->get("player8");
            $p9 = $config->get("player9");
            $p10 = $config->get("player10");
            $p11 = $config->get("player11");
            $p12 = $config->get("player12");

            $config->set("player4", $p5);
            $config->save();
            $config->set("player5", $p6);
            $config->save();
            $config->set("player6", $p7);
            $config->save();
            $config->set("player7", $p8);
            $config->save();
            $config->set("player8", $p9);
            $config->save();
            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player5")) {

            $p6 = $config->get("player6");
            $p7 = $config->get("player7");
            $p8 = $config->get("player8");
            $p9 = $config->get("player9");
            $p10 = $config->get("player10");
            $p11 = $config->get("player11");
            $p12 = $config->get("player12");

            $config->set("player5", $p6);
            $config->save();
            $config->set("player6", $p7);
            $config->save();
            $config->set("player7", $p8);
            $config->save();
            $config->set("player8", $p9);
            $config->save();
            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player6")) {

            $p7 = $config->get("player7");
            $p8 = $config->get("player8");
            $p9 = $config->get("player9");
            $p10 = $config->get("player10");
            $p11 = $config->get("player11");
            $p12 = $config->get("player12");

            $config->set("player6", $p7);
            $config->save();
            $config->set("player7", $p8);
            $config->save();
            $config->set("player8", $p9);
            $config->save();
            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player7")) {

            $p8 = $config->get("player8");
            $p9 = $config->get("player9");
            $p10 = $config->get("player10");
            $p11 = $config->get("player11");
            $p12 = $config->get("player12");

            $config->set("player7", $p8);
            $config->save();
            $config->set("player8", $p9);
            $config->save();
            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player8")) {

            $p9 = $config->get("player9");
            $p10 = $config->get("player10");
            $p11 = $config->get("player11");
            $p12 = $config->get("player12");

            $config->set("player8", $p9);
            $config->save();
            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player9")) {

            $p10 = $config->get("player10");
            $p11 = $config->get("player11");
            $p12 = $config->get("player12");

            $config->set("player9", $p10);
            $config->save();
            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player10")) {

            $p11 = $config->get("player11");
            $p12 = $config->get("player12");

            $config->set("player10", $p11);
            $config->save();
            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player11")) {

            $p12 = $config->get("player12");

            $config->set("player11", $p12);
            $config->save();
            $config->set("player12", "");
            $config->save();

        } else if ($player->getName() === $config->get("player12")) {

            $config->set("player12", "");
            $config->save();

        }
       
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "TTT":
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
        	
        	$pf = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
            if ($pf->get("VIP") === true) {
            	
            	$config = $this->getConfig();
                if ($config->get("ingame") === false) {
                	
                	$config->set("time", 15);
                    $config->save();
                	
                } else {
                	
                	$sender->sendMessage(Color::RED . "Die Runde hat schon begonnen!");
                
                }
            	
            } else {
            	
            	$sender->sendMessage(Color::RED . "Du hast keine Berechtigung fÃ¼r diesen befehl!");
            
            }
            
        }
        
        if ($command->getName() === "Pass") {
        	
        	$playerfile = new Config("/home/EnderCloud/TTT/players/" . $sender->getName() . ".yml", Config::YAML);
            $sender->sendMessage($this->prefix . "Du hast: " . Color::RED . $playerfile->get("Traitor") . Color::WHITE . " Traitor Paesse");
        	
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
        $x = $block->getX();
        $y = $block->getY();
        $z = $block->getZ();
        $playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
        if ($block->getId() === Block::CHEST) {
        	
        	$i = mt_rand(1, 3);
            if ($i === 1) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(272, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 2) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(268, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            } else if ($i === 3) {
            	
            	$event->setCancelled(true);
            	$player->getInventory()->addItem(Item::get(261, 0, 1));
                $player->getInventory()->addItem(Item::get(262, 0, 32));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
            	
            }
        	
        } else if ($block->getId() === Block::ENDER_CHEST) {
        	
        	if ($config->get("schutz") === false) {
        	
        	    $event->setCancelled(true);
        	    $player->getInventory()->addItem(Item::get(267, 0, 1));
                $player->getLevel()->setBlock(new Vector3($x,($y),$z), Block::get(Block::AIR));
                
            } else {
            	
            	$event->setCancelled(true);
            	$player->sendMessage($this->prefix . Color::RED . "Warte bis die Schutzzeit vorbei ist");
            	
            }
        	
        }
        
        if ($item->getCustomName() === Color::DARK_PURPLE . "Einstellungen") {
        	
        	if ($gf->get("NickP") === true) {
        	
        	$player->getInventory()->clearAll();
            $courch = Item::get(395, 0, 1);
            $courch->setCustomName(Color::AQUA . "Courch");
            $player->getInventory()->setItem(4, $courch);
            
            }
            
        } else if ($item->getCustomName() === Color::AQUA . "Courch") {
        	
        	$config->set("Arena", "Courch");
            $config->save();
            $player->sendMessage(Color::WHITE . "Die Map " . Color::GOLD . "Courch" . Color::WHITE . " wurde erfolgreich ausgesucht!");
            $player->getInventory()->clearAll();
            $pass = Item::get(355, 10, 1);
            $set = Item::get(395, 0, 1);
            $pass->setCustomName(Color::DARK_PURPLE . "Paesse");
            $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
            $player->getInventory()->setItem(0, $pass);
            $player->getInventory()->setItem(4, $set);
        	
        } else if ($item->getCustomName() === Color::DARK_PURPLE . "Paesse") {
        	
            $player->getInventory()->clearAll();
            $t = Item::get(395, 0, 1);
            $t->setCustomName(Color::RED . "Traitor");
            $player->getInventory()->setItem(4, $t);
        	
        } else if ($item->getCustomName() === Color::RED . "Traitor") {

            if ($playerfile->get("Traitor") > 0) {

                $player->getInventory()->clearAll();
                $pass = Item::get(355, 10, 1);
                $set = Item::get(395, 0, 1);
                $pass->setCustomName(Color::DARK_PURPLE . "Paesse");
                $set->setCustomName(Color::DARK_PURPLE . "Einstellungen");
                $player->getInventory()->setItem(0, $pass);
                $player->getInventory()->setItem(4, $set);
                if ($playerfile->get("Pass") === true) {

                    $player->sendMessage(Color::RED . "Du hast schon einen Pass eingeloest");

                } else {
                	
                    if ($config->get("t1") === true) {

                        if ($config->get("t2") === true) {

                            $player->sendMessage(Color::RED . "Ein Fehler ist aufgetreten");

                        } else if ($config->get("t2") === false) {

                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                            $playerfile->set("Traitor", $playerfile->get("Traitor") - 1);
                            $playerfile->set("Pass", true);
                            $playerfile->save();
                            $player->sendMessage(Color::GREEN . "Der Pass wurde erfolgreich eingeloest");

                        }

                    } else if ($config->get("t1") === false) {

                        $config->set("t2", true);
                        $config->set("tn2", $player->getName());
                        $config->save();
                        $playerfile->set("Traitor", $playerfile->get("Traitor") - 1);
                        $playerfile->set("Pass", true);
                        $playerfile->save();
                        $player->sendMessage(Color::GREEN . "Der Pass wurde erfolgreich eingeloest");

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
    
        $event->setCancelled(true);
        
    }
    
    public function onBreak(BlockBreakEvent $event) {
    
        $event->setCancelled(true);
        
    }
    
    public function onDeath(PlayerDeathEvent $event) {
    	
    	$player = $event->getEntity();
        $event->setDrops(array());
        $config = $this->getConfig();
        $x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
        if ($player instanceof Player) {

            $cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {

                $killer = $cause->getDamager();
                if ($killer instanceof Player) {

                    $pf = new Config("/home/EnderCloud/TTT/players/" . $killer->getName() . ".yml", Config::YAML);
                    if ($killer->getName() === $config->get("tn1")) {

                        if ($player->getName() === $config->get("tn2")) {

                            $pf->set("Karma", $pf->get("Karma")-100);
                            $pf->save();
                            $killer->sendMessage($this->prefix . "- 100 " . Color::YELLOW . "Karma");
                            $killer->sendMessage($this->prefix . "Da du deinen Team Traitor getoetet hast bkommst du minus Karma");

                        } else {

                            $pf->set("Karma", $pf->get("Karma")+50);
                            $pf->save();
                            if ($pf->get("Karma") === 1000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 1000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 2000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 2000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 5000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 5000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 10000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 10000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 50000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 50000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 100000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 100000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 1000000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+100);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 1000000 Karma hast, erhaeltst du 100 Traitor Pass");

                            } else {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");

                            }

                        }

                    } else if ($killer->getName() === $config->get("tn2")) {

                        if ($player->getName() === $config->get("tn1")) {

                            $pf->set("Karma", $pf->get("Karma")-100);
                            $pf->save();
                            $killer->sendMessage($this->prefix . "- 100 " . Color::YELLOW . "Karma");
                            $killer->sendMessage($this->prefix . "Da du deinen Team Traitor getoetet hast bkommst du minus Karma");

                        } else {

                            $pf->set("Karma", $pf->get("Karma")+50);
                            $pf->save();
                            if ($pf->get("Karma") === 1000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 1000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 2000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 2000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 5000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 5000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 10000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 10000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 50000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 50000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 100000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 100000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 1000000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+100);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 1000000 Karma hast, erhaeltst du 100 Traitor Pass");

                            } else {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");

                            }

                        }

                    } else {

                        if ($player->getName() === $config->get("tn1")) {

                            $pf->set("Karma", $pf->get("Karma")+50);
                            $pf->save();
                            if ($pf->get("Karma") === 1000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 1000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 2000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 2000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 5000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 5000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 10000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 10000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 50000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 50000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 100000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 100000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 1000000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+100);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 1000000 Karma hast, erhaeltst du 100 Traitor Pass");

                            } else {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");

                            }

                        } else if ($player->getName() === $config->get("tn2")) {

                            $pf->set("Karma", $pf->get("Karma")+50);
                            $pf->save();
                            if ($pf->get("Karma") === 1000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 1000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 2000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 2000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 5000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 5000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 10000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 10000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 50000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 50000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 100000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+1);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 100000 Karma hast, erhaeltst du 1nen Traitor Pass");

                            } else if ($pf->get("Karma") === 1000000) {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");
                                $pf->set("Traitor", $pf->get("Traitor")+100);
                                $pf->save();
                                $killer->sendMessage($this->prefix . "Da du 1000000 Karma hast, erhaeltst du 100 Traitor Pass");

                            } else {

                                $killer->sendMessage($this->prefix . "+ 50 " . Color::YELLOW . "Karma");

                            }

                        } else {

                            $pf->set("Karma", $pf->get("Karma")-100);
                            $pf->save();
                            $killer->sendMessage($this->prefix . "- 100 " . Color::YELLOW . "Karma");
                            $killer->sendMessage($this->prefix . "Der Spieler war ein Innocent");

                        }

                    }
                    
                    if ($config->get("tn1") === $player->getName()) {

                        $config->set("tn1", "");
                        $config->set("t1", false);
                        $config->save();
                        $event->setDeathMessage($this->prefix . Color::RED . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
                        $player->getLevel()->addParticle(new FloatingTextParticle(new Vector3($x, $y, $z),"", Color::RED . $player->getName()));

                    } else if ($config->get("tn2") === $player->getName()) {

                        $config->set("tn2", "");
                        $config->set("t2", false);
                        $config->save();
                        $event->setDeathMessage($this->prefix . Color::RED . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
                        $player->getLevel()->addParticle(new FloatingTextParticle(new Vector3($x, $y, $z),"", Color::RED . $player->getName()));

                    } else {

                        $event->setDeathMessage($this->prefix . Color::GREEN . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
                        $player->getLevel()->addParticle(new FloatingTextParticle(new Vector3($x, $y, $z),"", Color::GREEN . $player->getName()));

                    }

                }

            }

        }
        
    }
    
    public function stringToInstance($name, $vec3) {
    	
    	switch(strtolower($name)) {
			
           case "text":
           return new FloatingTextParticle($vec3);
           
        }
              
    }
    
    public function onDrop(PlayerDropItemEvent $event) {
    	
    	$player = $event->getPlayer();
        $event->setCancelled(true);
        
    }
    
    public function onRespawn(PlayerRespawnEvent $event)
    {

        $player = $event->getPlayer();
        $pos = $player->getPosition();
        $config = $this->getConfig();
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $player->setSpawn($spawn);
        $player->getInventory()->clearAll();
        $playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
        $playerfile->set("teleportt", 2);
        $playerfile->set("teleport", true);
        $playerfile->set("Pass", false);
        $playerfile->save();
        $playerfile->save();

    }
   
   public function giveRolle(Player $player) {
   	
    	$config = $this->getConfig();
        if ($player->getName() === $config->get("player1")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("tn2", $player->getName());
                            $config->set("t2", true);
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("tn1", $player->getName());
                        $config->set("t1", true);
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player2")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player3")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player4")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player5")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player6")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player7")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player7")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player8")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player9")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player10")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player11")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        } else if ($player->getName() === $config->get("player12")) {
        	
        	$pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($pf->get("Pass") === true) {
            	
            	if ($player->getName() === $config->get("tn1")) {
            	    
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            	    
                } else if ($player->getName() === $config->get("tn2")) {
                	
            	    $player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
            
                }
            	
            } else {
            	
            	$r = mt_rand(1, 2);
                if ($r === 1) {
                	
                	if ($config->get("t1") === true) {
                	
                	    if ($config->get("t2") === true) {
                	        
                	         $player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                             
                        } else {
                        	
                        	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                            $config->set("t2", true);
                            $config->set("tn2", $player->getName());
                            $config->save();
                        	
                        }
                        
                    } else if ($config->get("t1") === false) {
                    	
                    	$player->addTitle(Color::WHITE . "Du bist ein", Color::RED . "Traitor", 20, 40, 20);
                        $config->set("t1", true);
                        $config->set("tn1", $player->getName());
                        $config->save();
                    	
                    }
                	
                } else if ($r === 2) {
                	
                	$player->addTitle(Color::WHITE . "Du bist ein", Color::GREEN . "Innocent", 20, 40, 20);
                	
                }
            	
            }
        	
        }
   	
   }
   
   public function teleportIngame(Player $player) {
    	
    	$config = $this->getConfig();
        $level = $this->getServer()->getLevelByName($config->get("Arena"));
        $af = new Config($this->getDataFolder() . "/" . $config->get("Arena") . ".yml", Config::YAML);
        $player->teleport(new Position($af->get("s1x"), $af->get("s1y")+1, $af->get("s1z"), $level));
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
        if (count($all) === 0) {

            if ($config->get("state") === true) {

                $config->set("ingame", false);
                $config->set("state", false);
                $config->set("reset", false);
                $config->set("rtime", 10);
                $config->set("time", 60);
                $config->set("playtime", 1200);
                $config->set("tn1", "");
                $config->set("t1", false);
                $config->set("tn2", "");
                $config->set("t2", false);
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

            if (count($all) < 4) {

                foreach ($all as $player) {

                    $player->sendPopup(Color::GRAY . ">> Warten auf weitere Spieler <<");

                }

            }

            if (count($all) >= 4) {

                $config->set("time", $config->get("time") - 1);
                $config->save();
                $time = $config->get("time") + 1;
                foreach ($all as $player) {

                    $pf = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
                    $player->sendPopup(
                        Color::YELLOW . ">> Karma: " . COLOR::GRAY . $pf->get("Karma") . "\n" .
                        Color::RED . ">> Paesse: " . Color::GRAY . $pf->get("Traitor") . "\n" .
                        Color::YELLOW . "Spieler: " . Color::WHITE . count($all) . Color::YELLOW . " / " . Color::WHITE . "12" . "\n" .
                        Color::GREEN . "Map: " . Color::WHITE . $config->get("Arena"));

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
                    $config->set("schutz", true);
                    $config->set("state", true);
                    foreach ($all as $player) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . "20" . Color::WHITE . " Minuten!");
                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "30" . Color::WHITE . " Sekunden!");
                        $player->setHealth(20);
                        $player->setFood(20);
                        $player->getInventory()->clearAll();
                        $steak = Item::get(364, 0, 64);
                        $player->getInventory()->setItem(8, $steak);
                        $this->plugin->giveRolle($player);
                        $this->plugin->teleportIngame($player);

                    }

                    $config->save();

                }

            }

        } else if ($config->get("ingame") === true) {

            $all = $this->plugin->getServer()->getOnlinePlayers();
            if (count($all) <= 1) {

                foreach ($all as $player) {

                    $config->set("ingame", false);
                    $config->set("reset", true);
                    $config->set("rtime", 10);
                    $config->set("time", 60);
                    $config->set("playtime", 1200);
                    $config->set("tn1", "");
                    $config->set("t1", false);
                    $config->set("tn2", "");
                    $config->set("t2", false);
                    $config->save();
                    $player->getInventory()->clearAll();
                    $playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
                    $playerfile->set("Pass", false);
                    $playerfile->save();
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
                    $pc->set("coins", $pc->get("coins") + 200);
                    $pc->save();
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
                	
                    if ($player->getName() === $config->get("tn1")) {

                        $player->sendPopup(
                            Color::RED . "TRAITOR\n" .
                            Color::GREEN . " Partner: " . Color::WHITE . $config->get("tn2") . "\n" .
                            Color::YELLOW . "Spieler: " . Color::WHITE . count($all) . Color::YELLOW . " / " . Color::WHITE . "12"
                        );

                    } else if ($player->getName() === $config->get("tn2")) {

                        $player->sendPopup(
                            Color::RED . "TRAITOR\n" .
                            Color::GREEN . " Partner: " . Color::WHITE . $config->get("tn1") . "\n" .
                            Color::YELLOW . "Spieler: " . Color::WHITE . count($all) . Color::YELLOW . " / " . Color::WHITE . "12"
                        );

                    } else {

                        $player->sendPopup(
                            Color::GREEN . "INNOCENT\n" .
                            Color::YELLOW . "Spieler: " . Color::WHITE . count($all) . Color::YELLOW . " / " . Color::WHITE . "12"
                        );

                    }

                    if ($config->get("t1") === false) {

                        if ($config->get("t2") === false) {

                            $config->set("ingame", false);
                            $config->set("reset", true);
                            $config->set("rtime", 10);
                            $config->set("time", 60);
                            $config->set("playtime", 1200);
                            $config->set("tn1", "");
                            $config->set("t1", false);
                            $config->set("tn2", "");
                            $config->set("t2", false);
                            $config->save();
                            $playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
                            $playerfile->set("Pass", false);
                            $playerfile->save();
                            $player->getInventory()->clearAll();
                            $player->setHealth(20);
                            $player->setFood(20);
                            $player->removeAllEffects();
                            $this->plugin->getServer()->broadcastMessage($this->plugin->prefix . "Die" . Color::GREEN . " Innocents" . Color::WHITE . " haben das Match in " . Color::WHITE . $config->get("Arena") . Color::GREEN . " Gewonnen!");
                            $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                            $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                            $player->teleport($spawn, 0, 0);
                            $levelname = $config->get("Arena");
                            $lev = $this->plugin->getServer()->getLevelByName($levelname);
                            $this->plugin->getServer()->unloadLevel($lev);
                            $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                            $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                            $this->plugin->getServer()->loadLevel($levelname);

                        }

                    }

                    if (count($all) === 2) {

                        if ($config->get("t1") === true) {

                            if ($config->get("t2") === true) {

                                $this->plugin->getServer()->broadcastMessage($this->plugin->prefix . "Die" . Color::RED . " Traitors" . Color::WHITE . " haben das Match in " . Color::WHITE . $config->get("Arena") . Color::GREEN . " Gewonnen!");
                                $config->set("ingame", false);
                                $config->set("reset", true);
                                $config->set("rtime", 10);
                                $config->set("time", 60);
                                $config->set("playtime", 1200);
                                $config->set("tn1", "");
                                $config->set("t1", false);
                                $config->set("tn2", "");
                                $config->set("t2", false);
                                $config->save();
                                $playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
                                $playerfile->set("Pass", false);
                                $playerfile->save();
                                $player->getInventory()->clearAll();
                                $player->setHealth(20);
                                $player->setFood(20);
                                $player->removeAllEffects();
                                $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                                $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                                $player->teleport($spawn, 0, 0);
                                $levelname = $config->get("Arena");
                                $lev = $this->plugin->getServer()->getLevelByName($levelname);
                                $this->plugin->getServer()->unloadLevel($lev);
                                $this->plugin->deleteDirectory($this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                                $this->plugin->copymap($this->plugin->getDataFolder() . "/maps/" . $levelname, $this->plugin->getServer()->getDataPath() . "/worlds/" . $levelname);
                                $this->plugin->getServer()->loadLevel($levelname);

                            }

                        }

                    }

                    if ($time === 1190) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "20" . Color::WHITE . " Sekunden!");

                    } else if ($time === 1180) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "10" . Color::WHITE . " Sekunden!");

                    } else if ($time === 1175) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "5" . Color::WHITE . " Sekunden!");

                    } else if ($time === 1173) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "3" . Color::WHITE . " Sekunden!");

                    } else if ($time === 1172) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "2" . Color::WHITE . " Sekunden!");

                    } else if ($time === 1171) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Schutzzeit endet in " . Color::DARK_PURPLE . "1er" . Color::WHITE . " Sekunde!");

                    } else if ($time === 1170) {

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Die Schutz Zeit ist nun Vorrueber!");
                        $config->set("schutz", false);
                        $config->save();

                    }

                    if ($time % 60 === 0 && $time > 60 && $time < 1200) {

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
                        $spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
                        $this->plugin->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
                        $player->teleport($spawn, 0, 0);
                        $config->set("ingame", false);
                        $config->set("reset", true);
                        $config->set("rtime", 10);
                        $config->set("time", 60);
                        $config->set("playtime", 1200);
                        $config->set("tn1", "");
                        $config->set("t1", false);
                        $config->set("tn2", "");
                        $config->set("t2", false);
                        $config->save();
                        $playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
                        $playerfile->set("Pass", false);
                        $playerfile->save();
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
                $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
                $player->sendMessage(Color::GOLD . "Bitte Warte bis der Lobby Timer beendet wurde!");
            	
            } else if ($time === 5) {

                 $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");

            } else if ($time === 0) {
            	
            	$config->set("reset", false);
                $config->set("rtime", 10);
                $config->set("state", false);
                $config->save();
                $clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
                $clouddata->set("ServerMessage", "Der Server: " . $config->get("Server") . " wird hochgefahren!");
                $clouddata->set("ServerMessageStatus", true);
                $clouddata->set($config->get("Server"), true);
                $clouddata->save();
                foreach ($all as $player) {
                	
                	$playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
                    $playerfile->set("Pass", false);
                    $playerfile->save();
                    $player->transfer("84.200.84.61", 19132);
                	
                }
            	
            }
            
        }

        foreach ($all as $player) {

            $playerfile = new Config("/home/EnderCloud/TTT/players/" . $player->getName() . ".yml", Config::YAML);
            if ($playerfile->get("teleport") === true) {

                $playerfile->set("teleportt", $playerfile->get("teleportt") - 1);
                $playerfile->save();
                $time = $playerfile->get("teleportt") + 1;
                if ($time === 0) {

                    $playerfile->set("teleport", false);
                    $playerfile->set("teleportt", 1);
                    $playerfile->set("Pass", false);
                    $playerfile->save();
                    $player->transfer("84.200.84.61", 19132);

                }


            }

        }

    }

}