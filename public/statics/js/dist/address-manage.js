$(function() {
    var api = frameElement.api;
    var a = this;
    var b = api.data.oper;
    var c = api.data.rowData || {};
    var d = api.data.callback;
    var e = api.data.hasDefault;
    var f = !1;
    var _page = {
        $shortName: $('#shortName'),
        $postalcode: $('#postalcode'),
        $province: $('#province'),
        $city: $('#city'),
        $area: $('#area'),
        $address: $('#address'),
        $linkman: $('#linkman'),
        $phone: $('#phone'),
        $mobile: $('#mobile'),
        $isDefault: $('#isDefault'),
        init: function() {
            mod_AreasCombo.init(_page.$province, _page.$city, _page.$area, {listHeight: 155}, function() {
                _page.provinceCombo = mod_AreasCombo.provinceCombo;
                _page.cityCombo = mod_AreasCombo.cityCombo;
                _page.areaCombo = mod_AreasCombo.areaCombo;
                var b = 1 == c.isDefault ? 0 : 1;
                f = c.isDefault ? !0 : !1;
                var d = e ? f : !0;
                _page.isDefaultCombo = _page.$isDefault.combo({
                    data: [{
                        id: 1,
                        name: '是'
                    }, {
                        id: 0,
                        name: '否'
                    }],
                    value: 'id',
                    text: 'name',
                    width: 197,
                    defaultSelected: b || void 0,
                    editable: !1,
                    disabled: !d
                }).getCombo();
                _page.$shortName.val(c.shortName);
                _page.$postalcode.val(c.postalcode);
                _page.provinceCombo.selectByText(c.province);
                _page.cityCombo.selectByText(c.city);
                _page.areaCombo.selectByText(c.area || c.county);
                _page.$address.val(c.address);
                _page.$linkman.val(c.linkman);
                _page.$phone.val(c.phone);
                _page.$mobile.val(c.mobile);
                $('#province').find('input').attr('name', 'provinceInput');
                $('#city').find('input').attr('name', 'cityInput');
                $('#area').find('input').attr('name', 'areaInput');
                a.initButton();
                a.initValidator();
                _page.$shortName.focus().select();
            });
        }
    };
    a.initButton = function() {
        api.button({
            id: 'confirm',
            name: '保存',
            focus: !0,
            callback: function() {
                return a.postData(b, c.id), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    };
    a.initValidator = function() {
        $.validator.addMethod('mobile', function(a) {
            return a ? /0?(13|14|15|16|17|18|19)[0-9]{9}/.test(a) : !0;
        });
        $.validator.addMethod('phone', function(a) {
            return a ? /[0-9-()（）]{7,18}/.test(a) : !0;
        });
        $.validator.addMethod('postalcode', function(a) {
            return a ? /\d{6}/.test(a) : !0;
        });
        $.validator.addMethod('require_from_group', function(a, b, c) {
            var d = this;
            var e = c[1];
            var f = $(e, b.form).filter(function() {
                return d.elementValue(this);
            }).length >= c[0];
            if (!$(b).data('being_validated')) {
                var g = $(e, b.form);
                g.data('being_validated', !0), g.valid(), g.data('being_validated', !1);
            }
            return f;
        }, jQuery.format('请输入电话或者手机号码'));
        $('#manage-form').validate({
            errorPlacement: function(a, b) {
                a.appendTo(b.parent());
                var c = '0';
                var d = '10px';
                a.parent().hasClass('ui-combo-wrap') && (c = '0', d = '25px'), a.css({
                    display: 'block',
                    position: 'absolute',
                    top: c,
                    right: d
                });
            },
            rules: {
                shortName: {},
                address: {required: !0},
                linkman: {required: !0},
                mobile: {require_from_group: [1, '.phone-group']},
                phone: {require_from_group: [1, '.phone-group']},
                postalcode: {required: !0},
                provinceInput: {required: !0}
            }
        });
    };
    a.postData = function(a, b) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var e = $.trim(_page.$shortName.val());
        var f = $.trim(_page.$postalcode.val());
        var g = _page.provinceCombo.getText();
        var h = _page.cityCombo.getText();
        var i = _page.areaCombo.getText();
        var j = $.trim(_page.$address.val());
        var k = $.trim(_page.$linkman.val());
        var l = $.trim(_page.$phone.val());
        var m = $.trim(_page.$mobile.val());
        var n = _page.isDefaultCombo && _page.isDefaultCombo.getValue();
        params = c.id ? {id: b, shortName: e, postalcode: f, province: g, city: h, area: i, address: j, linkman: k, phone: l, mobile: m, isDefault: n}
            : {shortName: e, postalcode: f, province: g, city: h, area: i, address: j, linkman: k, phone: l, mobile: m, isDefault: n};
        d(params, api);
    };
    _page.init();
    window.resetForm = function() {
        $('#manage-form').validate().resetForm();
        _page.$shortName.val('');
        _page.$postalcode.val('');
        _page.provinceCombo.selectByText('');
        _page.cityCombo.selectByText('');
        _page.areaCombo.selectByText('');
        _page.$address.val('');
        _page.$linkman.val('');
        _page.$phone.val('');
        _page.$mobile.val('');
        _page.isDefaultCombo.selectByText('否');
        e && 'add' == b && _page.isDefaultCombo.disable();
    };
});
