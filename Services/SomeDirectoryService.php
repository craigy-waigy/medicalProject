<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\SomeDirectory;
use App\Models\SomeDirectoryType;
use Illuminate\Support\Facades\DB;

class SomeDirectoryService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * SanatoriumDoctorService constructor.
     * @param PaginatorFormat $paginatorFormat
     */
    public function __construct(PaginatorFormat $paginatorFormat)
    {
        $this->paginatorFormat = $paginatorFormat;
    }

    /**
     * Получение всех справочников
     *
     * @return object
     */
    public function getAll()
    {
        $someDirectories = SomeDirectory::distinct('type')->get();

        $directories = [];
        foreach ($someDirectories as $directory){
            $directories[$directory->type] = $this->get($directory->type);
        }

        return (object)$directories;
    }

    /**
     * Получение справочника
     *
     * @param string $type
     * @param bool $fromAdmin
     * @return array|string
     */
    public function get(string $type, bool $fromAdmin = false)
    {
        if ($fromAdmin){
            $type = SomeDirectoryType::where('type', $type)->with('items')->first();

            return $type;
        } else {
            $directories = SomeDirectory::where('type', $type)->get();
            $lists = [];
            foreach ($directories as $directory)
            {
                $lists[] = $directory->value;
            }

            return $lists;
        }
    }

    /**
     * Создание/Редактирование справочника
     *
     * @param string $type
     * @param array $items
     * @param null|string $description
     * @return array|string
     */
    public function update(string $type, array $items, ?string $description = null)
    {
        DB::transaction(function () use($type, $items, $description){
            SomeDirectory::where('type', $type)->delete();
            foreach ($items as $item){
                $directory = new SomeDirectory();
                $directory->type = $type;
                $directory->value = $item;
                $directory->save();
            }
            if (!is_null($description)){
                SomeDirectoryType::where('type', $type)->update([
                    'description' => $description
                ]);
            }

        });

        return $this->get($type, true);
    }

    /**
     * Получение типа справочника
     *
     * @param int $typeId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getType(int $typeId)
    {
        $type = SomeDirectoryType::find($typeId);

        if (is_null($type))
            throw new ApiProblemException('Справочник не найден', 404);

        return $type;
    }

    /**
     * Редактирование типа стправочника
     *
     * @param int $typeId
     * @param array $data
     * @return mixed
     * @throws ApiProblemException
     */
    public function editType(int $typeId, array $data)
    {
        $type = SomeDirectoryType::find($typeId);

        if (is_null($type))
            throw new ApiProblemException('Справочник не найден', 404);

        $exist = SomeDirectoryType::where('id', '<>', $typeId)->where('type', $data['type'])->count();
        if ($exist > 0)
            throw new ApiProblemException('Справочник с таким именем уже существует', 400);

        foreach ($data as $field => $value){
            $type->$field = $value;
        }
        $type->save();

        return $this->getType($type->id);
    }

    /**
     * Создание справочника
     *
     * @param string $type
     * @param string $description
     * @param array $items
     * @return array|string
     * @throws ApiProblemException
     */
    public function create(string $type, string $description, array $items)
    {
        $type = mb_strtolower($type);
        $count = SomeDirectoryType::where('type', $type)->count();
        if ( $count > 0 )
            throw new ApiProblemException('Тип справочника уже существует', 422);

        $someDirectoryType = new SomeDirectoryType;
        $someDirectoryType->type = $type;
        $someDirectoryType->description = $description;
        $someDirectoryType->save();
        foreach ($items as $item){
            $directory = new SomeDirectory();
            $directory->type = $type;
            $directory->value = $item;
            $directory->save();
        }

        return $this->get($type, true);
    }

    /**
     * Удаление
     *
     * @param string $type
     */
    public function delete(string $type)
    {
        $type = strtolower($type);
        SomeDirectoryType::where('type', $type)->delete();
    }

    /**
     *  Получение списка справочников
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param array|null $sorting
     * @param null|string $searchKey
     * @return array
     */
    public function listTypes(int $page, int $rowsPerPage, ?array $sorting = null, ?string $searchKey = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = mb_strtolower($searchKey);

        $qb = SomeDirectoryType::
            when($sorting, function ($query, $sorting){
                if ( !is_null($sorting)) {
                    foreach ($sorting as $key => $value) {
                        $query = $query->orderBy($key, $value);
                    }
                    return $query;
                } else {
                    return $query->orderBy('updated_at', 'desc');
                }
            })
            ->when($searchKey, function ($query, $searchKey){
                if (!is_null($searchKey)){
                    $query->whereRaw("lower(type) LIKE '%{$searchKey}%' OR lower(description) LIKE '%{$searchKey}%'");
                }

                return $query;
            });

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);


    }
}
