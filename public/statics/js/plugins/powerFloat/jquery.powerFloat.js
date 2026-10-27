/* powerFloat 浮动插件 */
(function(a) {
    a.fn.powerFloat = function(d) {
        return a(this).each(function() {
            var e,
                f = a.extend({}, c, d || {}),
                g = function(a, c) {
                    b.target && "none" !== b.target.css("display") && b.targetHide(),
                        (b.s = a),
                        (b.trigger = c);
                };
            switch (f.eventType) {
                case "hover":
                    a(this).hover(
                        function() {
                            b.timerHold && (b.flagDisplay = !0);
                            var c = parseInt(f.showDelay, 10);
                            g(f, a(this)),
                                c
                                    ? (e && clearTimeout(e),
                                        (e = setTimeout(function() {
                                            b.targetGet.call(b);
                                        }, c)))
                                    : b.targetGet();
                        },
                        function() {
                            e && clearTimeout(e),
                                b.timerHold && clearTimeout(b.timerHold),
                                (b.flagDisplay = !1),
                                b.targetHold();
                        }
                    ), f.hoverFollow &&
                        a(this).mousemove(function(a) {
                            return (
                                (b.cacheData.left = a.pageX),
                                (b.cacheData.top = a.pageY),
                                b.targetGet.call(b),
                                !1
                            );
                        });
                    break;
                case "click":
                    a(this).click(function(c) {
                        b.display && b.trigger && c.target === b.trigger.get(0)
                            ? ((b.flagDisplay = !1), b.displayDetect())
                            : (g(f, a(this)),
                                b.targetGet(),
                                a(document).data("mouseupBind") ||
                                a(document)
                                    .bind("mouseup", function(c) {
                                        var d = !1;
                                        if (b.trigger) {
                                            var e = b.target.attr("id");
                                            e ||
                                                ((e = "R_" + Math.random()),
                                                    b.target.attr("id", e)),
                                                a(c.target)
                                                    .parents()
                                                    .each(function() {
                                                        a(this).attr("id") === e && (d = !0);
                                                    }),
                                                "click" === f.eventType &&
                                                b.display &&
                                                c.target != b.trigger.get(0) &&
                                                !d &&
                                                ((b.flagDisplay = !1), b.displayDetect());
                                        }
                                        return !1;
                                    })
                                    .data("mouseupBind", !0));
                    });
                    break;
                case "focus":
                    a(this)
                        .focus(function() {
                            var c = a(this);
                            setTimeout(function() {
                                g(f, c), b.targetGet();
                            }, 200);
                        })
                        .blur(function() {
                            (b.flagDisplay = !1),
                                setTimeout(function() {
                                    b.displayDetect();
                                }, 190);
                        });
                    break;
                default:
                    g(f, a(this)),
                        b.targetGet(),
                        a(document)
                            .unbind("mouseup")
                            .data("mouseupBind", !1);
            }
        });
    };
    var b = {
        targetGet: function() {
            if (!this.trigger) return this;
            var c = this.trigger.attr(this.s.targetAttr),
                d =
                    "function" == typeof this.s.target
                        ? this.s.target.call(this.trigger)
                        : this.s.target;
            switch (this.s.targetMode) {
                case "common":
                    if (d) {
                        var e = typeof d;
                        "object" === e
                            ? d.size() && (b.target = d.eq(0))
                            : "string" === e && a(d).size() && (b.target = a(d).eq(0));
                    } else c && a("#" + c).size() && (b.target = a("#" + c));
                    if (!b.target) return this;
                    b.targetShow();
                    break;
                case "ajax":
                    var f = d || c;
                    if (((this.targetProtect = !1), !f)) return;
                    b.cacheData[f] || b.loading();
                    var g = new Image();
                    (g.onload = function() {
                        var c = g.width,
                            d = g.height,
                            e = a(window).width(),
                            h = a(window).height(),
                            i = c / d,
                            j = e / h;
                        i > j
                            ? c > e / 2 && ((c = e / 2), (d = c / i))
                            : d > h / 2 && ((d = h / 2), (c = d * i));
                        var k =
                            '<img class="float_ajax_image" src="' +
                            f +
                            '" width="' +
                            c +
                            '" height = "' +
                            d +
                            '" />';
                        (b.cacheData[f] = !0), (b.target = a(k)), b.targetShow();
                    }),
                        (g.onerror = function() {
                            /(\.jpg|\.png|\.gif|\.bmp|\.jpeg)$/i.test(f)
                                ? ((b.target = a(
                                    '<div class="float_ajax_error">图片加载失败。</div>'
                                )),
                                    b.targetShow())
                                : a.ajax({
                                    url: f,
                                    success: function(c) {
                                        "string" == typeof c &&
                                            ((b.cacheData[f] = !0),
                                                (b.target = a(
                                                    '<div class="float_ajax_data">' + c + "</div>"
                                                )),
                                                b.targetShow());
                                    },
                                    error: function() {
                                        (b.target = a(
                                            '<div class="float_ajax_error">数据没有加载成功。</div>'
                                        )),
                                            b.targetShow();
                                    }
                                });
                        }),
                        (g.src = f);
                    break;
                case "list":
                    var h,
                        i = '<ul class="float_list_ul">';
                    a.isArray(d) && (h = d.length)
                        ? a.each(d, function(a, b) {
                            var c,
                                d,
                                e = "",
                                f = "";
                            0 === a && (f = ' class="float_list_li_first"'),
                                a === h - 1 && (f = ' class="float_list_li_last"'),
                                "object" == typeof b && (c = b.text.toString())
                                    ? (e = (d = b.href || "javascript:")
                                        ? '<a href="' +
                                        d +
                                        '" class="float_list_a">' +
                                        c +
                                        "</a>"
                                        : c)
                                    : "string" == typeof b && b && (e = b),
                                e && (i += "<li" + f + ">" + e + "</li>");
                        })
                        : (i += '<li class="float_list_null">列表无数据。</li>'),
                        (i += "</ul>"),
                        (b.target = a(i)),
                        (this.targetProtect = !1),
                        b.targetShow();
                    break;
                case "remind":
                    var j = d || c;
                    (this.targetProtect = !1),
                        "string" == typeof j &&
                        ((b.target = a("<span>" + j + "</span>")), b.targetShow());
                    break;
                default:
                    var k = d || c,
                        e = typeof k;
                    k &&
                        ("string" === e
                            ? (/^.[^:#\[\.,]*$/.test(k)
                                ? a(k).size()
                                    ? ((b.target = a(k).eq(0)), (this.targetProtect = !0))
                                    : a("#" + k).size()
                                        ? ((b.target = a("#" + k).eq(0)),
                                            (this.targetProtect = !0))
                                        : ((b.target = a("<div>" + k + "</div>")),
                                            (this.targetProtect = !1))
                                : ((b.target = a("<div>" + k + "</div>")),
                                    (this.targetProtect = !1)),
                                b.targetShow())
                            : "object" === e &&
                            !a.isArray(k) &&
                            k.size() &&
                            ((b.target = k.eq(0)),
                                (this.targetProtect = !0),
                                b.targetShow()));
            }
            return this;
        },
        container: function() {
            var c = this.s.container,
                d = this.s.targetMode || "mode";
            return (
                (this.s.sharpAngle = "ajax" === d || "remind" === d ? !0 : !1),
                this.s.reverseSharp && (this.s.sharpAngle = !this.s.sharpAngle),
                "common" !== d &&
                (null === c && (c = "plugin"),
                    "plugin" === c &&
                    (a("#floatBox_" + d).size() ||
                        a(
                            '<div id="floatBox_' +
                            d +
                            '" class="float_' +
                            d +
                            '_box"></div>'
                        )
                            .appendTo(a("body"))
                            .hide(),
                        (c = a("#floatBox_" + d))),
                    c &&
                    "string" != typeof c &&
                    c.size() &&
                    (this.targetProtect && b.target.show().css("position", "static"),
                        (b.target = c.empty().append(b.target)))),
                this
            );
        },
        setWidth: function() {
            var a = this.s.width;
            return (
                "auto" === a
                    ? this.target.get(0).style.width && this.target.css("width", "auto")
                    : "inherit" === a
                        ? this.target.width(this.trigger.width())
                        : this.target.css("width", a),
                this
            );
        },
        position: function() {
            if (!this.trigger || !this.target) return this;
            var c,
                d,
                e,
                f,
                g,
                h,
                i,
                j = 0,
                k = 0,
                l = 0,
                m = 0,
                n = this.target.data("height"),
                o = this.target.data("width"),
                p = a(window).scrollTop(),
                q = parseInt(this.s.offsets.x, 10) || 0,
                r = parseInt(this.s.offsets.y, 10) || 0,
                s = this.cacheData;
            n ||
                ((n = this.target.outerHeight()),
                    this.s.hoverFollow && this.target.data("height", n)),
                o ||
                ((o = this.target.outerWidth()),
                    this.s.hoverFollow && this.target.data("width", o)),
                (c = this.trigger.offset()),
                (j = this.trigger.outerHeight()),
                (k = this.trigger.outerWidth()),
                (d = c.left),
                (e = c.top);
            var t = function() {
                0 > d
                    ? (d = 0)
                    : d + j > a(window).width() && (d = a(window).width() - k);
            },
                u = function() {
                    0 > e
                        ? (e = 0)
                        : e + j > a(document).height() && (e = a(document).height() - j);
                };
            this.s.hoverFollow &&
                s.left &&
                s.top &&
                ("x" === this.s.hoverFollow
                    ? ((d = s.left), t())
                    : "y" === this.s.hoverFollow
                        ? ((e = s.top), u())
                        : ((d = s.left), (e = s.top), t(), u()));
            var v,
                w = [
                    "4-1",
                    "1-4",
                    "5-7",
                    "2-3",
                    "2-1",
                    "6-8",
                    "3-4",
                    "4-3",
                    "8-6",
                    "1-2",
                    "7-5",
                    "3-2"
                ],
                x = this.s.position,
                y = !1;
            a.each(w, function(a, b) {
                return b === x ? void (y = !0) : void 0;
            }),
                y || (x = "4-1");
            var z = function(a) {
                var b = "bottom";
                switch (a) {
                    case "1-4":
                    case "5-7":
                    case "2-3":
                        b = "top";
                        break;
                    case "2-1":
                    case "6-8":
                    case "3-4":
                        b = "right";
                        break;
                    case "1-2":
                    case "8-6":
                    case "4-3":
                        b = "left";
                        break;
                    case "4-1":
                    case "7-5":
                    case "3-2":
                        b = "bottom";
                }
                return b;
            },
                A = function(a) {
                    return "5-7" === a || "6-8" === a || "8-6" === a || "7-5" === a
                        ? !0
                        : !1;
                },
                B = function(c) {
                    var f = 0,
                        g = 0,
                        h = b.s.sharpAngle && b.corner ? !0 : !1;
                    if ("right" === c) {
                        if (
                            ((g = d + k + o + q),
                                h && (g += b.corner.width()),
                                g > a(window).width())
                        )
                            return !1;
                    } else if ("bottom" === c) {
                        if (
                            ((f = e + j + n + r),
                                h && (f += b.corner.height()),
                                f > p + a(window).height())
                        )
                            return !1;
                    } else if ("top" === c) {
                        if (((f = n + r), h && (f += b.corner.height()), f > e - p))
                            return !1;
                    } else if (
                        "left" === c &&
                        ((g = o + q), h && (g += b.corner.width()), g > d)
                    )
                        return !1;
                    return !0;
                };
            (v = z(x)),
                this.s.edgeAdjust &&
                (B(v)
                    ? !(function() {
                        if (!A(x)) {
                            var a,
                                b = {
                                    top: {right: "2-3", left: "1-4"},
                                    right: {top: "2-1", bottom: "3-4"},
                                    bottom: {right: "3-2", left: "4-1"},
                                    left: {top: "1-2", bottom: "4-3"}
                                },
                                c = b[v];
                            if (c) for (a in c) B(a) || (x = c[a]);
                        }
                    })()
                    : !(function() {
                        if (A(x)) {
                            var a = {
                                "5-7": "7-5",
                                "7-5": "5-7",
                                "6-8": "8-6",
                                "8-6": "6-8"
                            };
                            x = a[x];
                        } else {
                            var b = {
                                top: {left: "3-2", right: "4-1"},
                                right: {bottom: "1-2", top: "4-3"},
                                bottom: {left: "2-3", right: "1-4"},
                                left: {bottom: "2-1", top: "3-4"}
                            },
                                c = b[v],
                                d = [];
                            for (name in c) d.push(name);
                            x = B(d[0]) || !B(d[1]) ? c[d[0]] : c[d[1]];
                        }
                    })());
            var C = z(x),
                D = x.split("-")[0];
            if (this.s.hoverFollow)
                "x" === this.s.hoverFollow
                    ? ((f = d + q),
                        (f =
                            "1" === D || "8" === D || "4" === D
                                ? d - (o - k) / 2 + q
                                : d - (o - k) + q),
                        "1" === D || "5" === D || "2" === D
                            ? ((g = e - r - n - m), (i = e - m - r - 1))
                            : ((g = e + j + r + m), (i = e + j + r + 1)),
                        (h = c.left - (l - k) / 2))
                    : "y" === this.s.hoverFollow
                        ? ((g =
                            "1" === D || "5" === D || "2" === D
                                ? e - (n - j) / 2 + r
                                : e - (n - j) + r),
                            "1" === D || "8" === D || "4" === D
                                ? ((f = d - o - q - l), (h = d - l - q - 1))
                                : ((f = d + k - q + l), (h = d + k + q + 1)),
                            (i = c.top - (m - j) / 2))
                        : ((f = d + q), (g = e + r));
            else
                switch (C) {
                    case "top":
                        (g = e - r - n - m),
                            (f =
                                "1" == D
                                    ? d - q
                                    : "5" === D
                                        ? d - (o - k) / 2 - q
                                        : d - (o - k) - q),
                            (i = e - m - r - 1),
                            (h = d - (l - k) / 2);
                        break;
                    case "right":
                        (f = d + k + q + l),
                            (g =
                                "2" == D
                                    ? e + r
                                    : "6" === D
                                        ? e - (n - j) / 2 + r
                                        : e - (n - j) + r),
                            (h = d + k + q + 1),
                            (i = e - (m - j) / 2);
                        break;
                    case "bottom":
                        (g = e + j + r + m),
                            (f =
                                "4" == D
                                    ? d + q
                                    : "7" === D
                                        ? d - (o - k) / 2 + q
                                        : d - (o - k) + q),
                            (i = e + j + r + 1),
                            (h = d - (l - k) / 2);
                        break;
                    case "left":
                        (f = d - o - q - l),
                            (g =
                                "2" == D
                                    ? e - r
                                    : "6" === D
                                        ? e - (o - k) / 2 - r
                                        : e - (n - j) - r),
                            (h = f + l),
                            (i = e - (o - l) / 2);
                }
            return (
                m &&
                l &&
                this.corner &&
                this.corner.css({left: h, top: i, zIndex: this.s.zIndex + 1}),
                this.target.css({
                    position: "absolute",
                    left: f,
                    top: g,
                    zIndex: this.s.zIndex
                }),
                this
            );
        },
        targetHold: function() {
            if (this.s.hoverHold) {
                var a = parseInt(this.s.hideDelay, 10) || 200;
                this.target &&
                    this.target.hover(
                        function() {
                            b.flagDisplay = !0;
                        },
                        function() {
                            b.timerHold && clearTimeout(b.timerHold),
                                (b.flagDisplay = !1),
                                b.targetHold();
                        }
                    ),
                    (b.timerHold = setTimeout(function() {
                        b.displayDetect.call(b);
                    }, a));
            } else this.displayDetect();
            return this;
        },
        loading: function() {
            return (
                (this.target = a('<div class="float_loading"></div>')),
                this.targetShow(),
                this.target.removeData("width").removeData("height"),
                this
            );
        },
        displayDetect: function() {
            return (
                !this.flagDisplay &&
                this.display &&
                (this.targetHide(), (this.timerHold = null)),
                this
            );
        },
        targetShow: function() {
            return (
                b.cornerClear(),
                (this.display = !0),
                this.container()
                    .setWidth()
                    .position(),
                this.target.show(),
                a.isFunction(this.s.showCall) &&
                this.s.showCall.call(this.trigger, this.target),
                this
            );
        },
        targetHide: function() {
            return (
                (this.display = !1),
                this.targetClear(),
                this.cornerClear(),
                a.isFunction(this.s.hideCall) && this.s.hideCall.call(this.trigger),
                (this.target = null),
                (this.trigger = null),
                (this.s = {}),
                (this.targetProtect = !1),
                this
            );
        },
        targetClear: function() {
            this.target &&
                (this.target.data("width") &&
                    this.target.removeData("width").removeData("height"),
                    this.targetProtect &&
                    this.target
                        .children()
                        .hide()
                        .appendTo(a("body")),
                    this.target.unbind().hide());
        },
        cornerClear: function() {
            this.corner && this.corner.remove();
        },
        target: null,
        trigger: null,
        s: {},
        cacheData: {},
        targetProtect: !1
    };
    (a.powerFloat = {});
    (a.powerFloat.hide = function() {
        b.targetHide();
    });
    var c = {
        width: "auto",
        offsets: {x: 0, y: 0},
        zIndex: 999,
        eventType: "hover",
        showDelay: 0,
        hideDelay: 0,
        hoverHold: !0,
        hoverFollow: !1,
        targetMode: "common",
        target: null,
        targetAttr: "rel",
        container: null,
        reverseSharp: !1,
        position: "4-1",
        edgeAdjust: !0,
        showCall: a.noop,
        hideCall: a.noop
    };
})(jQuery);
