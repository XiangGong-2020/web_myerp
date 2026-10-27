var api = frameElement.api;
var year = api.data.year;
var month = api.data.month;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        Public.ajaxPost('/Base/SalesTarget/queryMonth', {
            year: year,
            month: month
        }, function(res) {
            if (200 == res.status) {
                rowData = res.data;
                THISPAGE.initBtns();
                THISPAGE.initDom();
                THISPAGE.initEvent();
                THISPAGE.initValidator();
            } else {
                $.dialog.tips('获取数据失败！' + res.msg, 2);
            }
        });
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '保存',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(1), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        monthCombo = $('#month').combo({
            data: [
                {id: 0, name: ''},
                {id: 1, name: '1月'},
                {id: 2, name: '2月'},
                {id: 3, name: '3月'},
                {id: 4, name: '4月'},
                {id: 5, name: '5月'},
                {id: 6, name: '6月'},
                {id: 7, name: '7月'},
                {id: 8, name: '8月'},
                {id: 9, name: '9月'},
                {id: 10, name: '10月'},
                {id: 11, name: '11月'},
                {id: 12, name: '12月'}
            ],
            value: 'id',
            text: 'name',
            listHeight: 500,
            defaultSelected: rowData ? ['id', Number(rowData.month)] : 0,
            callback: {
                onListClick: function() {
                    month = monthCombo.getValue();
                    Public.ajaxPost('/Base/SalesTarget/queryMonth', {
                        year: year,
                        month: month
                    }, function(res) {
                        if (200 == res.status) {
                            rowData = res.data;
                            THISPAGE.initDom();
                        } else {
                            $.dialog.tips('获取数据失败！' + res.msg, 2);
                        }
                    });

                }
            }
        }).getCombo();
        $('#month').find('input').attr('name', 'monthInput');
        if (rowData) {
            $('#targetAmount').val(rowData.targetAmount);
            var html = '';
            if (rowData.info) {
                $.each(rowData.info, function(k, v) {
                    html += (
                        '<li class="row-item item-pct50" data-id="' + v.id + '" data-salerid="' + v.salerId + '">'
                            + '<div class="label-wrap"><label>' + v.salerName + '</label></div>'
                            + '<div class="ctn-wrap">'
                                + '<input type="number" value="' + v.targetAmount + '" class="ui-input" autocomplete="off" />'
                                + '<span class="unit">元</span>'
                            + '</div>'
                        + '</li>'
                    );
                })
            }
            $('#userCon').html(html);
        } else {
            $('#targetAmount').val('');
        }
    },
    initEvent: function() {
        $('#manage-form').on('submit', function(a) {
            a.preventDefault();
            THISPAGE.postData();
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                monthInput: {required: !0},
                targetAmount: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var data = THISPAGE.getData();
        var title = '制定' + data.year + '年' + data.month + '月月度销售目标';
        Public.ajaxPost('/Base/SalesTarget/makeMonth', data, function(res) {
            if (200 == res.status) {
                parent.parent.$.dialog.tips(title + '成功！');
                if (callback && 'function' == typeof callback) {
                    callback(res.data, 'add', window, callbackType);
                }
            } else {
                $.dialog.tips(title + '失败！' + res.msg, 2);
            }
        });
    },
    getData: function() {
        var info = [];
        var users = $('#userCon').children('li');
        var user = {};
        for (var i = 0; i < users.length; i++) {
            user = {
                id: users.eq(i).data('id'),
                salerId: users.eq(i).data('salerid'),
                salerName: users.eq(i).find('label').html(),
                targetAmount: Number(users.eq(i).find('input').val()).toFixed(2)
            };
            info.push(user);
        }
        var data = {
            id: rowData ? rowData.id : 0,
            year: rowData ? rowData.year : year,
            month: monthCombo.getValue(),
            targetAmount: Number($.trim($('#targetAmount').val())).toFixed(2),
            info: info
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
