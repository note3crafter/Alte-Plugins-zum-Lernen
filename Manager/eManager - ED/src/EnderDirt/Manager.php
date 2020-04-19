<?php

namespace EnderDirt;

//Base
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\server\QueryRegenerateEvent;
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
//GUI
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class Manager extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "e" . Color::WHITE . "Manager] ";
	
	public $listener;
	
	public function onEnable() {
		
		$this->getLogger()->info($this->prefix . Color::GREEN . "lade das Plugin!");
		
		if (is_dir($this->getDataFolder()) !== true) {
        	
            mkdir($this->getDataFolder());
            
        }
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		if (!is_file("/home/EnderCloud/Daten.yml")) {
			
			$this->getLogger()->info($this->prefix . Color::GREEN . "erstellen der Cloud!");
			
			if (is_dir("/home/EnderCloud") !== true) {
			
                mkdir("/home/EnderCloud");
            
            }
            
            if (is_dir("/home/EnderCloud/players") !== true) {
			
                mkdir("/home/EnderCloud/players");
            
            }
            
            if (is_dir("/home/EnderCloud/eCoins") !== true) {
			
                mkdir("/home/EnderCloud/eCoins");
            
            }
            
            if (is_dir("/home/EnderCloud/eGroups") !== true) {
			
                mkdir("/home/EnderCloud/eGroups");
            
            }
            
            if (is_dir("/home/EnderCloud/eGroups/Clans") !== true) {
			
                mkdir("/home/EnderCloud/eGroups/Clans");
            
            }
            
            if (is_dir("/home/EnderCloud/SchwitzerWars") !== true) {
			
                mkdir("/home/EnderCloud/SchwitzerWars");
            
            }
            
            if (is_dir("/home/EnderCloud/UHCMeetup") !== true) {
			
                mkdir("/home/EnderCloud/UHCMeetup");
            
            }
			
			$clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
			$server = new Config("/home/EnderCloud/Servers/Main.yml", Config::YAML);
			
			$clouddata->set("Main", true);
			$clouddata->set("Online", 0);
			$clouddata->set("ServerMessageStatus", false);
			$clouddata->set("ServerMessage", "");
			$clouddata->save();
			
			$this->saveDefaultConfig();
            $this->reloadConfig();

            $config = $this->getConfig();
        
            $config->set("Server", "Main");
            $config->save();
			
			$this->getLogger()->info($this->prefix . Color::GREEN . "die Cloud wurde erfolgreich erstellt!");
			
        } else {
        	
        	$this->getLogger()->info($this->prefix . Color::GREEN . "verbindung zur Cloud erfolgreich hergestellt!");
        	
        }
        
        $this->listener = new ManagerListener($this);
        
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new MessageServer($this), 10);
        $this->getLogger()->info($this->prefix . Color::GREEN . "die Cloud wurde erfolgreich geladen!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::DARK_PURPLE . " EnderDirt!");
		
    }
    
    public function onLogin(PlayerLoginEvent $event) {
    	
    	$player = $event->getPlayer();
        $config = $this->getConfig();
        $clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
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
            $playerfile->set("Lobby", "Main");
            $playerfile->save();
        	
        }
        
        if ($clouddata->get("Main") === false) {
        	
        	if ($player->isOp()) {
        	
            } else {
            	
            	$player->kick(
                Color::RED . "Das Server Netzwerk ist gerade in Wartungsarbeiten\n" .
                Color::AQUA . "Twitter: " . Color::YELLOW . "@cube_ender\n" .
                Color::GRAY . "Discord: " . Color::YELLOW . "https://discord.gg/cX9Ncmn", false
                );
            	
            }
        	
        } else {
        	
        	$cloudplayer = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            if ($cloudplayer->get("Ban") === true) {
            	
            	$player->kick(
                Color::RED . "Du wurdest Gebannt\n" .
                Color::GRAY . "Grund: " . Color::YELLOW . $cloudplayer->get("BanGrund") . "\n" .
                Color::GRAY . "Dauer: " . Color::YELLOW . $cloudplayer->get("BanTime") . " Tag/e\n", false
                );
            	
            }
        	
        }
    	
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "Server") {
    	
    	    $group = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
            if ($group->get("Moderator") === true) {
            	
            	$this->sendManagerGUI2($sender);
            	
            }
            
    	    if ($sender->isOp()) {
    	
    	        $this->sendGUI($sender);
    
            }
            
        }
        
        return true;
    	
    }
    
    public function sendManagerGUI(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§l§6Server Manager';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§4Wartungsarbeiten'];
        $fdata['buttons'][] = ['text' => '§6Server-Log'];
        $fdata['buttons'][] = ['text' => '§eMulti-Chat'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 1;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
    public function sendGui(Player $sender) {
    	
        $fdata = [];

        $fdata['title'] = '§6Shop';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§2Swapper'];
        $fdata['buttons'][] = ['text' => '§4SOON'];
        $fdata['buttons'][] = ['text' => '§4SOON'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 1;
        $pk->formData = json_encode($fdata);

        $sender->sendDataPacket($pk);
        
    }
    
    public function sendManagerGUI2(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§l§6Server Manager';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';
        
        $fdata['buttons'][] = ['text' => '§eMulti-Chat'];
        $fdata['buttons'][] = ['text' => '§4SOON'];
        $fdata['buttons'][] = ['text' => '§4SOON'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 2;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
}

class MessageServer extends PluginTask {

    public function __construct($plugin)
    {

        $this->plugin = $plugin;
        parent::__construct($plugin);

    }

    public function onRun($tick) {
    	
        $all = $this->plugin->getServer()->getOnlinePlayers();
        foreach ($all as $player) {
        	
        	$cloudplayer = new Config("/home/EnderCloud/players/" . $player->getName() . ".yml", Config::YAML);
            if ($cloudplayer->get("ServerLog") === true) {
            	
            	$clouddata = new Config("/home/EnderCloud/Daten.yml", Config::YAML);
                if ($clouddata->get("ServerMessageStatus") === true) {
                	
                	$clouddata->set("ServerMessageStatus", false);
                    $clouddata->save();
                	$msg = $clouddata->get("ServerMessage");
                    $player->sendMessage(Color::YELLOW . $msg);
                	
                }
            	
            }
        	
        }

    }

}