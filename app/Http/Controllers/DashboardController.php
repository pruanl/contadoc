<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\WebhookEvent;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $clients = Client::query()->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id));
        $documents = Document::query()->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id));
        $messages = WhatsappMessage::query()->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id));

        return view('dashboard', [
            'clientsCount' => (clone $clients)->count(),
            'pendingClientsCount' => (clone $clients)->where('status', Client::STATUS_PENDING)->count(),
            'documentsCount' => (clone $documents)->count(),
            'newDocumentsCount' => (clone $documents)->where('status', 'new')->count(),
            'pendingDocumentsCount' => (clone $documents)->where('status', 'pending')->count(),
            'recentDocuments' => (clone $documents)->with('client')->latest('received_at')->take(6)->get(),
            'recentMessages' => (clone $messages)->with('client')->latest('message_at')->take(6)->get(),
            'failedWebhooksCount' => WebhookEvent::where('status', 'failed')->count(),
        ]);
    }
}
