var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uName: '',
    isCheck: urlParam.isCheck || -1,
    beginDate: Public.reduceMonths((new Date).format('yyyy-MM-01'), 2),
    endDate: Public.addMonths((new Date).format('yyyy-MM-31'), 2),
    skey: '',
    omit: 0
};
var usercombo = $('#usercombo').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有员工'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').parent().find('.current').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: [
        {id: -1, name: '所有审批'},
        {id: 0, name: '未审批'},
        {id: 1, name: '已批准'},
        {id: 2, name: '已拒绝'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.isCheck ? ['id', parseInt(urlParam.isCheck)] : 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').parent().find('.current').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-01'), 2));
        $('#endDate').val(Public.addMonths((new Date).format('yyyy-MM-31'), 2));
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Clockin/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '姓名',
                width: 54,
                fixed: !0
            }, {
                name: 'clockDate',
                label: '日期',
                width: 80,
                fixed: !0
            }, {
                name: 'weekday',
                label: '星期',
                width: 42,
                fixed: !0
            }, {
                name: 'amClock',
                label: '上班',
                width: 66,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = a == '00:00:00' ? '未打卡' : a;
                    var e = a == '00:00:00' ? (c.pmClock == '00:00:00' ? 'silver' : 'blue') : (a >= '08:01:00' ? ((c.marks.includes('值班') && a < '09:01:00') ? 'green' : 'red') : 'green');
                    return '<span class="def ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'pmClock',
                label: '下班',
                width: 66,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = a == '00:00:00' ? '未打卡' : a;
                    var e = a == '00:00:00' ? (c.amClock == '00:00:00' ? 'silver' : 'blue') : (a < '18:00:00' ? ((c.marks.includes('值班') && a >= '16:40:00') ? 'green' : 'red') : 'green');
                    return '<span class="def ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'amClock2',
                label: '二次上班',
                width: 66,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = a == '00:00:00' ? '未打卡' : a;
                    var e = a == '00:00:00' ? (c.pmClock2 == '00:00:00' ? 'silver' : 'blue') : 'green';
                    return '<span class="def ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'pmClock2',
                label: '二次下班',
                width: 66,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = a == '00:00:00' ? '未打卡' : a;
                    var e = a == '00:00:00' ? (c.amClock2 == '00:00:00' ? 'silver' : 'blue') : (a < '18:00:00' ? 'red' : 'green');
                    return '<span class="def ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'amClock3',
                label: '三次上班',
                width: 66,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = a == '00:00:00' ? '未打卡' : a;
                    var e = a == '00:00:00' ? (c.pmClock3 == '00:00:00' ? 'silver' : 'blue') : 'green';
                    return '<span class="def ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'pmClock3',
                label: '三次下班',
                width: 66,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = a == '00:00:00' ? '未打卡' : a;
                    var e = a == '00:00:00' ? (c.amClock3 == '00:00:00' ? 'silver' : 'blue') : (a < '18:00:00' ? 'red' : 'green');
                    return '<span class="def ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'marks',
                label: '标记与申请',
                width: 100,
                fixed: !0,
                title: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'status',
                label: '特殊状态',
                width: 80,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return a == 1 ? '断网应急打卡' : '';
                }
            }, {
                name: 'isCheck',
                label: '审批信息',
                width: 104,
                fixed: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    if (c.marks.search('请假') > -1 || c.marks.search('调休') > -1 || c.marks.search('补休') > -1 || c.marks.search('外勤') > -1) {
                        if (a == 0) {
                            return '<span class="check-btn fl pl10 pr10 tc h28 lh28 b-r-3 hover-teal" data-ischeck="'
                                + a
                                + '" data-id="'
                                + c.id
                                + '">批准</span>'
                                + '<span class="uncheck-btn fl ml5 pl10 pr10 tc h28 lh28 b-r-3 hover-red" data-ischeck="'
                                + a
                                + '" data-id="'
                                + c.id
                                + '">拒绝</span>';
                        } else if (a == 1) {
                            return '<span class="recheck-btn fl pl10 pr10 tc h28 lh28 b-r-3 hover-green" title="' + c.checkName + ' 于 ' + c.checkTime + ' 批准<br>点击取消批准" data-ischeck="' + a + '" data-id="' + c.id + '">已批准</span>';
                        } else if (a == 2) {
                            return '<span class="recheck-btn fl pl10 pr10 tc h28 lh28 b-r-3 hover-night" title="' + c.checkName + ' 于 ' + c.checkTime + ' 拒绝<br>点击取消拒绝" data-ischeck="' + a + '" data-id="' + c.id + '">已拒绝</span>';
                        }
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'addressSearch',
                label: '打卡地址',
                width: 150,
                fixed: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    if (c.latitude > 0 && c.longitude > 0) {
                        return '<span class="addressSearch-btn db fl pl10 pr10 h28 lh28 tc hover-white b-r-3" data-id="' + c.id + '">查验</span><span id="address" class="fl ml10 es" style="width:calc(100% - 60px)"></span>';
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'latitude',
                label: '纬度',
                width: 0,
                hidden: !0,
            }, {
                name: 'longitude',
                label: '经度',
                width: 0,
                hidden: !0,
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                $('#page_left').html('<span class="ml10 dark">' + data.data.countInfo + '</span>');
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(a) {
                $('#' + a).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click').addClass('current').siblings().removeClass('current');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.uName = usercombo.getValue() == -1 ? '' : usercombo.getText();
            queryConditions.isCheck = combo2.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#today').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM-dd'));
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#yesterday').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceDays((new Date).format('yyyy-MM-dd'), 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM-dd'), 1));
            $('#search').trigger('click');
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM-01'));
            $('#endDate').val(Public.reduceDays(Public.addMonths((new Date).format('yyyy-MM-01'), 1), 1));
            $('#search').trigger('click');
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-01'), 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM-01'), 1));
            $('#search').trigger('click');
        });
        $('#omit').on('click', function() {
            queryConditions.uName = usercombo.getValue() == -1 ? '' : usercombo.getText();
            queryConditions.isCheck = combo2.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            if ($(this).hasClass('current')) {
                queryConditions.omit = 0;
                $(this).removeClass('current');
            } else {
                queryConditions.omit = 1;
                $(this).addClass('current');
            }
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_ADD')) {
                $.dialog({
                    title: '新增考勤打卡',
                    content: 'url:/Uri/clockin_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 490,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#grid').on('click', '.check-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_CHECK')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && Public.ajaxPost('/Base/Clockin/check', {
                    id: id,
                    isCheck: 1,
                    uName: rowData.uName,
                    clockDate: rowData.clockDate,
                    marks: rowData.marks
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('批准成功！');
                        rowData.isCheck = 1;
                        rowData.checkName = defaultPage.SYSTEM.uName;
                        rowData.checkTime = (new Date).format('yyyy-MM-dd hh:mm:ss');
                        $('#grid').jqGrid('setRowData', id, rowData);
                    } else {
                        $.dialog.tips('批准失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.uncheck-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_CHECK')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && Public.ajaxPost('/Base/Clockin/check', {
                    id: id,
                    isCheck: 2,
                    uName: rowData.uName,
                    clockDate: rowData.clockDate,
                    marks: rowData.marks
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('拒绝成功！');
                        rowData.isCheck = 2;
                        rowData.checkName = defaultPage.SYSTEM.uName;
                        rowData.checkTime = (new Date).format('yyyy-MM-dd hh:mm:ss');
                        $('#grid').jqGrid('setRowData', id, rowData);
                    } else {
                        $.dialog.tips('拒绝失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.recheck-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_CHECK')) {
                var id = $(this).data('id');
                var isCheck = Number($(this).data('ischeck'));
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && Public.ajaxPost('/Base/Clockin/check', {
                    id: id,
                    isCheck: 0,
                    uName: rowData.uName,
                    clockDate: rowData.clockDate,
                    marks: rowData.marks
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((isCheck == 1 ? '取消批准' : '取消拒绝') + '成功！');
                        rowData.isCheck = 0;
                        rowData.checkName = '';
                        rowData.checkTime = '';
                        $('#grid').jqGrid('setRowData', id, rowData);
                    } else {
                        $.dialog.tips((isCheck == 1 ? '取消批准' : '取消拒绝') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.addressSearch-btn', function(a) {
            var _this = $(this);
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_CHECK')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && Public.ajaxPost('/Base/Clockin/addressSearch', {
                    latitude: rowData.latitude,
                    longitude: rowData.longitude
                }, function(res) {
                    if (res && 200 == res.status) {
                        _this.next().html(res.data.title + '，经度：' + res.data.location.lng + '，纬度：' + res.data.location.lat).attr('title', res.data.title + "\r\n经度：" + res.data.location.lng + "\r\n纬度：" + res.data.location.lat);
                    } else {
                        $.dialog.tips('查验失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_UPDATE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑考勤打卡',
                    content: 'url:/Uri/clockin_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 490,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_DELETE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Clockin/delete', {
                        id: id,
                        uName: rowData.uName,
                        clockDate: rowData.clockDate
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $('#grid').jqGrid('delRowData', id);
                            $.dialog.tips('删除成功！');
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#beginDate, #endDate').blur();
                $('#search').parent().find('.current').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function(a, b, c, d) {
        var e = $('#grid').data('gridData');
        e || (e = {}, $('#grid').data('gridData', e));
        e[a.id] = a;
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : ($('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
