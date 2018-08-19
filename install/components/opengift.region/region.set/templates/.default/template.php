<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

?>
<div class="opengift-region">
<a href="#" class="city-select js-city-select-link"
   onclick="$('.js-city-select').toggle();return false;"><?= $arResult['CURRENT_REGION']['name'] ?></a>
<?if ($arResult['CURRENT_FILIAL']):?>
    <div class="current-filial">
        <?
        $phoneProp = $arParams['FILIAL_PHONE_PROPERTY'];
        if ($phoneProp) {
            if ($arResult['CURRENT_FILIAL']['PROPERTIES'][$phoneProp]['VALUE']) {
                $phone = $arResult['CURRENT_FILIAL']['PROPERTIES'][$phoneProp]['VALUE'];
                ?>
                <a class="current-filial--phone" href="tel:<?=$phone?>"><?=$phone?></a>
                <?
            }
        }
        $addressProp = $arParams['FILIAL_ADDRESS_PROPERTY'];
        if ($addressProp) {
            if ($arResult['CURRENT_FILIAL']['PROPERTIES'][$addressProp]['VALUE']) {
                $address = $arResult['CURRENT_FILIAL']['PROPERTIES'][$addressProp]['VALUE'];
                ?>
                <div class="current-filial--adddress"><?=$address?></div>
                <?
            }
        }
        $emailProp = $arParams['FILIAL_EMAIL_PROPERTY'];
        $email = $emailProp && $arResult['CURRENT_FILIAL']['PROPERTIES'][$emailProp]['VALUE'] ? $arResult['CURRENT_FILIAL']['PROPERTIES'][$emailProp]['VALUE'] : $arParams['DEFAULT_EMAIL'];
        if ($email) {
            ?>
            <a class="current-filial--email" href="mailto:<?=$email?>"><?=$email?></a>
            <?
        }
        ?>
    </div>
<?endif;?>
</div>
<div class="js-city-select select-city-modal modal out" tabindex="-1" role="dialog" style="padding-left: 0px;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <button type="button" onclick="$('.js-city-select').toggle();return false;" class="btn btn-default modal-close-btn" data-dismiss="modal" aria-hidden="true">
                ×
            </button>
            <div class="modal-body">
                <div class="select-lists state-region-select">
                    <div class="modal-cities-header">
                        <div class="modal-row select-labels">
                            <a href="#" data-role="back-select" rel="nofollow noopener">
                                <i class="icon-arrow icon-left"></i>
                                <div class="lbl-select lbl-select-big-cities">Крупные города</div>
                                <div class="lbl-select lbl-select-district">Федеральный округ</div>
                                <div class="lbl-select lbl-select-region">Регион</div>
                                <div class="lbl-select lbl-select-city">Город</div>
                            </a>
                        </div>

                        <div class="modal-row search-field-row">
                            <div class="search-field">
                                <input data-role="search-city" class="form-control" placeholder="Название города"
                                       type="text">
                                <i class="geolocation"></i>
                                <div class="geolocation-title">
                                    <span>Автоопределение выключено</span>
                                </div>
                                <div class="city-input-hint show-hint">
                                    <?
                                    $randCity = $arResult['LIST_RAW'][rand(0, count($arResult['LIST_RAW']) - 1)];
                                    ?>
                                    <span>Например:&nbsp;</span><a href="javascript:"
                                                                   onclick="changeCity('<?= $randCity['id'] ?>')"
                                                                   rel="nofollow noopener"><?= $randCity['name'] ?></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <div data-role="city-not-found" style="display: none; margin-top: 1em;" class="alert alert-danger">
                        Город не найден
                    </div>

                    <ul class="big-cities js-unsearcheble">
                        <li class="block-title">Крупные города</li>
                        <?
                        for ($i = 0; $i <= 6; $i++):
                            $city = $arResult['LIST_RAW'][$i];
                            ?>
                            <li class="modal-row">
                                <a data-city-id="<?= $city['id'] ?>" href="#" rel="nofollow noopener">
                                    <span><?= $city['name'] ?></span>
                                    <i class="icon-arrow icon-right"></i>
                                </a>
                            </li>
                        <? endfor; ?>
                    </ul>

                    <ul class="districts js-unsearcheble">
                        <li class="block-title">Федеральный округ</li>
                        <?
                        $i = 0;
                        foreach ($arResult['LIST'] as $district => $arRegions):
                            $i++; ?>
                            <li class="modal-row js-district <?= $i == 1 ? 'active' : '' ?>">
                                <a data-district-id="<?= md5($district) ?>" href="#" rel="nofollow noopener">
                                    <span><?= $district ?></span>
                                    <i class="icon-arrow icon-right"></i>
                                </a>
                            </li>
                        <? endforeach; ?>
                    </ul>

                    <ul class="regions js-unsearcheble">
                        <li class="block-title" style="display: list-item;">Регион</li>
                        <? $i = 0;
                        foreach ($arResult['LIST'] as $district => $arRegions):$i++;
                            foreach ($arRegions as $region => $arCities):?>
                                <li class="modal-row js-region <?= $i == 1 ? 'active' : '' ?>" style="display: <?= $i == 1 ? 'list-item' : 'none' ?>;">
                                    <a data-district-id="<?= md5($district) ?>" data-region-id="<?= md5($region) ?>"
                                       href="#" rel="nofollow noopener">
                                        <span><?= $region ?></span>
                                        <i class="icon-arrow icon-right"></i>
                                    </a>
                                </li>
                                <?
                            endforeach;
                        endforeach;
                        ?>
                    </ul>

                    <ul class="cities">
                        <li class="block-title" style="display: list-item;">Город</li>
                        <? $i = 0;
                        foreach ($arResult['LIST'] as $district => $arRegions):
                            foreach ($arRegions as $region => $arCities):$i++;
                                foreach ($arCities as $arCity):?>
                                    <li class="modal-row js-city"
                                        style="display: <?= $i == 1 ? 'list-item' : 'none' ?>;">
                                        <a data-district-id="<?= md5($district) ?>" data-region-id="<?= md5($region) ?>"
                                           data-city-id="<?= $arCity['id'] ?>" href="#" rel="nofollow noopener">
                                            <span><?= $arCity['name'] ?></span>
                                            <i class="icon-arrow icon-right"></i>
                                        </a>
                                    </li>
                                    <?
                                endforeach;
                            endforeach;
                        endforeach; ?>
                    </ul>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('.select-city-modal').appendTo('body');
    })
</script>