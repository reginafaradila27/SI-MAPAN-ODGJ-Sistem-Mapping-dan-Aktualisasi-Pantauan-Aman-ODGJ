<?php require 'config/db.php';$_SESSION=[];session_regenerate_id(true);session_destroy();redirect('login.php');
