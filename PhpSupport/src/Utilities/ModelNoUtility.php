<?php

namespace ZhenMu\Support\Utilities;

class ModelNoUtility
{
    public static function setCurrentIndex($model, string $field, &$params = [], $prefix = null, $orderByField = 'created_at', $indexLength = 4, $dateFormat = 'Ymd')
    {
        if (!is_string($model)) {
            $model = get_class($model);
        }

        if (!defined("{$model}::CUSTOMER_NUMBER_PREFIX")) {
            throw new \RuntimeException("{$model}::CUSTOMER_NUMBER_PREFIX doesn't exist.");
        }

        $prefix = $model::CUSTOMER_NUMBER_PREFIX;
        $index = ModelNoUtility::getCurrentIndex($model, $field, $prefix, $orderByField, $indexLength);

        $customerNumber = ModelNoUtility::customerNumber($prefix, $index, $indexLength, $dateFormat);
        $params[$field] = $customerNumber;

        return $customerNumber;
    }


    public static function setCurrentIndexByIndex($model, $index, string $field, &$params = [], $prefix = null, $indexLength = 4, $dateFormat = 'Ymd')
    {
        if (!is_string($model)) {
            $model = get_class($model);
        }

        if (!defined("{$model}::CUSTOMER_NUMBER_PREFIX")) {
            throw new \RuntimeException("{$model}::CUSTOMER_NUMBER_PREFIX doesn't exist.");
        }

        $prefix = $model::CUSTOMER_NUMBER_PREFIX;

        $customerNumber = ModelNoUtility::customerNumber($prefix, $index, $indexLength, $dateFormat);
        $params[$field] = $customerNumber;

        return $customerNumber;
    }

    public static function getCurrentIndex($model, string $field, $prefix = null, $orderByField = 'created_at', $indexLength = 4)
    {
        if (!is_string($model)) {
            $model = get_class($model);
        }

        if (!defined("{$model}::CUSTOMER_NUMBER_PREFIX")) {
            throw new \RuntimeException("{$model}::CUSTOMER_NUMBER_PREFIX doesn't exist.");
        }

        $orderByField = $orderByField ?? 'created_at';

        $date = now();
        $prefix = $model::CUSTOMER_NUMBER_PREFIX ?? $prefix;
        $batch_number = $model::whereDate($orderByField, $date)
            ->orderByDesc($orderByField)
            ->value($field) ?? 0;

        $index = 0;
        if ($batch_number) {
            $index = mb_substr($batch_number, -$indexLength);
        }

        return $index;
    }

    public static function customerNumber(?string $prefix = null, int $currentIndex = 0, $indexLength = 4, string $dateFormat = 'Ymd')
    {
        $nextIndex = $currentIndex + 1;
        $nextIndexString = str_pad($nextIndex, $indexLength, '0', STR_PAD_LEFT);

        $date = date($dateFormat);

        if ($prefix && !str_ends_with($prefix, '-')) {
            $prefix = $prefix . '-';
        } else {
            $prefix = '';
        }

        return "{$prefix}{$date}-{$nextIndexString}";
    }
}