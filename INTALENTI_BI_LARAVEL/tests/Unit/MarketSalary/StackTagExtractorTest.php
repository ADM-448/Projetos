<?php

namespace Tests\Unit\MarketSalary;

use App\Services\MarketSalary\StackTagExtractor;
use PHPUnit\Framework\TestCase;

class StackTagExtractorTest extends TestCase
{
    public function test_javascript_is_not_classified_as_java(): void
    {
        $extractor = new StackTagExtractor;

        $this->assertSame(['javascript'], $extractor->extract(['JavaScript']));
        $this->assertSame(['java'], $extractor->extract(['Java']));
        $this->assertNotEquals(
            $extractor->extract(['Java']),
            $extractor->extract(['Python'])
        );
    }
}
