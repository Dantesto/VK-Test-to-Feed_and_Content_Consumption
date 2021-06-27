<?php //PHP version 8.0.0

//consts with types of chests and monsters. X_TYPES[1] = range of reward or monster's power.
const CHEST_TYPES = [["min"=>1, "max"=>5], ["min"=>5, "max"=>10], ["min"=>10, "max"=>15]];
const MONSTER_TYPES = [["min"=>1, "max"=>5], ["min"=>5, "max"=>10], ["min"=>10, "max"=>15]];
const ATTACK_RANGE = ["min"=>1, "max"=>5];

class Room {
	public function __construct(private $nextRooms) {
	}
	
	final public static function clearRoom($room) {
		return new Room($room->getNextRooms());
	}
	
	public function interact() {
		return 0;
	}
	
	public function getNextRooms() {
		return $this->nextRooms;
	}
}

class ChestRoom extends Room {
	public function __construct($nextRooms, private $chestPointsRange) {
		parent::__construct($nextRooms);
	}
	
	public function interact() {
		return rand($this->chestPointsRange["min"], $this->chestPointsRange["max"]);
	}
}

class MonsterRoom extends Room {
	public function __construct($nextRooms, private $monsterPointsRange) {
		parent::__construct($nextRooms);
	}
	
	public function interact() {
		$monsterPoints = rand($this->monsterPointsRange["min"], $this->monsterPointsRange["max"]);
		while ($monsterPoints > 0) {
			$attack = rand(ATTACK_RANGE["min"], ATTACK_RANGE["max"]);
			if ($attack > $monsterPoints)
				break;
			$monsterPoints -= $attack;
		}
		return $monsterPoints;
	}
}

class Dungeon {
	private $rooms;
	private $entranceIndex;
	private $exitIndex;
	private $currentRoomIndex;
	private $playerPoints;
	
	/*rooms = [["type"=>"monster2", "nextRoomsIndices"=>[2, 5, 1, 3]], ...]
	numer in type of monster or chest means its type
	nextRoomIndices = [topDoorIndex, leftDoorIndex, bottomDoorIndex, rightDoorIndex]
	if the room doesn't have the door, then its index is -1*/
	public function __construct($rooms, $entranceIndex, $exitIndex) {
		$this->createDungeon($rooms);
		$this->entranceIndex = $entranceIndex;
		$this->exitIndex = $exitIndex;
		$this->currentRoomIndex = $entranceIndex;
		$this->playerPoints = 0;
	}
	
	private function createDungeon($rooms) {
		foreach ($rooms as $room) {
			if (strncmp($room["type"], "chest", 5) == 0) {
				$chestType = CHEST_TYPES[(int)$room["type"][-1]];
				$this->rooms[] = new ChestRoom($room["nextRoomsIndices"], $chestType);
			}
			elseif (strncmp($room["type"], "monster", 7) == 0) {
				$monsterType = MONSTER_TYPES[(int)$room["type"][-1]];
				$this->rooms[] = new MonsterRoom($room["nextRoomsIndices"], $monsterType);
			}
			else
				$this->rooms[] = new Room($room["nextRoomsIndices"]);
		}
	}
	
	//direction: top = 0, left = 1, bottom = 2, right = 3;
	//return: -1 if move isn't possible, 0 if move is done, playerPoints if exit is found
	public function makeMove($direction) {
		$nextRooms = $this->getNextRooms();
		if ($nextRooms[$direction] == -1)
			return -1;
		$this->currentRoomIndex = $nextRooms[$direction];
		$this->playerPoints += $this->rooms[$this->currentRoomIndex]->interact();
		$this->rooms[$this->currentRoomIndex] = Room::clearRoom($this->rooms[$this->currentRoomIndex]);
		if ($this->currentRoomIndex == $this->exitIndex)
			return $this->getPlayerPoints();
		return 0;
	}
	
	public function getPlayerPoints() {
		return $this->playerPoints;
	}
	
	public function getNextRooms() {
		return $this->rooms[$this->currentRoomIndex]->getNextRooms();
	}
}

function printArray($arr) {
	foreach ($arr as $key=>$value) {
		echo $key . "=>" . $value . " ";
	}
	echo "\n";
}