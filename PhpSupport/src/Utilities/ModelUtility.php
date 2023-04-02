<?php

namespace ZhenMu\Support\Utilities;

class ModelUtility
{
    public static function isMethodInClassChain($className, $methodName)
    {
        $refClass = new \ReflectionClass($className);

        while ($refClass) {
            if ($refClass->hasMethod($methodName)) {
                return true;
            }
            $refClass = $refClass->getParentClass();
        }

        return false;
    }

    /**
        $relations = [];
        // 定义查询条件与执行方式
        // => Model::where('where_field', 'where_field_value')
        // => ->whereIn('where_field_in', ['where_field_value_1', 'where_field_value_2'])
        // => ->get()
        $relations['relationNameInUse']['wheres'][] = [Model::class, 'where_field', 'where_field_value']; 
        $relations['relationNameInUse']['wheres']['whereIn'] = [Model::class, 'where_field_in', ['where_field_value_1', 'where_field_value_2']];
        $relations['tests']['performMethod'] = 'get';

        $relations['tests']['wheres'][] = [Test::class, 'test_number', $params['test_numbers']];
        $relations['tests']['performMethod'] = 'get';

        $relations['experiment_records']['wheres'][] = [CsimExperiment::class, 'experiment_batch_number', $csim_experiment['experiment_batch_number']];
        $relations['experiment_records']['performMethod'] = 'first';
        $relations['experiment_records']['params'] = [['id', 'experiment_batch_number']];

        $relations['user']['wheres'][] = [User::class, 'experiment_batch_number', $csim_experiment['experiment_batch_number']];
        $relations['user']['performMethod'] = 'value';
        $relations['user']['params'] = ['username'];

        // 查询关联数据
        $relationData = RelationUtility::getRelations($relations);
        dd($relationData);
     */
    public static function getData(array $relationsWhereList = [])
    {
        $relations = [];

        foreach ($relationsWhereList as $relationName => $relationsWheres) {
            $relations[$relationName] = static::getRelationData(...$relationsWheres);
        }

        $filterRelations = array_filter($relations);
        return $filterRelations;
    }

    public static function getRelationData(array $wheres = [], $performMethod = null, bool $toArray = true, array $params = [['*']])
    {
        if (empty($wheres)) {
            return null;
        }

        $model = null;
        $query = null;

        foreach ($wheres as $whereKey => $where) {
            if (!$model) {
                $model = $where[0];
            }
            unset($where[0]);

            if (!$model) {
                continue;
            }

            $whereMethod = 'where';
            if (is_string($whereKey)) {
                $whereMethod = $whereKey;
            }

            $query = $model::{$whereMethod}(...$where);
        }

        $result = $query->{$performMethod}(...$params);
        if ($toArray && is_object($result) && method_exists($result, 'toArray')) {
            return $result?->toArray();
        }

        return $result;
    }

    public static function formatRecords($relations, $relationName, $callable)
    {
        if (empty($relations[$relationName])) {
            return [];
        }

        $data = [];
        foreach ($relations[$relationName] as $item) {
            $data[] = $callable($item, $relations);
        }

        return $data;
    }
}
