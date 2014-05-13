<?php
function unsetSession() {
    if(isset($_SESSION['error_hour']) === true) {
        unset($_SESSION['error_hour']);
    }
    if(isset($_SESSION['error_ymd']) === true) {
        unset($_SESSION['error_ymd']);
    }
    if(isset($_SESSION['error_schedule_title']) === true) {
        unset($_SESSION['error_schedule_title']);
    }
    if(isset($_SESSION['error_schedule_detail']) === true) {
        unset($_SESSION['error_schedule_detail']);
    }
    if(isset($_SESSION['error_compare_date']) === true) {
        unset($_SESSION['error_compare_date']);
    }
    if(isset($_SESSION['date_error']) === true) {
        unset($_SESSION['date_error']);
    }
    /*if(isset($_SESSION['count']) === true) {
        unset($_SESSION['count']);
    }*/
    return $_SESSION;
}