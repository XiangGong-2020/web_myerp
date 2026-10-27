var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var camera = [
    { id: "BH5462509-1-live", name: "1楼门口1" },
    { id: "E48354366-12-live", name: "1楼门口2" },
    { id: "D08199542-1-live", name: "1楼门口3" },
    { id: "E48354366-13-live", name: "1楼门口4" },
    { id: "E48354366-2-live", name: "1楼门外1" },
    { id: "E48354366-1-live", name: "1楼门外2" },
    { id: "E48354366-6-live", name: "1楼门外3" },
    { id: "E48354366-15-live", name: "1楼过道1" },
    { id: "E48354366-14-live", name: "1楼过道2" },
    { id: "E48354366-16-live", name: "1楼扫码台" },
    { id: "D10832849-1-live", name: "1楼包货台1" },
    { id: "D07809476-1-live", name: "1楼包货台2" },
    { id: "E48354366-17-live", name: "1楼包货台3" },
    { id: "J27034484-12-live", name: "2楼门厅" },
    { id: "J27034484-15-live", name: "2楼过道1" },
    { id: "J27034484-11-live", name: "2楼过道2" },
    { id: "J27034484-6-live", name: "2楼激光加工1" },
    { id: "J27034484-9-live", name: "2楼激光加工2" },
    { id: "J27034484-14-live", name: "2楼电子组装1" },
    { id: "J27034484-13-live", name: "2楼电子组装2" },
    { id: "J27034484-8-live", name: "2楼办公区1" },
    { id: "J27034484-7-live", name: "2楼办公区2" },
    { id: "E48354366-5-live", name: "4楼前台" },
    { id: "E48354366-21-live", name: "4楼休息区" },
    { id: "E48354366-18-live", name: "4楼办公区" },
    { id: "E48354366-19-live", name: "4楼配货区1" },
    { id: "E48354366-20-live", name: "4楼配货区2" },
    { id: "BH5462529-1-live", name: "4楼配货区3" },
];
if ($.inArray("CAMERA_MIDDLE", defaultPage.SYSTEM.rights) !== -1) {
    camera.push(
        { id: "J27034484-5-live", name: "3楼前台1" },
        { id: "J27034484-3-live", name: "3楼前台2" },
        { id: "J27034484-1-live", name: "3楼休息区1" },
        { id: "J27034484-2-live", name: "3楼休息区2" },
        { id: "J27034484-4-live", name: "3楼过道" },
        { id: "E48354366-22-live", name: "4楼拍摄室" },
        //{ id: "E48354366-23-live", name: "4楼财务室" },
        { id: "E48354366-25-live", name: "4楼厨房" },
    );
}
if ($.inArray("CAMERA_ADVANCE", defaultPage.SYSTEM.rights) !== -1) {
    camera.push(
        { id: "E48354366-7-live", name: "1楼围墙" },
        { id: "E48354366-8-live", name: "1楼广场1" },
        { id: "E48354366-9-live", name: "1楼广场2" },
        { id: "E48354366-10-live", name: "1楼广场3" },
        { id: "E48354366-11-live", name: "1楼大梯子" },
        { id: "E48354366-3-live", name: "2楼阳台1" },
        { id: "J27034484-10-live", name: "2楼阳台2" },
        { id: "E48354366-4-live", name: "2楼阳台3" },
        { id: "E48354366-24-live", name: "4楼阳台1" },
    );
}
var combo = $("#combo")
    .combo({
        data: camera,
        value: "id",
        text: "name",
        defaultSelected: 0,
        width: 120,
        listHeight: 430,
        callback: {
            onListClick: function () {
                var selectedCamera = camera[this._selectedIndex];
                var idParts = selectedCamera.id.split("-");
                THISPAGE.initDom(idParts[0], idParts[1], idParts[2]);
            },
        },
    })
    .getCombo();
var THISPAGE = {
    init: function () {
        THISPAGE.initDom("BH5462509", 1, "live");
    },
    initDom: function (deviceSerial, channelNo, type) {
        // 延迟1秒再发起数据获取，以便等待插件加载完成。
        setTimeout(() => {
            // 获取播放url
            Public.ajaxPost(
                "/Base/CameraView/index",
                {
                    deviceSerial: deviceSerial,
                    channelNo: channelNo,
                    type: type,
                },
                function (res) {
                    if (res.status == 200 && res.data) {
                        var width = document.documentElement.clientWidth;
                        var height = document.documentElement.clientHeight - 52;
                        if (window.EZOPENDemo == void 0) {
                            var EZOPENDemo;
                            window.EZOPENDemo = EZOPENDemo;
                            window.EZOPENDemo = new EZUIKit.EZUIKitPlayer({
                                id: "playWind",
                                width: width,
                                height: height,
                                quality: "qp", // 0:流畅；1:标清; 2:高清; 3:超清; 4:极清; 5:3K; 6:4K ; "pp":"性能优先 (Performance Priority)"; "qp":"画质优先(Quality Priority)"
                                template: "a508eea154fb4ebd9cf55c6d953cf1c0",
                                url: res.data.url,
                                accessToken: res.data.accessToken,
                            });
                        } else {
                            window.EZOPENDemo.stop();
                            window.EZOPENDemo.changePlayUrl({
                                type: type,
                                deviceSerial: deviceSerial,
                                channelNo: channelNo,
                            });
                        }
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                },
            );
        }, 1000);
    },
    initFloat: function () {
        defaultPage.$.dialog({
            title: "监控直播",
            content: "url: /Uri/camera_live",
            data: {},
            width: 820,
            height: 520,
            left: document.documentElement.clientWidth - 820 + 100 + "px",
            top: "50px",
            max: !0,
            min: !0,
            cache: !1,
            lock: !1,
            resize: !1,
        });
        defaultPage.tab.removeTabItem("index-cameraLive");
    },
};
$(function () {
    THISPAGE.init();
});
