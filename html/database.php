<?php
include_once "include/common.php";
include_once "include/db.php";
if (!isset($_SESSION)) {
    session_start();
}

$cards_per_page = 60;
$id_offset = 0;
$page = 1;

// if there is a page specified
if (isset($_GET["page"])) {
    // reload the page without a page specified if the page isn't a number
    if (!is_numeric($_GET["page"])) {
        header("Location: /database.php");
    }

    $page = intval($_GET["page"]);

    // reload the page without a page specified if the page number is invalid
    if ($page < 1) {
        header("Location: /database.php");
    }

    $id_offset = ($page - 1) * $cards_per_page;
}

$sql = "SELECT * FROM cards
        WHERE real_card='1'
        AND NOT layout='art_series'
        AND NOT layout='token'
        AND NOT layout='emblem'
        AND id > $id_offset";

if (!empty($_GET["card_name"])) {
    $sql_search .= " AND name LIKE '%{$_GET["card_name"]}%'";
}
if (!empty($_GET["oracle_text"])) {
    $sql_search .= " AND oracle_text LIKE '%{$_GET["oracle_text"]}%'";
}
if (!empty($_GET["card_type"])) {
    $sql_search .= " AND type_line LIKE '%{$_GET["card_type"]}%'";
}

if (isset($_GET["white"]) or isset($_GET["blue"]) or isset($_GET["black"]) or isset($_GET["red"])
or isset($_GET["green"]) or isset($_GET["colorless"])) {
    if (isset($_GET["white"])) {
        $sql_search .= " AND colors LIKE '%W%'";
    }
    else {
        $sql_search .= " AND NOT colors LIKE '%W%'";
    }
    if (isset($_GET["blue"])) {
        $sql_search .= " AND colors LIKE '%U%'";
    }
    else {
        $sql_search .= " AND NOT colors LIKE '%U%'";
    }
    if (isset($_GET["black"])) {
        $sql_search .= " AND colors LIKE '%B%'";
    }
    else {
        $sql_search .= " AND NOT colors LIKE '%B%'";
    }
    if (isset($_GET["red"])) {
        $sql_search .= " AND colors LIKE '%R%'";
    }
    else {
        $sql_search .= " AND NOT colors LIKE '%R%'";
    }
    if (isset($_GET["green"])) {
        $sql_search .= " AND colors LIKE '%G%'";
    }
    else {
        $sql_search .= " AND NOT colors LIKE '%G%'";
    }
    if (isset($_GET["colorless"])) {
        $sql_search .= " AND colors LIKE '%C%'";
    }
    else {
        $sql_search .= " AND NOT colors LIKE '%C%'";
    }
}

if (isset($sql_search)) {
    $_SESSION["search"] = $sql_search;
}
else if (isset($_SESSION["search"])) {
    $sql_search = $_SESSION["search"];
}

$sql .= $sql_search;
$sql .= " ORDER BY id ASC LIMIT 60";

$cards = query_execute_unsafe($db, $sql);

$sql_amount = "SELECT COUNT(1) FROM cards ";
$sql_amount .= "WHERE real_card='1'
                AND NOT layout='art_series'
                AND NOT layout='token'
                AND NOT layout='emblem'";
$sql_amount .= $sql_search;

$last_page = mysqli_query($db, $sql_amount);
$last_page = mysqli_fetch_array($last_page)[0];
$last_page = intdiv(intval($last_page), $cards_per_page) + 1;
echo $last_page;
?>

<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MTG | Shop</title>

    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
</head>

<body>

<?php include_once "header.php"; ?>

<div class="box">
    <div class="box-row box-light">
        <b>Simple search</b>
    </div>
    <div class="box-row form">
    <form action="" method="GET">
            <b>card name</b>
            <label>
                <input type="text" name="card_name"
                       value="<?php echo $_GET['card_name']??''; ?>" >
            </label>
            <br><br><br>
            <b>oracle text</b>
            <label>
                <input type="text" name="oracle_text" value="<?php echo $_GET['oracle_text']??''; ?>" >
            </label>
            <br><br><br>
            <b>card type</b>
            <label>
                <input type="text" name="card_type" value="<?php echo $_GET['card_type']??''; ?>" >
            </label>
            <br><br><br>
        <b>colors</b>
            <div class="color-checkbox">
                <input class="white_checkbox" type="checkbox" name="white"
                <?php if(isset($_GET['white'])) echo "checked='checked'"; ?> >
                <input class="blue_checkbox" type="checkbox" name="blue"
                <?php if(isset($_GET['blue'])) echo "checked='checked'"; ?> >
                <input class="black_checkbox" type="checkbox" name="black"
                <?php if(isset($_GET['black'])) echo "checked='checked'"; ?> >
                <input class="red_checkbox" type="checkbox" name="red"
                <?php if(isset($_GET['red'])) echo "checked='checked'"; ?> >
                <input class="green_checkbox" type="checkbox" name="green"
                <?php if(isset($_GET['green'])) echo "checked='checked'"; ?> >
                <input class="colorless_checkbox" type="checkbox" name="colorless"
                <?php if(isset($_GET['colorless'])) echo "checked='checked'"; ?> >
            </div>
            <br>
            <input type="submit" name="submit" value="Search">
    </form>
    </div>
</div>

<div class="box box-row box-container">
    <?php
    foreach ($cards as $card):
        $card_front = $card["image"];
        $card_back = $card["back_image"];
        $card_price = $card["normal_price"];
        $card_page = "/product.php?id=" . $card["id"];

        if (!$card_front) {
            $card_front = "https://mtgcardsmith.com/view/cards_ip/1674397095190494.png?t=014335";
        }
        if ($card["normal_price"] == 0) {
            if ($card["foil_price"] == 0) {
                $card_price = "--";
            }
            else {
                $card_price = $card["foil_price"];
            }
        }
    ?>
    <div class="box box-item">
        <div class="box-row item-header">
            <div class="box-left item-name">
                <a href="product.php?id=<?= $card["id"] ?>"><?= $card["name"] ?></a>
            </div>
            <div class="box-right item-price">€<?= $card_price ?></div>
        </div>

        <div class="box-row item-set"><?= $card["set_name"] ?></div>

        <div class="box-row">
<?php if (isset($card_back)): ?>
            <div class="box-card-small">
                <div class="box-card-flip">
                    <div class="box-card-front">
                        <a href="<?= $card_page ?>">
                            <img src="<?= $card_front ?>" alt="<?= $card["name"] ?>">
                        </a>
                    </div>
                    <div class="box-card-back">
                        <a href="<?= $card_page ?>">
                            <img src="<?= $card_back ?>" alt="<?= $card["name"] ?>">
                        </a>
                    </div>
                </div>
            </div>
<?php else: ?>
            <div class="box-card-small">
                <a href="<?= $card_page ?>">
                    <img src="<?= $card_front ?>" alt="<?= $card["name"] ?>">
                </a>
            </div>
<?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div class="pageinator">
<?php if ($page > 2): ?>
    <a class="first-page" href="/database.php?page=1";>
        <i class="fa-solid fa-chevron-left"></i>
        <i class="fa-solid fa-chevron-left"></i>
    </a>
<?php endif; ?>
<?php if ($page > 1): ?>
    <a href="/database.php?page=<?= $page - 1 ?>">
        <i class="fa-solid fa-chevron-left"></i>
    </a>
<?php endif; ?>
<?php
    function window($page, $last_page) {
        if ($page < 4) {
            return range(1, 7);
        }

        if ($last_page - $page < 4) {
            return range($last_page - 6, $last_page);
        }

        return range($page - 3, $page + 3);
    }

    foreach (window($page, $last_page) as $page_ref) {
        $tag = '<a href="/database.php?page=' . strval($page_ref). '"';

        $tag .= $page_ref == $page ? ' class="this-page-button">' : ">";
        $tag .= strval($page_ref);
        $tag .= "</a>";

        if (strval($page_ref) <= $last_page And strval($page_ref) > 0) {
            echo "\t$tag\n";
        }
    }
?>
<?php if ($last_page != $page): ?>
    <a href="/database.php?page=<?= $page + 1 ?>">
        <i class="fa-solid fa-chevron-right"></i>
    </a>
<?php endif; ?>
<?php if ($last_page - $page > 1): ?>
    <a class="last-page" href="/database.php?page=<?= $last_page ?>">
        <i class="fa-solid fa-chevron-right"></i>
        <i class="fa-solid fa-chevron-right"></i>
    </a>
<?php endif; ?>
</div>

<?php include_once "footer.php"; ?>

</body>

</html>
