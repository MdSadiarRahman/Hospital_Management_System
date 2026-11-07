<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalNotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display the Notification Center for the authenticated staff user.
     */
    public function index(Request $request): View
    {
        $filters = [
            'type' => $request->query('type'),
            'is_read' => $request->query('is_read'),
        ];

        $user = $request->user();
        $notifications = $this->notificationService->getPaginatedForUser($user, $filters, 20);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return view('portal.notifications.index', compact('notifications', 'filters', 'unreadCount'));
    }

    /**
     * Return unread count and latest unread notifications for navbar bell polling.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->notificationService->getUnreadCount($user);
        $recent = $this->notificationService->getRecentUnreadForUser($user, 5);

        return response()->json([
            'count' => $count,
            'recent' => $recent->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type,
                'type_badge' => $n->type_badge_class,
                'type_icon' => $n->type_icon,
                'action_url' => $n->action_url,
                'created_at_human' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $success = $this->notificationService->markAsRead($id, $user);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => $success]);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $count = $this->notificationService->markAllAsRead($user);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'updated_count' => $count]);
        }

        return redirect()->back()->with('success', "All {$count} notifications marked as read.");
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $success = $this->notificationService->deleteNotification($id, $user);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => $success]);
        }

        return redirect()->back()->with('success', 'Notification dismissed.');
    }
}

