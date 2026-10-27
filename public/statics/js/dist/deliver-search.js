var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var queryConditions = api ? api.data : {
    company: '',
    type: '',
    postid: '',
    phone: ''
};
var deliverCompanyCode100List = {};
var combo1 = $('#combo1').combo({
    data: '/Base/DeliverCompany/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            for (var i = 0; i < a.data.rows.length; i++) {
                deliverCompanyCode100List['' + a.data.rows[i].name] = a.data.rows[i].code100;
            }
            return a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: queryConditions.company ? ['name', queryConditions.company] : -1,
    width: 102,
    listHeight: 440
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        queryConditions.postid && $('#postid').val(queryConditions.postid);
        queryConditions.phone && $('#phone').val(queryConditions.phone);
        if (api) {
            $('.wrapper').css({margin: 0});
        }
    },
    initEvent: function() {
        $('#search').on('click', function(a) {
            a.preventDefault();
            $('.wrapper').append('<div id="div-load"><div class="loading">查询中...</div></div>');
            $('#deliverInfo').html('');
            queryConditions.company = combo1.getText();
            queryConditions.type = deliverCompanyCode100List[queryConditions.company];
            queryConditions.postid = $.trim($('#postid').val());
            queryConditions.phone = $.trim($('#phone').val());
            queryConditions.temp = Math.random();
            if (queryConditions.type != 'shunfeng') {
                queryConditions.phone = '';
            }
            Public.ajaxPost('/Base/Tool/deliverSearch', queryConditions, function(res) {
                $('#div-load').remove();
                if (res && 200 == res.status && res.data) {
                    if (res.data.inData) {
                        var inData = '';
                        if (res.data.inData.scanTime != '0000-00-00 00:00:00') {
                            inData += '<div class="pt10 pb10 lh20 black bbe ovh">' +
                                '<span class="fl w120 h20">' + res.data.inData.scanTime + '</span>' +
                                '<span class="fl h20 ml20" style="width:calc(100% - 140px)">' + res.data.inData.scanName + ' 称重扫码</span>' +
                                '</div>';
                        }
                        if (res.data.inData.packTime != '0000-00-00 00:00:00') {
                            inData += '<div class="pt10 pb10 lh20 black bbe ovh">' +
                                '<span class="fl w120 h20">' + res.data.inData.packTime + '</span>' +
                                '<span class="fl h20 ml20" style="width:calc(100% - 140px)">' + res.data.inData.packName + ' 打包扫码</span>' +
                                '</div>';
                        }
                        if (res.data.inData.printTime != '0000-00-00 00:00:00') {
                            inData += '<div class="pt10 pb10 lh20 black bbe ovh">' +
                                '<span class="fl w120 h20">' + res.data.inData.printTime + '</span>' +
                                '<span class="fl h20 ml20" style="width:calc(100% - 140px)">' + res.data.inData.lastUpdator + ' 打印出单</span>' +
                                '</div>';
                        }
                        $('#inData').html(inData);
                    }
                    if (res.data.outData) {
                        var outData = '';
                        $.each(res.data.outData, function(k, v) {
                            outData += (
                                '<div class="pt10 pb10 lh20 ' + (k > 0 ? 'bte' : '') + ' ovh">' +
                                '<span class="fl ' + (k > 0 ? 'gray' : 'black') + '">' + v.time + '</span>' +
                                '<span class="fl ml20 ' + (k > 0 ? 'gray' : 'black') + '" style="width:calc(100% - 140px)" title="' + (v.context || v['0']) + '">' + (v.context || v['0']) + '</span>' +
                                '</div>');
                        });
                        $('#outData').html(outData);
                    }
                } else {
                    $('#inData').html(res.msg);
                    $('#outData').html(res.msg);
                }
            });
        });
    }
};
$(function() {
    THISPAGE.init();
    if (api) {
        setTimeout(function() {
            api.data && $('#search').trigger('click');
        }, 500);
    }
});
