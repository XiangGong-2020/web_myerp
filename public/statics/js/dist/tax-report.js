var defaultPage = Public.getDefaultPage();
var queryConditions = {
    skey: "",
};
var companyAll = {};
var usCombo = $("#uscombo")
    .combo({
        data: "/Base/Company/queryAll",
        ajaxOptions: {
            formatData: function(a) {
                a.data.rows.forEach((element) => {
                    if (element.id > 0) {
                        var id = "" + element.id;
                        companyAll[id] = element.name;
                    }
                });
                return (
                    a.data.rows.unshift({
                        id: -1,
                        name: "所有经营主体",
                    }),
                    a.data.rows
                );
            },
        },
        value: "id",
        text: "name",
        defaultSelected: 0,
        width: 102,
        callback: {
            onListClick: function() {
                $("#search").trigger("click");
            },
        },
    })
    .getCombo();
var dateCombo = $("#datecombo")
    .combo({
        data: [
            {id: 0, name: "所有时段"},
            {id: 1, name: "本月"},
            {id: 2, name: "上月"},
            {id: 3, name: "本季度"},
            {id: 4, name: "上季度"},
            {id: 5, name: "本年"},
            {id: 6, name: "上年"},
        ],
        value: "id",
        text: "name",
        defaultSelected: 0,
        width: 78,
        callback: {
            onListClick: function() {
                $("#search").trigger("click");
            },
        },
    })
    .getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $("#beginDate").val(defaultPage.SYSTEM.beginDate);
        $("#endDate").val(defaultPage.SYSTEM.endDate);
    },
    initGrid: function() { },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            type: "date",
            range: ["#beginDate", "#endDate"],
            done: function() {
                setTimeout(function() {
                    $("#search").trigger("click");
                }, 300);
            },
        });
        $("#search").on("click", function() { });
        $("#refresh").on("click", function() {
            window.location.reload();
        });
    },
    callback: function(a, b, c) { },
};
$(function() {
    THISPAGE.init();
});
