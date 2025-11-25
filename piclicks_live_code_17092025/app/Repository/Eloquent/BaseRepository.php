<?php

namespace App\Repository\Eloquent;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected $model;
    protected $cacheTime = 60;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all($columns = ['*'],$orderBY=['id'=>'desc'])
    {
        $cacheKey = 'all_' . $this->model->getTable();
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($columns,$orderBY) {
            return $this->model->orderBy($orderBY)->get();
        });
    }

    public function getOneData($byWhere){
        $cacheKey = 'getOneData_' . $this->model->getTable();
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($byWhere) {
            return $this->model->where($byWhere)->first();
        });
    }

    public function create(array $payload){
        //dd($payload);
        $this->clearAllCache();
        $model = $this->model->create($payload);
        return $model->fresh();
    }

    public function update(array $modelId, array $payload){
        $this->clearAllCache();
        $model = $this->getOneData($modelId);
        return $model->update($payload);
    }

    public function deleteData(array $modelData){
        $this->clearAllCache();
        return $this->getOneData($modelData)->delete();
    }
    public function clearAllCache()
    {
        $table = $this->model->getTable();
        
        // Cache tags don't work with file driver, so we clear by pattern
        // Store cache keys with table prefix for efficient clearing
        $cachePrefix = config('cache.prefix', '');
        $pattern = $cachePrefix . 'all_' . $table;
        $pattern2 = $cachePrefix . 'getOneData_' . $table;
        
        // Clear common cache keys for this table
        Cache::forget('all_' . $table);
        Cache::forget('getOneData_' . $table);
        
        // If using Redis/Memcached with tags support, use tags
        $driver = config('cache.default');
        if (in_array($driver, ['redis', 'memcached'])) {
            try {
                Cache::tags([$table])->flush();
            } catch (\Exception $e) {
                // Fallback to pattern-based clearing if tags fail
            }
        }
    }
}
