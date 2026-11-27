<?php

namespace App\Exceptions;

use RuntimeException;

/** A checkout problem the buyer can fix (stock changed, listing removed). */
class CheckoutException extends RuntimeException
{
}
