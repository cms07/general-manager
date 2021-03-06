<?php
class Player implements JsonSerializable
{
	public $id;
	public $team;
	public $name;
	public $number;
	public $is_pitcher;
	protected $db;

	public function jsonSerialize()
	{
		$stats = new BatterStats($this->id);
		return [
			'id' => (int) $this->id,
			'name' => $this->name,
			'number' => $this->number,
			'is_pitcher' => $this->is_pitcher,
			'avg' => sprintf("%.3f", $stats->avg())
		];
	}

	public function __construct($id)
	{
		$this->db = new PDO('mysql:host=localhost;dbname=manager;charset=utf8', 'root', '', array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true));
		$this->_load_player($id);
	}

	public static function id_is_on_team($id, $team_id)
	{
		$id = intval($id);
		$db = new PDO('mysql:host=localhost;dbname=manager;charset=utf8', 'root', '', array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true));
		$query = "SELECT * FROM `player` WHERE `team` = :team AND `id` = :id";
		$stmt = $db->prepare($query);
		$stmt->bindParam(':team', $team_id, PDO::PARAM_INT);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() == 0) return false;
		else return true;
	}

	public static function create_player($team, $name, $number, $is_pitcher)
	{
		$is_pitcher ? $is_pitcher = 1 : $is_pitcher = 0;
		$db = new PDO('mysql:host=localhost;dbname=manager;charset=utf8', 'root', '', array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true));
		$query = "INSERT INTO `player` (`team`, `name`, `number`, `is_pitcher`) VALUES (:team, :name, :number, :is_pitcher)";
		$stmt = $db->prepare($query);
		$stmt->bindParam(':team', $team, PDO::PARAM_INT);
		$stmt->bindParam(':name', $name);
		$stmt->bindParam(':number', $number, PDO::PARAM_INT);
		$stmt->bindParam(':is_pitcher', $is_pitcher, PDO::PARAM_INT);
		$stmt->execute();
	}

	private function _load_player($id)
	{
		$query = "SELECT * FROM `player` WHERE `id` = :id";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();

		$this->id = $id;
		$this->team = new Team($row["team"]);
		$this->name = $row["name"];
		$this->number = $row["number"];
		$this->is_pitcher = $row["is_pitcher"];
	}

	public function played_in_game($game_id)
	{
		$query = "SELECT * FROM `batting` WHERE `player` = :id AND `game` = :game";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':id', $this->id(), PDO::PARAM_INT);
		$stmt->bindParam(':game', $game_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() == 0) return false;
		else return true;
	}

	public function id()
	{
		return $this->id;
	}

	public function team()
	{
		return $this->team;
	}

	public function name()
	{
		return htmlentities($this->name);
	}

	public function number()
	{
		return $this->number;
	}

	public function is_pitcher()
	{
		return $this->is_pitcher;
	}

}
