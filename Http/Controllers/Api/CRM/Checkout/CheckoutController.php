<?php

namespace App\Http\Controllers\Api\CRM\Checkout;

use App\Exceptions\ApiProblemException;
use App\Models\CRM\Lead;
use App\Models\ShowcaseRoom;
use App\Models\User;
use App\Notifications\NewCheckoutNotification;
use App\Services\LeadService;
use App\Services\ReservationService;
use App\Traits\CaptchaTrait;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    use CaptchaTrait;

    /**
     * @var LeadService
     */
    protected $leadService;

    /**
     * @var ReservationService
     */
    protected $reservationService;

    /**
     * CheckoutController constructor.
     *
     * @param ReservationService $reservationService
     * @param LeadService $leadService
     */
    public function __construct(ReservationService $reservationService, LeadService $leadService)
    {
        $this->reservationService = $reservationService;
        $this->leadService = $leadService;
    }

    /**
     * @api {post} /api/crm/checkout Бронь номера
     * @apiVersion 0.1.0
     * @apiName CheckoutCRM
     * @apiGroup CRM
     *
     * @apiParam {integer} showcase_room_id ID номера
     * @apiParam {string} name Имя
     * @apiParam {string} email Адрес почты
     * @apiParam {string} [tel] Телефон
     * @apiParam {string} text Текст доп. информации
     * @apiParam {date} from_date Дата заезда ("2019-03-02")
     * @apiParam {integer} date_count Количество дней
     *
     * @apiParam {string} response The user response token provided by the reCAPTCHA client-side integration on your site.
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "запрос отправлен"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkout(Request $request)
    {
        $valid = Validator($request->all(),[
            'showcase_room_id' => 'required|integer',
            'name' => 'required|string',
            'tel' => 'string',
            'email' => 'required|email',
            'text' => 'string|nullable',
            'from_date' => 'required|date',
            'date_count' => 'required|integer',
            'response' => 'required',
        ],[
            'response.required' => 'Не отправлен response для reCAPTCHA',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        try {
            $this->captchaValidate( $request->get('response') ); //: раскомментируй перед пушем

            $data = $request->only('name', 'tel', 'email', 'text', 'from_date', 'date_count');
            $roomId = $request->get('showcase_room_id');

            $this->reservationService->create($roomId, $data);

            $data['offer'] = $data['text']; unset($data['text']);
            $data = array_merge($data, $request->only( 'showcase_room_id', 'last_name', 'middle_name'));
            $authUser = auth()->guard('api')->user();
            if( !is_null($authUser) && !is_null($authUser->object) ) $data['object_id'] = $authUser->object->id;
            $this->leadService->createLead($data, Lead::TYPE_ORDER);

        } catch (ApiProblemException $exception){

            return response()->json( ['message' => $exception->getMessage()], $exception->getCode() );
        }

        return response()->json(['message' => 'запрос отправлен'], 200);
    }
}
