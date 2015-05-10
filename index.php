<?php
/* Author: Jonathan Gamble
/* Course: CS290 @Oregon State Spring Term 2015
*/
    
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require 'video_db.php';

$vs = new video_db();

// Check for actions
if (isset($_POST['delete']) && $_POST['delete'] === "all") {
    $error = $vs->remove_all();
}
elseif (isset($_POST['add'])) {
    // validate input
    if (isset($_POST['name'])) {
        // add item
        $fields['name'] = $_POST['name'];
        if ($_POST['category']) $fields['category'] = $_POST['category'];
        if ($_POST['length']) $fields['length'] = $_POST['length'];
        $error = $vs->add($fields);
    }
    else {
        // this should not happen unless not html5
        $error = "You must have all required fields!";
    }
}
elseif (isset($_GET['delete'])) {
    // delete item
    $error = $vs->remove($_GET['delete']);
}
elseif (isset($_GET['checkin'])) {
    // check in
    $error = $vs->checkin($_GET['checkin']);
}
elseif (isset($_GET['checkout'])) {
    // check out
    $error = $vs->checkout($_GET['checkout']);
}

// Categories...
if (isset($_POST['filter']) || isset($_GET['filter'])) {
    // filter category
    $filter = $_REQUEST['filter'];
    if ($filter !== "all") $vs->set_filter($filter);
}
if ($vs->get_categories()) {
    // get the categories
    $categories = array();
    foreach($vs->results as $item) {
        if (!in_array($item['category'], $categories) && $item['category']) 
            array_push($categories, $item['category']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Video Store</title>
    <style>
        table, td, tr {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px;
        }
        label {
            float: left;
            width: 90px;
            font-weight: bold;
        }
        legend {
            font-weight:  bold;
            text-decoration: underline;
        }
        fieldset {
            border: none;
        }
    </style>
</head>
<body>
    <?php if (isset($error)) { ?><h2>Error: <?php echo $error; ?></h2><?php } ?>
    <fieldset>
        <legend>Video Library</legend>
        <?php if (isset($categories) && !empty($categories)) { ?>
        <form method="post">
            <p><label for="category_select">Categories:</label>
            <select id="category_select" name="filter">
            <option value="all" <?php if (!isset($filter)) echo "selected"; ?>>All Movies</option>
            <?php foreach ($categories as $c) { ?>
            <?php if ($c) { ?>
            <option value="<?php echo $c; ?>" <?php if (isset($filter) && $c === $filter) echo "selected"; ?>><?php echo $c; ?></option>
            <?php } ?>
            <?php } ?>
            </select>
            <input type="submit" value="Filter" /></p>
        </form>
        <?php } ?>
    <?php if ($vs->get_inventory()) { ?>
        <table>
            <thead>
                <tr>
                    <th>Video</th>
                    <th>Category</th>
                    <th>Length</th>
                    <th>Status</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
        <?php foreach ($vs->results as $item) { ?>
            <tbody>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['category']; ?></td>
                    <td><?php echo $item['length']; ?></td>
                    <td><?php echo ($item['rented']) ? "checked out" : "available"; ?></td>
                    <td><a href="?delete=<?php echo $item['id']; if (isset($filter)) echo "&filter=$filter"; ?>">delete</a></td>
                    <td><a href="?<?php echo ($item['rented']) ? "checkin" : "checkout"; ?>=<?php
                        echo $item['id']; if (isset($filter)) echo "&filter=$filter"; ?>"><?php
                        echo ($item['rented']) ? "check-in" : "check-out"; ?></a></td>
                </tr>
            </tbody>
        <?php } ?>
        </table>
        <form method="post">
        <p><input type="hidden" name="delete" value="all" />
        <input type="submit" value="Delete All Videos" /></p>
        </form>
    <?php } else { ?>
        <?php if (isset($filter)) {?>
        <p>There are no items in this category!</p>
        <?php } else { ?>
        <p>There are no items in the inventory!</p>
        <?php } ?>
    <?php } ?>
    </fieldset>
    <form method="post">
        <fieldset>
            <legend>Add a Video</legend>
            <p><label for="name">Name:</label>
            <input id="name" type="text" name="name" required /></p>
            <p><label for="category">Category:</label>
            <input id="category" type="text" name="category" /></p>
            <p><label for="number">Length:</label>
            <input id="length" type="number" min="1" name="length" /></p>
            <p><input name="add" type="submit" value="Add Video" /></p>
        </fieldset>
    </form>
</body>
</html>
