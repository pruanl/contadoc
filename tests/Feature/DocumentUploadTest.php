<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_document_without_client_for_triage(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('documents.store'), [
                'client_id' => '',
                'status' => 'new',
                'file' => UploadedFile::fake()->create('recibo.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect(route('documents.index'));

        $document = Document::first();

        $this->assertNotNull($document);
        $this->assertNull($document->client_id);
        $this->assertSame($user->id, $document->user_id);
        $this->assertSame('pending', $document->status);
        $this->assertSame('manual', $document->origin);
        $this->assertTrue(Str::isUuid($document->uuid));
        $this->assertStringStartsWith('users/'.$user->storageFolder().'/documents/', $document->file_path);
        $this->assertTrue(Str::isUuid(pathinfo($document->file_path, PATHINFO_FILENAME)));
        $this->assertSame('pdf', pathinfo($document->file_path, PATHINFO_EXTENSION));
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_user_can_open_document_file_inline(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $path = Document::storagePathFor($user, 'sample.jpg');
        Storage::disk('local')->put($path, 'image-bytes');

        $document = Document::create([
            'user_id' => $user->id,
            'file_path' => $path,
            'original_name' => 'sample.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 11,
            'status' => 'pending',
            'origin' => 'manual',
            'received_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('documents.file', $document));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg')
            ->assertHeader('content-disposition', 'inline; filename=sample.jpg');

        $this->assertSame('image-bytes', $response->streamedContent());
    }
}
