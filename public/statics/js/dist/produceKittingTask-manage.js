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
                "/Base/ProKittingTask/query",
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
            $("#reqTime").val(rowData.reqTime).attr({disabled: true}).addClass('ui-input-dis');
            $("#lastDate").val(rowData.lastDate);
            $("#goodsText").data("goodsId", rowData.goodsId).val(rowData.goodsText);
            $("#qty").val(rowData.qty);
            $("#description").val(rowData.description);
            THISPAGE.getHisQty(rowData.goodsId);
            if (api.config.disEdit) {
                $("#lastDate").attr("disabled", true).addClass("ui-input-dis");
                $("#reqTime").attr("disabled", true).addClass("ui-input-dis");
                $("#goodsText").attr("disabled", true).addClass("ui-input-dis");
                $("#qty").attr("disabled", true).addClass("ui-input-dis");
                $("#description").attr("disabled", true).addClass("ui-input-dis");
            }
        } else {
            if (cRowData) {
                $("#reqTime").val(cRowData.createTime).attr({disabled: true}).addClass('ui-input-dis');
                $("#lastDate").val(cRowData.lastDate);
                $("#goodsText").data("goodsId", cRowData.goodsId).val(cRowData.goodsText);
                $("#description").val("申请备注：" + cRowData.content + "\r\n" + "任务备注：");
                THISPAGE.getHisQty(cRowData.goodsId);
            }
        }
        reqNameCombo = $("#reqName")
            .combo({
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
            })
            .getCombo();
        $("#reqName").find("input").attr("name", "reqNameInput");
        Public.ajaxGet("/Base/User/queryAll", { department: "生产部", position: "配货专员", isDisable: 0 }, function (data) {
            if (data.status === 200) {
                var groupId = 0;
                var users = "";
                $.each(data.data.rows, function (i, v) {
                    if (v.groupId != groupId) {
                        if (i > 0) {
                            $("#users" + groupId).html(users);
                        }
                        $("#membersCombo").append('<optgroup id="users' + v.groupId + '" label="' + v.groupName + '"></optgroup>');
                        users = '<option value="' + v.id + '">' + v.name + "</option>";
                        groupId = v.groupId;
                    } else {
                        users += '<option value="' + v.id + '">' + v.name + "</option>";
                    }
                    if (i == data.data.rows.length - 1) {
                        $("#users" + groupId).html(users);
                    }
                });
                $("#membersCombo").fSelect({
                    placeholder:
                        "特殊情况下，可指定配货人并自动均分产量。如需修改配货人，只要所有配货人未交付过（最好是确定配货人未开始配货），可重选配货人，保存时将进行重新指定并均分产量。",
                    showSearch: !1,
                    listHeight: 372,
                });
            }
        });
    },
    initGrid: function (a) {
        $("#grid").jqGrid({
            data: a,
            datatype: "local",
            colModel: [
                {
                    name: "uName",
                    label: "姓名",
                    width: 56,
                    fixed: !0,
                    title: !1,
                    sortable: !0,
                },
                {
                    name: "getQty",
                    label: "领取产量",
                    width: 66,
                    fixed: !0,
                    title: !1,
                    sortable: !0,
                },
                {
                    name: "createTime",
                    label: "领取时间",
                    width: 140,
                    fixed: !0,
                    title: !1,
                    sortable: !0,
                    classes: "gray",
                },
                {
                    name: "upQty",
                    label: "已交付产量",
                    width: 70,
                    fixed: !0,
                    title: !1,
                    sortable: !0,
                },
                {
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
                },
                {
                    name: "backInfo",
                    label: "遇阻反馈",
                    width: 200,
                    fixed: !0,
                    title: !0,
                    sortable: !0,
                    classes: "ui-ellipsis red",
                },
                {
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
                },
                {
                    name: "transHis",
                    label: "转让历史",
                    width: 100,
                    title: !0,
                    sortable: !0,
                    classes: "ui-ellipsis gray",
                },
                {
                    name: "id",
                    label: "ID",
                    width: 0,
                    hidden: !0,
                },
            ],
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
                goodsText: { required: !0 },
                qty: { required: !0 },
            },
        });
    },
    initEvent: function () {
        laydate.render({
            elem: "#reqTime, #lastDate",
            type: "datetime",
        });
        if (!api.config.disEdit) {
            $("#goods").on("click", function () {
                $.dialog({
                    width: 1250,
                    height: 500,
                    title: "选择产品",
                    content: "url:/Uri/goods_batch",
                    data: {
                        isNoAdd: !0,
                        isSingle: !0,
                    },
                    max: !1,
                    min: !1,
                    lock: !0,
                    ok: function () {
                        for (let rowid in this.content.addList) {
                            var row = this.content.addList[rowid];
                            $("#goodsText")
                                .data("goodsId", rowid)
                                .val(
                                    row.no +
                                        " " +
                                        row.name +
                                        (row.spec ? " " + row.spec : "") +
                                        (row.sku ? " " + row.sku : "") +
                                        (row.brandName ? " " + row.brandName : "")
                                );
                            THISPAGE.getHisQty(rowid);
                        }
                    },
                    cancel: function () {
                        return true;
                    },
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
            if (rowData.takeStatus > 0 && !data.members) {
                $.dialog.tips("任务已被领取，不允许随意编辑！", 2);
                return void 0;
            }
            if (data.members) {
                var membersArr = data.members.split(",");
                if (data.qty < membersArr.length) {
                    $.dialog.tips("选择的配货人太多了，产量不够均分！", 2);
                    return void 0;
                }
            }
            var title = "add" == oper ? "新增" : "编辑";
            data &&
                Public.ajaxPost("/Base/ProKittingTask/" + ("add" == oper ? "add" : "update"), data, function (res) {
                    if (res && 200 == res.status) {
                        parent.parent.$.dialog.tips(title + "成功！");
                        parent.parent.frames["produce-produceKittingTask"] && parent.parent.frames["produce-produceKittingTask"].$("#search").trigger("click");
                        parent.parent.frames["produce-produceKittingReq"] && parent.parent.frames["produce-produceKittingReq"].$("#search").trigger("click");
                        window.api.close();
                    } else {
                        $.dialog.tips(title + "失败！" + res.msg, 2);
                    }
                });
        } else {
            $.dialog.tips("不能编辑别人发布的配货任务！仅本人或高级权限可编辑！", 2);
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
            goodsId: Number($("#goodsText").data("goodsId")),
            goodsText: $.trim($("#goodsText").val()),
            qty: parseInt($.trim($("#qty").val())),
            members: $.trim($(".fs-input").val()),
            description: $.trim($("#description").val()),
        };
        return data;
    },
    getHisQty: function (goodsId) {
        var qtyNotice = "";
        if (cRowData) {
            qtyNotice += '<div class="db fl pl10 pr10 lh28 gray">本次申请产量：' + cRowData.qty + "</span></div>";
        }
        Public.ajaxPost(
            "/Base/ProKittingTask/lastTaskInfo",
            {
                async: !1,
                which: "LastYear",
                id: cRowId || 0,
                goodsId: goodsId,
            },
            function (res) {
                if (200 === res.status && res.data) {
                    qtyNotice +=
                        '<div class="db fl pl10 pr10 lh28 gray">上次排产产量：' +
                        res.data.qty +
                        '</span></div><div class="db fl pl5 pr5 lh28 gray">上次排产时间：' +
                        res.data.createTime +
                        "</span></div>";
                }
            }
        );
        Public.ajaxPost(
            "/Base/Goods/listQty",
            {
                async: !1,
                which: "LastYear",
                goodsId: goodsId,
            },
            function (res) {
                if (200 === res.status && res.qty) {
                    var hisQty = res.qty || 0;
                    qtyNotice += '<div class="db fl pl10 pr10 lh28 gray">最近一年消耗量：' + hisQty + "</span></div>";
                }
            }
        );
        $("#qtyNotice").html(qtyNotice);
    },
};
$(function () {
    THISPAGE.init();
});
