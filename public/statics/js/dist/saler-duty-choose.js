var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    dutyDate: defaultPage.SYSTEM.endDate
};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#dutyDate').val(queryConditions.dutyDate);
        $('.wrapper').append('<div id="div-lock"><div class="loading">更新中...</div></div>');
        Public.ajaxPost('/Base/SalerDuty/list', queryConditions, function(res) {
            $('.wrapper > #div-lock').remove();
            if (200 == res.status) {
                var html = '';
                $.each(res.data, function(i, v) {
                    html += '<div class="duty-item' + (v.length > 0 ? ' black bg-white' : ' white bg-red') + '" data-dutyname="' + i + '" title="点击选择"><div class="duty-item-header" title="点击选择">' + i + '</div>';
                    if (v.length > 0) {
                        $.each(v, function(j, k) {
                            html += '<span class="duty-item-name white ' + (k.name == defaultPage.SYSTEM.uName ? 'bg-teal' : 'bg-green') + '">' + k.name + (k.name == defaultPage.SYSTEM.uName ? '<i class="duty-item-name-cancel iconfont icon-shanchu" data-id="' + k.id + '" title="放弃"></i>' : '') + '</span>';
                        });
                    } else {
                        html += '<span class="duty-item-name">目前无人选择<br>点击选择</span>';
                    }
                    html += '</div>';
                });
                $('#dutyList').html(html);
                $('#dutyNote').val(res.dutyNote);
            } else {
                $.dialog.tips('获取值班内容失败！' + res.msg, 2);
            }
        });
        $('#uName').text(defaultPage.SYSTEM.uName);
        if (queryConditions.dutyDate == defaultPage.SYSTEM.endDate) {
            $('#dutyNote').attr('disabled', false);
            $('#dutyNote').val();
            $('#dutyNoteBtn').removeClass('dn');
        } else {
            $('#dutyNote').attr('disabled', true);
            $('#dutyNoteBtn').addClass('dn');
        }
    },
    initEvent: function() {
        laydate.render({
            elem: '#dutyDate',
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.dutyDate = $('#dutyDate').val() ? $.trim($('#dutyDate').val()) : '';
            THISPAGE.initDom();
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $(window).on('keydown', function(e) {
            var curKey = e.which;
            if (curKey == 13) {
                $('#dutyDate').blur();
                $('#search').trigger('click');
            }
        });
        $('.wrapper').on('click', '.duty-item', function(a) {
            $('.wrapper').append('<div id="div-lock"><div class="loading">更新中...</div></div>');
            var dutyName = $(this).data('dutyname');
            Public.ajaxPost('/Base/SalerDuty/choose', {
                dutyDate: queryConditions.dutyDate,
                dutyName: dutyName,
                name: defaultPage.SYSTEM.uName
            }, function(res) {
                $('.wrapper > #div-lock').remove();
                if (200 == res.status) {
                    THISPAGE.initDom();
                } else {
                    $.dialog.tips('选择值班内容失败！' + res.msg, 2);
                }
            });
        });
        $('.wrapper').on('click', '.duty-item-name-cancel', function(a) {
            a.stopPropagation();
            a.preventDefault();
            $('.wrapper').append('<div id="div-lock"><div class="loading">更新中...</div></div>');
            var id = $(this).data('id');
            Public.ajaxPost('/Base/SalerDuty/cancel', {
                id: id
            }, function(res) {
                $('.wrapper > #div-lock').remove();
                if (200 == res.status) {
                    THISPAGE.initDom();
                } else {
                    $.dialog.tips('放弃值班内容失败！' + res.msg, 2);
                }
            });
        });
        $('.wrapper').on('click', '#dutyNoteBtn', function(a) {
            a.preventDefault();
            var note = $.trim($('#dutyNote').val());
            if (note != '') {
                $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                Public.ajaxPost('/Base/SalerDuty/saveNote', {
                    dutyDate: queryConditions.dutyDate,
                    name: defaultPage.SYSTEM.uName,
                    note: note
                }, function(res) {
                    $('.wrapper > #div-lock').remove();
                    if (200 == res.status) {
                        THISPAGE.initDom();
                    } else {
                        $.dialog.tips('保存值班记录失败！' + res.msg, 2);
                    }
                });
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
