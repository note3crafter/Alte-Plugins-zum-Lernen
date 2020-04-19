<?php

namespace EnderDirt;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
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
use pocketmine\level\sound\BlazeShootSound;
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
use pocketmine\event\player\PlayerChatEvent;

class Main extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::DARK_PURPLE . "eGroups" . Color::WHITE . "] ";
	public $nicks = ["PorscheBoy", "ModeFNA", "Ronaldo7", "BedWarsTMS", "VipGames", "MCPE_Nicht", "DrogenEye", "DBEa", "PvpPro3", "IchBinDeinFan", "TheFas2P", "xpypypy", "MrTee", "GGGamer", "depp123", "Pupsblue", "Gommwaffl", "BestHDMik", "BubugagaLp", "Optimus10", "EzImMax", "CuzImSkill", "NebzFreak", "CowLer", "Elexieres", "Freesionce", "Wicess", "MarieXNice", "IronedPvP", "Likepvez", "Freezestyler", "H4xvr", "MaxIsLikes", "Bluenchen", "XxPvPlerXX", "Madienblack", "PPAP", "HitsLikesX", "ItzJusteZ", "RlyBestPvP110", "Com3back", "Hell0W0rld", "ItzJustin", "Bedip2003", "Bahmutshari20", "BigManofMCPE", "Qwertz123", "Skywarsgamer7", "ItzOPPvE", "Kuhlman77", "LuluBoy", "Luki", "Maschine", "Stevina", "Alessio", "WeilMeinRuf", "HansPeterKuhl"];
	public $players = [];
	
	public function onEnable() {
		
		@mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
        
    }
    
    public function onChat(PlayerChatEvent $event) {
    	
    	$player = $event->getPlayer();
        $msg = $event->getMessage();
        $pf = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        $clan = $pf->get("Clan");
        if ($pf->get("ClanI") === true) {
        	
        	$event->setFormat(Color::WHITE . "[" . Color::YELLOW . $clan . Color::WHITE . "] " . $player->getDisplayName() . " : " . $msg);
        
        } else {
        	
        	$event->setFormat($player->getDisplayName() . " : " . $msg);
        	
        }
        
    }
    
    public function onJoin(PlayerJoinEvent $event) {
    	
    	$player = $event->getPlayer();
        $pf = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
        if ($pf->get("ClanI") === false) {
        	
        	$player->sendMessage(Color::GREEN . "Der Clan " . Color::YELLOW . $pf->get("Clan") . Color::GREEN . " hat dir eine Clan einladung geschickt!");
            $player->sendMessage(Color::GREEN . "Um die Anfrage anzunehmen /clan accept");
        	
        }
        
            if ($pf->get("Default") === true) {
            	
                $player->setDisplayName(Color::GRAY . $player->getName());
                $player->setNameTag(Color::GRAY . $player->getName());
                
            } else if ($pf->get("Owner") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$this->nick_off($player);
            	$this->nick_on($player);
                
                } else {
                
                $player->setDisplayName(Color::DARK_RED . $player->getName() . Color::WHITE);
                $player->setNameTag(Color::DARK_RED . $player->getName() . Color::WHITE);
                
                }
                
            } else if ($pf->get("Admin") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$this->nick_off($player);
            	$this->nick_on($player);
                
                } else {
                
                $player->setDisplayName(Color::RED . $player->getName() . Color::WHITE);
                $player->setNameTag(Color::RED . $player->getName() . Color::WHITE);
                }
                } else if ($pf->get("Builder") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$this->nick_off($player);
            	$this->nick_on($player);
                
                } else {
                	
                $player->setDisplayName(Color::GREEN . $player->getName() . Color::WHITE);
                $player->setNameTag(Color::GREEN . $player->getName() . Color::WHITE);
             
                }
                
            } else if ($pf->get("Moderator") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$this->nick_off($player);
            	$this->nick_on($player);
                
                } else {
            
                $player->setDisplayName(Color::BLUE . $player->getName() . Color::WHITE);
                $player->setNameTag(Color::BLUE . $player->getName() . Color::WHITE);
                
                }
                
            } else if ($pf->get("YouTuber") === true) {
                        	                       	
            	if ($pf->get("Nick") === true) {
            	
            	$this->nick_off($player);
            	$this->nick_on($player);
                
                } else {
            
                $player->setDisplayName(Color::DARK_PURPLE . $player->getName() . Color::WHITE);
                $player->setNameTag(Color::DARK_PURPLE . $player->getName() . Color::WHITE);
                
                }
                
            } else if ($pf->get("VIP") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$this->nick_off($player);
            	$this->nick_on($player);
                
                } else {
                	
                $player->setDisplayName(Color::GOLD . $player->getName() . Color::WHITE);
                $player->setNameTag(Color::GOLD . $player->getName() . Color::WHITE);
                
                }
                
            }
            
    }
    
    public function nick_on(Player $player) {
		$playerfile = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
		if ($playerfile->get("NickP") === true) {
			
		if (!isset($this->players[strtolower($player->getName())])) {
			
		  if (count($this->nicks) !== 0) {
			
			  $nickNum = mt_rand(0, count($this->nicks)+1);
			  $player->setDisplayName($this->nicks[$nickNum]);
			  $player->setNameTag($this->nicks[$nickNum]);
			  $player->setDisplayName(Color::GRAY . $this->nicks[$nickNum]);
                $player->setNameTag(Color::GRAY . $this->nicks[$nickNum]);
            $playerfile->set("Nick", true);
            $playerfile->save();
			  $this->players[strtolower($player->getName())] = $this->nicks[$nickNum];
			  unset($this->nicks[$nickNum]);
			  $player->sendMessage($this->prefix."Dein Nickname ist nun ".Color::GOLD."".$player->getDisplayName().Color::BLUE."!");
			
		  } else {
			
			  $player->sendMessage($this->prefix.Color::RED."Keine Nicknames verfuegbar!");
			
		  }
		
	  } else {
		
		  $player->sendMessage($this->prefix.Color::RED."Du benutzt bereits einen Nickname!");
		
		}
		
	  }
		
	}
	
	public function nick_off(Player $player) {
		
		if (isset($this->players[($name = strtolower($player->getName()))])) {
			
			array_push($this->nicks, $this->players[$name]);
			$player->setDisplayName($player->getName());
			$player->setNameTag($player->getName());
			$playerfile = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Nick", false);
            $playerfile->save();
			unset($this->players[$name]);
			$player->sendMessage($this->prefix.Color::YELLOW."Dein Nickname wurde entfernt".Color::BLUE."!");
			
		} else {
			
			$player->sendMessage($this->prefix.Color::RED."Du benutzt keinen Nickname".Color::BLUE."!");
			
		}
		
	}
    
    public function onPlayerLogin(PlayerLoginEvent $event) {
    
        $player = $event->getPlayer();        
        if (!is_file("/home/EnderCloud/eGroups/" . $player->getName() . ".yml")) {
        
            $playerfile = new Config("/home/EnderCloud/eGroups/" . $player->getName() . ".yml", Config::YAML);
            $playerfile->set("Default", true);
            $playerfile->set("Owner", false);
            $playerfile->set("Admin", false);
            $playerfile->set("Builder", false);
            $playerfile->set("Moderator", false);
            $playerfile->set("YouTuber", false);
            $playerfile->set("VIP", false);
            $playerfile->set("Nick", false);
            $playerfile->set("NickP", false);
            $playerfile->save();
            
        }
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	switch ($command->getName()) {
    	
     	   case "Group":
            if (isset($args[0])) {
            	
            	if (file_exists("/home/EnderCloud/eGroups/" . $args[0] . ".yml")) {
            	
            	    $playerfile = new Config("/home/EnderCloud/eGroups/" . $args[0] . ".yml", Config::YAML);
            	    if ($sender->isOp()) {
            	
            	        if (isset($args[1])) {
            	
            	            if (strtolower($args[1]) === "default") {
            	
            	                $playerfile->set("Default", true);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", false);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "owner") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", true);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "admin") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", true);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "builder") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", true);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "moderator") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", true);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "youtuber") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("YouTuber", true);
                                $playerfile->set("VIP", false);
                                $playerfile->set("NickP", true);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            } else if (strtolower($args[1]) === "vip") {
                            	
                            	$playerfile->set("Default", false);
                                $playerfile->set("Owner", false);
                                $playerfile->set("Admin", false);
                                $playerfile->set("Builder", false);
                                $playerfile->set("Moderator", false);
                                $playerfile->set("YouTuber", false);
                                $playerfile->set("VIP", true);
                                $playerfile->set("NickP", false);
                                $playerfile->save();
                                $sender->sendMessage($this->prefix . "Die Gruppe wurde getauscht!");
                                
                            }
                            
                        }
                        
                    }
                    
                } else if (strtolower($args[0]) === "addperm") {
                	
                }
                
            }
            
        }
        
        if ($command->getName() === "Nick") {
        	$playerfile = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
        	if ($playerfile->get("NickP") === true) {
        	$this->nick_on($sender);
        
            $playerfile->set("Nick", true);
            $playerfile->save();
        } else {
        	$sender->sendMessage($this->prefix . "Keine Berechtigung!");
        }
        } else if ($command->getName() === "Unnick") {
        	
        	$this->nick_off($sender);
        $pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
            $pf->set("Nick", false);
            $pf->save();
            if ($pf->get("Default") === true) {
            	
                $sender->setDisplayName(Color::GRAY . $sender->getName());
                $sender->setNameTag(Color::GRAY . $sender->getName());
                
            } else if ($pf->get("Owner") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$sender->setDisplayName(Color::GRAY . $sender->getName());
                $sender->setNameTag(Color::GRAY . $sender->getName());
                
                } else {
                
                $sender->setDisplayName(Color::DARK_RED . $sender->getName() . Color::WHITE);
                $sender->setNameTag(Color::DARK_RED . $sender->getName() . Color::WHITE);
                
                }
                
            } else if ($pf->get("Admin") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$sender->setDisplayName(Color::GRAY . $sender->getName());
                $sender->setNameTag(Color::GRAY . $sender->getName());
                
                } else {
                
                $sender->setDisplayName(Color::RED . $sender->getName() . Color::WHITE);
                $sender->setNameTag(Color::RED . $sender->getName() . Color::WHITE);
                
                }
                
            } else if ($pf->get("Builder") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$sender->setDisplayName(Color::GRAY . $sender->getName());
                $sender->setNameTag(Color::GRAY . $sender->getName());
                
                } else {
                	
                $sender->setDisplayName(Color::GREEN . $sender->getName() . Color::WHITE);
                $sender->setNameTag(Color::GREEN . $sender->getName() . Color::WHITE);
                
                }
                
            } else if ($pf->get("Moderator") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$sender->setDisplayName(Color::GRAY . $sender->getName());
                $sender->setNameTag(Color::GRAY . $sender->getName());
                
                } else {
            
                $sender->setDisplayName(Color::BLUE . $sender->getName() . Color::WHITE);
                $sender->setNameTag(Color::BLUE . $sender->getName() . Color::WHITE);
                
                }
                
            } else if ($pf->get("YouTuber") === true) {
                        	                       	
            	if ($pf->get("Nick") === true) {
            	
            	$sender->setDisplayName(Color::GRAY . $sender->getName());
                $sender->setNameTag(Color::GRAY . $sender->getName());
                
                } else {
            
                $sender->setDisplayName(Color::DARK_PURPLE . $sender->getName() . Color::WHITE);
                $sender->setNameTag(Color::DARK_PURPLE . $sender->getName() . Color::WHITE);
                
                }
                
            } else if ($pf->get("VIP") === true) {
            	
            	if ($pf->get("Nick") === true) {
            	
            	$sender->setDisplayName(Color::GRAY . $sender->getName());
                $sender->setNameTag(Color::GRAY . $sender->getName());
                
                } else {
                	
                $sender->setDisplayName(Color::GOLD . $sender->getName() . Color::WHITE);
                $sender->setNameTag(Color::GOLD . $sender->getName() . Color::WHITE);
                
                }
                
            }
        
        }
        
        if ($command->getName() === "VIP") {
        	
        	if (isset($args[0])) {
        	    $playerfile = new Config("/home/EnderCloud/players/" . $args[0] . ".yml", Config::YAML);
                $playerfile->set("VIP", true);
                $playerfile->save();
                $sender->sendMessage("Erfolgreich");
            }
        	
        }
        
        if ($command->getName() === "Clan") {
            
        	if (isset($args[0])) {
        	
        	    if (strtolower($args[0]) === "help") {
        	
        	$sender->sendMessage(Color::GRAY . "-> " . Color::GOLD . "/clan make <Name>");
            $sender->sendMessage(Color::GRAY . "-> " . Color::GOLD . "/clan add <PlayerName>");
            $sender->sendMessage(Color::GRAY . "-> " . Color::GOLD . "/clan leave");
            $sender->sendMessage(Color::GRAY . "-> " . Color::GOLD . "/clan accept");
            $sender->sendMessage(Color::GRAY . "-> " . Color::GOLD . "/clan info <ClanName>");
            
                }
                
                if (strtolower($args[0]) === "leave") {
                	
                	$pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);	
                	$clan = new Config("/home/EnderCloud/eGroups/Clans/" . $pf->get("Clan") . ".yml", Config::YAML);
                    if ($sender->getName() === $clan->get("Owner")) {
                    	
                    	$p2 = $clan->get("p2");
                        $p3 = $clan->get("p3");
                        $p4 = $clan->get("p4");
                        $p5 = $clan->get("p5");
                        $p6 = $clan->get("p6");
                        $p7 = $clan->get("p7");
                        $p8 = $clan->get("p8");
                        $p9 = $clan->get("p9");
                        $p10 = $clan->get("p10");
                        $p11 = $clan->get("p11");
                        $p12 = $clan->get("p12");
                        $p13 = $clan->get("p13");
                        $p14 = $clan->get("p14");
                        $p15 = $clan->get("p15");
                        
                        $clan->set("Owner", $p2);
                        $clan->set("p2", $p3);
                        $clan->set("p3", $p4);
                        $clan->set("p4", $p5);
                        $clan->set("p5", $p6);
                        $clan->set("p6", $p7);
                        $clan->set("p7", $p8);
                        $clan->set("p8", $p9);
                        $clan->set("p9", $p10);
                        $clan->set("p10", $p11);
                        $clan->set("p11", $p12);
                        $clan->set("p12", $p13);
                        $clan->set("p13", $p14);
                        $clan->set("p14", $p15);
                        $clan->set("Players", $clan->get("Players")-1);
                        $clan->save();
                        $pf->set("Clan", "");
                        $pf->set("ClanI", false);
                        $pf->save();
                        $sender->sendMessage(Color::GREEN . "Du hast deinen Clan verlassen!");
                    	
                    } else {
                    	
                    	$p2 = $clan->get("p2");
                        $p3 = $clan->get("p3");
                        $p4 = $clan->get("p4");
                        $p5 = $clan->get("p5");
                        $p6 = $clan->get("p6");
                        $p7 = $clan->get("p7");
                        $p8 = $clan->get("p8");
                        $p9 = $clan->get("p9");
                        $p10 = $clan->get("p10");
                        $p11 = $clan->get("p11");
                        $p12 = $clan->get("p12");
                        $p13 = $clan->get("p13");
                        $p14 = $clan->get("p14");
                        $p15 = $clan->get("p15");
                        
                        $clan->set("p2", $p3);
                        $clan->set("p3", $p4);
                        $clan->set("p4", $p5);
                        $clan->set("p5", $p6);
                        $clan->set("p6", $p7);
                        $clan->set("p7", $p8);
                        $clan->set("p8", $p9);
                        $clan->set("p9", $p10);
                        $clan->set("p10", $p11);
                        $clan->set("p11", $p12);
                        $clan->set("p12", $p13);
                        $clan->set("p13", $p14);
                        $clan->set("p14", $p15);
                        $clan->set("Players", $clan->get("Players")-1);
                        $clan->save();
                        $pf->set("Clan", "");
                        $pf->set("ClanI", false);
                        $pf->save();
                        $sender->sendMessage(Color::GREEN . "Du hast deinen Clan verlassen!");
                    	
                    }
                	
                }
                
                if (strtolower($args[0]) === "info") {
                	
                	if (isset($args[1])) {
                	
                	    $clan = new Config("/home/EnderCloud/eGroups/Clans/" . $args[1] . ".yml", Config::YAML);
                        $sender->sendMessage(Color::GRAY . "Name -> " . Color::GOLD . $args[1]);
                        $sender->sendMessage(Color::GRAY . "Mitglieder -> " . Color::GOLD . $clan->get("Players") . "/15");
                        $sender->sendMessage(Color::GRAY . "Besitzer -> " . Color::GOLD . $clan->get("Owner"));
                        
                    } else {
                    	
                    	$pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                    	$clan = new Config("/home/EnderCloud/eGroups/Clans/" . $pf->get("Clan") . ".yml", Config::YAML);
                        $sender->sendMessage(Color::GRAY . "Name -> " . Color::GOLD . $pf->get("Clan"));
                        $sender->sendMessage(Color::GRAY . "Mitglieder -> " . Color::GOLD . $clan->get("Players") . "/15");
                        $sender->sendMessage(Color::GRAY . "Besitzer -> " . Color::GOLD . $clan->get("Owner"));
                    	
                    }
                	
                }
        	
        	    if (strtolower($args[0]) === "make") {
        	
        	        if (isset($args[1])) {
        	
        	            $pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        if ($pf->get("Clan") === true) {
                        	
                        	$sender->sendMessage(Color::RED . "Du bist schon in einem Clan!");
                        
                        } else {
                        	
                        	if (file_exists("/home/EnderCloud/eGroups/Clans/" . $args[1] . ".yml")) {
                        	
                        	    $sender->sendMessage(Color::RED . "Diesen Clan gibt es schon!");
                        
                            } else {
                            	
                            	$clan = new Config("/home/EnderCloud/eGroups/Clans/" . $args[1] . ".yml", Config::YAML);
                                $clan->set("Owner", $sender->getName());
                                $clan->set("Players", 1);
                                $clan->save();
                                $pf->set("Clan", $args[1]);
                                $pf->set("ClanI", true);
                                $pf->save();
                                $sender->sendMessage(Color::GREEN . "Der Clan wurde erfolgreich erstellt!");
                            	
                            }
                        	
                        }
                        
                    }
                    
                }
                
                if (strtolower($args[0]) === "add") {
                	
                	if (isset($args[1])) {
                	
                	    $pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        if ($pf->get("Clan") === false) {
                        	
                        	$sender->sendMessage(Color::RED . "Du bist in keinem Clan!");
                        
                        } else {
                        	
                        	$af = new Config("/home/EnderCloud/eGroups/" . $args[1] . ".yml", Config::YAML);
                            if (file_exists("/home/EnderCloud/eGroups/" . $args[1] . ".yml")) {
                            	
                            if ($af->get("ClanI") === true) {
                            	
                            	$sender->sendMessage(Color::RED . "Dieser Spieler ist derzeit in einem anderen Clan schon!");
                            
                            } else {
                            	
                            	$af->set("ClanA", $pf->get("Clan"));
                                $af->save();
                                $sender->sendMessage(Color::GREEN . "Die Clan Einladung wurde erfolgreich verschickt!");
                                $v = $this->getServer()->getPlayerExact($args[1]);	
                                if (!$v == null) {
                                	
                                	$v->sendMessage(Color::GREEN . "Der Clan " . Color::YELLOW . $pf->get("Clan") . Color::GREEN . " hat dir eine Clan einladung geschickt!");
                                    $v->sendMessage(Color::GREEN . "Um die Anfrage anzunehmen /clan accept");
                                	
                                }
                                
                                }
                            	
                            } else {
                            	
                            	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht!");
                            	
                            }
                        	
                        }
                        
                    }
                    
                }
                
                if (strtolower($args[0]) === "accept") {
                	
                	    $pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        $clan = $pf->get("ClanA");
                        $cf = new Config("/home/EnderCloud/eGroups/Clans/" . $clan . ".yml", Config::YAML);
                        if ($cf->get("Players") === 1) {
                        	
                        	$cf->set("p2", $sender->getName());
                            $cf->set("Players", 2);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 2) {
                        	
                        	$cf->set("p3", $sender->getName());
                            $cf->set("Players", 3);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 3) {
                        	
                        	$cf->set("p4", $sender->getName());
                            $cf->set("Players", 4);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 4) {
                        	
                        	$cf->set("p5", $sender->getName());
                            $cf->set("Players", 5);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 5) {
                        	
                        	$cf->set("p6", $sender->getName());
                            $cf->set("Players", 6);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 6) {
                        	
                        	$cf->set("p7", $sender->getName());
                            $cf->set("Players", 7);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 7) {
                        	
                        	$cf->set("p8", $sender->getName());
                            $cf->set("Players", 8);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 8) {
                        	
                        	$cf->set("p9", $sender->getName());
                            $cf->set("Players", 9);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 9) {
                        	
                        	$cf->set("p10", $sender->getName());
                            $cf->set("Players", 10);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 10) {
                        	
                        	$cf->set("p11", $sender->getName());
                            $cf->set("Players", 11);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 11) {
                        	
                        	$cf->set("p12", $sender->getName());
                            $cf->set("Players", 12);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 12) {
                        	
                        	$cf->set("p13", $sender->getName());
                            $cf->set("Players", 13);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 13) {
                        	
                        	$cf->set("p14", $sender->getName());
                            $cf->set("Players", 14);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") === 14) {
                        	
                        	$cf->set("p15", $sender->getName());
                            $cf->set("Players", 15);
                            $cf->save();
                            $sender->sendMessage(Color::GREEN . "Du bist erfolgreich diesem Clan beigetreten!");
                            $pf->set("ClanI", true);
                            $pf->set("Clan", $clan);
                            $pf->save();
                        	
                        } else if ($cf->get("Players") > 15) {
                        	
                            $sender->sendMessage(Color::RED . "Dieser Clan ist voll!");
                        	
                        }
                	
                }
                
            }
            
        }
        
        return true;
        
    }
	
}