<?php

namespace Tests\Unit\Support;

use App\Support\Media;
use Database\Seeders\DemoData;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoContentTest extends TestCase
{
    public function test_demo_catalog_has_local_images_and_bilingual_descriptions(): void
    {
        $places = DemoData::places();
        $souvenirs = DemoData::souvenirs();

        $this->assertCount(10, $places);
        $this->assertCount(10, $souvenirs);

        foreach ([...$places, ...$souvenirs] as $record) {
            $this->assertNotEmpty($record['description_id']);
            $this->assertNotEmpty($record['description_en']);
            $this->assertStringStartsWith('demo/', $record['image']);
            $this->assertFileExists(public_path($record['image']));
            $this->assertSame('image/webp', mime_content_type(public_path($record['image'])));
            $this->assertSame('/'.$record['image'], Media::url($record['image']));
        }
    }

    public function test_media_resolves_demo_assets_from_the_public_directory(): void
    {
        $path = 'demo/destinations/senso-ji-temple.webp';

        $this->assertSame('/demo/destinations/senso-ji-temple.webp', Media::url($path));
    }

    public function test_media_keeps_uploaded_paths_on_the_configured_disk(): void
    {
        $path = 'uploads/places/example.webp';

        $this->assertSame(Storage::disk(Media::diskName())->url($path), Media::url($path));
    }
}
