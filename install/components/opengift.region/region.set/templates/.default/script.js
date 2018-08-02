function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
$(function () {
    var currentRegion = false;
    $('.js-district, .js-region').click(function () {
        var $t = $(this).find('a'),
            $districts = $('.js-district'),
            $regions = $('.js-region'),
            $city = $('.js-city');

        if ($t.data('regionId')) {
            currentRegion = $t.data('regionId');
            $city.hide().find('[data-region-id=' + $t.data('regionId') + ']').closest('li').show();
            $regions.removeClass('active').find('[data-region-id=' + $t.data('regionId') + ']')
                .closest('li').addClass('active');
        } else {
            $districts.filter('.active').removeClass('active').end()
                .find('[data-district-id=' + $t.data('districtId') + ']').closest('li').addClass('active');
            $regions.hide().find('[data-district-id=' + $t.data('districtId') + ']').closest('li').show();
        }
    });


    $('[data-role="search-city"]').keyup(function () {
        var $t = $(this), $un = $('.js-unsearcheble'), $cityList = $('.js-city');
        if ($t.val()) {
            $un.hide();
            $cityList.hide().filter(':contains('+capitalizeFirstLetter($t.val())+')').show();
        } else {
            $cityList.hide();
            if (currentRegion)
                $cityList.find('[data-region-id='+currentRegion+']').closest('li').show();
            $un.show();
        }
    });

    $('.js-city a').click(function () {
        $.post(
            '/local/modules/opengift.region/ajax/',
            {
                'sessid': BX.bitrix_sessid(),
                'action': 'regionSet',
                'city': $(this).data('cityId')
            },
            function (data) {
                location.reload();
            }
        )
    })
});