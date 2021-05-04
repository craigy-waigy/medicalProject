<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Exceptions\ApiProblemException;
use App\Models\FileStorage;
use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    /**
     * Правила валидации
     *
     * @param string $for
     * @return array
     * @throws ApiProblemException
     */
    public function getRules(string $for)
    {
        switch ($for){
            case FileStorage::FOR_MAIN_PAGE :
                $rules = [];
                break;

            case FileStorage::FOR_MAIN_NEWS :
                $rules = [];
                break;

            case FileStorage::FOR_LIST_NEWS :
                $rules = [];
                break;

            case FileStorage::FOR_NEWS :
                $rules = [ 'news_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_OBJECT_PAGE :
                $rules = [ 'object_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_DISEASE :
                $rules = [ 'disease_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_MEDICAL_PROFILE :
                $rules = [ 'medical_profile_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_THERAPY :
                $rules = [ 'therapy_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_COUNTRY :
                $rules = [ 'country_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_REGION :
                $rules = [ 'region_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_CITY :
                $rules = [ 'city_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_SEARCH_OBJECT_PAGE :
                $rules = [];
                break;

            case FileStorage::FOR_SEARCH_DISEASE :
                $rules = [];
                break;

            case FileStorage::FOR_SEARCH_MEDICAL_PROFILE :
                $rules = [];
                break;

            case FileStorage::FOR_SEARCH_THERAPY :
                $rules = [];
                break;

            case FileStorage::FOR_SEARCH_GEO :
                $rules = [];
                break;

            case FileStorage::FOR_FEEDBACK :
                $rules = [];
                break;

            case FileStorage::FOR_PARTNERS :
                $rules = [];
                break;

            case FileStorage::FOR_OFFER :
                $rules = [ 'offer_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_ABOUT :
                $rules = [ 'about_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_PARTNER :
                $rules = [ 'partner_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_PUBLICATION :
                $rules = [ 'publication_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_RECOMMENDATION_CITY :
                $rules = [ 'recommendation_city_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_RECOMMENDATION_REGION :
                $rules = [ 'recommendation_region_id' => 'required|integer' ];
                break;

            case FileStorage::FOR_RECOMMENDATION_COUNTRY :
                $rules = [ 'recommendation_country_id' => 'required|integer' ];
                break;

            default : throw new ApiProblemException('Обработка данных для параметра for не определена на сервере', 412);
        }

        return $rules;
    }

    /**
     * Добавления файла в хранилище по ссылке
     *
     * @param Request $request
     * @return FileStorage|null
     * @throws ApiProblemException
     */
    public function addFileByLink(Request $request)
    {
        if ($request->has('link_to_file')){
            $condition = $request->only('object_id', 'news_id', 'country_id', 'region_id', 'city_id',
                'disease_id', 'therapy_id', 'medical_profile_id', 'about_id', 'partner_id', 'publication_id',
                'recommendation_city_id', 'recommendation_region_id', 'recommendation_country_id'
            );
            if (count($condition) > 1)
                throw new ApiProblemException('Отправлен более одного идентификатора');

            $fileStorage = new FileStorage();
            foreach ($condition as $field=>$value){
                $fileStorage->$field = $value;
            }

            $linkToFile = $request->get('link_to_file');
            $fileContent = file_get_contents($linkToFile);
            $fileName = uniqid() . '_' . basename($linkToFile);
            $path = "files/$fileName";

            Storage::put($path, $fileContent);

            $url = env('APP_URL') . Storage::url($path);
            $fileStorage->for = $request->get('for');
            $fileStorage->url = $url;
            $fileStorage->file = Storage::url($path);
            $fileStorage->save();

            return $fileStorage;
        } else return null;
    }

    /**
     * Добавления файла в хранилище
     *
     * @param Request $request
     * @return FileStorage|null
     * @throws ApiProblemException
     */
    public function addFile(Request $request)
    {
        if ($request->hasFile('file')){
            $condition = $request->only('object_id', 'news_id', 'country_id', 'region_id', 'city_id',
                'disease_id', 'therapy_id', 'medical_profile_id', 'about_id', 'partner_id', 'publication_id',
                'recommendation_city_id', 'recommendation_region_id', 'recommendation_country_id'
            );
            if (count($condition) > 1)
                throw new ApiProblemException('Отправлен более одного идентификатора');
            $fileStorage = new FileStorage();
            foreach ($condition as $field=>$value){
                $fileStorage->$field = $value;
            }
            $fileName = uniqid() . '.' . $request->file('file')->getClientOriginalExtension();
            $path = $request->file('file')->storeAs('files', $fileName);
            $url = env('APP_URL') . Storage::url($path);
            $fileStorage->for = $request->get('for');
            $fileStorage->url = $url;
            $fileStorage->file = Storage::url($path);
            $fileStorage->save();

            return $fileStorage;
        } else return null;
    }

    /**
     * Получение файлов
     *
     * @param Request $request
     * @return mixed
     * @throws ApiProblemException
     */
    public function getFiles(Request $request)
    {
        $condition = $request->only('object_id', 'news_id', 'country_id', 'region_id', 'city_id',
            'disease_id', 'therapy_id', 'medical_profile_id', 'about_id', 'partner_id', 'publication_id',
            'recommendation_city_id', 'recommendation_region_id', 'recommendation_country_id'
        );
        if (count($condition) > 1)
            throw new ApiProblemException('Отправлен более одного идентификатора');
        $fileStorage = FileStorage::where('for', $request->get('for'));
        foreach ($condition as $field=>$value){
            $fileStorage->where($field, $value);
        }

        return $fileStorage->get(['id', 'for', 'url']);
    }

    /**
     * Удаление файла из хранилища
     *
     * @param int $id
     * @throws ApiProblemException
     */
    public function deleteFile(int $id)
    {
        $storage = FileStorage::find($id);
        if (is_null($storage)) throw new ApiProblemException('Файл не найден', 404);
        Storage::delete('files/' . basename($storage->file));
        $storage->delete();
    }
}
