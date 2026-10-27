var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var originalData;
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
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#createTime').html(a.createTime);
            $('#modifyTime').html(a.modifyTime);
        }
    },
    initGrid: function() {
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
                width: 80,
                fixed: !0,
                editable: !1
            }, {
                name: 'department',
                label: '部门',
                width: 80,
                fixed: !0,
                editable: !1
            }, {
                name: 'position',
                label: '职位',
                width: 80,
                fixed: !0,
                editable: !1
            }, {
                name: 'score',
                label: '考评得分',
                width: 80,
                fixed: !0
            }, {
                name: 'description',
                label: '评语',
                width: 100,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'lastName',
                label: '最后考评人',
                width: 70,
                fixed: !0,
                classes: 'gray'
            }, {
                name: 'lastTime',
                label: '最后考评时间',
                width: 124,
                fixed: !0,
                classes: 'gray'
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0,
                editable: !1
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
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
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid'));
                }, 10);
            }
        });
    },
    initEvent: function() {
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATE_ADVANCE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = originalData.isDraft;
                    Public.ajaxPost('/Bill/Evaluate/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['personnel-evaluate'] && parent.frames['personnel-evaluate'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            parent.$.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#final', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATE_ADVANCE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/Evaluate/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['personnel-evaluate'] && parent.frames['personnel-evaluate'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            parent.$.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATE_ADVANCE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/Evaluate/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['personnel-evaluate'] && parent.frames['personnel-evaluate'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATE_ADVANCE')) {
                Public.ajaxPost('/Bill/Evaluate/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['personnel-evaluate'] && parent.frames['personnel-evaluate'].$('#refresh').trigger('click');
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
    getPostData: function() {
        originalData.description = $.trim($('#description').val());
        return originalData;
    }
};
$(function() {
    if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/Evaluate/query', {
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
    }
});
