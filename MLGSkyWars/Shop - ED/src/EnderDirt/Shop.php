<?php

namespace EnderDirt;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Color;
use pocketmine\event\player\PlayerInteractEvent;

use EnderDirt\ShopListener;

class Shop extends PluginBase {
	
    public $prefix = Color::WHITE . "[" . Color::YELLOW . "SkyWars" . Color::WHITE . "] ";
    public $listener;

    public function onEnable() {
    	
        $this->listener = new ShopListener($this);

        $this->getLogger()->info($this->prefix . '§f loaded!');
    }

    public function onInteract(PlayerInteractEvent $event) {
    	
    	$player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        if ($item->getCustomName() === Color::DARK_PURPLE . "Kits") {
        	
        	$this->sendKits($player);
        	
        }
    	
    }

    public function sendKits(Player $player) {
    	
        $fdata = [];

        $fdata['title'] = '§l§5KITS';
        $fdata['buttons'] = [];
        $fdata['content'] = "";
        $fdata['type'] = 'form';

        $fdata['buttons'][] = ['text' => '§eSprengmeister'];
        $fdata['buttons'][] = ['text' => '§4SOON'];
        $fdata['buttons'][] = ['text' => '§4SOON'];

        $pk = new ModalFormRequestPacket();
        $pk->formId = 1;
        $pk->formData = json_encode($fdata);

        $player->sendDataPacket($pk);
        
    }
    
}