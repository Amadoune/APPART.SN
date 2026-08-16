<?php

namespace Tests\Feature;

use Appart\Modules\RealEstateCatalog\Application\Promotion\Contract\PromoteAuthoredPropertyV1;
use Appart\Modules\RealEstateCatalog\Application\Promotion\DeterministicPromoteAuthoredPropertyV1;
use Tests\TestCase;

final class PublicPropertyPromotionBindingTest extends TestCase
{
    public function test_promotion_contract_resolves_to_productive_singleton(): void
    {
        $promotion = $this->app->make(PromoteAuthoredPropertyV1::class);

        self::assertInstanceOf(DeterministicPromoteAuthoredPropertyV1::class, $promotion);
        self::assertSame($promotion, $this->app->make(PromoteAuthoredPropertyV1::class));
    }
}
