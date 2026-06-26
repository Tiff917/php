<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

logout_user();
redirect('signin.php');
