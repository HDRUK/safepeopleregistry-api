<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\CommonFunctions;
use App\Models\User;

class NotificationController extends Controller
{
    use CommonFunctions;
    /**
     * @OA\Get(
     *      path="/api/v1/users/{id}/notifications",
     *      summary="Get notifications for a specific user",
     *      description="Retrieves notifications for a user, with an optional filter for read/unread notifications.",
     *      tags={"Notifications"},
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="User ID",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          required=false,
     *          description="Filter notifications by status (read/unread)",
     *          @OA\Schema(type="string", enum={"read", "unread"}, example="unread")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="string", example="abc95e84-0ebd-45d2-8129-9bf7ed043433"),
     *                      @OA\Property(property="type", type="string", example="AdminUserChangedOrganisation"),
     *                      @OA\Property(property="notifiable_type", type="string", example="App\Models\User"),
     *                      @OA\Property(property="notifiable_id", type="integer", example=1),
     *                      @OA\Property(property="data", type="object",
     *                          @OA\Property(property="message", type="string", example="A user has changed their details"),
     *                          @OA\Property(property="time", type="string", format="date-time", example="2025-01-21T21:40:11.607325Z")
     *                      ),
     *                      @OA\Property(property="read_at", type="string", format="date-time", nullable=true, example=null),
     *                      @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-21T21:40:11.000000Z"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-21T21:40:11.000000Z")
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="User not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="error", type="string", example="User not found")
     *          )
     *      )
     * )
     */
    public function getUserNotifications(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $status = $request->query('status');

        $notificationsQuery = $user->notifications();
        if ($status === 'read') {
            $notificationsQuery->whereNotNull('read_at');
        } elseif ($status === 'unread') {
            $notificationsQuery->whereNull('read_at');
        }

        $perPage = $request->query('perPage', (int)$this->getSystemConfig('PER_PAGE'));

        $notifications = $notificationsQuery
                            ->orderBy('created_at', 'desc')
                            ->paginate($perPage);

        return response()->json([
            'message' => 'success',
            'data' => $notifications
        ]);
    }

    public function getNotificationCounts($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $readCount = $user->notifications()->whereNotNull('read_at')->count();
        $unreadCount = $user->notifications()->whereNull('read_at')->count();

        return response()->json([
            'message' => 'success',
            'data' => [
                'total' => $readCount + $unreadCount,
                'read' => $readCount,
                'unread' => $unreadCount
            ]
        ]);
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/users/{id}/notifications/read",
     *      summary="Mark all notifications as read for a specific user",
     *      tags={"Notifications"},
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="User ID",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *
     *      @OA\Response(response=200, description="Notifications marked as read", @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="message", type="string", example="Notifications marked as read")
     *      )),
     *      @OA\Response(response=404, description="User not found", @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="error", type="string", example="User not found")
     *      ))
     * )
     */
    public function markUserNotificationsAsRead($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Notifications marked as read']);
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/users/{id}/notifications/{notificationId}/read",
     *      summary="Mark a specific notification as read",
     *      tags={"Notifications"},
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="User ID",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *
     *      @OA\Parameter(
     *          name="notificationId",
     *          in="path",
     *          required=true,
     *          description="Notification ID",
     *          @OA\Schema(type="string", example="abc95e84-0ebd-45d2-8129-9bf7ed043433")
     *      ),
     *
     *      @OA\Response(response=200, description="Notification marked as read", @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="message", type="string", example="Notification marked as read")
     *      )),
     *      @OA\Response(response=404, description="User or notification not found", @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="error", type="string", example="Notification not found")
     *      ))
     * )
     */
    public function markUserNotificationAsRead($userId, $notificationId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/users/{id}/notifications/{notificationId}/unread",
     *      summary="Mark a specific notification as unread",
     *      tags={"Notifications"},
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="User ID",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *
     *      @OA\Parameter(
     *          name="notificationId",
     *          in="path",
     *          required=true,
     *          description="Notification ID",
     *          @OA\Schema(type="string", example="abc95e84-0ebd-45d2-8129-9bf7ed043433")
     *      ),
     *
     *      @OA\Response(response=200, description="Notification marked as unread")
     * )
     */
    public function markUserNotificationAsUnread($userId, $notificationId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsUnread();

        return response()->json(['message' => 'Notification marked as unread']);
    }
}
