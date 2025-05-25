<?php

namespace App\Database;

use App\Models\RoomModel;
use App\Models\GuestModel;
use App\Views\Display;
use App\Database\Database;
use Exception;

class Install extends Database
{

    protected CONST SETUP = [
        'numberOfRecords' => 20,
        'floorCount' => 5,
        'roomCount' => 10,
        'days' => 5,
        'accommodation' => [2,4,6],
        'priceRange' => [10000,40000],
        'lastNames' => [ 'Major','Riz','Kard','Pum','Víz','Kandisz','Patta','Para','Pop','Remek','Ének','Szalmon','Ultra','Dil','Git','Har','Külö','Harm',
                         'Zsíros B.','Virra','Kasza','Budipa','Bekre','Fejet','Minden','Bármi','Lapos','Bor','Mikorka','Szikla','Fekete','Rabsz','Kalim',
                         'Békés','Szenyo'],
        'firstNames' => ['Ottó','Pál','Elek','Simon','Ödön','Kálmán','Áron','Elemér','Szilárd','Csaba','Anna','Virág','Nóra','Zita','Ella','Viola','Emma',
                         'Mónika','Dóra','Blanka','Piroska','Lenke','Mercédesz','Olga','Rita',]
    ];

    public function dbExists(): bool
    {
        try {
            $mysqli = $this->getConn('mysql');
            if (!$mysqli) {
                return false;
            }

            $query = sprintf("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '%s';", self::DEFAULT_CONFIG['database']);
            $result = $mysqli->query($query);

            if (!$result) {
                throw new Exception('Lekérdezési hiba: ' . $mysqli->error);
            }
            $exists = $result->num_rows > 0;

            return $exists;

        }
        catch (Exception $e) {
            Display::message($e->getMessage(), 'error');
            error_log($e->getMessage());

            return false;
        }
        finally {
            // Ensure the database connection is always closed
            $mysqli?->close();
        }

    }

    public function getConn($dbName)
    {
        try {
            // Kapcsolódás az adatbázishoz
            $mysqli = mysqli_connect(self::DEFAULT_CONFIG["host"], self::DEFAULT_CONFIG["user"], self::DEFAULT_CONFIG["password"], $dbName);
    
            // Ellenőrizzük a csatlakozás sikerességét
            if (!$mysqli) {
                throw new Exception("Kapcsolódási hiba az adatbázishoz: " . mysqli_connect_error());
            }
    
            return $mysqli;
        } catch (Exception $e) {
            // Hibaüzenet megjelenítése a felhasználónak
            echo $e->getMessage();
    
            // Hibanaplózás
            error_log($e->getMessage());
    
            // Hibás csatlakozás esetén `null`-t ad vissza
            return null;
        }
    }

    public function createTable(string $tableName, string $tableBody, string $dbName): bool
    {
        try {
            $sql = "
                CREATE TABLE `$dbName`.`$tableName`
                ($tableBody)
                ENGINE = InnoDB
                DEFAULT CHARACTER SET = utf8
                COLLATE = utf8_hungarian_ci;
            ";
            return (bool) $this->execSql($sql);

        } catch (Exception $e) {
            Display::message($e->getMessage(), 'error');
            error_log($e->getMessage());
            return false;
        }
    }

    function createTableGuests($dbName = self::DEFAULT_CONFIG['database']): bool
    {
        $tableBody = "
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `name` varchar(100) DEFAULT NULL,
            `age` int(10) DEFAULT NULL";

        return $this->createTable('guests', $tableBody, $dbName);
    }
    function createTableReservations($dbName = self::DEFAULT_CONFIG['database']): bool
    {
        $tableBody = "
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `room_id` int(10) DEFAULT NULL,
            `guest_id` int(10) DEFAULT NULL,
            `days` int(10) DEFAULT NULL,
            `date` date DEFAULT NULL,
            FOREIGN KEY (`room_id`) REFERENCES rooms(`id`),
            FOREIGN KEY (`guest_id`) REFERENCES guests(`id`)";
            
        return $this->createTable("reservations", $tableBody, $dbName);
    }

    function createTableRooms($dbName = self::DEFAULT_CONFIG['database']): bool
    {
        $tableBody = "
            `id` int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `floor` int(10) NOT NULL,
            `room_number` int(10) NOT NULL,
            `accommodation` int(10) NOT NULL,
            `price` int(10) NOT NULL,
            `comment` varchar(100) NOT NULL";

        return $this->createTable("rooms", $tableBody, $dbName);
    }

    function fillTables($dbName = self::DEFAULT_CONFIG['database']){
        if ($this::SETUP['floorCount'] * $this::SETUP['roomCount'] < $this::SETUP['numberOfRecords']) {
            $length = $this::SETUP['floorCount'] * $this::SETUP['roomCount'];
        } else {
            $length = $this::SETUP['numberOfRecords'];
        }
        $this->fillTableGuests($dbName, $length);
        $this->fillTableRooms($dbName, $length);
        $this->fillTableReservations($dbName, $length);
    }

    function fillTableGuests($dbName, $length): bool{
        try {
            $sql = "INSERT INTO `$dbName`.guests(name, age) VALUES";

            for ($i = 0; $i < $length; $i++){
                $name = $this::SETUP['lastNames'][rand(0,count($this::SETUP['lastNames'])-1)] . " " . $this::SETUP['firstNames'][rand(0,count($this::SETUP['firstNames'])-1)];
                $age = rand(18, 100);
                $sql .= "('$name',$age)";
                if ($i != $length-1){
                    $sql .= ",";
                }
            }

            return (bool) $this->execSql($sql);
        } catch (Exception $e) {
            Display::message($e->getMessage(), 'error');
            error_log($e->getMessage());
            return false;
        }
    }

    function fillTableRooms($dbName, $length): bool
    {
        try {
            $sql = "INSERT INTO `$dbName`.rooms(floor, room_number, accommodation, price, comment) VALUES";
            $rooms = array_map(fn($j) => [$j => array_map(fn($i) => (string) $i, range(1, $this::SETUP['roomCount']))], range(0, $this::SETUP['floorCount']-1));

            for ($k = 0; $k < $length; $k++){
                if (count($rooms) > 0){
                    $floorIndex = rand(0, count($rooms)-1);
                    $floor = array_keys($rooms[$floorIndex])[0];
                    if (count($rooms[$floorIndex][$floor]) > 0){
                        $roomIndex = rand(0, count($rooms[$floorIndex][$floor])-1);
                        $room = $rooms[$floorIndex][$floor][$roomIndex];
                        array_splice($rooms[$floorIndex][$floor], $roomIndex, 1);
                    }
                    if (count($rooms[$floorIndex][$floor]) == 0){
                        array_splice($rooms, $floorIndex, 1);
                    }
    
                    $accommodation = $this::SETUP['accommodation'][rand(0, count($this::SETUP['accommodation'])-1)];
                    $price = round(rand($this::SETUP['priceRange'][0], $this::SETUP['priceRange'][1]),-2);
                    $comment = 'Megjegyzés ' . $k;
                    $sql .= "(" . $floor . "," . $room+$floor*$this::SETUP['roomCount'] . "," . $accommodation . "," . $price . ",'" . $comment . "')";
                    if ($k != $length-1){
                        $sql .= ',';
                    }
                }
            }
            $sql .= ';';
            return (bool) $this->execSql($sql);

        } catch (Exception $e) {
            Display::message($e->getMessage(), 'error');
            error_log($e->getMessage());
            return false;
        }
    }

    function fillTableReservations($dbName, $length){
        try{
            $sql = "INSERT INTO `$dbName`.reservations(room_id, guest_id, days, date) VALUES";

            $room = new RoomModel();
            $rooms = $room->all(['order_by' => ['RAND()'], 'direction' => ['ASC']]);
            $guest = new GuestModel();
            $guests = $guest->all(['order_by' => ['RAND()'], 'direction' => ['ASC']]);

            for ($i = 0; $i < count($rooms); $i++){
                $roomID = $rooms[$i]->id;
                $guestID = $guests[$i]->id;
                $days = rand(1, $this::SETUP['days']);
                $date = date("Y-m-d", rand(strtotime("Jan 01 2015"), strtotime("Nov 01 2016")));
                echo $date . '<br>';
                $sql .= "($roomID,'$guestID',$days,'$date')";
                if ($i != count($rooms)-1){
                    $sql .= ',';
                }
            }
            $sql .= ';';

            return (bool) $this->execSql($sql);
        }
        catch (Exception $e){
            Display::message($e->getMessage(), 'error');
            error_log($e->getMessage());
            return false;
        }
    }
}