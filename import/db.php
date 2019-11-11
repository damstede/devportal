<?PHP
    // nogit.php contains $dbUsername, $dbPassword and $dbName
    require_once("nogit.php");

    class DamstedeDB {
        private $connection = null;

        function __construct() {
            global $dbUsername, $dbPassword, $dbName;
            $this->connection = mysqli_connect("localhost", $dbUsername, $dbPassword, $dbName) or returnError("MySQL connection error: " . mysqli_connect_error());
            mysqli_set_charset($this->connection, 'utf8mb4');
        }

        private function makeSafe($str) {
            $str = stripslashes(trim($str));
            $str = mysqli_real_escape_string($this->connection, strip_tags($str));
            return $str;
        }

        private function runQuery($query) {
            return mysqli_query($this->connection, $query);
        }

        private function getStartAndEndDate($year, $week) {
            // modified from https://stackoverflow.com/questions/4861384/php-get-start-and-end-date-of-a-week-by-weeknumber
            $dto = new DateTime();
            $dto->setISODate($year, $week);
            $ret = array();
            array_push($ret, $dto->format('Y-m-d'));
            $dto->modify('+4 days');
            array_push($ret, $dto->format('Y-m-d'));
            return $ret;
        }

        public function getDeviceCarts() {
            $carts = array();
            $result = $this->runQuery("SELECT id FROM damstede.devicecarts");
            if ($result != false) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $cart = $this->getDeviceCart($row["id"]);
                    if (!empty($cart)) {
                        array_push($carts, $cart);
                    }
                }
            }
            return $carts;
        }

        public function getDeviceCart($cartId) {
            $cart = false;
            $result = $this->runQuery("SELECT * FROM damstede.devicecarts WHERE id='".$this->makeSafe($cartId)."' LIMIT 1");
            if ($result != false) {
                if (mysqli_num_rows($result) > 0) {
                    $cart = mysqli_fetch_assoc($result);
                    $cart["id"] = intval($cart["id"]);
                    $cart["dev_amount"] = intval($cart["dev_amount"]);
                    $cart["available"] = (intval($cart["available"]) > 0);
                }
            }
            return $cart;
        }

        public function isReserved($cartId, $date, $hour) {
            $result = $this->runQuery("SELECT * FROM damstede.cartreservations WHERE cart_id='".intval($cartId)."' AND DATE(date)=STR_TO_DATE('".$this->makeSafe($date)."', '%Y-%m-%d') AND hour='".intval($hour)."' LIMIT 1");
            if ($result != false) {
                if (mysqli_num_rows($result) > 0) {
                    return $this->formatReservation(mysqli_fetch_assoc($result));
                }
                else {
                    return false;
                }
            }
            else {
                return false;
            }
        }

        public function reserveCart($cartId, $date, $hour, $location, $user, $teacher) {
            if ($this->isReserved($cartId, $date, $hour) == false) {
                $result = $this->runQuery("INSERT INTO damstede.cartreservations (cart_id, date, hour, location, user, teacher) VALUES ('".intval($cartId)."', '".date("Y-m-d", strtotime($date))."', '".intval($hour)."', '".$this->makeSafe($location)."', '".$this->makeSafe($user)."', '".$this->makeSafe($teacher)."')");
                if ($result != false) {
                    if (mysqli_affected_rows($this->connection) > 0) {
                        return true;
                    }
                    else {
                        return false;
                    }
                }
                else {
                    return false;
                }
            }
            else {
                return false;
            }
        }

        private function formatReservation($res) {
            $res["id"] = intval($res["id"]);
            $res["cart_id"] = intval($res["cart_id"]);
            $res["registered_on"] = strtotime($res["registered_on"]);
            $res["day"] = intval(date("w", strtotime($res["date"]))) - 1;
            $res["hour"] = intval($res["hour"]);
            $res["cancelled"] = (intval($res["cancelled"]) > 0);
            return $res;
        }

        public function getCartReservations($cartId, $year, $week) {
            $reservations = array();
            $week = $this->getStartAndEndDate($year, $week);
            $sql = "SELECT * FROM damstede.cartreservations WHERE DATE(date) >= STR_TO_DATE('".$this->makeSafe($week[0])."', '%Y-%m-%d') AND DATE(date) <= STR_TO_DATE('".$this->makeSafe($week[1])."', '%Y-%m-%d')";
            if (!empty($cartId)) {
                $sql .= " AND cart_id=".intval($cartId);
            }
            $result = $this->runQuery($sql);
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($reservations, $this->formatReservation($row));
            }
            return $reservations;
        }
    }
?>