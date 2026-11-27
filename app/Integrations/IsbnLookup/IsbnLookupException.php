<?php

namespace App\Integrations\IsbnLookup;

use RuntimeException;

/** Google Books could not be reached or answered with an error (not "book not found"). */
class IsbnLookupException extends RuntimeException
{
}
