var defaultPage = Public.getDefaultPage();
var queryConditions = {
    beginDate: defaultPage.SYSTEM.beginDate,
    endDate: defaultPage.SYSTEM.endDate,
    type: -1,
    us: -1,
    inout: ($.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) === -1 && defaultPage.SYSTEM.user.department == '销售部') ? 0 : (($.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) === -1 && defaultPage.SYSTEM.user.department == '采购部') ? 1 : -1),
    transType: -1,
    status1: -1,
    status2: -1,
    uName: $.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) === -1 ? defaultPage.SYSTEM.uName : '',
    minAmount: '',
    maxAmount: '',
    skey: ''
};
var companyAll = {};
var dateCombo = $('#dateCombo').combo({
    data: [
        {id: 0, name: '所有时段'},
        {id: 1, name: '本月'},
        {id: 2, name: '上月'},
        {id: 3, name: '本季度'},
        {id: 4, name: '上季度'},
        {id: 5, name: '本年'},
        {id: 6, name: '上年'}
    ],
    value: 'id',
    text: 'name',
    width: 102,
    listHeight: 500,
    defaultSelected: 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        /* onChange: function(data) {
            console.log(data);
        }, */
        onListClick: function() {
            switch (this._selectedIndex) {
                case 0:
                    $('#beginDate').val(defaultPage.SYSTEM.beginDate);
                    $('#endDate').val(defaultPage.SYSTEM.endDate);
                break;
                case 1:
                    $('#beginDate').val((new Date).format('yyyy-MM-01'));
                    $('#endDate').val(Public.reduceDays(Public.addMonths((new Date).format('yyyy-MM-01'), 1), 1));
                break;
                case 2:
                    $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-01'), 1));
                    $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM-01'), 1));
                break;
                case 3:
                    var quarter = Math.ceil((new Date().getMonth() + 1) / 3); // 使用ceil函数将月份除以3并向上取整得到本季度
                    if (quarter == 1) {
                        $('#beginDate').val(new Date().getFullYear() + '-01-01');
                        $('#endDate').val(new Date().getFullYear() + '-03-31');
                    } else if (quarter == 2) {
                        $('#beginDate').val(new Date().getFullYear() + '-04-01');
                        $('#endDate').val(new Date().getFullYear() + '-06-30');
                    } else if (quarter == 3) {
                        $('#beginDate').val(new Date().getFullYear() + '-07-01');
                        $('#endDate').val(new Date().getFullYear() + '-09-30');
                    } else {
                        $('#beginDate').val(new Date().getFullYear() + '-10-01');
                        $('#endDate').val(new Date().getFullYear() + '-12-31');
                    }
                break;
                case 4:
                    var quarter = Math.ceil((new Date().getMonth() + 1) / 3); // 使用ceil函数将月份除以3并向上取整得到本季度
                    if (quarter == 1) {
                        $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-10-01'), 12));
                        $('#endDate').val(Public.reduceMonths((new Date).format('yyyy-12-31'), 12));
                    } else if (quarter == 2) {
                        $('#beginDate').val(new Date().getFullYear() + '-01-01');
                        $('#endDate').val(new Date().getFullYear() + '-03-31');
                    } else if (quarter == 3) {
                        $('#beginDate').val(new Date().getFullYear() + '-04-01');
                        $('#endDate').val(new Date().getFullYear() + '-06-30');
                    } else {
                        $('#beginDate').val(new Date().getFullYear() + '-07-01');
                        $('#endDate').val(new Date().getFullYear() + '-09-30');
                    }
                break;
                case 5:
                    $('#beginDate').val((new Date).format('yyyy-01-01'));
                    $('#endDate').val((new Date).format('yyyy-MM-dd'));
                break;
                case 6:
                    $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-01-01'), 12));
                    $('#endDate').val(Public.reduceMonths((new Date).format('yyyy-12-31'), 12));
                break;
            }
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var typeCombo = $('#typeCombo').combo({
    data: [
        {id: -1, name: '所有类别'},
        {id: 0, name: '普通发票'},
        {id: 1, name: '增值税专用发票'},
        {id: 2, name: '火车票'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    listWidth: 100,
    listHeight: 500,
    defaultSelected: 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var usCombo = $('#usCombo').combo({
    data: '/Base/Company/queryAll?isDisable=0',
    ajaxOptions: {
        formatData: function(a) {
            a.data.rows.forEach(element => {
                if (element.id > 0) {
                    var id = '' + element.id;
                    companyAll[id] = element.name;
                }
            });
            THISPAGE.initGrid();
            return a.data.rows.unshift({
                id: -1,
                name: '所有我方主体'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    listWidth: 150,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var inoutCombo = $('#inoutCombo').combo({
    data: [
        {id: -1, name: '所有往来'},
        {id: 0, name: '我方开出'},
        {id: 1, name: '我方收取'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    defaultSelected: ($.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) === -1 && defaultPage.SYSTEM.user.department == '销售部') ? 1 : (($.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) === -1 && defaultPage.SYSTEM.user.department == '采购部') ? 2 : 0),
    disabled: $.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) === -1 ? !0 : !1,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var transTypeCombo = $('#transTypeCombo').combo({
    data: [
        {id: -1, name: '所有业务'},
        {id: 0, name: '未知'},
        {id: 101101, name: '采购订单'},
        {id: 101111, name: '特殊采购单'},
        {id: 101201, name: '销售订单'},
        {id: 101211, name: '特殊销售单'},
        {id: 103103, name: '其他收入单'},
        {id: 103104, name: '其他支出单'},
        {id: 109101, name: '快递月结单'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    listWidth: 150,
    listHeight: 500,
    defaultSelected: 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var billFromCombo = $('#billFromCombo').combo({
    data: '/Base/Billfrom/queryAll?isDisable=0',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有店铺'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    minListWidth: 200,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var status1Combo = $('#status1Combo').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '未验证'},
        {id: 1, name: '验证正常'},
        {id: 2, name: '验证异常'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    defaultSelected: 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var status2Combo = $('#status2Combo').combo({
    data: [
        {id: -1, name: '所有风险'},
        {id: 0, name: '未验证'},
        {id: 1, name: '验证正常'},
        {id: 2, name: '验证异常'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    defaultSelected: 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var userCombo = $('#userCombo').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有录入人'
            }, {
                id: 1,
                name: '系统'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    width: 90,
    listHeight: 500,
    defaultSelected: $.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) === -1 ? ['id', defaultPage.SYSTEM.uId] : 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
        if ($.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) !== -1) {
            $('#tochinatax, #upfile_1, #upfile_2').removeClass('dn');
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Invoice/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'date',
                label: '<span class="ml10">开票日期</span>',
                width: 80,
                fixed: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'code',
                label: '发票代码',
                width: 96,
                fixed: !0,
                sortable: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'no',
                label: '发票号码',
                width: 150,
                fixed: !0,
                sortable: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'amount',
                label: '价税合计',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: function(b, c, d) {
                    return b || '待验证';
                }
            }, {
                name: 'type',
                label: '发票类别',
                width: 80,
                fixed: !0,
                sortable: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(a) {
                    var b = '';
                    switch (a) {
                        case 0:
                            b = '普通发票';
                            break;
                        case 1:
                            b = '增值税专用发票';
                            break;
                        case 2:
                            b = '火车票';
                            break;
                        default:
                            break;
                    }
                    return b;
                }
            }, {
                name: 'us',
                label: '我方主体',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(cellvalue, options, rowObject) {
                    return companyAll[cellvalue];
                }
            }, {
                name: 'otherSide',
                label: '对方主体',
                width: 80,
                fixed: !0,
                sortable: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(b, c, d) {
                    return b || '待验证';
                }
            }, {
                name: 'inoutType',
                label: '往来区分',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a) {
                    var b = '';
                    switch (a) {
                        case 0:
                            b = '<sapn class="gray">我方开出</span>';
                            break;
                        case 1:
                            b = '<sapn class="blue">我方收取</span>';
                            break;
                        default:
                            break;
                    }
                    return b;
                }
            }, {
                name: 'transTypeName',
                label: '业务类别',
                width: 78,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'billNo',
                label: '关联单据',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'billFrom',
                label: '对应店铺',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'status1',
                label: '发票状态',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a == '正常' ? ('<span class="db h30 lh30 tc white bg-green b-r-3 def">' + a + '</span>') : (a == '' ? '<span class="db h30 lh30 tc white bg-yellow b-r-3 def">未验证</span>' : ('<span class="db pl5 pr5 h30 lh30 tc es white bg-red b-r-3 def" title="' + a + '">' + a + '</span>'));
                    return d;
                }
            }, {
                name: 'status2',
                label: '风险等级',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a == '正常' ? ('<span class="db h30 lh30 tc white bg-green b-r-3 def">' + a + '</span>') : (a == '' ? '<span class="db h30 lh30 tc white bg-yellow b-r-3 def">未验证</span>' : ('<span class="db pl5 pr5 h30 lh30 tc es white bg-red b-r-3 def" title="' + a + '">' + a + '</span>'));
                    return d;
                }
            }, {
                name: 'uName',
                label: '录入人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '录入时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'id',
                label: 'id',
                hidden: !0
            }, {
                name: 'billId',
                label: '业务单ID',
                hidden: !0
            }, {
                name: 'transType',
                label: '业务类别ID',
                hidden: !0
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
                if (data && 200 == data.status) {
                    $('#totalOutAmount').html(Public.numToCurrency((data.data.totalOutAmount || 0), 2));
                    $('#totalTaxAmount').html(Public.numToCurrency((data.data.totalTaxAmount || 0), 2));
                    $('#totalInAmount').html(Public.numToCurrency((data.data.totalInAmount || 0), 2));
                    $('#totalReAmount').html(Public.numToCurrency((Number(data.data.totalTaxAmount || 0) - Number(data.data.totalInAmount || 0)), 2));
                } else {
                    data.status == -1 && $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(b) {
                $('#' + b).find('.ui-icon-pencil').trigger('click');
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
            if (Business.verifyRight('INVOICE_COUNT')) {
                queryConditions.beginDate = $('#beginDate').val();
                queryConditions.endDate = $('#endDate').val();
                queryConditions.type = typeCombo.getValue();
                queryConditions.us = usCombo.getValue();
                queryConditions.inout = inoutCombo.getValue();
                queryConditions.transType = transTypeCombo.getValue();
                queryConditions.billFrom = billFromCombo.getValue() == -1 ? '' : billFromCombo.getText();
                queryConditions.status1 = status1Combo.getValue();
                queryConditions.status2 = status2Combo.getValue();
                queryConditions.uName = userCombo.getValue() == -1 ? '' : userCombo.getText();
                queryConditions.minAmount = $('#minAmount').val();
                queryConditions.maxAmount = $('#maxAmount').val();
                queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
                $('#grid').jqGrid('setGridParam', {
                    postData: queryConditions,
                    page: 1
                }).trigger('reloadGrid');
            }
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('INVOICE_ADVANCE')) {
                $.dialog({
                    title: '新增发票记录',
                    content: 'url:/Uri/invoice_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1100,
                    height: 500,
                    min: !1,
                    max: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#upfile_1').uploader({
            /* 服务端脚本 */
            action: '/Base/Invoice/uploadExcel_1',
            /* 上传模式，html5/flash */
            mode: 'html5',
            /* 字段名 */
            name: 'file',
            formData: {},
            /* 超时时间 秒 */
            callee: 300,
            /* 是否多选 */
            multiple: false,
            /* 是否自动上传 */
            auto: true,
            debug: false,
            preview: false,
            /* 显示队列的位置（传递jQuery选择器自定义队列显示的元素，传递true自动生成队列） */
            showQueue: '#queue',
            /* 文件大小限制（'100kb' '5M' 等） */
            fileSizeLimit: '2G',
            /* 可选择的文件的描述，用中竖线分组。此字符串出现在浏览文件对话框的文件类型下拉中 */
            fileTypeDesc: '选择文件',
            /* 允许上传的文件类型类表，用逗号分隔多个扩展，用中竖线分组（eg: 'jpg,jpeg,gif,png | zip,rar'） */
            fileTypeExts: 'xls,xlsx,csv',
            onInit: function() {
            },
            /* 选择文件 */
            onSelected: function(filelist) {
            },
            /* 取消选择文件 */
            onCancel: function(file) {
            },
            /* 开始上传 */
            onStart: function(e) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">自动处理中...</div></div>');
            },
            /* 上传进行中 */
            onProgress: function(e) {
            },
            /* 上传发生错误 */
            onError: function(e) {
                $('.wrapper > #div-lock').remove();
            },
            /* 上传成功 */
            onSuccess: function(e) {
                $('.wrapper > #div-lock').remove();
            },
            /* 单个文件处理完成（error or success） */
            onComplete: function(e) {
                $('.wrapper > #div-lock').remove();
                var data = JSON.parse(e.data);
                if (200 == data.status) {
                    $.dialog.tips(data.msg);
                } else {
                    $.dialog.tips(data.msg, 2);
                }
            },
            /* 全部文件处理完成（error or success） */
            onAllComplete: function() {
                $('#grid').jqGrid('setGridParam', {
                    postData: queryConditions,
                    page: 1
                }).trigger('reloadGrid');
            },
            /* 清空队列 */
            onClearQueue: function() {
            }
        });
        $('#upfile_2').uploader({
            /* 服务端脚本 */
            action: '/Base/Invoice/uploadExcel_2',
            /* 上传模式，html5/flash */
            mode: 'html5',
            /* 字段名 */
            name: 'file',
            formData: {},
            /* 超时时间 秒 */
            callee: 300,
            /* 是否多选 */
            multiple: false,
            /* 是否自动上传 */
            auto: true,
            debug: false,
            preview: false,
            /* 显示队列的位置（传递jQuery选择器自定义队列显示的元素，传递true自动生成队列） */
            showQueue: '#queue',
            /* 文件大小限制（'100kb' '5M' 等） */
            fileSizeLimit: '2G',
            /* 可选择的文件的描述，用中竖线分组。此字符串出现在浏览文件对话框的文件类型下拉中 */
            fileTypeDesc: '选择文件',
            /* 允许上传的文件类型类表，用逗号分隔多个扩展，用中竖线分组（eg: 'jpg,jpeg,gif,png | zip,rar'） */
            fileTypeExts: 'xls,xlsx,csv',
            onInit: function() {
            },
            /* 选择文件 */
            onSelected: function(filelist) {
            },
            /* 取消选择文件 */
            onCancel: function(file) {
            },
            /* 开始上传 */
            onStart: function(e) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">自动处理中...</div></div>');
            },
            /* 上传进行中 */
            onProgress: function(e) {
            },
            /* 上传发生错误 */
            onError: function(e) {
                $('.wrapper > #div-lock').remove();
            },
            /* 上传成功 */
            onSuccess: function(e) {
                $('.wrapper > #div-lock').remove();
            },
            /* 单个文件处理完成（error or success） */
            onComplete: function(e) {
                $('.wrapper > #div-lock').remove();
                var data = JSON.parse(e.data);
                if (200 == data.status) {
                    $.dialog.tips(data.msg);
                } else {
                    $.dialog.tips(data.msg, 2);
                }
            },
            /* 全部文件处理完成（error or success） */
            onAllComplete: function() {
                $('#grid').jqGrid('setGridParam', {
                    postData: queryConditions,
                    page: 1
                }).trigger('reloadGrid');
            },
            /* 清空队列 */
            onClearQueue: function() {
            }
        });
        $('.upload-el').on({
            click: function(a) {
                !Business.verifyRight('INVOICE_ADVANCE') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('ui-btn-hover');
            },
            mouseleave: function() {
                $(this).prev().removeClass('ui-btn-hover');
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('INVOICE_UPDATE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData.uName != '系统' && rowData.uName != defaultPage.SYSTEM.uName && $.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) == -1 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) == -1) {
                    $.dialog.tips('不能编辑他人的发票记录！', 2);
                } else {
                    Public.ajaxPost('/Base/Invoice/query', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog({
                                title: '编辑发票记录',
                                content: 'url:/Uri/invoice_manage',
                                data: {
                                    oper: 'edit',
                                    rowData: res.data,
                                    callback: THISPAGE.callback
                                },
                                width: 1100,
                                height: 500,
                                min: !1,
                                max: !1,
                                cache: !1,
                                lock: !0
                            });
                        } else {
                            $.dialog.tips('获取数据失败！', 2);
                        }
                        return void 0;
                    });
                }
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('INVOICE_DELETE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData.uName != defaultPage.SYSTEM.uName) {
                    $.dialog.tips('不能删除他人的发票记录！', 2);
                } else {
                    $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                        Public.ajaxPost('/Base/Invoice/delete', {
                            id: id
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('删除成功！');
                                $('#grid').jqGrid('delRowData', id);
                            } else {
                                $.dialog.tips('删除失败！' + res.msg, 2);
                            }
                        });
                    });
                }
            }
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#beginDate, #endDate').blur();
                $('#search').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function(a, b, c) {
        var d = $('#grid').data('gridData');
        d || (d = {}, $('#grid').data('gridData', d));
        d[a.id] = a;
        'edit' == b ? ($('#grid').jqGrid().trigger('reloadGrid'), c && c.api.close()) : ($('#grid').jqGrid('addRowData', a.id, a, 'first'), c && c.api.close());
    }
};
$(function() {
    THISPAGE.init();
});
