<?php

namespace Tests\Unit\Support;

use App\Support\Brand;
use Tests\TestCase;

class BrandTest extends TestCase
{
    public function test_brand_identity_is_read_from_central_configuration(): void
    {
        config()->set('brand', [
            'name' => 'Europe Travel',
            'mark' => 'ET',
            'legal_name' => 'Europe Travel Studio',
            'region' => [
                'id' => 'Eropa',
                'en' => 'Europe',
            ],
        ]);

        app()->setLocale('id');

        $this->assertSame([
            'name' => 'Europe Travel',
            'mark' => 'ET',
            'legalName' => 'Europe Travel Studio',
            'region' => 'Eropa',
        ], Brand::props());

        app()->setLocale('en');

        $this->assertSame('Europe', Brand::region());
    }

    public function test_brand_uses_safe_fallbacks_for_invalid_configuration(): void
    {
        config()->set('brand.name', null);
        config()->set('brand.mark', '');
        config()->set('brand.legal_name', null);
        config()->set('brand.region', 'invalid');

        app()->setLocale('id');

        $this->assertSame('Japan Travel', Brand::name());
        $this->assertSame('JT', Brand::mark());
        $this->assertSame('Japan Travel', Brand::legalName());
        $this->assertSame('Jepang', Brand::region());
    }
}
