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
            $openingHours = $this->getOpeningHours($cartId, $date);
            if (!empty($openingHours["max_devs"])) {
                $cart["dev_amount"] = $openingHours["max_devs"];
            }
            if (!in_array($hour, $openingHours["opening_hours"])) {
                return 0;
            }
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

        public function userIsManager($user) {
            $sql = "SELECT * FROM damstede.managers WHERE username='".$this->makeSafe($user)."' LIMIT 1";
            $result = $this->runQuery($sql);
            return ($result != false && mysqli_num_rows($result) > 0);
        }

        private function dayNumToText($day) {
            switch ($day) {
                case 1:
                    return "maandag";
                case 2:
                    return "dinsdag";
                case 3:
                    return "woensdag";
                case 4:
                    return "donderdag";
                case 5:
                    return "vrijdag";
                case 6:
                    return "zaterdag";
                case 7:
                case 0:
                    return "zondag";
                default:
                    return null;
            }
        }

        private function formatOpeningHours($hours) {
            $hours["rule_num"] = intval($hours["rule_num"]);
            if (isset($hours["weekday"]) && !empty($hours["weekday"])) {
                $hours["weekday"] = intval($hours["weekday"]);
            }
            else {
                $hours["weekday"] = intval(date("w", strtotime($hours["date"])));
                if ($hours["weekday"] == 0) {
                    $hours["weekday"] = 7;
                }
            }
            if (isset($hours["is_default"])) {
                $hours["is_default"] = intval($hours["is_default"]) > 0;
            }
            $hours["weekday_text"] = $this->dayNumToText($hours["weekday"]);
            $hours["opening_hours_start"] = intval($hours["opening_hours_start"]);
            $hours["opening_hours_end"] = intval($hours["opening_hours_end"]);
            $hours["opening_hours"] = array();
            if ($hours["opening_hours_start"] > 0) {
                for ($i = $hours["opening_hours_start"]; $i <= $hours["opening_hours_end"]; $i++) {
                    array_push($hours["opening_hours"], $i);
                }
            }
            $hours["max_devs"] = intval($hours["max_devs"]);
            return $hours;
        }

        public function getOpeningHours($cartId, $date) {
            $weekday = intval(date("w", strtotime($date)));
            if ($weekday == 0) {
                $weekday = 7;
            }
            $openingHours = $this->createFakeOpeningHours($weekday);
            $sql = "SELECT rule_num, is_default AS weekday, date, is_default, opening_hours_start, opening_hours_end, max_devs FROM damstede.openinghours WHERE cart_id=".intval($cartId)." AND (DATE(date)=STR_TO_DATE('".$this->makeSafe($date)."', '%Y-%m-%d') OR is_default=".intval($weekday).") ORDER BY is_default ASC LIMIT 2";
            $result = $this->runQuery($sql);
            if ($result != false) {
                if (mysqli_num_rows($result) > 0) {
                    $openingHours = $this->formatOpeningHours(mysqli_fetch_assoc($result));
                }
            }
            return $openingHours;
        }

        private function createFakeOpeningHours($day) {
            $fake = array();
            $fake["rule_num"] = -1;
            $fake["weekday"] = $day;
            $fake["weekday_text"] = $this->dayNumToText($day);
            $fake["is_default"] = true;
            if ($day > 0 && $day < 6) {
                $fake["opening_hours_start"] = 1;
                $fake["opening_hours_end"] = 9;
                $fake["opening_hours"] = [1, 2, 3, 4, 5, 6, 7, 8, 9];
                $fake["max_devs"] = null;
            }
            else {
                $fake["opening_hours_start"] = 0;
                $fake["opening_hours_end"] = 0;
                $fake["opening_hours"] = [];
                $fake["max_devs"] = 0;
            }
            return $fake;
        }

        public function getDefaultOpeningHours($cartId) {
            $openingHours = array();
            for ($i = 0; $i < 5; $i++) {
                $openingHours[$i] = $this->createFakeOpeningHours($i+1);
            }
            $sql = "SELECT rule_num, is_default AS weekday, opening_hours_start, opening_hours_end, max_devs FROM damstede.openinghours WHERE cart_id=".intval($cartId)." AND is_default>0 ORDER BY is_default ASC LIMIT 5";
            $result = $this->runQuery($sql);
            if ($result != false) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $row = $this->formatOpeningHours($row);
                    $openingHours[$row["weekday"]-1] = $row;
                }
            }
            return $openingHours;
        }

        public function getScheduledOpeningHours($cartId) {
            $openingHours = array();
            $sql = "SELECT rule_num, date, opening_hours_start, opening_hours_end, max_devs FROM damstede.openinghours WHERE cart_id=".intval($cartId)." AND is_default=0 AND date >= CURDATE() ORDER BY date ASC";
            $result = $this->runQuery($sql);
            if ($result != false) {
                while ($row = mysqli_fetch_assoc($result)) {
                    array_push($openingHours, $this->formatOpeningHours($row));
                }
            }
            return $openingHours;
        }
    }
?>