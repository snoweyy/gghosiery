<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/security.php';

start_secure_session();
unset($_SESSION['website_user']);
redirect_to('/');
