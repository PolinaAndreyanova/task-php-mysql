<?php
function parseFilterParameters(array $arGet): array
{
    global $arBrandValues, $arPowerValues, $arWeightValues, $arPriceValues;

    if (isset($arGet['brand'])) {
        $arBrands = $arGet['brand'];
    } else {
        $arBrands = $arBrandValues;
    }

    if ($arGet['min-price']) {
        $minPrice = $arGet['min-price'];
    } else {
        $minPrice = $arPriceValues['min'];
    }

    if ($arGet['max-price']) {
        $maxPrice = $arGet['max-price'];
    } else {
        $maxPrice = $arPriceValues['max'];
    }

    if ($arGet['min-power']) {
        $minPower = $arGet['min-power'];
    } else {
        $minPower = $arPowerValues['min'];
    }

    if ($arGet['max-power']) {
        $maxPower = $arGet['max-power'];
    } else {
        $maxPower = $arPowerValues['max'];
    }

    if ($arGet['min-weight']) {
        $minWeight = $arGet['min-weight'];
    } else {
        $minWeight = $arWeightValues['min'];
    }

    if ($arGet['max-weight']) {
        $maxWeight = $arGet['max-weight'];
    } else {
        $maxWeight = $arWeightValues['max'];
    }

    return [
        'brand' => $arBrands, 
        'min-price' => $minPrice, 
        'max-price' => $maxPrice, 
        'min-power' => $minPower, 
        'max-power' => $maxPower, 
        'min-weight' => $minWeight, 
        'max-weight' => $maxWeight
    ];
}

function convertBrandsArrayToSqlString(array $arBrands): string
{
    $sqlBrand = '(';
    foreach ($arBrands as $b) {
        $sqlBrand .= '"' . $b . '", ';
    }
    $sqlBrand = substr($sqlBrand, 0, -2);
    $sqlBrand .= ')';

    return $sqlBrand;
}

function presetParameters(array $arGet): array
{
    if (isset($arGet['number'])) {
        $number = $arGet['number'];
    } else {
        $number = 3;
    }

    if (isset($arGet['cur-page'])) {
        $curPage = $arGet['cur-page'];
    } else {
        $curPage = 1;
    }

    return [
        'number' => $number,
        'cur-page' => $curPage,
    ];
}

function isValidParameters(array $arNumeric, array $arBrands): bool
{
    $isValid = true;

    foreach ($arNumeric as $p) {
        if (!is_numeric($p)) {
            $isValid = !$isValid;
            break;
        }
    }

    foreach ($arBrands as $b) {
        if (!in_array($b, $arBrands)) {
            $isValid = !$isValid;
            break;
        }
    }

    return $isValid;
}

function createSqlQueryString($select, $from, $where = ''): string
{
    if ($where) {
        return 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where;
    }
    
    return 'SELECT ' . $select . ' FROM ' . $from;
}