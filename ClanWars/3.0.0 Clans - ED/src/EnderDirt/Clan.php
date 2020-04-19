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

class Clan extends PluginBase implements Listener {
	
	public $prefix = Color::WHITE . "[" . Color::YELLOW . "Clans" . Color::WHITE . "] ";
	
	public function onEnable() {
		
		if (is_dir("/home/EnderCloud/Clans") !== true) {

            mkdir("/home/EnderCloud/Clans");

        }
		
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix . Color::GREEN . "wurde aktiviert!");
        $this->getLogger()->info($this->prefix . Color::AQUA . "Made By" . Color::GREEN . " EnderDirt!");
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	
    	if ($command->getName() === "Clan") {
    	
    	    if (isset($args[0])) {
    	
    	        if (strtolower($args[0]) === "make") {
    	
    	            if (isset($args[1])) {
    	
    	                if (file_exists("/home/EnderCloud/Clans/" . $args[1] . ".yml")) {
    	
    	                    $sender->sendMessage(Color::RED . "Diesen Clan gibt es schon!");
    
                        } else {
                        	
                        	$clan = new Config("/home/EnderCloud/Clans/" . $args[1] . ".yml", Config::YAML);
                            $clan->set("Owner1", $sender->getName());
                            $clan->set("Owner2", "");
                            $clan->set("Owner3", "");
                            $clan->set("player1", $sender->getName());
                            $clan->set("player2", "");
                            $clan->set("player3", "");
                            $clan->set("player4", "");
                            $clan->set("player5", "");
                            $clan->set("player6", "");
                            $clan->set("player7", "");
                            $clan->set("player8", "");
                            $clan->set("player9", "");
                            $clan->set("player10", "");
                            $clan->set("player11", "");
                            $clan->set("player12", "");
                            $clan->set("player13", "");
                            $clan->set("player14", "");
                            $clan->set("player15", "");
                            $clan->set("Member", 1);
                            $clan->save();
                            $pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                            $pf->set("Clan", $args[1]);
                            $pf->set("ClanStatus", true);
                            $pf->save();
                            $sender->sendMessage($this->prefix . Color::WHITE . "Der Clan: " .  Color::YELLOW . $pf->get("Clan") . Color::WHITE . " wurde erfolgreich erstellt!");
                        	
                        }
                        
                    }
        
                    } else if (strtolower($args[0]) === "accept") {
                    	
                    	$pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        if ($pf->get("ClanAnfrage") === "") {
                        	
                        	$sender->sendMessage(Color::RED . "Du wurdest von keinem Clan eingeladen");
                        	
                        } else {
                        	
                        	$clan = new Config("/home/EnderCloud/Clans/" . $pf->get("ClanAnfrage") . ".yml", Config::YAML);
                            if ($clan->get("player2") === "") {
                            	
                            	$clan->set("player2", $sender->getName());
                                $clan->set("Member", $clan->get("Member")+1);
                                $clan->save();
                                $pf->set("Clan", $pf->get("ClanAnfrage"));
                                $pf->set("ClanStatus", true);
                                $pf->set("ClanAnfrage", "");
                                $pf->save();
                                $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                            } else {
                            	
                            	if ($clan->get("player3") === "") {
                            	
                                	$clan->set("player3", $sender->getName());
                                    $clan->set("Member", $clan->get("Member")+1);
                                    $clan->save();
                                    $pf->set("Clan", $pf->get("ClanAnfrage"));
                                    $pf->set("ClanStatus", true);
                                    $pf->set("ClanAnfrage", "");
                                    $pf->save();
                                    $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                               } else {
                               	
                               	if ($clan->get("player4") === "") {
                            	
                                   	$clan->set("player4", $sender->getName());
                                       $clan->set("Member", $clan->get("Member")+1);
                                       $clan->save();
                                       $pf->set("Clan", $pf->get("ClanAnfrage"));
                                       $pf->set("ClanStatus", true);
                                       $pf->set("ClanAnfrage", "");
                                       $pf->save();
                                       $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                  } else {
                                  	
                                  	if ($clan->get("player5") === "") {
                            	
                                      	$clan->set("player5", $sender->getName());
                                          $clan->set("Member", $clan->get("Member")+1);
                                          $clan->save();
                                          $pf->set("Clan", $pf->get("ClanAnfrage"));
                                          $pf->set("ClanStatus", true);
                                          $pf->set("ClanAnfrage", "");
                                          $pf->save();
                                          $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                     } else {
                                     	
                                     	if ($clan->get("player6") === "") {
                            	
                                         	$clan->set("player6", $sender->getName());
                                             $clan->set("Member", $clan->get("Member")+1);
                                             $clan->save();
                                             $pf->set("Clan", $pf->get("ClanAnfrage"));
                                             $pf->set("ClanStatus", true);
                                             $pf->set("ClanAnfrage", "");
                                             $pf->save();
                                             $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                        } else {
                                        	
                                        	if ($clan->get("player7") === "") {
                            	
                                            	$clan->set("player7", $sender->getName());
                                                $clan->set("Member", $clan->get("Member")+1);
                                                $clan->save();
                                                $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                $pf->set("ClanStatus", true);
                                                $pf->set("ClanAnfrage", "");
                                                $pf->save();
                                                $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                           } else {
                                           	
                                           	if ($clan->get("player8") === "") {
                            	
                                               	$clan->set("player8", $sender->getName());
                                                   $clan->set("Member", $clan->get("Member")+1);
                                                   $clan->save();
                                                   $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                   $pf->set("ClanStatus", true);
                                                   $pf->set("ClanAnfrage", "");
                                                   $pf->save();
                                                   $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                              } else {
                                              	
                                              	if ($clan->get("player9") === "") {
                            	
                                                  	$clan->set("player9", $sender->getName());
                                                      $clan->set("Member", $clan->get("Member")+1);
                                                      $clan->save();
                                                      $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                      $pf->set("ClanStatus", true);
                                                      $pf->set("ClanAnfrage", "");
                                                      $pf->save();
                                                      $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                                 } else {
                                                 	
                                                 	if ($clan->get("player10") === "") {
                            	
                                                     	$clan->set("player10", $sender->getName());
                                                         $clan->set("Member", $clan->get("Member")+1);
                                                         $clan->save();
                                                         $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                         $pf->set("ClanStatus", true);
                                                         $pf->set("ClanAnfrage", "");
                                                         $pf->save();
                                                         $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                                    } else {
                                                    	
                                                    	if ($clan->get("player11") === "") {
                            	
                                                        	$clan->set("player11", $sender->getName());
                                                            $clan->set("Member", $clan->get("Member")+1);
                                                            $clan->save();
                                                            $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                            $pf->set("ClanStatus", true);
                                                            $pf->set("ClanAnfrage", "");
                                                            $pf->save();
                                                            $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                                       } else {
                                                       	
                                                       	if ($clan->get("player12") === "") {
                            	
                                                           	$clan->set("player12", $sender->getName());
                                                               $clan->set("Member", $clan->get("Member")+1);
                                                               $clan->save();
                                                               $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                               $pf->set("ClanStatus", true);
                                                               $pf->set("ClanAnfrage", "");
                                                               $pf->save();
                                                               $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                                          } else {
                                                          	
                                                          	if ($clan->get("player13") === "") {
                            	
                                                              	$clan->set("player13", $sender->getName());
                                                                  $clan->set("Member", $clan->get("Member")+1);
                                                                  $clan->save();
                                                                  $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                                  $pf->set("ClanStatus", true);
                                                                  $pf->set("ClanAnfrage", "");
                                                                  $pf->save();
                                                                  $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                                             } else {
                                                             	
                                                             	if ($clan->get("player14") === "") {
                            	
                                                                 	$clan->set("player14", $sender->getName());
                                                                     $clan->set("Member", $clan->get("Member")+1);
                                                                     $clan->save();
                                                                     $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                                     $pf->set("ClanStatus", true);
                                                                     $pf->set("ClanAnfrage", "");
                                                                     $pf->save();
                                                                     $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                            	
                                                                } else {
                                                                	
                                                                	if ($clan->get("player15") === "") {
                            	
                                                                    	$clan->set("player15", $sender->getName());
                                                                        $clan->set("Member", $clan->get("Member")+1);
                                                                        $clan->save();
                                                                        $pf->set("Clan", $pf->get("ClanAnfrage"));
                                                                        $pf->set("ClanStatus", true);
                                                                        $pf->set("ClanAnfrage", "");
                                                                        $pf->save();
                                                                        $sender->sendMessage($this->prefix . "Du bist erfolgreich den Clan beigetreten");
                                                                     
                                                                     }
                                                                	
                                                                }
                                                             	
                                                             }
                                                          
                                                          }
                                                       	
                                                       }
                                                    	
                                                    }
                                                 	
                                                 }
                                              	
                                              }
                                           	
                                           }
                                        	
                                        }
                                     	
                                     }
                                  	
                                  }
                               	
                               }
                            	
                            }
                        	
                        }
                    	
                    } else if (strtolower($args[0]) === "leader2") {
                    	
                    	$pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        if (isset($args[1])) {
                        	
                        	if ($pf->get("ClanStatus") === false) {
                        	
                           	$sender->sendMessage(Color::RED . "Du bist in keinem Clan!");
                        
                            } else {
                            	
                            	$clan = new Config("/home/EnderCloud/Clans/" . $pf->get("Clan") . ".yml", Config::YAML);
                                if (file_exists("/home/EnderCloud/eGroups/" . $args[1] . ".yml")) {
                                	
                                	if ($sender->getName() === $clan->get("Owner1")) {
                                	
                                	    $sf = new Config("/home/EnderCloud/eGroups/" . $args[1] . ".yml", Config::YAML);
                                        if ($sf->get("Clan") === $pf->get("Clan")) {
                                        	
                                        	$clan->set("Owner2", $args[1]);
                                            $clan->save();
                                            $sender->sendMessage(Color::GREEN . "Der Spieler wurde erfolgreich zu einem Leader befoerdert");
                                        	
                                        } else {
                                        	
                                        	$sender->sendMessage(Color::RED . "Dieser Spieler befindet sich nicht in deinem Clan");
                                        	
                                        }
                                        
                                    } else {
                                    	
                                    	$sender->sendMessage(Color::RED . "Du bist kein Leader dieses Clans");
                                    	
                                    }
                                	
                                } else {
                                	
                                	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht");
                                	
                                }
                                
                            }
                            
                        }
                        
                    } else if (strtolower($args[0]) === "leader3") {
                    	
                    	$pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        if (isset($args[1])) {
                        	
                        	if ($pf->get("ClanStatus") === false) {
                        	
                           	$sender->sendMessage(Color::RED . "Du bist in keinem Clan!");
                        
                            } else {
                            	
                            	$clan = new Config("/home/EnderCloud/Clans/" . $pf->get("Clan") . ".yml", Config::YAML);
                                if (file_exists("/home/EnderCloud/eGroups/" . $args[1] . ".yml")) {
                                	
                                	if ($sender->getName() === $clan->get("Owner1")) {
                                	
                                	    $sf = new Config("/home/EnderCloud/eGroups/" . $args[1] . ".yml", Config::YAML);
                                        if ($sf->get("Clan") === $pf->get("Clan")) {
                                        	
                                        	$clan->set("Owner3", $args[1]);
                                            $clan->save();
                                            $sender->sendMessage(Color::GREEN . "Der Spieler wurde erfolgreich zu einem Leader befoerdert");
                                        	
                                        } else {
                                        	
                                        	$sender->sendMessage(Color::RED . "Dieser Spieler befindet sich nicht in deinem Clan");
                                        	
                                        }
                                        
                                    } else {
                                    	
                                    	$sender->sendMessage(Color::RED . "Du bist kein Leader dieses Clans");
                                    	
                                    }
                                	
                                } else {
                                	
                                	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht");
                                	
                                }
                                
                            }
                            
                        }
                        
                    } else if (strtolower($args[0]) === "leave") {
                    	
                    	$pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        $clan = new Config("/home/EnderCloud/Clans/" . $pf->get("Clan") . ".yml", Config::YAML);
                        
                    	
                    } else if (strtolower($args[0]) === "add") {
                    	
                    	$pf = new Config("/home/EnderCloud/eGroups/" . $sender->getName() . ".yml", Config::YAML);
                        if (isset($args[1])) {
                        	
                            if ($pf->get("ClanStatus") === false) {
                        	
                           	$sender->sendMessage(Color::RED . "Du bist in keinem Clan!");
                        
                            } else {
                            	
                            	$clan = new Config("/home/EnderCloud/Clans/" . $pf->get("Clan") . ".yml", Config::YAML);
                                if (file_exists("/home/EnderCloud/eGroups/" . $args[1] . ".yml")) {
                                	
                                    if ($sender->getName() === $clan->get("Owner1")) {
                        	
                        	           if ($clan->get("Member") === 15) {
                        	
                        	               $sender->sendMessage(Color::RED . "Dein Clan ist Voll!");
                        
                                       } else {
                                       	
                                       	$sf = new Config("/home/EnderCloud/eGroups/" . $args[1] . ".yml", Config::YAML);
                                           if ($sf->get("ClanStatus") === false) {
                                           	
                                           	$v = $this->getServer()->getPlayerExact($args[1]);	
                                               if (!$v == null) {
                                           	
                                                  $sf->set("ClanAnfrage", $pf->get("Clan"));
                                                  $sf->save();
                                                  $v->sendMessage(Color::GREEN . "Der Clan " . Color::YELLOW . $sf->get("ClanAnfrage") . Color::GREEN . " hat dir eine Clan einladung geschickt!");
                                                  $v->sendMessage(Color::GREEN . "Um die Anfrage anzunehmen /clan accept");
                                                  $sender->sendMessage(Color::GREEN . "Die Clan Einladung wurde erfolgreich verschickt!");
                                           	
                                              } else {
                                           	
                                              	$sender->sendMessage(Color::RED . "Dieser Spieler ist nicht Online");
                                           	
                                              }
                                              
                                           } else {
                                           	
                                           	$sender->sendMessage(Color::RED . "Dieser Spieler ist schon in einem Clan");
                                           	
                                           }
                                       	
                                       }
                        	
                                   } else {
                        	
                                  	$sender->sendMessage(Color::RED . "Du bist kein Leader von diesem Clan");
                        	
                                  }
                                	
                                } else {
                                	
                                	$sender->sendMessage(Color::RED . "Diesen Spieler gibt es nicht");
                                	
                                }
                            	
                            }
                        	
                        }
                
                   }
            
              } else {
            	
              	$sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/clan make <ClanName>");
                  $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/clan leave");
                  $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/clan add <PlayerName>");
                  $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/clan accept <ClanName>");
                  $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/clan leader2 <ClanMemberName>");
                  $sender->sendMessage(Color::YELLOW . "-> " . Color::WHITE . "/clan leader3 <ClanMemberName>");
                
            }
        
        }
        
        return true;
        
    }
	
}