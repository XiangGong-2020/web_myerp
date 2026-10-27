var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var queryConditions = {
    uName: $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) == -1 ? defaultPage.SYSTEM.uName : '',
    isDisable: urlParam.isDisable || -1,
    beginDate: '',
    endDate: '',
    skey: ''
};
var userCombo = $('#userCombo').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有采购人'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) == -1 ? ['name', defaultPage.SYSTEM.uName] : 0,
    width: 90,
    listHeight: 620,
    trigger: $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) == -1 ? !1 : !0,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var isDisableCombo = $('#isDisableCombo').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '未操作'},
        {id: 1, name: '已同意'},
        {id: 2, name: '已拒绝'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.isDisable ? ['id', parseInt(urlParam.isDisable)] : 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/InvPc/modifyPurAmountList',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">特采单号</span>',
                width: 160,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ti10 ui-ellipsis hover'
            }, {
                name: 'zid',
                label: '自编序号',
                width: 80,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'goods',
                label: '产品信息',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return c.name + (c.spec ? ' ' + c.spec : '') + (c.sku ? ' ' + c.sku : '') + (c.brand ? ' ' + c.brand : '') + ' × ' + c.qty + (c.unit ? ' ' + c.unit : '');
                }
            }, {
                name: 'uName',
                label: '采购人',
                width: 56,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'oldAmount',
                label: '原采购总价',
                width: 80,
                fixed: !0,
                classes: 'ui-ellipsis',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'newAmount',
                label: '修改后采购总价',
                width: 94,
                fixed: !0,
                classes: 'ble',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'modifyName',
                label: '修改人',
                width: 56,
                fixed: !0
            }, {
                name: 'modifyTime',
                label: '修改时间',
                width: 130,
                fixed: !0,
                sortable: !0
            }, {
                name: 'modifyReason',
                label: '修改原因',
                width: 150,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'isRePush',
                label: '修改人操作',
                width: 68,
                fixed: !0,
                align: 'center',
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    if (c.isDisable == 2 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) !== -1) {
                        var btns = '<span class="rePush-btn fl mr10 pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击再次推送给采购人<br>或点击单号去重新改价" data-id="' + c.id + '">再次推送</span>';
                        return btns;
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'disReason',
                label: '采购人异议',
                width: 150,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis ble'
            }, {
                name: 'isDisable',
                label: '采购人操作',
                width: 90,
                fixed: !0,
                align: 'center',
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    var btns = '';
                    if (c.isDisable == 0) {
                        btns = '<span class="agree-btn fl pl5 pr5 tc h28 lh28 b-r-3 hover-teal" title="点击同意" data-id="' + c.id + '">同意</span><span class="disagree-btn fl ml5 pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击拒绝并提出异议" data-id="' + c.id + '">拒绝</span>';
                    } else if (c.isDisable == 1) {
                        btns = '<span class="agreed-btn fl pl5 pr5 tc h28 lh28 b-r-3 hover-green" title="点击取消同意" data-id="' + c.id + '">已同意</span>';
                    } else if (c.isDisable == 2) {
                        btns = '<span class="disagreed-btn fl pl5 pr5 tc h28 lh28 b-r-3 hover-black" title="点击取消拒绝并撤回异议" data-id="' + c.id + '">已拒绝</span>';
                    }
                    return btns;
                }
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'iid',
                label: 'ID',
                width: 0,
                hidden: !0,
                editable: !1
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            multiselect: !1,
            multiboxonly: !0,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'modifyTime',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(c, d) {
                var rowData = $('#grid').jqGrid('getRowData', c);
                if (d == 0) {
                    if (Business.verifyRight('PC_QUERY')) {
                        var billNo = rowData['billNo'];
                        billNo && parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContractList',
                            text: '<i class="iconfont icon-danju f14 mr5"></i>特殊采购',
                            url: '/Uri/purchaseContract_list?skey=' + billNo
                        });
                    }
                }
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.uName = userCombo.getValue() == -1 ? '' : userCombo.getText();
            queryConditions.isDisable = isDisableCombo.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.agree-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    if (rowData.uName == defaultPage.SYSTEM.uName || $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1) { //本人或有采购经理权限
                        Public.ajaxPost('/Bill/InvPc/modifyPurAmountOprate', {
                            id: id,
                            type: 0,
                            isDisable: 1,
                            disReason: ''
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('操作成功！');
                                $('#grid').jqGrid('setRowData', id, {id: id, isDisable: 1, disReason: ''});
                            } else {
                                $.dialog.tips('操作失败！' + res.msg, 2);
                            }
                        });
                    } else {
                        $.dialog.tips('不能操作其他采购人的改价通知！', 2);
                    }
                } else {
                    $.dialog.tips('操作失败！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.agreed-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    if (rowData.uName == defaultPage.SYSTEM.uName || $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1) { //本人或有采购经理权限
                        Public.ajaxPost('/Bill/InvPc/modifyPurAmountOprate', {
                            id: id,
                            type: 0,
                            isDisable: 0,
                            disReason: ''
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('操作成功！');
                                $('#grid').jqGrid('setRowData', id, {id: id, isDisable: 0, disReason: ''});
                            } else {
                                $.dialog.tips('操作失败！' + res.msg, 2);
                            }
                        });
                    } else {
                        $.dialog.tips('不能操作其他采购人的改价通知！', 2);
                    }
                } else {
                    $.dialog.tips('操作失败！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.disagree-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    if (rowData.uName == defaultPage.SYSTEM.uName || $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1) { //本人或有采购经理权限
                        $.dialog.prompt('请输入针对本条改价的异议', function(inputValue) {
                            var disReason = $.trim(inputValue);
                            if (disReason) {
                                Public.ajaxPost('/Bill/InvPc/modifyPurAmountOprate', {
                                    id: id,
                                    type: 0,
                                    isDisable: 2,
                                    disReason: disReason
                                }, function(res) {
                                    if (res && 200 == res.status) {
                                        $.dialog.tips('操作成功！');
                                        $('#grid').jqGrid('setRowData', id, {id: id, isDisable: 2, disReason: disReason});
                                    } else {
                                        $.dialog.tips('操作失败！' + res.msg, 2);
                                    }
                                });
                            } else {
                                $.dialog.tips('异议不能为空！', 2);
                            }
                        });
                    } else {
                        $.dialog.tips('不能操作其他采购人的改价通知！', 2);
                    }
                } else {
                    $.dialog.tips('操作失败！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.disagreed-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    if (rowData.uName == defaultPage.SYSTEM.uName || $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1) { //本人或有采购经理权限
                        Public.ajaxPost('/Bill/InvPc/modifyPurAmountOprate', {
                            id: id,
                            type: 0,
                            isDisable: 0,
                            disReason: ''
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('操作成功！');
                                $('#grid').jqGrid('setRowData', id, {id: id, isDisable: 0, disReason: ''});
                            } else {
                                $.dialog.tips('操作失败！' + res.msg, 2);
                            }
                        });
                    } else {
                        $.dialog.tips('不能操作其他采购人的改价通知！', 2);
                    }
                } else {
                    $.dialog.tips('操作失败！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.rePush-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('FINANCE_MANAGER')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    if (rowData.modifyName == defaultPage.SYSTEM.uName || $.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1) { //本人或有管理员权限
                        Public.ajaxPost('/Bill/InvPc/modifyPurAmountOprate', {
                            id: id,
                            type: 1,
                            isDisable: 0,
                            disReason: ''
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('操作成功！');
                                //$('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
                                $('#grid').jqGrid('setRowData', id, {id: id, isRePush: '', isDisable: 0, disReason: ''});
                            } else {
                                $.dialog.tips('操作失败！' + res.msg, 2);
                            }
                        });
                    } else {
                        $.dialog.tips('不能操作其他修改人的改价通知！', 2);
                    }
                } else {
                    $.dialog.tips('操作失败！', 2);
                }
            }
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#search').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    }
};
$(function() {
    THISPAGE.init();
});
