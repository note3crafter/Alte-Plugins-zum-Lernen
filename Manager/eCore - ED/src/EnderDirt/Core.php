<?php

namespace EnderDirt;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as Color;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\Server;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Chest;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\entity\Effect;
use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\level\sound\GhastSound;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\PopSound;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\item\enchantment\Enchantment;

class Core extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "eCore" . Color::WHITE . "] ";
	
	public function onEnable() {
		
		@mkdir($this->getDataFolder());
        if (is_dir($this->getDataFolder()) !== true) {

            mkdir($this->getDataFolder());

        }

		if (is_dir($this->getDataFolder() . "/players") !== true) {
			
            mkdir($this->getDataFolder() . "/players");
            
        }

        $this->saveDefaultConfig();
        $this->reloadConfig();
        
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::DARK_PURPLE . " EnderDirt!");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Open($this), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Anzeige($this), 10);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new JoinText($this), 20);
        
    }
    
    public function onLogin(PlayerLoginEvent $event) {
    	
    	$player = $event->getPlayer();
        $config = $this->getConfig();
        $uuid = $player->getClientID();
        if (!is_file("/home/EnderCloud/players/" . $player->getName() . ".yml")) {
        	
        	$playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("VIP", false);
            $playerfile->set("portal", false);
            $playerfile->set("smoke", false);
            $playerfile->set("lava", false);
            $playerfile->set("heart", false);
            $playerfile->set("flame", false);
            $playerfile->set("build", false);
            $playerfile->set("russi1", false);
            $playerfile->set("bombe", false);
            $playerfile->set("portal1", false);
            $playerfile->set("smoke1", false);
            $playerfile->set("lava1", false);
            $playerfile->set("heart1", false);
            $playerfile->set("flame1", false);
            $playerfile->set("fly", false);
            $playerfile->set("Jump", false);
            $playerfile->set("Speed", false);
            $playerfile->set("JumpA", false);
            $playerfile->set("SpeedA", false);
            $playerfile->set("open", false);
            $playerfile->set("time", 5);
            $playerfile->set("join", 10);
            $playerfile->set("ke", 0);
            $playerfile->set("Ban", false);
            $playerfile->set("BanTime", 0);
            $playerfile->set("BanGrund", "");
            $playerfile->save();
        	
        }
        
    }
    
    public function onJoin(PlayerJoinEvent $event) {
    	
    	$player = $event->getPlayer();
        $player->setGamemode(0);
        $player->setAllowFlight(false);
        $player->removeAllEffects();
		$event->setJoinMessage("");
		$player->setHealth(20);
        $player->setFood(20);
        $player->getInventory()->clearAll();
        $spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
        $this->getServer()->getDefaultLevel()->loadChunk($spawn->getX(), $spawn->getZ());
        $player->teleport($spawn, 0, 0);
        $pos = $player->getPosition();
        $player->setSpawn($pos);
        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
		$playerfile->set("build", false);
		$playerfile->set("join", 5);
		$playerfile->save();
		$this->giveBoots($player);
		if ($playerfile->get("JumpA") === true) {
			
			$effect = Effect::getEffect(Effect::JUMP);
                $effect->setAmplifier(3);
                $effect->setDuration(2333333);
                $player->addEffect($effect);
                
        }
        
        if ($playerfile->get("SpeedA") === true) {
        	
			$effect = Effect::getEffect(Effect::SPEED);
                $effect->setAmplifier(3);
                $effect->setDuration(2333333);
                $player->addEffect($effect);
                
        }
        
		foreach($this->getServer()->getOnlinePlayers() as $p) {
			
			$player->showPlayer($p);
			
		}
		
    }
    
    public function onQuit(PlayerQuitEvent $event){
		
		$player = $event->getPlayer();
		$event->setQuitMessage("");
        $all = $this->getServer()->getOnlinePlayers();
        $pp = new Config("/home/EnderCloud/Daten.yml");
        $pp->set("Lobby1", count($all));
        $pp->save();
		
	}
	
	public function stringToInstance($name, $vec3) {
    	
    	switch(strtolower($name)) {
			
           case "portal":
           return new PortalParticle($vec3);
           
        }
        
        switch(strtolower($name)) {
			
           case "lava":
           return new LavaParticle($vec3);
           
        }
        
        switch(strtolower($name)) {
        	
        	case "smoke":
            return new SmokeParticle($vec3);
            
        }

        switch(strtolower($name)) {
        	
            case "heart":
            return new HeartParticle($vec3);
            
         }
         
         switch(strtolower($name)) {

            case "flame":
                return new FlameParticle($vec3);

        }
        
        switch(strtolower($name)) {
        	
        	case "redstone":
            return new RedstoneParticle($vec3);
            
        }
         
    }
    
    //OnMove Sachen
    public function onMove(PlayerMoveEvent $event) {
    	
    	$level = $event->getPlayer()->getLevel();
        $player = $event->getPlayer();
        $x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        $config = $this->getConfig();
        if ($config->get("world") === $player->getLevel()->getName()) {
        	
        if ($playerfile->get("portal") === true) {
        	
        	$level->addParticle(new PortalParticle(new Vector3($x, $y, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x+0.5, $y, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y, $z+0.5)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y, $z)));
        
        } else if ($playerfile->get("smoke") === true) {
        	
        	$level->addParticle(new SmokeParticle(new Vector3($x, $y, $z)));
        
        } else if ($playerfile->get("lava") === true) {
        	
        	$level->addParticle(new LavaParticle(new Vector3($x, $y, $z)));
        
        } else if ($playerfile->get("heart") === true) {
        	
        	$level->addParticle(new HeartParticle(new Vector3($x, $y, $z)));
        
        } else if ($playerfile->get("flame") === true) {
        	
        	$level->addParticle(new FlameParticle(new Vector3($x, $y, $z)));
        
        }
        
        }
        
    }
    
    //Keiner kann Schlagen
    public function onDamage(EntityDamageEvent $event) {
    	
    	$player = $event->getEntity();
        $config = $this->getConfig();
        $event->setCancelled(true);
        
    }
    
    public function onKnock(EntityDamageEvent $event) {
    	
        $config = $this->getConfig();
        if ($event instanceof EntityDamageByEntityEvent) {
        	$damager = $event->getDamager();
            $player = $event->getEntity();
            if ($damager instanceof Player) {
            	if ($damager->getInventory()->getItemInHand()->getId() === Item::BONE) {
            	    $player->setMotion(new Vector3(4 * (sin($damager->yaw / 180 * M_PI) * cos($damager->pitch / 180 * M_PI)), -sin($damager->pitch / 180 * M_PI), 4 * (cos($damager->yaw / 180 * M_PI) * cos($damager->pitch / 180 * M_1_PI))));
            	    $player->setHealth(20);
                }
            }
        }
        
    }
    
    public function onDrop(PlayerDropItemEvent $event) {
    	
    	$player = $event->getPlayer();
        $config = $this->getConfig();
        if ($config->get("world") === $player->getLevel()->getName()) {
        	
        	$event->setCancelled(true);
        
        } else {
        	
        	$event->setCancelled(false);
        
        }
        
    }
    
    public function onPlace(BlockPlaceEvent $event) {
    	
    	$level = $event->getPlayer()->getLevel();
        $player = $event->getPlayer();  
        $config = $this->getConfig();
        if ($config->get("world") === $player->getLevel()->getName()) {
        	
        	if ($player->isOp()) {
        	
        	    $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                if ($playerfile->get("build") === true) {
                	
                	$event->setCancelled(false);
                
                } else {
                	
                	$event->setCancelled(true);
                
                }
                
            } else {
            	
            	$event->setCancelled(true);
            
            }
        
        } else {
        	
        	$event->setCancelled(false);
        
        }
               	                         
    }
    
    public function onBreak(BlockBreakEvent $event) {
    	
    	$level = $event->getPlayer()->getLevel();
        $player = $event->getPlayer();  
        $config = $this->getConfig();
        if ($config->get("world") === $player->getLevel()->getName()) {
        	
        	if ($player->isOp()) {
        	
        	    $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
                if ($playerfile->get("build") === true) {
                	
                	$event->setCancelled(false);
                
                } else {
                	
                	$event->setCancelled(true);
                
                }
                
            } else {
            	
            	$event->setCancelled(true);
            
            }
        
        } else {
        	
        	$event->setCancelled(false);
        
        }       
        
    }
    
    //Command
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "Build") {
        	
        	$playerfile = new Config("/home/EnderCloud/players/" . $sender->getName() . ".yml", Config::YAML);
            if ($playerfile->get("build") === true) {
            	
            	$sender->sendMessage(Color::RED . "Build wurde Deaktiviert!");
                $playerfile->set("build", false);
                $playerfile->save();
                
            } else if ($playerfile->get("build") === false) {
            	
            	if ($sender->isOp()) {
            	
            	 $sender->sendMessage(Color::GREEN . "Build wurde aktiviert!");
                $playerfile->set("build", true);
                $playerfile->save();
                
                }
                
            }
            
        }
        
        return true;
        
    }
    
    public function giveBoots(Player $player) {
    	
    	$playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        if ($playerfile->get("SpeedA") === true) {
        	
        	$player->getInventory()->setBoots(Item::get(305, 0, 1));
        
        }
        
        if ($playerfile->get("JumpA") === true) {
        	
        	$player->getInventory()->setBoots(Item::get(301, 0, 1));
        
        }
    	
    }
    
    public function onInteract(PlayerInteractEvent $event) {
    	
    	$player = $event->getPlayer();
        $level = $event->getPlayer()->getLevel();
        $playerfile = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        $pc = new Config("/home/EnderCloud/eCoins/" . $player->getName() . ".yml", Config::YAML);
        $x = $player->getX();
        $y = $player->getY();
        $z = $player->getZ();
		$item = $player->getInventory()->getItemInHand();
        $config = $this->getConfig();
        $world = $config->get("world");
        $block = $event->getBlock();
		$name = $player->getName();
		$level = $player->getLevel();
        $pp = new Config("/home/EnderCloud/Daten.yml");
		if ($block->getId() ===  Block::NOTE_BLOCK) {
			
			if ($player->getLevel()->getName() == $world) {
				
				if ($pc->get("coins") >= 5000) {
					
					$player->sendMessage($this->prefix . Color::RED . "Deine Kiste wird geoeffnet ...");
                    $playerfile->set("open", true);
                    $playerfile->set("time", 5);
                    $playerfile->save();

                } else {

                    $player->sendMessage($this->prefix . Color::RED . "Du hast zu wenig Coins!");

                }
                
            }
            
        }
        
        if ($item->getCustomName() === Color::DARK_PURPLE . "Teleporter") {
        	
        	$player->getInventory()->clearAll();
            $schwitzerwars = Item::get(57, 0, 1);
            $cores = Item::get(138, 0, 1);
            $mlg = Item::get(280, 0, 1);
            $kw = Item::get(54, 0, 1);
            $bu = Item::get(267, 0, 1);
            $back = Item::get(331, 0, 1);
            $schwitzerwars->setCustomName(Color::YELLOW . "Schwitzer" . Color::WHITE . "Wars");
            $cores->setCustomName(Color::AQUA . "Cores");          
            $mlg->setCustomName(Color::GOLD . "MLG" . Color::YELLOW . "Rush");
            $kw->setCustomName(Color::DARK_PURPLE . "Kit" . Color::WHITE . "Wars");
            $bu->setCustomName(Color::YELLOW . "Build" . Color::GREEN . "UHC");
            $back->setCustomName(Color::RED . "Back");
            $player->getInventory()->setItem(0, $schwitzerwars);
            $player->getInventory()->setItem(1, $bu);
            $player->getInventory()->setItem(2, $cores);
            $player->getInventory()->setItem(4, $mlg);
            $player->getInventory()->setItem(6, $kw);
            $player->getInventory()->setItem(8, $back);           
            
        } else if ($item->getCustomName() === Color::YELLOW . "Schwitzer" . Color::WHITE . "Wars") {
        	
        	$x = -530;
			$y = 7;
			$z = 303;
			$player->teleport(new Vector3 ($x, $y, $z));
        	$player->getInventory()->clearAll();
            $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$player->getInventory()->setItem(4, $acces);
		$player->getInventory()->setItem(2, $switcher);
		$player->getInventory()->setItem(6, $hide);
        	
        } else if ($item->getCustomName() === Color::YELLOW . "Build" . Color::GREEN . "UHC") {
        	
            $x = -1189;
			$y = 7;
			$z = 287;
			$player->teleport(new Vector3 ($x, $y, $z));
        	$player->getInventory()->clearAll();
            $telp = Item::get(345, 0, 1);
            $acces = Item::get(130, 0, 1);
            $switcher = Item::get(352, 0, 1);
            $hide = Item::get(351, 5, 1);
            $acces->setCustomName(Color::AQUA . "Accessoires");
		    $telp->setCustomName(Color::DARK_PURPLE . "Teleporter");
		    $hide->setCustomName(Color::RED . "Spieler Verstecken");
		    $switcher->setCustomName(Color::GOLD . "LobbyFight");
		    $player->getInventory()->setItem(0, $telp);
		    $player->getInventory()->setItem(4, $acces);
		    $player->getInventory()->setItem(6, $switcher);
		    $player->getInventory()->setItem(6, $hide);
        	
        } else if ($item->getCustomName() === Color::AQUA . "Cores") {
        	
            $x = -651;
			$y = 7;
			$z = 302;
			$player->teleport(new Vector3 ($x, $y, $z));
        	$player->getInventory()->clearAll();
            $telp = Item::get(345, 0, 1);
            $acces = Item::get(130, 0, 1);
            $switcher = Item::get(352, 0, 1);
            $hide = Item::get(351, 5, 1);
            $acces->setCustomName(Color::AQUA . "Accessoires");
		    $telp->setCustomName(Color::DARK_PURPLE . "Teleporter");
		    $hide->setCustomName(Color::RED . "Spieler Verstecken");
		    $switcher->setCustomName(Color::GOLD . "LobbyFight");
		    $player->getInventory()->setItem(0, $telp);
		    $player->getInventory()->setItem(4, $acces);
		    $player->getInventory()->setItem(6, $switcher);
		    $player->getInventory()->setItem(6, $hide);
        	
        } else if ($item->getCustomName() === Color::DARK_PURPLE . "Kit" . Color::WHITE . "Wars") {
        	
            $x = -939;
			$y = 7;
			$z = 291;
			$player->teleport(new Vector3 ($x, $y, $z));
        	$player->getInventory()->clearAll();
            $telp = Item::get(345, 0, 1);
            $acces = Item::get(130, 0, 1);
            $switcher = Item::get(352, 0, 1);
            $hide = Item::get(351, 5, 1);
            $acces->setCustomName(Color::AQUA . "Accessoires");
		    $telp->setCustomName(Color::DARK_PURPLE . "Teleporter");
		    $hide->setCustomName(Color::RED . "Spieler Verstecken");
		    $switcher->setCustomName(Color::GOLD . "LobbyFight");
		    $player->getInventory()->setItem(0, $telp);
		    $player->getInventory()->setItem(4, $acces);
		    $player->getInventory()->setItem(6, $hide);
        	
        } else if ($item->getCustomName() === Color::GOLD . "MLG" . Color::YELLOW . "Rush") {
        	
            $x = -785;
			$y = 7;
			$z = 296;
			$player->teleport(new Vector3 ($x, $y, $z));
        	$player->getInventory()->clearAll();
            $telp = Item::get(345, 0, 1);
            $acces = Item::get(130, 0, 1);
            $switcher = Item::get(352, 0, 1);
            $hide = Item::get(351, 5, 1);
            $acces->setCustomName(Color::AQUA . "Accessoires");
		    $telp->setCustomName(Color::DARK_PURPLE . "Teleporter");
		    $hide->setCustomName(Color::RED . "Spieler Verstecken");
		    $switcher->setCustomName(Color::GOLD . "LobbyFight");
		    $player->getInventory()->setItem(0, $telp);
		    $player->getInventory()->setItem(4, $acces);
		    $player->getInventory()->setItem(6, $hide);
        	
        } else if ($item->getCustomName() === Color::GOLD . "LobbySwitcher") {
        	
        	if ($config->get("Server") === "Main") {
        	
        	    $player->getInventory()->clearAll();
                $lobby = Item::get(502, 0, 1);
                $back = Item::get(331, 0, 1);
                $lobby->setCustomName(Color::GOLD . "VIP" . Color::WHITE . " Lobby");
                $back->setCustomName(Color::RED . "Back");
                $player->getInventory()->setItem(4, $lobby);
                $player->getInventory()->setItem(8, $back);
                $this->giveBoots($player);
                
            } else if ($config->get("Server") === "MainVIP") {
        	
        	    $player->getInventory()->clearAll();
                $lobby = Item::get(508, 0, 1);
                $back = Item::get(331, 0, 1);
                $lobby->setCustomName(Color::WHITE . " Lobby 1");
                $back->setCustomName(Color::RED . "Back");
                $player->getInventory()->setItem(4, $lobby);
                $player->getInventory()->setItem(8, $back);
                $this->giveBoots($player);
                
            }
        
        	
        } else if ($item->getCustomName() === Color::WHITE . " Lobby 1") {
        	
        	$event->setCancelled(true);
            $playerfile->set("Lobby", "Main");
            $playerfile->save();
        	$player->transfer("5.230.146.16", 19198);
        	
        } else if ($item->getCustomName() === Color::GOLD . "VIP" . Color::WHITE . " Lobby") {
        	
        	if ($playerfile->get("VIP") === true) {
        	
        	    $event->setCancelled(true);
                $playerfile->set("Lobby", "VIP");
                $playerfile->save();
        	    $player->transfer("5.230.146.16", 19199);
        
            } else {
            	
            	$player->sendMessage(Color::RED . "Um in diese Lobby zu kommen brauchst du Mindestens VIP");
            	
            }
        	
        } else if ($item->getCustomName() === Color::RED . "Back") {
        	
        	$player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		    //Accessoires
		} else if ($item->getCustomName() === Color::AQUA . "Fly Boots") {
			
			if ($playerfile->get("VIP") === true) {
				
				$player->setAllowFlight(true);
				$player->sendMessage($this->prefix . Color::GREEN . "Die FlyBoots wurden Aktiviert!");
				$player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
            } else {
            	
            	$player->sendMessage($this->prefix . Color::RED . "Du hast diese Feature nicht freigeschaltet!");
            	
            }
			
        } else if ($item->getCustomName() === Color::AQUA . "Accessoires") {
        	
        	$player->getInventory()->clearAll();
            $partikel = Item::get(433, 0, 1);
            $bomb = Item::get(351, 8, 1);
            $russi = Item::get(334, 0, 1);
            $boots = Item::get(313, 0, 1);
            $bot = Item::get(317, 0, 1);
            $back = Item::get(331, 0, 1);
            $partikel->setCustomName(Color::GOLD . "Partikel");
            $bomb->setCustomName(Color::GRAY . "Bombe");
            $boots->setCustomName(Color::AQUA . "FlyBoots");
            $bot->setCustomName(Color::GREEN . "Boots");
            $russi->setCustomName(Color::YELLOW . "Ra" . Color::GREEN . "in" . Color::RED . "bow" . Color::GRAY . " Ruessi");
            $back->setCustomName(Color::RED . "Back");
            $player->getInventory()->setItem(4, $bomb);
            $player->getInventory()->setItem(6, $bot);
            $player->getInventory()->setItem(2, $partikel);
            $player->getInventory()->setItem(0, $russi);
            $player->getInventory()->setItem(8, $back);
            $this->giveBoots($player);
        
        } else if ($item->getCustomName() === Color::GREEN . "Boots") {
        	
        	$player->getInventory()->clearAll();
            $jump = Item::get(301, 0, 1);
            $speed = Item::get(305, 0, 1);
            $fly = Item::get(313, 0, 1);
            $back = Item::get(331, 0, 1);
            $jump->setCustomName(Color::GOLD . "Jump Boots");
            $fly->setCustomName(Color::AQUA . "Fly Boots");
            $speed->setCustomName(Color::GRAY . "Speed Boots");
            $back->setCustomName(Color::RED . "Back");
            $player->getInventory()->setItem(3, $jump);
            $player->getInventory()->setItem(1, $fly);
            $player->getInventory()->setItem(5, $speed);
            $player->getInventory()->setItem(8, $back);
            $this->giveBoots($player);
            
        } else if ($item->getCustomName() === Color::GRAY . "Speed Boots") {
        	
        	if ($playerfile->get("Speed") === true) {
        	
        	$player->sendMessage($this->prefix . Color::GREEN . "Die Speed Boots wurden Aktiviert!");
            $player->removeAllEffects();
            $player->setAllowFlight(false);
            $effect = Effect::getEffect(Effect::SPEED);
                $effect->setAmplifier(3);
                $effect->setDuration(2333333);
                $player->addEffect($effect);
                $playerfile->set("SpeedA", true);
                $playerfile->set("JumpA", false);
                $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
            } else {
            	
            	$player->sendMessage($this->prefix . Color::RED . "Du hast diese Feature nicht freigeschaltet!");
            	
            }
        	
        } else if ($item->getCustomName() === Color::GOLD . "Jump Boots") {
        	
        	if ($playerfile->get("Jump") === true) {
        	
        	$player->sendMessage($this->prefix . Color::GREEN . "Die Jump Boots wurden Aktiviert!");
            $player->removeAllEffects();
            $player->setAllowFlight(false);
            $effect = Effect::getEffect(Effect::JUMP);
                $effect->setAmplifier(3);
                $effect->setDuration(2333333);
                $player->addEffect($effect);
                $playerfile->set("SpeedA", false);
                $playerfile->set("JumpA", true);
                $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
            } else {
            	
            	$player->sendMessage($this->prefix . Color::RED . "Du hast diese Feature nicht freigeschaltet!");
            	
            }
        	
        } else if ($item->getCustomName() === Color::YELLOW . "Ra" . Color::GREEN . "in" . Color::RED . "bow" . Color::GRAY . " Ruessi") {
        	
        	if ($playerfile->get("russi1") === true) {
        	
        	if ($playerfile->get("russi") === false) {
        	
        	$player->sendMessage($this->prefix . Color::GREEN . "Die Rainbow Ruessi wurde Aktiviert!");
        	$playerfile->set("russi", true);
            $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
		    } else if ($playerfile->get("russi") === true) {
			
			$player->sendMessage($this->prefix . Color::GREEN . "Die Rainbow Ruessi wurde Deaktiviert!");
        	$playerfile->set("russi", false);
            $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
            }
            
            } else if ($playerfile->get("russi1") === false) {
            	
            	$player->sendMessage($this->prefix . Color::RED . "Du hast diese Feature nicht freigeschaltet!");
            
            }
            
        } else if ($item->getCustomName() === Color::GRAY . "Bombe") {
        	
        	if ($playerfile->get("bombe") === true) {    
        	
            $level->addParticle(new SmokeParticle(new Vector3($x, $y, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+1, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+2, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+3, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+4, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x+1, $y+1, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x+2, $y+2, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x+3, $y+3, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+1, $z+1)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+2, $z+2)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+3, $z+3)));
            $level->addParticle(new SmokeParticle(new Vector3($x-1, $y+1, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x-2, $y+2, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x-3, $y+3, $z)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+1, $z-1)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+2, $z-2)));
            $level->addParticle(new SmokeParticle(new Vector3($x, $y+3, $z-3)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+1, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+2, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+3, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+4, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x+1, $y+1, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x+2, $y+2, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x+3, $y+3, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+1, $z+1)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+2, $z+2)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+3, $z+3)));
            $level->addParticle(new FlameParticle(new Vector3($x-1, $y+1, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x-2, $y+2, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x-3, $y+3, $z)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+1, $z-1)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+2, $z-2)));
            $level->addParticle(new FlameParticle(new Vector3($x, $y+3, $z-3)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+1, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+2, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+3, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+4, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x+1, $y+1, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x+2, $y+2, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x+3, $y+3, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+1, $z+1)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+2, $z+2)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+3, $z+3)));
            $level->addParticle(new PortalParticle(new Vector3($x-1, $y+1, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x-2, $y+2, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x-3, $y+3, $z)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+1, $z-1)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+2, $z-2)));
            $level->addParticle(new PortalParticle(new Vector3($x, $y+3, $z-3)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+1, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+2, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+3, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+4, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x+1, $y+1, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x+2, $y+2, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x+3, $y+3, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+1, $z+1)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+2, $z+2)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+3, $z+3)));
            $level->addParticle(new LavaParticle(new Vector3($x-1, $y+1, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x-2, $y+2, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x-3, $y+3, $z)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+1, $z-1)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+2, $z-2)));
            $level->addParticle(new LavaParticle(new Vector3($x, $y+3, $z-3)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+1, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+2, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+3, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+4, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x+1, $y+1, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x+2, $y+2, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x+3, $y+3, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+1, $z+1)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+2, $z+2)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+3, $z+3)));
            $level->addParticle(new RedstoneParticle(new Vector3($x-1, $y+1, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x-2, $y+2, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x-3, $y+3, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+1, $z-1)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+2, $z-2)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+3, $z-3)));
            $level->addParticle(new RedstoneParticle(new Vector3($x-4, $y+4, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x-5, $y+5, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x-6, $y+6, $z)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+4, $z-4)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+5, $z-5)));
            $level->addParticle(new RedstoneParticle(new Vector3($x, $y+6, $z-6)));
            
            }
            
        } else if ($item->getCustomName() === Color::GOLD . "Partikel") {
        	
        	$player->getInventory()->clearAll();
            $portal = Item::get(351, 5, 1);
            $smoke = Item::get(351, 8, 1);
            $lava = Item::get(351, 14, 1);
            $heart = Item::get(351, 1, 1);
            $flame = Item::get(351, 11, 1);
            $back = Item::get(331, 0, 1);
            $portal->setCustomName(Color::DARK_PURPLE . "Portal");
            $smoke->setCustomName(Color::GRAY . "Rauch");
            $lava->setCustomName(Color::GOLD . "Lava");
            $heart->setCustomName(Color::RED . "Herz");
            $flame->setCustomName(Color::YELLOW . "Flamme");
            $back->setCustomName(Color::RED . "Back");
            $player->getInventory()->setItem(0, $portal);
            $player->getInventory()->setItem(1, $smoke);
            $player->getInventory()->setItem(2, $lava);
            $player->getInventory()->setItem(3, $heart);
            $player->getInventory()->setItem(4, $flame);
            $player->getInventory()->setItem(8, $back);
            $this->giveBoots($player);
            
        } else if ($item->getCustomName() === Color::DARK_PURPLE . "Portal") {
        	
        	if ($playerfile->get("portal1") === true) {
        	
        	$player->sendMessage($this->prefix . Color::GREEN . "Dein Ausgewaehlter Partikel wurde Aktiviert!");
            $playerfile->set("portal", true);
            $playerfile->set("smoke", false);
            $playerfile->set("lava", false);
            $playerfile->set("heart", false);
            $playerfile->set("flame", false);
            $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
		} else if ($playerfile->get("portal1") === false) {
			
		$player->sendMessage($this->prefix . Color::RED . "Du hast diesen Partikel nicht freigeschaltet!");
		
        }
        
    } else if ($item->getCustomName() === Color::GRAY . "Rauch") {
    	
    	if ($playerfile->get("smoke1") === true) {
        	
        	$player->sendMessage($this->prefix . Color::GREEN . "Dein Ausgewaehlter Partikel wurde Aktiviert!");
            $playerfile->set("portal", false);
            $playerfile->set("smoke", true);
            $playerfile->set("lava", false);
            $playerfile->set("heart", false);
            $playerfile->set("flame", false);
            $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
        } else if ($playerfile->get("smoke1") === false) {
			
		$player->sendMessage($this->prefix . Color::RED . "Du hast diesen Partikel nicht freigeschaltet!");
		
        }
        
        } else if ($item->getCustomName() === Color::GOLD . "Lava") {
        	
        	if ($playerfile->get("lava1") === true) {      
        	
        	$player->sendMessage($this->prefix . Color::GREEN . "Dein Ausgewaehlter Partikel wurde Aktiviert!");
            $playerfile->set("portal", false);
            $playerfile->set("smoke", false);
            $playerfile->set("lava", true);
            $playerfile->set("heart", false);
            $playerfile->set("flame", false);
            $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
        } else if ($playerfile->get("lava1") === false) {
			
		$player->sendMessage($this->prefix . Color::RED . "Du hast diesen Partikel nicht freigeschaltet!");
		
        }
        
        } else if ($item->getCustomName() === Color::RED . "Herz") {
        	
        	if ($playerfile->get("heart1") === true) {
        	
        	$player->sendMessage($this->prefix . Color::GREEN . "Dein Ausgewaehlter Partikel wurde Aktiviert!");
            $playerfile->set("portal", false);
            $playerfile->set("smoke", false);
            $playerfile->set("lava", false);
            $playerfile->set("heart", true);
            $playerfile->set("flame", false);
            $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
        } else if ($playerfile->get("heart1") === false) {
			
		$player->sendMessage($this->prefix . Color::RED . "Du hast diesen Partikel nicht freigeschaltet!");
		
        }
        
        } else if ($item->getCustomName() === Color::YELLOW . "Flamme") {
        	
        	if ($playerfile->get("flame1") === true) {
        	
        	$player->sendMessage($this->prefix . Color::GREEN . "Dein Ausgewaehlter Partikel wurde Aktiviert!");
            $playerfile->set("portal", false);
            $playerfile->set("smoke", false);
            $playerfile->set("lava", false);
            $playerfile->set("heart", false);
            $playerfile->set("flame", true);
            $playerfile->save();
            $player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		$this->giveBoots($player);
		
		} else if ($playerfile->get("flame1") === false) {
			
		$player->sendMessage($this->prefix . Color::RED . "Du hast diesen Partikel nicht freigeschaltet!");
		
        }
		
        } else if ($item->getCustomName() === Color::RED . "Spieler Verstecken") {
        	
        	$player->sendMessage(Color::DARK_PURPLE . "Alle Spieler sind nun Versteckt!");
            foreach ($this->getServer()->getOnlinePlayers() as $p) {
					
					$player->hidePlayer($p);
					
			}
			
			$player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::GREEN . "Spieler Anzeigen");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
		    $this->giveBoots($player);
		    
				
        } else if ($item->getCustomName() === Color::GREEN . "Spieler Anzeigen") {
        	
        	$player->sendMessage(Color::DARK_PURPLE . "Alle Spieler sind nun Sichtbar!");
            foreach ($this->getServer()->getOnlinePlayers() as $p) {
					
					$player->showPlayer($p);
					
			}
			
			$player->getInventory()->clearAll();
            $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$player->getInventory()->setItem(2, $acces);
		$player->getInventory()->setItem(4, $switch);
		$player->getInventory()->setItem(6, $hide);
			$this->giveBoots($player);
			
        } else if ($item->getCustomName() === Color::AQUA . "Boots") {
        }
        
        }
	
}

class Open extends Task {
	
	public function __construct($plugin) {
		
		$this->plugin = $plugin;
        parent::__construct($plugin);
        
    }
    
    public function onRun($tick) {
    	
    	$level = $this->plugin->getServer()->getDefaultLevel();
        $playersin = $level->getPlayers();
        foreach ($playersin as $p) {
        	
        	
        	$pf = new Config("/home/EnderCloud/players/" . $p->getName() . ".yml", Config::YAML);
        $pc = new Config("/home/EnderCloud/eCoins/" . $p->getName() . ".yml", Config::YAML);
            if ($pf->get("open") === true) {
            	
            	$pf->set("time", $pf->get("time") - 1);
                $pf->save();
                $time = $pf->get("time") + 1;
                $enchantment = Enchantment::getEnchantment(1);
                if ($time === 5) {
                	
                	$r = mt_rand(1, 10);
                    if ($r === 1) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $russi = Item::get(334, 0, 1);
                        $russi->setCustomName(Color::YELLOW . "Ra" . Color::GREEN . "in" . Color::RED . "bow" . Color::GRAY . " Ruessi");
                        $p->getInventory()->setItem(4, $russi);
                        
                    } else if ($r === 2) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $bomb = Item::get(351, 8, 1);
                        $bomb->setCustomName(Color::GRAY . "Bombe");
                        $p->getInventory()->setItem(4, $bomb);
                        
                    } else if ($r === 3) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $portal = Item::get(351, 5, 1);
                        $portal->setCustomName(Color::DARK_PURPLE . "Portal");
                        $p->getInventory()->setItem(4, $portal);
                        
                    } else if ($r === 4) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $smoke = Item::get(351, 8, 1);
                        $smoke->setCustomName(Color::GRAY . "Rauch");
                        $p->getInventory()->setItem(4, $smoke);                       
                        
                    } else if ($r === 5) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $lava = Item::get(351, 14, 1);
                        $lava->setCustomName(Color::GOLD . "Lava");
                        $p->getInventory()->setItem(4, $lava);                       
                        
                    } else if ($r === 6) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $heart = Item::get(351, 1, 1);
                        $heart->setCustomName(Color::RED . "Herz");
                        $p->getInventory()->setItem(4, $heart);                       
                        
                    } else if ($r === 7) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $flame = Item::get(351, 11, 1);
                        $flame->setCustomName(Color::YELLOW . "Flamme");
                        $p->getInventory()->setItem(4, $flame);                       
                        
                    } else if ($r === 8) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 9) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $gold = Item::get(41, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 10) {
                    	
                    	$pf->set("russi", false);
                    $pf->save();
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    }
                    
                } else if ($time === 4) {
                	
                	$r = mt_rand(1, 10);
                    if ($r === 1) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $russi = Item::get(334, 0, 1);
                        $russi->setCustomName(Color::YELLOW . "Ra" . Color::GREEN . "in" . Color::RED . "bow" . Color::GRAY . " Ruessi");
                        $p->getInventory()->setItem(4, $russi);
                        
                    } else if ($r === 2) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $bomb = Item::get(351, 8, 1);
                        $bomb->setCustomName(Color::GRAY . "Bombe");
                        $p->getInventory()->setItem(4, $bomb);
                        
                    } else if ($r === 3) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $portal = Item::get(351, 5, 1);
                        $portal->setCustomName(Color::DARK_PURPLE . "Portal");
                        $p->getInventory()->setItem(4, $portal);
                        
                    } else if ($r === 4) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $smoke = Item::get(351, 8, 1);
                        $smoke->setCustomName(Color::GRAY . "Rauch");
                        $p->getInventory()->setItem(4, $smoke);                       
                        
                    } else if ($r === 5) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $lava = Item::get(351, 14, 1);
                        $lava->setCustomName(Color::GOLD . "Lava");
                        $p->getInventory()->setItem(4, $lava);                       
                        
                    } else if ($r === 6) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $heart = Item::get(351, 1, 1);
                        $heart->setCustomName(Color::RED . "Herz");
                        $p->getInventory()->setItem(4, $heart);                       
                        
                    } else if ($r === 7) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $flame = Item::get(351, 11, 1);
                        $flame->setCustomName(Color::YELLOW . "Flamme");
                        $p->getInventory()->setItem(4, $flame);                       
                        
                    } else if ($r === 8) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 9) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(41, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 10) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    }
                	
                } else if ($time === 3) {
                	
                	$r = mt_rand(1, 10);
                    if ($r === 1) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $russi = Item::get(334, 0, 1);
                        $russi->setCustomName(Color::YELLOW . "Ra" . Color::GREEN . "in" . Color::RED . "bow" . Color::GRAY . " Ruessi");
                        $p->getInventory()->setItem(4, $russi);
                        
                    } else if ($r === 2) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $bomb = Item::get(351, 8, 1);
                        $bomb->setCustomName(Color::GRAY . "Bombe");
                        $p->getInventory()->setItem(4, $bomb);
                        
                    } else if ($r === 3) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $portal = Item::get(351, 5, 1);
                        $portal->setCustomName(Color::DARK_PURPLE . "Portal");
                        $p->getInventory()->setItem(4, $portal);
                        
                    } else if ($r === 4) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $smoke = Item::get(351, 8, 1);
                        $smoke->setCustomName(Color::GRAY . "Rauch");
                        $p->getInventory()->setItem(4, $smoke);                       
                        
                    } else if ($r === 5) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $lava = Item::get(351, 14, 1);
                        $lava->setCustomName(Color::GOLD . "Lava");
                        $p->getInventory()->setItem(4, $lava);                       
                        
                    } else if ($r === 6) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $heart = Item::get(351, 1, 1);
                        $heart->setCustomName(Color::RED . "Herz");
                        $p->getInventory()->setItem(4, $heart);                       
                        
                    } else if ($r === 7) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $flame = Item::get(351, 11, 1);
                        $flame->setCustomName(Color::YELLOW . "Flamme");
                        $p->getInventory()->setItem(4, $flame);                       
                        
                    } else if ($r === 8) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 9) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(41, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 10) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    }
                	
                } else if ($time === 2) {
                	
                	$r = mt_rand(1, 10);
                    if ($r === 1) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $russi = Item::get(334, 0, 1);
                        $russi->setCustomName(Color::YELLOW . "Ra" . Color::GREEN . "in" . Color::RED . "bow" . Color::GRAY . " Ruessi");
                        $p->getInventory()->setItem(4, $russi);
                        
                    } else if ($r === 2) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $bomb = Item::get(351, 8, 1);
                        $bomb->setCustomName(Color::GRAY . "Bombe");
                        $p->getInventory()->setItem(4, $bomb);
                        
                    } else if ($r === 3) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $portal = Item::get(351, 5, 1);
                        $portal->setCustomName(Color::DARK_PURPLE . "Portal");
                        $p->getInventory()->setItem(4, $portal);
                        
                    } else if ($r === 4) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $smoke = Item::get(351, 8, 1);
                        $smoke->setCustomName(Color::GRAY . "Rauch");
                        $p->getInventory()->setItem(4, $smoke);                       
                        
                    } else if ($r === 5) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $lava = Item::get(351, 14, 1);
                        $lava->setCustomName(Color::GOLD . "Lava");
                        $p->getInventory()->setItem(4, $lava);                       
                        
                    } else if ($r === 6) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $heart = Item::get(351, 1, 1);
                        $heart->setCustomName(Color::RED . "Herz");
                        $p->getInventory()->setItem(4, $heart);                       
                        
                    } else if ($r === 7) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $flame = Item::get(351, 11, 1);
                        $flame->setCustomName(Color::YELLOW . "Flamme");
                        $p->getInventory()->setItem(4, $flame);                       
                        
                    } else if ($r === 8) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 9) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(41, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 10) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    }
                	
                } else if ($time === 1) {
                	
                	$r = mt_rand(1, 10);
                    if ($r === 1) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $russi = Item::get(334, 0, 1);
                        $russi->setCustomName(Color::YELLOW . "Ra" . Color::GREEN . "in" . Color::RED . "bow" . Color::GRAY . " Ruessi");
                        $p->getInventory()->setItem(4, $russi);
                        
                    } else if ($r === 2) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $bomb = Item::get(351, 8, 1);
                        $bomb->setCustomName(Color::GRAY . "Bombe");
                        $p->getInventory()->setItem(4, $bomb);
                        
                    } else if ($r === 3) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $portal = Item::get(351, 5, 1);
                        $portal->setCustomName(Color::DARK_PURPLE . "Portal");
                        $p->getInventory()->setItem(4, $portal);
                        
                    } else if ($r === 4) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $smoke = Item::get(351, 8, 1);
                        $smoke->setCustomName(Color::GRAY . "Rauch");
                        $p->getInventory()->setItem(4, $smoke);                       
                        
                    } else if ($r === 5) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $lava = Item::get(351, 14, 1);
                        $lava->setCustomName(Color::GOLD . "Lava");
                        $p->getInventory()->setItem(4, $lava);                       
                        
                    } else if ($r === 6) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $heart = Item::get(351, 1, 1);
                        $heart->setCustomName(Color::RED . "Herz");
                        $p->getInventory()->setItem(4, $heart);                       
                        
                    } else if ($r === 7) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $flame = Item::get(351, 11, 1);
                        $flame->setCustomName(Color::YELLOW . "Flamme");
                        $p->getInventory()->setItem(4, $flame);                       
                        
                    } else if ($r === 8) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 9) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(41, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    } else if ($r === 10) {
                    	
                    	$pf->set("russi", false);
                        $p->getInventory()->clearAll();
                        $gold = Item::get(266, 0, 1);
                        $gold->setCustomName(Color::GOLD . "Coins");
                        $p->getInventory()->setItem(4, $gold);                       
                        
                    }
                	
                } else if ($time === 0) {
                	
                	$r = mt_rand(1, 15);
                    if ($r === 1) {
                    	
                    	$pf->set("russi", false);
                        $pf->set("russi1", true);
                        $pc->set("coins", $pc->get("coins")-5000);
                        $pf->save();
                        $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
                        $p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Ranibow Ruessi gewonnen!");
                        $this->plugin->giveBoots($p);
                        
                    } else if ($r === 2) {
                    	
                    	$pf->set("russi", false);
                    $pf->set("bombe", true);
                    $pc->set("coins", $pc->get("coins")-5000);
                        $pf->save();
                        $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Bombe gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 3) {
                    	
                    	$pf->set("russi", false);
                    $pf->set("portal1", true);
                    $pc->set("coins", $pc->get("coins")-5000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
                        $p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Portal Partikel gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 4) {
                    	
                    	$pf->set("russi", false);
                    $pf->set("smoke1", true);
                    $pc->set("coins", $pc->get("coins")-5000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
                        $p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Rauch Partikel gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 5) {
                    	
                    	$pf->set("russi", false);
                    $pf->set("lava1", true);
                    $pc->set("coins", $pc->get("coins")-5000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
                        $p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Lava Partikel gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 6) {
                    	
                    	$pf->set("russi", false);
                    $pf->set("heart1", true);
                    $pc->set("coins", $pc->get("coins")-5000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
                        $p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Herz Partikel gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 7) {
                    	
                    	$pf->set("russi", false);
                    $pf->set("flame1", true);
                    $pc->set("coins", $pc->get("coins")-5000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
                        $p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Flammen Partikel gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 8) {
                    	
                    	$pf->set("russi", false);
                    $pc->set("coins", $pc->get("coins")-5000);
                        $pc->set("coins", $pc->get("coins")+2000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast 2000 Coins gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 9) {
                    	
                    	$pf->set("russi", false);
                    $pc->set("coins", $pc->get("coins")-5000);
                        $pc->set("coins", $pc->get("coins")+10000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast 10000 Coins gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 10) {
                    	
                    	$pf->set("russi", false);
                    $pc->set("coins", $pc->get("coins")-5000);
                        $pc->set("coins", $pc->get("coins")+500);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast 500 Coins gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 11) {
                    	
                        $p->getInventory()->clearAll();
                        $pf->set("russi", false);
                        $pf->set("Jump", true);
                    $pc->set("coins", $pc->get("coins")-5000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Jump Boots gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 12) {
                    	
                        $p->getInventory()->clearAll();
                        $pf->set("russi", false);
                        $pf->set("Speed", true);
                    $pc->set("coins", $pc->get("coins")-5000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast die Speed Boots gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 13) {
                    	
                    	$pf->set("russi", false);
                    $pc->set("coins", $pc->get("coins")-5000);
                        $pc->set("coins", $pc->get("coins")+1000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast 1000 Coins gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 14) {
                    	
                    	$pf->set("russi", false);
                    $pc->set("coins", $pc->get("coins")-5000);
                        $pc->set("coins", $pc->get("coins")+12500);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast 12500 Coins gewonnen!");
                        $this->plugin->giveBoots($p);
                    } else if ($r === 15) {
                    	
                    	$pf->set("russi", false);
                    $pc->set("coins", $pc->get("coins")-5000);
                        $pc->set("coins", $pc->get("coins")+5000);
                    $pf->save();
                    $pc->save();
                        $p->getInventory()->clearAll();
                        $enchantment = Enchantment::getEnchantment(1);
        $switch = Item::get(399, 0, 1);
        $acces = Item::get(130, 0, 1);
        $switcher = Item::get(352, 0, 1);
        $hide = Item::get(351, 5, 1);
        $acces->setCustomName(Color::AQUA . "Accessoires");
		$hide->setCustomName(Color::RED . "Spieler Verstecken");
		$switcher->setCustomName(Color::GOLD . "LobbyFight");
		$switcher->addEnchantment($enchantment);
		$switch->setCustomName(Color::GOLD . "LobbySwitcher");
		$switch->addEnchantment($enchantment);
		$p->getInventory()->setItem(2, $acces);
		$p->getInventory()->setItem(4, $switch);
		$p->getInventory()->setItem(6, $hide);
		$p->sendMessage($this->plugin->prefix . Color::GREEN . "Du hast 5000 Coins gewonnen!");
                        $this->plugin->giveBoots($p);
                    }
                    
                    $pf->set("open", false);
                    $pf->set("time", 5);
                    $pf->save();
                	
                }
                
            }
            
        }
        
    }
	
}

class Anzeige extends Task {
	
	public function __construct($plugin) {
    
        $this->plugin = $plugin;
        parent::__construct($plugin);
        
    }
    
    public function onRun($tick) {
    	
    	$level = $this->plugin->getServer()->getDefaultLevel();
        $playersin = $level->getPlayers();
        $all = $this->plugin->getServer()->getOnlinePlayers();
        foreach ($playersin as $p) {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $p->getName() . ".yml", Config::YAML);
            $p->setFood(20);
            if ($pf->get("russi") === true) {
            	
            	$r = mt_rand(1, 10);
                if ($r === 1) {
                	
                	$p->getInventory()->setHelmet(Item::get(298, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(299, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(300, 0, 1));
                    $p->getInventory()->setBoots(Item::get(301, 0, 1));
                    
                } else if ($r === 2) {
                	
                	$p->getInventory()->setHelmet(Item::get(302, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(303, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(304, 0, 1));
                    $p->getInventory()->setBoots(Item::get(305, 0, 1));
                    
                } else if ($r === 3) {
                	
                	$p->getInventory()->setHelmet(Item::get(306, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(307, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(308, 0, 1));
                    $p->getInventory()->setBoots(Item::get(309, 0, 1));
                    
                } else if ($r === 4) {
                	
                	$p->getInventory()->setHelmet(Item::get(310, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(311, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(312, 0, 1));
                    $p->getInventory()->setBoots(Item::get(313, 0, 1));
                    
                } else if ($r === 5) {
                	
                	$p->getInventory()->setHelmet(Item::get(314, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(315, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(316, 0, 1));
                    $p->getInventory()->setBoots(Item::get(317, 0, 1));
                    
                } else if ($r === 6) {
                	
                	$p->getInventory()->setHelmet(Item::get(310, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(315, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(308, 0, 1));
                    $p->getInventory()->setBoots(Item::get(313, 0, 1));
                    
                } else if ($r === 7) {
                	
                	$p->getInventory()->setHelmet(Item::get(302, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(299, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(316, 0, 1));
                    $p->getInventory()->setBoots(Item::get(313, 0, 1));
                    
                } else if ($r === 8) {
                	
                	$p->getInventory()->setHelmet(Item::get(302, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(311, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(316, 0, 1));
                    $p->getInventory()->setBoots(Item::get(317, 0, 1));
                    
                } else if ($r === 9) {
                	
                	$p->getInventory()->setHelmet(Item::get(314, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(299, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(316, 0, 1));
                    $p->getInventory()->setBoots(Item::get(313, 0, 1));
                    
                } else if ($r === 10) {
                	
                	$p->getInventory()->setHelmet(Item::get(298, 0, 1));
                    $p->getInventory()->setChestplate(Item::get(303, 0, 1));
                    $p->getInventory()->setLeggings(Item::get(316, 0, 1));
                    $p->getInventory()->setBoots(Item::get(313, 0, 1));
                    
                }
                
            }
             
        }
        
    }
	
}

class JoinText extends PluginTask
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
        foreach ($all as $player) {
        	
        	$pf = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
        
        	
        if ($pf->get("join") < 6) {
            $pf->set("join", $pf->get("join") - 1);
                $pf->save();
                $time = $pf->get("join") + 1;
                if ($time === 0) {
                	$player->addTitle(Color::DARK_PURPLE . "EnderCubePE.de", Color::GRAY . "Your Network", 20, 40, 20);
                	$pf->set("join", 10);
                    $pf->save();
                }
             }
        }
    	
    }
	
}