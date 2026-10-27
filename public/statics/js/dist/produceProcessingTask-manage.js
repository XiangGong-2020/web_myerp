var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var cRowData = api.data.cRowData;
var rowData = {};
var reqNameCombo;
var THISPAGE = {
    init: function () {
        if (void 0 !== cRowId) {
            Public.ajaxPost(
                "/Base/ProProcessingTask/query",
                {
                    id: cRowId,
                },
                function (res) {
                    if (200 == res.status) {
                        rowData = res.data;
                        THISPAGE.initBtns();
                        THISPAGE.initDom();
                        THISPAGE.initGrid(rowData.entries);
                        THISPAGE.initValidator();
                        THISPAGE.initEvent();
                    } else {
                        $.dialog.tips("获取数据失败！" + res.msg, 2);
                    }
                }
            );
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initGrid();
            THISPAGE.initValidator();
            THISPAGE.initEvent();
        }
    },
    initBtns: function () {
        if (api.config.disEdit) {
            api.button({
                id: "cancel",
                name: "关闭",
            });
        } else {
            api.button(
                {
                    id: "confirm",
                    name: "保存",
                    defClass: "ui_state_highlight",
                    callback: function () {
                        return THISPAGE.postData(), !1;
                    },
                },
                {
                    id: "cancel",
                    name: "关闭",
                }
            );
        }
    },
    initDom: function () {
        if ("edit" == oper) {
            $("#reqTime").val(rowData.reqTime).attr({ disabled: true }).addClass('ui-input-dis');
            $("#lastDate").val(rowData.lastDate);
            $("#projectName").data("projectId", rowData.projectId).data("needTest", rowData.needTest).val(rowData.projectName);
            $("#qty").val(rowData.qty);
            $("#description").val(rowData.description);
            if (rowData.needTest == 1) {
                $('#testersCombo').parent().parent().removeClass('dn');
            }
            if (api.config.disEdit) {
                $("#lastDate").attr("disabled", true).addClass("ui-input-dis");
                $("#reqTime").attr("disabled", true).addClass("ui-input-dis");
                $("#projectName").attr("disabled", true).addClass("ui-input-dis");
                $("#qty").attr("disabled", true).addClass("ui-input-dis");
                $("#description").attr("disabled", true).addClass("ui-input-dis");
            }
        } else {
            if (cRowData) {
                $("#reqTime").val(cRowData.createTime).attr({ disabled: true }).addClass('ui-input-dis');
                $("#lastDate").val(cRowData.lastDate);
                $("#qty").val(cRowData.qty);
                $("#projectName").data("projectId", cRowData.projectId).data("needTest", cRowData.needTest).val(cRowData.projectName);
                $("#description").val("申请备注：" + cRowData.content + "\r\n" + "任务备注：");
                if (cRowData.needTest == 1) {
                    $('#testersCombo').parent().parent().removeClass('dn');
                }
            }
        }
        reqNameCombo = $("#reqName").combo({
            data: "/Base/User/queryAll?normal=yes",
            ajaxOptions: {
                formatData: function (a) {
                    return a.data.rows;
                },
            },
            value: "id",
            text: "name",
            defaultSelected: cRowData ? ["name", cRowData.uName] : rowData ? ["name", rowData.reqName] : -1,
            width: 90,
            listHeight: 500,
            disabled: (api.config.disEdit || cRowData || rowData.reqId > 0) ? !0 : !1,
        }).getCombo();
        $("#reqName").find("input").attr("name", "reqNameInput");
        Public.ajaxGet("/Base/User/queryAll", { department: "生产部", position: "加工专员", isDisable: 0 }, function (data) {
            if (data.status === 200) {
                var groupId = 0;
                var users = "";
                $.each(data.data.rows, function (i, v) {
                    if (v.groupId != groupId) {
                        if (i > 0) {
                            $("#producersGroup" + groupId).html(users);
                            $("#testersGroup" + groupId).html(users);
                        }
                        $("#producersCombo").append('<optgroup id="producersGroup' + v.groupId + '" label="' + v.groupName + '"></optgroup>');
                        $("#testersCombo").append('<optgroup id="testersGroup' + v.groupId + '" label="' + v.groupName + '"></optgroup>');
                        users = '<option value="' + v.id + '">' + v.name + "</option>";
                        groupId = v.groupId;
                    } else {
                        users += '<option value="' + v.id + '">' + v.name + "</option>";
                    }
                    if (i == data.data.rows.length - 1) {
                        $("#producersGroup" + groupId).html(users);
                        $("#testersGroup" + groupId).html(users);
                    }
                });
                $("#producersCombo").fSelect({
                    id: 'producersCombo',
                    placeholder: "可指定加工人并自动均分产量",
                    showSearch: !1,
                    listHeight: 372,
                    disabled: api.config.disEdit ? !0 : !1
                });
                $("#testersCombo").fSelect({
                    id: 'testersCombo',
                    placeholder: "可指定测试人并自动均分产量",
                    showSearch: !1,
                    listHeight: 372,
                    disabled: api.config.disEdit ? !0 : !1
                });
            }
        });
    },
    initGrid: function (a) {
        $("#grid").jqGrid({
            data: a,
            datatype: "local",
            colModel: [{
                name: "uName",
                label: "姓名",
                width: 56,
                fixed: !0,
                title: !1,
                sortable: !0,
            }, {
                name: 'isTest',
                label: '领取工序',
                width: 70,
                fixed: !0,
                title: !1,
                sortable: !0,
                formatter: function (a, b, c) {
                    return a == 1 ? '<span class="green">测试工序</span>' : '加工工序';
                }
            }, {
                name: "getQty",
                label: "领取产量",
                width: 66,
                fixed: !0,
                title: !1,
                sortable: !0,
            }, {
                name: "createTime",
                label: "领取时间",
                width: 140,
                fixed: !0,
                title: !1,
                sortable: !0,
                classes: "gray",
            }, {
                name: "upQty",
                label: "已交付产量",
                width: 70,
                fixed: !0,
                title: !1,
                sortable: !0,
            }, {
                name: "doneStatus",
                label: "完成状态",
                width: 140,
                fixed: !0,
                title: !1,
                sortable: !0,
                formatter: function (a, b, c) {
                    var d = 0 == a ? "未完全交付" : 1 == a ? "有交付待审核" : "已完全交付";
                    var e = 0 == a ? "yellow" : 1 == a ? "blue" : "green";
                    var f =
                        0 == a && new Date().format("yyyy-MM-dd hh:mm:ss") > c.lastDate
                            ? '<span class="db fr lh22 pl5 pr5 white bg-yellow b-r-3">已超时</span>'
                            : "";
                    return '<span class="' + e + '">' + d + "</span>" + f;
                },
            }, {
                name: "backInfo",
                label: "遇阻反馈",
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: "ui-ellipsis red",
            }, {
                name: "transInfo",
                label: "转让中",
                width: 100,
                title: !0,
                sortable: !0,
                classes: "ui-ellipsis",
                formatter: function (a, b, c) {
                    var d = "";
                    if (a) {
                        $.each(a, function (k, v) {
                            d += (k.includes(",") ? "整组" : "单人") + "转让产量" + v.qty + "给" + k + " " + v.time + "\r\n";
                        });
                    }
                    return d;
                },
            }, {
                name: "transHis",
                label: "转让历史",
                width: 100,
                title: !0,
                sortable: !0,
                classes: "ui-ellipsis gray",
            }, {
                name: "id",
                label: "ID",
                width: 0,
                hidden: !0,
            }],
            cmTemplate: { sortable: !1, title: !1 },
            localReader: { root: "rows", records: "records", repeatitems: !1, id: "id" },
            width: 1228,
            height: 224,
            rownumbers: !0,
            gridview: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 100,
            cellEdit: !1,
            footerrow: !0,
            userDataOnFooter: !0,
            userData: {
                rn: "合计",
                getQty: 0,
                upQty: 0,
            },
            loadComplete: function (data) {
                THISPAGE.calTotal();
            },
            loadError: function (a, b) {
                $.dialog.tips("Type: " + b + "; Response: " + a.status + " " + a.statusText, 2);
            },
        });
    },
    calTotal: function () {
        for (var a = $("#grid").jqGrid("getDataIDs"), b = 0, c = 0, d = 0, e = a.length; e > d; d++) {
            var f = a[d];
            var g = $("#grid").jqGrid("getRowData", f);
            g.getQty && (b += parseFloat(g.getQty));
            g.upQty && (c += parseFloat(g.upQty));
        }
        $("#grid").jqGrid("footerData", "set", {
            rn: "合计",
            getQty: b,
            upQty: c,
        });
    },
    initValidator: function () {
        $("#manage-form").validate({
            rules: {
                reqNameInput: { required: !0 },
                reqTime: { required: !0 },
                lastDate: { required: !0 },
                projectName: { required: !0 },
                qty: { required: !0 },
            },
        });
    },
    initEvent: function () {
        laydate.render({
            elem: "#reqTime, #lastDate",
            type: "datetime",
        });
        if (api.config.disEdit || cRowData || rowData.reqId > 0) {
            $("#projectName").attr({ disabled: true }).addClass('ui-input-dis');
        } else {
            $("#project").on("click", function () {
                $.dialog({
                    width: 1250,
                    height: 500,
                    title: '选择加工项目',
                    content: 'url:/Uri/produceProcessingProject_batch',
                    data: {
                        isSingle: !0
                    },
                    max: !1,
                    min: !1,
                    lock: !0,
                    ok: function () {
                        for (let rowid in this.content.addList) {
                            var row = this.content.addList[rowid];
                            $('#projectName').data('projectId', rowid).data('needTest', row.needTest == '有' ? 1 : 0).val(
                                row.name
                            );
                            if (row.needTest == '有') {
                                $('#testersCombo').parent().parent().removeClass('dn');
                            } else {
                                $("#producersCombo .fs-input").val('');
                                $("#testersCombo .fs-input").val('');
                                $('#testersCombo').parent().parent().addClass('dn');
                            }
                        }
                    },
                    cancel: function () {
                        return !0;
                    }
                });
            });
        }
    },
    postData: function () {
        if ($.inArray("PROTASK_ADVANCE", defaultPage.SYSTEM.rights) !== -1 || rowData.uId == defaultPage.SYSTEM.uId || void 0 == cRowId) {
            if (!$("#manage-form").validate().form()) {
                return void $("#manage-form").find("input.valid-error").eq(0).focus();
            }
            var data = THISPAGE.getData();
            if (data.qty <= 0) {
                $.dialog.tips("任务产量必须大于0", 2);
                return void 0;
            }
            if (rowData.takeStatus > 0 && !data.producers) {
                $.dialog.tips("任务已被领取，不允许随意编辑！", 2);
                return void 0;
            }
            if (data.producers) {
                var producersArr = data.producers.split(",");
                if (data.qty < producersArr.length) {
                    $.dialog.tips("指定的加工人太多了，产量不够均分！", 2);
                    return void 0;
                }
                if (data.needTest == 1 && !data.testers) {
                    $.dialog.tips("含测试工序的项目必须指定测试人！", 2);
                    return void 0;
                }
            }
            if (data.testers) {
                var testersArr = data.testers.split(",");
                if (data.qty < testersArr.length) {
                    $.dialog.tips("指定的测试人太多了，产量不够均分！", 2);
                    return void 0;
                }
                if (data.needTest == 1 && !data.producers) {
                    $.dialog.tips("指定了测试人时，必须同时指定加工人！", 2);
                    return void 0;
                }
            }
            var title = "add" == oper ? "新增" : "编辑";
            data && Public.ajaxPost("/Base/ProProcessingTask/" + ("add" == oper ? "add" : "update"), data, function (res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + "成功！");
                    parent.parent.frames["produce-produceProcessingTask"] && parent.parent.frames["produce-produceProcessingTask"].$("#search").trigger("click");
                    parent.parent.frames["produce-produceProcessingReq"] && parent.parent.frames["produce-produceProcessingReq"].$("#search").trigger("click");
                    window.api.close();
                } else {
                    $.dialog.tips(title + "失败！" + res.msg, 2);
                }
            });
        } else {
            $.dialog.tips("不能编辑别人发布的加工任务！仅本人或高级权限可编辑！", 2);
            return void 0;
        }
    },
    getData: function () {
        var data = {
            id: cRowId || 0,
            reqId: cRowData ? cRowData.id : 0,
            reqName: reqNameCombo.getText() || "",
            reqTime: $.trim($("#reqTime").val()),
            lastDate: $.trim($("#lastDate").val()),
            projectId: Number($("#projectName").data("projectId")),
            projectName: $.trim($("#projectName").val()),
            needTest: Number($("#projectName").data("needTest")),
            qty: parseInt($.trim($("#qty").val())),
            producers: $.trim($("#producersCombo .fs-input").val()),
            testers: $.trim($("#testersCombo .fs-input").val()),
            description: $.trim($("#description").val()),
        };
        return data;
    }
};
$(function () {
    THISPAGE.init();
});
