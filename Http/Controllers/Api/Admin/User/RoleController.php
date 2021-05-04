<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @api {get} /api/admin/role Получение всех ролей
     * @apiVersion 0.1.0
     * @apiName GetRoles
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 1,
            "name": "Администратор",
            "description": "Администратор с высокими привилегиями",
            "slug": "admin"
        },
        {
            "id": 2,
            "name": "Пациент",
            "description": "Простой пользователь",
            "slug": "patient"
        },
        {
            "id": 3,
            "name": "Объект",
            "description": "Владелец объекта",
            "slug": "object"
        }
    ]
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Role::all(['id', 'name', 'description', 'slug']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
