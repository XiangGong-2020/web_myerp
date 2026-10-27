var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var originalData;
var curRow;
var curCol;
var companyCombo = $('#companyCombo').combo({
    data: '/Base/Company/queryAll?isDisable=0',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: -1,
    width: 139
}).getCombo();
var btn_final = '<a id="final" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存为正式</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_export = '<a id="export" class="ui-btn bg-teal" ><i class="iconfont icon-daochu"></i><span>导出原单</span></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        if (a.id > 0) {
            $('#toolTop').html(btn_edit + (a.isDraft == 1 ? btn_final : '') + btn_export + btn_delete);
            a.isDraft == 1 && $('#edit span').after('<b>当前为草稿</b>');
            $('#billNo').val(a.billNo);
            $('#beginDate').val(a.beginDate);
            $('#endDate').val(a.endDate);
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#createTime').html(a.createTime);
            $('#modifyTime').html(a.modifyTime);
            $('#exportByCompany').removeClass('dn');
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
                width: 46,
                fixed: !0,
                editable: !1
            }, {
                name: 'department',
                label: '部门',
                width: 46,
                fixed: !0,
                editable: !1
            }, {
                name: 'position',
                label: '职位',
                width: 68,
                fixed: !0,
                editable: !1
            }, {
                name: 'level',
                label: '等级',
                width: 34,
                fixed: !0,
                editable: !1
            }, {
                name: 'dailyWage',
                label: '日薪',
                width: 40,
                fixed: !0,
                editable: !1,
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'workDays',
                label: '<p class="mt10 lh14">出勤</p><p class="lh14">天数</p>',
                width: 46,
                fixed: !0,
                editable: !1
            }, {
                name: 'fillHours',
                label: '<p class="mt10 lh14">补班</p><p class="lh14">小时</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'workRate',
                label: '出勤率',
                width: 48,
                fixed: !0,
                editable: !1,
                formatter: function(a, b, c) {
                    return Number(Number(a) * 100).toFixed(1) + '%';
                }
            }, {
                name: 'evaluateScore',
                label: '考评分',
                width: 42,
                fixed: !0,
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'isFull',
                label: '全勤',
                width: 32,
                fixed: !0,
                formatter: function(a, b, c) {
                    return 1 == a ? '是' : '';
                },
                editable: !1,
                edittype: 'select',
                editoptions: {
                    value: {
                        1: '是',
                        0: '否'
                    }
                }
            }, {
                name: 'dutyTimes',
                label: '<p class="mt10 lh14">值班</p><p class="lh14">次数</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'overtimeHours',
                label: '<p class="mt10 lh14">加班</p><p class="lh14">小时</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'recordHours',
                label: '<p class="mt10 lh14">计时</p><p class="lh14">小时</p>',
                width: 46,
                fixed: !0,
                editable: !1,
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'attendanceSalary',
                label: '出勤工资',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'evaluateSalary',
                label: '考评工资',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'achievementSalary',
                label: '绩效工资',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'dutySalary',
                label: '值班工资',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'overtimeSalary',
                label: '加班工资',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'recordSalary',
                label: '计时工资',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'bonusFullAtt',
                label: '全勤奖金',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'bonusBid',
                label: '投标奖金',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'bonusPerson',
                label: '岗位津贴',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'deductionSocialSecurity',
                label: '社保扣费',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'deductionOther',
                label: '其他扣除',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb bg-rgba05',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'sumSalary',
                label: '汇总工资',
                width: 54,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb yellow',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'bonusOffSeason',
                label: '淡季津贴',
                width: 54,
                fixed: !0,
                editable: !0,
                classes: 'f14 fb',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'finalSalary',
                label: '实发工资',
                width: 54,
                fixed: !0,
                editable: !0,
                classes: 'f14 fb green',
                formatter: function(x, y, z) {
                    return x || '';
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'id',
                label: '',
                width: 0,
                hidden: !0,
                editable: !1
            }],
            cmTemplate: {sortable: !1, title: !1},
            width: setHW.w,
            height: '100%',
            gridview: !0,
            rownumbers: !0,
            rownumWidth: 30,
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
        $('.wrapper').on('click', '#exportCompanyAchievementExcel', function(a) {
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                var id = originalData.id;
                var company = companyCombo.getText();
                if (company) {
                    var href = '/Bill/Achievement/exportCompanyAchievementExcel?id=' + id + '&company=' + company;
                    $(this).attr('href', href);
                } else {
                    $.dialog.tips('请先选择经营主体！', 2, 5);
                }
            }
        });
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = originalData.isDraft;
                    Public.ajaxPost('/Bill/Achievement/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['personnel-achievement'] && parent.frames['personnel-achievement'].$('#refresh').trigger('click');
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
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/Achievement/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['personnel-achievement'] && parent.frames['personnel-achievement'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#export', function(a) {
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                var id = originalData.id;
                var href = '/Bill/Achievement/export?id=' + id;
                $(this).attr('href', href);
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/Achievement/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['personnel-achievement'] && parent.frames['personnel-achievement'].$('#refresh').trigger('click');
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
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                Public.ajaxPost('/Bill/Achievement/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['personnel-achievement'] && parent.frames['personnel-achievement'].$('#refresh').trigger('click');
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
            if (scrTop >= nTop) {
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
                    bonusOffSeason: rowData.bonusOffSeason || 0,
                    finalSalary: rowData.finalSalary || 0,
                    description: rowData.description || ''
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
        Public.ajaxGet('/Bill/Achievement/query', {
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
