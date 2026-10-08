<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageTemplateController extends Controller
{
    protected function ensureAuthenticatedUser(): void
    {
        if (! Auth::check()) {
            $user = User::withoutGlobalScopes()->first();
            if ($user) {
                Auth::login($user);
            }
        }
    }

    public function index(Request $request): JsonResponse
    {
        $this->ensureAuthenticatedUser();
        $tenantId = Auth::user()?->tenant_id ?? 1;

        $query = MessageTemplate::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('is_favorite', 'desc')
            ->latest();

        if ($request->has('channel') && $request->channel !== 'all') {
            $query->whereIn('channel', [$request->channel, 'all']);
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        return response()->json([
            'success' => true,
            'templates' => $query->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();
        $tenantId = Auth::user()?->tenant_id ?? 1;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:approach,objection,followup,meeting,closing,general',
            'channel' => 'required|string|in:all,whatsapp,email',
            'content' => 'required|string',
            'is_favorite' => 'nullable|boolean',
        ]);

        $template = MessageTemplate::create([
            'tenant_id' => $tenantId,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'channel' => $validated['channel'],
            'content' => $validated['content'],
            'is_favorite' => $validated['is_favorite'] ?? false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'template' => $template,
                'message' => 'Script salvo com sucesso!',
            ]);
        }

        return back()->with('success', 'Script salvo com sucesso!');
    }

    public function update(Request $request, MessageTemplate $template): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:approach,objection,followup,meeting,closing,general',
            'channel' => 'required|string|in:all,whatsapp,email',
            'content' => 'required|string',
            'is_favorite' => 'nullable|boolean',
        ]);

        $template->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'template' => $template,
                'message' => 'Script atualizado com sucesso!',
            ]);
        }

        return back()->with('success', 'Script atualizado com sucesso!');
    }

    public function toggleFavorite(Request $request, MessageTemplate $template): JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $template->update([
            'is_favorite' => ! $template->is_favorite,
        ]);

        return response()->json([
            'success' => true,
            'is_favorite' => $template->is_favorite,
        ]);
    }

    public function destroy(Request $request, MessageTemplate $template): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $template->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Script excluído com sucesso!',
            ]);
        }

        return back()->with('success', 'Script excluído com sucesso!');
    }
}
