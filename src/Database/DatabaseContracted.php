<?php

namespace Dreamscape\Database;

require_once COMMON_CLASSES_DIR . 'database.php';

use Dreamscape\Contracts\Database\Database as DatabaseContract;

class DatabaseContracted extends \Database implements DatabaseContract
{

}