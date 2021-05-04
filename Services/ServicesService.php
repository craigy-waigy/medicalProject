<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServicesService
{
    protected $paginatorFormat;

    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Добавление категории
     *
     * @param Request $request
     * @return ServiceCategory
     */
    public function addCategory(Request $request)
    {
        $newCategory = new ServiceCategory();

        $newCategory->name_ru = $request->get('name_ru');
        $newCategory->name_en = $request->get('name_en');
        $newCategory->sorting = $request->get('sorting') ?? 0;
        $newCategory->active = $request->get('active') ?? false;
        if ($request->hasFile('image')){
            $path = $request->file('image')->store('service_icons');
            $newCategory->image = Storage::url($path);
        }
        $newCategory->save();

        return $newCategory;
    }

    /**
     * Поиск категорий
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @return array
     */
    public function searchCategory(int $page, int $rowsPerPage, ?string $searchKey)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = ServiceCategory::when($searchKey, function ($query, $searchKey){
            if (!is_null($searchKey)){
                $searchCond = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                $searchCond = $query->orWhereRaw("lower(name_en) LIKE '%{$searchKey}%'");

                return $searchCond;
            }
        })->select(['id', 'name_ru', 'name_en', 'image', 'active']);
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->withCount('services')->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение категории по id
     *
     * @param int $categoryId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getCategory(int $categoryId)
    {
        $category = ServiceCategory::where('id', $categoryId)
            ->with('services')->first();
        if (is_null($category)) throw new ApiProblemException('Запись не найдена', 404);

        return $category;
    }

    /**
     * Редактирование категории
     *
     * @param Request $request
     * @param int $categoryId
     * @return bool
     */
    public function editCategory(Request $request, int $categoryId)
    {
        $category = ServiceCategory::find($categoryId);
        if (!is_null($category)){
            $data = $request->only('name_ru', 'name_en', 'active');
            foreach ($data  as $field=>$value){
                $category->$field = $value;
            }
            if ($request->hasFile('image')){
                Storage::delete('service_icons/' . basename($category->image));
                $path = $request->file('image')->store('service_icons');
                $category->image = Storage::url($path);
            }
            $category->save();

            return true;
        } else return false;
    }

    /**
     * Удаление категории
     *
     * @param int $categoryId
     * @return array
     */
    public function deleteCategory(int $categoryId)
    {
        $category = ServiceCategory::find($categoryId);
        if ($category->services()->count() == 0){
            if (!is_null($category)){

                Storage::delete('service_icons/' . basename($category->image));
                $category->delete();

                return ['message' => ['service' => ['удалено']], 'status' => 200];
            } else {
                return ['message' => ['service' => ['не найдено']], 'status' => 404];
            }
        } else {
            return ['message' => ['service' => ['Не может быть удалено']], 'status' => 419];
        }
    }

    /**
     * Добавление / изменение иконки категории
     *
     * @param Request $request
     * @param int $categoryId
     * @return bool
     */
    public function addIconCategory(Request $request, int $categoryId)
    {
        $category = ServiceCategory::find($categoryId);
        if (!is_null($category)){
            if ($request->hasFile('image')){
                Storage::delete('service_icons/' . basename($category->image));
                $path = $request->file('image')->store('service_icons');
                $category->image = Storage::url($path);
                $category->save();

                return true;
            } else false;
        } else false;
    }


    /**
     * Добавление услуги
     *
     * @param array $data
     * @return Service
     */
    public function addService(array $data)
    {
        $newService = new Service();

        $newService->service_category_id = $data['service_category_id'];
        $newService->name_ru = $data['name_ru'];
        $newService->name_en = $data['name_en'];
        $newService->filter_name_ru = $data['filter_name_ru'] ?? $data['name_ru'];
        $newService->filter_name_en = $data['filter_name_en'] ?? $data['name_en'];
        $newService->is_filter = $data['is_filter'] ?? false;
        $newService->active = $data['active'] ?? false;
        $newService->save();

        return $newService;
    }

    /**
     * Поиск услуг
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @return array
     */
    public function searchService(int $page, int $rowsPerPage, ?string $searchKey)
    {
        $skip = ($page - 1) * $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = Service::when($searchKey, function ($query, $searchKey) {
            if (!is_null($searchKey)) {
                $searchCond = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                $searchCond = $query->orWhereRaw("lower(name_en) LIKE '%{$searchKey}%'");

                return $searchCond;
            }
        });
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->with('category')->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение услуги
     *
     * @param int $serviceId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getService(int $serviceId)
    {
        $service = Service::where('id',$serviceId)->with('seo')->first();
        if (is_null($service)) throw new ApiProblemException('Запись не найдена', 404);

        return $service;
    }

    /**
     * Редактирование услуги
     *
     * @param array $data
     * @param int $serviceId
     * @return bool
     * @throws ApiProblemException
     */
    public function editService(array $data, int $serviceId)
    {
        $service = Service::find($serviceId);
        if (is_null($service)) throw new ApiProblemException('Запись не найдена', 404);
        foreach ($data as $field=>$value){
            $service->$field = $value;
        }
        $service->save();

        return $service;
    }

    /**
     * Удаление услуги
     *
     * @param int $serviceId
     * @return bool
     * @throws ApiProblemException
     */
    public function deleteService(int $serviceId)
    {
        $service = Service::find($serviceId);
        if (is_null($service)) throw new ApiProblemException('Запись не найдена', 404);

        if (!is_null($service->seo)) $service->seo()->delete();
        $service->delete();
    }

    /**
     * Получение фильтра
     *
     * @param string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getServicesFilter(string $locale)
    {
        $qb = Service::where('is_filter', true)->where('active', true);
        switch ($locale){
            case 'ru' :
                $qb->select([ 'id', 'filter_name_ru as filter_name', 'alias' ]);
                $qb->with('seo:id,service_id,order,title_ru as title');
                break;

            case 'en' :
                $qb->select([ 'id', 'filter_name_en as filter_name', 'alias' ]);
                $qb->with('seo:id,service_id,order,title_en as title');
                break;

            default :
                throw new ApiProblemException('Локаль не поддерживается', 422);
        }

        return $qb->get();
    }
}
