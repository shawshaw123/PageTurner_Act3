<?php

namespace App\Http\Controllers;

use App\Services\AIChatService;
use App\Services\AIServiceManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIChatController extends Controller
{
    protected AIChatService $chatService;
    protected AIServiceManager $aiManager;

    public function __construct(AIChatService $chatService, AIServiceManager $aiManager)
    {
        $this->chatService = $chatService;
        $this->aiManager = $aiManager;
    }

    /**
     * Show the AI Chat page.
     */
    public function index()
    {
        return view('ai.chat');
    }

    /**
     * Send a message and get AI response.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'required|string',
        ]);

        try {
            $result = $this->chatService->chat(
                $request->input('message'),
                $request->input('session_id'),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, I encountered an error. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get conversation history.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id', session()->getId());

        $messages = $this->chatService->getConversationMessages($sessionId);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Start a new conversation.
     */
    public function newConversation(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id', session()->getId());

        $this->chatService->newConversation($sessionId);

        return response()->json([
            'success' => true,
            'message' => 'New conversation started.',
        ]);
    }

    /**
     * AI Usage Dashboard (Admin).
     */
    public function dashboard()
    {
        $stats = $this->aiManager->getUsageStats();
        return view('ai.dashboard', compact('stats'));
    }
}
