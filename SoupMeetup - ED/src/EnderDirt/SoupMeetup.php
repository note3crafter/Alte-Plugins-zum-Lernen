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

class SoupMeetup extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "Soup" . Color::GOLD . "Meetup" . Color::WHITE . "] ";
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
            $playerfile->set("kills", 0);
            $playerfile->set("deaths", 0);
            $playerfile->set("wins", 0);
            $playerfile->set("Kit", "NO");
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
        if ($config->get("ingame") === true) {
        	
        	$player->transfer("84.200.84.61", 19132);
        
        } else {
        	
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
            
        } else if (count($all) === 13) {
        	
            $config->set("player13", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 14) {
        	
            $config->set("player14", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 15) {
        	
            $config->set("player15", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        } else if (count($all) === 16) {
        	
            $config->set("player16", $player->getName());
            $player->setGamemode(0);
            $config->save();
            
        }
        
        }

    }
    
    public function onQuit(PlayerQuitEvent $event) {
    	
    	$player = $event->getPlayer();
        $config = $this->getConfig();
        $event->setQuitMessage(Color::GRAY . "< " . Color::DARK_GRAY . "< " . $player->getDisplayName() . Color::GRAY . " hat den Server verlassen!");
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
    	    case "SoupMeetup":
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
                	
                	$config->set("time", 15);
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
        $it = $event->getItem();
        if ($it->getId() === 282) {
        	
        	$player->getInventory()->removeItem($it);
            $player->setHealth($player->getHealth() + 5);
            $player->setFood(20);
        	
        }
        
        if ($this->mode === 1 && $player->isOp()) {
        	
        	$config->set("s1x", $block->getX() + 0.5);
            $config->set("s1y", $block->getY() + 1);
            $config->set("s1z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 2. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 2 && $player->isOp()) {
        	
        	$config->set("s2x", $block->getX() + 0.5);
            $config->set("s2y", $block->getY() + 1);
            $config->set("s2z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 3. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 3 && $player->isOp()) {
        	
        	$config->set("s3x", $block->getX() + 0.5);
            $config->set("s3y", $block->getY() + 1);
            $config->set("s3z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 4. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 4 && $player->isOp()) {
        	
        	$config->set("s4x", $block->getX() + 0.5);
            $config->set("s4y", $block->getY() + 1);
            $config->set("s4z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 5. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 5 && $player->isOp()) {
        	
        	$config->set("s5x", $block->getX() + 0.5);
            $config->set("s5y", $block->getY() + 1);
            $config->set("s5z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 6. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 6 && $player->isOp()) {
        	
        	$config->set("s6x", $block->getX() + 0.5);
            $config->set("s6y", $block->getY() + 1);
            $config->set("s6z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 7. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 7 && $player->isOp()) {
        	
        	$config->set("s7x", $block->getX() + 0.5);
            $config->set("s7y", $block->getY() + 1);
            $config->set("s7z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 8. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 8 && $player->isOp()) {
        	
        	$config->set("s8x", $block->getX() + 0.5);
            $config->set("s8y", $block->getY() + 1);
            $config->set("s8z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 9. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 9 && $player->isOp()) {
        	
        	$config->set("s9x", $block->getX() + 0.5);
            $config->set("s9y", $block->getY() + 1);
            $config->set("s9z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 10. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 10 && $player->isOp()) {
        	
        	$config->set("s10x", $block->getX() + 0.5);
            $config->set("s10y", $block->getY() + 1);
            $config->set("s10z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 11. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 11 && $player->isOp()) {
        	
        	$config->set("s11x", $block->getX() + 0.5);
            $config->set("s11y", $block->getY() + 1);
            $config->set("s11z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 12. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 12 && $player->isOp()) {
        	
        	$config->set("s12x", $block->getX() + 0.5);
            $config->set("s12y", $block->getY() + 1);
            $config->set("s12z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 13. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 13 && $player->isOp()) {
        	
        	$config->set("s13x", $block->getX() + 0.5);
            $config->set("s13y", $block->getY() + 1);
            $config->set("s13z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 14. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 14 && $player->isOp()) {
        	
        	$config->set("s14x", $block->getX() + 0.5);
            $config->set("s14y", $block->getY() + 1);
            $config->set("s14z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 15. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 15 && $player->isOp()) {
        	
        	$config->set("s15x", $block->getX() + 0.5);
            $config->set("s15y", $block->getY() + 1);
            $config->set("s15z", $block->getZ() + 0.5);
            $config->save();
            
            $player->sendMessage($this->prefix . "Jetzt den 16. Spawn");
            $this->mode++;
            
        } else if ($this->mode === 16 && $player->isOp()) {
        	
        	$config->set("s16x", $block->getX() + 0.5);
            $config->set("s16y", $block->getY() + 1);
            $config->set("s16z", $block->getZ() + 0.5);
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
        if ($player instanceof Player) {
        	
        	$cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
            	
            	$killer = $cause->getDamager();
                if ($killer instanceof Player) {
                	
                	$event->setDeathMessage($this->prefix . $player->getName() . Color::GOLD . " wurde von " . Color::WHITE . $killer->getName() . Color::GOLD . " getoetet!");
                    $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                    $playerfile->set("kills", $playerfile->get("kills") + 1);
                    $playerfile->save();
                    $killer->sendMessage(Color::WHITE . "[" . Color::DARK_PURPLE . "+" . Color::WHITE . "] 50 Coins");
                    $pc = new Config("/home/EnderCloud/eCoins/" . $killer->getName() . ".yml", Config::YAML);
                    $pc->set("coins", $pc->get("coins")+50);
                    $pc->save();
                    $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                    $pf->set("deaths", $pf->get("deaths") + 1);
                    $pf->save();
                
                } else {
                	
                	$event->setDeathMessage($this->prefix . Color::GOLD . $player->getDisplayName() . Color::WHITE . " ist Gestorben!");
                    $pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                    $pf->set("deaths", $pf->get("deaths") + 1);
                    $pf->save();
                
                }
                
            }
            
        }
    
    }
    
    public function onRespawn(PlayerRespawnEvent $event) {
    	
    	$player = $event->getPlayer();
        $pos = $player->getPosition();
        $config = $this->getConfig();
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $player->getInventory()->clearAll();
        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        $playerfile->set("teleportt", 1);
        $playerfile->set("teleport", true);
        $playerfile->save();
        
    }
    
    public function giveKit(Player $player) {
    	
    	$player->getInventory()->clearAll();
        $sword = Item::get(267, 0, 1);
        $helm = Item::get(310, 0, 1);
        $chest = Item::get(311, 0, 1);
        $hose = Item::get(312, 0, 1);
        $boots = Item::get(313, 0, 1);
        $e1 = Enchantment::getEnchantment(0);
        $e2 = Enchantment::getEnchantment(1);
        $helm->addEnchantment($e2);
        $chest->addEnchantment($e1);
        $chest->addEnchantment($e2);
        $hose->addEnchantment($e2);
        $boots->addEnchantment($e2);
        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->addItem(Item::get(282, 0, 1));
        $player->getInventory()->setHelmet($helm);
        $player->getInventory()->setChestplate($chest);
        $player->getInventory()->setLeggings($hose);
        $player->getInventory()->setBoots($boots);
        
    }
    
    public function teleportIngame(Player $player) {
    	
    	$config = $this->getConfig();
    	$levl = $this->getServer()->getLevelByName("world");
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
        $config->set("players", count($all));
        $config->save();
        if (count($all) === 0) {

            if ($config->get("state") === true) {

                $config->set("ingame", false);
                $config->set("state", false);
                $config->set("reset", false);
                $config->set("rtime", 10);
                $config->set("time", 60);
                $config->set("playtime", 3600);
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

                        $player->sendMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Match endet in " . Color::DARK_PURPLE . "60" . Color::WHITE . " Minuten!");
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
                    $config->save();
                    $levelname = "world";
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
                	
                   if ($time === 3585) {
                    	
                    	$player->sendMessage(Color::DARK_PURPLE . "> > ". Color::WHITE . "Die Schutz Zeit ist nun Vorrueber!");
                    	$config->set("schutz", false);
                        $config->save();
                    
                    } else if ($time === 3300) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 3000) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 2700) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 2400) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 2100) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 1800) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 1500) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 1200) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 900) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 600) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time === 300) {
                    	
                    	$this->plugin->teleportIngame($player);
                        $this->plugin->giveKit($player);
                    
                    } else if ($time % 60 === 0 && $time > 60 && $time < 3600) {

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
                        $config->save();
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
            	
            	$this->plugin->getServer()->broadcastMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
            	
            } else if ($time === 5) {
            	
            	$this->plugin->getServer()->broadcastMessage(Color::DARK_PURPLE . ">> " . Color::WHITE . "Das Server restartet in " . Color::DARK_PURPLE . $time . Color::WHITE . " Sekunden!");
            	
            } else if ($time === 0) {
            	
            	$config->set("reset", false);
                $config->set("rtime", 10);
                $config->set("state", false);
                $config->save();
                foreach ($all as $player) {
                	
                	$player->transfer("84.200.84.61", 19132);
                	
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
                    $player->transfer("84.200.84.61", 19132);

                }


            }

        }

    }

}