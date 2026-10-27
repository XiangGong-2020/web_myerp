var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var isNewImportExcel = !1;
var contactCombo;
var ourselfCombo;
var company = [];
var originalData;
var curRow;
var curCol;
var btn_draft = '<a id="draft" class="ui-btn bg-night"><i class="iconfont icon-baocun"></i><span>保存为草稿</span></a>';
var btn_final = '<a id="final" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存为正式</span></a>';
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_print = '<a id="print" class="ui-btn bg-teal"><i class="iconfont icon-dayin"></i><span>打印</span></a>';
var btn_copy = '<a id="copy" class="ui-btn bg-teal"><i class="iconfont icon-fuzhi"></i><span>复制</span></a>';
var btn_check = '<a id="check" class="ui-btn bg-green"><i class="iconfont icon-shenhe"></i><span>审核</span></a>';
var btn_uncheck = '<a id="uncheck" class="ui-btn bg-red"><i class="iconfont icon-shenhe"></i><span>反审核</span></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var btn_del_srcmoney1 = '<a id="delsrcmoney1" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除付款单</span></a>';
var btn_del_srcmoney2 = '<a id="delsrcmoney2" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除退款单</span></a>';
var combo1;
var breakShow = !1;
var quoteOperData = {};
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid(a);
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        $('#billFrom').val(0 == a.isReq ? '手工' : (1 == a.isReq ? '转化' : '转正'));
        $('#billDate').val(defaultPage.SYSTEM.endDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
        contactCombo = Business.supplierCombo($('#contact'), {
            width: 200,
            disabled: originalData.isReq == 1 ? !0 : !1
        });
        ourselfCombo = $('#ourself').combo({
            async: false, // 异步 否
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    a.data.rows.forEach(element => {
                        if (element.id > 0 && element.isDisable == 0) {
                            company.push(element);
                        }
                    });
                    return company;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: a.sId > 0 ? ['id', a.sId] : -1,
            width: 200,
            callback: {
                onChange: function(data) {
                    if (data) {
                        $('#ourself').data('ourselfData', data);
                    } else {
                        $('#ourself').removeData('ourselfData');
                    }
                }
            }
        }).getCombo();
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + (a.isDraft == 1 ? btn_final : '') + btn_print + btn_copy + (a.checkId > 0 ? btn_uncheck : btn_check) + btn_delete + (a.srcMoneyId1 > 0 ? btn_del_srcmoney1 : '') + (a.srcMoneyId2 > 0 ? btn_del_srcmoney2 : ''));
            a.isDraft == 1 && $('#edit span').after('<b>当前为草稿</b>');
            $('#billNo').val(a.billNo);
            $('#repeatNo').html(a.billNo);
            $('#contact').data('contactData', {
                id: a.contactId,
                name: a.contactName
            });
            contactCombo.input.val(a.contactName);
            $('#ourself').data('ourselfData', {
                id: a.sId,
                name: a.sName
            });
            ourselfCombo.input.val(a.sName);
            if ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) == -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) == -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) == -1) {
                $('#amount').parent().addClass('dn');
            }
            if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) != -1) {
                $('#exportGridAll').removeClass('dn');
            }
            $('#amount').val(a.amount);
            $('#billDate').val(a.billDate);
            $('#endDate').val(a.endDate);
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#createTime').html(a.createTime);
            $('#checkName').html(a.checkName);
            $('#modifyTime').html(a.modifyTime);
            if (a.isReq == 2) {
                if (a.reqInfo) {
                    $('.reqInfo').length == 0 && $('.come-from').after('<span class="reqInfo es" title="' + a.reqInfo + '">' + a.reqInfo + '</span>');
                } else {
                    $('.reqInfo').length == 0 && $('.come-from').after('<span class="reqInfo es">申请人： ' + a.reqName + ' &nbsp;&nbsp; / &nbsp;&nbsp; 申请方式： 手动新增</span>');
                }
                $('#quoteOper').removeClass('dn');
                combo1 = $('#combo1').combo({
                        data: '/Bill/InvPc/getAcceptedUser?id=' + a.id,
                        value: 'id',
                        text: 'name',
                        width: 80,
                        ajaxOptions: {
                            formatData: function(res) {
                                return res.data.unshift({
                                    id: -1,
                                    name: '全部'
                                }), res.data;
                            }
                        },
                        defaultSelected: 0,
                        cache: !1,
                        editable: !1,
                        trigger: !0,
                        emptyOptions: !1,
                        callback: {
                            onListClick: function() {
                                quoteOperData = {id: a.id, uId: combo1.getValue()};
                                $('#grid').jqGrid('setGridParam', {
                                    url: '/Bill/InvPc/getAcceptedInfo',
                                    postData: quoteOperData,
                                    datatype: 'json'
                                }).trigger('reloadGrid');
                            }
                        },
                        queryDelay: 0
                }).getCombo();
                if ($.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) === -1 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) === -1) {
                    $('#showQuoteHis').remove();
                } else {
                    $('#showQuoteHis').removeClass('dn');
                }
            }
            a.files && THISPAGE.addFiles(a.files);
        } else {
            if (a.contactId && a.contactId > 0) {
                $('#contact').data('contactData', {
                    id: a.contactId,
                    name: a.contactName
                });
                contactCombo.input.val(a.contactName);
            } else {
                $('#toolTop').html(btn_save + btn_draft);
                $('#contact').data('contactData', {
                    id: 0,
                    name: '特殊采购虚拟商家'
                });
                contactCombo.input.val('特殊采购虚拟商家');
            }
            if (a.sId && a.sId > 0) {
                $('#ourself').data('ourselfData', {
                    id: a.sId,
                    name: a.sName
                });
                ourselfCombo.input.val(a.sName);
            }
            $('#amount').val(a.amount || '');
            $('#description').val(a.description || '');
            $('#uName').html(defaultPage.SYSTEM.uName || '');
        }
        if (urlParam.copyFromContractSale) {
            $('#endDate').val(originalData.endDate);
        }
    },
    initGrid: function(a) {
        if (a.id) {
            var b = 1 - a.entries.length;
            if (b > 0) {
                for (var c = 0; b > c; c++) {
                    a.entries.push({});
                }
            }
        }
        THISPAGE.newId = 2;
        $('#grid').jqGrid({
            data: a.entries,
            datatype: 'clientSide',
            colModel: [{
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                hidden: (a.isReq == 2 || (a.id > 0 && a.uId != defaultPage.SYSTEM.uId)) ? !0 : !1,
                formatter: Public.billsOper
            }, {
                name: 'zid',
                label: '自编序号',
                width: 56,
                fixed: !0,
                title: !0,
                editable: (a.isReq == 2 || (a.id > 0 && a.uId != defaultPage.SYSTEM.uId)) ? !1 : !0,
                edittype: 'textarea',
                formatter: function(x, y, z) {
                    var zid = '';
                    if (!x) {
                        if (z.name) {
                            zid = y.rowId;
                        }
                    } else {
                        zid = x;
                    }
                    return zid;
                }
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                editable: (a.isReq == 2 || (a.id > 0 && a.uId != defaultPage.SYSTEM.uId)) ? !1 : !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                editable: (a.isReq == 2 || (a.id > 0 && a.uId != defaultPage.SYSTEM.uId)) ? !1 : !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                editable: (a.isReq == 2 || (a.id > 0 && a.uId != defaultPage.SYSTEM.uId)) ? !1 : !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '品牌',
                width: 60,
                fixed: !0,
                title: !0,
                editable: (a.isReq == 2 || (a.id > 0 && a.uId != defaultPage.SYSTEM.uId)) ? !1 : !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 40,
                fixed: !0,
                title: !0,
                editable: (a.isReq == 2 || (a.id > 0 && a.uId != defaultPage.SYSTEM.uId)) ? !1 : !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'qty',
                label: '数量',
                width: 54,
                fixed: !0,
                editable: (a.isReq == 2 || (a.id > 0 && a.uId != defaultPage.SYSTEM.uId)) ? !1 : !0,
                formatter: function(a, b, c) {
                    return Number(a) || '';
                }
            }, {
                name: 'limitPrice',
                label: '参考单价',
                width: 70,
                fixed: !0,
                hidden: (a.isReq == 2 && ($.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1 || $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) !== -1)) ? !1 : !0,
                editable: !1,
                classes: 'gray',
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'price',
                label: '采购单价',
                width: 82,
                fixed: !0,
                hidden: (a.isReq == 1 || ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1)) ? !0 : !1,
                editable: !0,
                formatter: function(x, y, z) {
                    if (a.isReq == 2 && $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) === -1 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) === -1 && z.uId != defaultPage.SYSTEM.uId && $.inArray(String(z.uId), defaultPage.SYSTEM.enableUserIds) !== -1) {
                        return '';
                    } else {
                        return x ? Number(x).toFixed(pricePlaces) : '';
                    }
                },
                cellattr: function(rowId, val, rawObject, cm, rdata) {
                    if (a.isReq == 2 && $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1) {
                        if (rawObject.price > rawObject.limitPrice) {
                            return 'class="red" title="红色：采购价异常偏高"';
                        } else if (a.isReq == 2 && rawObject.price > rawObject.quotePrice) {
                            return 'class="yellow" title="黄色：采购价高于最初报价"';
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'amount',
                label: '采购总价',
                width: 70,
                fixed: !0,
                hidden: (a.isReq == 1 || ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1)) ? !0 : !1,
                editable: !0,
                formatter: function(x, y, z) {
                    if (a.isReq == 2 && $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) === -1 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) === -1 && z.uId != defaultPage.SYSTEM.uId && $.inArray(String(z.uId), defaultPage.SYSTEM.enableUserIds) !== -1) {
                        return '';
                    } else {
                        return x ? Number(x).toFixed(amountPlaces) : '';
                    }
                },
                cellattr: function(rowId, val, rawObject, cm, rdata) {
                    if (a.isReq == 2 && $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1) {
                        if (rawObject.price > rawObject.limitPrice) {
                            return 'class="red" title="红色：采购价异常偏高"';
                        } else if (a.isReq == 2 && rawObject.price > rawObject.quotePrice) {
                            return 'class="yellow" title="黄色：采购价高于最初报价"';
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'saleAmount',
                label: '销售总价',
                width: 70,
                fixed: !0,
                hidden: (a.isReq == 2 && $.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1) ? !1 : !0,
                editable: !1,
                classes: 'gray',
                formatter: function(a, b, c) {
                    return c.limitPrice ? Number(c.limitPrice * 1.25 * c.qty).toFixed(amountPlaces) : (a || '');
                }
            }, {
                name: 'buyUrl',
                label: '参考链接',
                width: 56,
                fixed: !0,
                hidden: a.isReq == 0 ? !0 : !1,
                title: !0,
                editable: (a.isReq == 1 && a.uId == defaultPage.SYSTEM.uId) ? !0 : !1,
                edittype: 'textarea',
                classes: 'silver poi ui-ellipsis'
            }, {
                name: 'myUrl',
                label: '采购链接',
                width: 80,
                fixed: !0,
                hidden: (a.isReq == 1 || ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1)) ? !0 : !1,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'poi ui-ellipsis',
                formatter: function(x, y, z) {
                    if (a.isReq == 2 && $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) === -1 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) === -1 && z.uId != defaultPage.SYSTEM.uId && $.inArray(String(z.uId), defaultPage.SYSTEM.enableUserIds) !== -1) {
                        return '';
                    } else {
                        return x || '';
                    }
                }
            }, {
                name: 'description1',
                label: '备注1',
                width: 60,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis',
                formatter: function(x, y, z) {
                    if (a.isReq == 2 && $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) === -1 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) === -1 && z.uId != defaultPage.SYSTEM.uId && $.inArray(String(z.uId), defaultPage.SYSTEM.enableUserIds) !== -1) {
                        return '';
                    } else {
                        return x || '';
                    }
                }
            }, {
                name: 'description2',
                label: '备注2',
                width: 60,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis',
                formatter: function(x, y, z) {
                    if (a.isReq == 2 && $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) === -1 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) === -1 && z.uId != defaultPage.SYSTEM.uId && $.inArray(String(z.uId), defaultPage.SYSTEM.enableUserIds) !== -1) {
                        return '';
                    } else {
                        return x || '';
                    }
                }
            }, {
                name: 'uName',
                label: '报价人',
                width: 44,
                fixed: !0,
                hidden: a.isReq == 2 ? !1 : !0,
                classes: 'night'
            }, {
                name: 'quoteInfo',
                label: '报价信息',
                width: 56,
                fixed: !0,
                hidden: (a.isReq !== 2 || ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1)) ? !0 : !1,
                editable: !1,
                formatter: function(x, y, z) {
                    if (a.isReq == 2 && $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) === -1 && $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) === -1 && z.uId != defaultPage.SYSTEM.uId && $.inArray(String(z.uId), defaultPage.SYSTEM.enableUserIds) !== -1) {
                        return '';
                    } else {
                        var quoteInfo =
                            '<div class="ml-5 mr-5 ovh" style="width:calc(100% + 10px)">'
                            + '<div class="pl5 pr5 lh12 silver es" title="' + z.quotePrice + '">' + z.quotePrice + '</div>'
                            + '<div class="pl5 pr5 mt5 ovh">'
                            + (z.isHasExpress == 1 ? '<span class="db fl lh12 green">包邮</span>' : '')
                            + (z.isHasInvoice == 1 ? '<span class="db fl lh12 green">带票</span>' : '')
                            + '</div></div>';
                        return quoteInfo;
                    }
                }
            }, {
                name: 'isPurchased',
                label: '采买',
                width: 70,
                fixed: !0,
                hidden: (a.isReq == 1 || a.id <= 0) ? !0 : !1,
                align: 'center',
                formatter: function(a, b, c) {
                    if (a == 0) {
                        if ((c.uId == 0 || c.uId == defaultPage.SYSTEM.uId || $.inArray(c.uId + '', defaultPage.SYSTEM.enableUserIds) === -1) && $.inArray('PC_UPDATE', defaultPage.SYSTEM.rights) !== -1) {
                            if (c.paidAmount > 0 && c.paidAmount < c.amount) {
                                return '<span class="db mt-2 lh18 yellow bd-yellow bbn b-r-3-top">未买全</span><span class="nobuy db mb-10 lh18 hover-yellow b-r-3-bottom" data-id="' + c.id + '" title="点击设置为不再买">设为不再买</span>';
                            } else {
                                return '<span class="db mt-2 lh18 red bd-red bbn b-r-3-top">未买</span><span class="nobuy db mb-10 lh18 hover-red b-r-3-bottom" data-id="' + c.id + '" title="点击设置为不买">设为不买</span>';
                            }
                        } else {
                            return '<span class="red">未买</span>';
                        }
                    } else if (a == 1) {
                        if ((c.uId == 0 || c.uId == defaultPage.SYSTEM.uId || $.inArray(c.uId + '', defaultPage.SYSTEM.enableUserIds) === -1) && $.inArray('PC_UPDATE', defaultPage.SYSTEM.rights) !== -1) {
                            if (c.paidAmount > 0 && c.paidAmount < c.amount) {
                                return '<span class="db mt-2 lh18 yellow bd-yellow bbn b-r-3-top">未买全</span><span class="nobuy db mb-10 lh18 hover-yellow b-r-3-bottom" data-id="' + c.id + '" title="点击设置为不再买">设为不再买</span>';
                            } else {
                                return '<span class="green">已买</span>';
                            }
                        } else {
                            return '<span class="green">已买</span>';
                        }
                    } else if (a == 2) {
                        if ((c.uId == 0 || c.uId == defaultPage.SYSTEM.uId || $.inArray(c.uId + '', defaultPage.SYSTEM.enableUserIds) === -1) && $.inArray('PC_UPDATE', defaultPage.SYSTEM.rights) !== -1) {
                            if (c.paidAmount > 0 && c.paidAmount < c.amount) {
                                return '<span class="db mt-2 lh18 gray bd-gray bbn b-r-3-top" title="原因：' + c.noPurchaseReason + '">不再买</span><span class="needbuy db mb-10 lh18 hover-gray b-r-3-bottom" data-id="' + c.id + '" title="点击设置为继续买">设为继续买</span>';
                            } else {
                                return '<span class="db mt-2 lh18 black bd-black bbn b-r-3-top" title="原因：' + c.noPurchaseReason + '">不买</span><span class="needbuy db mb-10 lh18 hover-black b-r-3-bottom" data-id="' + c.id + '" title="点击设置为要买">设为要买</span>';
                            }
                        } else {
                            return '<span class="black" title="原因：' + c.noPurchaseReason + '">不买</span>';
                        }
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'isRecInvoice',
                label: '发票',
                width: 112,
                fixed: !0,
                hidden: (a.isReq == 1 || a.id <= 0 || ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) == -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) == -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) == -1)) ? !0 : !1,
                align: 'center',
                formatter: function(a, b, c) {
                    if (a == 0) {
                        if ((c.uId == 0 || c.uId == defaultPage.SYSTEM.uId || $.inArray(c.uId + '', defaultPage.SYSTEM.enableUserIds) === -1) && $.inArray('PC_UPDATE', defaultPage.SYSTEM.rights) !== -1) {
                            var f = '<span class="ignoreReq-btn db fl ml1 w70 tc h28 lh28 hover-white b-r-3" title="点击申请白条入账" data-id="' + c.id + '">白条入账</span>';
                            if (c.isIgnoreInvoice == 2) {
                                f = '<span class="db fl ml1 w70 tc h28 lh28 hover-green b-r-3" title="管理员已同意白条入账">已同意白条</span>';
                            } else if (c.isIgnoreInvoice == 1) {
                                f = '<span class="ignoreReq-btn db fl ml1 w70 tc h28 lh28 hover-dark b-r-3" title="管理员已拒绝白条入账<br>拒绝原因：' + c.disagreeIgnoreReason + '<br>点击可再次申请白条入账" data-id="' + c.id + '">已拒绝白条</span>';
                            } else if (c.isIgnoreInvoice == 0 && c.ignoreInvoiceReason != '') {
                                f = '<span class="ignoreEdit-btn db fl ml1 w70 tc h28 lh28 hover-red b-r-3" title="您已申请过白条入账<br>申请原因：' + c.ignoreInvoiceReason + '<br>点击可重新编辑" data-id="' + c.id + '">白条待审批</span>';
                            }
                            return '<span class="invoice-btn db fl w30 h28 lh28 hover-red b-r-3" title="点击设为已收" data-id="' + c.id + '">未收</span>' + f;
                        } else {
                            return '<span class="db fl w30 h28 lh28 hover-red b-r-3">未收</span>';
                        }
                    } else if (a == 1) {
                        if ((c.uId == 0 || c.uId == defaultPage.SYSTEM.uId || $.inArray(c.uId + '', defaultPage.SYSTEM.enableUserIds) === -1) && $.inArray('PC_UPDATE', defaultPage.SYSTEM.rights) !== -1) {
                            return '<span class="invoice-btn db fl w30 h28 lh28 hover-green b-r-3" title="点击设为未收" data-id="' + c.id + '">已收</span>';
                        } else {
                            return '<span class="db fl w30 h28 lh28 hover-green b-r-3">已收</span>';
                        }
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'purFeedback',
                label: '采购反馈',
                width: 60,
                fixed: !0,
                hidden: (a.isReq == 1 || a.id <= 0) ? !0 : !1,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis',
                edittype: 'textarea'
            }, {
                name: 'hasRecQty',
                label: '已收货量',
                width: 60,
                fixed: !0,
                hidden: (a.isReq == 1 || a.id <= 0 || ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) == -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) == -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) == -1)) ? !0 : !1,
                editable: !0,
                formatter: function(a, b, c) {
                    return a > 0 ? a : '&nbsp;';
                }
            }, {
                name: 'purSuggestId',
                label: '采购建议',
                width: 66,
                fixed: !0,
                hidden: (a.isReq == 1 || a.id <= 0 || ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) == -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) == -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) == -1)) ? !0 : !1,
                align: 'center',
                classes: '',
                formatter: function(a, b, c) {
                    var d = a == 0 ? '提供建议' : '已建议';
                    var e = a == 0 ? 'hover-white' : 'hover-green';
                    var f = a == 0 ? '采购经理可对下属采购价格偏高的产品提供采购建议' : ($.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1 ? '点击编辑建议' : '点击查看建议');
                    var g = a == 0 ? 'purchase-suggest-add-btn' : ($.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1 ? 'purchase-suggest-edit-btn' : 'purchase-suggest-view-btn');
                    var btn = '<span class="' + g + ' db h28 lh28 b-r-3 ' + e + '" title="' + f + '" data-id="' + c.id + '" data-pursuggestid="' + a + '">' + d + '</span>';
                    return btn;
                }
            }, {
                name: 'salePriceNewReq',
                label: '销售单价',
                width: 100,
                fixed: !0,
                hidden: a.isReq == 1 ? !1 : !0,
                editable: a.isReq == 1 ? !0 : !1,
                formatter: function(x, y, z) {
                    if (a.isReq == 1 && z.limitPrice > 0) {
                        x = Number(z.limitPrice * 1.25).toFixed(2);
                    }
                    return x || '';
                }
            }, {
                name: 'paidAmount',
                label: '已付款',
                width: 56,
                fixed: !0,
                hidden: (a.id <= 0 || ($.inArray('PC_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('PAYMENT_ADD', defaultPage.SYSTEM.rights) === -1 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1)) ? !0 : !1,
                title: !0,
                editable: !1,
                classes: 'gray ui-ellipsis',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'uId',
                label: 'uId',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'transType',
                label: 'transType',
                width: 0,
                hidden: !0,
                editable: !1
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.rows', records: 'data.records', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: '100%',
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !1,
            cellsubmit: 'clientArray',
            triggerAdd: a.isReq == 2 ? !1 : !0,
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(gridData) {
                if (urlParam.id > 0) {
                    var rows = gridData.rows || gridData.data.rows;
                    var rowsLength = rows.length;
                    THISPAGE.newId = rowsLength + 1;
                    for (var i = 0; rowsLength > i; i++) {
                        var id = rows[i].id,
                            rowData = rows[i];
                        if (a.isReq == 1 && (a.uId != defaultPage.SYSTEM.uId || Number(rowData.paidAmount) > 0)) {
                            // 如果是采购申请单，但不是制单人或改产品已购买，设为不允许编辑
                            $('#grid tr#' + id).addClass('bg-rgba1');
                            $('#grid tr#' + id + ' .operating').html('已付款采购');
                            $('#grid').jqGrid('setCell', id, 'zid', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'name', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'spec', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'sku', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'brand', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'unit', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'qty', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'price', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'amount', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'buyUrl', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'myUrl', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'description1', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'description2', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'purFeedback', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'hasRecQty', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'salePriceNewReq', '', 'not-editable-cell');
                        } else if ((a.isReq != 1 && rowData.uId != 0 && rowData.uId != defaultPage.SYSTEM.uId)) {
                            // 如果不是申请单，且不是留空条目，且不是采购人
                            $('#grid tr#' + id).addClass('bg-rgba1');
                            $('#grid tr#' + id + ' .operating').html('已付款采购');
                            $('#grid').jqGrid('setCell', id, 'zid', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'name', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'spec', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'sku', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'brand', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'unit', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'buyUrl', '', 'not-editable-cell');
                            $('#grid').jqGrid('setCell', id, 'salePriceNewReq', '', 'not-editable-cell');
                            // 已离职采购人员，可由具有相同权限的采购同事代为编辑其之前的录入数据，如数量、单价、总价、采购链接等
                            if ($.inArray(rowData.uId + '', defaultPage.SYSTEM.enableUserIds) === -1 && $.inArray('PC_UPDATE', defaultPage.SYSTEM.rights) !== -1) {
                                if (Number(rowData.paidAmount) > 0) {
                                    $('#grid').jqGrid('setCell', id, 'qty', '', 'not-editable-cell');
                                    $('#grid').jqGrid('setCell', id, 'price', '', 'not-editable-cell');
                                    $('#grid').jqGrid('setCell', id, 'amount', '', 'not-editable-cell');
                                } else {
                                    $('#grid tr#' + id).removeClass('bg-rgba1');
                                }
                            } else {
                                $('#grid').jqGrid('setCell', id, 'qty', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'price', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'amount', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'myUrl', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'description1', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'description2', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'purFeedback', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'hasRecQty', '', 'not-editable-cell');
                            }
                        } else {
                            // 本人、系统留空条目
                            // 已付款的条目不允许再编辑数量、单价、总价、采购链接等
                            if (Number(rowData.paidAmount) > 0) {
                                // 设置不允许编辑
                                $('#grid tr#' + id).addClass('bg-rgba1');
                                $('#grid tr#' + id + ' .operating').html('已付款采购');
                                $('#grid').jqGrid('setCell', id, 'zid', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'name', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'spec', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'sku', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'brand', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'unit', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'qty', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'price', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'amount', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'buyUrl', '', 'not-editable-cell');
                                $('#grid').jqGrid('setCell', id, 'salePriceNewReq', '', 'not-editable-cell');
                            }
                        }
                    }
                }
                THISPAGE.calTotal();
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid'));
                }, 10);
            },
            onCellSelect: function(rowId, iCol, cellContent, event) {
                if (a.id > 0) {
                    var rowData = $('#grid').jqGrid('getRowData', rowId);
                    if (a.isReq == 1) { // 是申请单
                        if (a.uId != defaultPage.SYSTEM.uId || Number(rowData.paidAmount) > 0) { // 但不是制单人或该产品已购买
                            if (iCol == 13) { // 直接打开参考链接
                                rowData.buyUrl && rowData.buyUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.buyUrl));
                            } else if (iCol == 14) { // 直接打开采购链接
                                rowData.myUrl && rowData.myUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.myUrl));
                            }
                        }
                    } else { // 不是申请单
                        if (rowData.uId != 0 && rowData.uId != defaultPage.SYSTEM.uId) {
                            // 不是留空条目，也不是采购人
                            if (iCol == 13) { // 直接打开参考链接
                                rowData.buyUrl && rowData.buyUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.buyUrl));
                            } else if (iCol == 14 && $.inArray(rowData.uId + '', defaultPage.SYSTEM.enableUserIds) !== -1) { // 直接打开采购链接
                                rowData.myUrl && rowData.myUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.myUrl));
                            } else {
                                // 已离职采购人员，可由具有相同权限的采购同事代为编辑其之前的录入数据，如数量、单价、总价等信息
                                if ($.inArray(rowData.uId + '', defaultPage.SYSTEM.enableUserIds) === -1 && $.inArray('PC_UPDATE', defaultPage.SYSTEM.rights) !== -1) {
                                    if (Number(rowData.paidAmount) == 0) { // 保险起见，再查一次已付款信息，如果已付款，则不允许编辑数量、单价、总价等信息
                                        Public.ajaxPost('/Bill/InvPc/getPaidAmount', {
                                            id: rowId
                                        }, function(res) {
                                            if (res && 200 == res.status) {
                                                if (res.data && res.data.paidAmount && Number(res.data.paidAmount) > 0) {
                                                    $('#grid').jqGrid('setCell', rowId, 'qty', '', 'not-editable-cell');
                                                    $('#grid').jqGrid('setCell', rowId, 'price', '', 'not-editable-cell');
                                                    $('#grid').jqGrid('setCell', rowId, 'amount', '', 'not-editable-cell');
                                                    $('#grid').jqGrid('setCell', rowId, 'buyUrl', '', 'not-editable-cell');
                                                    $('#grid').jqGrid('setCell', rowId, 'paidAmount', res.data.paidAmount);
                                                    if (iCol == 14) { // 直接打开采购链接
                                                        rowData.myUrl && rowData.myUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.myUrl));
                                                    }
                                                }
                                            } else {
                                                $.dialog.tips(res.msg, 2);
                                            }
                                        });
                                    }
                                }
                            }
                        } else { // 本人条目、系统留空条目
                            if (Number(rowData.paidAmount) > 0) {
                                if (iCol == 13) { // 直接打开参考链接
                                    rowData.buyUrl && rowData.buyUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.buyUrl));
                                }
                            } else {
                                Public.ajaxPost('/Bill/InvPc/getPaidAmount', {
                                    id: rowId
                                }, function(res) {
                                    if (res && 200 == res.status) {
                                        if (res.data && res.data.paidAmount && Number(res.data.paidAmount) > 0) {
                                            // 设置不允许编辑
                                            $('#grid tr#' + rowId + ' .operating').html('已付款采购');
                                            $('#grid').jqGrid('setCell', rowId, 'zid', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'name', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'spec', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'sku', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'brand', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'unit', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'qty', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'price', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'amount', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'buyUrl', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'salePriceNewReq', '', 'not-editable-cell');
                                            $('#grid').jqGrid('setCell', rowId, 'paidAmount', res.data.paidAmount);
                                            if (iCol == 13) { // 直接打开参考链接
                                                rowData.buyUrl && rowData.buyUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.buyUrl));
                                            }
                                        } else {
                                            if (iCol == 13) { // 直接打开参考链接
                                                rowData.buyUrl && rowData.buyUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.buyUrl));
                                            }
                                        }
                                    } else {
                                        if (iCol == 13) { // 直接打开参考链接
                                            rowData.buyUrl && rowData.buyUrl.indexOf('http') === 0 && window.open(Public.fixUrl(rowData.buyUrl));
                                        }
                                        $.dialog.tips(res.msg, 2);
                                    }
                                });
                            }
                        }
                    }
                }
            },
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                THISPAGE.curID = rowId;
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                switch (cellName) {
                    case 'name':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if (cellValue && !rowData.zid) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                zid: iRow
                            });
                        }
                        break;
                    case 'qty':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if ($.isNumeric(cellValue) && $.isNumeric(rowData.price)) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                amount: Number(parseFloat(cellValue) * parseFloat(rowData.price)).toFixed(2)
                            });
                            THISPAGE.calTotal();
                        }
                        break;
                    case 'price':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if ($.isNumeric(cellValue) && $.isNumeric(rowData.qty)) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                amount: Number(parseFloat(cellValue) * parseFloat(rowData.qty)).toFixed(2)
                            });
                            THISPAGE.calTotal();
                        }
                        break;
                    case 'amount':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if ($.isNumeric(cellValue) && $.isNumeric(rowData.qty)) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                price: Number(parseFloat(cellValue) / parseFloat(rowData.qty)).toFixed(2)
                            });
                            THISPAGE.calTotal();
                        }
                        break;
                    default:
                        break;
                }
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initEvent: function() {
        Business.billsEvent(this, 'purchaseContract');
        laydate.render({
            elem: "#billDate, #endDate",
        });
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            var data = THISPAGE.getPostData();
            if (data) {
                if (
                    (data.isReq == 1 && Business.verifyRight('SC_ADD')) ||
                    (data.isReq != 1 && Business.verifyRight('PC_ADD'))
                ) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/InvPc/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            if (data.isReq == 1) {
                                parent.frames['purchase-purchaseContractReq'] && parent.frames['purchase-purchaseContractReq'].$('#search').trigger('click');
                                parent.frames['sales-salesContractReqList'] && parent.frames['sales-salesContractReqList'].$('#search').trigger('click');
                            } else if (data.isFill == 1) {
                                parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                            } else {
                                parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                                parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                            }
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('新增失败！' + res.msg, 2);
                        }
                    });
                }
            } else {
                $.dialog.tips('数据提取失败！', 2);
            }
        });
        $('.wrapper').on('click', '#draft', function(a) {
            a.preventDefault();
            var data = THISPAGE.getPostData();
            if (data) {
                if (
                    (data.isReq == 1 && Business.verifyRight('SC_ADD')) ||
                    (data.isReq != 1 && Business.verifyRight('PC_ADD'))
                ) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 1;
                    Public.ajaxPost('/Bill/InvPc/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            if (data.isReq == 1) {
                                parent.frames['purchase-purchaseContractReq'] && parent.frames['purchase-purchaseContractReq'].$('#search').trigger('click');
                                parent.frames['sales-salesContractReqList'] && parent.frames['sales-salesContractReqList'].$('#search').trigger('click');
                            } else if (data.isFill == 1) {
                                parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                            } else {
                                parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                                parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                            }
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('新增失败！' + res.msg, 2);
                        }
                    });
                }
            } else {
                $.dialog.tips('数据提取失败！', 2);
            }
        });
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            var data = THISPAGE.getPostData();
            if (data) {
                if (
                    (data.isReq == 1 && Business.verifyRight('SC_UPDATE')) ||
                    (data.isReq != 1 && Business.verifyRight('PC_UPDATE'))
                ) {
                    if (data.isReq == 0 && data.uId != defaultPage.SYSTEM.uId) {
                        $.dialog.tips('不能编辑别人的手工单据！', 2);
                        return false;
                    }
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = originalData.isDraft;
                    Public.ajaxPost('/Bill/InvPc/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            if (res.data && res.data.errZids.length > 0) {
                                parent.parent.$.dialog.alert('部分产品更新失败：自编序号为（' + res.data.errZids + '）的产品已被有效付款，禁止编辑单价总价！');
                            } else {
                                parent.parent.$.dialog.tips('更新成功！');
                            }
                            if (data.isReq == 1) {
                                parent.frames['purchase-purchaseContractReq'] && parent.frames['purchase-purchaseContractReq'].$('#search').trigger('click');
                            } else if (data.isFill == 1) {
                                parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                            } else {
                                parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                                parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                            }
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                }
            } else {
                $.dialog.tips('数据提取失败！', 2);
            }
        });
        $('.wrapper').on('click', '#final', function(a) {
            a.preventDefault();
            var data = THISPAGE.getPostData();
            if (data) {
                if (
                    (data.isReq == 1 && Business.verifyRight('SC_UPDATE')) ||
                    (data.isReq != 1 && Business.verifyRight('PC_UPDATE'))
                ) {
                    if (data.isReq == 0 && data.uId != defaultPage.SYSTEM.uId) {
                        $.dialog.tips('不能编辑别人的手工单据！', 2);
                        return false;
                    }
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/InvPc/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            if (res.data && res.data.errZids.length > 0) {
                                parent.parent.$.dialog.alert('部分产品更新失败：自编序号为（' + res.data.errZids + '）的产品已被有效付款，禁止编辑单价总价！');
                            } else {
                                parent.parent.$.dialog.tips('更新成功！');
                            }
                            if (data.isReq == 1) {
                                parent.frames['purchase-purchaseContractReq'] && parent.frames['purchase-purchaseContractReq'].$('#search').trigger('click');
                            } else if (data.isFill == 1) {
                                parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                            } else {
                                parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                                parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                            }
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                }
            } else {
                $.dialog.tips('数据提取失败！', 2);
            }
        });
        $('.wrapper').on('click', '#print', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_PRINT')) {
                THISPAGE.preview(1);
            }
        });
        $('.wrapper').on('click', '#copy', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                originalData.id && parent.tab.addTabItem({
                    tabid: 'purchase-purchaseContract' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单（新建）',
                    url: '/Uri/purchaseContract_manage?copyFromContractPur&id=' + originalData.id + '&tabId=purchase-purchaseContract' + newTabId
                });
            }
        });
        $('.wrapper').on('click', '#check', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('PC_CHECK')) {
                originalData.id && Public.ajaxPost('/Bill/InvPc/check', {
                    id: originalData.id,
                    checkId: defaultPage.SYSTEM.uId,
                    checkName: defaultPage.SYSTEM.uName,
                    billNo: originalData.billNo,
                    transType: originalData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('审核成功！');
                        originalData.checkId = defaultPage.SYSTEM.uId;
                        originalData.checkName = defaultPage.SYSTEM.uName;
                        $('#mark').addClass('bill-has-checked');
                        $('#check').after(btn_uncheck).remove();
                        $('#checkName').html(defaultPage.SYSTEM.uName);
                        parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                        parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#uncheck', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('PC_UNCHECK')) {
                originalData.id && Public.ajaxPost('/Bill/InvPc/check', {
                    id: originalData.id,
                    checkId: 0,
                    checkName: '',
                    billNo: originalData.billNo,
                    transType: originalData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('反审核成功！');
                        originalData.checkId = 0;
                        originalData.checkName = '';
                        $('#mark').removeClass('bill-has-checked');
                        $('#uncheck').after(btn_check).remove();
                        $('#checkName').html('');
                        parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                        parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('反审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvPc/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcmoney1', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PAYMENT_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: originalData.srcMoneyId1
                    }, function(res) {
                        if (res && 200 == res.status) {
                            originalData.moneyStatus1 = 0;
                            originalData.srcMoneyId1 = 0;
                            originalData.srcMoneyNo1 = '';
                            $('#delsrcmoney1').remove();
                            $.dialog.tips('删除成功！');
                            parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                            parent.frames['money-payment'] && parent.frames['money-payment'].$('#search').trigger('click');
                            parent.frames['money-payment' + originalData.srcMoneyId1] && parent.tab.removeTabItem('money-payment' + originalData.srcMoneyId1);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcmoney2', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: originalData.srcMoneyId2
                    }, function(res) {
                        if (res && 200 == res.status) {
                            originalData.moneyStatus2 = 0;
                            originalData.srcMoneyId2 = 0;
                            originalData.srcMoneyNo2 = '';
                            $('#delsrcmoney2').remove();
                            $.dialog.tips('删除成功！');
                            parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                            parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                            parent.frames['money-receipt' + originalData.srcMoneyId2] && parent.tab.removeTabItem('money-receipt' + originalData.srcMoneyId2);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#breakshow', function(a) {
            a.preventDefault();
            if (breakShow) {
                breakShow = !1;
                $(this).children('span').text('显示详细表格');
                $('.ui-jqgrid-bdiv td').removeClass('ui-break').addClass('ui-ellipsis');
            } else {
                breakShow = !0;
                $(this).children('span').text('显示缩略表格');
                $('.ui-jqgrid-bdiv td').removeClass('ui-ellipsis').addClass('ui-break');
            }
        });
        $('.wrapper').on('click', '#showQuoteHis', function(a) {
            a.preventDefault();
            if ($.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1 || $.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) !== -1) {
                $.dialog({
                    title: '特采报价记录',
                    content: 'url:/Uri/purchaseContractReq_quote_info',
                    data: {
                        id: originalData.id
                    },
                    width: $(window).width() - 10,
                    height: $(window).height() - 100,
                    left: 5,
                    top: 5,
                    max: !0,
                    min: !0,
                    lock: !0,
                    cache: !1
                });
            } else {
                $.dialog.tips('对不起，权限不足！', 2);
            }
        });
        $('.wrapper').on('click', '#exportGridAll', function(a) {
            if (Business.verifyRight('SYS_ADMIN')) {
                var billNo = $('#billNo').val();
                var href = '/Bill/InvPc/exportInfoGridAll?id=' + originalData.id + '&billNo=' + billNo;
                $(this).attr('href', href);
            }
        });
        $('.wrapper').on('click', '#exportGrid', function(a) {
            if (Business.verifyRight('PC_EXPORT')) {
                var billNo = $('#billNo').val();
                var uId = originalData.isReq == 2 ? combo1.getValue() : 0;
                var href = '/Bill/InvPc/exportInfoGrid?id=' + originalData.id + '&billNo=' + billNo + '&uId=' + uId;
                $(this).attr('href', href);
            }
        });
        $('.wrapper').on('click', '#printGrid', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_PRINT')) {
                THISPAGE.preview(2);
            }
        });
        $('.wrapper').on('click', '.nobuy', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            $.dialog.prompt('<span class="f14">请输入不买/不再买的原因。', function(inputValue) {
                Public.ajaxPost('/Bill/InvPc/updateIsPurchased', {
                    id: id,
                    isPurchased: 2,
                    noPurchaseReason: inputValue
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('采买状态设置成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                        parent.frames['purchase-purchaseContractLost'] && parent.frames['purchase-purchaseContractLost'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('采买状态设置失败！' + res.msg, 2);
                    }
                });
            });
        });
        $('.wrapper').on('click', '.needbuy', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            Public.ajaxPost('/Bill/InvPc/updateIsPurchased', {
                id: id,
                isPurchased: 0
            }, function(res) {
                if (res && 200 == res.status) {
                    $.dialog.tips('采买状态设置成功！');
                    $('#grid').jqGrid('setRowData', id, res.data);
                    parent.frames['purchase-purchaseContractLost'] && parent.frames['purchase-purchaseContractLost'].$('#search').trigger('click');
                } else {
                    $.dialog.tips('采买状态设置失败！' + res.msg, 2);
                }
            });
        });
        $('.wrapper').on('click', '.invoice-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            Public.ajaxPost('/Bill/InvPc/updateIsRecInvoice', {
                id: id,
                isRecInvoice: $(this).hasClass('hover-red') ? 1 : 0
            }, function(res) {
                if (res && 200 == res.status) {
                    $.dialog.tips('发票状态设置成功！');
                    $('#grid').jqGrid('setCell', id, 'isRecInvoice', res.data);
                } else {
                    $.dialog.tips('发票状态设置失败！' + res.msg, 2);
                }
            });
        });
        $('.grid-wrap').on('click', '.ignoreReq-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id) {
                    if (rowData.uName == defaultPage.SYSTEM.uName) {
                        $.dialog({
                            title: '申请白条入账',
                            content: 'url:/Uri/invoice_miss_ignore',
                            data: {
                                oper: 'add',
                                billType: 'contract_info',
                                type: 'ignoreReq',
                                id: id,
                                callback: THISPAGE.ignoreCallback
                            },
                            width: 400,
                            height: 260,
                            max: !1,
                            min: !1,
                            cache: !1,
                            lock: !0
                        });
                    } else {
                        $.dialog.tips('不能申请别人的采购项白条入账！', 2);
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.ignoreEdit-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id) {
                    if (rowData.uName == defaultPage.SYSTEM.uName) {
                        $.dialog({
                            title: '编辑白条入账',
                            content: 'url:/Uri/invoice_miss_ignore',
                            data: {
                                oper: 'edit',
                                billType: 'contract_info',
                                type: 'ignoreReq',
                                id: id,
                                callback: THISPAGE.ignoreCallback
                            },
                            width: 400,
                            height: 260,
                            max: !1,
                            min: !1,
                            cache: !1,
                            lock: !0
                        });
                    } else {
                        $.dialog.tips('不能编辑别人的采购项白条入账！', 2);
                    }
                }
            }
        });
        $('.wrapper').on('click', '.purchase-suggest-add-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PUR_MANAGER')) {
                var id = parseInt($(this).data('id'));
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    $.dialog({
                        title: '新增采购建议',
                        content: 'url:/Uri/purchase_suggest_manage',
                        data: {
                            oper: 'add',
                            rowData: {
                                billId: originalData.id,
                                billNo: originalData.billNo,
                                transType: originalData.transType,
                                infoId: id,
                                zid: rowData.zid,
                                no: '',
                                name: rowData.name,
                                spec: rowData.spec,
                                sku: rowData.sku,
                                brand: rowData.brand,
                                unit: rowData.unit,
                                qty: parseFloat(rowData.qty),
                                purPrice: parseFloat(rowData.price),
                                purId: parseInt(rowData.uId),
                                purName: rowData.uName
                            },
                            callback: THISPAGE.callback
                        },
                        width: 1250,
                        height: 190,
                        max: !1,
                        min: !1,
                        cache: !1,
                        lock: !0
                    });
                }
            }
        });
        $('.wrapper').on('click', '.purchase-suggest-edit-btn', function(a) {
            a.preventDefault();
            var id = parseInt($(this).data('id'));
            var purSuggestId = parseInt($(this).data('pursuggestid'));
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData && purSuggestId > 0) {
                $.dialog({
                    title: '编辑采购建议',
                    content: 'url:/Uri/purchase_suggest_manage',
                    data: {
                        oper: 'edit',
                        purSuggestId: purSuggestId,
                        newData: {
                            infoId: id,
                            zid: rowData.zid,
                            no: '',
                            name: rowData.name,
                            spec: rowData.spec,
                            sku: rowData.sku,
                            brand: rowData.brand,
                            unit: rowData.unit,
                            qty: parseFloat(rowData.qty),
                            purPrice: parseFloat(rowData.price),
                            purId: parseInt(rowData.uId),
                            purName: rowData.uName
                        },
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 190,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.wrapper').on('click', '.purchase-suggest-view-btn', function(a) {
            a.preventDefault();
            var purSuggestId = parseInt($(this).data('pursuggestid'));
            if (purSuggestId > 0) {
                $.dialog({
                    title: '查看采购建议',
                    content: 'url:/Uri/purchase_suggest_manage',
                    data: {
                        oper: 'edit',
                        purSuggestId: purSuggestId
                    },
                    width: 1250,
                    height: 190,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0,
                    disEdit: !0,
                    button: [{
                        id: 'cancel',
                        name: '关闭'
                    }]
                });
            }
        });
        $('.wrapper').on('click', '#desBtn', function(a) {
            a.preventDefault();
            var data = THISPAGE.getPostData();
            if (data) {
                if (
                    (data.isReq == 1 && Business.verifyRight('SC_UPDATE')) ||
                    (data.isReq != 1 && Business.verifyRight('PC_UPDATE'))
                ) {
                    if (data.isReq == 0 && data.uId != defaultPage.SYSTEM.uId) {
                        $.dialog.tips('不能编辑别人的手工单据！', 2);
                        return false;
                    }
                    Public.ajaxPost('/Bill/InvPc/updatedes', {
                        id: data.id,
                        newDes: data.description
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('备注保存成功！');
                            parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('备注保存失败！' + res.msg, 2);
                        }
                    });
                }
            } else {
                $.dialog.tips('数据提取失败！', 2);
            }
        });
        if (originalData.isReq == 1) {
            $('#add-excel').uploader({
                action: '/Bill/InvPc/uploadReqExcel',
                mode: 'html5',
                name: 'file',
                formData: {},
                multiple: false,
                auto: true,
                debug: false,
                preview: false,
                showQueue: '#queue',
                fileSizeLimit: '2G',
                fileTypeDesc: '选择文件',
                fileTypeExts: 'csv,xls,xlsx',
                onSuccess: function(e) {
                    isNewImportExcel = !0;
                    var data = JSON.parse(e.data);
                    $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
                        data: data.data.entries
                    }).trigger('reloadGrid');
                    THISPAGE.newId = data.data.entries.length + 1;
                }
            });
        }
        $('#upFile').uploader({
            action: '/Bill/InvPc/uploadfile',
            mode: 'html5',
            name: 'file',
            formData: {},
            multiple: false,
            auto: true,
            debug: false,
            preview: false,
            showQueue: '#queue',
            fileSizeLimit: '2G',
            fileTypeDesc: '选择文件',
            fileTypeExts: 'jpg,gif,png,csv,xls,xlsx,doc,docx,xps,pdf,zip,rar,iso,7z',
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.file) {
                    if (!originalData.files) {
                        originalData.files = [];
                        originalData.files[0] = data.file;
                    } else {
                        originalData.files.unshift(data.file);
                    }
                    THISPAGE.addFiles(originalData.files);
                }
            },
            onError: function(e) {
                e.message && $.dialog.tips('上传失败！' + e.message, 2);
            },
            onCancel: function(e) {
                e.name && $.dialog.tips('已取消文件' + e.name + '上传！', 2);
            },
            onAllComplete: function() {
                $('.upload-queue .queue ul').html('上传成功！').addClass('tc');
            }
        });
        $('.upload-el').on({
            mouseover: function() {
                if ($(this).prev().hasClass('ui-btn')) {
                    $(this).prev().addClass('ui-btn-hover');
                } else {
                    $(this).prev().addClass('black');
                }
            },
            mouseleave: function() {
                if ($(this).prev().hasClass('ui-btn')) {
                    $(this).prev().removeClass('ui-btn-hover');
                } else {
                    $(this).prev().removeClass('black');
                }
            }
        });
        $('#files').on('click', '.fileRename', function() {
            if (Business.verifyRight('PC_UPDATE')) {
                $('.manage-wrapper .renameInput').addClass('dn');
                $('.manage-wrapper .renameSumbit').addClass('dn');
                $('.manage-wrapper .renameCancel').addClass('dn');
                $(this).parent().children('.renameInput').removeClass('dn');
                $(this).parent().children('.renameSumbit').removeClass('dn');
                $(this).parent().children('.renameCancel').removeClass('dn');
            }
        });
        $('#files').on('click', '.renameSumbit', function() {
            var newName = $(this).parent().children('.renameInput').val();
            if (newName) {
                var key = $(this).closest('.file-line').data('key');
                originalData.files[key].name = newName;
                THISPAGE.addFiles(originalData.files);
            }
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameCancel').addClass('dn');
        });
        $('#files').on('click', '.renameCancel', function() {
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameSumbit').addClass('dn');
        });
        $('#files').on('click', '.fileDel', function(a) {
            a.stopPropagation();
            if (Business.verifyRight('PC_UPDATE')) {
                var upName = $(this).parent().data('upname');
                if (upName !== defaultPage.SYSTEM.uName) {
                    $.dialog.tips('禁止删除别人上传的文件！', 2);
                } else {
                    $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                        var key = $(this).closest('.file-line').data('key');
                        originalData.files.splice(key, 1) && THISPAGE.addFiles(originalData.files);
                    });
                }
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
        $(window).on('scroll', function() {
            var nTop = $(".grid-wrap").offset().top - 100;
            var scrTop = $(this).scrollTop();
            if (scrTop >= nTop) {
                $('.ui-jqgrid .ui-jqgrid-hdiv').css({'position': 'fixed', 'z-index': 2, 'padding': '0 16px', 'top': '50px', 'left': 0, 'right': 0});
            } else {
                $('.ui-jqgrid .ui-jqgrid-hdiv').css({'position': 'relative', 'padding': 0, 'top': 'auto', 'left': 0, 'right': 0});
            }
        });
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '" data-upname="' + v.upName + '">' +
                '<a href="/data/upfile/contract/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), totalQty = 0, totalAmount = 0, totalSaleAmount = 0, d = 0, e = a.length; e > d; d++) {
            var f = a[d];
            var g = $('#grid').jqGrid('getRowData', f);
            g.qty && (totalQty += parseFloat(g.qty));
            g.amount && (totalAmount += parseFloat(g.amount));
            g.saleAmount && (totalSaleAmount += parseFloat(g.saleAmount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            qty: totalQty,
            amount: (originalData.isReq == 2 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) !== -1) ? totalAmount : '',
            saleAmount: (originalData.isReq == 2 && $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) !== -1) ? Public.numToCurrency(totalSaleAmount) : '0.00'
        });
    },
    getEntriesData() {
        for (var zids = [], entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);

            //处理容易出问题的特殊字符
            rowData.zid = rowData.zid.replace(/"|'|{|}/g, '”');
            rowData.name = rowData.name.replace(/"|'|{|}/g, '”');
            rowData.spec = rowData.spec.replace(/"|'|{|}/g, '”');
            rowData.sku = rowData.sku.replace(/"|'|{|}/g, '”');
            rowData.brand = rowData.brand.replace(/"|'|{|}/g, '”');
            rowData.unit = rowData.unit.replace(/"|'|{|}/g, '”');
            rowData.buyUrl = rowData.buyUrl.replace(/"|'|{|}|%/g, '');
            rowData.myUrl = rowData.myUrl.replace(/"|'|{|}|%/g, '');
            rowData.description1 = rowData.description1.replace(/"|'|{|}/g, '”');
            rowData.description2 = rowData.description2.replace(/"|'|{|}/g, '”');

            if ('' == rowData.name) {
                if (rowData.qty != 0) {
                    $.dialog.tips('第 ' + (i + 1) + ' 行产品填入了产品数量时，产品名称不能为空！', 2);
                    return !1;
                }
            } else {
                if (rowData.zid == 0 || rowData.zid == '') {
                    $.dialog.tips('第 ' + (i + 1) + ' 行产品的自编序号不允许为0或空！', 2);
                    return !1;
                }
                if (rowData.qty == 0 || rowData.qty == '') {
                    $.dialog.tips('第 ' + (i + 1) + ' 行自编序号 ' + rowData.zid + ' 的产品的产品数量不允许为0或空！', 2);
                    return !1;
                }
                if ((urlParam.newReq || urlParam.modifyReq) && (rowData.salePriceNewReq == 0 || rowData.salePriceNewReq == '')) {
                    $.dialog.tips('第 ' + (i + 1) + ' 行自编序号 ' + rowData.zid + ' 的产品的销售单价不允许为零或空！', 2);
                    return !1;
                }
                if (rowData.buyUrl) {
                    if (rowData.buyUrl.indexOf('http') !== 0 && originalData.isReq == 1) {
                        $.dialog.tips('第 ' + (i + 1) + ' 行自编序号 ' + rowData.zid + ' 的产品的参考采购链接格式错误！请只填入真实链接或留空。无真实链接但有其他提醒信息，则请填入备注里。', 2);
                        return !1;
                    } else if ((rowData.buyUrl.indexOf('//cart.') !== -1 || rowData.buyUrl.indexOf('//trade.') !== -1) && originalData.isReq == 1) {
                        $.dialog.tips('第 ' + (i + 1) + ' 行自编序号 ' + rowData.zid + ' 的产品的参考采购链接错误！不能乱填别人看不见的地址！', 2);
                        return !1;
                    }
                }
                if (rowData.myUrl) {
                    if (rowData.myUrl.indexOf('http') !== 0 && rowData.uId == defaultPage.SYSTEM.uId) {
                        $.dialog.tips('第 ' + (i + 1) + ' 行自编序号 ' + rowData.zid + ' 的产品的实际采购链接格式错误！请只填入真实链接或留空。无真实链接但有其他提醒信息，则请填入备注里。', 2);
                        return !1;
                    } else if ((rowData.myUrl.indexOf('//cart.') !== -1 || rowData.myUrl.indexOf('//trade.') !== -1) && rowData.uId == defaultPage.SYSTEM.uId) {
                        $.dialog.tips('第 ' + (i + 1) + ' 行自编序号 ' + rowData.zid + ' 的产品的实际采购链接错误！不能为别人看不见的地址！', 2);
                        return !1;
                    }
                }
                var data = {
                    id: rowData.id || -1,
                    zid: rowData.zid || '',
                    no: '',
                    name: rowData.name || '',
                    spec: rowData.spec || '',
                    sku: rowData.sku || '',
                    brand: rowData.brand || '',
                    unit: rowData.unit || '',
                    qty: rowData.qty,
                    price: rowData.price,
                    amount: rowData.amount,
                    buyUrl: rowData.buyUrl || '',
                    myUrl: rowData.myUrl || '',
                    description1: rowData.description1 || '',
                    description2: rowData.description2 || '',
                    hasRecQty: rowData.hasRecQty || 0,
                    purFeedback: rowData.purFeedback || '',
                    limitPrice: originalData.isReq == 1 ? (rowData.salePriceNewReq ? rowData.salePriceNewReq * 0.8 : 0) : (rowData.limitPrice || 0),
                    uId: rowData.uId || defaultPage.SYSTEM.uId,
                    uName: rowData.uName || defaultPage.SYSTEM.uName
                };
                entriesData.push(data);
                zids.push(rowData.zid);
            }
        }
        // 提取重复自编序号并处理
        let repeatZids = [];
        for (let k in zids) {
            if (zids.indexOf(zids[k]) !== zids.lastIndexOf(zids[k]) && repeatZids.indexOf(zids[k]) === -1) {
                repeatZids.push(zids[k]);
            }
        }
        if (repeatZids.length > 0) {
            $.dialog.tips('自编序号：' + repeatZids.toString() + ' 重复！', 2);
            return !1;
        }
        return entriesData;
    },
    getPostData: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var billDate = $.trim($('#billDate').val());
        var endDate = $.trim($('#endDate').val());
        if (endDate <= billDate) {
            $.dialog.tips('交货限期不合理！请录入合理的交货限期！', 2);
            return !1;
        }
        if (!urlParam.copyFromContractSale && !urlParam.modifyReq && !urlParam.newReq && !urlParam.copyFromContractPur) {
            var amount = Number($.trim($('#amount').val()));
            if (amount == 0) {
                $.dialog.tips('应付金额不能为空或0！', 2);
                return !1;
            }
        }
        var entriesData = THISPAGE.getEntriesData();
        var contactInput = $('#contact').find('input');
        var ourselfInput = $('#ourself').find('input');
        var contactData = $('#contact').data('contactData');
        var ourselfData = $('#ourself').data('ourselfData');
        if ('' === contactInput.val()) {
            $('#contact').removeData('contactData');
            contactCombo.active = !0;
            contactCombo.doQuery();
            contactCombo.input.focus();
            $.dialog.tips('请选择卖方(对方)！', 2);
            return !1;
        }
        if (!contactData || contactData.id < 0) {
            setTimeout(function() {
                contactInput.focus().select();
            }, 10);
            $.dialog.tips('当前卖方(对方)不存在！', 2);
            return !1;
        }
        if ('' === ourselfInput.val()) {
            $('#ourself').removeData('ourselfData');
            ourselfCombo.active = !0;
            ourselfCombo.doQuery();
            ourselfCombo.input.focus();
            $.dialog.tips('请选择买方(我方)！', 2);
            return !1;
        }
        if (!ourselfData || ourselfData.id < 0) {
            setTimeout(function() {
                ourselfInput.focus().select();
            }, 10);
            $.dialog.tips('当前买方(我方)不存在！', 2);
            return !1;
        }
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            var data = {
                srcPurReqId: originalData.srcPurReqId || 0,
                isNewImportExcel: isNewImportExcel,
                id: originalData.id,
                billNo: $.trim($('#billNo').val()),
                billType: 'PUR',
                transType: 101111,
                amount: $.trim($('#amount').val()),
                totalQty: $('#grid').jqGrid('footerData', 'get').qty.replace(/,/g, ''),
                contactId: contactData.id,
                contactName: contactData.name,
                sId: ourselfData.id,
                sName: ourselfData.name,
                billDate: $.trim($('#billDate').val()),
                endDate: $.trim($('#endDate').val()),
                entries: entriesData,
                description: $.trim($('#description').val()).replace(/"|'|{|}/g, '”'),
                files: originalData.files ? JSON.stringify(originalData.files) : '',
                isReq: originalData.isReq || 0,
                reqNo: originalData.isReq ? originalData.billNo : '',
                isFill: urlParam.isFill || 0,
                uId: originalData.uId || defaultPage.SYSTEM.uId
            };
            return data;
        } else {
            return $.dialog.tips('产品信息不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
        }
    },
    preview: function(oper) {
        switch (oper) {
            case 1:
                $('#contractType').attr('value', '特殊采购单').removeClass('dn');
                $('#notice, .ui-combo-wrap .trigger, .ui-icon-ellipsis, .ui-icon-triangle-1-s, .fs-arrow, #desBtn, .upfile-title, .upfile-con, #excel-btns').hide();
                $('label, .ui-input, .ui-combo-wrap, .fs-wrap, .con-footer ul li span').css({'border': '0 none', 'border-bottom': '1px solid #ccc'});
                $('.wrapper').find('input').each(function() {
                    var value = $(this).val();
                    $(this).attr('value', value);
                });
                $('#files').css({'width': '100%', 'height': 'auto'});
                $('.file-line').css({'margin': '5px 10px'});
                $('#grid').hideCol('operate').hideCol('id').hideCol('goods').trigger('reloadGrid');
                $('#grid td').removeClass('ui-ellipsis').css({
                    'height': 'auto',
                    'line-height': '18px',
                    'word-wrap': 'break-word',
                    'word-break': 'break-all',
                    'white-space': 'normal'
                });
                break;
            case 2:
                $('#gridNo').removeClass('dn');
                $('#excel-btns').hide();
                $('#grid').hideCol('operate').hideCol('id').hideCol('goods').hideCol('no').hideCol('buyUrl').hideCol('description1').hideCol('hasRecQty').trigger('reloadGrid');
                $('#grid td').removeClass('ui-ellipsis').css({
                    'height': 'auto',
                    'line-height': '18px',
                    'word-wrap': 'break-word',
                    'word-break': 'break-all',
                    'white-space': 'normal'
                });
                break;
        }
        /* 获取处理后的html代码 */
        var bdhtml = window.document.body.innerHTML;
        /* 设置打印开始区域 */
        var sprnstr = '<!--startprint' + oper + '-->';
        /* 设置打印结束区域 */
        var eprnstr = '<!--endprint' + oper + '-->';
        /* 从开始代码向后取html */
        var prnhtml = bdhtml.substring(bdhtml.indexOf(sprnstr) + 17 + oper.toString().length);
        /* 从结束代码向前取html */
        prnhtml = prnhtml.substring(0, prnhtml.indexOf(eprnstr));
        window.document.body.innerHTML = prnhtml;
        window.print();
        /* 将页面恢复成打印前状态 */
        window.location.reload();
    },
    callback: function(a, b, c, d) {
        $('#grid').jqGrid('setRowData', a.infoId, {purSuggestId: a.id}), c && c.api.close();
    },
    ignoreCallback: function(a, b, c, d) {
        $('#grid').jqGrid('setRowData', a.id, a);
        c && c.api.close();
    }
};
$(function() {
    if (urlParam.turnByPurReq) {
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        originalData = {
            srcPurReqId: parseInt(openerData.id),
            id: -1,
            isReq: 0,
            transType: 101111,
            entries: [],
            totalQty: 0,
            totalAmount: 0,
            description: openerData.title
        };
        THISPAGE.init(originalData);
    } else if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/InvPc/query', {
            id: urlParam.id,
            infoIds: urlParam.infoIds || '',
            reqType: urlParam.reqType || ''
        }, function(res) {
            if (res && 200 == res.status) {
                originalData = res.data;
                if (urlParam.copyFromContractSale) {
                    originalData.srcPurReqId = 0;
                    originalData.id = -1;
                    originalData.transType = 101111;
                    originalData.contactId = -1;
                    originalData.amount = '0';
                    originalData.files = '';
                    originalData.description = '原销售单备注：' + originalData.description + "\r\n" + '采购申请备注：';
                    let goodsArr = [];
                    for (let i = 0; i < originalData.entries.length; i++) {
                        if (originalData.entries[i].no != '') {
                            originalData.entries[i].name = '【' + originalData.entries[i].no + '】' + originalData.entries[i].name;
                        }
                        originalData.entries[i].limitPrice = Number(originalData.entries[i].price * 0.8).toFixed(2); // 根据销售价计算得出采购参考单价
                        originalData.entries[i].price = '&nbsp;';
                        originalData.entries[i].amount = '&nbsp;';
                        goodsArr.push(originalData.entries[i]);
                    }
                    originalData.entries = goodsArr;
                    originalData.isReq = 1;
                    $('#reqNotice').removeClass('dn');
                    $('#amount').addClass('ui-input-dis').attr('disabled', true);
                    $('#goodsbtns').hide();
                    btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>提交</span></a>';
                } else if (urlParam.modifyReq) {
                    $('#reqNotice').removeClass('dn');
                    $('#amount').addClass('ui-input-dis').attr('disabled', true);
                    $('#goodsbtns').hide();
                    btn_print = btn_copy = '';
                } else if (urlParam.copyFromContractPur) {
                    originalData.srcPurReqId = 0;
                    originalData.id = -1;
                    originalData.transType = 101111;
                    $.dialog.alert('此为通过复制生成的新单，请按需编辑确认各项信息，检查无误后方可保存！');
                }
                THISPAGE.init(originalData);
                $('.wrapper > #div-lock').remove();
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    } else {
        originalData = {
            srcPurReqId: 0,
            id: -1,
            isReq: 0,
            transType: 101111,
            entries: [],
            totalQty: 0,
            totalAmount: 0,
            description: ''
        };
        if (urlParam.newReq) {
            originalData.srcPurReqId = 0;
            originalData.isReq = 1;
            originalData.contactId = -1;
            originalData.amount = '0';
            originalData.files = '';
            $('#reqNotice').removeClass('dn');
            $('#amount').addClass('ui-input-dis').attr('disabled', true);
            $('#goodsbtns').hide();
            $('#goodsbtns2').show();
            btn_print = btn_copy = '';
        }
        THISPAGE.init(originalData);
    }
});
