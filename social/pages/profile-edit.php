<?php
include("../scripts/format_date.php");
require_once "../scripts/db_connect.php";
session_start();

$user_id = null;
$will_banner_picture_change  = false;
$new_password = $confirm_new_password = "";
$username_err = $first_name_err = $last_name_err = $email_err = $new_password_err = $confirm_new_password_err = $update_profile_success = $update_profile_err = "";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $user_id = $_SESSION["id"];

    $userQuery = "SELECT * FROM users WHERE id = ?";
    $stmt = $link->prepare($userQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $temp_user_data = $result->fetch_assoc();


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (is_uploaded_file($_FILES['banner_picture']['tmp_name'])) {
            date_default_timezone_set('Europe/Istanbul');
            $file_extension = strtolower(pathinfo($_FILES["banner_picture"]["name"], PATHINFO_EXTENSION));
            $uploaded_file_name = $user_id . "_" . date("Y-m-d_H-i-s") . "_" . rand() . '.' . $file_extension;
            $source_path = $_FILES["banner_picture"]["tmp_name"];
            $target_path = '../assets/images/user/banner/' . $uploaded_file_name;

            if (move_uploaded_file($source_path, $target_path)) {
                if ($file_extension == 'jpg' || $file_extension == 'png' || $file_extension == 'jpeg') {
                    $banner_picture = $uploaded_file_name;
                } else {
                    $banner_picture_err = "Please enter a valid file extension.";
                }
            } else {
                $banner_picture_err = "We encountered a problem while saving your image.";
            }
        } else {
            $banner_picture = $temp_user_data["banner_picture"];
        }

        if (is_uploaded_file($_FILES['profile_picture']['tmp_name'])) {
            date_default_timezone_set('Asia/Kolkata');
            $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
            $uploaded_file_name = $user_id . "_" . date("Y-m-d_H-i-s") . "_" . rand() . '.' . $file_extension;
            $source_path = $_FILES["profile_picture"]["tmp_name"];
            $target_path = '../assets/images/user/profile/' . $uploaded_file_name;

            if (move_uploaded_file($source_path, $target_path)) {
                if ($file_extension == 'jpg' || $file_extension == 'png' || $file_extension == 'jpeg') {
                    $profile_picture = $uploaded_file_name;
                } else {
                    $profile_picture_err = "Please enter a valid file extension..";
                }
            } else {
                $profile_picture_err = "We encountered an issue while saving your image.";
            }
        } else {
            $profile_picture = $temp_user_data["profile_picture"];
        }

        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter your username.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
            $username_err = "The username can only contain letters, numbers, and underscores (_).";
        } else {
            $param_username = trim($_POST["username"]);
            $query = "SELECT id FROM users WHERE username = ? AND id != ?;";
            $stmt = $link->prepare($query);
            $stmt->bind_param("si", $param_username, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $temp = $result->fetch_assoc();

            if ($result->num_rows >= 1) {
                $username_err = "This username is already taken.";
            } else {
                $username = trim($_POST["username"]);
            }
        }

        if (empty(trim($_POST["first_name"]))) {
            $first_name_err = "Please enter your first name.";
        } else {
            $first_name = trim($_POST["first_name"]);
        }

        if (empty(trim($_POST["last_name"]))) {
            $last_name_err = "Please enter your last name..";
        } else {
            $last_name = trim($_POST["last_name"]);
        }

        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter your email address.";
        } elseif (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', trim($_POST["email"]))) {
            $email_err = "Please enter a valid email address.";
        } else {
            $param_email = trim($_POST["email"]);
            $query = "SELECT id FROM users WHERE email = ? AND id != ?;";
            $stmt = $link->prepare($query);
            $stmt->bind_param("si", $param_email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $temp = $result->fetch_assoc();

            if ($result->num_rows >= 1) {
                $email_err = "This email address is already taken.";
            } else {
                $email = trim($_POST["email"]);
            }
        }

        if (!empty(trim($_POST["new_password"])) || !empty(trim($_POST["confirm_new_password"]))) {
            $will_new_password_change = true;

            if (empty(trim($_POST["new_password"]))) {
                $new_password_err = "Please enter your password..";
            } elseif (strlen(trim($_POST["new_password"])) < 6) {
                $new_password_err = "Your password must be at least 6 characters long.";
            } else {
                $new_password = trim($_POST["new_password"]);
            }

            if (empty(trim($_POST["confirm_new_password"]))) {
                $confirm_new_password_err = "Please confirm your password.";
            } else {
                $confirm_new_password = trim($_POST["confirm_new_password"]);
                if (empty($password_err) && ($new_password != $confirm_new_password)) {
                    $confirm_new_password_err = "The passwords do not match.";
                }
            }
        } else {
            $will_new_password_change = false;
        }

        if (empty($new_password_err) && empty($profile_picture_err) && empty($banner_picture_err) && empty($confirm_new_password_err) && empty($username_err) && empty($first_name_err) && empty($last_name_err) && empty($email_err)) {
            if ($will_new_password_change) {
                $query = "UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ?, password = ?, profile_picture = ?, banner_picture = ? WHERE id=?";
                $stmt = $link->prepare($query);
                if ($stmt) {
                    $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt->bind_param("sssssssi", $username, $first_name, $last_name, $email, $param_password, $profile_picture, $banner_picture, $user_id);
                    if ($stmt->execute()) {
                        $update_profile_success = "Your profile has been successfully updated";
                    } else {
                        $update_profile_error = "Sorry, your post could not be shared at this time.";
                    }
                } else {
                    $update_profile_error = "Sorry, your post could not be shared at this time.";
                }
            } else {
                $query = "UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ?, profile_picture = ?, banner_picture = ? WHERE id=?";
                $stmt = $link->prepare($query);
                if ($stmt) {
                    $stmt->bind_param("ssssssi", $username, $first_name, $last_name, $email, $profile_picture, $banner_picture, $user_id);
                    if ($stmt->execute()) {
                        $update_profile_success = "Your profile has been successfully updated.";
                    } else {
                        $update_profile_error = "Sorry, your profile cannot be updated at this time. Please try again later.";
                    }
                } else {
                    $update_profile_error = "Sorry, your profile cannot be updated at this time. Please try again later.";
                }
            }
        }
    }
} else {
    header("location: sign-up.php");
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="icon" href="/social2/social/assets/images/favicon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.10.2/dist/cdn.min.js"></script>
</head>

<body>
    <?php include("../components/header.php");  ?>
    <?php include("../components/profile/profile-edit-card.php"); ?>
</body>

</html>