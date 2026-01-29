<?php
function clearSession() {
    session_unset();
    session_destroy();
}
?>