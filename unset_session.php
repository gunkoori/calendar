<?php
function unsetSession() {
    if(isset($_SESSION['error']['error_schedule_title']) === true) {
        unset($_SESSION['error']['error_schedule_title']);
    }
    if(isset($_SESSION['error']['error_schedule_detail']) === true) {
        unset($_SESSION['error']['error_schedule_detail']);
    }
    if(isset($_SESSION['error']['error_compare_date']) === true) {
        unset($_SESSION['error']['error_compare_date']);
    }
    if(isset($_SESSION['error']['error_date']) === true) {
        unset($_SESSION['error']['error_date']);
    }

    if(isset($SESSION['keep_value']['schedule_detail']) === true) {
        unset($SESSION['keep_value']['schedule_detail']);
    }
    if(isset($SESSION['keep_value']['schedule_title']) === true) {
        unset($SESSION['keep_value']['schedule_title']);
    }
    return $_SESSION;
}