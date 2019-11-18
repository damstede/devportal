<?PHP
    if (isset($_GET["year"]) && !empty($_GET["year"]) && isset($_GET["week"]) && !empty($_GET["week"])) {
        // modified from https://stackoverflow.com/questions/4861384/php-get-start-and-end-date-of-a-week-by-weeknumber
        $dto = new DateTime();
        $dto->setISODate(intval($_GET["year"]), intval($_GET["week"]));
        echo $dto->format('Y-W') . ' ';
        echo $dto->format('Y-m-d') . ' ';
        $dto->modify('+1 days');
        echo $dto->format('Y-m-d') . ' ';
        $dto->modify('+1 days');
        echo $dto->format('Y-m-d') . ' ';
        $dto->modify('+1 days');
        echo $dto->format('Y-m-d') . ' ';
        $dto->modify('+1 days');
        echo $dto->format('Y-m-d');
    }
?>