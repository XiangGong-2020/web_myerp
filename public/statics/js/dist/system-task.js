var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        Public.ajaxPost('/Base/Option/systemTaskList', {}, function(res) {
            if (res && 200 == res.status) {
                var div = '';
                $.each(res.data.rows, function(i, v) {
                    div +=
                        '<div class="task-line" data-id="' + v.id + '">' +
                        '<div class="task-name">' + v.optionCname + '</div>' +
                        '<div class="task-status">' + (
                            v.optionValue == 0 ? '<span class="green">已启动</span>' : (
                                v.optionValue == 1 ? '<span class="yellow">运行中</span>' : '<span class="fb red">已停止</span>'
                            )
                        ) + '</div>' +
                        '<div class="fr ui-btn-group">' +
                        '<div id="start" class="ui-btn bg-green"><i class="iconfont icon-qidong"></i><span>启动</span></div>' +
                        '<div id="stop" class="ui-btn bg-red"><i class="iconfont icon-tingzhi"></i><span>停止</span></div>' +
                        '</div>' +
                        '</div>';
                });
                $('#task-wrapper').html(div);
            } else {
                $.dialog.tips('获取系统任务失败！' + res.msg, 2);
            }
        });
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#task-wrapper').on('click', '#start', function() {
            if (Business.verifyRight('SYS_TASK_UPDATE')) {
                var id = $(this).parent().parent().data('id');
                var returnDiv = $(this).parent().siblings('.task-status');
                Public.ajaxPost('/Base/Option/systemTaskUpdate', {id: id, optionvalue: 0}, function(res) {
                    if (200 == res.status) {
                        returnDiv.html('<span class="fb green">已启动</span>');
                    } else {
                        $.dialog.tips('任务启动失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#task-wrapper').on('click', '#stop', function() {
            if (Business.verifyRight('SYS_TASK_UPDATE')) {
                var id = $(this).parent().parent().data('id');
                var returnDiv = $(this).parent().siblings('.task-status');
                Public.ajaxPost('/Base/Option/systemTaskUpdate', {id: id, optionvalue: 2}, function(res) {
                    if (200 == res.status) {
                        returnDiv.html('<span class="fb red">已停止</span>');
                    } else {
                        $.dialog.tips('任务停止失败！' + res.msg, 2);
                    }
                });
            }
        });
        setInterval(() => {
            THISPAGE.initDom();
        }, 2000);
    }
};
$(function() {
    THISPAGE.init();
});
