<?php

namespace App\Integrations\PayMongo;

use RuntimeException;

/** PayMongo is not configured, unreachable, or rejected a request. */
class PaymentException extends RuntimeException
{
}
