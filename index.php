<?php
include_once('filterValues.php');
include_once('functions.php');

$link = mysqli_connect('localhost', 'polina', '01032004', 'catalog');

if ($link === false) {
    print('Ошибка: Невозможно подключиться к MySQL ' . mysqli_connect_error());
} else {
    mysqli_set_charset($link, 'utf8');

    $arDefaultParameters = presetParameters($_GET);

    $countProductsOnPage = $arDefaultParameters['number'];
    $currentPage = $arDefaultParameters['cur-page'];
    $arCatalogProducts = [];
    $countPage = 1;

    if (isset($_GET['brand']) and !is_array($_GET['brand'])) {
        $_GET['brand'] = json_decode($_GET['brand']);
    }

    $isValid = false;

    if (isset($_GET['show'])) {
        $arParsedFilterParameters = parseFilterParameters($_GET);

        $arSqlBrands = $arParsedFilterParameters['brand'];
        $minPrice = $arParsedFilterParameters['min-price'];
        $maxPrice = $arParsedFilterParameters['max-price'];
        $minPower = $arParsedFilterParameters['min-power'];
        $maxPower = $arParsedFilterParameters['max-power'];
        $minWeight = $arParsedFilterParameters['min-weight'];
        $maxWeight = $arParsedFilterParameters['max-weight'];

        $isValid = isValidParameters([$minPrice, $maxPrice, $minPower, $maxPower, $minWeight, $maxWeight], $arSqlBrands);

        if ($isValid) {
            $sqlBrands = convertBrandsArrayToSqlString($arSqlBrands);

            $whereParameters = '(brand IN ' . $sqlBrands . ') 
                AND (price BETWEEN ' .  $minPrice . ' AND ' . $maxPrice . ') 
                AND (power BETWEEN ' . $minPower . ' AND ' . $maxPower . ') 
                AND (weight BETWEEN ' . $minWeight . ' AND ' . $maxWeight . ')';

            $sqlCatalogProducts = createSqlQueryString('id, model, brand, power, weight, price', 'products', $whereParameters);
            $sqlCountCatalogProducts = createSqlQueryString('COUNT(*)', 'products', $whereParameters);
        } else {
            $arCatalogProducts = [];
        }
    } else {
        $isValid = !$isValid;

        $sqlCatalogProducts = createSqlQueryString('id, model, brand, power, weight, price', 'products');
        $sqlCountCatalogProducts = createSqlQueryString('COUNT(*)', 'products');
    }

    if ($isValid) {
        $resultCatalogProducts = mysqli_query($link, $sqlCatalogProducts);

        $i = 0;
        $p = 0;
        while ($arRow = mysqli_fetch_array($resultCatalogProducts) and $i < $countProductsOnPage * $currentPage) {
            if ($p >= $countProductsOnPage * ($currentPage - 1)) {
                $arCatalogProducts[] = $arRow;
            }
            $i++;
            $p++;
        }

        $resultCountCatalogProducts = mysqli_query($link, $sqlCountCatalogProducts);

        $arCountCatalogProducts = mysqli_fetch_row($resultCountCatalogProducts);

        $countPage = ceil($arCountCatalogProducts[0] / $countProductsOnPage);
    }
}
?>
<!DOCTYPE html>
<html lang='ru'>

<head>
    <meta charset='UTF-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
    <link rel='stylesheet' href='./index.css' />
    <title>Каталог</title>
</head>

<body class='page'>
    <div class='page__catalog'>
        <div class='page__products'>
            <?php if (count($arCatalogProducts) > 0): ?>
                <?php foreach ($arCatalogProducts as $product) : ?>
                    <div class='card'>
                        <img src='./img/product.png' alt='powerbank' class='card__img' />
                        <h1 class='card__name'>
                            <?= $product['model'] ?>
                        </h1>
                        <div class='card__info'>
                            <h2 class='card__attribute'>
                                Бренд:
                            </h2>
                            <p class='card__description'>
                                <?= $product['brand'] ?>
                            </p>
                        </div>
                        <div class='card__info'>
                            <h2 class='card__attribute'>
                                Мощность:
                            </h2>
                            <p class='card__description'>
                                <?= $product['power'] . ' мАч' ?>
                            </p>
                        </div>
                        <div class='card__info'>
                            <h2 class='card__attribute'>
                                Масса:
                            </h2>
                            <p class='card__description'>
                                <?= $product['weight'] . ' кг' ?>
                            </p>
                        </div>
                        <div class='card__info'>
                            <h2 class='card__attribute'>
                                Цена:
                            </h2>
                            <p class='card__description'>
                                <?= $product['price'] . ' ₽' ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class='page__nothing'>Ничего не найдено</p>
            <?php endif; ?>
        </div>

        <div class='page__filter'>
            <form class='filter' method='get'>
                <h1 class='filter__title'>Фильтр</h1>

                <h2 class='filter__key'>Бренд:</h2>
                <select name='brand[]' multiple>
                    <?php foreach ($arBrandValues as $brand) : ?>
                        <?php if (isset($_GET['brand']) and in_array($brand, $_GET['brand'])): ?>
                            <option value='<?= $brand ?>' selected><?= $brand ?></option>
                        <?php else: ?>
                            <option value='<?= $brand ?>'><?= $brand ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <h2 class='filter__key'>Цена: ( ₽ )</h2>
                <div class='filter__value'>
                    <input class='filter__input' type='number' name='min-price' placeholder='От' value='<?= isset($_GET['min-price']) ? $_GET['min-price'] : '' ?>' />
                    <input class='filter__input' type='number' name='max-price' placeholder='До' value='<?= isset($_GET['max-price']) ? $_GET['max-price'] : ''  ?>' />
                </div>

                <h2 class='filter__key'>Мощность: ( мАч )</h2>
                <div class='filter__value'>
                    <input class='filter__input' type='number' name='min-power' placeholder='От' value='<?= isset($_GET['min-power']) ? $_GET['min-power'] : ''  ?>' />
                    <input class='filter__input' type='number' name='max-power' placeholder='До' value='<?= isset($_GET['max-power']) ? $_GET['max-power'] : '' ?>' />
                </div>

                <h2 class='filter__key'>Масса: ( кг )</h2>
                <div class='filter__value'>
                    <input class='filter__input' type='number' name='min-weight' placeholder='От' value='<?= isset($_GET['min-weight']) ? $_GET['min-weight'] : '' ?>' />
                    <input class='filter__input' type='number' name='max-weight' placeholder='До' value='<?= isset($_GET['max-weight']) ? $_GET['max-weight'] : '' ?>' />
                </div>

                <button class='filter__button' type='submit' name='show'>Показать</button>

                <a href='index.php' class='filter__link' type='submit' name='clear'>Очистить</a>
            </form>
        </div>
    </div>

    <div class='page__settings'>
        <form class='nav' method='get'>
            <?php if (intval($currentPage) > 1): ?>
                <button class='nav__button' type='submit' name='cur-page' value='<?= intval($currentPage) - 1 ?>'>Назад</button>
            <?php endif; ?>

            <?php $i = 1; ?>
            <?php while ($i <= intval($countPage)): ?>
                <?php if (((($i - 1) === 1) and (intval($currentPage) >= 5))): ?>
                    <button class='nav__number' type='button' name='button' value='button' disabled>. . .</button>
                    <?php $i = intval($currentPage) - 3; ?>
                <?php elseif ((($i - 3) === intval($currentPage)) and (intval($currentPage) <= (intval($countPage) - 4))): ?>
                    <button class='nav__number' type='button' name='button' value='button' disabled>. . .</button>
                    <?php $i = intval($countPage) - 1; ?>
                <?php else: ?>
                    <?php if ($i === intval($currentPage)): ?>
                        <button class='nav__number nav__number_type_current' type='submit' name='cur-page' value='<?= $i ?>'><?= $i ?></button>
                    <?php else: ?>
                        <button class='nav__number' type='submit' name='cur-page' value='<?= $i ?>'><?= $i ?></button>
                    <?php endif; ?>
                <?php endif; ?>
                <?php $i++; ?>
            <?php endwhile; ?>

            <?php if ($currentPage < $countPage): ?>
                <button class='nav__button' type='submit' name='cur-page' value='<?= intval($currentPage) + 1 ?>'>Вперёд</button>
            <?php endif; ?>

            <?php if ($_GET): ?>
                <?php foreach ($_GET as $key => $value): ?>
                    <?php if (!in_array($key, ['cur-page', 'change'])): ?>
                        <?php if (is_array($value)): ?>
                            <input type='hidden' name='<?= $key ?>' value='<?= json_encode($value) ?>' />
                        <?php else: ?>
                            <input type='hidden' name='<?= $key ?>' value='<?= $value ?>' />
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </form>

        <form class='count' method='get'>
            <h2 class='count__title'>Количество товаров на странице:</h2>

            <?php foreach (['3', '7', '11'] as $num): ?>
                <?php if ($num === strval($countProductsOnPage)): ?>
                    <input type='radio' id='<?= $num ?>' name='number' value='<?= $num ?>' checked />
                <?php else: ?>
                    <input type='radio' id='<?= $num ?>' name='number' value='<?= $num ?>' />
                <?php endif; ?>
                <label for='<?= $num ?>'><?= $num ?></label>
            <?php endforeach; ?>

            <button class='count__button' type='submit' name='change'>Изменить</button>

            <?php if ($_GET): ?>
                <?php foreach ($_GET as $key => $value): ?>
                    <?php if (!in_array($key, ['number', 'change', 'cur-page'])): ?>
                        <?php if (is_array($value)): ?>
                            <input type='hidden' name='<?= $key ?>' value='<?= json_encode($value) ?>' />
                        <?php else: ?>
                            <input type='hidden' name='<?= $key ?>' value='<?= $value ?>' />
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>