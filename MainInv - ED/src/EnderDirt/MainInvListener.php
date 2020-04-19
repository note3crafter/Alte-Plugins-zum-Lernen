<?php

namespace EnderDirt;

use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat as Color;

class MainInvListener {
	
	protected $plugin;

	public function __construct(TestShop $plugin) {
		
		$this->plugin = $plugin;
		
	}
	
	public function onTransaction(Player $player, Item $itemClickedOn, Item $itemClickedWith) : bool {
    }
	
}