<?php

namespace Tests\Feature;

use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionReaderV1;
use Appart\Modules\Geography\Application\GeographySelection\DeterministicGeographySelectionReader;
use Tests\TestCase;

final class GeographySelectionBindingTest extends TestCase
{
    public function test_reader_is_bound_to_the_dedicated_read_only_composition(): void
    {
        self::assertInstanceOf(DeterministicGeographySelectionReader::class, $this->app->make(GeographySelectionReaderV1::class));
    }
}
