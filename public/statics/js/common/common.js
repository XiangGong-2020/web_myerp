var Public = Public || {};
var Business = Business || {};
/* 设置cookie */
$.cookie = function(name, value, options) {
    if (typeof value != 'undefined') {
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString();//use expires attribute, max-age is not supported by IE
        }
        var path = options.path ? '; path=' + options.path : '';
        var domain = options.domain ? '; domain=' + options.domain : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else {//only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                //Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
/* 获取默认页 */
Public.getDefaultPage = function() {
    var win = window.self;
    var i = 20;//最多20层，防止无限嵌套
    try {
        do {
            if (/index.php/.test(win.location.href)) {
                return win;
            }
            win = win.parent;
            i--;
        } while (i > 0);
    } catch (e) {
        return win;
    }
    return win;
};
/* 获取URL参数值 */
Public.urlParam = function() {
    var param, url = location.search, theRequest = {};
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for (var i = 0, len = strs.length; i < len; i++) {
            param = strs[i].split("=");
            theRequest[param[0]] = decodeURIComponent(param[1]);
        }
    }
    return theRequest;
};
/* 纠正被JQGRID错误反转义的url链接 */
Public.fixUrl = function(url) {
    if (void 0 !== url) {
        return url.replace('¬', '&not').replace('¢', '&ent').replace('¤', '&curren').replace('£', '&pound').replace('§', '&sect');
    } else {
        return '';
    }
};
/* 批量绑定页签打开 */
Public.pageTab = function() {
    $(document).on('click', '[rel=pageTab]', function(a) {
        a.preventDefault();
        var right = $(this).data('right');
        if (right && !Business.verifyRight(right)) {
            return false;
        };
        var tabid = $(this).attr('tabid');
        var text = $(this).attr('tabTxt') || $(this).text();
        var url = $(this).attr('href');
        var showClose = $(this).attr('showClose');
        var parentOpen = $(this).attr('parentOpen');
        if (parentOpen) {
            parent.tab.addTabItem({tabid: tabid, text: text, url: url, showClose: showClose});
        } else {
            tab.addTabItem({tabid: tabid, text: text, url: url, showClose: showClose});
        }
    });
};
/* 设置表格宽高 */
Public.setGrid = function(adjustH, adjustW) {
    var adjustH = adjustH || 90;
    var adjustW = adjustW || 0;
    var gridW = $(window).width() - adjustW;
    var gridH = $(window).height() - $(".grid-wrap").offset().top - adjustH;
    return {
        w: gridW,
        h: gridH
    };
};
/* 重设表格宽高 */
Public.resizeGrid = function(adjustH, adjustW, obj) {
    var grid = obj || $("#grid");
    var gridWH = Public.setGrid(adjustH, adjustW);
    grid.jqGrid('setGridHeight', gridWH.h);
    grid.jqGrid('setGridWidth', gridWH.w);
};
/* 表格自动宽度 */
Public.autoGridWidth = function(obj, width) {
    grid = obj || $("#grid");
    grid.jqGrid('setGridWidth', width || grid.closest('.grid-wrap').innerWidth());
};
/* 自动高度 */
Public.setAutoHeight = function(obj) {
    if (!obj || obj.length < 1) {
        return;
    }
    Public._setAutoHeight(obj);
    $(window).on('resize', function() {
        Public._setAutoHeight(obj);
    });
};
Public._setAutoHeight = function(obj) {
    obj = $(obj);
    //parent = parent || window;
    var winH = $(window).height();
    var h = winH - obj.offset().top - (obj.outerHeight() - obj.height());
    obj.height(h);
};
/* 操作项格式化 */
Public.operFmatter = function(val, opt, row) {
    var html_con = (
        '<div class="operating" data-id="' + row.id + '">' +
        '<span class="oper-icon ui-icon-pencil iconfont icon-chakanbianji" title="查看与编辑"></span>' +
        '<span class="oper-icon ui-icon-trash iconfont icon-shanchu" title="删除"></span>' +
        '</div>'
    );
    return html_con;
};
Public.billsOper = function(val, opt, row) {
    var html_con = (
        '<div class="operating" data-id="' + opt.rowId + '">' +
        '<span class="oper-icon ui-icon-plus iconfont icon-zengjia" title="新增一行"></span>' +
        '<span class="ml2 oper-icon ui-icon-trash iconfont icon-shanchu" title="删除本行"></span>' +
        '</div>'
    );
    return html_con;
};
/* 日期格式化 */
Date.prototype.format = function(format) {
    if (!format) {
        format = 'yyyy-MM-dd';//默认1997-01-01这样的格式
    }
    var o = {
        "M+": this.getMonth() + 1, //month
        "d+": this.getDate(), //day
        "h+": this.getHours(), //hour
        "m+": this.getMinutes(), //minute
        "s+": this.getSeconds(), //second
        "q+": Math.floor((this.getMonth() + 3) / 3), //quarter
        "S": this.getMilliseconds() //millisecond
    };
    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
};
/* 当前日期 增加月 */
Date.prototype.addMonths = function(m) {
    var d = this.getDate();
    this.setMonth(this.getMonth() + m);
    if (this.getDate() < d) {
        this.setDate(0);
    }
};
/* 当前日期 减少月 */
Date.prototype.reduceMonths = function(m) {
    var d = this.getDate();
    this.setMonth(this.getMonth() - m);
    if (this.getDate() > d) {
        this.setDate(0);
    }
};
/* 当前日期 增加天 */
Date.prototype.addDays = function(d) {
    this.setDate(this.getDate() + d);
};
/* 当前日期 减少天 */;
Date.prototype.reduceDays = function(d) {
    this.setDate(this.getDate() - d);
};
/* 指定日期 增加月 */
Public.addMonths = function(date, Months) {
    var a = new Date(date);
    a.setMonth(a.getMonth() + Months);
    var b = a.getMonth() + 1,
        c = a.getDate();
    return a.getFullYear() + '-' + (b < 10 ? '0' : '') + b + '-' + (c < 10 ? '0' : '') + c;
};
/* 指定日期 减少月 */
Public.reduceMonths = function(date, Months) {
    var a = new Date(date);
    a.setMonth(a.getMonth() - Months);
    var b = a.getMonth() + 1,
        c = a.getDate();
    return a.getFullYear() + '-' + (b < 10 ? '0' : '') + b + '-' + (c < 10 ? '0' : '') + c;
};
/* 指定日期 增加天 */
Public.addDays = function(date, days) {
    var a = new Date(date);
    a.setDate(a.getDate() + days);
    var b = a.getMonth() + 1,
        c = a.getDate();
    return a.getFullYear() + '-' + (b < 10 ? '0' : '') + b + '-' + (c < 10 ? '0' : '') + c;
};
/* 指定日期 减少天 */
Public.reduceDays = function(date, days) {
    var a = new Date(date);
    a.setDate(a.getDate() - days);
    var b = a.getMonth() + 1,
        c = a.getDate();
    return a.getFullYear() + '-' + (b < 10 ? '0' : '') + b + '-' + (c < 10 ? '0' : '') + c;
};
/* ajax通用post请求 */
Public.ajaxPost = function(url, params, callback, errCallback) {
    if (url.includes('?')) {
        url += '&__time=' + Date.now();
    } else {
        url += '?__time=' + Date.now();
    }
    $.ajax({
        async: params.async != void 0 ? params.async : true,
        type: "POST",
        url: url,
        dataType: "json",
        data: params,
        timeout: 600000,
        success: function(data, status) {
            callback(data);
        },
        error: function(err, ms) {
            $('.wrapper > #div-lock').remove();
            if (errCallback) {
                errCallback(err);
            } else {
                parent.$.dialog.tips('服务端响应错误！', 2);
            }
        }
    });
};
/* ajax通用get请求 */
Public.ajaxGet = function(url, params, callback, errCallback) {
    if (url.includes('?')) {
        url += '&__time=' + Date.now();
    } else {
        url += '?__time=' + Date.now();
    }
    $.ajax({
        async: params.async != void 0 ? params.async : true,
        type: "GET",
        url: url,
        dataType: "json",
        data: params,
        timeout: 600000,
        success: function(data, status) {
            callback(data);
        },
        error: function(err) {
            $('.wrapper > #div-lock').remove();
            if (errCallback) {
                errCallback(err);
            } else {
                parent.$.dialog.tips('服务端响应错误！', 2);
            }
        }
    });
};
/* 扩展对象方法 */
$.fn.extend({
    //为对象新增ajaxPost方法
    ajaxPost: function(url, params, callback, errCallback) {
        if (url.includes('?')) {
            url += '&__time=' + Date.now();
        } else {
            url += '?__time=' + Date.now();
        }
        var $this = $(this);
        var loading;
        var myTimer;
        var preventTooFast = 'ui-btn-dis';
        var ajaxOpts = {
            type: "POST",
            url: url,
            data: params,
            dataType: "json",
            success: function(data, status) {
                callback(data);
            },
            error: function(err) {
                if (errCallback) {
                    errCallback(err);
                } else {
                    parent.$.dialog.tips('服务端响应错误！', 2);
                }
            }
        };
        $.extend(true, ajaxOpts, {
            timeout: 20000,
            beforeSend: function() {
                $this.addClass(preventTooFast);
                myTimer = setTimeout(function() {
                    $this.removeClass(preventTooFast);
                }, 2000);
                loading = $.dialog.tips('提交中，请稍候...', 0, 999);
            },
            success: function(data) {
                callback(data);
            },
            complete: function() {
                loading.close();
            },
            error: function(err) {
                if (errCallback) {
                    errCallback(err);
                } else {
                    parent.$.dialog.tips('服务端响应错误！', 2);
                }
            }
        });
        if ($this.hasClass(preventTooFast)) {
            return;
        }
        $.ajax(ajaxOpts);
    }
});
/* 数字格式 转为 货币格式 */
Public.numToCurrency = function(val, dec) {
    val = parseFloat(val);
    dec = dec || 2;//小数位
    if (val === 0 || isNaN(val)) {
        return '0.00';
    }
    val = val.toFixed(dec).split('.');
    var reg = /(\d{1,3})(?=(\d{3})+(?:$|\D))/g;
    return val[0].replace(reg, "$1,") + '.' + val[1];
};
/* 货币格式 转为 数字格式 */
Public.currencyToNum = function(val) {
    var val = String(val);
    if ($.trim(val) == '') {
        return 0;
    }
    val = val.replace(/,/g, '');
    val = parseFloat(val);
    return isNaN(val) ? 0 : val;
};
/* 只允许输入数字 */
Public.numerical = function(e) {
    var allowed = '0123456789.-', allowedReg;
    allowed = allowed.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
    allowedReg = new RegExp('[' + allowed + ']');
    var charCode = typeof e.charCode != 'undefined' ? e.charCode : e.keyCode;
    var keyChar = String.fromCharCode(charCode);
    if (!e.ctrlKey && charCode != 0 && !allowedReg.test(keyChar)) {
        e.preventDefault();
    };
};
/* 限制只能输入允许的字符，不支持中文的控制 */
Public.limitInput = function(obj, allowedReg) {
    var ctrlKey = null;
    obj.css('ime-mode', 'disabled').on('keydown', function(e) {
        ctrlKey = e.ctrlKey;
    }).on('keypress', function(e) {
        allowedReg = typeof allowedReg == 'string' ? new RegExp(allowedReg) : allowedReg;
        var charCode = typeof e.charCode != 'undefined' ? e.charCode : e.keyCode;
        var keyChar = $.trim(String.fromCharCode(charCode));
        if (!ctrlKey && charCode != 0 && charCode != 13 && !allowedReg.test(keyChar)) {
            e.preventDefault();
        }
    });
};
/* 限制输入的字符长度 */
Public.limitLength = function(obj, count) {
    obj.on('keyup', function(e) {
        if (count < obj.val().length) {
            e.preventDefault();
            obj.val(obj.val().substr(0, count));
        }
    });
};
/* 文本列表滚动 */
Public.txtSlide = function(opt) {
    var def = {
        notice: '#notices > ul',
        size: 1, //显示出来的条数
        pause_time: 3000, //每次滚动后停留的时间
        speed: 'fast', //滚动动画执行的时间
        stop: true //鼠标移到列表时停止动画
    };
    opt = opt || {};
    opt = $.extend({}, def, opt);
    var $list = $(opt.notice),
        $lis = $list.children(),
        height = $lis.eq(0).outerHeight() * opt.size,
        interval_id;
    if ($lis.length <= opt.size) return;
    interval_id = setInterval(begin, opt.pause_time);
    opt.stop && $list.on({
        'mouseover': function() {
            clearInterval(interval_id);
            $list.stop(true, true);
        },
        'mouseleave': function() {
            interval_id = setInterval(begin, opt.pause_time);
        }
    });
    function begin() {
        $list.stop(true, true).animate({marginTop: -height}, opt.speed, function() {
            for (var i = 0; i < opt.size; i++) {
                $list.append($list.find('li:first'));
            }
            $list.css('margin-top', 0);
        });
    }
};
/* 绑定enter键 */
Public.bindEnterSkip = function(obj, func) {
    var args = arguments;
    $(obj).on('keydown', 'input:visible:not(:disabled)', function(e) {
        if (e.keyCode == '13') {
            var inputs = $(obj).find('input:visible:not(:disabled)');
            var idx = inputs.index($(this));
            idx = idx + 1;
            if (idx >= inputs.length) {
                if (typeof func == 'function') {
                    var _args = Array.prototype.slice.call(args, 2);
                    func.apply(null, _args);
                }
            } else {
                inputs.eq(idx).focus();
            }
        }
    });
};
/* enter键 */
$.fn.enterKey = function() {
    this.each(function() {
        $(this).keydown(function(e) {
            if (e.which == 13) {
                var ref = $(this).data("ref");
                if (ref) {
                    $('#' + ref).select().focus().click();
                } else {
                    eval($(this).data("enterKeyHandler"));
                }
            }
        });
    });
};
/* 单选框插件 */
$.fn.cssRadio = function(opts) {
    var opts = $.extend({}, opts);
    var $_radio = $('label.radio', this), $_this = this;
    $_radio.each(function() {
        var self = $(this);
        if (self.find("input")[0].checked) {
            self.addClass("checked");
        };
    }).hover(function() {
        $(this).addClass("over");
    }, function() {
        $(this).removeClass("over");
    }).click(function(event) {
        $_radio.find("input").removeAttr("checked");
        $_radio.removeClass("checked");
        $(this).find("input").attr("checked", "checked");
        $(this).addClass("checked");
        opts.callback($(this));
    });
    return {
        getValue: function() {
            return $_radio.find("input[checked]").val();
        },
        setValue: function(index) {
            return $_radio.eq(index).click();
        }
    };
};
/* 复选框插件 */
$.fn.cssCheckbox = function() {
    var $_chk = $(".chk", this);
    $_chk.each(function() {
        if ($(this).find("input")[0].checked) {
            $(this).addClass("checked");
        };
        if ($(this).find("input")[0].disabled) {
            $(this).addClass("dis_check");
        };
    }).hover(function() {
        $(this).addClass("over");
    }, function() {
        $(this).removeClass("over");
    }).click(function(event) {
        if ($(this).find("input")[0].disabled) {
            return;
        };
        $(this).toggleClass("checked");
        $(this).find("input")[0].checked = !$(this).find("input")[0].checked;
        event.preventDefault();
    });
    return {
        chkAll: function() {
            $_chk.addClass("checked");
            $_chk.find("input").attr("checked", "checked");
        },
        chkNot: function() {
            $_chk.removeClass("checked");
            $_chk.find("input").removeAttr("checked");
        },
        chkVal: function() {
            var val = [];
            $_chk.find("input:checked").each(function() {
                val.push($(this).val());
            });
            return val;
        }
    };
};
/* 数字输入框 */
$.fn.digital = function() {
    this.each(function() {
        $(this).keyup(function() {
            this.value = this.value.replace(/\D/g, '');
        });
    });
};
/* 判断:当前元素是否是被筛选元素的子元素 */
$.fn.isChildOf = function(b) {
    return (this.parents(b).length > 0);
};
/* 判断:当前元素是否是被筛选元素的子元素或者本身 */
$.fn.isChildAndSelfOf = function(b) {
    return (this.closest(b).length > 0);
};/* 权限验证 */
Business.verifyRight = function(right) {
    let system = Public.getDefaultPage().SYSTEM;
    let rights = system.rights;
    // 管理员直接放行
    if (system.user.roleId == 1) {
        return true;
    }
    // 空权限
    if (!right) {
        return false;
    }
    // && 逻辑：所有权限都要满足
    if (right.indexOf('&&') !== -1) {
        let rightArr = right.split('&&');
        for (let i = 0; i < rightArr.length; i++) {
            if ($.inArray(rightArr[i], rights) === -1) {
                $.dialog.tips('对不起，权限不足！', 2);
                return false;
            }
        }
        return true;
    }
    // || 逻辑：满足其中一个即可
    if (right.indexOf('||') !== -1) {
        let rightArr = right.split('||');
        if (rightArr.some(v => $.inArray(v, rights) !== -1)) {
            return true;
        }
        $.dialog.tips('对不起，权限不足！', 2);
        return false;
    }
    // 单个权限
    if ($.inArray(right, rights) !== -1) {
        return true;
    }
    $.dialog.tips('对不起，权限不足！', 2);
    return false;
};
/* 获取文件 */
Business.getFile = function(url, args, isNewWinOpen) {
    if (typeof url != 'string') {
        return;
    }
    var url = url.indexOf('?') == -1 ? url += '?' : url;
    if (args.id) {
        url += '&id=' + args.id + '&random=' + new Date().getTime();
    } else {
        url += '&random=' + new Date().getTime();
    };
    var downloadForm = parent.$('form#downloadForm');
    if (downloadForm.length == 0) {
        downloadForm = parent.$('<form method="post" />').attr('id', 'downloadForm').hide().appendTo('body');
    } else {
        downloadForm.empty();
    }
    downloadForm.attr('action', url);
    for (k in args) {
        $('<input type="hidden" />').attr({name: k, value: args[k]}).appendTo(downloadForm);
    }
    if (isNewWinOpen) {
        downloadForm.attr('target', '_blank');
    } else {
        var downloadIframe = $('iframe#downloadIframe');
        if (downloadIframe.length == 0) {
            downloadIframe = $('<iframe />').attr('id', 'downloadIframe').hide().appendTo('body');
        }
        downloadForm.attr('target', 'downloadIframe');
    }
    downloadForm.trigger('submit');
};
/* 单据功能事件 */
Business.billsEvent = function(obj, type) {
    var _self = obj;
    // 新增一行
    $('.grid-wrap').on('click', '.ui-icon-plus', function(a) {
        var rowId = $(this).parent().data('id');
        var datarow = {id: _self.newId};
        var su = $("#grid").jqGrid('addRowData', _self.newId, datarow, 'last', rowId);
        if (su) {
            $(this).parents('td').removeAttr('class');
            $(this).parents('tr').removeClass('selected-row ui-state-hover');
            $("#grid").jqGrid('resetSelection');
            _self.newId++;
        }
    });
    // 删除本行
    $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
        if ($('#grid tbody tr').length === 2) {
            $.dialog.tips('至少保留一行！', 2);
            return false;
        }
        var rowId = $(this).parent().data('id');
        var su = $("#grid").jqGrid('delRowData', rowId);
        if (su) {
            _self.calTotal && _self.calTotal();
        };
    });
    // 保存行数据并取消行编辑状态
    $(document).on('click.cancel', function(a) {
        if (!$(a.target).closest(".ui-jqgrid-bdiv").length > 0 && $(a.target).closest(".pika-single").length == 0 && curRow && curCol) {
            $("#grid").jqGrid("saveCell", curRow, curCol);
            curRow = curCol = null;
        };
    });
    // 选择客户 | 商家
    $('#contact').on('click', '.input-txt, .ui-icon-ellipsis', function(e) {
        var contactType = $('#contact').data('type');
        if (contactType === 'customer') {
            var title = '选择客户';
            var content = 'url:/Uri/select_contact?type=0';
        } else if (contactType === 'supplier') {
            var title = '选择商家';
            var content = 'url:/Uri/select_contact?type=1';
        } else {
            var title = '选择客户或商家';
            var content = 'url:/Uri/select_contact?type=-1';
        }
        _self.contactDialog = $.dialog({
            width: 1250,
            height: 500,
            title: title,
            content: content,
            data: {
                isSingle: !0
            },
            max: false,
            min: false,
            lock: true,
            ok: function() {
                if (typeof this.content.THISPAGE.callback === 'function') {
                    this.content.THISPAGE.callback();
                    this.close();
                }
                return false;
            },
            cancel: function() {
                return true;
            }
        });
    });
    // 批量选择产品
    $('.grid-wrap').on('click', '.goods .goodsAuto, .goods .ui-icon-ellipsis', function(e) {
        $.dialog({
            width: 1250,
            height: 500,
            title: '选择产品',
            content: 'url:/Uri/goods_batch',
            data: {
                callback: function(newId, curID, curRow) {
                    if (curID === '') {
                        $("#grid").jqGrid('addRowData', newId, {}, 'last');
                        _self.newId = newId + 1;
                    };
                    setTimeout(function() {
                        $("#grid").jqGrid("editCell", curRow, 2, true);
                    }, 10);
                    _self.calTotal && _self.calTotal();
                }
            },
            max: false,
            min: false,
            lock: true,
            button: [{
                name: '提取',
                defClass: 'ui_state_highlight',
                callback: function() {
                    this.content.THISPAGE.callback && this.content.THISPAGE.callback(type);
                    return false;
                }
            }, {
                name: '提取并关闭',
                defClass: 'ui_state_highlight',
                callback: function() {
                    this.content.THISPAGE.callback && this.content.THISPAGE.callback(type);
                    this.close();
                    return false;
                }
            }, {
                name: '关闭',
                callback: function() {
                    return true;
                }
            }]
        });
        $(this).data('hasInstance', true);
    });
    // 仓库批量操作
    initStorage();
    function initStorage() {
        Public.ajaxPost("/Base/Storage/list", {}, function(d) {
            if (d && 200 == d.status) {
                var data = d.data.rows;
            } else {
                var data = [];
                $.dialog.tips('获取仓库信息失败！', 2);
            }
            var list = '<ul>';
            for (var i = 0, len = data.length; i < len; i++) {
                if (data[i].isDisable == 1) {
                    continue;
                }
                list += '<li data-id="' + data[i].id + '" data-name="' + data[i].name + '" >' + data[i].id + ' ' + data[i].name + '</li>';
            };
            list += '</ul>';
            $("#storageBox").html(list);
        });
    };
    $("#batchStorage").powerFloat({
        eventType: "click",
        hoverHold: false,
        reverseSharp: true,
        target: function() {
            if (curRow !== null && curCol !== null) {
                $("#grid").jqGrid("saveCell", curRow, curCol);
                curRow = null;
                curCol = null;
            };
            return $("#storageBox");
        }
    });
    $('.wrapper').on('click', '#storageBox li', function(e) {
        var stoId = $(this).data('id');
        var stoName = $(this).data('name');
        var ids = $("#grid").jqGrid('getDataIDs');
        var batName = 'storageName';
        var batInfo = 'storageInfo';
        for (var i = 0, len = ids.length; i < len; i++) {
            var id = ids[i];
            var row = $("#grid").jqGrid('getRowData', id);
            var $_id = $('#' + id);
            if (row.goods === '' || $_id.data('goodsInfo') === undefined) {
                continue;//跳过无效行
            };
            var setData = {};
            setData[batName] = stoName;
            $("#grid").jqGrid('setRowData', id, setData);
            $('#' + id).data(batInfo, {id: stoId, name: stoName});
        };
        $.powerFloat.hide();
    });
};
/* 子产品选择事件 */
Business.querySonGoodsEvent = function(obj) {
    var _self = obj;
    //新增一行
    $(".grid-wrap").on("click", ".ui-icon-plus", function(a) {
        var rowId = $(this).parent().data('id');
        var datarow = {id: _self.newId};
        var su = $("#grid").jqGrid('addRowData', _self.newId, datarow, 'last', rowId);
        if (su) {
            $(this).parents('td').removeAttr('class');
            $(this).parents('tr').removeClass('selected-row ui-state-hover');
            $("#grid").jqGrid('resetSelection');
            _self.newId++;
        }
    });
    //删除本行
    $(".grid-wrap").on("click", ".ui-icon-trash", function(a) {
        if ($('#grid tbody tr').length === 2) {
            $.dialog.tips('至少保留一行！', 2);
            return false;
        }
        var rowId = $(this).parent().data("id");
        var su = $("#grid").jqGrid("delRowData", rowId);
        if (su) {
            _self.calTotal && _self.calTotal();
        };
    });
    //选择子产品
    $('.grid-wrap').on('click', '.goods .goodsAuto, .goods .ui-icon-ellipsis', function(a) {
        $.dialog({
            width: 1250,
            height: 500,
            title: '选择子产品',
            content: 'url:/Uri/goods_batch',
            data: {
                isSonGoods: !0,
                isNoAdd: !0,
                skey: _self.skey,
                categoryId: _self.categoryId,
                callback: function(newId, curID, curRow) {
                    if (curID === '') {
                        $("#grid").jqGrid('addRowData', newId, {}, 'last');
                        _self.newId = newId + 1;
                    };
                    setTimeout(function() {
                        $("#grid").jqGrid("editCell", curRow, 2, true);
                    }, 10);
                    _self.calTotal && _self.calTotal();
                }
            },
            max: false,
            min: false,
            lock: true,
            button: [{
                name: '提取',
                defClass: 'ui_state_highlight',
                callback: function() {
                    this.content.THISPAGE.callback && this.content.THISPAGE.callback();
                    return false;
                }
            }, {
                name: '提取并关闭',
                defClass: 'ui_state_highlight',
                callback: function() {
                    this.content.THISPAGE.callback && this.content.THISPAGE.callback();
                    this.close();
                    return false;
                }
            }, {
                name: '关闭',
                callback: function() {
                    return true;
                }
            }]
        });
        $(this).data('hasInstance', true);
    });
};
/* 类别组合框 【多场景复用】*/
Business.categoryCombo = function($_obj, opts, type) {
    if ($_obj.length == 0) {
        return;
    }
    if (typeof opts != 'object') {
        opts = {};
    }
    var typeNumber = type || '';
    if (!typeNumber) {
        return;
    }
    var opts = $.extend(true, {
        data: '/Base/Category/list?typeNumber=' + typeNumber,
        ajaxOptions: {
            formatData: function(data) {
                return data.data.rows;
            }
        },
        maxListWidth: 600,
        value: 'id',
        text: 'name'
    }, opts);
    var categoryCombo = $_obj.combo(opts).getCombo();
    return categoryCombo;
};
/* 产品组合框 */
Business.goodsCombo = function($_obj, opts) {
    if ($_obj.length == 0) {
        return;
    }
    if (typeof opts != 'object') {
        opts = {};
    }
    var opts = $.extend(true, {
        data: [],
        value: 'id',
        text: 'no',
        defaultSelected: -1,
        forceSelection: !1,
        trigger: !1,
        callback: {
            onChange: function(data) {
                if (data) {
                    $_obj.data('goodsInfo', data);
                } else {
                    $_obj.removeData('goodsInfo');
                }
            }
        }
    }, opts);
    var goodsCombo = $_obj.combo(opts).getCombo();
    return goodsCombo;
};
/* 客户组合框 */
Business.customerCombo = function($_obj, opts) {
    if ($_obj.length == 0) {
        return;
    }
    if (typeof opts != 'object') {
        opts = {};
    }
    var opts = $.extend(true, {
        data: [],
        value: 'id',
        text: 'name',
        defaultSelected: -1,
        forceSelection: !1,
        trigger: !1,
        callback: {
            onChange: function(data) {
                if (data) {
                    $_obj.data('contactData', data);
                } else {
                    $_obj.removeData('contactData');
                }
            }
        }
    }, opts);
    var customerCombo = $_obj.combo(opts).getCombo();
    return customerCombo;
};
/* 商家组合框 */
Business.supplierCombo = function($_obj, opts) {
    if ($_obj.length == 0) {
        return;
    }
    if (typeof opts != 'object') {
        opts = {};
    }
    var opts = $.extend(true, {
        data: [],
        value: 'id',
        text: 'name',
        defaultSelected: -1,
        forceSelection: !1,
        trigger: !1,
        callback: {
            onChange: function(data) {
                if (data) {
                    $_obj.data('contactData', data);
                } else {
                    $_obj.removeData('contactData');
                }
            }
        }
    }, opts);
    var supplierCombo = $_obj.combo(opts).getCombo();
    return supplierCombo;
};
/* 仓库组合框 */
Business.storageCombo = function($_obj, opts) {
    if ($_obj.length == 0) {
        return;
    }
    if (typeof opts != 'object') {
        opts = {};
    }
    var opts = $.extend(true, {
        data: '/Base/Storage/list',
        ajaxOptions: {
            formatData: function(res) {
                return res.data.rows;
            }
        },
        value: 'id',
        text: 'name',
        defaultSelected: 0,
        defaultFlag: !1,
        forceSelection: !1,
        trigger: !1,
        callback: {
            onChange: function(data) {
                var parentTr = this.input.parents('tr');
                if (data) {
                    parentTr.data('storageInfo', {id: data.id, name: data.name});
                }
            }
        }
    }, opts);
    var storageCombo = $_obj.combo(opts).getCombo();
    return storageCombo;
};
/* 结算账户组合框 */
Business.companyAccountCombo = function($_obj, opts) {
    if ($_obj.length == 0) {
        return;
    }
    if (typeof opts != 'object') {
        opts = {};
    }
    var opts = $.extend(true, {
        data: '/Base/CompanyAccount/list?rows=1000&isDisable=0',
        ajaxOptions: {
            formatData: function(data) {
                return data.data.rows;
            }
        },
        value: 'id',
        text: 'name',
        defaultSelected: 0,
        defaultFlag: !1,
        trigger: !1
    }, opts);
    var companyAccountCombo = $_obj.combo(opts).getCombo();
    return companyAccountCombo;
};
/* 员工组合框 */
Business.userCombo = function($_obj, opts, otherOpts) {
    if ($_obj.length == 0) {
        return;
    }
    if (typeof opts != 'object') {
        opts = {};
    }
    var department = otherOpts.department || '';
    var isDisable = otherOpts.isDisable || '';
    var normal = otherOpts.normal || '';
    var opts = $.extend(true, {
        data: '/Base/User/queryAll?department=' + department + '&isDisable=' + isDisable + '&normal=' + normal,
        ajaxOptions: {
            formatData: function(res) {
                return res.data.rows;
            }
        },
        value: 'id',
        text: 'name',
        defaultSelected: -1,
        defaultFlag: !1,
        trigger: !1
    }, opts);
    var userCombo = $_obj.combo(opts).getCombo();
    return userCombo;
};
/* 过滤器【产品】初始化 */
Business.filterGoods = function(isSingle) {
    Business.goodsCombo($('#goodsAuto'), {
        data: [],
        defaultSelected: -1,
        forceSelection: !1,
        callback: {
            onChange: function(data) {
                if (data) {
                    this.input.data('ids', data.id);
                    this.input.val(data.id);
                }
            }
        }
    });
    $('#filter-goods').on('click', function() {
        var $input = $(this).children('input');
        if ($(this).parents('.mod-search').find('#search').parent().find('.current').length > 0) {
            $search = $(this).parents('.mod-search').find('#search').parent().find('.current');
        } else {
            if ($(this).parents('.mod-search').find('#search').length > 0) {
                $search = $(this).parents('.mod-search').find('#search');
            } else {
                $search = null;
            }
        }
        $.dialog({
            width: 1250,
            height: 500,
            title: '选择产品',
            content: 'url:/Uri/goods_batch',
            data: {
                isNoAdd: !0,
                isSingle: isSingle || !1
            },
            max: false,
            min: false,
            lock: true,
            ok: function() {
                Business.setFilterData(this.content, $input);
                $search && $search.trigger('click');
            },
            cancel: function() {
                return true;
            }
        });
    });
};
/* 过滤器【客户】初始化 */
Business.filterCustomer = function(isSingle) {
    Business.customerCombo($('#customerAuto'), {
        data: [],
        width: '',
        editable: !1,
        defaultSelected: -1,
        forceSelection: !1,
        extraListHtml: '',
        callback: {
            onChange: function(data) {
                if (data) {
                    this.input.data('ids', data.id);
                    this.input.val(data.id);
                }
            }
        }
    });
    $('#filter-customer').on('click', function() {
        var $input = $(this).children('input');
        if ($(this).parents('.mod-search').find('#search').parent().find('.current').length > 0) {
            $search = $(this).parents('.mod-search').find('#search').parent().find('.current');
        } else {
            if ($(this).parents('.mod-search').find('#search').length > 0) {
                $search = $(this).parents('.mod-search').find('#search');
            } else {
                $search = null;
            }
        }
        $.dialog({
            width: 1250,
            height: 500,
            title: '选择客户',
            content: 'url:/Uri/customer_batch',
            data: {
                isSingle: isSingle || !1
            },
            max: false,
            min: false,
            lock: true,
            ok: function() {
                Business.setFilterData(this.content, $input);
                $search && $search.trigger('click');
            },
            cancel: function() {
                return true;
            }
        });
    });
};
/* 过滤器【商家】初始化 */
Business.filterSupplier = function(isSingle) {
    Business.supplierCombo($('#supplierAuto'), {
        data: [],
        width: '',
        editable: !1,
        defaultSelected: -1,
        forceSelection: !1,
        extraListHtml: '',
        callback: {
            onChange: function(data) {
                if (data) {
                    this.input.data('ids', data.id);
                    this.input.val(data.id);
                }
            }
        }
    });
    $('#filter-supplier').on('click', function() {
        var $input = $(this).children('input');
        if ($(this).parents('.mod-search').find('#search').parent().find('.current').length > 0) {
            $search = $(this).parents('.mod-search').find('#search').parent().find('.current');
        } else {
            if ($(this).parents('.mod-search').find('#search').length > 0) {
                $search = $(this).parents('.mod-search').find('#search');
            } else {
                $search = null;
            }
        }
        $.dialog({
            width: 1250,
            height: 500,
            title: '选择商家',
            content: 'url:/Uri/supplier_batch',
            data: {
                isSingle: isSingle || !1
            },
            max: false,
            min: false,
            lock: true,
            ok: function() {
                Business.setFilterData(this.content, $input);
                $search && $search.trigger('click');
            },
            cancel: function() {
                return true;
            }
        });
    });
};
/* 过滤器【仓库】初始化 */
Business.filterStorage = function(isSingle) {
    Business.storageCombo($('#storageAuto'), {
        data: [],
        editable: !1,
        defaultSelected: -1,
        forceSelection: !1,
        callback: {
            onChange: function(data) {
                if (data) {
                    this.input.data('ids', data.id);
                    this.input.val(data.id);
                }
            }
        }
    });
    $('#filter-storage').on('click', function() {
        var $input = $(this).children('input');
        if ($(this).parents('.mod-search').find('#search').parent().find('.current').length > 0) {
            $search = $(this).parents('.mod-search').find('#search').parent().find('.current');
        } else {
            if ($(this).parents('.mod-search').find('#search').length > 0) {
                $search = $(this).parents('.mod-search').find('#search');
            } else {
                $search = null;
            }
        }
        $.dialog({
            width: 1250,
            height: 500,
            title: '选择仓库',
            content: 'url:/Uri/storage_batch',
            data: {
                isSingle: isSingle || !1
            },
            max: false,
            min: false,
            lock: true,
            ok: function() {
                Business.setFilterData(this.content, $input);
                $search && $search.trigger('click');
            },
            cancel: function() {
                return true;
            }
        });
    });
};
/* 将过滤器返回的数据记录到相应的input中 */
Business.setFilterData = function(dialogCtn, $input) {
    var numbers = [];
    var ids = [];
    for (rowid in dialogCtn.addList) {
        var row = dialogCtn.addList[rowid];
        ids.push(rowid);
        numbers.push(row.id);
    }
    $input.data('ids', ids.join(',')).val(numbers.join(','));
};
/* 产品类别树 */
Public.zTree = {
    zTree: {},
    opts: {
        showRoot: true, //是否显示根节点
        rootTxt: '全部',//根节点名称
        defaultClass: 'ztreeDefault',//基础CSS样式名称
        callback: ''
    },
    setting: {
        view: {
            dblClickExpand: true, // 是否双击展开|收缩
            showLine: true, // 是否显示层级线
            selectedMulti: true // 是否可多选
        },
        data: {
            simpleData: {
                enable: true,
                idKey: "id",
                pIdKey: "parentId",
                rootPId: ""
            }
        },
        callback: {
            beforeClick: function(treeId, treeNode) { }
        }
    },
    _getTemplate: function(opts) {
        this.id = 'tree' + parseInt(Math.random() * 10000);
        var _defaultClass = "ztree";
        if (opts) {
            if (opts.defaultClass) {
                _defaultClass += ' ' + opts.defaultClass;
            }
        }
        return '<ul id="' + this.id + '" class="' + _defaultClass + '"></ul>';
    },
    init: function($target, opts, setting, callback) {
        if ($target.length === 0) {
            return;
        }
        var self = this;
        self.opts = $.extend(true, self.opts, opts);
        self.container = $($target);
        self.obj = $(self._getTemplate(opts));
        self.container.append(self.obj);
        setting = $.extend(true, self.setting, setting);
        Public.ajaxGet(opts.url || '/Base/Category/list?typeNumber=goodstype&sidx=ordnum&sord=asc' + (self.opts.onlyposition ? '&onlyposition=' + self.opts.onlyposition : ''), {}, function(data) {
            if (data.status === 200 && data.data) {
                self._callback(data.data.rows);
            } else {
                $.dialog.tips('加载失败！', 2);
            }
        });
        return self;
    },
    _callback: function(data) {
        var self = this;
        var callback = self.opts.callback;
        if (self.opts.showRoot) {
            data.unshift({name: self.opts.rootTxt, id: 0, open: true});
            self.obj.addClass('showRoot');
        }
        if (!data.length) return;
        self.zTree = $.fn.zTree.init(self.obj, self.setting, data);
        if (callback && typeof callback === 'function') {
            callback(self, data);
        }
    }
};
/* 产品类别下拉框 在产品类别树基础上封装 */
Public.categoryTree = function($obj, opts) {
    if ($obj.length === 0) {
        return;
    }
    opts = opts ? opts : opts = {};
    var opts = $.extend(true, {
        inputWidth: '230',
        width: 'auto', //'auto' or int
        height: 'auto', //'auto' or int
        trigger: !0,
        defaultClass: 'ztreeCombo',
        disExpandAll: !1, //展开全部
        defaultSelectValue: 0,
        showRoot: !0, //显示root选择
        rootTxt: '全部',
        treeSettings: {
            callback: {
                beforeClick: function(treeId, treeNode) {
                    if (_Combo.obj) {
                        _Combo.obj.val(treeNode.name);
                        _Combo.obj.data('id', treeNode.id);
                        _Combo.hideTree();
                    }
                }
            }
        }
    }, opts);
    var _Combo = {
        container: $('<span class="ui-tree-wrap" style="width:' + opts.inputWidth + 'px"></span>'),
        obj: $('<input type="text" class="input-txt" style="width:' + (opts.inputWidth - 30) + 'px" id="' + $obj.attr('id') + '" autocomplete="off" readonly value="' + ($obj.val() || $obj.text()) + '">'),
        trigger: $('<span class="trigger iconfont icon-xiajiantou"></span>'),
        data: {},
        init: function() {
            var _parent = $obj.parent();
            var _this = this;
            $obj.remove();
            this.obj.appendTo(this.container);
            if (opts.trigger) {
                this.container.append(this.trigger);
            }
            this.container.appendTo(_parent);
            opts.callback = function(publicTree, data) {
                _this.zTree = publicTree;
                if (publicTree) {
                    publicTree.obj.css({
                        'max-height': opts.height
                    });
                    for (var i = 0, len = data.length; i < len; i++) {
                        _this.data[data[i].id] = data[i];
                    };
                    if (opts.defaultSelectValue !== '') {
                        _this.selectByValue(opts.defaultSelectValue);
                    };
                    _this._eventInit();
                }
            };
            this.zTree = Public.zTree.init($('body'), opts, opts.treeSettings);
            return this;
        },
        showTree: function() {
            if (!this.zTree) return;
            if (this.zTree) {
                var offset = this.obj.offset();
                var topDiff = this.obj.outerHeight();
                var w = opts.width ? opts.width : this.obj.width();
                var _o = this.zTree.obj.hide();
                _o.css({width: w, top: offset.top + topDiff, left: offset.left - 1});
            }
            var _o = this.zTree.obj.show();
            this.isShow = true;
            this.container.addClass('ui-tree-active');
        },
        hideTree: function() {
            if (!this.zTree) return;
            var _o = this.zTree.obj.hide();
            this.isShow = false;
            this.container.removeClass('ui-tree-active');
        },
        _eventInit: function() {
            var _this = this;
            if (opts.trigger) {
                _this.trigger.on('click', function(e) {
                    e.stopPropagation();
                    if (_this.zTree && !_this.isShow) {
                        _this.showTree();
                    } else {
                        _this.hideTree();
                    }
                });
            };
            _this.obj.on('click', function(e) {
                e.stopPropagation();
                if (_this.zTree && !_this.isShow) {
                    _this.showTree();
                } else {
                    _this.hideTree();
                }
            });
            if (_this.zTree) {
                _this.zTree.obj.on('click', function(e) {
                    e.stopPropagation();
                });
            }
            $(document).click(function() {
                _this.hideTree();
            });
        },
        getValue: function() {
            var id = this.obj.data('id') || '';
            if (!id) {
                var text = this.obj.val();
                if (this.data) {
                    for (var item in this.data) {
                        if (this.data[item].name === text) {
                            id = this.data[item].id;
                        }
                    }
                }
            }
            return id;
        },
        getText: function() {
            if (this.obj.data('id'))
                return this.obj.val();
            return '';
        },
        selectByValue: function(value) {
            if (value !== '') {
                if (this.data) {
                    this.obj.data('id', value).val(this.data[value].name);
                }
            } else {
                this.obj.data('id', '').val('');
            }
            return this;
        }
    };
    return _Combo.init();
};
/* 兼容IE8 数组对象不支持indexOf() */
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(elt /*, from*/) {
        var len = this.length >>> 0;
        var from = Number(arguments[1]) || 0;
        from = (from < 0)
            ? Math.ceil(from)
            : Math.floor(from);
        if (from < 0)
            from += len;
        for (; from < len; from++) {
            if (from in this &&
                this[from] === elt)
                return from;
        }
        return -1;
    };
};
/* 省市区下拉框（省下拉框ID：provinceCombo，市下拉框ID：cityCombo，，区下拉框ID：areaCombo，需要搭配COMBO插件，默认值放在data('defaultValue') */
var mod_AreasCombo = (function(mod) {
    var _areasList = [],
        _capable = false,//模块启动开关
        _provinceList = [],
        _cityList = [],
        _areasCahe = {},
        _pCombo, _cCombo, _aCombo, _opt;
    mod.init = function(_provinceCombo, _cityCombo, _areaCombo, opt, callback) {
        _pCombo = _provinceCombo;
        _cCombo = _cityCombo;
        _aCombo = _areaCombo;
        _opt = opt || {};
        //缓存省市数据
        if (!(_provinceCombo && _cityCombo && _areaCombo)) {
            return;
        }
        Public.ajaxGet('/Base/Areas/list', {rows: 9999}, function(data) {
            if (data) {
                _capable = true;
                _areasList = data.data.rows;
                for (i = 0, len = _areasList.length; i < len; i++) {
                    //中国的省
                    if (_areasList[i].type === 2 && _areasList[i].parentId === 1) {
                        _provinceList.push({name: _areasList[i].name, id: _areasList[i].id, type: 2, parentId: 1});
                    }
                    //中国的市
                    if (_areasList[i].type === 3 && _areasList[i].parentId >= 110000) {
                        _cityList.push({name: _areasList[i].name, id: _areasList[i].id, type: _areasList[i].type, parentId: _areasList[i].parentId});
                    }
                }
                mod.provinceCombo = _getCombo(_pCombo, _opt, _getProvinceData());
                mod.cityCombo = _getCombo(_cCombo, _opt, []);
                mod.areaCombo = _getCombo(_aCombo, _opt, []);
                callback();
            } else {
                $.dialog.tips('初始化省市区失败！', 2);
            }
        });
        return mod;
    };
    function _getCombo(obj, opt, data) {
        var _disabled = opt.disabled || !1;
        var _listHeight = opt.listHeight || 155;
        return obj.combo({
            data: data,
            text: 'name',
            value: 'id',
            placeholder: '请选择',
            width: 150,
            listHeight: _listHeight,
            defaultSelected: -1,
            cache: !1,
            editable: !1,
            disabled: _disabled,
            trigger: !0,
            callback: {
                onFocus: null,
                onBlur: null,
                beforeChange: null,
                onChange: function() {
                    switch (this) {
                        case mod.provinceCombo:
                            mod.cityCombo.loadData(_getCityData(mod.provinceCombo.getValue()), -1, false);
                            mod.areaCombo.loadData([], -1, false);
                            break;
                        case mod.cityCombo:
                            mod.areaCombo.loadData(_getAreaData(mod.cityCombo.getValue()), -1, false);
                            break;
                        case mod.areaCombo:
                            break;
                        default: break;
                    }
                },
                onExpand: null,
                onCollapse: null,
                onListClick: function() {
                    if ($("#search").length > 0) {
                        $('#search').trigger('click');
                    }
                }
            }
        }).getCombo();
    };
    function _getProvinceData() {
        var _data = [];
        for (i = 0, len = _provinceList.length; i < len; i++) {
            if (_provinceList[i].type === 2 && _provinceList[i].parentId === 1) {
                _data.push({name: _provinceList[i].name, id: _provinceList[i].id});
            }
        }
        return _data;
    };
    function _getCityData(PID) {
        var _data = [];
        for (i = 0, len = _cityList.length; i < len; i++) {
            if (_cityList[i].parentId === PID) {
                _data.push({name: _cityList[i].name, id: _cityList[i].id});
            }
        }
        return _data;
    };
    function _getAreaData(PID) {
        var _data = [];
        //查找缓存
        if (_areasCahe[PID]) {
            _data = _areasCahe[PID].areaData;
        }
        //没有缓存则全表查找
        else {
            for (i = 0, len = _areasList.length; i < len; i++) {
                if (_areasList[i].type === 4 && _areasList[i].parentId === PID) {
                    _data.push({name: _areasList[i].name, id: _areasList[i].id});
                }
            }
            //缓存该次查找结果
            _areasCahe[PID] = {areaData: _data};
        }
        return _data;
    };
    return mod;
})(mod_AreasCombo || {});
/* 自定义事件替换html默认title显示效果 */
$(document).ready(function() {
    $(document).on({
        mouseenter: function(e) {
            var elem = $(this);
            var title = '';
            if ((typeof elem.attr('title') !== "undefined" && $.trim(elem.attr('title')) != '') || (typeof elem.attr('diytitle') !== "undefined" && $.trim(elem.attr('diytitle')) != '')) {
                title = elem.attr('title') || elem.attr('diytitle');
            }
            if (title) {
                var diyTitleDiv = $('.diyTitle').length > 0 ? $('.diyTitle') : $('<div class="diyTitle" />');
                var options = {
                    'follow': !0
                };
                elem.attr({'title': '', 'diytitle': title});
                diyTitleDiv.html(title).appendTo('body').hide();
                var _ww = $(window).width(), _wh = $(window).height(), _tw = $('body div').last().outerWidth(), _th = $('body div').last().outerHeight();
                if (options.follow) {
                    elem.mousemove(function(e) {
                        var _top = e.pageY + 15, _left = e.pageX + 15;
                        if (_top > _wh + $(document).scrollTop() - _th) {_top = e.pageY - _th - 15;}
                        if (_left > _ww + $(document).scrollLeft() - _tw) {_left = e.pageX - 15 - _tw;}
                        diyTitleDiv.css({
                            top: _top,
                            left: _left
                        });
                    });
                } else {
                    var _top = e.pageY + 15, _left = e.pageX + 15;
                    if (_top > _wh + $(document).scrollTop() - _th) {_top = e.pageY - _th - 15;}
                    if (_left > _ww + $(document).scrollLeft() - _tw) {_left = e.pageX - 15 - _tw;}
                    diyTitleDiv.css({
                        top: _top,
                        left: _left
                    });
                }
                diyTitleDiv.fadeIn(100);
            }
        },
        mouseleave: function(e) {
            $('.diyTitle').remove();
        }
    }, '*');
});
