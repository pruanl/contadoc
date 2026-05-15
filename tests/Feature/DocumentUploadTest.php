<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $this->assertStringStartsWith('documents/inbox/', $document->file_path);
        Storage::disk('local')->assertExists($document->file_path);
    }
}
