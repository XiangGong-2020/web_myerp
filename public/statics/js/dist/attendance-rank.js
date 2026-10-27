var defaultPage = Public.getDefaultPage();
var queryConditions = {
    company: '',
    department: '',
    position: '',
    isDisable: 0,
    beginDate: (new Date).format('yyyy-MM') + '-01',
    endDate: (new Date).format('yyyy-MM-dd')
};
var combo1 = $('#combo1').combo({
    data: '/Base/Company/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有主体'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: '/Base/Category/list?typeNumber=departmenttype',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有部门'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onChange: function() {
            if (this._selectedIndex > 0) { // 如果选择了具体部门，才动态加载对应部门的职位
                Public.ajaxGet('/Base/Category/list?typeNumber=positiontype&parentId=' + this.formattedData[this._selectedIndex].value, {}, function(res) {
                    if (res && 200 == res.status) {
                        res.data.rows.unshift({
                            id: -1,
                            name: '所有职位'
                        });
                        combo3.loadData(res.data.rows, -1, false);
                    }
                });
            }
        },
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo3 = $('#combo3').combo({
    data: [],
    value: 'id',
    text: 'name',
    defaultSelected: -1,
    width: 90,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo4 = $('#combo4').combo({
    data: [
        {id: -1, name: '所有员工'},
        {id: 0, name: '在职员工'},
        {id: 1, name: '离职员工'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 1,
    width: 78,
    listHeight: 500,
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
        $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
        $('#endDate').val((new Date).format('yyyy-MM-dd'));
    },
    initGrid: function() {
        var a = Public.setGrid();
        var rowData = {};
        var rank = 0;
        var colModel = [{
            name: '',
            label: '<span class="ml30">排名</span>',
            width: 90,
            fixed: !0,
            align: 'center',
            formatter: function(a, b, c) {
                if (b.rowId == 1) {
                    rank = 1;
                    rowData = c;
                } else {
                    if (!(c.workHours == rowData.workHours && c.workDays == rowData.workDays && c.llTimes == rowData.llTimes && c.clockoffDays == rowData.clockoffDays && c.overtimeHours == rowData.overtimeHours && c.dutyTimes == rowData.dutyTimes && c.isFull == rowData.isFull)) {
                        rowData = c;
                        rank++;
                    }
                }
                if (rank == 1) {
                    return '<i class="iconfont icon-diyiming gold f30"></i>';
                } else if (rank == 2) {
                    return '<i class="iconfont icon-dierming gray f26"></i>';
                } else if (rank == 3) {
                    return '<i class="iconfont icon-disanming copper f22"></i>';
                } else {
                    return rank;
                }
            }
        }, {
            name: 'uName',
            label: '姓名',
            width: 100,
            fixed: !0
        }, {
            name: 'workRate',
            label: '出勤率',
            width: 100,
            fixed: !0,
            formatter: function(a, b, c) {
                var d = (Number(a) >= 100 ? '<sapn class="green fb">' + a + '</span>' : a) + ' <span class="gray">％</span>';
                return d;
            }
        }, {
            name: 'workDays',
            label: '工作天数',
            width: 100,
            fixed: !0
        }, {
            name: 'workHours',
            label: '工作小时',
            width: 100,
            fixed: !0
        }, {
            name: 'isFull',
            label: '全勤月数',
            width: 100,
            fixed: !0
        }, {
            name: 'dutyTimes',
            label: '值班次数',
            width: 100,
            fixed: !0
        }, {
            name: 'overtimeHours',
            label: '加班小时',
            width: 100,
            fixed: !0
        }, {
            name: 'llTimes',
            label: '迟到早退次数',
            width: 100,
            fixed: !0
        }, {
            name: 'missDays',
            label: '缺勤天数',
            width: 100,
            formatter: function(a, b, c) {
                var d = a <= 0 ? 0 : Math.abs(a);
                return d;
            }
        }];
        if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) === -1) {
            colModel = [{
                name: '',
                label: '<span class="ml30">排名</span>',
                width: 90,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (b.rowId == 1) {
                        rank = 1;
                        rowData = c;
                    } else {
                        if (!(c.workHours == rowData.workHours && c.workDays == rowData.workDays && c.llTimes == rowData.llTimes && c.clockoffDays == rowData.clockoffDays && c.overtimeHours == rowData.overtimeHours && c.dutyTimes == rowData.dutyTimes && c.isFull == rowData.isFull)) {
                            rowData = c;
                            rank++;
                        }
                    }
                    if (rank == 1) {
                        return '<i class="iconfont icon-diyiming gold f30"></i>';
                    } else if (rank == 2) {
                        return '<i class="iconfont icon-dierming gray f26"></i>';
                    } else if (rank == 3) {
                        return '<i class="iconfont icon-disanming copper f22"></i>';
                    } else {
                        return rank;
                    }
                }
            }, {
                name: 'uName',
                label: '姓名',
                width: 100,
                fixed: !0
            }, {
                name: 'workRate',
                label: '出勤率',
                width: 100,
                formatter: function(a, b, c) {
                    var d = (Number(a) >= 100 ? '<sapn class="green fb">' + a + '</span>' : a) + ' <span class="gray">％</span>';
                    return d;
                }
            }];
        }
        $('#grid').jqGrid({
            url: '/Bill/Report/attendanceRank',
            postData: queryConditions,
            datatype: 'json',
            colModel: colModel,
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
            sortname: 'CONVERT(uName USING gbk)',
            sortorder: 'ASC',
            pager: '#page',
            page: 1,
            rowNum: 1000,
            rowList: [1000, 2000, 5000],
            viewrecords: !0,
            cellsubmit: 'clientArray',
            footerrow: !1,
            userDataOnFooter: !1,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
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
                }, 300)
            }
        });
        $('#search').on('click', function(a) {
            queryConditions.company = combo1.getValue() == -1 ? '' : combo1.getText();
            queryConditions.department = combo2.getValue() == -1 ? '' : combo2.getText();
            queryConditions.position = combo3.getValue() == -1 ? '' : combo3.getText();
            queryConditions.isDisable = combo4.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1));
            $('#search').trigger('click');
        });
        $('#curYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-01-01'));
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#lastYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-01-01'), 12));
            $('#endDate').val(Public.reduceMonths((new Date).format('yyyy-12-31'), 12));
            $('#search').trigger('click');
        });
        $('#searchAll').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(defaultPage.SYSTEM.beginDate);
            $('#endDate').val(defaultPage.SYSTEM.endDate);
            $('#search').trigger('click');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    }
};
$(function() {
    THISPAGE.init();
});
