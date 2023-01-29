<?php
include_once "include/common.php";
include_once "include/db.php";
if (!isset($_SESSION)) {
    session_start();
}

$cards_per_page = 60;
$page_offset = 0;
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

    $page_offset = ($page - 1) * $cards_per_page;
}

$sql = "SELECT * FROM cards
        WHERE real_card='1'
        AND NOT layout='art_series'
        AND NOT layout='token'
        AND NOT layout='emblem'";

if (!empty($_GET["card_name"])) {
    $sql_search .= " AND name LIKE '%{$_GET["card_name"]}%'";
}
if (!empty($_GET["oracle_text"])) {
    $sql_search .= " AND oracle_text LIKE '%{$_GET["oracle_text"]}%'";
}
if (!empty($_GET["card_type"])) {
    $sql_search .= " AND type_line LIKE '%{$_GET["card_type"]}%'";
}

if (isset($_GET["white"])) {
    if (strcmp($_GET["color_type"], "excluding") == 0) {
        $sql_search .= " AND NOT color_identity LIKE '%W%'";
    }
    else {
        $sql_search .= " AND color_identity LIKE '%W%'";
    }
}
else if (strcmp($_GET["color_type"], "exact") == 0) {
    $sql_search .= " AND NOT color_identity LIKE '%W%'";
}
if (isset($_GET["blue"])) {
    if (strcmp($_GET["color_type"], "excluding") == 0) {
        $sql_search .= " AND NOT color_identity LIKE '%U%'";
    }
    else {
        $sql_search .= " AND color_identity LIKE '%U%'";
    }
}
else if (strcmp($_GET["color_type"], "exact") == 0) {
    $sql_search .= " AND NOT color_identity LIKE '%U%'";
}
if (isset($_GET["black"])) {
    if (strcmp($_GET["color_type"], "excluding") == 0) {
        $sql_search .= " AND NOT color_identity LIKE '%B%'";
    }
    else {
        $sql_search .= " AND color_identity LIKE '%B%'";
    }
}
else if (strcmp($_GET["color_type"], "exact") == 0) {
    $sql_search .= " AND NOT color_identity LIKE '%B%'";
}
if (isset($_GET["red"])) {
    if (strcmp($_GET["color_type"], "excluding") == 0) {
        $sql_search .= " AND NOT color_identity LIKE '%R%'";
    }
    else {
        $sql_search .= " AND color_identity LIKE '%R%'";
    }
}
else if (strcmp($_GET["color_type"], "exact") == 0) {
    $sql_search .= " AND NOT color_identity LIKE '%R%'";
}
if (isset($_GET["green"])) {
    if (strcmp($_GET["color_type"], "excluding") == 0) {
        $sql_search .= " AND NOT color_identity LIKE '%G%'";
    }
    else {
        $sql_search .= " AND color_identity LIKE '%G%'";
    }
}
else if (strcmp($_GET["color_type"], "exact") == 0) {
    $sql_search .= " AND NOT color_identity LIKE '%G%'";
}

if (isset($_GET["legality"])) {
    switch ($_GET["legality"]) {
        case "standard": $sql_search .= " AND standard_legal='legal'";
        case "pioneer": $sql_search .= " AND pioneer_legal='legal'";
        case "modern": $sql_search .= " AND modern_legal='legal'";
        case "legacy": $sql_search .= " AND legacy_legal='legal'";
        case "vintage": $sql_search .= " AND vintage_legal='legal'";
        case "pauper": $sql_search .= " AND pauper_legal='legal'";
        case "commander": $sql_search .= " AND commander_legal='legal'";
    }
}

if (isset($_GET["cmc"])) {
    if ($_GET["cmc_type"] == ">") {
        $sql_search .= " AND cmc>'{$_GET["cmc"]}'";
    }
    if ($_GET["cmc_type"] == "=") {
        $sql_search .= " AND cmc='{$_GET["cmc"]}'";
    }
    if ($_GET["cmc_type"] == "<") {
        $sql_search .= " AND cmc<'{$_GET["cmc"]}'";
    }
}

if (isset($_GET["card_order"])) {
    switch ($_GET["card_order"]) {
        case "ID": $sql_search .= " ORDER BY id"; break;
        case "name": $sql_search .= " ORDER BY name"; break;
        case "n_price": $sql_search .= " AND NOT normal_price='0' ORDER BY normal_price"; break;
        case "f_price": $sql_search .= " AND NOT foil_price='0' ORDER BY foil_price"; break;
        case "popularity": $sql_search .= " ORDER BY popularity"; break;
        case "release": $sql_search .= " ORDER BY released_at"; break;
        case "rarity": $sql_search .= " AND NOT rarity_num='0' ORDER BY rarity_num"; break;
        case "set": $sql_search .= " ORDER BY set_code"; break;
        case "power": $sql_search .= " AND NOT power='' AND NOT power LIKE '%*%'
              AND NOT power LIKE '%-%' AND NOT power LIKE '%+%' AND NOT power LIKE '%?%' ORDER BY power"; break;
        case "toughness": $sql_search .= " AND NOT toughness='' AND NOT toughness LIKE '%*%'
              AND NOT toughness LIKE '%-%' AND NOT toughness LIKE '%+%' AND NOT toughness LIKE '%?%' ORDER BY toughness"; break;
        case "loyalty": $sql_search .= "AND NOT loyalty='' ORDER BY loyalty"; break;
    }
}

if (isset($_GET["asc_dsc"])) {
    if (strcmp($_GET["asc_dsc"], "asc") == 0) {
        $sql_search .= " ASC";
    }
    else {
        $sql_search .= " DESC";
    }
}

if (isset($sql_search)) {
    $_SESSION["search"] = $sql_search;
}
else if (isset($_SESSION["search"])) {
    $sql_search = $_SESSION["search"];
}

$sql .= $sql_search;
$sql .= " LIMIT {$cards_per_page} OFFSET {$page_offset}";

$cards = query_execute_unsafe($db, $sql);

$sql_amount = "SELECT COUNT(1) FROM cards ";
$sql_amount .= "WHERE real_card='1'
                AND NOT layout='art_series'
                AND NOT layout='token'
                AND NOT layout='emblem'";
$sql_amount .= $sql_search;

$card_amount = mysqli_query($db, $sql_amount);
$card_amount = mysqli_fetch_array($card_amount)[0];
$last_page = intdiv(intval($card_amount), $cards_per_page) + 1;
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
    <button class="collapsible-button" onclick="collapse()">
        <b>filter cards</b>
    </button>
    <div id="search_bar" class="collapsed-row">
    <form action="" method="GET">
        <div class="column">
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
        </div>
        <div class="column">
            <b>converted mana cost</b>
            <select name="cmc_type">
                <option value="--" <?php if(strcmp($_GET["cmc_type"], "--") == 0)
                                        echo "selected='selected'"; ?> >--</option>
                <option value="=" <?php if(strcmp($_GET["cmc_type"], "=") == 0)
                                        echo "selected='selected'"; ?> >=</option>
                <option value=">" <?php if(strcmp($_GET["cmc_type"], ">") == 0)
                                        echo "selected='selected'"; ?> >></option>
                <option value="<" <?php if(strcmp($_GET["cmc_type"], "<") == 0)
                                        echo "selected='selected'"; ?> ><</option>
            </select>
            <input type="number" name="cmc" min="-10" max="10" value="<?php echo $_GET['cmc']??''; ?>" >
            <br><br>
            <b>color identity</b>
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
            </div>
            <select name="color_type">
                <option value="including" <?php if(strcmp($_GET["color_type"], "including") == 0)
                                        echo "selected='selected'"; ?> >including</option>
                <option value="exact" <?php if(strcmp($_GET["color_type"], "exact") == 0)
                                        echo "selected='selected'"; ?> >exact</option>
                <option value="excluding" <?php if(strcmp($_GET["color_type"], "excluding") == 0)
                                        echo "selected='selected'"; ?> >excluding</option>
            </select>
            <br>
        </div>
        <div class="column">
            <b>legal in</b>
            <select name="legality">
                <option value="" <?php if(strcmp($_GET["legality"], "") == 0)
                                        echo "selected='selected'"; ?> >--</option>
                <option value="standard" <?php if(strcmp($_GET["legality"], "standard") == 0)
                                        echo "selected='selected'"; ?> >standard</option>
                <option value="pioneer" <?php if(strcmp($_GET["legality"], "pioneer") == 0)
                                        echo "selected='selected'"; ?> >pioneer</option>
                <option value="modern" <?php if(strcmp($_GET["legality"], "modern") == 0)
                                        echo "selected='selected'"; ?> >modern</option>
                <option value="legacy" <?php if(strcmp($_GET["legality"], "legacy") == 0)
                                        echo "selected='selected'"; ?> >legacy</option>
                <option value="vintage" <?php if(strcmp($_GET["legality"], "vintage") == 0)
                                        echo "selected='selected'"; ?> >vintage</option>
                <option value="pauper" <?php if(strcmp($_GET["legality"], "pauper") == 0)
                                        echo "selected='selected'"; ?> >pauper</option>
                <option value="commander" <?php if(strcmp($_GET["legality"], "commander") == 0)
                                        echo "selected='selected'"; ?> >commander</option>
            </select>

            <b>order by</b>
            <select name="card_order">
                <option value="ID" <?php if(strcmp($_GET["card_order"], "ID") == 0)
                                        echo "selected='selected'"; ?> >ID</option>
                <option value="name" <?php if(strcmp($_GET["card_order"], "name") == 0)
                                        echo "selected='selected'"; ?> >name</option>
                <option value="n_price"  <?php if(strcmp($_GET["card_order"], "n_price") == 0)
                                        echo "selected='selected'"; ?> >normal price</option>
                <option value="f_price" <?php if(strcmp($_GET['card_order'], "f_price") == 0)
                                        echo "selected='selected'"; ?> >foil price</option>
                <option value="popularity" <?php if(strcmp($_GET['card_order'], "popularity") == 0)
                                        echo "selected='selected'"; ?> >popularity</option>
                <option value="release" <?php if(strcmp($_GET['card_order'], "release") == 0)
                                        echo "selected='selected'"; ?> >release</option>
                <option value="rarity" <?php if(strcmp($_GET['card_order'], "rarity") == 0)
                                        echo "selected='selected'"; ?> >rarity</option>
                <option value="set" <?php if(strcmp($_GET['card_order'], "set") == 0)
                                        echo "selected='selected'"; ?> >set</option>
                <option value="power" <?php if(strcmp($_GET['card_order'], "power") == 0)
                                        echo "selected='selected'"; ?> >power</option>
                <option value="toughness" <?php if(strcmp($_GET['card_order'], "toughness") == 0)
                                        echo "selected='selected'"; ?> >toughness</option>
                <option value="loyalty" <?php if(strcmp($_GET['card_order'], "loyalty") == 0)
                                        echo "selected='selected'"; ?> >loyalty</option>
            </select>
            <select name="asc_dsc">
                <option value="asc" <?php if(strcmp($_GET["asc_dsc"], "asc") == 0)
                                        echo "selected='selected'"; ?> >ascending</option>
                <option value="dsc" <?php if(strcmp($_GET["asc_dsc"], "dsc") == 0)
                                        echo "selected='selected'"; ?> >descending</option>
            </select>
            <br><br><br>
            <b><?php echo $card_amount??''; ?> results</b>
            <input type="submit" name="submit" value="Search">

            </div>
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

    <script>
        function collapse() {
            if (document.getElementById('search_bar').classList == "collapsible-row form") {
                document.getElementById('search_bar').setAttribute("class", "collapsed-row");
            }
            else {
                document.getElementById('search_bar').setAttribute("class", "collapsible-row form");
            }
        }
    </script>

</body>

</html>
