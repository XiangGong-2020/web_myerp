/* jQuery ligerUI 1.3.2 http://ligerui.com */

/* 基类 */
(function($) {
    //ligerui 继承方法
    Function.prototype.ligerExtend = function(parent, overrides) {
        if (typeof parent != 'function') return this;
        //保存对父类的引用
        this.base = parent.prototype;
        this.base.constructor = parent;
        //继承
        var f = function() { };
        f.prototype = parent.prototype;
        this.prototype = new f();
        this.prototype.constructor = this;
        //附加属性方法
        if (overrides) $.extend(this.prototype, overrides);
    };
    //延时加载
    Function.prototype.ligerDefer = function(o, defer, args) {
        var fn = this;
        return setTimeout(function() {fn.apply(o, args || []);}, defer);
    };
    // 核心对象
    window.liger = $.ligerui = {
        version: 'V1.3.2',
        managerCount: 0,
        //组件管理器池
        managers: {},
        managerIdPrev: 'ligerui',
        //管理器id已经存在时自动创建新的
        autoNewId: true,
        //错误提示
        error: {
            managerIsExist: '管理器id已经存在'
        },
        pluginPrev: 'liger',
        attrPrev: 'data',
        getId: function(prev) {
            prev = prev || this.managerIdPrev;
            var id = prev + (1000 + this.managerCount);
            this.managerCount++;
            return id;
        },
        add: function(manager) {
            if (arguments.length == 2) {
                var m = arguments[1];
                m.id = m.id || m.options.id || arguments[0].id;
                this.addManager(m);
                return;
            }
            if (!manager.id) manager.id = this.getId(manager.__idPrev());
            //if (this.managers[manager.id]) manager.id = this.getId(manager.__idPrev());
            //if (this.managers[manager.id])
            //{
            //    throw new Error(this.error.managerIsExist);
            //}
            this.managers[manager.id] = manager;
        },
        remove: function(arg) {
            if (typeof arg == "string" || typeof arg == "number") {
                delete liger.managers[arg];
            }
            else if (typeof arg == "object") {
                if (arg instanceof liger.core.Component) {
                    delete liger.managers[arg.id];
                }
                else {
                    if (!$(arg).attr(this.idAttrName)) return false;
                    delete liger.managers[$(arg).attr(this.idAttrName)];
                }
            }

        },
        //获取ligerui对象
        //1,传入ligerui ID
        //2,传入Dom Object
        get: function(arg, idAttrName) {
            idAttrName = idAttrName || "ligeruiid";
            if (typeof arg == "string" || typeof arg == "number") {
                return liger.managers[arg];
            }
            else if (typeof arg == "object") {
                var domObj = arg.length ? arg[0] : arg;
                var id = domObj[idAttrName] || $(domObj).attr(idAttrName);
                if (!id) return null;
                return liger.managers[id];
            }
            return null;
        },
        //根据类型查找某一个对象
        find: function(type) {
            var arr = [];
            for (var id in this.managers) {
                var manager = this.managers[id];
                if (type instanceof Function) {
                    if (manager instanceof type) {
                        arr.push(manager);
                    }
                }
                else if (type instanceof Array) {
                    if ($.inArray(manager.__getType(), type) != -1) {
                        arr.push(manager);
                    }
                }
                else {
                    if (manager.__getType() == type) {
                        arr.push(manager);
                    }
                }
            }
            return arr;
        },
        //$.fn.liger{Plugin} 和 $.fn.ligerGet{Plugin}Manager
        //会调用这个方法,并传入作用域(this)
        //parm [plugin]  插件名
        //parm [args] 参数(数组)
        //parm [ext] 扩展参数,定义命名空间或者id属性名
        run: function(plugin, args, ext) {
            if (!plugin) return;
            ext = $.extend({
                defaultsNamespace: 'ligerDefaults',
                methodsNamespace: 'ligerMethods',
                controlNamespace: 'controls',
                idAttrName: 'ligeruiid',
                isStatic: false,
                hasElement: true,           //是否拥有element主体(比如drag、resizable等辅助性插件就不拥有)
                propertyToElemnt: null      //链接到element的属性名
            }, ext || {});
            plugin = plugin.replace(/^ligerGet/, '');
            plugin = plugin.replace(/^liger/, '');
            if (this == null || this == window || ext.isStatic) {
                if (!liger.plugins[plugin]) {
                    liger.plugins[plugin] = {
                        fn: $[liger.pluginPrev + plugin],
                        isStatic: true
                    };
                }
                return new $.ligerui[ext.controlNamespace][plugin]($.extend({}, $[ext.defaultsNamespace][plugin] || {}, $[ext.defaultsNamespace][plugin + 'String'] || {}, args.length > 0 ? args[0] : {}));
            }
            if (!liger.plugins[plugin]) {
                liger.plugins[plugin] = {
                    fn: $.fn[liger.pluginPrev + plugin],
                    isStatic: false
                };
            }
            if (/Manager$/.test(plugin)) return liger.get(this, ext.idAttrName);
            this.each(function() {
                if (this[ext.idAttrName] || $(this).attr(ext.idAttrName)) {
                    var manager = liger.get(this[ext.idAttrName] || $(this).attr(ext.idAttrName));
                    if (manager && args.length > 0) manager.set(args[0]);
                    //已经执行过
                    return;
                }
                if (args.length >= 1 && typeof args[0] == 'string') return;
                //只要第一个参数不是string类型,都执行组件的实例化工作
                var options = args.length > 0 ? args[0] : null;
                var p = $.extend({}, $[ext.defaultsNamespace][plugin], $[ext.defaultsNamespace][plugin + 'String'], options);
                if (ext.propertyToElemnt) p[ext.propertyToElemnt] = this;
                if (ext.hasElement) {
                    new $.ligerui[ext.controlNamespace][plugin](this, p);
                }
                else {
                    new $.ligerui[ext.controlNamespace][plugin](p);
                }
            });
            if (this.length == 0) return null;
            if (args.length == 0) return liger.get(this, ext.idAttrName);
            if (typeof args[0] == 'object') return liger.get(this, ext.idAttrName);
            if (typeof args[0] == 'string') {
                var manager = liger.get(this, ext.idAttrName);
                if (manager == null) return;
                if (args[0] == "option") {
                    if (args.length == 2)
                        return manager.get(args[1]);  //manager get
                    else if (args.length >= 3)
                        return manager.set(args[1], args[2]);  //manager set
                }
                else {
                    var method = args[0];
                    if (!manager[method]) return; //不存在这个方法
                    var parms = Array.apply(null, args);
                    parms.shift();
                    return manager[method].apply(manager, parms);  //manager method
                }
            }
            return null;
        },

        //扩展
        //1,默认参数
        //2,本地化扩展
        defaults: {},
        //3,方法接口扩展
        methods: {},
        //命名空间
        //核心控件,封装了一些常用方法
        core: {},
        //命名空间
        //组件的集合
        controls: {},
        //plugin 插件的集合
        plugins: {}
    };
    //扩展对象
    $.ligerDefaults = {};
    //扩展对象
    $.ligerMethos = {};
    //关联起来
    liger.defaults = $.ligerDefaults;
    liger.methods = $.ligerMethos;
    //获取ligerui对象
    //parm [plugin]  插件名,可为空
    $.fn.liger = function(plugin) {
        if (plugin) {
            return liger.run.call(this, plugin, arguments);
        }
        else {
            return liger.get(this);
        }
    };
    //组件基类
    //1,完成定义参数处理方法和参数属性初始化的工作
    //2,完成定义事件处理方法和事件属性初始化的工作
    liger.core.Component = function(options) {
        //事件容器
        this.events = this.events || {};
        //配置参数
        this.options = options || {};
        //子组件集合索引
        this.children = {};
    };
    $.extend(liger.core.Component.prototype, {
        __getType: function() {
            return 'liger.core.Component';
        },
        __idPrev: function() {
            return 'ligerui';
        },
        //设置属性
        // arg 属性名    value 属性值
        // arg 属性/值   value 是否只设置事件
        set: function(arg, value, value2) {
            if (!arg) return;
            if (typeof arg == 'object') {
                var tmp;
                if (this.options != arg) {
                    $.extend(this.options, arg);
                    tmp = arg;
                }
                else {
                    tmp = $.extend({}, arg);
                }
                if (value == undefined || value == true) {
                    for (var p in tmp) {
                        if (p.indexOf('on') == 0)
                            this.set(p, tmp[p]);
                    }
                }
                if (value == undefined || value == false) {
                    for (var p in tmp) {
                        if (p.indexOf('on') != 0)
                            this.set(p, tmp[p], value2);
                    }
                }
                return;
            }
            var name = arg;
            //事件参数
            if (name.indexOf('on') == 0) {
                if (typeof value == 'function')
                    this.bind(name.substr(2), value);
                return;
            }
            if (!this.options) this.options = {};
            if (this.trigger('propertychange', [arg, value]) == false) return;
            this.options[name] = value;
            var pn = '_set' + name.substr(0, 1).toUpperCase() + name.substr(1);
            if (this[pn]) {
                this[pn].call(this, value, value2);
            }
            this.trigger('propertychanged', [arg, value]);
        },
        //获取属性
        get: function(name) {
            var pn = '_get' + name.substr(0, 1).toUpperCase() + name.substr(1);
            if (this[pn]) {
                return this[pn].call(this, name);
            }
            return this.options[name];
        },
        hasBind: function(arg) {
            var name = arg.toLowerCase();
            var event = this.events[name];
            if (event && event.length) return true;
            return false;
        },
        //触发事件
        //data (可选) Array(可选)传递给事件处理函数的附加参数
        trigger: function(arg, data) {
            if (!arg) return;
            var name = arg.toLowerCase();
            var event = this.events[name];
            if (!event) return;
            data = data || [];
            if ((data instanceof Array) == false) {
                data = [data];
            }
            for (var i = 0; i < event.length; i++) {
                var ev = event[i];
                if (ev.handler.apply(ev.context, data) == false)
                    return false;
            }
        },
        //绑定事件
        bind: function(arg, handler, context) {
            if (typeof arg == 'object') {
                for (var p in arg) {
                    this.bind(p, arg[p]);
                }
                return;
            }
            if (typeof handler != 'function') return false;
            var name = arg.toLowerCase();
            var event = this.events[name] || [];
            context = context || this;
            event.push({handler: handler, context: context});
            this.events[name] = event;
        },
        //取消绑定
        unbind: function(arg, handler) {
            if (!arg) {
                this.events = {};
                return;
            }
            var name = arg.toLowerCase();
            var event = this.events[name];
            if (!event || !event.length) return;
            if (!handler) {
                delete this.events[name];
            }
            else {
                for (var i = 0, l = event.length; i < l; i++) {
                    if (event[i].handler == handler) {
                        event.splice(i, 1);
                        break;
                    }
                }
            }
        },
        destroy: function() {
            liger.remove(this);
        }
    });
    //界面组件基类,
    //1,完成界面初始化:设置组件id并存入组件管理器池,初始化参数
    //2,渲染的工作,细节交给子类实现
    //parm [element] 组件对应的dom element对象
    //parm [options] 组件的参数
    liger.core.UIComponent = function(element, options) {
        liger.core.UIComponent.base.constructor.call(this, options);
        var extendMethods = this._extendMethods();
        if (extendMethods) $.extend(this, extendMethods);
        this.element = element;
        this._init();
        this._preRender();
        this.trigger('render');
        this._render();
        this.trigger('rendered');
        this._rendered();
    };
    liger.core.UIComponent.ligerExtend(liger.core.Component, {
        __getType: function() {
            return 'liger.core.UIComponent';
        },
        //扩展方法
        _extendMethods: function() {

        },
        _init: function() {
            this.type = this.__getType();
            if (!this.element) {
                this.id = this.options.id || liger.getId(this.__idPrev());
            }
            else {
                this.id = this.options.id || this.element.id || liger.getId(this.__idPrev());
            }
            //存入管理器池
            liger.add(this);
            if (!this.element) return;
            //读取attr方法,并加载到参数,比如['url']
            var attributes = this.attr();
            if (attributes && attributes instanceof Array) {
                for (var i = 0; i < attributes.length; i++) {
                    var name = attributes[i];
                    if ($(this.element).attr(name)) {
                        this.options[name] = $(this.element).attr(name);
                    }
                }
            }
            //读取ligerui这个属性，并加载到参数，比如 ligerui = "width:120,heigth:100"
            var p = this.options;
            if ($(this.element).attr("ligerui")) {
                try {
                    var attroptions = $(this.element).attr("ligerui");
                    if (attroptions.indexOf('{') != 0) attroptions = "{" + attroptions + "}";
                    eval("attroptions = " + attroptions + ";");
                    if (attroptions) $.extend(p, attroptions);
                }
                catch (e) { }
            }
            //v1.3.2增加 从data-XX 加载属性
            function loadDataOp(control, jelement) {
                var op = {};
                if (!control || control.indexOf('.') != -1) return op;
                var defaultOp = liger.defaults[control];
                if (!defaultOp) return op;
                for (var name in defaultOp) {
                    if (jelement.attr(liger.attrPrev + "-" + name)) {
                        var value = jelement.attr(liger.attrPrev + "-" + name);
                        if (typeof (defaultOp[name]) == "boolean") {
                            op[name] = value == "true" || value == "1";
                        } else {
                            op[name] = value;
                        }
                    }
                }
                return op;
            }

            $.extend(p, loadDataOp(this.__getType(), $(this.element)));

        },
        //预渲染,可以用于继承扩展
        _preRender: function() {

        },
        _render: function() {

        },
        _rendered: function() {
            if (this.element) {
                $(this.element).attr("ligeruiid", this.id);
            }
        },
        _setCls: function(value) {
            if (this.element && value) {
                $(this.element).addClass(value);
            }
        },
        //返回要转换成ligerui参数的属性,比如['url']
        attr: function() {
            return [];
        },
        destroy: function() {
            if (this.element) {
                $(this.element).remove();
            }
            this.options = null;
            liger.remove(this);
        }
    });
    //表单控件基类
    liger.controls.Input = function(element, options) {
        liger.controls.Input.base.constructor.call(this, element, options);
    };
    liger.controls.Input.ligerExtend(liger.core.UIComponent, {
        __getType: function() {
            return 'liger.controls.Input';
        },
        attr: function() {
            return ['nullText'];
        },
        setValue: function(value) {
            return this.set('value', value);
        },
        getValue: function() {
            return this.get('value');
        },
        //设置只读
        _setReadonly: function(readonly) {
            var wrapper = this.wrapper || this.text;
            if (!wrapper || !wrapper.hasClass("l-text")) return;
            var inputText = this.inputText;
            if (readonly) {
                if (inputText) inputText.attr("readonly", "readonly");
                wrapper.addClass("l-text-readonly");
            } else {
                if (inputText) inputText.removeAttr("readonly");
                wrapper.removeClass("l-text-readonly");
            }
        },
        setReadonly: function(readonly) {
            return this.set('readonly', readonly);
        },
        setEnabled: function() {
            return this.set('disabled', false);
        },
        setDisabled: function() {
            return this.set('disabled', true);
        },
        updateStyle: function() {

        },
        resize: function(width, height) {
            this.set({width: width, height: height + 2});
        }
    });
    //全局窗口对象
    liger.win = {
        //顶端显示
        top: false,
        //遮罩
        mask: function(win) {
            function setHeight() {
                if (!liger.win.windowMask) return;
                var h = $(window).height() + $(window).scrollTop();
                liger.win.windowMask.height(h);
            }
            if (!this.windowMask) {
                this.windowMask = $("<div class='l-window-mask' style='display: block;'></div>").appendTo('body');
                $(window).bind('resize.ligeruiwin', setHeight);
                $(window).bind('scroll', setHeight);
            }
            this.windowMask.show();
            setHeight();
            this.masking = true;
        },
        //取消遮罩
        unmask: function(win) {
            var jwins = $("body > .l-dialog:visible,body > .l-window:visible");
            for (var i = 0, l = jwins.length; i < l; i++) {
                var winid = jwins.eq(i).attr("ligeruiid");
                if (win && win.id == winid) continue;
                //获取ligerui对象
                var winmanager = liger.get(winid);
                if (!winmanager) continue;
                //是否模态窗口
                var modal = winmanager.get('modal');
                //如果存在其他模态窗口，那么不会取消遮罩
                if (modal) return;
            }
            if (this.windowMask)
                this.windowMask.hide();
            this.masking = false;
        },
        //显示任务栏
        createTaskbar: function() {
            if (!this.taskbar) {
                this.taskbar = $('<div class="l-taskbar"><div class="l-taskbar-tasks"></div><div class="l-clear"></div></div>').appendTo('body');
                if (this.top) this.taskbar.addClass("l-taskbar-top");
                this.taskbar.tasks = $(".l-taskbar-tasks:first", this.taskbar);
                this.tasks = {};
            }
            this.taskbar.show();
            this.taskbar.animate({bottom: 0});
            return this.taskbar;
        },
        //关闭任务栏
        removeTaskbar: function() {
            var self = this;
            self.taskbar.animate({bottom: -32}, function() {
                self.taskbar.remove();
                self.taskbar = null;
            });
        },
        activeTask: function(win) {
            for (var winid in this.tasks) {
                var t = this.tasks[winid];
                if (winid == win.id) {
                    t.addClass("l-taskbar-task-active");
                }
                else {
                    t.removeClass("l-taskbar-task-active");
                }
            }
        },
        //获取任务
        getTask: function(win) {
            var self = this;
            if (!self.taskbar) return;
            if (self.tasks[win.id]) return self.tasks[win.id];
            return null;
        },
        //增加任务
        addTask: function(win) {
            var self = this;
            if (!self.taskbar) self.createTaskbar();
            if (self.tasks[win.id]) return self.tasks[win.id];
            var title = win.get('title');
            var task = self.tasks[win.id] = $('<div class="l-taskbar-task"><div class="l-taskbar-task-icon"></div><div class="l-taskbar-task-content">' + title + '</div></div>');
            self.taskbar.tasks.append(task);
            self.activeTask(win);
            task.bind('click', function() {
                self.activeTask(win);
                if (win.actived)
                    win.min();
                else
                    win.active();
            }).hover(function() {
                $(this).addClass("l-taskbar-task-over");
            }, function() {
                $(this).removeClass("l-taskbar-task-over");
            });
            return task;
        },
        hasTask: function() {
            for (var p in this.tasks) {
                if (this.tasks[p])
                    return true;
            }
            return false;
        },
        //移除任务
        removeTask: function(win) {
            var self = this;
            if (!self.taskbar) return;
            if (self.tasks[win.id]) {
                self.tasks[win.id].unbind();
                self.tasks[win.id].remove();
                delete self.tasks[win.id];
            }
            if (!self.hasTask()) {
                self.removeTaskbar();
            }
        },
        //前端显示
        setFront: function(win) {
            var wins = liger.find(liger.core.Win);
            for (var i in wins) {
                var w = wins[i];
                if (w == win) {
                    $(w.element).css("z-index", "9200");
                    this.activeTask(w);
                }
                else {
                    $(w.element).css("z-index", "9100");
                }
            }
        }
    };
    //窗口基类 window、dialog
    liger.core.Win = function(element, options) {
        liger.core.Win.base.constructor.call(this, element, options);
    };
    liger.core.Win.ligerExtend(liger.core.UIComponent, {
        __getType: function() {
            return 'liger.controls.Win';
        },
        mask: function() {
            if (this.options.modal)
                liger.win.mask(this);
        },
        unmask: function() {
            if (this.options.modal)
                liger.win.unmask(this);
        },
        min: function() {
        },
        max: function() {
        },
        active: function() {
        }
    });
    liger.draggable = {
        dragging: false
    };
    liger.resizable = {
        reszing: false
    };
    liger.toJSON = typeof JSON === 'object' && JSON.stringify ? JSON.stringify : function(o) {
        var f = function(n) {
            return n < 10 ? '0' + n : n;
        },
            escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
            quote = function(value) {
                escapable.lastIndex = 0;
                return escapable.test(value) ?
                    '"' + value.replace(escapable, function(a) {
                        var c = meta[a];
                        return typeof c === 'string' ? c :
                            '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                    }) + '"' :
                    '"' + value + '"';
            };
        if (o === null) return 'null';
        var type = typeof o;
        if (type === 'undefined') return undefined;
        if (type === 'string') return quote(o);
        if (type === 'number' || type === 'boolean') return '' + o;
        if (type === 'object') {
            if (typeof o.toJSON === 'function') {
                return liger.toJSON(o.toJSON());
            }
            if (o.constructor === Date) {
                return isFinite(this.valueOf()) ?
                    this.getUTCFullYear() + '-' +
                    f(this.getUTCMonth() + 1) + '-' +
                    f(this.getUTCDate()) + 'T' +
                    f(this.getUTCHours()) + ':' +
                    f(this.getUTCMinutes()) + ':' +
                    f(this.getUTCSeconds()) + 'Z' : null;
            }
            var pairs = [];
            if (o.constructor === Array) {
                for (var i = 0, l = o.length; i < l; i++) {
                    pairs.push(liger.toJSON(o[i]) || 'null');
                }
                return '[' + pairs.join(',') + ']';
            }
            var name, val;
            for (var k in o) {
                type = typeof k;
                if (type === 'number') {
                    name = '"' + k + '"';
                } else if (type === 'string') {
                    name = quote(k);
                } else {
                    continue;
                }
                type = typeof o[k];
                if (type === 'function' || type === 'undefined') {
                    continue;
                }
                val = liger.toJSON(o[k]);
                pairs.push(name + ':' + val);
            }
            return '{' + pairs.join(',') + '}';
        }
    };
    //获取 默认的编辑构造器
    liger.getEditor = function(e) {
        var type = e.type, control = e.control, master = e.master;
        if (!type) return null;
        var inputTag = 0;
        if (control) control = control.substr(0, 1).toUpperCase() + control.substr(1);
        var defaultOp = {
            create: function(container, editParm, controlOptions) {
                //field in form , column in grid
                var field = editParm.field || editParm.column, options = controlOptions || {};
                var isInGrid = editParm.column ? true : false;
                var p = $.extend({}, e.options);
                var inputType = "text";
                if ($.inArray(type, ["password", "file", "checkbox", "radio"]) != -1) inputType = type;
                if (e.password) inputType = "password";
                var inputBody = $("<input type='" + inputType + "'/>");
                if (e.body) {
                    inputBody = e.body.clone();
                }
                inputBody.appendTo(container);
                if (editParm.field) {
                    var txtInputName = field.name;
                    var prefixID = $.isFunction(options.prefixID) ? options.prefixID(master) : (options.prefixID || "");
                    p.id = field.id || (prefixID + field.name);
                    if ($.inArray(type, ["select", "combobox", "autocomplete", "popup"]) != -1) {
                        txtInputName = field.textField || field.comboboxName;
                        if (field.comboboxName && !field.id)
                            p.id = (options.prefixID || "") + field.comboboxName;
                    }
                    if ($.inArray(type, ["select", "combobox", "autocomplete", "popup", "radiolist", "checkboxlist", "listbox"]) != -1) {
                        p.valueFieldID = prefixID + field.name;
                    }
                    if (!e.body) {
                        var inputName = prefixID + txtInputName;
                        var inputId = new Date().getTime() + "_" + ++inputTag + "_" + field.name;
                        inputBody.attr($.extend({
                            id: inputId,
                            name: inputName
                        }, field.attr));
                        if (field.cssClass) {
                            inputBody.addClass(field.cssClass);
                        }
                        if (field.validate && !master.options.unSetValidateAttr) {
                            inputBody.attr('validate', liger.toJSON(field.validate));
                        }
                    }
                    $.extend(p, field.options);
                }
                if (field.dictionary) //字典字段，比如:男|女
                {
                    field.editor = field.editor || {};
                    if (!field.editor.data) {
                        var dicEditorData = [], dicItems = field.dictionary.split('|');
                        $(dicItems).each(function(i, dicItem) {
                            var dics = dicItem.split(',');
                            var dicItemId = dics[0], dicItemText = dics.length >= 2 ? dics[1] : dics[0];
                            dicEditorData.push({
                                id: dicItemId,
                                value: dicItemId,
                                text: dicItemText
                            });
                        });
                        field.editor.data = dicEditorData;
                    }
                }
                if (field.editor) {
                    if (field.editor.options) {
                        $.extend(p, field.editor.options);
                        delete field.editor.options;
                    }
                    if (field.editor.valueColumnName) {
                        p.valueField = field.editor.valueColumnName;
                        delete field.editor.valueColumnName;
                    }
                    if (field.editor.displayColumnName) {
                        p.textField = field.editor.displayColumnName;
                        delete field.editor.displayColumnName;
                    }
                    //可扩展参数,支持动态加载
                    var ext = field.editor.p || field.editor.ext;
                    if (ext) {
                        ext = typeof (ext) == 'function' ? ext(editParm) : ext;
                        $.extend(p, ext);
                        delete field.editor.p;
                        delete field.editor.ext;
                    }
                    $.extend(p, field.editor);
                }

                if (isInGrid) {
                    p.host_grid = this;
                    p.host_grid_row = editParm.record;
                    p.host_grid_column = editParm.column;
                } else {
                    p.host_form = this;

                    if (field.readonly || p.host_form.get('readonly')) {
                        p.readonly = true;
                    }
                }
                //返回的是ligerui对象
                var lobj = inputBody['liger' + control](p);
                if (isInGrid) {
                    setTimeout(function() {inputBody.focus();}, 100);
                }
                return lobj;
            },
            getValue: function(editor, editParm) {
                var field = editParm.field || editParm.column;
                if (editor.getValue) {
                    var value = editor.getValue();
                    var edtirType = editParm.column ? editParm.column.editor.type : editParm.field.type;
                    //isArrayValue属性可将提交字段数据改成[id1,id2,id3]的形式
                    if (field && field.editor && field.editor.isArrayValue && value) {
                        value = value.split(';');
                    }
                    //isRef属性可将提交字段数据改成[id,value]的形式
                    if (field && field.editor && field.editor.isRef && editor.getText) {
                        value = [value, editor.getText()];
                    }
                    //isRefMul属性可将提交字段数据改成[[id1,value1],[id2,value2]]的形式
                    if (field && field.editor && field.editor.isRefMul && editor.getText) {
                        var vs = value.split(';');
                        var ts = editor.getText().split(';');
                        value = [];
                        for (var i = 0; i < vs.length; i++) {
                            value.push([vs[i], ts[i]]);
                        }
                    }
                    if (edtirType == "int" || edtirType == "digits") {
                        value = value ? parseInt(value, 10) : 0;
                    }
                    else if (edtirType == "float" || edtirType == "number") {
                        value = value ? parseFloat(value) : 0;
                    }
                    return value;
                }
            },
            setValue: function(editor, value, editParm) {
                var field = editParm.field || editParm.column;
                if (editor.setValue) {
                    //设置了isArrayValue属性- 如果获取到的数据是[id1,id2,id3]的形式，需要合并为一个完整字符串
                    if (field && field.editor && field.editor.isArrayValue && value) {
                        value = value.join(';');
                    }
                    //设置了isRef属性-如果获取到的数据是[id,text]的形式，需要获取[0]
                    if (field && field.editor && field.editor.isRef && $.isArray(value)) {
                        value = value[0];
                    }
                    //设置了isRefMul属性- 获取到[[id1,value1],[id2,value2]]的形式，需要合并为一个完整字符串
                    if (field && field.editor && field.editor.isRefMul && $.isArray(value)) {
                        var vs = [];
                        for (var i = 0; i < value.length; i++) {
                            vs.push(value[i].length > 1 ? value[i][1] : value[i][0]);
                        }
                        value = vs.join(';');
                    }
                    editor.setValue(value);
                }
            },
            //从控件获取到文本信息
            getText: function(editor, editParm) {
                var field = editParm.field || editParm.column;
                if (editor.getText) {
                    var text = editor.getText();
                    if (text) return text;
                }
            },
            //设置文本信息到控件去
            setText: function(editor, text, editParm) {
                if (text && editor.setText) {
                    editor.setText(text);
                }
                //如果没有把数据保存到 textField 字段，那么需要获取值字段
                else {
                    var field = editParm.field || editParm.column;
                    text = editor.setValue() || editParm.value || "";
                    //如果获取到的数据是[id,text]的形式，需要获取[0]
                    if (field && field.editor && field.editor.isRef && $.isArray(text) && text.length > 1) {
                        text = text[1];
                    }
                    //在grid的编辑里面 获取到[[id1,value1],[id2,value2]]的形式，需要合并为一个完整字符串
                    if (field && field.editor && field.editor.isRefMul && $.isArray(text) && text.length > 1) {
                        var vs = [];
                        for (var i = 0; i < text.length; i++) {
                            vs.push(text[1]);
                        }
                        text = vs.join(';');
                    }
                    if (editor.setText) {
                        editor.setText(text);
                    }
                }
            },
            getSelected: function(editor, editParm) {
                if (editor.getSelected) {
                    return editor.getSelected();
                }
            },
            resize: function(editor, width, height, editParm) {
                if (editParm.field) width = width - 2;
                if (editor.resize) editor.resize(width, height);
            },
            setEnabled: function(editor, isEnabled) {
                if (isEnabled) {
                    if (editor.setEnabled) editor.setEnabled();
                }
                else {
                    if (editor.setDisabled) editor.setDisabled();
                }
            },
            destroy: function(editor, editParm) {
                if (editor.destroy) editor.destroy();
            }
        };

        return $.extend({}, defaultOp, liger.editorCreatorDefaults || {}, e);
    };
    //几个默认的编辑器构造函数
    liger.editors = {
        "text": {
            control: 'TextBox'
        },
        "date": {
            control: 'DateEditor',
            setValue: function(editor, value, editParm) {
                // /Date(1328423451489)/
                if (typeof value == "string" && /^\/Date/.test(value)) {
                    value = value.replace(/^\//, "new ").replace(/\/$/, "");
                    eval("value = " + value);
                }
                editor.setValue(value);
            }
        },
        "combobox": {
            control: 'ComboBox'
        },
        "spinner": {
            control: 'Spinner'
        },
        "checkbox": {
            control: 'CheckBox'
        },
        "checkboxlist": {
            control: 'CheckBoxList',
            body: $('<div></div>'),
            resize: function(editor, width, height, editParm) {
                editor.set('width', width - 2);
            }
        },
        "radiolist": {
            control: 'RadioList',
            body: $('<div></div>'),
            resize: function(editor, width, height, editParm) {
                editor.set('width', width - 2);
            }
        },
        "listbox": {
            control: 'ListBox',
            body: $('<div></div>'),
            resize: function(editor, width, height, editParm) {
                editor.set('width', width - 2);
            }
        },
        "popup": {
            control: 'PopupEdit'
        },
        "number": {
            control: 'TextBox',
            options: {number: true}
        },
        "currency": {
            control: 'TextBox',
            options: {currency: true}
        },
        "digits": {
            control: 'TextBox',
            options: {digits: true}
        },
        "password": {
            control: 'TextBox',
            password: true
        }
    };
    liger.editors["string"] = liger.editors["text"];
    liger.editors["select"] = liger.editors["combobox"];
    liger.editors["int"] = liger.editors["digits"];
    liger.editors["float"] = liger.editors["number"];
    liger.editors["chk"] = liger.editors["checkbox"];
    liger.editors["popupedit"] = liger.editors["popup"];
    //jQuery version fix
    $.fn.live = $.fn.on ? $.fn.on : $.fn.live;
    if (!$.browser) {
        var userAgent = navigator.userAgent.toLowerCase();
        $.browser = {
            version: (userAgent.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [0, '0'])[1],
            safari: /webkit/.test(userAgent),
            opera: /opera/.test(userAgent),
            msie: /msie/.test(userAgent) && !/opera/.test(userAgent),
            mozilla: /mozilla/.test(userAgent) && !/(compatible|webkit)/.test(userAgent)
        };
    }
})(jQuery);

/* TAB类 */
(function($) {
    $.fn.ligerTab = function(options) {
        return $.ligerui.run.call(this, "ligerTab", arguments);
    };
    $.fn.ligerGetTabManager = function() {
        return $.ligerui.run.call(this, "ligerGetTabManager", arguments);
    };
    $.ligerDefaults.Tab = {
        height: null, //高度
        heightDiff: 0, //高度补差
        changeHeightOnResize: true, //随窗口大小改变高度
        contextmenu: true, //右键菜单
        dblClickToClose: true, //是否双击时关闭
        dragToMove: true, //是否允许拖动时改变tab项的位置
        showSwitch: true, //显示切页窗口按钮
        showSwitchInTab: false, //切页窗口按钮显示在tab最后一项
        data: null, //传递数据容器
        onBeforeOverrideTabItem: null,
        onAfterOverrideTabItem: null,
        onBeforeRemoveTabItem: null,
        onAfterRemoveTabItem: null,
        onBeforeAddTabItem: null,
        onAfterAddTabItem: null,
        onBeforeSelectTabItem: null,
        onAfterSelectTabItem: null,
        onCloseOther: null,
        onCloseAll: null,
        onClose: null,
        onReload: null,
        onSwitchRender: null
    };
    $.ligerDefaults.TabString = {
        reloadMessage: "刷新当前页",
        closeMessage: "关闭当前页",
        closeOtherMessage: "关闭其他页",
        closeAllMessage: "关闭所有页"
    };
    $.ligerMethos.Tab = {};
    $.ligerui.controls.Tab = function(element, options) {
        $.ligerui.controls.Tab.base.constructor.call(this, element, options);
    };
    $.ligerui.controls.Tab.ligerExtend($.ligerui.core.UIComponent, {
        __getType: function() {
            return 'Tab';
        },
        __idPrev: function() {
            return 'Tab';
        },
        _extendMethods: function() {
            return $.ligerMethos.Tab;
        },
        _render: function() {
            var g = this, p = this.options;
            if (p.height) g.makeFullHeight = true;
            g.tab = $(this.element);
            g.tab.addClass("l-tab");
            if (p.contextmenu && $.ligerMenu) {
                g.tab.menu = $.ligerMenu({
                    items: [
                        {
                            text: p.reloadMessage, id: 'reload', click: function() {
                                g._menuItemClick.apply(g, arguments);
                            }
                        },
                        {
                            text: p.closeMessage, id: 'close', click: function() {
                                g._menuItemClick.apply(g, arguments);
                            }
                        },
                        {
                            text: p.closeOtherMessage, id: 'closeother', click: function() {
                                g._menuItemClick.apply(g, arguments);
                            }
                        },
                        {
                            text: p.closeAllMessage, id: 'closeall', click: function() {
                                g._menuItemClick.apply(g, arguments);
                            }
                        }
                    ]
                });
            }
            g.tab.content = $('<div class="l-tab-content"></div>');
            $("> div", g.tab).appendTo(g.tab.content);
            g.tab.content.appendTo(g.tab);
            g.tab.links = $('<div class="l-tab-links"><ul style="left:0px;"></ul></div>');
            g.tab.links.prependTo(g.tab);
            g.tab.links.ul = $("ul", g.tab.links);
            var lselecteds = $("> div[lselected=true]", g.tab.content);
            var haslselected = lselecteds.length > 0;
            g.selectedTabId = lselecteds.attr("tabid");
            $("> div", g.tab.content).each(function(i, box) {
                var li = $('<li class=""><a></a><div class="l-tab-links-item-left"></div><div class="l-tab-links-item-right"></div></li>');
                var contentitem = $(this);
                if (contentitem.attr("title")) {
                    $("> a", li).html(contentitem.attr("title"));
                    contentitem.attr("title", "");
                }
                var tabid = contentitem.attr("tabid");
                if (tabid == undefined) {
                    tabid = g.getNewTabid();
                    contentitem.attr("tabid", tabid);
                    if (contentitem.attr("lselected")) {
                        g.selectedTabId = tabid;
                    }
                }
                li.attr("tabid", tabid);
                if (!haslselected && i == 0) g.selectedTabId = tabid;
                var showClose = contentitem.attr("showClose");
                if (showClose) {
                    li.append("<div class='l-tab-links-item-close'></div>");
                }
                $("> ul", g.tab.links).append(li);
                if (!contentitem.hasClass("l-tab-content-item")) contentitem.addClass("l-tab-content-item");
                if (contentitem.find("iframe").length > 0) {
                    var iframe = $("iframe:first", contentitem);
                    if (iframe[0].readyState != "complete") {
                        if (contentitem.find(".l-tab-loading:first").length == 0)
                            contentitem.prepend("<div class='l-tab-loading' style='display:block;'></div>");
                        var iframeloading = $(".l-tab-loading:first", contentitem);
                        iframe.bind('load.tab', function() {
                            iframeloading.hide();
                        });
                    }
                }
            });
            //init
            g.selectTabItem(g.selectedTabId);
            //set content height
            if (p.height) {
                if (typeof (p.height) == 'string' && p.height.indexOf('%') > 0) {
                    g.onResize();
                    if (p.changeHeightOnResize) {
                        $(window).resize(function() {
                            g.onResize.call(g);
                        });
                    }
                } else {
                    g.setHeight(p.height);
                }
            }
            if (g.makeFullHeight)
                g.setContentHeight();
            //add even
            $("li", g.tab.links).each(function() {
                g._addTabItemEvent($(this));
            });
            g.tab.bind('dblclick.tab', function(e) {
                if (!p.dblClickToClose) return;
                g.dblclicking = true;
                var obj = (e.target || e.srcElement);
                var tagName = obj.tagName.toLowerCase();
                if (tagName == "a") {
                    var tabid = $(obj).parent().attr("tabid");
                    var allowClose = $(obj).parent().find("div.l-tab-links-item-close").length ? true : false;
                    if (allowClose) {
                        g.removeTabItem(tabid);
                    }
                }
                g.dblclicking = false;
            });
            g.set(p);
            //set tab links width
            setTimeout(setLinksWidth, 100);
            $(window).resize(function() {
                setLinksWidth.call(g);
            });
            function setLinksWidth() {
                var w = g.tab.width() - parseInt(g.tab.links.css("marginLeft"), 10) - parseInt(g.tab.links.css("marginRight"), 10);
                g.tab.links.width(w);
            }
            g.bind('sysWidthChange', function() {
                setLinksWidth.call(g);
            });
        },
        _setShowSwitch: function(value) {
            var g = this, p = this.options;
            if (value) {
                if (!$(".l-tab-switch", g.tab.links).length) {
                    $('<div class="l-tab-switch"><i class="iconfont icon-mulu f20" title="所有打开的页面"></i></div>').appendTo(g.tab.links);
                }
                $(g.tab).addClass("l-tab-switchable");
                $(".l-tab-switch", g.tab).click(function() {
                    g.toggleSwitch(this);
                });
            }
            else {
                $(g.tab).removeClass("l-tab-switchable");
                $(".l-tab-switch > .l-tab-windowsswitch").remove();
            }
        },
        _setShowSwitchInTab: function(value) {
            var g = this, p = this.options;
            if (p.showSwitch && value) {
                $(g.tab).removeClass("l-tab-switchable");
                $(".l-tab-switch", g.tab).remove();
                var tabitem = $('<li class="l-tab-itemswitch"><i class="iconfont icon-mulu f20" title="所有打开的页面"></i><div class="l-tab-links-item-left"></div><div class="l-tab-links-item-right"></div></li>');
                tabitem.appendTo(g.tab.links.ul);
                tabitem.click(function() {
                    g.toggleSwitch(this);
                });
            } else {
                $(".l-tab-itemswitch", g.tab.ul).remove();
            }
        },
        toggleSwitch: function(btn) {
            var g = this, p = this.options;
            if ($(".l-tab-switch > .l-tab-windowsswitch").length) {
                $(".l-tab-switch > .l-tab-windowsswitch").remove();
                return;
            }
            if (btn == null) return;
            var windowsswitch = $("<div class='l-tab-windowsswitch'></div>").appendTo('.l-tab-switch');
            var windows = $("<div class='l-tab-windows'></div>").appendTo('.l-tab-windowsswitch');
            var tabItems = g.tab.links.ul.find('>li');
            var selectedTabItemID = g.getSelectedTabItemID();
            tabItems.each(function(i, item) {
                var jlink = $("<a href='javascript:void(0)'></a>");
                jlink.html($(item).find("a").html());
                var tabid = $(item).attr("tabid");
                if (tabid == null) return;
                if (tabid == selectedTabItemID) {
                    jlink.addClass("selected");
                }
                jlink.attr("tabid", tabid);
                windows.append(jlink);
            });
            windows.find("a").bind("click", function(e) {
                var tabid = $(this).attr("tabid");
                if (tabid == undefined) return;
                g.selectTabItem(tabid);
                g.moveToTabItem(tabid);
                $(".l-tab-switch > .l-tab-windowsswitch").remove();
            });
            g.trigger('switchRender', [windowsswitch]);
        },
        _applyDrag: function(tabItemDom) {
            var g = this, p = this.options;
            g.droptip = g.droptip || $("<div class='l-tab-drag-droptip' style='display:none'><div class='l-drop-move-up'></div><div class='l-drop-move-down'></div></div>").appendTo('body');
            var drag = $(tabItemDom).ligerDrag(
                {
                    revert: true, animate: false,
                    proxy: function() {
                        var name = $(this).find("a").html();
                        g.dragproxy = $("<div class='l-tab-drag-proxy' style='display:none'><div class='l-drop-icon l-drop-no'></div></div>").appendTo('body');
                        g.dragproxy.append(name);
                        return g.dragproxy;
                    },
                    onRendered: function() {
                        this.set('cursor', 'pointer');
                    },
                    onStartDrag: function(current, e) {
                        if (!$(tabItemDom).hasClass("l-selected")) return false;
                        if (e.button == 2) return false;
                        var obj = e.srcElement || e.target;
                        if ($(obj).hasClass("l-tab-links-item-close")) return false;
                    },
                    onDrag: function(current, e) {
                        if (g.dropIn == null)
                            g.dropIn = -1;
                        var tabItems = g.tab.links.ul.find('>li');
                        var targetIndex = tabItems.index(current.target);
                        tabItems.each(function(i, item) {
                            if (targetIndex == i) {
                                return;
                            }
                            var isAfter = i > targetIndex;
                            if (g.dropIn != -1 && g.dropIn != i) return;
                            var offset = $(this).offset();
                            var range = {
                                top: offset.top,
                                bottom: offset.top + $(this).height(),
                                left: offset.left - 10,
                                right: offset.left + 10
                            };
                            if (isAfter) {
                                range.left += $(this).width();
                                range.right += $(this).width();
                            }
                            var pageX = e.pageX || e.screenX;
                            var pageY = e.pageY || e.screenY;
                            if (pageX > range.left && pageX < range.right && pageY > range.top && pageY < range.bottom) {
                                g.droptip.css({
                                    left: range.left + 5,
                                    top: range.top - 9
                                }).show();
                                g.dropIn = i;
                                g.dragproxy.find(".l-drop-icon").removeClass("l-drop-no").addClass("l-drop-yes");
                            }
                            else {
                                g.dropIn = -1;
                                g.droptip.hide();
                                g.dragproxy.find(".l-drop-icon").removeClass("l-drop-yes").addClass("l-drop-no");
                            }
                        });
                    },
                    onStopDrag: function(current, e) {
                        if (g.dropIn > -1) {
                            var to = g.tab.links.ul.find('>li:eq(' + g.dropIn + ')').attr("tabid");
                            var from = $(current.target).attr("tabid");
                            setTimeout(function() {
                                g.moveTabItem(from, to);
                            }, 0);
                            g.dropIn = -1;
                            g.dragproxy.remove();
                        }
                        g.droptip.hide();
                        this.set('cursor', 'default');
                    }
                });
            return drag;
        },
        _setDragToMove: function(value) {
            if (!$.fn.ligerDrag) return; //需要ligerDrag的支持
            var g = this, p = this.options;
            if (value) {
                if (g.drags) return;
                g.drags = g.drags || [];
                g.tab.links.ul.find('>li').each(function() {
                    g.drags.push(g._applyDrag(this));
                });
            }
        },
        moveTabItem: function(fromTabItemID, toTabItemID) {
            var g = this;
            var from = g.tab.links.ul.find(">li[tabid=" + fromTabItemID + "]");
            var to = g.tab.links.ul.find(">li[tabid=" + toTabItemID + "]");
            var index1 = g.tab.links.ul.find(">li").index(from);
            var index2 = g.tab.links.ul.find(">li").index(to);
            if (index1 < index2) {
                to.after(from);
            }
            else {
                to.before(from);
            }
        },
        //设置tab按钮(左和右),显示返回true,隐藏返回false
        setTabButton: function() {
            var g = this, p = this.options;
            var sumwidth = 0;
            $("li", g.tab.links.ul).each(function() {
                sumwidth += ($(this).outerWidth() + 20);
            });
            var mainwidth = g.tab.outerWidth() - $(".l-tab-switch", g.tab.links).outerWidth() - $(".l-tab-msg", g.tab.links).outerWidth() - $(".l-tab-user", g.tab.links).outerWidth();
            if (sumwidth > mainwidth) {
                if (!$(".l-tab-links-left", g.tab).length) {
                    g.tab.links.append('<div class="l-tab-links-left" title="左滑"><i class="iconfont icon-shuangzuojiantou"></i></div><div class="l-tab-links-right" title="右滑"><i class="iconfont icon-shuangyoujiantou"></i></div>');
                    g.setTabButtonEven();
                }
                return true;
            } else {
                g.tab.links.ul.animate({left: 0});
                $(".l-tab-links-left, .l-tab-links-right", g.tab.links).remove();
                return false;
            }
        },
        //设置左右按钮的事件 标签超出最大宽度时，可左右拖动
        setTabButtonEven: function() {
            var g = this, p = this.options;
            $(".l-tab-links-left", g.tab.links).hover(function() {
                $(this).addClass("l-tab-links-left-over");
            }, function() {
                $(this).removeClass("l-tab-links-left-over");
            }).click(function() {
                g.moveToPrevTabItem();
            });
            $(".l-tab-links-right", g.tab.links).hover(function() {
                $(this).addClass("l-tab-links-right-over");
            }, function() {
                $(this).removeClass("l-tab-links-right-over");
            }).click(function() {
                g.moveToNextTabItem();
            });
        },
        //切换到上一个tab
        moveToPrevTabItem: function(tabid) {
            var g = this, p = this.options;
            var tabItems = $("> li", g.tab.links.ul),
                nextBtn = $(".l-tab-links-right", g.tab),
                prevBtn = $(".l-tab-links-left", g.tab);
            if (!nextBtn.length || !prevBtn.length) return false;
            var prevBtnOffset = prevBtn.offset();
            //计算应该移动到的标签项,并计算从第一项到这个标签项的上一项的宽度总和
            var moveToTabItem = null, currentWidth = 0;
            var prevBtnLeft = prevBtnOffset.left + prevBtn.outerWidth();
            for (var i = 0, l = tabItems.length; i < l; i++) {
                var tabitem = $(tabItems[i]);
                var offset = tabitem.offset();
                var start = offset.left, end = offset.left + tabitem.outerWidth() + parseInt(tabitem.css("marginLeft")) + parseInt(tabitem.css("marginRight"));
                if (tabid != null) {
                    if (start < prevBtnLeft && tabitem.attr("tabid") == tabid) {
                        moveToTabItem = tabitem;
                        break;
                    }
                } else if (start < prevBtnLeft && end >= prevBtnLeft) {
                    moveToTabItem = tabitem;
                    break;
                }
                currentWidth += tabitem.outerWidth() + parseInt(tabitem.css("marginLeft")) + parseInt(tabitem.css("marginRight"));
            }
            if (moveToTabItem == null) return false;
            //计算出正确的移动位置
            var left = currentWidth
                - prevBtn.outerWidth()
                - parseInt(moveToTabItem.outerWidth())
                - parseInt(moveToTabItem.css("marginLeft"))
                - parseInt(moveToTabItem.css("marginRight"));
            left < -prevBtn.outerWidth() && (left = -prevBtn.outerWidth());
            g.tab.links.ul.animate({left: -1 * left});
            return true;
        },
        //切换到下一个tab
        moveToNextTabItem: function(tabid) {
            var g = this, p = this.options;
            var tabItems = $("> li", g.tab.links.ul),
                nextBtn = $(".l-tab-links-right", g.tab),
                prevBtn = $(".l-tab-links-left", g.tab);
            if (!nextBtn.length || !prevBtn.length) return false;
            var nextBtnOffset = nextBtn.offset(), prevBtnOffset = prevBtn.offset();
            //计算应该移动到的标签项,并计算从第一项到这个标签项的宽度总和
            var moveToTabItem = null, currentWidth = 0;
            for (var i = 0, l = tabItems.length; i < l; i++) {
                var tabitem = $(tabItems[i]);
                currentWidth += tabitem.outerWidth()
                    + parseInt(tabitem.css("marginLeft"))
                    + parseInt(tabitem.css("marginRight"));
                var offset = tabitem.offset();
                var start = offset.left, end = offset.left + tabitem.outerWidth() + parseInt(tabitem.css("marginLeft")) + parseInt(tabitem.css("marginRight"));
                if (tabid != null) {
                    if (end > nextBtnOffset.left && tabitem.attr("tabid") == tabid) {
                        moveToTabItem = tabitem;
                        break;
                    }
                } else if (start <= nextBtnOffset.left && end > nextBtnOffset.left) {
                    moveToTabItem = tabitem;
                    break;
                }
            }
            if (moveToTabItem == null) return false;
            //计算出正确的移动位置
            var left = currentWidth
                - (nextBtnOffset.left - prevBtnOffset.left)
                + parseInt(moveToTabItem.outerWidth())
                + parseInt(moveToTabItem.css("marginLeft"))
                + parseInt(moveToTabItem.css("marginRight"));
            g.tab.links.ul.animate({left: -1 * left});
            return true;
        },
        //切换到指定的项目项
        moveToTabItem: function(tabid) {
            var g = this, p = this.options;
            if (!g.moveToPrevTabItem(tabid)) {
                g.moveToNextTabItem(tabid);
            }
        },
        getTabItemCount: function() {
            var g = this, p = this.options;
            return $("li", g.tab.links.ul).length;
        },
        getSelectedTabItemID: function() {
            var g = this, p = this.options;
            return $("li.l-selected", g.tab.links.ul).attr("tabid");
        },
        removeSelectedTabItem: function() {
            var g = this, p = this.options;
            g.removeTabItem(g.getSelectedTabItemID());
        },
        //覆盖选择的tabitem
        overrideSelectedTabItem: function(options) {
            var g = this, p = this.options;
            g.overrideTabItem(g.getSelectedTabItemID(), options);
        },
        //覆盖
        overrideTabItem: function(targettabid, options) {
            var g = this, p = this.options;
            if (g.trigger('beforeOverrideTabItem', [targettabid]) == false)
                return false;
            var tabid = options.tabid;
            if (tabid == undefined) tabid = g.getNewTabid();
            var url = options.url;
            var content = options.content;
            var target = options.target;
            var text = options.text;
            var showClose = options.showClose;
            var height = options.height;
            var tabitem = $("li[tabid=" + targettabid + "]", g.tab.links.ul);
            var contentitem = $(".l-tab-content-item[tabid=" + targettabid + "]", g.tab.content);
            if (!tabitem || !contentitem) return;
            tabitem.attr("tabid", tabid);
            contentitem.attr("tabid", tabid);
            if ($("iframe", contentitem).length == 0 && url) {
                contentitem.html("<iframe frameborder='0'></iframe>");
            }
            else if (content) {
                contentitem.html(content);
            }
            $("iframe", contentitem).attr("name", tabid);
            if (showClose == undefined) showClose = true;
            if (showClose == false) $(".l-tab-links-item-close", tabitem).remove();
            else {
                if ($(".l-tab-links-item-close", tabitem).length == 0)
                    tabitem.append("<div class='l-tab-links-item-close'></div>");
            }
            if (text == undefined) text = tabid;
            if (height) contentitem.height(height);
            $("a", tabitem).html(text);
            $("iframe", contentitem).attr("src", url);
            g.trigger('afterOverrideTabItem', [targettabid]);
        },
        //设置页签项标题
        setHeader: function(tabid, header) {
            $("li[tabid=" + tabid + "] a", this.tab.links.ul).text(header);
        },
        //选中tab项
        selectTabItem: function(tabid) {
            var g = this, p = this.options;
            if (g.trigger('beforeSelectTabItem', [tabid]) == false)
                return false;
            g.selectedTabId = tabid;
            $("> .l-tab-content-item[tabid=" + tabid + "]", g.tab.content).show().siblings().hide();
            $("li[tabid=" + tabid + "]", g.tab.links.ul).addClass("l-selected").siblings().removeClass("l-selected");
            g.trigger('afterSelectTabItem', [tabid]);
        },
        //移动到最后一个tab
        moveToLastTabItem: function() {
            var g = this, p = this.options;
            var sumwidth = 0;
            $("li", g.tab.links.ul).each(function() {
                sumwidth += $(this).width() + 2;
            });
            var mainwidth = g.tab.width();
            if (sumwidth > mainwidth) {
                var btnWitdth = $(".l-tab-links-right", g.tab.links).width();
                g.tab.links.ul.animate({left: -1 * (sumwidth - mainwidth + btnWitdth + 2)});
            }
        },
        getTabItemTitle: function(tabid) {
            var g = this, p = this.options;
            return $("li[tabid=" + tabid + "] a", g.tab.links.ul).text();
        },
        setTabItemTitle: function(tabid, title) {
            var g = this, p = this.options;
            $("li[tabid=" + tabid + "] a", g.tab.links.ul).text(title);
        },
        getTabItemSrc: function(tabid) {
            var g = this, p = this.options;
            return $(".l-tab-content-item[tabid=" + tabid + "] iframe", g.tab.content).attr("src");
        },
        setTabItemSrc: function(tabid, url) {
            var g = this, p = this.options;
            var contentitem = $(".l-tab-content-item[tabid=" + tabid + "]", g.tab.content);
            var iframeloading = $(".l-tab-loading:first", contentitem);
            var iframe = $(".l-tab-content-item[tabid=" + tabid + "] iframe", g.tab.content);
            iframeloading.show();
            iframe.attr("src", url).unbind('load.tab').bind('load.tab', function() {
                iframeloading.hide();
            });
        },
        //判断tab是否存在
        isTabItemExist: function(tabid) {
            var g = this, p = this.options;
            return $("li[tabid=" + tabid + "] a", g.tab.links.ul).length > 0;
        },
        //增加一个tab
        addTabItem: function(options) {
            var g = this, p = this.options;
            if (g.trigger('beforeAddTabItem', [options]) == false)
                return false;
            var tabid = options.tabid;
            if (tabid == undefined) tabid = g.getNewTabid();
            var url = options.url, content = options.content, text = options.text, showClose = options.showClose, height = options.height;
            //如果已经存在
            if (g.isTabItemExist(tabid)) {
                var oldurl = $(".l-tab-content-item[tabid=" + tabid + "]").find("iframe").attr("src");
                g.selectTabItem(tabid);
                if (oldurl != url) {
                    g.overrideTabItem(tabid, options);
                }
                return;
            }
            var tabitem = $("<li><a title='双击关闭 | 右键管理'></a><div class='l-tab-links-item-left'></div><div class='l-tab-links-item-right'></div><div class='l-tab-links-item-close' title='关闭'><i class='iconfont icon-guanbi f12'></i></div></li>");
            var contentitem = $("<div class='l-tab-content-item'><div class='l-tab-loading' style='display:block;'></div><iframe frameborder='0'></iframe></div>");
            var iframeloading = $("div:first", contentitem);
            var iframe = $("iframe:first", contentitem);
            if (g.makeFullHeight) {
                var newheight = g.tab.height() - g.tab.links.height();
                contentitem.height(newheight);
            }
            tabitem.attr("tabid", tabid);
            contentitem.attr("tabid", tabid);
            if (url) {
                iframe[0].tab = g;//增加iframe对tab对象的引用
                if (options.data) { // 传递数据
                    iframe[0].openerData = options.data;
                }
                iframe.attr("name", tabid)
                    .attr("id", tabid)
                    .attr("src", url)
                    .bind('load.tab', function() {
                        iframeloading.hide();
                        if (options.callback)
                            options.callback();
                    });
            } else {
                iframe.remove();
                iframeloading.remove();
            }
            if (content) {
                contentitem.html(content);
                if (options.callback)
                    options.callback();
            } else if (options.target) {
                contentitem.append(options.target);
                if (options.callback)
                    options.callback();
            }
            if (showClose == undefined) showClose = true;
            if (showClose == false) $(".l-tab-links-item-close", tabitem).remove();
            if (text == undefined) text = tabid;
            if (height) contentitem.height(height);
            $("a", tabitem).html(text);
            if ($(".l-tab-itemswitch", g.tab.links.ul).length) {
                tabitem.insertBefore($(".l-tab-itemswitch", g.tab.links.ul));
            } else {
                g.tab.links.ul.append(tabitem);
            }
            g.tab.content.append(contentitem);
            g.selectTabItem(tabid);
            if (g.setTabButton()) {
                g.moveToTabItem(tabid);
            }
            //增加事件
            g._addTabItemEvent(tabitem);
            if (p.dragToMove && $.fn.ligerDrag) {
                g.drags = g.drags || [];
                tabitem.each(function() {
                    g.drags.push(g._applyDrag(this));
                });
            }
            g.toggleSwitch();
            g.trigger('afterAddTabItem', [options]);
        },
        _addTabItemEvent: function(tabitem) {
            var g = this, p = this.options;
            tabitem.click(function() {
                var tabid = $(this).attr("tabid");
                g.selectTabItem(tabid);
            });
            //右键事件支持
            g.tab.menu && g._addTabItemContextMenuEven(tabitem);
            $(".l-tab-links-item-close", tabitem).hover(function() {
                $(this).addClass("l-tab-links-item-close-over");
            }, function() {
                $(this).removeClass("l-tab-links-item-close-over");
            }).click(function() {
                var tabid = $(this).parent().attr("tabid");
                g.removeTabItem(tabid);
            });

        },
        //移除tab项
        removeTabItem: function(tabid) {
            var g = this, p = this.options;
            if (g.trigger('beforeRemoveTabItem', [tabid]) == false)
                return false;
            var currentIsSelected = $("li[tabid=" + tabid + "]", g.tab.links.ul).hasClass("l-selected");
            if (currentIsSelected) {
                $(".l-tab-content-item[tabid=" + tabid + "]", g.tab.content).prev().show();
                $("li[tabid=" + tabid + "]", g.tab.links.ul).prev().addClass("l-selected").siblings().removeClass("l-selected");
            }
            var contentItem = $(".l-tab-content-item[tabid=" + tabid + "]", g.tab.content);
            var jframe = $('iframe', contentItem);
            if (jframe.length) {
                var frame = jframe[0];
                frame.src = "about:blank";
                try {
                    frame.contentWindow.document.write('');
                } catch (e) {
                }
                $.browser.msie && CollectGarbage();
                jframe.remove();
            }
            contentItem.remove();
            $("li[tabid=" + tabid + "]", g.tab.links.ul).remove();
            g.setTabButton();
            g.trigger('afterRemoveTabItem', [tabid]);
        },
        hideTabItem: function(tabid) {
            var g = this, p = this.options;
            var currentIsSelected = $("li[tabid=" + tabid + "]", g.tab.links.ul).hasClass("l-selected");
            if (currentIsSelected) {
                $(".l-tab-content-item[tabid=" + tabid + "]", g.tab.content).prev().show();
                $("li[tabid=" + tabid + "]", g.tab.links.ul).prev().addClass("l-selected").siblings().removeClass("l-selected");
            }
            $("li[tabid=" + tabid + "]", g.tab.links.ul).hide();
            $(".l-tab-content-item[tabid=" + tabid + "]", g.tab.content).hide();
        },
        showTabItem: function(tabid) {
            var g = this, p = this.options;
            $("li[tabid=" + tabid + "]", g.tab.links.ul).show();
        },
        addHeight: function(heightDiff) {
            var g = this, p = this.options;
            var newHeight = g.tab.height() + heightDiff;
            g.setHeight(newHeight);
        },
        setHeight: function(height) {
            var g = this, p = this.options;
            g.tab.height(height);
            g.setContentHeight();
        },
        setContentHeight: function() {
            var g = this, p = this.options;
            var newheight = g.tab.height() - g.tab.links.height();
            g.tab.content.height(newheight);
            $("> .l-tab-content-item", g.tab.content).height(newheight);
        },
        getNewTabid: function() {
            var g = this, p = this.options;
            g.getnewidcount = g.getnewidcount || 0;
            return 'tabitem' + (++g.getnewidcount);
        },
        //notabid 过滤掉没tabid的
        //noclose 过滤掉没有关闭按钮的
        getTabidList: function(notabid, noclose) {
            var g = this, p = this.options;
            var tabidlist = [];
            $("> li", g.tab.links.ul).each(function() {
                if ($(this).attr("tabid")
                    && $(this).attr("tabid") != notabid
                    && (!noclose || $(".l-tab-links-item-close", this).length > 0)) {
                    tabidlist.push($(this).attr("tabid"));
                }
            });
            return tabidlist;
        },
        removeOther: function(tabid, compel) {
            var g = this, p = this.options;
            var tabidlist = g.getTabidList(tabid, true);
            $(tabidlist).each(function() {
                g.removeTabItem(this);
            });
        },
        reload: function(tabid) {
            var g = this, p = this.options;
            var contentitem = $(".l-tab-content-item[tabid=" + tabid + "]");
            var iframeloading = $(".l-tab-loading:first", contentitem);
            var iframe = $("iframe:first", contentitem);
            var url = $(iframe).attr("src");
            iframeloading.show();
            iframe.attr("src", url).unbind('load.tab').bind('load.tab', function() {
                iframeloading.hide();
            });
        },
        removeAll: function(compel) {
            var g = this, p = this.options;
            var tabidlist = g.getTabidList(null, true);
            $(tabidlist).each(function() {
                g.removeTabItem(this);
            });
        },
        onResize: function() {
            var g = this, p = this.options;
            if (!p.height || typeof (p.height) != 'string' || p.height.indexOf('%') == -1) return false;
            //set tab height
            if (g.tab.parent()[0].tagName.toLowerCase() == "body") {
                var windowHeight = $(window).height();
                windowHeight -= parseInt(g.tab.parent().css('paddingTop'));
                windowHeight -= parseInt(g.tab.parent().css('paddingBottom'));
                g.height = p.heightDiff + windowHeight * parseFloat(g.height) * 0.01;
            }
            else {
                g.height = p.heightDiff + (g.tab.parent().height() * parseFloat(p.height) * 0.01);
            }
            g.tab.height(g.height);
            g.setContentHeight();
        },
        _menuItemClick: function(item) {
            var g = this, p = this.options;
            if (!item.id || !g.actionTabid) return;
            switch (item.id) {
                case "close":
                    if (g.trigger('close') == false) return;
                    g.removeTabItem(g.actionTabid);
                    g.actionTabid = null;
                    break;
                case "closeother":
                    if (g.trigger('closeother') == false) return;
                    g.removeOther(g.actionTabid);
                    break;
                case "closeall":
                    if (g.trigger('closeall') == false) return;
                    g.removeAll();
                    g.actionTabid = null;
                    break;
                case "reload":
                    if (g.trigger('reload', [{tabid: g.actionTabid}]) == false) return;
                    g.selectTabItem(g.actionTabid);
                    g.reload(g.actionTabid);
                    break;
            }
        },
        _addTabItemContextMenuEven: function(tabitem) {
            var g = this, p = this.options;
            tabitem.bind("contextmenu", function(e) {
                if (!g.tab.menu) return;
                g.actionTabid = tabitem.attr("tabid");
                g.tab.menu.show({top: e.pageY, left: e.pageX});
                if ($(".l-tab-links-item-close", this).length == 0) {
                    g.tab.menu.setDisabled('close');
                }
                else {
                    g.tab.menu.setEnabled('close');
                }
                return false;
            });
        }
    });
})(jQuery);

/* MENU类 */
(function($) {
    $.ligerMenu = function(options) {
        return $.ligerui.run.call(null, "ligerMenu", arguments);
    };
    $.ligerDefaults.Menu = {
        top: 0,
        left: 0,
        cls: null,
        items: null,
        shadow: true
    };
    $.ligerMethos.Menu = {};
    $.ligerui.controls.Menu = function(options) {
        $.ligerui.controls.Menu.base.constructor.call(this, null, options);
    };
    $.ligerui.controls.Menu.ligerExtend($.ligerui.core.UIComponent, {
        __getType: function() {
            return 'Menu';
        },
        __idPrev: function() {
            return 'Menu';
        },
        _extendMethods: function() {
            return $.ligerMethos.Menu;
        },
        _render: function() {
            var g = this, p = this.options;
            g.menuItemCount = 0;
            //全部菜单
            g.menus = {};
            //顶级菜单
            g.menu = g.createMenu();
            g.element = g.menu[0];
            g.menu.css({top: p.top, left: p.left, width: p.width});
            p.cls && g.menu.addClass(p.cls);
            p.items && $(p.items).each(function(i, item) {
                g.addItem(item);
            });
            $(document).bind('click.menu', function() {
                for (var menuid in g.menus) {
                    var menu = g.menus[menuid];
                    if (!menu) return;
                    menu.hide();
                    if (menu.shadow) menu.shadow.hide();
                }
            });
            g.set(p);
        },
        show: function(options, menu) {
            var g = this, p = this.options;
            if (menu == undefined) menu = g.menu;
            if (options && options.left != undefined) {
                menu.css({left: options.left});
            }
            if (options && options.top != undefined) {
                menu.css({top: options.top});
            }
            menu.show();
        },
        hide: function(menu) {
            var g = this, p = this.options;
            if (menu == undefined) menu = g.menu;
            g.hideAllSubMenu(menu);
            menu.hide();
        },
        toggle: function() {
            var g = this, p = this.options;
            g.menu.toggle();
        },
        removeItem: function(itemid) {
            var g = this, p = this.options;
            $("> .l-menu-item[menuitemid=" + itemid + "]", g.menu.items).remove();
        },
        setEnabled: function(itemid) {
            var g = this, p = this.options;
            $("> .l-menu-item[menuitemid=" + itemid + "]", g.menu.items).removeClass("l-menu-item-disable");
        },
        setMenuText: function(itemid, text) {
            var g = this, p = this.options;
            $("> .l-menu-item[menuitemid=" + itemid + "] >.l-menu-item-text:first", g.menu.items).html(text);
        },
        setDisabled: function(itemid) {
            var g = this, p = this.options;
            $("> .l-menu-item[menuitemid=" + itemid + "]", g.menu.items).addClass("l-menu-item-disable");
        },
        isEnable: function(itemid) {
            var g = this, p = this.options;
            return !$("> .l-menu-item[menuitemid=" + itemid + "]", g.menu.items).hasClass("l-menu-item-disable");
        },
        getItemCount: function() {
            var g = this, p = this.options;
            return $("> .l-menu-item", g.menu.items).length;
        },
        addItem: function(item, menu) {
            var g = this, p = this.options;
            if (!item) return;
            if (menu == undefined) menu = g.menu;
            var ditem = $('<div class="l-menu-item"><div class="l-menu-item-text"></div></div>');
            var itemcount = $("> .l-menu-item", menu.items).length;
            menu.items.append(ditem);
            ditem.attr("ligeruimenutemid", ++g.menuItemCount);
            item.id && ditem.attr("menuitemid", item.id);
            item.text && $(">.l-menu-item-text:first", ditem).html(item.text);
            if (item.disable || item.disabled)
                ditem.addClass("l-menu-item-disable");
            if (item.children) {
                ditem.append('<div class="l-menu-item-arrow"></div>');
                var newmenu = g.createMenu(ditem.attr("ligeruimenutemid"));
                g.menus[ditem.attr("ligeruimenutemid")] = newmenu;
                newmenu.width(p.width);
                newmenu.hover(null, function() {
                    if (!newmenu.showedSubMenu)
                        g.hide(newmenu);
                });
                $(item.children).each(function() {
                    g.addItem(this, newmenu);
                });
            }
            item.click && ditem.click(function() {
                if ($(this).hasClass("l-menu-item-disable")) return;
                item.click(item, itemcount);
            });
            item.dblclick && ditem.dblclick(function() {
                if ($(this).hasClass("l-menu-item-disable")) return;
                item.dblclick(item, itemcount);
            });

            var menuover = $("> .l-menu-over:first", menu);
            ditem.hover(function() {
                if ($(this).hasClass("l-menu-item-disable")) return;
                var itemtop = $(this).offset().top;
                var top = itemtop - menu.offset().top;
                menuover.css({top: top});
                g.hideAllSubMenu(menu);
                if (item.children) {
                    var ligeruimenutemid = $(this).attr("ligeruimenutemid");
                    if (!ligeruimenutemid) return;
                    if (g.menus[ligeruimenutemid]) {
                        g.show({top: itemtop, left: $(this).offset().left + $(this).width() - 5}, g.menus[ligeruimenutemid]);
                        menu.showedSubMenu = true;
                    }
                }
            }, function() {
                if ($(this).hasClass("l-menu-item-disable")) return;
                var ligeruimenutemid = $(this).attr("ligeruimenutemid");
                if (item.children) {
                    var ligeruimenutemid = $(this).attr("ligeruimenutemid");
                    if (!ligeruimenutemid) return;
                };
            });
        },
        hideAllSubMenu: function(menu) {
            var g = this, p = this.options;
            if (menu == undefined) menu = g.menu;
            $("> .l-menu-item", menu.items).each(function() {
                if ($("> .l-menu-item-arrow", this).length > 0) {
                    var ligeruimenutemid = $(this).attr("ligeruimenutemid");
                    if (!ligeruimenutemid) return;
                    g.menus[ligeruimenutemid] && g.hide(g.menus[ligeruimenutemid]);
                }
            });
            menu.showedSubMenu = false;
        },
        createMenu: function(parentMenuItemID) {
            var g = this, p = this.options;
            var menu = $('<div class="l-menu" style="display:none"><div class="l-menu-inner"></div></div>');
            parentMenuItemID && menu.attr("ligeruiparentmenuitemid", parentMenuItemID);
            menu.items = $("> .l-menu-inner:first", menu);
            menu.appendTo('body');
            if (parentMenuItemID)
                g.menus[parentMenuItemID] = menu;
            else
                g.menus[0] = menu;
            return menu;
        }
    });
    //旧写法保留
    $.ligerui.controls.Menu.prototype.setEnable = $.ligerui.controls.Menu.prototype.setEnabled;
    $.ligerui.controls.Menu.prototype.setDisable = $.ligerui.controls.Menu.prototype.setDisabled;
})(jQuery);
