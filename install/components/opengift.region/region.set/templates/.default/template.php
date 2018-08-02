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
<p><?=$arResult['CURRENT_REGION']['name']?>
    <a href="#" class="js-city-select"
   onclick="$('.js-city-select').toggle();return false;">Выбрать город</a>
<div class="js-city-select select-city-modal modal out" tabindex="-1" role="dialog" style="padding-left: 0px;"></p>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <button type="button" class="btn btn-default modal-close-btn" data-dismiss="modal" aria-hidden="true">
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
                                <input data-role="search-city" class="form-control" placeholder="Название города" type="text">
                                <i class="search"></i>
                                <i class="geolocation"></i>
                                <div class="geolocation-title">
                                    <span>Автоопределение выключено</span>
                                </div>
                                <div class="city-input-hint show-hint">
                                    <?
                                    $randCity = $arResult['LIST_RAW'][rand(0, count($arResult['LIST_RAW'])-1)];
                                    ?>
                                    <span>Например:&nbsp;</span><a href="javascript:" onclick="changeCity('<?=$randCity['id']?>')"
                                                                   rel="nofollow noopener"><?=$randCity['name']?></a>
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
                            <a data-city-id="<?=$city['id']?>" href="#" rel="nofollow noopener">
                                <span><?=$city['name']?></span>
                                <i class="icon-arrow icon-right"></i>
                            </a>
                        </li>
                        <?endfor;?>
                    </ul>

                    <ul class="districts js-unsearcheble">
                        <li class="block-title">Федеральный округ</li>
                        <?
                        $i=0;
                        foreach ($arResult['LIST'] as $district => $arRegions):
                            $i++;?>
                            <li class="modal-row js-district <?=$i==1 ? 'active': ''?>">
                                <a data-district-id="<?=md5($district)?>" href="#" rel="nofollow noopener">
                                    <span><?=$district?></span>
                                    <i class="icon-arrow icon-right"></i>
                                </a>
                            </li>
                        <?endforeach;?>
                    </ul>

                    <ul class="regions js-unsearcheble">
                        <li class="block-title" style="display: list-item;">Регион</li>
                        <?$i=0;foreach ($arResult['LIST'] as $district => $arRegions):$i++;
                            foreach ($arRegions as $region => $arCities):?>
                            <li class="modal-row js-region" style="display: <?=$i == 1 ?'list-item':'none'?>;">
                                <a data-district-id="<?=md5($district)?>" data-region-id="<?=md5($region)?>" href="#" rel="nofollow noopener">
                                    <span><?=$region?></span>
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
                        <?$i=0;
                        foreach ($arResult['LIST'] as $district => $arRegions):$i++;
                            foreach ($arRegions as $region => $arCities):
                                foreach ($arCities as $arCity):?>
                                <li class="modal-row js-city" style="display: <?=$i == 1 ?'list-item':'none'?>;">
                                    <a data-district-id="<?=md5($district)?>" data-region-id="<?=md5($region)?>"
                                       data-city-id="<?=$arCity['id']?>" href="#" rel="nofollow noopener">
                                        <span><?=$arCity['name']?></span>
                                        <i class="icon-arrow icon-right"></i>
                                    </a>
                                </li>
                        <?
                                endforeach;
                            endforeach;
                        endforeach;?>
                    </ul>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('.select-city-modal').appendTo('body');
    })
</script>