<?php
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/db-connection.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/helpers/auth-functions.php");

authed();

$message = null;

function esc_str ($connection, $str) {
    return mysqli_real_escape_string($connection, $str);
}

function get_username($con) {
    $username = $_SESSION["username"];
    $query = "SELECT * FROM users
            WHERE username='$username'";
    $res = $con->query($query);
    $rowcount = mysqli_num_rows($res);
    if ($rowcount != 1) {
        header("Location: /404");
        exit();
    }
    return mysqli_fetch_assoc($res)["id"];
}

function fetch_data ($con, $id) {
    if ($id === 0) {
        header("Location: /404");
        exit();
    }
    // Fetch data from the database
    $query = "SELECT *, c.id AS candidate_id FROM candidates AS c
            INNER JOIN polls AS p ON c.poll_id = p.id
            WHERE c.poll_id = $id;";
    $res = $con->query($query);

    // Redirect to /404 if there is no results
    if(mysqli_num_rows($res) === 0) {
        header("Location: /404");
        exit();
    }

    // Filter the options
    while ( $row = mysqli_fetch_assoc($res) ) {
        $filtered_option = array_intersect_key($row, array_flip(["id", "name", "candidate_id"]));
        $GLOBALS["options"][] = $filtered_option;
        $GLOBALS["poll"]["title"] = $row["title"];
        $GLOBALS["poll"]["description"] = $row["description"];
    }

    // Get vote data if the user has already voted
    $u_id = get_username($con);
    $query = "SELECT c.id, c.name
            FROM users AS u
            INNER JOIN votes AS v ON v.user_id = $u_id
            INNER JOIN polls AS p ON p.id = $id
            INNER JOIN candidates AS c ON c.id = v.candidate_id";
    $res = $con->query($query);
    $result = mysqli_fetch_assoc($res);
    if ($result != NULL) {
        $GLOBALS["voted"] = $result;
    }
}

function post_vote($con, $p_id, $c_id) {
    if ($c_id < 1 || $p_id < 1) {
        header("Location: /404");
        exit();
    }
    
    // Check whether candidate id is valid with the poll id
    $query = "SELECT * FROM candidates AS c
            INNER JOIN polls AS p ON p.id = c.poll_id AND p.id = $p_id AND c.id = $c_id";
    $res = $con->query($query);
    $rowcount = mysqli_num_rows($res);
    if ($rowcount < 1) {
        header("Location: /404");
        exit();
    }
    
    // Get username
    $u_id = get_username($con);

    // Check whether the user has already voted or not
    $query = "SELECT *
            FROM users AS u
            INNER JOIN votes AS v ON v.user_id = $u_id
            INNER JOIN polls AS p ON p.id = $p_id";
    $res = $con->query($query);
    $rowcount = mysqli_num_rows($res);
    if ($rowcount != 0) {
        echo "Already Voted!";
        exit();
    }

    // Insert vote to the database
    $query = "INSERT INTO votes(poll_id, candidate_id, user_id)
            VALUES($p_id, $c_id, $u_id);";
    $res = $con->query($query);

    header("Location: /poll/$p_id/success");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === "GET") {
    $id = (int)esc_str($connection, $id);
    fetch_data($connection, $id);
} else if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $p_id = (int)esc_str($connection, $id);
    $c_id = (int)esc_str($connection, $_POST["candidate"]);
    post_vote($connection, $p_id, $c_id);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="/dist/css/create_poll.css">
    <title><?php echo $GLOBALS["poll"]["title"] ?></title>
</head>

<body style="font-family:'Segoe UI'">
    <div class="container">
        <h3 class="mt-5">Heisenberge Polls</h3>
        <h5><?php echo $message ?></h3>
        <form action="<?php echo "/poll/$id" ?>" method="POST" class="g-3 mb-4 mt-4">
            <div class="row col-12">
                <h4 class="fw-bold text-center mt-3"></h4>
                <h5 class="mb-3"><?php echo $GLOBALS["poll"]["title"] ?></h5>
                <?php foreach ($GLOBALS["options"] as $idx => $val) : ?>
                    <div class="form-check mb-3">
                        <input 
                            class="form-check-input"
                            type="radio"
                            name="candidate"
                            id="option_<?php echo $val["candidate_id"] ?>"
                            value="<?php echo $val["candidate_id"] ?>"
                            <?php echo isset($GLOBALS["voted"]) && $GLOBALS["voted"]["id"] == $val["candidate_id"] ? "checked" : NULL ?>
                        />
                        <label
                            class="form-check-label"
                            for="option_<?php echo $val["candidate_id"] ?>"
                        >
                            <?php echo $val["name"] ?>
                        </label>
                    </div>
                <?php endforeach ?>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary" <?php echo isset($GLOBALS["voted"]) && $GLOBALS["voted"] ? "disabled" : NULL ?>>Submit</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>