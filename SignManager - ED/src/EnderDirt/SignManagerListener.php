<?php

namespace EnderDirt;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Color;

class SignManagerListener implements Listener {
	
    private $plugin;

    public function __construct(SignManager $plugin) {
    	
        $this->plugin = $plugin;
        $this->plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
        
    }

    public function onPacketReceive(DataPacketReceiveEvent $ev) {
    	
        $pk = $ev->getPacket();
        $player = $ev->getPlayer();
        if ($pk instanceof ModalFormResponsePacket) {
        	
            $id = $pk->formId;
            $data = json_decode($pk->formData);
            if ($id == 1000) {
            	
                if ($data !== NULL) {
                	
                    if ($data == 0) {
                    	
                        $this->plugin->sendBedWarsSign($player);
                        
                    } else if ($data == 1) {
                    	
                        $this->plugin->sendMLGRushSign($player);
                        
                    }
                    
                }
                
            } else if ($id == 1001) {
            	
                if ($data !== NULL) {
                	
                	if ($data == 0) {
                    	
                        $bwf = new Config("/home/BW1/plugins/BedWars/config.yml");
                        $bwf->set("reset", false);
                        $bwf->save();
                        $bwf2 = new Config("/home/BW2/plugins/BedWars/config.yml");
                        $bwf2->set("reset", false);
                        $bwf2->save();
                        $bwf3 = new Config("/home/BW3/plugins/BedWars/config.yml");
                        $bwf3->set("reset", false);
                        $bwf3->save();
                        $bwf4 = new Config("/home/BW4/plugins/BedWars/config.yml");
                        $bwf4->set("reset", false);
                        $bwf4->save();
                        $bwf5 = new Config("/home/BW5/plugins/BedWars/config.yml");
                        $bwf5->set("reset", false);
                        $bwf5->save();
                        $bwf6 = new Config("/home/BW6/plugins/BedWars/config.yml");
                        $bwf6->set("reset", false);
                        $bwf6->save();
                        $bwf7 = new Config("/home/BW7/plugins/BedWars/config.yml");
                        $bwf7->set("reset", false);
                        $bwf7->save();
                        $bwf8 = new Config("/home/BW8/plugins/BedWars/config.yml");
                        $bwf8->set("reset", false);
                        $bwf8->save();
                        $bwf9 = new Config("/home/BW9/plugins/BedWars/config.yml");
                        $bwf9->set("reset", false);
                        $bwf9->save();
                        $bwf10 = new Config("/home/BW10/plugins/BedWars/config.yml");
                        $bwf10->set("reset", false);
                        $bwf10->save();
                        $player->sendMessage(Color::WHITE . "Die " . Color::AQUA . "BedWars" . Color::WHITE . " Server wurden hochgefahren");
                        
                    } else if ($data == 1) {
                    	
                        $bwf = new Config("/home/BW1/plugins/BedWars/config.yml");
                        $bwf->set("reset", true);
                        $bwf->save();
                        $bwf2 = new Config("/home/BW2/plugins/BedWars/config.yml");
                        $bwf2->set("reset", true);
                        $bwf2->save();
                        $bwf3 = new Config("/home/BW3/plugins/BedWars/config.yml");
                        $bwf3->set("reset", true);
                        $bwf3->save();
                        $bwf4 = new Config("/home/BW4/plugins/BedWars/config.yml");
                        $bwf4->set("reset", true);
                        $bwf4->save();
                        $bwf5 = new Config("/home/BW5/plugins/BedWars/config.yml");
                        $bwf5->set("reset", true);
                        $bwf5->save();
                        $bwf6 = new Config("/home/BW6/plugins/BedWars/config.yml");
                        $bwf6->set("reset", true);
                        $bwf6->save();
                        $bwf7 = new Config("/home/BW7/plugins/BedWars/config.yml");
                        $bwf7->set("reset", true);
                        $bwf7->save();
                        $bwf8 = new Config("/home/BW8/plugins/BedWars/config.yml");
                        $bwf8->set("reset", true);
                        $bwf8->save();
                        $bwf9 = new Config("/home/BW9/plugins/BedWars/config.yml");
                        $bwf9->set("reset", true);
                        $bwf9->save();
                        $bwf10 = new Config("/home/BW10/plugins/BedWars/config.yml");
                        $bwf10->set("reset", true);
                        $bwf10->save();
                        $player->sendMessage(Color::WHITE . "Die " . Color::AQUA . "BedWars" . Color::WHITE . " Server wurden heruntergefahren");
                        
                    }
                    
                }
                
            } else if ($id == 1002) {
            	
                if ($data !== NULL) {
                	
                	if ($data == 0) {
                    	
                        $mrf = new Config("/home/MR1/plugins/MLGRush/config.yml");
                        $mrf->set("reset", false);
                        $mrf->save();
                        $mrf2 = new Config("/home/MR2/plugins/MLGRush/config.yml");
                        $mrf2->set("reset", false);
                        $mrf2->save();
                        $mrf3 = new Config("/home/MR3/plugins/MLGRush/config.yml");
                        $mrf3->set("reset", false);
                        $mrf3->save();
                        $mrf4 = new Config("/home/MR4/plugins/MLGRush/config.yml");
                        $mrf4->set("reset", false);
                        $mrf4->save();
                        $mrf5 = new Config("/home/MR5/plugins/MLGRush/config.yml");
                        $mrf5->set("reset", false);
                        $mrf5->save();
                        $mrf6 = new Config("/home/MR6/plugins/MLGRush/config.yml");
                        $mrf6->set("reset", false);
                        $mrf6->save();
                        $mrf7 = new Config("/home/MR7/plugins/MLGRush/config.yml");
                        $mrf7->set("reset", false);
                        $mrf7->save();
                        $mrf8 = new Config("/home/MR8/plugins/MLGRush/config.yml");
                        $mrf8->set("reset", false);
                        $mrf8->save();
                        $mrf9 = new Config("/home/MR9/plugins/MLGRush/config.yml");
                        $mrf9->set("reset", false);
                        $mrf9->save();
                        $mrf10 = new Config("/home/MR10/plugins/MLGRush/config.yml");
                        $mrf10->set("reset", false);
                        $mrf10->save();
                        $mrf11 = new Config("/home/MR11/plugins/MLGRush/config.yml");
                        $mrf11->set("reset", false);
                        $mrf11->save();
                        $mrf12 = new Config("/home/MR12/plugins/MLGRush/config.yml");
                        $mrf12->set("reset", false);
                        $mrf12->save();
                        $player->sendMessage(Color::WHITE . "Die " . Color::GOLD . "MLG" . Color::YELLOW . "Rush" . Color::WHITE . " Server wurden hochgefahren");
                        
                    } else if ($data == 1) {
                    	
                        $mrf = new Config("/home/MR1/plugins/MLGRush/config.yml");
                        $mrf->set("reset", true);
                        $mrf->save();
                        $mrf2 = new Config("/home/MR2/plugins/MLGRush/config.yml");
                        $mrf2->set("reset", true);
                        $mrf2->save();
                        $mrf3 = new Config("/home/MR3/plugins/MLGRush/config.yml");
                        $mrf3->set("reset", true);
                        $mrf3->save();
                        $mrf4 = new Config("/home/MR4/plugins/MLGRush/config.yml");
                        $mrf4->set("reset", true);
                        $mrf4->save();
                        $mrf5 = new Config("/home/MR5/plugins/MLGRush/config.yml");
                        $mrf5->set("reset", true);
                        $mrf5->save();
                        $mrf6 = new Config("/home/MR6/plugins/MLGRush/config.yml");
                        $mrf6->set("reset", true);
                        $mrf6->save();
                        $mrf7 = new Config("/home/MR7/plugins/MLGRush/config.yml");
                        $mrf7->set("reset", true);
                        $mrf7->save();
                        $mrf8 = new Config("/home/MR8/plugins/MLGRush/config.yml");
                        $mrf8->set("reset", true);
                        $mrf8->save();
                        $mrf9 = new Config("/home/MR9/plugins/MLGRush/config.yml");
                        $mrf9->set("reset", true);
                        $mrf9->save();
                        $mrf10 = new Config("/home/MR10/plugins/MLGRush/config.yml");
                        $mrf10->set("reset", true);
                        $mrf10->save();
                        $mrf11 = new Config("/home/MR11/plugins/MLGRush/config.yml");
                        $mrf11->set("reset", true);
                        $mrf11->save();
                        $mrf12 = new Config("/home/MR12/plugins/MLGRush/config.yml");
                        $mrf12->set("reset", true);
                        $mrf12->save();
                        $player->sendMessage(Color::WHITE . "Die " . Color::GOLD . "MLG" . Color::YELLOW . "Rush" . Color::WHITE . " Server wurden heruntergefahren");
                        
                    }
                    
                }
                
            }
            
        }
        
    }
    
}