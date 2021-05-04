<?php

namespace App\Http\Controllers\Api\Admin\Notification;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * @var NotificationService
     */
    protected $notificationService;


    /**
     * NotificationController constructor.
     */
    public function __construct()
    {
        $this->notificationService = new NotificationService;
    }

    /**
     * @api {get} /api/admin/notification Получение счетчиков нотификаций
     * @apiVersion 0.1.0
     * @apiName GetNotification
     * @apiGroup Notification
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "moderation": {
            "object_count": 3,
            "award_count": 1,
            "partner_count": 1,
            "publication_count": 1
        }
    }
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get()
    {
        $notifications = $this->notificationService->get();

        return response()->json($notifications, 200);
    }
}
