var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var originalData;
var curRow;
var curCol;
var btn_final = '<a id="final" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存为正式</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        if (a.id > 0) {
            $('#toolTop').html(btn_edit + (a.isDraft == 1 ? btn_final : '') + btn_delete);
            a.isDraft == 1 && $('#edit span').after('<b>当前为草稿</b>');
            $('#billNo').val(a.billNo);
            $('#beginDate').val(a.beginDate);
            $('#endDate').val(a.endDate);
            $('#mustDayNum').val(a.mustDayNum || '');
            $('#fillClockinTimes').val(a.fillClockinTimes || '');
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#createTime').html(a.createTime);
            $('#modifyTime').html(a.modifyTime);
            if (a.Warning) {
                $.dialog.tips(a.Warning, 2, 60);
            }
        }
    },
    initGrid: function() {
        if (originalData.id) {
            var c = 1 - originalData.entries.length;
            if (c > 0) {
                for (var d = 0; c > d; d++) {
                    originalData.entries.push({});
                }
            }
        }
        THISPAGE.newId = 2;
        var setHW = Public.setGrid(0, 32);
        $('#grid').jqGrid({
            data: originalData.entries,
            datatype: 'clientSide',
            colModel: [{
                name: 'uId',
                label: '工号',
                width: 40,
                fixed: !0,
                editable: !1,
                align: 'center',
                formatter: function(x, y, z) {
                    return x ? String(x).padStart(4, '0') : '';
                }
            }, {
                name: 'uName',
                label: '姓名',
                width: 44,
                fixed: !0,
                editable: !1
            }, {
                name: 'department',
                label: '部门',
                width: 44,
                fixed: !0,
                editable: !1
            }, {
                name: 'position',
                label: '职位',
                width: 66,
                fixed: !0,
                editable: !1
            }, {
                name: 'workHours',
                label: '<p class="mt10 lh14">打卡</p><p class="lh14">小时</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'fillHours',
                label: '<p class="mt10 lh14">补班</p><p class="lh14">小时</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'overtimeHours',
                label: '<p class="mt10 lh14">加班</p><p class="lh14">小时</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'recordHours',
                label: '<p class="mt10 lh14">计时</p><p class="lh14">小时</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'dutyTimes',
                label: '<p class="mt10 lh14">值班</p><p class="lh14">次数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 0
                }
            }, {
                name: 'fillClockinTimes',
                label: '<p class="mt10 lh14">补卡</p><p class="lh14">次数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 0
                }
            }, {
                name: 'lateTimes',
                label: '<p class="mt10 lh14">迟到</p><p class="lh14">次数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 0
                }
            }, {
                name: 'leaveearlyTimes',
                label: '<p class="mt10 lh14">早退</p><p class="lh14">次数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 0
                }
            }, {
                name: 'clockoffDays',
                label: '<p class="mt10 lh14">请假</p><p class="lh14">天数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'clockadjRestDays',
                label: '<p class="mt10 lh14">调休</p><p class="lh14">天数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'fillRestDays',
                label: '<p class="mt10 lh14">补休</p><p class="lh14">天数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'travelDays',
                label: '<p class="mt10 lh14">外勤</p><p class="lh14">天数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'clockadjWorkDays',
                label: '<p class="mt10 lh14">调班</p><p class="lh14">天数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'fillDays',
                label: '<p class="mt10 lh14">补班</p><p class="lh14">天数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'workDays',
                label: '<p class="mt10 lh14">出勤</p><p class="lh14">天数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'mustDayNum',
                label: '<p class="mt10 lh14">应出勤天数</p><small class="fl lh14">可编辑</small>',
                width: 68,
                fixed: !0,
                editable: !0,
                classes: 'teal',
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    thousandsSeparator: '',
                    decimalPlaces: 1
                }
            }, {
                name: 'deSocialSecurityDayNum',
                label: '<p class="mt10 lh14">缺勤扣社保天数</p><small class="fl lh14">可编辑</small><small class="fl lh14 fb" title="豁免条件：<br>1、未买社保的员工不扣；<br>2、缺勤不满0.5天不扣；<br>3、本人结婚3天内不扣；<br>4、病假住院7天内不扣（需提供医院证明）；<br>5、丧假3天内不扣，包括父母（岳父母、公婆）、配偶、子女，不包括隔代亲属；<br>女员工生育属于另外的情况，不在此列。">豁免条件</small>',
                width: 100,
                fixed: !0,
                editable: !0,
                classes: 'red',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'isFull',
                label: '<p class="mt10 lh14">全勤</p><small class="fl lh14">可编辑</small>',
                width: 44,
                fixed: !0,
                formatter: function(a, b, c) {
                    return 1 == a ? '是' : '';
                },
                editable: !0,
                edittype: 'select',
                editoptions: {
                    value: {
                        0: '',
                        1: '是'
                    }
                }
            }, {
                name: 'description',
                label: '<p class="mt10 lh14">备注</p><small class="fl lh14">可编辑</small>',
                width: 100,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'modifyInfo',
                label: '修改标记',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis orange'
            }, {
                name: 'id',
                label: '',
                width: 0,
                hidden: !0,
                editable: !1
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.entries', records: 'records', repeatitems: !1, id: 'id'},
            width: setHW.w,
            height: '100%',
            gridview: !0,
            rownumbers: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !1,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadonce: !0,
            footerrow: !1,
            userDataOnFooter: !1,
            loadComplete: function(a) {
                if (urlParam.id > 0) {
                    var b = a.rows;
                    var c = b.length;
                    THISPAGE.newId = c + 1;
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid'));
                }, 10);
            },
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                THISPAGE.curID = rowId;
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initEvent: function() {
        laydate.render({elem: "#beginDate, #endDate"});
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ATTENDANCE_ADVANCE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = originalData.isDraft;
                    Public.ajaxPost('/Bill/Attendance/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (200 === res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['personnel-attendance'] && parent.frames['personnel-attendance'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#final', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ATTENDANCE_ADVANCE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/Attendance/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (200 === res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['personnel-attendance'] && parent.frames['personnel-attendance'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ATTENDANCE_ADVANCE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/Attendance/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['personnel-attendance'] && parent.frames['personnel-attendance'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-triangle-1-s', function(a) {
            var b = $(this).siblings();
            var c = b.getCombo();
            setTimeout(function() {
                c.active = !0;
                c.doQuery();
            }, 10);
        });
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ATTENDANCE_ADVANCE')) {
                Public.ajaxPost('/Bill/Attendance/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(b) {
                    if (200 === b.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['personnel-attendance'] && parent.frames['personnel-attendance'].$('#refresh').trigger('click');
                    } else {
                        $.dialog.tips('备注保存失败！' + res.msg, 2);
                    }
                });
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
        $(window).on('scroll', function() {
            var nTop = $(".grid-wrap").offset().top - 100;
            var scrTop = $(this).scrollTop();
            if(scrTop >= nTop) {
                $(".ui-jqgrid .ui-jqgrid-hdiv").css({'position': 'fixed', 'z-index': 2, 'padding': '0 16px', 'top': '50px', 'left': 0, 'right': 0});
            } else {
                $(".ui-jqgrid .ui-jqgrid-hdiv").css({'position': 'relative', 'padding': 0, 'top': 'auto', 'left': 0, 'right': 0});
            }
        });
    },
    getEntriesData: function() {
        for (var entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData) {
                var data = {
                    id: rowData.id,
                    uName: rowData.uName || '',
                    workHours: rowData.workHours || 0,
                    fillHours: rowData.fillHours || 0,
                    workDays: rowData.workDays || 0,
                    mustDayNum: rowData.mustDayNum || 0,
                    deSocialSecurityDayNum: rowData.deSocialSecurityDayNum || 0,
                    isFull: '是' == rowData.isFull ? 1 : 0,
                    description: rowData.description
                };
                entriesData.push(data);
            }
        }
        return entriesData;
    },
    getPostData: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var entriesData = THISPAGE.getEntriesData();
        if (entriesData && entriesData.length > 0) {
            var data = {
                id: originalData.id,
                billNo: originalData.billNo,
                beginDate: originalData.beginDate,
                endDate: originalData.endDate,
                entries: entriesData,
                description: $.trim($('#description').val())
            };
            return data;
        }
        return $.dialog.tips('表格不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
    }
};
$(function() {
    if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/Attendance/query', {
            id: urlParam.id
        }, function(res) {
            if (res && 200 == res.status) {
                originalData = res.data;
                THISPAGE.init(originalData);
                $('.wrapper > #div-lock').remove();
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    } else {
        originalData = {
            id: -1,
            status: 'add',
            entries: []
        };
        THISPAGE.init(originalData);
    }
});
