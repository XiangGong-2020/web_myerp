var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        Public.ajaxPost('/Base/WorkSite/list', {}, function(res) {
            if (res.status == 200) {
                var urls = res.data;
                var url = '';
                for (var i = 0; i < urls.length; i++) {
                    url += (
                        '<a href="' +
                        urls[i]['urlLink'] +
                        '" target="_blank"><div class="url" data-id="' +
                        urls[i]['id'] +
                        '"><div class="url-name">' +
                        urls[i]['urlName'] +
                        '<i class="edit-url iconfont icon-bianji" title="编辑"></i></div><div class="url-des">' +
                        urls[i]['urlDes'] +
                        '</div><div class="url-link">' +
                        urls[i]['urlLink'] +
                        '</div></div></a>'
                    );
                }
                $('#urls').html(url);
            } else {
                $.dialog.tips('未正确接收业务网站列表数据！' + res.msg, 2);
            }
        });
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('WORKSITE_ADD')) {
                var b = '新增业务网站';
                var c = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: b,
                    content: 'url:/Uri/work_site_manage',
                    data: c,
                    width: 800,
                    height: 130,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#urls').on('click', '.edit-url', function(a) {
            a.preventDefault();
            if (Business.verifyRight('WORKSITE_UPDATE')) {
                var id = $(this).parent().parent().data('id');
                $.dialog({
                    title: '编辑业务网站',
                    content: 'url:/Uri/work_site_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 800,
                    height: 130,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#urls').on('click', '.del-url', function(a) {
            a.preventDefault();
            if (Business.verifyRight('WORKSITE_DELETE')) {
                var id = $(this).parent().parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/WorkSite/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                        } else {
                            parent.$.dialog.tips('删除失败！' + res.msg, 2);
                        }
                        window.location.reload();
                    });
                });
            }
        });
    },
    callback: function(a, b, c, d) {
        THISPAGE.initDom(), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
    }
};
$(function() {
    THISPAGE.init();
});
