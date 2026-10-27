var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var originalData;
var classes;
var videotypeCombo;
var departmentCombo;
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_gotoedit = '<a id="gotoedit" class="ui-btn"><i class="iconfont icon-bianji"></i><span>进入编辑模式</span></a>';
var THISPAGE = {
    init: function(a) {
        this.initDom(a);
        this.initEvent();
    },
    initDom: function(a) {
        videotypeCombo = Business.categoryCombo($('#videotypeCombo'), {
            listHeight: 500,
            defaultSelected: a.videotype > 0 ? ['id', a.videotype] : -1,
            extraListHtml: '<a href="#" class="quick-add-link" onclick="THISPAGE.addVideoType();return false;">新增视频类别</a>'
        }, 'videotype');
        $('#videotypeCombo').find('input').attr('name', 'videotypeComboInput');
        if (a.type == 2) {
            $('#videotypeCon').removeClass('dn');
        }
        Public.ajaxGet('/Base/Category/list?typeNumber=departmenttype', {}, function(res) {
            if (res.status === 200) {
                var department = '';
                $.each(res.data.rows, function(i, v) {
                    department += '<option value="' + v.id + '">' + v.name + '</option>';
                });
                /* 异步获取数据成功之后再填充选项并加载选择事件 */
                $('#department').html(department);
                $('#departmentCombo').fSelect({
                    id: 'departmentFselect',
                    showSearch: !1,
                    width: 200,
                    listHeight: 500,
                    placeholder: '留空则所有部门均可查看'
                });
                if (a.department) {
                    $('#departmentFselect .fs-input').val(a.department);
                    var names = a.department.toString().split(',');
                    $("#departmentFselect .fs-option").each(function() {
                        if (names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    });
                }
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
        classes = $('#classes').cssRadio({
            callback: function(b) {
                originalData.type = $('#classes label.checked input').val();
                if (originalData.type == 2) {
                    $('#videotypeCon').removeClass('dn');
                } else {
                    $('#videotypeCon').addClass('dn');
                }
            }
        });
        switch (a.type) {
            case 1:
                classes.setValue(0);
                break;
            case 2:
                classes.setValue(1);
                break;
            case 3:
                classes.setValue(2);
                break;
            case 4:
                classes.setValue(3);
                break;
            default:
                classes.setValue(0);
                break;
        }
        if (a.id > 0) {
            $.inArray('NEWS_UPDATE', defaultPage.SYSTEM.rights) !== -1 ? $('#toolTop').html(btn_gotoedit) : $('#toolTop').html('');
            $('#title').val(a.title);
            $('#beginName').val(a.beginName);
            $('#beginDate').val(a.beginDate);
            $('#content').html(a.content);
            if (a.type == 2) {
                $('#content').addClass('fl pct50 white');
                $('#watch').removeClass('dn').show();
                THISPAGE.doWatchinfo();
            }
        } else {
            $.inArray('NEWS_ADD', defaultPage.SYSTEM.rights) !== -1 ? $('#toolTop').html(btn_save) : $('#toolTop').html('');
            $('#beginName').val(defaultPage.SYSTEM.uName);
            $('#beginDate').val((new Date).format('yyyy-MM-dd hh:mm:ss'));
            $('#content').hide();
            ue.addListener('ready', function() {
                $('#editor').show();
            });
        }
    },
    initEvent: function() {
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            if (Business.verifyRight('NEWS_ADD')) {
                var data = THISPAGE.getPostData();
                data && Public.ajaxPost('/Base/News/add', data, function(res) {
                    if (res && 200 == res.status) {
                        parent.parent.$.dialog.tips('新增成功！');
                        parent.frames['index-knowledges'] && parent.frames['index-knowledges'].$('#search').trigger('click');
                        parent.frames['index-videos'] && parent.frames['index-videos'].$('#search').trigger('click');
                        parent.frames['index-files'] && parent.frames['index-files'].$('#search').trigger('click');
                        parent.frames['index-rules'] && parent.frames['index-rules'].$('#search').trigger('click');
                        parent.tab.removeSelectedTabItem();
                    } else {
                        $.dialog.tips('新增失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('NEWS_UPDATE')) {
                var data = THISPAGE.getPostData();
                data && Public.ajaxPost('/Base/News/update', data, function(res) {
                    if (res && 200 == res.status) {
                        parent.parent.$.dialog.tips('更新成功！');
                        parent.frames['index-knowledges'] && parent.frames['index-knowledges'].$('#search').trigger('click');
                        parent.frames['index-videos'] && parent.frames['index-videos'].$('#search').trigger('click');
                        parent.frames['index-files'] && parent.frames['index-files'].$('#search').trigger('click');
                        parent.frames['index-rules'] && parent.frames['index-rules'].$('#search').trigger('click');
                        parent.tab.removeSelectedTabItem();
                    } else {
                        $.dialog.tips('更新失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#gotoedit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('NEWS_UPDATE')) {
                $('#toolTop').html(btn_edit);
                $('#content, #watch').hide();
                ue.setContent(originalData.content);
                $('#editor').show();
            }
        });
    },
    getPostData: function() {
        var data = {
            id: originalData.id,
            type: originalData.type,
            videotype: videotypeCombo.getValue() || 0,
            department: $.trim($('#departmentFselect .fs-input').val()),
            title: $.trim($('#title').val()),
            beginName: $.trim($('#beginName').val()),
            beginDate: $.trim($('#beginDate').val()),
            content: ue.getContent()
        };
        return data;
    },
    doWatchinfo: function() {
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
        async function doWatch() {
            for (var i = 0; i < 86400; i++) {
                Public.ajaxGet('/Base/News/getWatchinfo', {
                    id: originalData.id
                }, function(a) {
                    if (200 === a.status) {
                        originalData.watchinfo = a.data;
                        var div = '';
                        var isExist = false;
                        $.each(a.data, function(k, v) {
                            if (v.uId == defaultPage.SYSTEM.uId) {
                                isExist = true;
                                originalData.watchinfo[k].time = parseInt(originalData.watchinfo[k].time) + (i > 0 ? 10 : 0);
                                originalData.watchinfo[k].leave = (new Date).format('yyyy-MM-dd hh:mm:ss');
                            };
                            div += '<div class="mt10 pl10 pr10 h28 lh28 tl bde"><div class="fl w60">' + v.name + '</div><div class="fl w100">已累计观看</div><div class="fl w100 tr">' + v.time + ' 秒</div><div class="fr">最后观看时间 ' + v.leave + '</div></div>';
                        });
                        if (!isExist) {
                            originalData.watchinfo.push({uId: defaultPage.SYSTEM.uId, name: defaultPage.SYSTEM.uName, time: 0, leave: (new Date).format('yyyy-MM-dd hh:mm:ss')});
                        }
                        $('#watchinfo').html(div);
                    } else {
                        $.dialog.tips(a.msg, 2);
                        originalData.watchinfo = [{uId: defaultPage.SYSTEM.uId, name: defaultPage.SYSTEM.uName, time: 0, leave: (new Date).format('yyyy-MM-dd hh:mm:ss')}];
                        var div = '<div class="mt10 pl10 pr10 h28 lh28 tl bde"><div class="fl w60">' + defaultPage.SYSTEM.uName + '</div><div class="fl w100">已累计观看</div><div class="fl w100 tr">0 秒</div><div class="fr">最后观看时间 ' + (new Date).format('yyyy-MM-dd hh:mm:ss') + '</div></div>';
                        $('#watchinfo').html(div);
                    }
                    Public.ajaxPost('/Base/News/updateWatchinfo', {
                        id: originalData.id,
                        watchinfo: originalData.watchinfo
                    }, function(res) {
                        if (-1 === res.status) {
                            $.dialog.tips('更新观看信息失败！' + res.msg, 2);
                        }
                    });
                });
                await sleep(10000);
            }
        }
        doWatch();
    },
    addVideoType: function() {
        $.dialog({
            title: '新增视频类别',
            content: 'url:/Uri/category_manage',
            data: {
                oper: 'add',
                typeNumber: 'videotype',
                callback: function(a, b, c) {
                    Public.ajaxGet('/Base/Category/list?typeNumber=videotype', {}, function(res) {
                        if (res && 200 == res.status) {
                            var e = res.data.rows;
                        } else {
                            var e = [];
                            $.dialog.tips('获取视频类别失败！' + res.msg, 2);
                        }
                        categoryCombo.loadData(e, '-1', !1);
                        categoryCombo.selectByValue(a.id);
                    });
                    c && c.api.close();
                }
            },
            width: 600,
            height: 60,
            max: !1,
            min: !1,
            cache: !1,
            lock: !0
        });
    }
};
$(function() {
    if (urlParam.id) {
        Public.ajaxGet('/Base/News/query', {
            id: urlParam.id
        }, function(res) {
            if (res && 200 == res.status) {
                originalData = res.data;
                THISPAGE.init(originalData);
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    } else {
        originalData = {
            id: -1,
            type: parseInt(urlParam.type) || 1
        };
        THISPAGE.init(originalData);
    }
});
