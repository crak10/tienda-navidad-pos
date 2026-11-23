<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
redirect(usuario_actual() ? 'dashboard.php' : 'login.php');