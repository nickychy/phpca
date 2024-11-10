<?php
require('../inc/db_config.php');
require('../inc/essentials.php');

adminLogin();

if (isset($_POST['add_room'])) {
    $features = filteration(json_decode($_POST['features']));
    $facilities = filteration(json_decode($_POST['facilities']));



    $frm_data = filteration($_POST);

    $q1 = "INSERT INTO `rooms`( `name`, `area`, `price`, `quantity`, `adult`, `children`, `description`) VALUES (?,?,?,?,?,?,?)";
    $values = [$frm_data['name'], $frm_data['area'], $frm_data['price'], $frm_data['quantity'], $frm_data['adult'], $frm_data['children'], $frm_data['desc']];

    if (insert($q1, $values, 'siiiiis')) {
        $flag = 1;
    }

    $room_id = mysqli_insert_id($conn);
    $q2 = "INSERT INTO `room_facilities`(`room_id`, `facilities_id`) VALUES (?,?)";


    if ($stmt = mysqli_prepare($conn, $q2)) {
        foreach ($facilities as $f) {
            mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $flag = 0;
        die('query cannot be prepared - insert');
    }


    $q3 = "INSERT INTO `room_features`(`room_id`, `features_id`) VALUES (?,?)";


    if ($stmt = mysqli_prepare($conn, $q3)) {
        foreach ($features as $f) {
            mysqli_stmt_bind_param($stmt, 'ii', $room_id, $f);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $flag = 0;
        die('query cannot be prepared - insert');
    }

    if ($flag) {
        echo 1;
    } else {
        0;
    }

}
if (isset($_POST['get_all_rooms']) && $_POST['get_all_rooms'] == 'true') {
    // Query to fetch all rooms
    $res = selectAll('rooms');
    $i = 1;
    $data = "";

    // Check if any rooms exist
    while ($row = mysqli_fetch_assoc($res)) {
        if ($row['status'] == 1) {
            $status = "<button onclick='toggle_status($row[id],0)' class='btn btn-dark btn-sm shadow-none'>active</button>";
        } else {
            $status = "<button onclick='toggle_status($row[id],1)' class='btn btn-warning btn-sm shadow-none'>inactive</button>";
        }
        $data .= "
            <tr class='align-middle'>
                <td>$i</td>
                <td>{$row['name']}</td>
                <td>{$row['area']} sq. ft.</td>
                <td>
                    <span class='badge rounded-pill bg-light text-dark'>
                        Adult: {$row['adult']}
                    </span><br>
                    <span class='badge rounded-pill bg-light text-dark'>
                        Children: {$row['children']}
                    </span>
                </td>
                <td>₹{$row['price']}</td>
                <td>{$row['quantity']}</td>
                <td>$status</td>
                <td>
                <button onclick='edit_details($row[id])' type='button' class='btn btn-primary shadow-none btn-sm' data-bs-toggle='modal'
                data-bs-target='#edit-room'>
                <i class='bi bi-pencil-square'></i> Edit
                </button>
                </td>
            </tr>
        ";
        $i++;
    }

    // If no rooms found, return a message
    if (empty($data)) {
        $data = "<tr><td colspan='8'>No rooms available.</td></tr>";
    }

    echo $data;
}


if (isset($_POST['toggle_status'])) {

    $frm_data = filteration($_POST);

    $q = "UPDATE `rooms` SET `status` = ? WHERE `id` = ?";

    $values = [$frm_data['value'], $frm_data['toggle_status']];

    if (update($q, $values, 'ii')) {
        echo 1; // Success
    } else {
        echo 0; // Failure
    }

}

?>