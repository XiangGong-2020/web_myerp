var defaultPage = Public.getDefaultPage();
var today_lunar = calendar.solar2lunar();
var personalData = [];
var timer_countdown;
var countdownStop = !1;
if (typeof xcsoft == "undefined") {
    var xcsoft = new function() { };
    xcsoft.countdown = function(a, b, c) {
        var d = new Object();
        var e = 0;
        if (typeof a == "object") {
            var f = parseInt(new Date().getTime() / 1000);
            var g = a.startTime ? parseInt(a.startTime) : 0;
            g = g == 0 ? f : g;
            var h = a.endTime;
            var x = g - f;
            d.decimal = parseInt(a.msec ? a.msec : 0);
        } else {
            var f = 0;
            var x = 0;
            d.decimal = 0;
            var h = a;
            var i = h.toString();
            if (i.indexOf('.') > 0) {
                d.decimal = i.split('.')[1];
                if (d.decimal > 3) {
                    d.decimal = 3;
                }
            }
        }
        d.time = h;
        d.finish = false;
        if (isNaN(h)) {
            var j = h.substring(0, 19);
            j = j.replace(/-/g, '/');
            d.time = new Date(j).getTime() / 1000;
        }
        var k = d.decimal == 0 ? 100 : 100;
        d.day = 0;
        d.hour = 0;
        d.minute = 0;
        d.second = 0;
        d.t = setInterval(function() {
            e = new Date().getTime();
            f = parseInt(e / 1000 + x);
            var l = d.time - f;
            if (l <= 0) {
                e = 0;
                l = 0;
            }
            if (l >= 0) {
                d = xcsoft.getTimeObject(d, l);
            }
            if (d.decimal == 1) {
                d.msecZero = d.msec = parseInt(10 - e % 1000 / 100);
                if (d.msec == 10 || l == 0) {
                    d.msecZero = d.msec = 0;
                }
            } else if (d.decimal == 2) {
                d.msecZero = d.msec = parseInt(100 - e % 1000 / 10);
                if (d.msec < 10) {
                    d.msecZero = '0' + d.msec;
                } else if (d.msec == 100 || l == 0) {
                    d.msec = 0;
                    d.msecZero = '0' + d.msec;
                }
            } else {
                d.msecZero = d.msec = parseInt(1000 - e % 1000);
                if (d.msec < 10) {
                    d.msecZero = '00' + d.msec;
                } else if (d.msec < 100) {
                    d.msecZero = '0' + d.msec;
                } else if (d.msec == 1000 || l == 0) {
                    d.msec = 0;
                    d.msecZero = '00' + d.msec;
                }
            }
            if (b) {
                b(d);
            }
            if (l <= 0 || d.stop == true || countdownStop == true) {
                clearInterval(d.t);
                d.finish = true;
                if (c) c(d);
            }
        }, k);
    };
    xcsoft.countup = function(c, d) {
        if (!c) {
            var e = new Date().getTime();
            c = parseInt(e / 1000);
        } else if (isNaN(c)) {
            c = c.replace(/-/g, '/');
            var e = new Date(c).getTime();
            c = parseInt(e / 1000);
        }
        var f = new Object();
        f.time = c;
        f.t = setInterval(function() {
            var a = new Date().getTime();
            nowtime = parseInt(a / 1000);
            var l = nowtime - c;
            f = xcsoft.getTimeObject(f, l);
            if (d) var b = d(f);
            if (b === true || countdownStop == true) {
                clearTimeout(f.t);
            }
        }, 100);
    };
    xcsoft.getTimeObject = function(a, l) {
        var b = 60;
        var c = b * b;
        var d = 24 * c;
        a.days = Math.floor(l / d);
        a.year = Math.floor(a.days / 365);
        a.day = Math.floor(a.days % 365);
        a.hour = Math.floor(l % d / c);
        a.minute = Math.floor((l - (a.days * d + a.hour * c)) / b);
        a.second = Math.floor(l % (b));
        a.dayZero = a.day < 10 ? '0' + a.day : a.day;
        a.daysZero = a.days < 10 ? '0' + a.days : a.days;
        a.hourZero = a.hour < 10 ? '0' + a.hour : a.hour;
        a.minuteZero = a.minute < 10 ? '0' + a.minute : a.minute;
        a.secondZero = a.second < 10 ? '0' + a.second : a.second;
        return a;
    };
}
var THISPAGE = {
    init: function() {
        THISPAGE.initDomLeft();
        THISPAGE.initDomRight();
        THISPAGE.initEvent();
    },
    initDomLeft: function() {
        if (defaultPage.SYSTEM.locked && !defaultPage.SYSTEM.unlock) {
<<<<<<< HEAD
            $('.col-main-left').css({'width': 'calc(100% - 430px)', 'height': 'calc(100vh - 20px)', 'margin': '10px', 'text-align': 'center', 'background': 'url("../statics/img/bg-zhiban.png?v=2026102701") center bottom no-repeat #fff', 'border-radius': '3px'}).html('<div class="mt50 mb20 f30 fb tc blue">当前处于值班模式</div><div id="unlock" class="dib p10 hover-red b-r-3">切换到正常模式</div>');
=======
            $('.col-main-left').css({'width': 'calc(100% - 430px)', 'height': 'calc(100vh - 20px)', 'margin': '10px', 'text-align': 'center', 'background': 'url("../statics/img/bg-zhiban.png?v=2026101403") center bottom no-repeat #fff', 'border-radius': '3px'}).html('<div class="mt50 mb20 f30 fb tc blue">当前处于值班模式</div><div id="unlock" class="dib p10 hover-red b-r-3">切换到正常模式</div>');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        } else {
            if (defaultPage.SYSTEM.user.roleId == 1) {
                $('#salesTargetChart, #invoiceBackChart').removeClass('dn').addClass('link-tab-50');
                Public.ajaxGet('/Base/Home/salesTargetChartData', {}, function(res) {
                    if (res.status === 200) {
                        THISPAGE.loadChartData(res.data);
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                });
                Public.ajaxGet('/Base/Home/invoiceBackChartData', {}, function(res) {
                    if (res.status === 200) {
                        THISPAGE.loadChartData(res.data);
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                });
            } else {
                if ($.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) !== -1 || $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) !== -1) {
                    $('#achievementChart, #salesTargetChart, #invoiceBackChart').removeClass('dn').addClass('link-tab-33');
                    Public.ajaxGet('/Base/Home/achievementChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                    Public.ajaxGet('/Base/Home/salesTargetChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                    Public.ajaxGet('/Base/Home/invoiceBackChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                } else if (defaultPage.SYSTEM.user.department == '销售部') {
                    $('#evaluateChart, #achievementChart, #salesTargetChart').removeClass('dn').addClass('link-tab-33');
                    Public.ajaxGet('/Base/Home/evaluateChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                    Public.ajaxGet('/Base/Home/achievementChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                    Public.ajaxGet('/Base/Home/salesTargetChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                } else if (defaultPage.SYSTEM.user.department == '采购部') {
                    $('#evaluateChart, #achievementChart, #invoiceBackChart').removeClass('dn').addClass('link-tab-33');
                    Public.ajaxGet('/Base/Home/evaluateChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                    Public.ajaxGet('/Base/Home/achievementChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                    Public.ajaxGet('/Base/Home/invoiceBackChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                } else {
                    $('#evaluateChart, #achievementChart').removeClass('dn').addClass('link-tab-50');
                    Public.ajaxGet('/Base/Home/evaluateChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                    Public.ajaxGet('/Base/Home/achievementChartData', {}, function(res) {
                        if (res.status === 200) {
                            THISPAGE.loadChartData(res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                }
            }
            Public.ajaxGet('/Base/Home/alertData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.loadNoticeData(res.data);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
            Public.ajaxGet('/Base/Home/countdownData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.loadCountdownData(res.data);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
            THISPAGE.initGrid();
        }
    },
    initDomRight: function() {
        Public.ajaxGet('/Base/Home/personalData', {}, function(res) {
            if (res.status === 200) {
                personalData = res.data;
                THISPAGE.loadPersonalData(res.data);
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
        Public.ajaxGet('/Base/Home/noteData', {}, function(res) {
            if (res.status === 200) {
                THISPAGE.loadNoteData(res.data);
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    },
    initGrid: function() {
        $('#grid1').jqGrid({
            url: '/Base/Home/goodsRecentNotice',
            postData: {type: 1},
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'title',
                label: '',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis def'
            }, {
                name: '',
                label: ' ',
                width: 300,
                fixed: !0,
                classes: 'gray ui-ellipsis def',
                formatter: function(a, b, c) {
                    return c.uName + ' ' + c.date + (c.qty ? ('<span class="fr">' + c.qty + '</span>') : '');
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: $('#goodsRecentNotice').width(),
            height: 300,
            scrollOffset: 0,
            altRows: !1,
            gridview: !0,
            shrinkToFit: !0,
            forceFit: !1,
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page1',
            page: 1,
            rowNum: 10,
            viewrecords: !1,
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                $(this).closest('.ui-jqgrid-view').find('div.ui-jqgrid-hdiv').hide();
            }
        });
        $('#grid5').jqGrid({
            url: '/Base/News/list',
            datatype: 'json',
            postData: {type: 3},
            colModel: [{
                name: 'id',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'title',
                label: '标题',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'beginDate',
                label: '发布时间',
                width: 114,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'type',
                label: '类型',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: $('#newsNotice').width(),
            height: 300,
            scrollOffset: 0,
            altRows: !1,
            gridview: !0,
            shrinkToFit: !0,
            forceFit: !1,
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page5',
            page: 1,
            rowNum: 10,
            viewrecords: !1,
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                $(this).closest('.ui-jqgrid-view').find('div.ui-jqgrid-hdiv').hide();
            },
            onCellSelect: function(a, b) {
                if (b == 1) {
                    parent.tab.addTabItem({
                        tabid: 'news' + a,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>内部资讯详情',
                        url: '/Uri/news_manage?id=' + a
                    });
                }
            }
        });
    },
    initEvent: function() {
        /* 批量绑定页签打开事件 */
        Public.pageTab();
        /* 值班模式切换到正常模式事件 */
        $('.col-main-left').on('click', '#unlock', function(a) {
            a.preventDefault();
            if (Business.verifyRight('MODEL_UNLOCK')) {
                defaultPage.SYSTEM.unlock = !0;
                defaultPage.THISPAGE.initDom();
                defaultPage.THISPAGE.initNav();
                defaultPage.THISPAGE.initNormalEvent();
                window.location.reload();
            }
        });
        /* 关注事件刷新事件 */
        $('#notice-refresh-btn').on('click', function(a) {
            a.preventDefault();
            Public.ajaxGet('/Base/Home/alertData', {}, function(res) {
                if (res.status === 200) {
                    $('.link-large-con li').addClass('dn');
                    THISPAGE.loadNoticeData(res.data);
                    $('#notice-refresh-msg').html('刷新成功！').show(100).delay(1000).hide(100);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        });
        /* 图表刷新事件 */
        $('#userchart-refresh-btn-1').on('click', function(a) {
            a.preventDefault();
            Public.ajaxGet('/Base/Home/evaluateChartData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.reloadChartData(res.data);
                    $('#userchart-refresh-msg-1').html('刷新成功！').show(100).delay(1000).hide(100);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        });
        $('#userchart-refresh-btn-2').on('click', function(a) {
            a.preventDefault();
            Public.ajaxGet('/Base/Home/achievementChartData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.reloadChartData(res.data);
                    $('#userchart-refresh-msg-2').html('刷新成功！').show(100).delay(1000).hide(100);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        });
        $('#userchart-refresh-btn-3').on('click', function(a) {
            a.preventDefault();
            Public.ajaxGet('/Base/Home/salesTargetChartData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.reloadChartData(res.data);
                    $('#userchart-refresh-msg-3').html('刷新成功！').show(100).delay(1000).hide(100);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        });
        $('#userchart-refresh-btn-4').on('click', function(a) {
            a.preventDefault();
            Public.ajaxGet('/Base/Home/invoiceBackChartData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.reloadChartData(res.data);
                    $('#userchart-refresh-msg-4').html('刷新成功！').show(100).delay(1000).hide(100);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        });
        /* 限期计时 点击事件 */
        $('#bd').on('click', '.countdown-line', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_QUERY')) {
                var id = $(this).data('id');
                id && parent.tab.addTabItem({
                    tabid: 'sales-salesContract' + id,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单',
                    url: '/Uri/salesContract_manage?id=' + id
                });
            }
        });
        /* 限期计时 刷新事件 */
        $('#countdownRefreshBtn').on('click', function(a) {
            Public.ajaxGet('/Base/Home/countdownData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.loadCountdownData(res.data);
                    $(a.currentTarget).next('.link-msg').html('刷新成功！').show(100).delay(1000).hide(100);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        });
        // 限期计时 每隔10分钟自动执行一次
        timer_countdown = setInterval(() => {
            Public.ajaxGet('/Base/Home/countdownData', {}, function(res) {
                if (res.status == 200) {
                    THISPAGE.loadCountdownData(res.data);
                }
            });
        }, 600000);
        /* 表格事件 */
        var $wrapper = $('.tab-wrapper');
        var $tabMenu = $wrapper.find('.tab-menu .tab-tit');
        var $allTabs = $wrapper.find('.tab-content > .col-tab');
        $tabMenu.each(function(i) {
            $(this).attr('data-tab', 'tab' + (i + 1));
        });
        $allTabs.each(function(i) {
            $(this).attr('data-tab', 'tab' + (i + 1));
        });
        $tabMenu.on('click', function() {
            var dataTab = $(this).data('tab');
            var $getWrapper = $(this).closest($wrapper);
            $getWrapper.find($tabMenu).removeClass('active');
            $(this).addClass('active');
            $getWrapper.find($allTabs).hide();
            $getWrapper.find($allTabs).filter('[data-tab=' + dataTab + ']').show();
            var tabid = parseInt(dataTab.replace('tab', ''));
            $('#grid' + tabid).jqGrid({
                url: '/Base/Home/goodsRecentNotice',
                postData: {type: tabid},
                datatype: 'json',
                colModel: [{
                    name: 'id',
                    label: 'ID',
                    width: 0,
                    hidden: !0
                }, {
                    name: 'title',
                    label: '标题',
                    width: 100,
                    title: !0,
                    classes: 'ui-ellipsis def'
                }, {
                    name: '',
                    label: ' ',
                    width: tabid == 4 ? 240 : 300,
                    fixed: !0,
                    classes: 'gray ui-ellipsis def',
                    formatter: function(a, b, c) {
                        return c.uName + ' ' + c.date + (c.qty ? ('<span class="fr">' + c.qty + '</span>') : '');
                    }
                }],
                cmTemplate: {sortable: !1, title: !1},
                jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
                width: $('#goodsRecentNotice').width(),
                height: 300,
                scrollOffset: 0,
                altRows: !1,
                gridview: !0,
                shrinkToFit: !0,
                forceFit: !1,
                sortname: 'id',
                sortorder: 'DESC',
                pager: '#page' + tabid,
                page: 1,
                rowNum: 10,
                viewrecords: !1,
                loadError: function(a, b) {
                    $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
                },
                gridComplete: function() {
                    $(this).closest('.ui-jqgrid-view').find('div.ui-jqgrid-hdiv').hide();
                }
            });
        });
        /* 表格刷新事件 */
        $('#goodsRecentNotice #grid-refresh-btn').on('click', function(a) {
            a.preventDefault();
            var tabNum = $('.tab-wrapper').find('.tab-menu div.active').data('tab').replace('tab', '');
            $('#goodsRecentNotice #grid' + tabNum).jqGrid().trigger('reloadGrid');
            $('#goodsRecentNotice #grid-refresh-msg').html('刷新成功！').show(100).delay(1000).hide(100);
        });
        $('#newsNotice #grid-refresh-btn').on('click', function(a) {
            a.preventDefault();
            $('#newsNotice #grid5').jqGrid().trigger('reloadGrid');
            $('#newsNotice #grid-refresh-msg').html('刷新成功！').show(100).delay(1000).hide(100);
        });
        /* 表格查询事件 */
        $('#goodsRecentNotice #search').click(function() {
            var dataTab = $('#goodsRecentNotice').find('.active').data('tab');
            var tabid = parseInt(dataTab.replace('tab', ''));
            $('#goodsRecentNotice #grid' + tabid).jqGrid('setGridParam', {
                postData: {
                    type: tabid,
                    skey: $.trim($('#goodsRecentNotice #skey').val()).split(/[ ,，]+/).toString()
                },
                page: 1
            }).trigger('reloadGrid');
        });
        $('#newsNotice #search').click(function() {
            $('#newsNotice #grid5').jqGrid('setGridParam', {
                postData: {
                    type: 3,
                    skey: $.trim($('#newsNotice #skey').val()).split(/[ ,，]+/).toString()
                },
                page: 1
            }).trigger('reloadGrid');
        });
        /* 用户区事件 */
        $('#user-info-refresh-btn').on('click', function(a) {
            a.preventDefault();
            Public.ajaxGet('/Base/Home/personalData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.loadPersonalData(res.data);
                    $('#user-info-refresh-msg').html('刷新成功！').show(100).delay(1000).hide(100);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        });
        $('.saler-duty-choose').on('click', function(a) {
            a.preventDefault();
            $.dialog({
                title: '选择今日值班内容',
                content: 'url:/Uri/saler_duty_choose',
                data: {
                    uId: defaultPage.SYSTEM.uId,
                    uName: defaultPage.SYSTEM.uName
                },
                width: 1250,
                height: 800,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('.user-position-info').on('click', '#superiors .user-position-staff-con', function(a) {
            var data = personalData.myInfo.superiorsInfo;
            $('.staff-side ').html(
                '<i class="staff-side-close iconfont icon-shanchu" title="关闭"></i>'
<<<<<<< HEAD
                + '<img class="staff-side-avatar" src="' + data.avatar + '?v=2026102701">'
=======
                + '<img class="staff-side-avatar" src="' + data.avatar + '?v=2026101403">'
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                + '<div class="staff-side-avatar-right">'
                + '<div class="staff-side-tit">姓名</div><div class="staff-side-text">' + data.name + '</div>'
                + '<div class="staff-side-tit">成长等级</div><div class="staff-side-text">' + data.level + '</div>'
                + '<div class="staff-side-tit">所在部门</div><div class="staff-side-text">' + data.department + '</div>'
                + '<div class="staff-side-tit">所处职位</div><div class="staff-side-text">' + data.position + '</div>'
                + '<div class="staff-side-tit">上级主管</div><div class="staff-side-text">' + (data.superiorsName || '无') + '</div>'
                + '</div>'
                + '<div class="staff-side-tit">常用手机</div><div class="staff-side-text">' + data.mobile + '</div>'
                + '<div class="staff-side-tit">常用微信</div><div class="staff-side-text">' + data.wechat + '</div>'
                + '<div class="staff-side-tit">岗位职责</div><div class="staff-side-textarea">' + data.duty + '</div>'
            ).css('right', '0');
        });
        $('.user-position-info').on('click', '#members .user-position-staff-con', function(a) {
            var id = parseInt($(this).data('id'));
            var data = personalData.myInfo.membersInfo[id];
            $('.staff-side ').html(
                '<i class="staff-side-close iconfont icon-shanchu"></i>'
<<<<<<< HEAD
                + '<img class="staff-side-avatar" src="' + data.avatar + '?v=2026102701">'
=======
                + '<img class="staff-side-avatar" src="' + data.avatar + '?v=2026101403">'
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                + '<div class="staff-side-avatar-right">'
                + '<div class="staff-side-tit">姓名</div><div class="staff-side-text">' + data.name + '</div>'
                + '<div class="staff-side-tit">成长等级</div><div class="staff-side-text">' + data.level + '</div>'
                + '<div class="staff-side-tit">所在部门</div><div class="staff-side-text">' + data.department + '</div>'
                + '<div class="staff-side-tit">所处职位</div><div class="staff-side-text">' + data.position + '</div>'
                + '<div class="staff-side-tit">上级主管</div><div class="staff-side-text">' + (data.superiorsName || '无') + '</div>'
                + '</div>'
                + '<div class="staff-side-tit">常用手机</div><div class="staff-side-text">' + data.mobile + '</div>'
                + '<div class="staff-side-tit">常用微信</div><div class="staff-side-text">' + data.wechat + '</div>'
                + '<div class="staff-side-tit">岗位职责</div><div class="staff-side-textarea">' + data.duty + '</div>'
            ).css('right', '0');
        });
        $('.staff-side').on('click', '.staff-side-close', function() {
            $(this).parent().css('right', '-500px');
        });
        $('.user-position-info').on('click', '#toUserStaffMap', function(a) {
            parent.tab.addTabItem({
                tabid: 'personnel-userStaffMap',
                text: "<i class='iconfont icon-yonghuzu f12 mr5'></i>全员速览",
                url: '/Uri/user_staff_map'
            });
        });
        $('.user-note-nav').on('click', '.add', function(a) {
            var id = $(this).data('id');
            $.dialog({
                title: '新增私人备忘',
                content: 'url:/Uri/note_manage',
                data: {
                    oper: 'add',
                    rowId: id,
                    callback: function(a, b, c, d) {
                        parent.frames['index-note'] && parent.frames['index-note'].$('#search').trigger('click');
                        d == 1 ? c.api.close() : c.THISPAGE.resetForm();
                        THISPAGE.initDomRight();
                    }
                },
                width: 1250,
                height: 420,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('.user-note-nav').on('click', '.more', function(a) {
            parent.tab.addTabItem({
                tabid: 'index-note',
                text: "<i class='iconfont icon-beiwang f12 mr5'></i>私人备忘",
                url: '/Uri/note_list'
            });
        });
        $('.user-note-nav').on('click', '.refresh', function(a) {
            a.preventDefault();
            Public.ajaxGet('/Base/Home/noteData', {}, function(res) {
                if (res.status === 200) {
                    THISPAGE.loadNoteData(res.data);
                    $('#note-refresh-msg').html('刷新成功！').show(100).delay(1000).hide(100);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        });
        $('.user-note-con').on('click', '.user-note-item', function(a) {
            var id = $(this).data('id');
            $.dialog({
                title: '编辑私人备忘',
                content: 'url:/Uri/note_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: function(a, b, c, d) {
                        parent.frames['index-note'] && parent.frames['index-note'].$('#search').trigger('click');
                        d == 1 ? c.api.close() : c.THISPAGE.resetForm();
                        THISPAGE.initDomRight();
                    }
                },
                width: 1250,
                height: 420,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        /* 窗口缩放事件 */
        $(window).on('resize', function() {
            THISPAGE.chart_1 && THISPAGE.chart_1.resize();
            THISPAGE.chart_2 && THISPAGE.chart_2.resize();
            THISPAGE.chart_3 && THISPAGE.chart_3.resize();
            THISPAGE.chart_4 && THISPAGE.chart_4.resize();
            $('#grid1').jqGrid('setGridWidth', $('#goodsRecentNotice').width());
            $('#grid2').jqGrid('setGridWidth', $('#goodsRecentNotice').width());
            $('#grid3').jqGrid('setGridWidth', $('#goodsRecentNotice').width());
            $('#grid4').jqGrid('setGridWidth', $('#goodsRecentNotice').width());
            $('#grid5').jqGrid('setGridWidth', $('#newsNotice').width());
        });
    },
    loadChartData: function(a) {
        if (a.evaluateData) { // 用户图表 考评趋势
            THISPAGE.chart_1_config = {
                legend: {
                    top: '0',
                    left: 'center',
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    top: '40',
                    left: '20',
                    right: '20',
                    bottom: '0',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    show: false,
                    data: []
                },
                yAxis: {
                    type: 'value',
                    show: true
                },
                series: [
                    {
                        name: '考评得分',
                        type: 'bar',
                        data: []
                    }
                ]
            };
            $.each(a.evaluateData, function(k, v) {
                THISPAGE.chart_1_config.xAxis.data.push(v['billNo'].slice(2, 6) + '年' + v['billNo'].slice(6, 8) + '月');
                THISPAGE.chart_1_config.series[0].data.push(Number(v['evaluateScore']).toFixed(0));
            });
            if (THISPAGE.chart_1) {
                THISPAGE.chart_1.dispose();
            }
            THISPAGE.chart_1 = echarts.init(document.getElementById('evaluateChartCanvas'));
            THISPAGE.chart_1.setOption(THISPAGE.chart_1_config);
        }
        if (a.achievementData) { // 用户图表 绩效趋势
            THISPAGE.chart_2_config = {
                legend: {
                    top: '0',
                    left: 'center',
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    top: '40',
                    left: '20',
                    right: '20',
                    bottom: '0',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    show: false,
                    data: []
                },
                yAxis: {
                    type: 'value',
                    show: true
                },
                series: [
                    {
                        name: '绩效指数',
                        type: 'bar',
                        data: []
                    }
                ]
            };
            $.each(a.achievementData, function(k, v) {
                THISPAGE.chart_2_config.xAxis.data.push(v['billNo'].slice(2, 6) + '年' + v['billNo'].slice(6, 8) + '月');
                THISPAGE.chart_2_config.series[0].data.push(Number(v['sumSalary']).toFixed(0));
            });
            if (THISPAGE.chart_2) {
                THISPAGE.chart_2.dispose();
            }
            THISPAGE.chart_2 = echarts.init(document.getElementById('achievementChartCanvas'));
            THISPAGE.chart_2.setOption(THISPAGE.chart_2_config);
        }
        if (a.salesTargetData) { // 用户图表 销售目标
            THISPAGE.chart_3_config = {
                legend: {
                    top: '0',
                    left: 'center',
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    top: '40',
                    left: '20',
                    right: '20',
                    bottom: '0',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    show: false,
                    data: []
                },
                yAxis: {
                    type: 'value',
                    show: true
                },
                series: [
                    {
                        name: '目标金额',
                        type: 'bar',
                        data: []
                    }, {
                        name: '完成金额',
                        type: 'bar',
                        data: []
                    }
                ]
            };
            $.each(a.salesTargetData, function(k, v) {
                THISPAGE.chart_3_config.xAxis.data.push(v['year'] + '年' + (v['month'] < 10 ? ('0' + v['month']) : v['month']) + '月');
                THISPAGE.chart_3_config.series[0].data.push(Number(v['targetAmount']).toFixed(0));
                THISPAGE.chart_3_config.series[1].data.push(Number(v['curAmount']).toFixed(0));
            });
            if (THISPAGE.chart_3) {
                THISPAGE.chart_3.dispose();
            }
            THISPAGE.chart_3 = echarts.init(document.getElementById('salesTargetChartCanvas'));
            THISPAGE.chart_3.setOption(THISPAGE.chart_3_config);
        }
        if (a.invoiceBackData) { // 用户图表 采购回票
            THISPAGE.chart_4_config = {
                legend: {
                    top: '0',
                    left: 'center',
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    top: '40',
                    left: '20',
                    right: '20',
                    bottom: '0',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    show: false,
                    data: []
                },
                yAxis: {
                    type: 'value',
                    show: true
                },
                series: [
                    {
                        name: '采购支出',
                        type: 'bar',
                        data: []
                    }, {
                        name: '采购回票',
                        type: 'bar',
                        data: []
                    }
                ]
            };
            $.each(a.invoiceBackData, function(k, v) {
                THISPAGE.chart_4_config.xAxis.data.push(v['year'] + '年' + v['month'] + '月');
                THISPAGE.chart_4_config.series[0].data.push(Number(v['purPaidAmount']).toFixed(0));
                THISPAGE.chart_4_config.series[1].data.push(Number(v['purInvoiceAmount']).toFixed(0));
            });
            if (THISPAGE.chart_4) {
                THISPAGE.chart_4.dispose();
            }
            THISPAGE.chart_4 = echarts.init(document.getElementById('invoiceBackChartCanvas'));
            THISPAGE.chart_4.setOption(THISPAGE.chart_4_config);
        }
        //返回完成状态
        return !0;
    },
    reloadChartData: function(a) {
        if (a.evaluateData) { // 用户图表 考评趋势
            THISPAGE.chart_1_config.xAxis.data = [];
            THISPAGE.chart_1_config.series[0].data = [];
            $.each(a.evaluateData, function(k, v) {
                THISPAGE.chart_1_config.xAxis.data.push(v['billNo'].slice(2, 6) + '年' + v['billNo'].slice(6, 8) + '月');
                THISPAGE.chart_1_config.series[0].data.push(Number(v['evaluateScore']).toFixed(0));
            });
            if (THISPAGE.chart_1) {
                THISPAGE.chart_1.dispose();
            }
            THISPAGE.chart_1 = echarts.init(document.getElementById('evaluateChartCanvas'));
            THISPAGE.chart_1.setOption(THISPAGE.chart_1_config);
        }
        if (a.achievementData) { // 用户图表 绩效趋势
            THISPAGE.chart_2_config.xAxis.data = [];
            THISPAGE.chart_2_config.series[0].data = [];
            $.each(a.achievementData, function(k, v) {
                THISPAGE.chart_2_config.xAxis.data.push(v['billNo'].slice(2, 6) + '年' + v['billNo'].slice(6, 8) + '月');
                THISPAGE.chart_2_config.series[0].data.push(Number(v['sumSalary']).toFixed(0));
            });
            if (THISPAGE.chart_2) {
                THISPAGE.chart_2.dispose();
            }
            THISPAGE.chart_2 = echarts.init(document.getElementById('achievementChartCanvas'));
            THISPAGE.chart_2.setOption(THISPAGE.chart_2_config);
        }
        if (a.salesTargetData) { // 用户图表 销售目标
            THISPAGE.chart_3_config.xAxis.data = [];
            THISPAGE.chart_3_config.series[0].data = [];
            THISPAGE.chart_3_config.series[1].data = [];
            $.each(a.salesTargetData, function(k, v) {
                THISPAGE.chart_3_config.xAxis.data.push(v['year'] + '年' + (v['month'] < 10 ? ('0' + v['month']) : v['month']) + '月');
                THISPAGE.chart_3_config.series[0].data.push(Number(v['targetAmount']).toFixed(0));
                THISPAGE.chart_3_config.series[1].data.push(Number(v['curAmount']).toFixed(0));
            });
            if (THISPAGE.chart_3) {
                THISPAGE.chart_3.dispose();
            }
            THISPAGE.chart_3 = echarts.init(document.getElementById('salesTargetChartCanvas'));
            THISPAGE.chart_3.setOption(THISPAGE.chart_3_config);
        }
        if (a.invoiceBackData) { // 用户图表 采购回票
            THISPAGE.chart_4_config.xAxis.data = [];
            THISPAGE.chart_4_config.series[0].data = [];
            THISPAGE.chart_4_config.series[1].data = [];
            $.each(a.invoiceBackData, function(k, v) {
                THISPAGE.chart_4_config.xAxis.data.push(v['year'] + '年' + v['month'] + '月');
                THISPAGE.chart_4_config.series[0].data.push(Number(v['purPaidAmount']).toFixed(0));
                THISPAGE.chart_4_config.series[1].data.push(Number(v['purInvoiceAmount']).toFixed(0));
            });
            if (THISPAGE.chart_4) {
                THISPAGE.chart_4.dispose();
            }
            THISPAGE.chart_4 = echarts.init(document.getElementById('invoiceBackChartCanvas'));
            THISPAGE.chart_4.setOption(THISPAGE.chart_4_config);
        }
        //返回完成状态
        return !0;
    },
    loadCountdownData: function(a) {
        countdownStop = !0;
        if (a.saleContactCountdownData1.length > 0 || a.saleContactCountdownData2.length > 0) {
            $('#saleContactCountdownData1').parent().parent().parent().removeClass('dn');
            $('#saleContactCountdownData2').parent().parent().parent().removeClass('dn');
            $('#saleContactCountdownData1').html('<div class="loading">计时启动中...</div>');
            $('#saleContactCountdownData2').html('<div class="loading">计时启动中...</div>');
            setTimeout(() => {
                $('#saleContactCountdownData1').html('');
                $('#saleContactCountdownData2').html('');
                if (a.saleContactCountdownData1.length > 0) {
                    $.each(a.saleContactCountdownData1, function(k, v) {
                        var $html = '<div class="countdown-line" data-id="' + v.id + '"><div class="fl pct15 h40 pl10 es poi" title="' + v.billNo + '">' + v.billNo + '</div><div class="fl pct20 h40 pl10 es poi" title="' + v.contactName + '">' + v.contactName + '</div><div class="fl pct15 h40 pl10 es poi" title="' + v.saler + '">' + v.saler + '</div><div class="fl pct15 h40 pl10 es poi">' + v.endDate + '</div><div id="saleContactCountdownData1' + v.id + '" class="countdown-timer"></div></div>';
                        $('#saleContactCountdownData1').append($html);
                        xcsoft.countdown(v.endDate, function(time) {
                            $('#saleContactCountdownData1' + v.id).html(
                                '<div class="fl w50 h30 lh30 tc white bg-red">仅剩余</div>'
                                + '<div class="fl h30 w30 tc white bg-rgba1"><div class="h16 lh20">' + time.day + '</div><div class="f-pct80 h14 lh12">天</div></div>'
                                + '<div class="fl h30 w30 tc white"><div class="h16 lh20">' + time.hourZero + '</div><div class="f-pct80 h14 lh12">小时</div></div>'
                                + '<div class="fl h30 w30 tc white bg-rgba1"><div class="h16 lh20">' + time.minuteZero + '</div><div class="f-pct80 h14 lh12">分</div></div>'
                                + '<div class="fl h30 w30 tc white"><div class="h16 lh20">' + time.secondZero + '</div><div class="f-pct80 h14 lh12">秒</div></div>'
                            ).removeClass('bg-red').addClass('bg-yellow');
                        }, function(time) {
                            // 计时结束后的操作
                            xcsoft.countup(v.endDate, function(time) {
                                $('#saleContactCountdownData1' + v.id).html(
                                    '<div class="fl w50 h30 lh30 tc white bg-red">已超期</div>'
                                    + '<div class="fl h30 w30 tc white bg-rgba1"><div class="h16 lh20">' + time.day + '</div><div class="f-pct80 h14 lh12">天</div></div>'
                                    + '<div class="fl h30 w30 tc white"><div class="h16 lh20">' + time.hourZero + '</div><div class="f-pct80 h14 lh12">小时</div></div>'
                                    + '<div class="fl h30 w30 tc white bg-rgba1"><div class="h16 lh20">' + time.minuteZero + '</div><div class="f-pct80 h14 lh12">分</div></div>'
                                    + '<div class="fl h30 w30 tc white"><div class="h16 lh20">' + time.secondZero + '</div><div class="f-pct80 h14 lh12">秒</div></div>'
                                ).removeClass('bg-yellow').addClass('bg-red');
                            });
                        });
                    });
                } else {
                    $('#saleContactCountdownData1').html('<div class="no-data"></div>');
                }
                if (a.saleContactCountdownData2.length > 0) {
                    $.each(a.saleContactCountdownData2, function(k, v) {
                        var $html = '<div class="countdown-line" data-id="' + v.id + '"><div class="fl pct15 h40 pl10 es poi" title="' + v.billNo + '">' + v.billNo + '</div><div class="fl pct20 h40 pl10 es poi" title="' + v.contactName + '">' + v.contactName + '</div><div class="fl pct15 h40 pl10 es poi" title="' + v.saler + '">' + v.saler + '</div><div class="fl pct15 h40 pl10 es poi">' + v.endDate + '</div><div id="saleContactCountdownData2' + v.id + '" class="countdown-timer"></div></div>';
                        $('#saleContactCountdownData2').append($html);
                        xcsoft.countdown(v.endDate, function(time) {
                            $('#saleContactCountdownData2' + v.id).html(
                                '<div class="fl w50 h30 lh30 tc white bg-red">仅剩余</div>'
                                + '<div class="fl h30 w30 tc white bg-rgba1"><div class="h16 lh20">' + time.day + '</div><div class="f-pct80 h14 lh12">天</div></div>'
                                + '<div class="fl h30 w30 tc white"><div class="h16 lh20">' + time.hourZero + '</div><div class="f-pct80 h14 lh12">小时</div></div>'
                                + '<div class="fl h30 w30 tc white bg-rgba1"><div class="h16 lh20">' + time.minuteZero + '</div><div class="f-pct80 h14 lh12">分</div></div>'
                                + '<div class="fl h30 w30 tc white"><div class="h16 lh20">' + time.secondZero + '</div><div class="f-pct80 h14 lh12">秒</div></div>'
                            ).removeClass('bg-red').addClass('bg-yellow');
                        }, function(time) {
                            // 计时结束后的操作
                            xcsoft.countup(v.endDate, function(time) {
                                $('#saleContactCountdownData2' + v.id).html(
                                    '<div class="fl w50 h30 lh30 tc white bg-red">已超期</div>'
                                    + '<div class="fl h30 w30 tc white bg-rgba1"><div class="h16 lh20">' + time.day + '</div><div class="f-pct80 h14 lh12">天</div></div>'
                                    + '<div class="fl h30 w30 tc white"><div class="h16 lh20">' + time.hourZero + '</div><div class="f-pct80 h14 lh12">小时</div></div>'
                                    + '<div class="fl h30 w30 tc white bg-rgba1"><div class="h16 lh20">' + time.minuteZero + '</div><div class="f-pct80 h14 lh12">分</div></div>'
                                    + '<div class="fl h30 w30 tc white"><div class="h16 lh20">' + time.secondZero + '</div><div class="f-pct80 h14 lh12">秒</div></div>'
                                ).removeClass('bg-yellow').addClass('bg-red');
                            });
                        });
                    });
                } else {
                    $('#saleContactCountdownData2').html('<div class="no-data"></div>');
                }
                countdownStop = !1;
            }, 500);
            // 返回完成状态
            return !0;
        } else {
            $('#saleContactCountdownData1').html('');
            $('#saleContactCountdownData2').html('');
            $('#saleContactCountdownData1').parent().parent().parent().addClass('dn');
            $('#saleContactCountdownData2').parent().parent().parent().addClass('dn');
            // 返回完成状态
            return !0;
        }
    },
    loadNoticeData: function(a) {
        // 处理关注提醒信息
        parent.$('.main-alert, .nav-alert').remove();
        if (a.invWarningNum > 0) {
            $('#invWarningNum').html(a.invWarningNum);
            parent.$('.item-inventory .main-nav').append('<div class="main-alert"></div>');
            parent.$('.inventory-invWarning').append('<div class="nav-alert">' + a.invWarningNum + '</div>');
        }
        if (a.taskNotice > 0) {
            $('#taskNotice').removeClass('dn').find('.info').html(a.taskNotice);
            parent.$('.item-index .main-nav').append('<div class="main-alert"></div>');
            parent.$('.index-task').append('<div class="nav-alert">' + a.taskNotice + '</div>');
        } else {
            $('#taskNotice').addClass('dn');
        }
        if (a.planNotice > 0) {
            $('#planNotice').removeClass('dn').find('.info').html(a.planNotice);
            parent.$('.item-index .main-nav').append('<div class="main-alert"></div>');
            parent.$('.index-plan').append('<div class="nav-alert">' + a.planNotice + '</div>');
        } else {
            $('#planNotice').addClass('dn');
        }
        if (a.dutyNotice > 0) {
            $('#dutyNotice').removeClass('dn').find('.info').html(a.dutyNotice);
            parent.$('.item-index .main-nav').append('<div class="main-alert"></div>');
            parent.$('.index-duty').append('<div class="nav-alert">' + a.dutyNotice + '</div>');
        } else {
            $('#dutyNotice').addClass('dn');
        }
        if (a.managePlanNotice > 0) {
            $('#managePlanNotice').removeClass('dn').find('.info').html(a.managePlanNotice);
            parent.$('.item-index .main-nav').append('<div class="main-alert"></div>');
            parent.$('.index-managePlan').append('<div class="nav-alert">' + a.managePlanNotice + '</div>');
        } else {
            $('#managePlanNotice').addClass('dn');
        }
        if (a.purReqNotice > 0) {
            $('#purReqNotice').removeClass('dn').find('.info').html(a.purReqNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseReq').append('<div class="nav-alert">' + a.purReqNotice + '</div>');
        } else {
            $('#purReqNotice').addClass('dn');
        }
        if (a.purContractReqNotice > 0) {
            $('#purContractReqNotice').removeClass('dn').find('.info').html(a.purContractReqNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseContractReq').append('<div class="nav-alert">' + a.purContractReqNotice + '</div>');
        } else {
            $('#purContractReqNotice').addClass('dn');
        }
        if (a.discountReqNotice > 0) {
            $('#discountReqNotice').removeClass('dn').find('.info').html(a.discountReqNotice);
            parent.$('.item-sales .main-nav').append('<div class="main-alert"></div>');
            parent.$('.sales-discountReq').append('<div class="nav-alert">' + a.discountReqNotice + '</div>');
        }
        if (a.quoteReqNotice > 0) {
            $('#quoteReqNotice').removeClass('dn').find('.info').html(a.quoteReqNotice);
            parent.$('.item-sales .main-nav').append('<div class="main-alert"></div>');
            parent.$('.sales-quoteReq').append('<div class="nav-alert">' + a.quoteReqNotice + '</div>');
        }
        if (a.produceKittingReqNotice > 0) {
            $('#produceKittingReqNotice').removeClass('dn').find('.info').html(a.produceKittingReqNotice);
            parent.$('.item-produce .main-nav').append('<div class="main-alert"></div>');
            parent.$('.produce-produceKittingReq').append('<div class="nav-alert">' + a.produceKittingReqNotice + '</div>');
        } else {
            $('#produceKittingReqNotice').addClass('dn');
        }
        if (a.payReqNotice > 0) {
            $('#payReqNotice').removeClass('dn').find('.info').html(a.payReqNotice);
            parent.$('.item-money .main-nav').append('<div class="main-alert"></div>');
            parent.$('.money-payReq').append('<div class="nav-alert">' + a.payReqNotice + '</div>');
        } else {
            $('#payReqNotice').addClass('dn');
        }
        if (a.expensesReqNotice > 0) {
            $('#expensesReqNotice').removeClass('dn').find('.info').html(a.expensesReqNotice);
            parent.$('.item-money .main-nav').append('<div class="main-alert"></div>');
            parent.$('.money-expensesReq').append('<div class="nav-alert">' + a.expensesReqNotice + '</div>');
        } else {
            $('#expensesReqNotice').addClass('dn');
        }
        if (a.invoiceErrNotice > 0) {
            $('#invoiceErrNotice').removeClass('dn').find('.info').html(a.invoiceErrNotice);
            parent.$('.item-money .main-nav').append('<div class="main-alert"></div>');
            parent.$('.money-invoiceErr').append('<div class="nav-alert">' + a.invoiceErrNotice + '</div>');
        } else {
            $('#invoiceErrNotice').addClass('dn');
        }
        if (a.goodsDevReqNotice > 0) {
            $('#goodsDevReqNotice').removeClass('dn').find('.info').html(a.goodsDevReqNotice);
            parent.$('.item-develop .main-nav').append('<div class="main-alert"></div>');
            parent.$('.develop-goodsDevReq').append('<div class="nav-alert">' + a.goodsDevReqNotice + '</div>');
        } else {
            $('#goodsDevReqNotice').addClass('dn');
        }
        if (a.goodsFileReqNotice > 0) {
            $('#goodsFileReqNotice').removeClass('dn').find('.info').html(a.goodsFileReqNotice);
            parent.$('.item-develop .main-nav').append('<div class="main-alert"></div>');
            parent.$('.develop-goodsFileReq').append('<div class="nav-alert">' + a.goodsFileReqNotice + '</div>');
        } else {
            $('#goodsFileReqNotice').addClass('dn');
        }
        if (a.poNotice > 0) {
            $('#poNotice').removeClass('dn').find('.info').html(a.poNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseOrder').append('<div class="nav-alert">' + a.poNotice + '</div>');
        } else {
            $('#poNotice').addClass('dn');
        }
        if (a.pcNotice > 0) {
            $('#pcNotice').removeClass('dn').find('.info').html(a.pcNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseContract').append('<div class="nav-alert">' + a.pcNotice + '</div>');
        } else {
            $('#pcNotice').addClass('dn');
        }
        if (a.pcFillNotice > 0) {
            $('#pcFillNotice').removeClass('dn').find('.info').html(a.pcFillNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseContractFill').append('<div class="nav-alert">' + a.pcFillNotice + '</div>');
        } else {
            $('#pcFillNotice').addClass('dn');
        }
        if (a.pcLostNotice > 0) {
            $('#pcLostNotice').removeClass('dn').find('.info').html(a.pcLostNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseContractLost').append('<div class="nav-alert">' + a.pcLostNotice + '</div>');
        } else {
            $('#pcLostNotice').addClass('dn');
        }
        if (a.pcModifyPurAmountNotice > 0) {
            $('#pcModifyPurAmountNotice').removeClass('dn').find('.info').html(a.pcModifyPurAmountNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseContractModifyPurAmount').append('<div class="nav-alert">' + a.pcModifyPurAmountNotice + '</div>');
        } else {
            $('#pcModifyPurAmountNotice').addClass('dn');
        }
        if (a.purSuggestNotice > 0) {
            $('#purSuggestNotice').removeClass('dn').find('.info').html(a.purSuggestNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseSuggest').append('<div class="nav-alert">' + a.purSuggestNotice + '</div>');
        } else {
            $('#purSuggestNotice').addClass('dn');
        }
        if (a.purQCNotice > 0) {
            $('#purQCNotice').removeClass('dn').find('.info').html(a.purQCNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseQC').append('<div class="nav-alert">' + a.purQCNotice + '</div>');
        } else {
            $('#purQCNotice').addClass('dn');
        }
        if (a.purQCBackNotice > 0) {
            $('#purQCBackNotice').removeClass('dn').find('.info').html(a.purQCBackNotice);
            parent.$('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            parent.$('.purchase-purchaseQCBack').append('<div class="nav-alert">' + a.purQCBackNotice + '</div>');
        } else {
            $('#purQCBackNotice').addClass('dn');
        }
        if (a.soNotice > 0) {
            $('#soNotice').removeClass('dn').find('.info').html(a.soNotice);
            parent.$('.item-sales .main-nav').append('<div class="main-alert"></div>');
            parent.$('.sales-salesOrder').append('<div class="nav-alert">' + a.soNotice + '</div>');
        } else {
            $('#soNotice').addClass('dn');
        }
        if (a.scNotice > 0) {
            $('#scNotice').removeClass('dn').find('.info').html(a.scNotice);
            parent.$('.item-sales .main-nav').append('<div class="main-alert"></div>');
            parent.$('.sales-salesContract').append('<div class="nav-alert">' + a.scNotice + '</div>');
        } else {
            $('#scNotice').addClass('dn');
        }
        if (a.receiptNotice > 0) {
            $('#receiptNotice').removeClass('dn').find('.info').html(a.receiptNotice);
            parent.$('.item-money .main-nav').append('<div class="main-alert"></div>');
            parent.$('.money-receipt').append('<div class="nav-alert">' + a.receiptNotice + '</div>');
        } else {
            $('#receiptNotice').addClass('dn');
        }
        if (a.paymentNotice > 0) {
            $('#paymentNotice').removeClass('dn').find('.info').html(a.paymentNotice);
            parent.$('.item-money .main-nav').append('<div class="main-alert"></div>');
            parent.$('.money-payment').append('<div class="nav-alert">' + a.paymentNotice + '</div>');
        } else {
            $('#paymentNotice').addClass('dn');
        }
        if (a.incomeNotice > 0) {
            $('#incomeNotice').removeClass('dn').find('.info').html(a.incomeNotice);
            parent.$('.item-money .main-nav').append('<div class="main-alert"></div>');
            parent.$('.money-income').append('<div class="nav-alert">' + a.incomeNotice + '</div>');
        } else {
            $('#incomeNotice').addClass('dn');
        }
        if (a.expendNotice > 0) {
            $('#expendNotice').removeClass('dn').find('.info').html(a.expendNotice);
            parent.$('.item-money .main-nav').append('<div class="main-alert"></div>');
            parent.$('.money-expend').append('<div class="nav-alert">' + a.expendNotice + '</div>');
        } else {
            $('#expendNotice').addClass('dn');
        }
        if (a.invoiceMissNotice > 0) {
            $('#invoiceMissNotice').removeClass('dn').find('.info').html(a.invoiceMissNotice);
            parent.$('.item-money .main-nav').append('<div class="main-alert"></div>');
            parent.$('.money-invoiceMiss').append('<div class="nav-alert">' + a.invoiceMissNotice + '</div>');
        } else {
            $('#invoiceMissNotice').addClass('dn');
        }
        if (a.deliverNotice > 0) {
            $('#deliverNotice').removeClass('dn').find('.info').html(a.deliverNotice);
            parent.$('.item-deliver .main-nav').append('<div class="main-alert"></div>');
            parent.$('.deliver-deliver').append('<div class="nav-alert">' + a.deliverNotice + '</div>');
        } else {
            $('#deliverNotice').addClass('dn');
        }
        if (a.serviceNotice > 0) {
            $('#serviceNotice').removeClass('dn').find('.info').html(a.serviceNotice);
            parent.$('.item-sales .main-nav').append('<div class="main-alert"></div>');
            parent.$('.sales-service').append('<div class="nav-alert">' + a.serviceNotice + '</div>');
        } else {
            $('#serviceNotice').addClass('dn');
        }
        if (a.reviewHoldNotice > 0) {
            $('#reviewHoldNotice').removeClass('dn').find('.info').html(a.reviewHoldNotice);
            parent.$('.item-sales .main-nav').append('<div class="main-alert"></div>');
            parent.$('.sales-reviewHold').append('<div class="nav-alert">' + a.reviewHoldNotice + '</div>');
        } else {
            $('#reviewHoldNotice').addClass('dn');
        }
        if (a.bidProgramNotice > 0) {
            $('#bidProgramNotice').removeClass('dn').find('.info').html(a.bidProgramNotice);
            parent.$('.item-bid .main-nav').append('<div class="main-alert"></div>');
            parent.$('.bid-bidProgram').append('<div class="nav-alert">' + a.bidProgramNotice + '</div>');
        } else {
            $('#bidProgramNotice').addClass('dn');
        }
        if (a.deliverDullNotice > 0) {
            $('#deliverDullNotice').removeClass('dn').find('.info').html(a.deliverDullNotice);
            parent.$('.item-deliver .main-nav').append('<div class="main-alert"></div>');
            parent.$('.deliver-deliverBagDull').append('<div class="nav-alert">' + a.deliverDullNotice + '</div>');
        } else {
            $('#deliverDullNotice').addClass('dn');
        }
        if (a.goodsTaskNotice > 0) {
            $('#goodsTaskNotice').removeClass('dn').find('.info').html(a.goodsTaskNotice);
            parent.$('.item-develop .main-nav').append('<div class="main-alert"></div>');
            parent.$('.develop-goodsTask').append('<div class="nav-alert">' + a.goodsTaskNotice + '</div>');
        } else {
            $('#goodsTaskNotice').addClass('dn');
        }
        if (a.proKittingTaskNotice > 0) {
            $('#proKittingTaskNotice').removeClass('dn').find('.info').html(a.proKittingTaskNotice);
            parent.$('.item-produce .main-nav').append('<div class="main-alert"></div>');
            parent.$('.produce-produceKittingTask').append('<div class="nav-alert">' + a.proKittingTaskNotice + '</div>');
        } else {
            $('#proKittingTaskNotice').addClass('dn');
        }
        if (a.proKittingTaskUpNotice > 0) {
            $('#proKittingTaskUpNotice').removeClass('dn').find('.info').html(a.proKittingTaskUpNotice);
            parent.$('.item-produce .main-nav').append('<div class="main-alert"></div>');
            parent.$('.produce-produceKittingTaskUp').append('<div class="nav-alert">' + a.proKittingTaskUpNotice + '</div>');
        } else {
            $('#proKittingTaskUpNotice').addClass('dn');
        }
        if (a.proProcessingTaskUpNotice > 0) {
            $('#proProcessingTaskUpNotice').removeClass('dn').find('.info').html(a.proProcessingTaskUpNotice);
            parent.$('.item-produce .main-nav').append('<div class="main-alert"></div>');
            parent.$('.produce-produceProcessingTaskUp').append('<div class="nav-alert">' + a.proProcessingTaskUpNotice + '</div>');
        } else {
            $('#proProcessingTaskUpNotice').addClass('dn');
        }
        if (a.saleOrderSpiderErrGoodsNotice > 0) {
            $('#saleOrderSpiderErrGoodsNotice').removeClass('dn').find('.info').html(a.saleOrderSpiderErrGoodsNotice);
            parent.$('.item-product .main-nav').append('<div class="main-alert"></div>');
            parent.$('.product-salesOrderSpiderErrGoods').append('<div class="nav-alert">' + a.saleOrderSpiderErrGoodsNotice + '</div>');
        } else {
            $('#saleOrderSpiderErrGoodsNotice').addClass('dn');
        }
        if (a.saleOrderSpiderErrPriceNotice > 0) {
            $('#saleOrderSpiderErrPriceNotice').removeClass('dn').find('.info').html(a.saleOrderSpiderErrPriceNotice);
            parent.$('.item-product .main-nav').append('<div class="main-alert"></div>');
            parent.$('.product-salesOrderSpiderErrPrice').append('<div class="nav-alert">' + a.saleOrderSpiderErrPriceNotice + '</div>');
        } else {
            $('#saleOrderSpiderErrPriceNotice').addClass('dn');
        }
        if (a.competitiveMonitorNotice > 0) {
            $('#competitiveMonitorNotice').removeClass('dn').find('.info').html(a.competitiveMonitorNotice);
            parent.$('.item-product .main-nav').append('<div class="main-alert"></div>');
            parent.$('.product-competitiveMonitor').append('<div class="nav-alert">' + a.competitiveMonitorNotice + '</div>');
        } else {
            $('#competitiveMonitorNotice').addClass('dn');
        }
        if (a.clockinNotice > 0) {
            $('#clockinNotice').removeClass('dn').find('.info').html(a.clockinNotice);
            parent.$('.item-personnel .main-nav').append('<div class="main-alert"></div>');
            parent.$('.personnel-clockin').append('<div class="nav-alert">' + a.clockinNotice + '</div>');
        } else {
            $('#clockinNotice').addClass('dn');
        }
        if (a.workFillNotice > 0) {
            $('#workFillNotice').removeClass('dn').find('.info').html(a.workFillNotice);
            parent.$('.item-personnel .main-nav').append('<div class="main-alert"></div>');
            parent.$('.personnel-workFill').append('<div class="nav-alert">' + a.workFillNotice + '</div>');
        } else {
            $('#workFillNotice').addClass('dn');
        }
        if (a.workOvertimeNotice > 0) {
            $('#workOvertimeNotice').removeClass('dn').find('.info').html(a.workOvertimeNotice);
            parent.$('.item-personnel .main-nav').append('<div class="main-alert"></div>');
            parent.$('.personnel-workOvertime').append('<div class="nav-alert">' + a.workOvertimeNotice + '</div>');
        } else {
            $('#workOvertimeNotice').addClass('dn');
        }
        if (a.workHourNotice > 0) {
            $('#workHourNotice').removeClass('dn').find('.info').html(a.workHourNotice);
            parent.$('.item-personnel .main-nav').append('<div class="main-alert"></div>');
            parent.$('.personnel-workHour').append('<div class="nav-alert">' + a.workHourNotice + '</div>');
        } else {
            $('#workHourNotice').addClass('dn');
        }
        if (a.noteNotice > 0) {
            parent.$('.item-index .main-nav').append('<div class="main-alert"></div>');
            parent.$('.index-note').append('<div class="nav-alert">' + a.noteNotice + '</div>');
        }
        //返回完成状态
        return !0;
    },
    loadPersonalData: function(a) {
        //处理个人面板信息
        if (a.myInfo) {
            //如果缺少户口性质、银行卡号、支付宝号等信息，弹窗提醒补录
            if (!a.myInfo.hukou || !a.myInfo.bankNo || !a.myInfo.zfbNo) {
                $.dialog.confirm('缺少薪资发放等必需前置信息，请立即进行补录！', function() {
                    setTimeout(function() {
                        $.dialog({
                            title: '补录员工信息',
                            content: 'url:/Uri/user_fill',
                            data: {
                                rowId: defaultPage.SYSTEM.uId
                            },
                            width: 600,
                            height: 300,
                            max: !1,
                            min: !1,
                            cache: !1,
                            lock: !0
                        });
                    }, 200);
                });
            }
            //处理用户基本信息
            var userworkyears = Number((new Date() - new Date(a.myInfo.joinDate.replace(/-/g, "/"))) / 86400000 / 365).toFixed(1);
            var userexp = Number(Number(a.myInfo.exp1) + Number(a.myInfo.exp2)).toFixed(2);
            $('#user-name').html('姓名：' + a.myInfo.name);
            $('#user-department').html('部门：' + a.myInfo.department);
            $('#user-position').html('职位：' + a.myInfo.position);
            $('#user-work-years').html('工龄：' + parseInt(userworkyears) + '年');
            $('#user-level').html('等级：' + a.myInfo.level);
            $('#user-exp').html('经验：' + parseInt(userexp));
            $('#user-stars').addClass('user-star-' + a.myInfo.levelId);
            if (a.myInfo.levelId < 10) {
                    $('.user-exp-percent-bar').animate({width: parseInt((userexp - Number(defaultPage.SYSTEM.userLevel[a.myInfo.levelId].minExp)) / (Number(defaultPage.SYSTEM.userLevel[a.myInfo.levelId].maxExp) - Number(defaultPage.SYSTEM.userLevel[a.myInfo.levelId].minExp)) * 100) + "%"}, 1000);
                    $('.user-exp-percent-text').html('升到下一级还需 ' + parseInt(Number(defaultPage.SYSTEM.userLevel[String(parseInt(a.myInfo.levelId) + 1)].minExp) - userexp) + ' 经验<span class="level-preview hover" title="点击预览成长等级"><i class="iconfont icon-bangzhu ml10 mr2 f12"></i>等级预览</span>');
            } else {
                $('.user-exp-percent-bar').animate({width: "100%"}, 1000);
                $('.user-exp-percent-text').html('已达最高等级');
            }
            // 等级预览按钮点击事件
            $('.level-preview').on({
                click: function(e) {
                    $('.user-level-preview').removeClass('dn');
                }
            });
            // 等级预览关闭按钮点击事件
            $('.user-level-preview-close').on({
                click: function(e) {
                    $('.user-level-preview').addClass('dn');
                }
            });
            // 排名|奖章
            var medal = '';
            if (a.myInfo.rank != void 0) {
                if (a.myInfo.rank.bidRank) {
                    if (a.myInfo.rank.bidRank.length == 2) {
                        medal += '<div class="rank gold"><div>投标排名</div><div class="f20 fb">' + a.myInfo.rank.bidRank[0].rank + (a.myInfo.rank.bidRank[0].rank < a.myInfo.rank.bidRank[1].rank ? '<i class="iconfont icon-shangsheng ml10 f16 green"></i>' : (a.myInfo.rank.bidRank[0].rank == a.myInfo.rank.bidRank[1].rank ? '' : '<i class="iconfont icon-xiajiang ml10 f16 red"></i>')) + '</div><div>' + (a.myInfo.rank.bidRank[0].rank < a.myInfo.rank.bidRank[1].rank ? ('上升' + (a.myInfo.rank.bidRank[1].rank - a.myInfo.rank.bidRank[0].rank) + '位') : (a.myInfo.rank.bidRank[0].rank == a.myInfo.rank.bidRank[1].rank ? '持平' : ('下降' + (a.myInfo.rank.bidRank[0].rank - a.myInfo.rank.bidRank[1].rank) + '位'))) + '</div></div>';
                    } else if (a.myInfo.rank.bidRank.length == 1) {
                        medal += '<div class="rank gold"><div>投标排名</div><div class="f20 fb">' + a.myInfo.rank.bidRank[0].rank + '<i class="iconfont icon-chiping ml10 f16"></i>' + '</div><div>首次排名</div></div>';
                    } else if (a.myInfo.rank.bidRank.length == 0) {
                        medal += '<div class="rank"><div>投标排名</div><div class="f20 fb">0</div><div>无名次</div></div>';
                    }
                }
                if (a.myInfo.rank.saleRank) {
                    if (a.myInfo.rank.saleRank.length == 2) {
                        medal += '<div class="rank gold"><div>销售排名</div><div class="f20 fb">' + a.myInfo.rank.saleRank[0].rank + (a.myInfo.rank.saleRank[0].rank < a.myInfo.rank.saleRank[1].rank ? '<i class="iconfont icon-shangsheng ml10 f16 green"></i>' : (a.myInfo.rank.saleRank[0].rank == a.myInfo.rank.saleRank[1].rank ? '' : '<i class="iconfont icon-xiajiang ml10 f16 red"></i>')) + '</div><div>' + (a.myInfo.rank.saleRank[0].rank < a.myInfo.rank.saleRank[1].rank ? ('上升' + (a.myInfo.rank.saleRank[1].rank - a.myInfo.rank.saleRank[0].rank) + '位') : (a.myInfo.rank.saleRank[0].rank == a.myInfo.rank.saleRank[1].rank ? '持平' : ('下降' + (a.myInfo.rank.saleRank[0].rank - a.myInfo.rank.saleRank[1].rank) + '位'))) + '</div></div>';
                    } else if (a.myInfo.rank.saleRank.length == 1) {
                        medal += '<div class="rank gold"><div>销售排名</div><div class="f20 fb">' + a.myInfo.rank.saleRank[0].rank + '<i class="iconfont icon-chiping ml10 f16"></i>' + '</div><div>首次排名</div></div>';
                    } else if (a.myInfo.rank.saleRank.length == 0) {
                        medal += '<div class="rank"><div>销售排名</div><div class="f20 fb">0</div><div>无名次</div></div>';
                    }
                }
                if (a.myInfo.rank.serviceRank) {
                    if (a.myInfo.rank.serviceRank.length == 2) {
                        medal += '<div class="rank gold"><div>客服排名</div><div class="f20 fb">' + a.myInfo.rank.serviceRank[0].rank + (a.myInfo.rank.serviceRank[0].rank < a.myInfo.rank.serviceRank[1].rank ? '<i class="iconfont icon-shangsheng ml10 f16 green"></i>' : (a.myInfo.rank.serviceRank[0].rank == a.myInfo.rank.serviceRank[1].rank ? '' : '<i class="iconfont icon-xiajiang ml10 f16 red"></i>')) + '</div><div>' + (a.myInfo.rank.serviceRank[0].rank < a.myInfo.rank.serviceRank[1].rank ? ('上升' + (a.myInfo.rank.serviceRank[1].rank - a.myInfo.rank.serviceRank[0].rank) + '位') : (a.myInfo.rank.serviceRank[0].rank == a.myInfo.rank.serviceRank[1].rank ? '持平' : ('下降' + (a.myInfo.rank.serviceRank[0].rank - a.myInfo.rank.serviceRank[1].rank) + '位'))) + '</div></div>';
                    } else if (a.myInfo.rank.serviceRank.length == 1) {
                        medal += '<div class="rank gold"><div>客服排名</div><div class="f20 fb">' + a.myInfo.rank.serviceRank[0].rank + '<i class="iconfont icon-chiping ml10 f16"></i>' + '</div><div>首次排名</div></div>';
                    } else if (a.myInfo.rank.serviceRank.length == 0) {
                        medal += '<div class="rank gold"><div>客服排名</div><div class="f20 fb">0</div><div>无名次</div></div>';
                    }
                }
                if (a.myInfo.rank.purRank) {
                    if (a.myInfo.rank.purRank.length == 2) {
                        medal += '<div class="rank gold"><div>采购排名</div><div class="f20 fb">' + a.myInfo.rank.purRank[0].rank + (a.myInfo.rank.purRank[0].rank < a.myInfo.rank.purRank[1].rank ? '<i class="iconfont icon-shangsheng ml10 f16 green"></i>' : (a.myInfo.rank.purRank[0].rank == a.myInfo.rank.purRank[1].rank ? '' : '<i class="iconfont icon-xiajiang ml10 f16 red"></i>')) + '</div><div>' + (a.myInfo.rank.purRank[0].rank < a.myInfo.rank.purRank[1].rank ? ('上升' + (a.myInfo.rank.purRank[1].rank - a.myInfo.rank.purRank[0].rank) + '位') : (a.myInfo.rank.purRank[0].rank == a.myInfo.rank.purRank[1].rank ? '持平' : ('下降' + (a.myInfo.rank.purRank[0].rank - a.myInfo.rank.purRank[1].rank) + '位'))) + '</div></div>';
                    } else if (a.myInfo.rank.purRank.length == 1) {
                        medal += '<div class="rank gold"><div>采购排名</div><div class="f20 fb">' + a.myInfo.rank.purRank[0].rank + '<i class="iconfont icon-chiping ml10 f16"></i>' + '</div><div>首次排名</div></div>';
                    } else if (a.myInfo.rank.purRank.length == 0) {
                        medal += '<div class="rank"><div>采购排名</div><div class="f20 fb">0</div><div>无名次</div></div>';
                    }
                }
                if (a.myInfo.rank.invoiceRank) {
                    if (a.myInfo.rank.invoiceRank.length == 2) {
                        medal += '<div class="rank gold"><div>回票排名</div><div class="f20 fb">' + a.myInfo.rank.invoiceRank[0].rank + (a.myInfo.rank.invoiceRank[0].rank < a.myInfo.rank.invoiceRank[1].rank ? '<i class="iconfont icon-shangsheng ml10 f16 green"></i>' : (a.myInfo.rank.invoiceRank[0].rank == a.myInfo.rank.invoiceRank[1].rank ? '' : '<i class="iconfont icon-xiajiang ml10 f16 red"></i>')) + '</div><div>' + (a.myInfo.rank.invoiceRank[0].rank < a.myInfo.rank.invoiceRank[1].rank ? ('上升' + (a.myInfo.rank.invoiceRank[1].rank - a.myInfo.rank.invoiceRank[0].rank) + '位') : (a.myInfo.rank.invoiceRank[0].rank == a.myInfo.rank.invoiceRank[1].rank ? '持平' : ('下降' + (a.myInfo.rank.invoiceRank[0].rank - a.myInfo.rank.invoiceRank[1].rank) + '位'))) + '</div></div>';
                    } else if (a.myInfo.rank.invoiceRank.length == 1) {
                        medal += '<div class="rank gold"><div>回票排名</div><div class="f20 fb">' + a.myInfo.rank.invoiceRank[0].rank + '<i class="iconfont icon-chiping ml10 f16"></i>' + '</div><div>首次排名</div></div>';
                    } else if (a.myInfo.rank.invoiceRank.length == 0) {
                        medal += '<div class="rank"><div>回票排名</div><div class="f20 fb">0</div><div>无名次</div></div>';
                    }
                }
            }
            $.each(a.myInfo.medal, function(k, v) {
<<<<<<< HEAD
                medal += '<div class="medal gold" style="background-image: url(../statics/img/' + v.img + '?v=2026102701)"><div class="medal-tit"></div><div class="medal-text">' + v.name + '</div></div>';
=======
                medal += '<div class="medal gold" style="background-image: url(../statics/img/' + v.img + '?v=2026101403)"><div class="medal-tit"></div><div class="medal-text">' + v.name + '</div></div>';
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            });
            $('.medals').html(medal);
            // 历史奖章点击事件
            $('.my-all-medals').on('click', function(a) {
                a.preventDefault();
                parent.tab.addTabItem({
                    tabid: 'personnel-userMedal',
                    text: '<i class="iconfont icon-jiangzhang f14 mr5"></i>荣誉奖章',
                    url: '/Uri/user_medal_list'
                });
            });
            // 迟到提醒
            if (a.myInfo.lateTimes > 0) {
                $('#lateTimes').html('本月已迟到 ' + a.myInfo.lateTimes + ' 次，请注意准时上班，合理预留出行时间。').removeClass('dn');
            }
            // 岗位职责
            if (a.myInfo.duty) {
                $('#duty').html('<div class="user-position-duty-con">' + a.myInfo.duty + '</div>');
            }
            // 销售部值班选择入口
            if (a.myInfo.department == '销售部' || a.myInfo.name == '叶超' || a.myInfo.name == '杨颐') {
                $('.saler-duty-choose').removeClass('dn');
            }
            // 上级主管
            if (a.myInfo.superiorsInfo) {
<<<<<<< HEAD
                $('#superiors').html('<div class="user-position-staff-con"><img class="user-position-staff-avatar" src="' + a.myInfo.superiorsInfo.avatar + '?v=2026102701"><div class="user-position-staff-name">' + a.myInfo.superiorsInfo.name + '</div><div class="user-position-staff-onduty es ' + (a.myInfo.superiorsInfo.onDuty == '在岗' ? 'green' : 'red') + '">' + a.myInfo.superiorsInfo.onDuty + '</div><div class="user-position-staff-mobile"><i class="iconfont icon-mobile f12 mr5"></i>' + a.myInfo.superiorsInfo.mobile + '</div></div>').parent().removeClass('dn');
=======
                $('#superiors').html('<div class="user-position-staff-con"><img class="user-position-staff-avatar" src="' + a.myInfo.superiorsInfo.avatar + '?v=2026101403"><div class="user-position-staff-name">' + a.myInfo.superiorsInfo.name + '</div><div class="user-position-staff-onduty es ' + (a.myInfo.superiorsInfo.onDuty == '在岗' ? 'green' : 'red') + '">' + a.myInfo.superiorsInfo.onDuty + '</div><div class="user-position-staff-mobile"><i class="iconfont icon-mobile f12 mr5"></i>' + a.myInfo.superiorsInfo.mobile + '</div></div>').parent().removeClass('dn');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            } else {
                $('#superiors').parent().addClass('dn');
            }
            // 下属员工
            if (a.myInfo.membersInfo) {
                var membersInfo = '';
                $.each(a.myInfo.membersInfo, function(i, v) {
<<<<<<< HEAD
                    membersInfo += '<div data-id="' + i + '" class="user-position-staff-con"><img class="user-position-staff-avatar" src="' + v.avatar + '?v=2026102701"><div class="user-position-staff-name">' + v.name + '</div><div class="user-position-staff-onduty es ' + (v.onDuty == '在岗' ? 'green' : 'red') + '">' + v.onDuty + '</div><div class="user-position-staff-mobile"><i class="iconfont icon-mobile f12 mr5"></i>' + v.mobile + '</div></div>';
=======
                    membersInfo += '<div data-id="' + i + '" class="user-position-staff-con"><img class="user-position-staff-avatar" src="' + v.avatar + '?v=2026101403"><div class="user-position-staff-name">' + v.name + '</div><div class="user-position-staff-onduty es ' + (v.onDuty == '在岗' ? 'green' : 'red') + '">' + v.onDuty + '</div><div class="user-position-staff-mobile"><i class="iconfont icon-mobile f12 mr5"></i>' + v.mobile + '</div></div>';
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                });
                $('#members').html(membersInfo).parent().removeClass('dn');
            } else {
                $('#members').parent().addClass('dn');
            }
            // 生日皮肤
            if (a.myInfo.birthdaySolar == (new Date).format('M月d日') || a.myInfo.birthdayLunar.replace('闰', '') == (today_lunar.lMonthCn + today_lunar.lDayCn).toString().replace('闰', '')) {
<<<<<<< HEAD
                $('#user-happybirthday').css("background-image", "url('../statics/img/user-happybirthday.gif?v=2026102701')").removeClass('dn');
=======
                $('#user-happybirthday').css("background-image", "url('../statics/img/user-happybirthday.gif?v=2026101403')").removeClass('dn');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            }
            // 升级后第一次登录弹出升级皮肤
            if (a.isFirstLogin && a.myInfo.levelNoticedId < a.myInfo.levelId) {
                Public.ajaxGet('/Base/Home/levelupNoticed', {}, function(res) {
                    if (res.status === 200) {
                        $('.levelup-img').html('<image src="/statics/img/user-star-' + a.myInfo.levelId + '.png">');
                        $('.levelup-tit').html('恭喜您成功升级到『' + a.myInfo.level + '』！解锁更多福利！');
                        $('.levelup').removeClass('dn');
                        // 升级皮肤关闭按钮点击事件
                        $('.levelup-close').on('click', function() {
                            $('.levelup').remove();
                        });
                    }
                });
            }
        }
        // 今天首次登陆或上次登陆是今天0点前，显示销售部值班选择弹窗、节日皮肤
        if (a.isFirstLogin || a.lastLoginTime < (new Date).format('yyyy-MM-dd') + ' 00:00:00') {
            if (a.myInfo.department == '销售部' || a.myInfo.name == '叶超' || a.myInfo.name == '杨颐') {
                $.dialog({
                    title: '选择今日值班内容',
                    content: 'url:/Uri/saler_duty_choose',
                    data: {
                        uId: a.myInfo.id,
                        uName: a.myInfo.name
                    },
                    width: 1250,
                    height: 800,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
            // 春节
            if (today_lunar.lMonth == 1 && today_lunar.lDay >= 1 && today_lunar.lDay <= 7) {
<<<<<<< HEAD
                $('.festival-tit').css({"background-image": "url('../statics/img/chunjie-tit.gif?v=2026102701')", "background-position": "center 10px"});
                $('.festival').css({"background-image": "url('../statics/img/denglong.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 元宵节
            if (today_lunar.lMonth == 1 && today_lunar.lDay == 15) {
                $('.festival-tit').css({"background-image": "url('../statics/img/yuanxiao-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/yuanxiao.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 清明节
            if (today_lunar.term == '清明') {
                $('.festival-tit').css({"background-image": "url('../statics/img/qingming-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/qingming.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 端午节
            if (today_lunar.lMonth == 5 && today_lunar.lDay == 5) {
                $('.festival-tit').css({"background-image": "url('../statics/img/duanwu-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/duanwu.gif?v=2026102701')", "background-position": "center -50px"}).show(200).delay(8000).hide(200);
            }
            // 七夕节
            if (today_lunar.lMonth == 7 && today_lunar.lDay == 7) {
                $('.festival-tit').css({"background-image": "url('../statics/img/qixi-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/qixi.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 中元节
            if (today_lunar.lMonth == 7 && today_lunar.lDay == 15) {
                $('.festival-tit').css({"background-image": "url('../statics/img/zhongyuan-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/zhongyuan.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 中秋节
            if (today_lunar.lMonth == 8 && today_lunar.lDay == 15) {
                $('.festival-tit').css({"background-image": "url('../statics/img/zhongqiu-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/zhongqiu.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 重阳节
            if (today_lunar.lMonth == 9 && today_lunar.lDay == 9) {
                $('.festival-tit').css({"background-image": "url('../statics/img/chongyang-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/chongyang.gif?v=2026102701')", "background-position": "center bottom"}).show(200).delay(8000).hide(200);
            }
            // 除夕
            if (today_lunar.lMonth == 12 && today_lunar.lDay == 30) {
                $('.festival-tit').css({"background-image": "url('../statics/img/chuxi-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/chuxi.gif?v=2026102701')", "background-position": "center -200px"}).show(200).delay(8000).hide(200);
=======
                $('.festival-tit').css({"background-image": "url('../statics/img/chunjie-tit.gif?v=2026101403')", "background-position": "center 10px"});
                $('.festival').css({"background-image": "url('../statics/img/denglong.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 元宵节
            if (today_lunar.lMonth == 1 && today_lunar.lDay == 15) {
                $('.festival-tit').css({"background-image": "url('../statics/img/yuanxiao-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/yuanxiao.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 清明节
            if (today_lunar.term == '清明') {
                $('.festival-tit').css({"background-image": "url('../statics/img/qingming-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/qingming.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 端午节
            if (today_lunar.lMonth == 5 && today_lunar.lDay == 5) {
                $('.festival-tit').css({"background-image": "url('../statics/img/duanwu-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/duanwu.gif?v=2026101403')", "background-position": "center -50px"}).show(200).delay(8000).hide(200);
            }
            // 七夕节
            if (today_lunar.lMonth == 7 && today_lunar.lDay == 7) {
                $('.festival-tit').css({"background-image": "url('../statics/img/qixi-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/qixi.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 中元节
            if (today_lunar.lMonth == 7 && today_lunar.lDay == 15) {
                $('.festival-tit').css({"background-image": "url('../statics/img/zhongyuan-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/zhongyuan.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 中秋节
            if (today_lunar.lMonth == 8 && today_lunar.lDay == 15) {
                $('.festival-tit').css({"background-image": "url('../statics/img/zhongqiu-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/zhongqiu.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 重阳节
            if (today_lunar.lMonth == 9 && today_lunar.lDay == 9) {
                $('.festival-tit').css({"background-image": "url('../statics/img/chongyang-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/chongyang.gif?v=2026101403')", "background-position": "center bottom"}).show(200).delay(8000).hide(200);
            }
            // 除夕
            if (today_lunar.lMonth == 12 && today_lunar.lDay == 30) {
                $('.festival-tit').css({"background-image": "url('../statics/img/chuxi-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/chuxi.gif?v=2026101403')", "background-position": "center -200px"}).show(200).delay(8000).hide(200);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            }

            // 元旦节
            if (today_lunar.sMonth == 1 && today_lunar.sDay == 1) {
<<<<<<< HEAD
                $('.festival-tit').css({"background-image": "url('../statics/img/yuandan-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/denglong.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 情人节
            if (today_lunar.sMonth == 2 && today_lunar.sDay == 14) {
                $('.festival-tit').css({"background-image": "url('../statics/img/qingren-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/qingren.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 妇女节
            if (today_lunar.sMonth == 3 && today_lunar.sDay == 8) {
                $('.festival-tit').css({"background-image": "url('../statics/img/nvshen-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/nvshen.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 劳动节
            if (today_lunar.sMonth == 5 && today_lunar.sDay == 1) {
                $('.festival-tit').css({"background-image": "url('../statics/img/laodong-tit.gif?v=2026102701')", "background-position": "center 70px"});
                $('.festival').css({"background-image": "url('../statics/img/laodong.gif?v=2026102701')", "background-repeat": "no-repeat"}).show(200).delay(8000).hide(200);
            }
            // 青年节
            if (today_lunar.sMonth == 5 && today_lunar.sDay == 4) {
                $('.festival-tit').css({"background-image": "url('../statics/img/qingnian-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/qingnian.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 母亲节
            if (today_lunar.sMonth == 5 && today_lunar.nWeek == 0 && today_lunar.sDay > 7 && today_lunar.sDay < 14) {
                $('.festival-tit').css({"background-image": "url('../statics/img/muqin-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/muqin.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 儿童节
            if (today_lunar.sMonth == 6 && today_lunar.sDay == 1) {
                $('.festival-tit').css({"background-image": "url('../statics/img/ertong-tit.gif?v=2026102701')", "margin-top": "-200px"});
                $('.festival').css({"background-image": "url('../statics/img/ertong.gif?v=2026102701')", "background-position": "center bottom"}).show(200).delay(8000).hide(200);
            }
            // 父亲节
            if (today_lunar.sMonth == 6 && today_lunar.nWeek == 0 && today_lunar.sDay > 14 && today_lunar.sDay < 21) {
                $('.festival-tit').css({"background-image": "url('../statics/img/fuqin-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/fuqin.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 建党节
            if (today_lunar.sMonth == 7 && today_lunar.sDay == 1) {
                $('.festival-tit').css({"background-image": "url('../statics/img/jiandang-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/jiandang.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 建军节
            if (today_lunar.sMonth == 8 && today_lunar.sDay == 1) {
                $('.festival-tit').css({"background-image": "url('../statics/img/jianjun-tit.gif?v=2026102701')", "background-position": "center -30px"});
                $('.festival').css({"background-image": "url('../statics/img/jianjun.gif?v=2026102701')", "background-repeat": "no-repeat", "background-size": "1200px 1200px", "background-position": "center center"}).show(200).delay(8000).hide(200);
            }
            // 教师节
            if (today_lunar.sMonth == 9 && today_lunar.sDay == 10) {
                $('.festival-tit').css({"background-image": "url('../statics/img/jiaoshi-tit.gif?v=2026102701')", "margin-top": "-200px"});
                $('.festival').css({"background-image": "url('../statics/img/jiaoshi.gif?v=2026102701')", "background-position": "center bottom"}).show(200).delay(8000).hide(200);
=======
                $('.festival-tit').css({"background-image": "url('../statics/img/yuandan-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/denglong.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 情人节
            if (today_lunar.sMonth == 2 && today_lunar.sDay == 14) {
                $('.festival-tit').css({"background-image": "url('../statics/img/qingren-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/qingren.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 妇女节
            if (today_lunar.sMonth == 3 && today_lunar.sDay == 8) {
                $('.festival-tit').css({"background-image": "url('../statics/img/nvshen-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/nvshen.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 劳动节
            if (today_lunar.sMonth == 5 && today_lunar.sDay == 1) {
                $('.festival-tit').css({"background-image": "url('../statics/img/laodong-tit.gif?v=2026101403')", "background-position": "center 70px"});
                $('.festival').css({"background-image": "url('../statics/img/laodong.gif?v=2026101403')", "background-repeat": "no-repeat"}).show(200).delay(8000).hide(200);
            }
            // 青年节
            if (today_lunar.sMonth == 5 && today_lunar.sDay == 4) {
                $('.festival-tit').css({"background-image": "url('../statics/img/qingnian-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/qingnian.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 母亲节
            if (today_lunar.sMonth == 5 && today_lunar.nWeek == 0 && today_lunar.sDay > 7 && today_lunar.sDay < 14) {
                $('.festival-tit').css({"background-image": "url('../statics/img/muqin-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/muqin.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 儿童节
            if (today_lunar.sMonth == 6 && today_lunar.sDay == 1) {
                $('.festival-tit').css({"background-image": "url('../statics/img/ertong-tit.gif?v=2026101403')", "margin-top": "-200px"});
                $('.festival').css({"background-image": "url('../statics/img/ertong.gif?v=2026101403')", "background-position": "center bottom"}).show(200).delay(8000).hide(200);
            }
            // 父亲节
            if (today_lunar.sMonth == 6 && today_lunar.nWeek == 0 && today_lunar.sDay > 14 && today_lunar.sDay < 21) {
                $('.festival-tit').css({"background-image": "url('../statics/img/fuqin-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/fuqin.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 建党节
            if (today_lunar.sMonth == 7 && today_lunar.sDay == 1) {
                $('.festival-tit').css({"background-image": "url('../statics/img/jiandang-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/jiandang.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 建军节
            if (today_lunar.sMonth == 8 && today_lunar.sDay == 1) {
                $('.festival-tit').css({"background-image": "url('../statics/img/jianjun-tit.gif?v=2026101403')", "background-position": "center -30px"});
                $('.festival').css({"background-image": "url('../statics/img/jianjun.gif?v=2026101403')", "background-repeat": "no-repeat", "background-size": "1200px 1200px", "background-position": "center center"}).show(200).delay(8000).hide(200);
            }
            // 教师节
            if (today_lunar.sMonth == 9 && today_lunar.sDay == 10) {
                $('.festival-tit').css({"background-image": "url('../statics/img/jiaoshi-tit.gif?v=2026101403')", "margin-top": "-200px"});
                $('.festival').css({"background-image": "url('../statics/img/jiaoshi.gif?v=2026101403')", "background-position": "center bottom"}).show(200).delay(8000).hide(200);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            }
            // 国庆节
            if (today_lunar.sMonth == 10 && today_lunar.sDay == 1) {
                if (today_lunar.lMonth == 8 && today_lunar.lDay == 15) { // 如果同日也是中秋节，先短暂显示中秋，再显示国庆
<<<<<<< HEAD
                    $('.festival-tit').css({"background-image": "url('../statics/img/zhongqiu-tit.gif?v=2026102701')"});
                    $('.festival').css({"background-image": "url('../statics/img/zhongqiu.gif?v=2026102701')"}).show(200).delay(3000).hide(200);
                    setTimeout(() => {
                        $('.festival-tit').css({"background-image": "url('../statics/img/guoqing-tit.gif?v=2026102701')"});
                        $('.festival').css({"background-image": "url('../statics/img/denglong.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
                    }, 3400);
                } else {
                    $('.festival-tit').css({"background-image": "url('../statics/img/guoqing-tit.gif?v=2026102701')"});
                    $('.festival').css({"background-image": "url('../statics/img/denglong.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
=======
                    $('.festival-tit').css({"background-image": "url('../statics/img/zhongqiu-tit.gif?v=2026101403')"});
                    $('.festival').css({"background-image": "url('../statics/img/zhongqiu.gif?v=2026101403')"}).show(200).delay(3000).hide(200);
                    setTimeout(() => {
                        $('.festival-tit').css({"background-image": "url('../statics/img/guoqing-tit.gif?v=2026101403')"});
                        $('.festival').css({"background-image": "url('../statics/img/denglong.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
                    }, 3400);
                } else {
                    $('.festival-tit').css({"background-image": "url('../statics/img/guoqing-tit.gif?v=2026101403')"});
                    $('.festival').css({"background-image": "url('../statics/img/denglong.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                }
            }
            // 双11
            if (today_lunar.sMonth == 11 && today_lunar.sDay == 11) {
<<<<<<< HEAD
                $('.festival-tit').css({"background-image": "url('../statics/img/shopping-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/shopping.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 感恩节
            if (today_lunar.sMonth == 11 && today_lunar.nWeek == 4 && today_lunar.sDay > 21 && today_lunar.sDay < 28) {
                $('.festival-tit').css({"background-image": "url('../statics/img/ganen-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/ganen.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
            }
            // 双12
            if (today_lunar.sMonth == 12 && today_lunar.sDay == 12) {
                $('.festival-tit').css({"background-image": "url('../statics/img/shopping-tit.gif?v=2026102701')"});
                $('.festival').css({"background-image": "url('../statics/img/shopping.gif?v=2026102701')"}).show(200).delay(8000).hide(200);
=======
                $('.festival-tit').css({"background-image": "url('../statics/img/shopping-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/shopping.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 感恩节
            if (today_lunar.sMonth == 11 && today_lunar.nWeek == 4 && today_lunar.sDay > 21 && today_lunar.sDay < 28) {
                $('.festival-tit').css({"background-image": "url('../statics/img/ganen-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/ganen.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
            }
            // 双12
            if (today_lunar.sMonth == 12 && today_lunar.sDay == 12) {
                $('.festival-tit').css({"background-image": "url('../statics/img/shopping-tit.gif?v=2026101403')"});
                $('.festival').css({"background-image": "url('../statics/img/shopping.gif?v=2026101403')"}).show(200).delay(8000).hide(200);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            }
        }
        // 节日皮肤关闭按钮
        $('.festival-close').on('click', function() {
            $('.festival').remove();
        });
        // 返回完成状态
        return !0;
    },
    loadNoteData: function(a) {
        if (a) {
            //私人备忘
            if (a.noteNotice > 0) {
                $('#noteNotice').removeClass('bg-silver').addClass('bg-red').html(a.noteNotice);
            } else {
                $('#noteNotice').removeClass('bg-red').addClass('bg-silver').html(a.noteNotice);
            }
            if (a.noteInfo.length > 0) {
                var noteDiv = '';
                $.each(a.noteInfo, function(k, v) {
                    noteDiv += '<div class="user-note-item ovh es poi" data-id="' + v.id + '" title="' + v.title + '"><span class="user-info-numer">'
                        + (k + 1) + '</span>'
                        + v.title + '</div>';
                });
                $('.user-note-con').html(noteDiv);
            } else {
                $('.user-note-con').html('<div class="m50 tc silver">暂无</div>');
            }
        }
        //返回完成状态
        return !0;
    },
    timelimitedcallback: function(a) {
        $('#timelimited-refresh-btn').trigger('click');
    }
};
$(function() {
    THISPAGE.init();
});
