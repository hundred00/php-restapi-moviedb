<?php
session_start();
session_destroy();
header("Location: /movie-database/login");
exit;