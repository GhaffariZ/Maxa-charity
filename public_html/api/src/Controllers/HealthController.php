<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Request;
use Maksa\Core\Response;

final class HealthController
{
    public function ping(Request $request): void
    {
        Response::success(['status' => 'ok', 'service' => 'maksa-api', 'time' => gmdate('c')]);
    }
}
