<?php

namespace App\Http\Controllers\Api\CRM\Offer;

use App\Exceptions\ApiProblemException;
use App\Models\CRM\Lead;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\OfferSended;
use App\Notifications\OrderOffer;
use App\Notifications\TempCRMNotify;
use App\Notifications\UserQuestion;
use App\Services\LeadService;
use App\Services\TicketService;
use App\Traits\CaptchaTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use phpseclib\Crypt\Hash;

class OfferController extends Controller
{
    use CaptchaTrait;

    /**
     * @var TicketService
     */
    protected $ticketService;

    /**
     * @var LeadService
     */
    protected $leadService;

    /**
     * OfferController constructor.
     *
     * @param TicketService $ticketService
     * @param LeadService $leadService
     */
    public function __construct(TicketService $ticketService, LeadService $leadService)
    {
        $this->ticketService = $ticketService;
        $this->leadService = $leadService;
    }

    /**
     * @api {post} /api/crm/offer Заказ предложения
     * @apiVersion 0.1.0
     * @apiName OfferCRM
     * @apiGroup CRM
     *
     *
     * @apiParam {string} type Тип предложения. <br>
     * <b>type</b> = 'sanatorium-offer' - предложение от санатория. <br>
     * <b>type</b> = 'other' - обычное предложение <br>
     * <b>type</b> = 'partnership' - предложение о партнерстве<br>
     * <b>type</b> = 'question' - Вопрос FAQ<br>
     *
     * @apiParam {string} name Имя
     * @apiParam {string} [last_name] Фамилие
     * @apiParam {string} [middle_name] Отчество
     * @apiParam {string} email Адрес почты
     * @apiParam {string} [tel] Телефон
     * @apiParam {string} offer Текст предложения
     * @apiParam {integer} [country_id] ID страны
     * @apiParam {integer} [city_id] ID города
     *
     * @apiParam {string} response The user response token provided by the reCAPTCHA client-side integration on your site.
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "предложение отправлено"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function offer(Request $request)
    {
        $valid = Validator($request->all(), [
            'type' => 'required|string',
            'name' => 'required|string',
            'last_name' => 'string|max:255',
            'middle_name' => 'string|max:255',
            'tel' => 'string',
            'email' => 'required|email',
            'offer' => 'required|string',
            'response' => 'required',
            'country_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
        ],[
            'response.required' => 'Не отправлен response для reCAPTCHA',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $this->captchaValidate( $request->get('response') ); //: не забудь раскоментировать )

        $data = $request->only('type', 'name', 'last_name', 'middle_name', 'email', 'tel', 'offer');

        $email = $request->get('email');
        $isNewUser = false;
        if ($request->get('type') != 'sanatorium-offer'){
            $email = mb_strtolower($email);
            $user = User::where('email', $email)->first();
            if (is_null($user)){
                $user = new User();
                $password = str_random(8);
                $data['password'] = $password;
                $user->email = $request->get('email');
                $user->name = $request->get('name');
                $user->password = bcrypt($password);
                $user->role_id = User::ROLE_USER;
                $user->self_registered = false;
                $user->confirm_token = str_random(50);
                $user->save();
                $isNewUser = true;
            }
        } else {
            $user = (new User)->forceFill([ //Не создаем пользователя
                'name' => $request->get('name'),
                'email' => $request->get('email')
            ]);
        }
        $data['isNewUser'] = $isNewUser;

        switch($request->get('type')){
            case 'sanatorium-offer' :
                $dataToWrite = $request->only('name',
                    'last_name',
                    'middle_name',
                    'tel',
                    'email',
                    'offer',
                    'country_id',
                    'city_id'
                );
                $this->ticketService->createSanatoriumOffer($dataToWrite);

                $data['type_name'] = 'Поступило предложение от санатория.';
                $user->notify(new OrderOffer($data));

                break;

            case 'other' :

//                $dataToWrite['ticket_type_id'] = TicketType::QUESTION;
//                $this->ticketService->createTicket($dataToWrite);
                $authUser = auth()->guard('api')->user();
                if( !is_null($authUser) && !is_null($authUser->object) ) $data['object_id'] = $authUser->object->id;
                $this->leadService->createLead($data, Lead::TYPE_DOCTOR);

                $data['type_name'] = 'Поступило предложение.';
                $user->notify(new OrderOffer($data));
                break;

            case 'partnership' :

//                $dataToWrite['ticket_type_id'] = TicketType::PARTNERSHIP;
//                $this->ticketService->createTicket($dataToWrite);
                $authUser = auth()->guard('api')->user();
                if( !is_null($authUser) && !is_null($authUser->object) ) $data['object_id'] = $authUser->object->id;
                $this->leadService->createLead($data, Lead::TYPE_PARTNER);

                $data['type_name'] = 'Поступило предложение о партнерстве.';
                $user->notify(new OfferSended($data));
                break;

            case 'question' :
//                $dataToWrite['ticket_type_id'] = TicketType::FAQ_QUESTION;
//                $this->ticketService->createTicket($dataToWrite);
                $authUser = auth()->guard('api')->user();
                if( !is_null($authUser) && !is_null($authUser->object) ) $data['object_id'] = $authUser->object->id;
                $this->leadService->createLead($data, Lead::TYPE_QUESTION);

                $data['type_name'] = 'Новый вопрос от пользователя.';
                $user->notify(new UserQuestion($data));
                break;

            default :
                throw new ApiProblemException('Необрабатываемый тип предложения type', 422);
        }

        (new User)->forceFill([ //Оповещения для теста
            'name' => 'Доктор',
            'email' => env('DOCTOR_EMAIL', 'digital@tour-shop.ru')
        ])->notify(new TempCRMNotify($data));

        return response()->json(['message' => 'предложение отправлено']);
    }
}
