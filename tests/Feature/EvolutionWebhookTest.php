<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\AuthorizedSender;
use App\Models\Document;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Models\WhatsappMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvolutionWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_ignores_unauthorized_sender(): void
    {
        $this->postJson(route('webhooks.evolution'), $this->payload('5585988887777'), [
            'X-Evolution-Webhook-Secret' => 'testing-secret',
        ])->assertOk();

        $this->assertSame(0, Client::count());
        $this->assertSame(0, WhatsappMessage::count());

        $this->assertDatabaseHas('webhook_events', [
            'status' => 'ignored',
        ]);
    }

    public function test_webhook_with_client_command_links_existing_client(): void
    {
        $user = User::factory()->create();
        $this->authorizeSender($user, '5585999990000');

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Empresa Azul',
            'phone' => '(85) 98888-7777',
            'normalized_phone' => '5585988887777',
            'status' => Client::STATUS_ACTIVE,
        ]);

        $payload = $this->payload('5585999990000', 'cliente Empresa Azul');
        $payload['data']['message']['documentMessage'] = [
            'fileName' => 'balancete.pdf',
            'mimetype' => 'application/pdf',
            'base64' => base64_encode('pdf-content'),
        ];

        $this->postJson(route('webhooks.evolution'), $payload, [
            'X-Evolution-Webhook-Secret' => 'testing-secret',
        ])->assertOk();

        $this->assertDatabaseHas('whatsapp_messages', [
            'client_id' => $client->id,
            'user_id' => $user->id,
            'remote_phone' => '5585999990000',
        ]);

        $this->assertDatabaseHas('documents', [
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'new',
            'origin' => 'official_whatsapp',
            'sender_phone' => '5585999990000',
            'client_hint' => 'Empresa Azul',
        ]);
    }

    public function test_webhook_with_media_and_unknown_client_goes_to_triage(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->authorizeSender($user, '5585988887777');

        $payload = $this->payload('5585988887777', 'cliente Cliente Inexistente');
        $payload['data']['message']['documentMessage'] = [
            'fileName' => 'balancete.pdf',
            'mimetype' => 'application/pdf',
            'base64' => base64_encode('pdf-content'),
        ];

        $this->postJson(route('webhooks.evolution'), $payload, [
            'X-Evolution-Webhook-Secret' => 'testing-secret',
        ])->assertOk();

        $document = Document::first();

        $this->assertNotNull($document);
        $this->assertNull($document->client_id);
        $this->assertSame($user->id, $document->user_id);
        $this->assertSame('pending', $document->status);
        $this->assertSame('Cliente Inexistente', $document->client_hint);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_webhook_matches_authorized_sender_with_brazilian_ninth_digit_variant(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $this->authorizeSender($user, '5599991726895');

        $payload = $this->payload('559991726895', 'cliente Cliente Inexistente');
        $payload['data']['message']['imageMessage'] = [
            'fileName' => 'foto.jpg',
            'mimetype' => 'image/jpeg',
            'base64' => base64_encode('image-content'),
        ];

        $this->postJson(route('webhooks.evolution'), $payload, [
            'X-Evolution-Webhook-Secret' => 'testing-secret',
        ])->assertOk();

        $this->assertDatabaseHas('whatsapp_messages', [
            'user_id' => $user->id,
            'remote_phone' => '559991726895',
        ]);

        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'sender_phone' => '5599991726895',
            'status' => 'pending',
        ]);
    }

    public function test_webhook_with_encrypted_whatsapp_media_url_records_document_without_download(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $this->authorizeSender($user, '5599991726895');

        $payload = $this->payload('559991726895', 'cliente Cliente Inexistente');
        $payload['data']['message']['imageMessage'] = [
            'url' => 'https://mmg.whatsapp.net/v/t62.7118-24/file.enc?ccb=11-4',
            'mimetype' => 'image/jpeg',
            'fileLength' => [
                'low' => 81772,
                'high' => 0,
                'unsigned' => true,
            ],
        ];

        $this->postJson(route('webhooks.evolution'), $payload, [
            'X-Evolution-Webhook-Secret' => 'testing-secret',
        ])->assertOk();

        $document = Document::first();

        $this->assertNotNull($document);
        $this->assertNull($document->file_path);
        $this->assertSame('image/jpeg', $document->mime_type);
        $this->assertSame(81772, $document->size);
        $this->assertSame('pending', $document->status);
        $this->assertSame('5599991726895', $document->sender_phone);
    }

    public function test_webhook_fetches_base64_from_evolution_when_media_url_is_encrypted(): void
    {
        Storage::fake('local');
        Http::fake([
            'https://evolution.test/chat/getBase64FromMediaMessage/contadoc-local' => Http::response([
                'mediaType' => 'imageMessage',
                'fileName' => 'foto-recebida',
                'mimetype' => 'image/jpeg',
                'base64' => 'data:image/jpeg;base64,'.base64_encode('decoded-image'),
            ]),
        ]);
        config([
            'services.evolution.url' => 'https://evolution.test',
            'services.evolution.instance' => 'contadoc-local',
        ]);

        $user = User::factory()->create();
        $this->authorizeSender($user, '5599991726895');

        $payload = $this->payload('559991726895', 'cliente Cliente Inexistente');
        $payload['instance'] = 'contadoc-local';
        $payload['data']['message']['imageMessage'] = [
            'url' => 'https://mmg.whatsapp.net/v/t62.7118-24/file.enc?ccb=11-4',
            'mimetype' => 'image/jpeg',
        ];
        $payload['data']['messageType'] = 'imageMessage';

        $this->postJson(route('webhooks.evolution'), $payload, [
            'X-Evolution-Webhook-Secret' => 'testing-secret',
        ])->assertOk();

        $document = Document::first();

        $this->assertNotNull($document);
        $this->assertNotNull($document->file_path);
        $this->assertSame('foto-recebida.jpg', $document->original_name);
        $this->assertStringEndsWith('.jpg', $document->file_path);
        $this->assertSame('image/jpeg', $document->mime_type);
        $this->assertSame(strlen('decoded-image'), $document->size);
        Storage::disk('local')->assertExists($document->file_path);
        $this->assertSame('decoded-image', Storage::disk('local')->get($document->file_path));

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://evolution.test/chat/getBase64FromMediaMessage/contadoc-local'
                && $request['message']['key']['id'] === 'message-id-123'
                && ! isset($request['message']['key']['remoteJid'])
                && $request['convertToMp4'] === false;
        });
    }

    public function test_webhook_without_media_does_not_create_document(): void
    {
        $user = User::factory()->create();
        $this->authorizeSender($user, '5585988887777');

        $this->postJson(route('webhooks.evolution'), $this->payload('5585988887777'), [
            'X-Evolution-Webhook-Secret' => 'testing-secret',
        ])->assertOk();

        $this->assertSame(0, Document::count());
        $this->assertSame(1, WhatsappMessage::count());
    }

    public function test_webhook_rejects_invalid_secret(): void
    {
        $this->postJson(route('webhooks.evolution'), $this->payload('5585988887777'), [
            'X-Evolution-Webhook-Secret' => 'wrong',
        ])->assertForbidden();

        $this->assertSame(0, WebhookEvent::count());
    }

    public function test_webhook_accepts_event_suffix_when_evolution_webhook_by_events_is_enabled(): void
    {
        $user = User::factory()->create();
        $this->authorizeSender($user, '5585988887777');

        $payload = $this->payload('5585988887777');
        unset($payload['event']);

        $this->postJson('/webhooks/evolution/messages.upsert', $payload, [
            'X-Evolution-Webhook-Secret' => 'testing-secret',
        ])->assertOk();

        $this->assertDatabaseHas('webhook_events', [
            'event_type' => 'messages.upsert',
            'status' => 'processed',
        ]);
    }

    public function test_webhook_accepts_event_suffix_appended_to_secret_query(): void
    {
        $user = User::factory()->create();
        $this->authorizeSender($user, '5585988887777');

        $this->postJson('/webhooks/evolution?secret=testing-secret/messages-upsert', $this->payload('5585988887777'))
            ->assertOk();

        $this->assertDatabaseHas('webhook_events', [
            'event_type' => 'messages.upsert',
            'status' => 'processed',
        ]);
    }

    private function payload(string $phone, string $text = 'Segue documento.'): array
    {
        return [
            'event' => 'messages.upsert',
            'data' => [
                'key' => [
                    'remoteJid' => $phone.'@s.whatsapp.net',
                    'fromMe' => false,
                    'id' => 'message-id-123',
                ],
                'messageTimestamp' => 1_778_765_400,
                'message' => [
                    'conversation' => $text,
                ],
            ],
        ];
    }

    private function authorizeSender(User $user, string $phone): AuthorizedSender
    {
        return AuthorizedSender::create([
            'user_id' => $user->id,
            'name' => 'Fulano',
            'phone' => $phone,
            'normalized_phone' => $phone,
            'is_active' => true,
        ]);
    }
}
