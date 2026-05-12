<?php
require 'auth.php';
logoutUser();
header('Location: index.php');
exit;
