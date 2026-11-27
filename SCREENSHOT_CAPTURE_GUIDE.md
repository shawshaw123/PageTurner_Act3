# Lab 8 Screenshot Capture Guide

This guide will help you capture all required screenshots for Laboratory Activity 8.

## Prerequisites

1. **Start the Laravel development server:**
   ```bash
   php artisan serve
   ```

2. **Start the queue worker (in a separate terminal):**
   ```bash
   php artisan queue:work
   ```

3. **Ensure your OpenAI API key is configured in .env**

---

## Screenshot 1: Feature in Action - Chat Interface

**What to capture:** The AI chatbot responding to a user query

**Steps:**
1. Navigate to: `http://localhost:8000/ai/chat`
2. Type a message like: "Recommend me a good fiction book"
3. Wait for the AI response
4. **Capture screenshot showing:**
   - The chat interface with sidebar
   - Your message in the chat
   - The AI's response
   - The provider badge (should show "OpenAI GPT-4o-mini" or "Demo Mode")

**Save as:** `screenshot_1_chat_interface.png`

---

## Screenshot 2: Feature in Action - Book Query with RAG

**What to capture:** AI providing book-specific information using database data

**Steps:**
1. In the chat, type: "What books do you have about programming?"
2. Wait for response
3. **Capture screenshot showing:**
   - The AI response mentioning specific books from your catalog
   - Book titles, authors, prices mentioned in the response
   - The conversation context

**Save as:** `screenshot_2_rag_response.png`

---

## Screenshot 3: Feature in Action - Conversation History

**What to capture:** Multiple messages in a conversation

**Steps:**
1. Send 3-4 different messages in the chat:
   - "hello"
   - "books under $15"
   - "how many books in stock?"
2. **Capture screenshot showing:**
   - Multiple message bubbles (user and assistant)
   - The conversation flow
   - Timestamps on messages

**Save as:** `screenshot_3_conversation_history.png`

---

## Screenshot 4: Admin Interface - AI Dashboard

**What to capture:** The admin AI usage dashboard

**Steps:**
1. Login as admin: `admin@pageturner.com` / `admin123`
2. Navigate to: `http://localhost:8000/admin/ai/dashboard`
3. **Capture screenshot showing:**
   - Dashboard metrics (today's tokens, cost, requests)
   - Total statistics
   - Provider breakdown chart/table
   - Any usage graphs

**Save as:** `screenshot_4_admin_dashboard.png`

---

## Screenshot 5: Admin Interface - Usage Logs

**What to capture:** Detailed AI usage logs

**Steps:**
1. On the admin dashboard, scroll to the usage logs section
2. **Capture screenshot showing:**
   - Individual AI call records
   - Provider used
   - Tokens consumed
   - Response times
   - Success/failure status

**Save as:** `screenshot_5_usage_logs.png`

---

## Screenshot 6: Fallback Mechanism - Mock Provider

**What to capture:** System using mock provider when API unavailable

**Steps:**
1. Temporarily remove or comment out your OpenAI API key in `.env`:
   ```
   # OPENAI_API_KEY=sk-proj-...
   ```
2. Run: `php artisan config:clear`
3. Refresh the chat page
4. Send a message
5. **Capture screenshot showing:**
   - The "DEMO MODE" badge in the header
   - Provider badge showing "Demo Mode (Mock)"
   - The mock response from the AI
6. **Restore your API key** and run `php artisan config:clear`

**Save as:** `screenshot_6_fallback_mock.png`

---

## Screenshot 7: Fallback Mechanism - Error Handling

**What to capture:** Graceful error when all providers fail

**Steps:**
1. Set all API keys to empty in `.env`:
   ```
   OPENAI_API_KEY=
   GEMINI_API_KEY=
   ```
2. Run: `php artisan config:clear`
3. Refresh chat and send a message
4. **Capture screenshot showing:**
   - The error message: "I apologize, but I am temporarily unable to process your request"
   - The toast notification (if visible)
5. **Restore your API keys**

**Save as:** `screenshot_7_fallback_error.png`

---

## Screenshot 8: Queue Processing - Review Submission

**What to capture:** Review being queued for AI moderation

**Steps:**
1. Login as a regular user (or register)
2. Navigate to any book page
3. Submit a review with rating and text
4. **Capture screenshot showing:**
   - The review submission form
   - The submitted review showing "Pending" status
   - The page after submission

**Save as:** `screenshot_8_review_pending.png`

---

## Screenshot 9: Queue Processing - Queue Worker

**What to capture:** Queue worker processing the review

**Steps:**
1. In the terminal where `php artisan queue:work` is running
2. Submit a new review
3. **Capture screenshot showing:**
   - The queue worker output showing the job being processed
   - The job class name: `ProcessReviewAiModeration`
   - Success message after processing

**Save as:** `screenshot_9_queue_worker.png`

---

## Screenshot 10: Queue Processing - Approved Review

**What to capture:** Review after AI moderation

**Steps:**
1. After queue worker processes the review
2. Refresh the book page
3. **Capture screenshot showing:**
   - The review now showing "Approved" status
   - The AI-generated sentiment/summary (if implemented)
   - The review visible on the page

**Save as:** `screenshot_10_review_approved.png`

---

## Screenshot 11: Cost Tracking - Token Usage

**What to capture:** Token consumption metrics

**Steps:**
1. Send several messages in the chat (5-10 messages)
2. Go to admin dashboard: `http://localhost:8000/admin/ai/dashboard`
3. **Capture screenshot showing:**
   - Today's tokens used
   - Total tokens used
   - Cost estimates
   - The numbers increasing after your chat session

**Save as:** `screenshot_11_token_usage.png`

---

## Screenshot 12: Cost Tracking - Provider Breakdown

**What to capture:** Usage by provider

**Steps:**
1. On the admin dashboard
2. **Capture screenshot showing:**
   - Provider breakdown (OpenAI vs Gemini vs Mock)
   - Token counts per provider
   - Cost per provider

**Save as:** `screenshot_12_provider_breakdown.png`

---

## Screenshot 13: Database - AI Tables

**What to capture:** Database records showing AI functionality

**Steps:**
1. Open phpMyAdmin or MySQL Workbench
2. Navigate to database: `pageturner_bookstore`
3. Browse table: `ai_conversations`
4. **Capture screenshot showing:**
   - Conversation records
   - Session IDs
   - User IDs
   - Status (active/closed)

**Save as:** `screenshot_13_db_conversations.png`

---

## Screenshot 14: Database - AI Messages

**What to capture:** Message records

**Steps:**
1. In phpMyAdmin, browse table: `ai_messages`
2. **Capture screenshot showing:**
   - Message records
   - Roles (user/assistant)
   - Content snippets
   - Provider used
   - Tokens used

**Save as:** `screenshot_14_db_messages.png`

---

## Screenshot 15: Database - AI Usage Logs

**What to capture:** Usage log records

**Steps:**
1. In phpMyAdmin, browse table: `ai_usage_logs`
2. **Capture screenshot showing:**
   - Log entries
   - Provider, feature, model
   - Token counts
   - Cost estimates
   - Success status

**Save as:** `screenshot_15_db_usage_logs.png`

---

## Screenshot 16: Mobile Responsive Design

**What to capture:** Chat interface on mobile view

**Steps:**
1. Open browser DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select a mobile device (iPhone 12, etc.)
4. Navigate to chat page
5. **Capture screenshot showing:**
   - Responsive layout
   - Sidebar hidden on mobile
   - Chat messages properly sized
   - Input area accessible

**Save as:** `screenshot_16_mobile_responsive.png`

---

## Screenshot 17: New Conversation Feature

**What to capture:** Starting a new conversation

**Steps:**
1. Click "New Chat" button
2. **Capture screenshot showing:**
   - Welcome message appearing
   - Quick suggestion chips
   - Clean chat interface
   - Previous conversation cleared

**Save as:** `screenshot_17_new_conversation.png`

---

## Screenshot 18: Typing Indicator

**What to capture:** AI typing indicator

**Steps:**
1. Send a message
2. Quickly capture screenshot while AI is "thinking"
3. **Capture screenshot showing:**
   - Typing indicator (three dots animation)
   - "AI is typing..." or similar visual cue
   - Send button disabled

**Save as:** `screenshot_18_typing_indicator.png`

---

## Screenshot 19: Error Toast Notification

**What to capture:** Error message display

**Steps:**
1. Disconnect internet or invalidate API key
2. Send a message
3. **Capture screenshot showing:**
   - Toast notification appearing
   - Error message text
   - Red/error styling

**Save as:** `screenshot_19_error_toast.png`

---

## Screenshot 20: Quick Suggestions

**What to capture:** Suggestion chips in action

**Steps:**
1. On fresh chat page (with welcome message)
2. **Capture screenshot showing:**
   - Welcome message
   - Quick suggestion chips (Recommend fiction, Programming books, etc.)
   - Clean, modern design

**Save as:** `screenshot_20_quick_suggestions.png`

---

## Organization

Create a folder structure:
```
Activity4/
└── lab8_screenshots/
    ├── screenshot_1_chat_interface.png
    ├── screenshot_2_rag_response.png
    ├── screenshot_3_conversation_history.png
    ├── screenshot_4_admin_dashboard.png
    ├── screenshot_5_usage_logs.png
    ├── screenshot_6_fallback_mock.png
    ├── screenshot_7_fallback_error.png
    ├── screenshot_8_review_pending.png
    ├── screenshot_9_queue_worker.png
    ├── screenshot_10_review_approved.png
    ├── screenshot_11_token_usage.png
    ├── screenshot_12_provider_breakdown.png
    ├── screenshot_13_db_conversations.png
    ├── screenshot_14_db_messages.png
    ├── screenshot_15_db_usage_logs.png
    ├── screenshot_16_mobile_responsive.png
    ├── screenshot_17_new_conversation.png
    ├── screenshot_18_typing_indicator.png
    ├── screenshot_19_error_toast.png
    └── screenshot_20_quick_suggestions.png
```

---

## Tips for Good Screenshots

1. **Use full-screen screenshots** (Windows: Win+Shift+S, Mac: Cmd+Shift+4)
2. **Include browser URL bar** to show the route
3. **Ensure good lighting/contrast** - avoid dark screenshots
4. **Crop appropriately** - focus on relevant content
5. **Use consistent sizing** - all screenshots should be similar dimensions
6. **Add annotations** if needed (arrows, circles) to highlight important features
7. **Test before capturing** - ensure the feature is working properly

---

## Minimum Required Screenshots (for grading)

At minimum, you MUST have:
1. ✅ Chat interface with AI response
2. ✅ Admin dashboard
3. ✅ Fallback mechanism (mock or error)
4. ✅ Queue processing (pending review → queue worker → approved)
5. ✅ Cost tracking dashboard

The additional screenshots are recommended for a comprehensive submission.

---

## After Capturing

1. Review all screenshots for quality
2. Rename files if needed for clarity
3. Add to your documentation
4. Include in your presentation slides
5. Upload to your submission folder as required by your instructor
