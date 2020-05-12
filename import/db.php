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

        public function getStartAndEndDate($year, $week) {
            // modified from https://stackoverflow.com/questions/4861384/php-get-start-and-end-date-of-a-week-by-weeknumber
            $dto = new DateTime();
            $dto->setTimestamp($year, $week);
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
                    $cart["amount_choosable"] = (intval($cart["amount_choosable"]) > 0);
                    $cart["cart_type"] = intval($cart["cart_type"]);
                }
            }
            return $cart;
        }

        public function isReserved($cartId, $date, $hour, $amount) {
            $cart = $this->getDeviceCart($cartId);
            if (!$cart["amount_choosable"]) {
                $result = $this->runQuery("SELECT amount FROM damstede.cartreservations WHERE cart_id='".intval($cartId)."' AND DATE(date)=STR_TO_DATE('".$this->makeSafe($date)."', '%Y-%m-%d') AND hour='".intval($hour)."' AND cancelled=0 LIMIT 1");
                if ($result != false) {
                    if (mysqli_num_rows($result) > 0) {
                        $reservedAmount = mysqli_fetch_assoc($result)["amount"];
                        if ($reservedAmount < 1) {
                            $reservedAmount = $cart["dev_amount"];
                        }
                        return $reservedAmount;
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
                $result = $this->runQuery("SELECT SUM(amount) AS reserved_amount FROM damstede.cartreservations WHERE cart_id='".intval($cartId)."' AND DATE(date)=STR_TO_DATE('".$this->makeSafe($date)."', '%Y-%m-%d') AND hour='".intval($hour)."' AND cancelled=0 LIMIT 1");
                if ($result != false) {
                    $reservedAmount = mysqli_fetch_assoc($result)["reserved_amount"];
                    if ($reservedAmount >= $cart["dev_amount"]) {
                        return $cart["dev_amount"];
                    }
                    else {
                        return false;
                    }
                }
                else {
                    return false;
                }
            }
        }

        public function getAmountOfDevicesLeft($cartId, $date, $hour) {
            $cart = $this->getDeviceCart($cartId);
            $result = $this->runQuery("SELECT SUM(amount) AS reserved_amount FROM damstede.cartreservations WHERE cart_id='".intval($cartId)."' AND DATE(date)=STR_TO_DATE('".$this->makeSafe($date)."', '%Y-%m-%d') AND hour='".intval($hour)."' AND cancelled=0 LIMIT 1");
            if ($result != false) {
                $reservedAmount = mysqli_fetch_assoc($result)["reserved_amount"];
                return $cart["dev_amount"] - $reservedAmount;
            }
            else {
                return 0;
            }
        }

        public function userHasNotReservedYet($isTeacher, $user, $date, $hour) {
            // only students may reserve once per hour. Teachers may reserve as much as they like.
            if ($isTeacher) {
                return true;
            }
            else {
                $result = $this->runQuery("SELECT * FROM damstede.cartreservations WHERE USER='".$this->makeSafe($user)."' AND DATE(date)=STR_TO_DATE('".$this->makeSafe($date)."', '%Y-%m-%d') AND hour='".intval($hour)."' AND cancelled=0 LIMIT 1");
                if ($result != false) {
                    if (mysqli_num_rows($result) > 0) {
                        return false;
                    }
                    return true;
                }
                return false;
            }
        }

        public function reserveCart($cartId, $date, $hour, $location, $user, $teacher, $amount) {
            if ($this->getAmountOfDevicesLeft($cartId, $date, $hour) >= $amount) {
                $result = $this->runQuery("INSERT INTO damstede.cartreservations (cart_id, date, hour, location, user, teacher, amount) VALUES ('".intval($cartId)."', '".date("Y-m-d", strtotime($date))."', '".intval($hour)."', '".$this->makeSafe($location)."', '".$this->makeSafe($user)."', '".$this->makeSafe($teacher)."', '".intval($amount)."')");
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

        public function cancelReservation($reservationId) {
            $result = $this->runQuery("UPDATE damstede.cartreservations SET cancelled=1 WHERE id='".$this->makeSafe($reservationId)."' LIMIT 1");
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

        private function formatReservation($res) {
            $res["id"] = intval($res["id"]);
            $res["cart_id"] = intval($res["cart_id"]);
            $res["cart_type"] = $this->getDeviceCart($res["cart_id"])["cart_type"];
            $res["registered_on"] = strtotime($res["registered_on"]);
            $res["day"] = intval(date("w", strtotime($res["date"]))) - 1;
            $res["hour"] = intval($res["hour"]);
            $res["amount"] = intval($res["amount"]);
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

        public function getCartReservation($reservationId) {
            $result = $this->runQuery("SELECT * FROM damstede.cartreservations WHERE id='".$this->makeSafe($reservationId)."' LIMIT 1");
            if (mysqli_num_rows($result) > 0) {
                return $this->formatReservation(mysqli_fetch_assoc($result));
            }
            else {
                return false;
            }
        }

        public function getMyUpcomingReservations($user, $weeks = 4, $cartId = null) {
            $reservations = array();
            $sql = "SELECT * FROM damstede.cartreservations WHERE USER='".$this->makeSafe($user)."' AND DATE(date) >= STR_TO_DATE('" . $this->makeSafe(date('Y-m-d', strtotime('-1 week'))) . "', '%Y-%m-%d') AND DATE(date) <= STR_TO_DATE('" . $this->makeSafe(date('Y-m-d', strtotime('+4 weeks'))) . "', '%Y-%m-%d')";
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