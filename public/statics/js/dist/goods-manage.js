var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var qtyPlaces = defaultPage.SYSTEM.qtyPlaces;
var pricePlaces = defaultPage.SYSTEM.pricePlaces;
var amountPlaces = defaultPage.SYSTEM.amountPlaces;
var categoryTree;
var brandCombo;
var unitCombo;
var storageCombo;
var locationCombo;
var disableCombo;
var proofreadCombo;
var serNameCombo;
var rowData = {};
var curRow;
var curCol;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Goods/query', {
                id: cRowId
            }, function(res) {
                if (200 === res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initGrid(rowData.sonGoods);
                    THISPAGE.initEvent();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initGrid([]);
            THISPAGE.initEvent();
            THISPAGE.initValidator();
        }
    },
    initBtns: function() {
        api.button({
            id: 'copyAllGoodsInfo',
            name: '从已有产品快速复制（替换全部信息）',
            defClass: 'ui_state_default',
            callback: function() {
                return THISPAGE.copyGoodsInfo('all'), !1;
            }
        }, {
            id: 'copySonGoodsInfo',
            name: '从已有产品快速复制（替换子产品信息）',
            defClass: 'ui_state_default',
            callback: function() {
                return THISPAGE.copyGoodsInfo('son'), !1;
            }
        }, {
            id: 'pullSonGoodsInfo',
            name: '从已有产品快速复制（追加子产品信息）',
            defClass: 'ui_state_default',
            callback: function() {
                return THISPAGE.copyGoodsInfo('pullson'), !1;
            }
        }, {
            id: 'saveAndAdd',
            name: '保存并新增',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.cancleGridEdit(), THISPAGE.postData(0), !1;
            }
        }, {
            id: 'confirm',
            name: '保存',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.cancleGridEdit(), THISPAGE.postData(1), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        if ('edit' == oper) {
            $('#no').val(rowData.no);
            $('#barCode').val(rowData.barCode);
            $('#name').val(rowData.name);
            $('#spec').val(rowData.spec);
            $('#sku').val(rowData.sku);
            $('#weight').val(rowData.weight, pricePlaces);
            $('#minInventory').val(rowData.lowQty);
            $('#maxInventory').val(rowData.highQty);
            $('#purchasePrice').val(rowData.purPrice, pricePlaces);
            $('#salePrice').val(rowData.salePrice, pricePlaces);
            $('#createTime').val(rowData.uName + ' ' + rowData.createTime);
            $('#modifyTime').val(rowData.modName + ' ' + rowData.modifyTime);
            $('#description').val(rowData.description);
            $('#produceClass1').val(rowData.produceClass1);
            $('#produceClass2').val(rowData.produceClass2);
            $('#produceQty1').val(rowData.produceQty1);
            $('#produceQty2').val(rowData.produceQty2);
            $('#producePrice1').val(rowData.producePrice1);
            $('#producePrice2').val(0 == rowData.producePrice2 ? '' : rowData.producePrice2);
            $('#producePrice3').val(rowData.producePrice3);
            if (rowData.imgs) {
                THISPAGE.addImgs(rowData.imgs);
            }
            if (rowData.files) {
                THISPAGE.addFiles(rowData.files);
            }
        }
        if ($.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) !== -1) {
            $('#produceInfo').removeClass('dn');
            $('#produceInfoHide').addClass('dn');
        }
        categoryTree = Public.categoryTree($('#category'), {
            onlyposition: 1,
            height: 400,
            defaultSelectValue: rowData.categoryId || '',
            showRoot: !1
        });
        brandCombo = $('#brand').combo({
            data: '/Base/Category/list?typeNumber=brandtype',
            ajaxOptions: {
                formatData: function(res) {
                    return res.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            listWidth: 200,
            defaultSelected: rowData.brandId > 0 ? ['id', rowData.brandId] : -1,
            extraListHtml: '<a href="#" class="quick-add-link" onclick="THISPAGE.addBrand();return false;">新增产品品牌</a>'
        }).getCombo();
        unitCombo = $('#unit').combo({
            data: '/Base/Category/list?typeNumber=unittype',
            ajaxOptions: {
                formatData: function(res) {
                    return res.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            listWidth: 100,
            defaultSelected: rowData.unitId > 0 ? ['id', rowData.unitId] : -1,
            extraListHtml: '<a href="#" class="quick-add-link" onclick="THISPAGE.addUnit();return false;">新增计量单位</a>'
        }).getCombo();
        storageCombo = $('#storage').combo({
            data: '/Base/Storage/list',
            ajaxOptions: {
                formatData: function(res) {
                    return res.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.storageId > 0 ? ['id', rowData.storageId] : 0
        }).getCombo();
        locationCombo = $('#location').combo({
            data: '/Base/Storage/locationList?storageId=1&type=child',
            ajaxOptions: {
                formatData: function(res) {
                    return res.data;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.locationId > 0 ? ['id', rowData.locationId] : -1
        }).getCombo();
        disableCombo = $('#isDisable').combo({
            data: [{id: 0, name: '正常'}, {id: 1, name: '将停产'}, {id: 2, name: '已停产'}, {id: 9, name: '已禁用'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.isDisable >= 0 ? ['id', rowData.isDisable] : -1
        }).getCombo();
        proofreadCombo = $('#isProofread').combo({
            data: [{id: 0, name: '未校对'}, {id: 1, name: '已校对'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.isProofread >= 0 ? ['id', rowData.isProofread] : -1
        }).getCombo();
        serNameCombo = $('#serName').combo({
            data: '/Base/User/queryAll?normal=yes&department=技术部',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            placeholder: '如为自产产品，请选择维护人',
            defaultSelected: rowData.serName ? ['name', rowData.serName] : -1,
            listHeight: 434
        }).getCombo();
        $('#category').attr('name', 'categoryInput');
        $('#unit').find('input').attr('name', 'unitInput');
        $('#storage').find('input').attr('name', 'storageInput');
        $('#location').find('input').attr('name', 'locationInput');
        $('#isDisable').find('input').attr('name', 'disableInput');
        $('#isProofread').find('input').attr('name', 'proofreadInput');
        $('#serName').find('input').attr('name', 'serNameInput');
        Public.ajaxGet('/Base/User/queryAll', {department: '技术部', isDisable: -2}, function(data) {
            if (data.status === 200) {
                var normalUser = disableUser = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        normalUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        disableUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                $('#devNameNormal').html(normalUser);
                $('#devNameDisable').html(disableUser);
                /* 异步获取数据成功之后再填充选项并加载选择事件 */
                $('#devNameCombo').fSelect({
                    id: 'devNameFselect',
                    showSearch: !1,
                    listHeight: 372,
                    placeholder: '如为自产产品，请选择开发人',
                });
                if (rowData.devName) {
                    $('#devNameFselect .fs-input').val(rowData.devName);
                    var names = rowData.devName.toString().split(',');
                    $("#devNameFselect .fs-option").each(function() {
                        if (names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    })
                }
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
    },
    initGrid: function(a) {
        THISPAGE.skey = '';
        THISPAGE.categoryId = 0;
        var b = 8 - a.length;
        if (b > 0) {
            for (var c = 0; b > c; c++) {
                a.push({});
            }
            THISPAGE.newId = 9;
        } else {
            THISPAGE.newId = a.length + 1;
        }
        $('#grid').jqGrid({
            data: a,
            datatype: 'local',
            colModel: [{
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.billsOper
            }, {
                name: 'goods',
                label: '选产品',
                width: 72,
                fixed: !0,
                editable: !0,
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.goodsAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            var d = $(a).parents('tr');
                            return d.removeData('goodsInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function(a, b, c) {
                        $('#initCombo').append($('.goodsAuto').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-ellipsis iconfont icon-shenglvehao'
                },
                classes: 'goods ui-ellipsis'
            }, {
                name: 'gid',
                label: '产品ID',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'no',
                label: '产品编码',
                width: 150,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '产品品牌',
                width: 58,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'qty1',
                label: '每份数量',
                width: 58,
                fixed: !0,
                editable: !0
            }, {
                name: 'unit1',
                label: '每份单位',
                width: 58,
                fixed: !0,
                editable: !1
            }, {
                name: 'qty2',
                label: '份数',
                width: 40,
                fixed: !0,
                editable: !0
            }, {
                name: 'unit2',
                label: ' ',
                width: 22,
                fixed: !0,
                editable: !1
            }, {
                name: 'produceClass1',
                label: '',
                hidden: !0
            }, {
                name: 'produceClass2',
                label: '',
                hidden: !0
            }, {
                name: 'produceQty1',
                label: '',
                hidden: !0
            }, {
                name: 'produceQty2',
                label: '',
                hidden: !0
            }, {
                name: 'producePrice1',
                label: '',
                hidden: !0
            }, {
                name: 'producePrice2',
                label: '',
                hidden: !0
            }, {
                name: 'producePrice3',
                label: '',
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1},
            width: $(window).width() - 22,
            height: $(window).height() - 440,
            gridview: !0,
            rownumbers: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            rowNum: 1000,
            loadComplete: function(a) {
                if (void 0 !== cRowId) {
                    var b = a.rows.length;
                    THISPAGE.newId = b + 1;
                }
                THISPAGE.calTotal();
            },
            gridComplete: function() {
                setTimeout(function() {
                    $("#grid").jqGrid('setGridWidth', $(window).width() - 22);
                    $("#grid").jqGrid('setGridHeight', $(window).height() - 440);
                    // 拖动排序功能
                    $('#grid').tableDnD({
                        dragHandle: 'jqgrid-rownum',
                        onDragClass: 'tDnD_whileDrag'
                    });
                    $("#grid").tableDnDUpdate();
                }, 100);
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
                    case 'qty1':
                        THISPAGE.calTotal();
                        break;
                    case 'qty2':
                        THISPAGE.calTotal();
                        break;
                    default:
                        break;
                }
            }
        });
    },
    calTotal: function() {
        var ids = $('#grid').jqGrid('getDataIDs');
        for (var length = ids.length, produceClass1 = 0, produceQty1 = 0, key = 0; length > key; key++) {
            var rowData = $('#grid').jqGrid('getRowData', ids[key]);
            if (!rowData.gid) {
                continue;
            }
            // 累计元件配货种类
            if (parseInt(rowData.produceClass1) > 0) { //子产品是套件
                produceClass1 += (parseInt(rowData.produceClass1) + parseInt(rowData.produceClass2));
                produceQty1 += (parseInt(rowData.produceQty1) + parseInt(rowData.produceQty2));
            } else { // 子产品不是套件
                produceClass1 += 1;
                var rowQty1 = parseInt(rowData.qty1) != parseFloat(rowData.qty1) ? 1 : parseInt(rowData.qty1); // 如果是小数则算成1个元件配货量
                var rowQty2 = parseInt(rowData.qty2);
                if ($.isNumeric(rowQty1) && $.isNumeric(rowQty2)) {
                    produceQty1 += rowQty1 * rowQty2; // 累计元件配货量
                }
            }
        };
        var produceClass2 = parseFloat($('#produceClass2').val());
        var produceQty2 = parseFloat($('#produceQty2').val());
        produceClass2 = $.isNumeric(produceClass2) ? produceClass2 : 0;
        produceQty2 = $.isNumeric(produceQty2) ? produceQty2 : 0;
        var producePrice1 = Number((((produceClass1 + produceClass2) * defaultPage.SYSTEM.expPar.proKitting.par1) + ((produceQty1 + produceQty2) * defaultPage.SYSTEM.expPar.proKitting.par2)) * defaultPage.SYSTEM.expPar.proKitting.par3).toFixed(5);
        var producePrice2 = parseFloat($('#producePrice2').val());
        producePrice2 = $.isNumeric(producePrice2) ? producePrice2 : 0;
        var producePrice3 = Number(parseFloat(producePrice1) + parseFloat(producePrice2)).toFixed(5);
        $('#produceClass1').val(produceClass1);
        $('#produceQty1').val(produceQty1);
        $('#producePrice1').val(producePrice1);
        $('#producePrice3').val(producePrice3);
    },
    initEvent: function() {
        Business.querySonGoodsEvent(this);
        $('#notice').on('click', function(a) {
            a.preventDefault();
            if ($('.notice-wrap').hasClass('dn')) {
                $('.notice-wrap').removeClass('dn');
            } else {
                $('.notice-wrap').addClass('dn');
            }
        });
        $('.notice-close').on('click', function(a) {
            a.preventDefault();
            $('.notice-wrap').addClass('dn');
        });
        $('.qty').on({
            keypress: Public.numerical,
            focus: function() {
                $(this).select();
            },
            blur: function() {
                this.value = Number(this.value).toFixed(qtyPlaces);
            }
        });
        $('.money').on({
            keypress: Public.numerical,
            focus: function() {
                $(this).select();
            },
            blur: function() {
                this.value = Number(this.value).toFixed(pricePlaces);
            }
        });
        $('#purchasePrice').on('keyup', function() {
            $('#salePrice').val(Number(this.value) * 1.5);
        });
        $('#modifyHis').on('click', function() {
            THISPAGE.modifyHis();
        });
        $('#upimg').uploader({
            action: '/Base/Goods/uploadImg',
            mode: 'html5',
            name: 'file',
            formData: {},
            multiple: false,
            auto: true,
            debug: false,
            preview: false,
            showQueue: '#queue',
            fileSizeLimit: '10M',
            fileTypeDesc: '选择图片文件',
            fileTypeExts: 'jpg,jpeg,gif,png,webp',
            onStart: function(e) {
                $('#manage-wrap').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！', 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.img) {
                    if (!rowData.imgs) {
                        rowData.imgs = [];
                        rowData.imgs[0] = data.img;
                    } else {
                        rowData.imgs.unshift(data.img);
                    }
                    THISPAGE.addImgs(rowData.imgs);
                }
            },
            onAllComplete: function() {
                $('#manage-wrap > #div-lock').remove();
            }
        });
        $('#upFile').uploader({
            action: '/Base/Goods/uploadfile',
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
            fileTypeExts: 'jpg,jpeg,gif,png | csv,xls,xlsx,doc,docx,xps,pdf,txt | zip,rar,iso,7z | mp4,3gp,avi',
            onStart: function(e) {
                $('#manage-wrap').append('<div id="div-lock"><div class="loading">上传中...</div></div>');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！', 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.file) {
                    if (!rowData.files) {
                        rowData.files = [];
                        rowData.files[0] = data.file;
                    } else {
                        rowData.files.unshift(data.file);
                    }
                    THISPAGE.addFiles(rowData.files);
                }
            },
            onAllComplete: function() {
                $('#manage-wrap > #div-lock').remove();
            }
        });
        $('.upload-el').on({
            click: function(a) {
                if (!Business.verifyRight('GOODS_UPDATE')) {
                    a.preventDefault();
                } else {
                    if ($(this).siblings('.upfile-title').html() == '参考文件') {
                        $.dialog.alert('仅上传外部产品采购参考文件！自家产品各种文件请上传到产品菜单相应文件分类栏目！');
                    }
                }
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
        $('#imgs').on('click', '.imgDiv', function() {
            var a = $(this).find('img').prop('src');
            window.open(a);
        });
        $('#imgs').on('click', '.del', function(a) {
            a.stopPropagation();
            if (Business.verifyRight('GOODS_UPDATE')) {
                $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    var key = $(this).closest('.imgDiv').hide().data('key');
                    rowData.imgs.splice(key, 1) && THISPAGE.addImgs(rowData.imgs);
                });
            }
        });
        $('#files').on('click', '.fileRename', function() {
            if (Business.verifyRight('GOODS_UPDATE')) {
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
                rowData.files[key].name = newName;
                THISPAGE.addFiles(rowData.files);
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
            if (Business.verifyRight('GOODS_UPDATE')) {
                $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    var key = $(this).closest('.file-line').data('key');
                    rowData.files.splice(key, 1) && THISPAGE.addFiles(rowData.files);
                });
            }
        });
        $('#export').on('click', function(a) {
            if (typeof rowData.hasSon == 'undefined') {
                $.dialog.tips('未保存产品无法获取子产品信息！', 2);
            } else {
                if (rowData.hasSon < 1) {
                    $.dialog.tips('未查找到已保存的子产品信息！', 2);
                } else {
                    if (Business.verifyRight('GOODS_EXPORT')) {
                        $(this).attr('href', '/Base/Goods/exportSons?id=' + rowData.id);
                    }
                }
            }
        });
        $('#sonrule').on('click', function(a) {
            a.preventDefault();
            if ($('.sonrule-wrap').hasClass('dn')) {
                $('.sonrule-wrap').removeClass('dn');
            } else {
                $('.sonrule-wrap').addClass('dn');
            }
        });
        $('.sonrule-close').on('click', function(a) {
            a.preventDefault();
            $('.sonrule-wrap').addClass('dn');
        });
        $('.grid-wrap').on('keypress', '.textbox', Public.numerical);
        $('#produceClass2, #produceQty2, #producePrice2').on('change', function(a) {
            var produceClass1 = parseFloat($('#produceClass1').val());
            var produceClass2 = parseFloat($('#produceClass2').val());
            var produceQty1 = parseFloat($('#produceQty1').val());
            var produceQty2 = parseFloat($('#produceQty2').val());
            var producePrice2 = parseFloat($('#producePrice2').val());
            produceClass1 = $.isNumeric(produceClass1) ? produceClass1 : 0;
            produceClass2 = $.isNumeric(produceClass2) ? produceClass2 : 0;
            produceQty1 = $.isNumeric(produceQty1) ? produceQty1 : 0;
            produceQty2 = $.isNumeric(produceQty2) ? produceQty2 : 0;
            producePrice2 = $.isNumeric(producePrice2) ? producePrice2 : 0;
            var producePrice1 = Number((((produceClass1 + produceClass2) * defaultPage.SYSTEM.expPar.proKitting.par1) + ((produceQty1 + produceQty2) * defaultPage.SYSTEM.expPar.proKitting.par2)) * defaultPage.SYSTEM.expPar.proKitting.par3).toFixed(5);
            var producePrice3 = Number((((produceClass1 + produceClass2) * defaultPage.SYSTEM.expPar.proKitting.par1) + ((produceQty1 + produceQty2) * defaultPage.SYSTEM.expPar.proKitting.par2)) * defaultPage.SYSTEM.expPar.proKitting.par3 + producePrice2).toFixed(5);
            $('#producePrice1').val(producePrice1);
            $('#producePrice3').val(producePrice3);
        });
        $(document).on('click.cancel', function(a) {
            var b = a.target || a.srcElement;
            !$(b).closest('#grid').length > 0 && THISPAGE.cancleGridEdit();
        });
        $(window).on('resize', function() {
            setTimeout(function() {
                $("#grid").jqGrid('setGridWidth', $(window).width() - 22);
                $("#grid").jqGrid('setGridHeight', $(window).height() - 440);
            }, 100);
        });
    },
    addImgs: function(imgs) {
        var imgDiv = '';
        $.each(imgs, function(k, img) {
            imgDiv += '<div class="imgDiv" data-key="' + k + '"><p class="imgControl"><span class="controlBtn see iconfont icon-xianshi" title="查看"></span><span class="controlBtn del iconfont icon-shanchu" title="删除"></span></p><img src="/data/upfile/goods/img/' + img + '"/></div>';
        });
        $('#imgs').html(imgDiv);
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '">' +
                '<a href="/data/upfile/goods/file/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    modifyHis: function() {
        $.dialog({
            title: '产品编辑历史',
            content: 'url:/Uri/goods_modify_his',
            data: {
                id: rowData.id
            },
            width: $(window).width() - 100,
            height: $(window).height() - 200,
            max: !1,
            min: !1,
            lock: !0,
            cache: !1
        });
    },
    initValidator: function() {
        $.validator.addMethod('checkNo', function(a) {
            /** 以大小写字母或数字开头，中间接0-24位大小写字母或数字或连接符，并以大小写字母或数字结尾 */
            return a ? /^[a-zA-Z0-9][a-zA-Z0-9-]{0,24}[a-zA-Z0-9]$/.test(a) : !0;
        });
        $.validator.addMethod('checkBarCode', function(a) {
            return a ? /^[a-zA-Z0-9]{1,30}$/.test(a) : !0;
        });
        $('#manage-form').validate({
            rules: {
                categoryInput: {required: !0},
                no: {required: !0, checkNo: !0},
                barCode: {checkBarCode: !0},
                name: {required: !0},
                unitInput: {required: !0},
                storageInput: {required: !0},
                minInventory: {required: !0},
                maxInventory: {required: !0},
                purchasePrice: {required: !0},
                salePrice: {required: !0},
                disableInput: {required: !0},
                proofreadInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('GOODS_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增产品' : '编辑产品';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Goods/' + ('add' == oper ? 'add' : 'update'), {
                postData: JSON.stringify(data)
            }, function(res) {
                if (200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    }
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var a = THISPAGE.getGridData();
        if (!a) {
            return !1;
        }
        var b = {
            id: void 0 !== cRowId ? cRowId : -1,
            categoryId: categoryTree.getValue(),
            no: $.trim($('#no').val()),
            barCode: $('#barCode').val(),
            name: $.trim($('#name').val()),
            spec: $.trim($('#spec').val()),
            sku: $.trim($('#sku').val()),
            brandId: brandCombo.getValue(),
            unitId: unitCombo.getValue(),
            storageId: storageCombo.getValue(),
            locationId: locationCombo.getValue(),
            isDisable: disableCombo.getValue(),
            isProofread: proofreadCombo.getValue(),
            devName: $.trim($('#devNameFselect .fs-input').val()),
            serName: serNameCombo.getText(),
            weight: $('#weight').val() || 0,
            lowQty: $('#minInventory').val() || 0,
            highQty: $('#maxInventory').val() || 0,
            purPrice: $('#purchasePrice').val() || 0,
            salePrice: $('#salePrice').val() || 0,
            imgs: rowData.imgs ? JSON.stringify(rowData.imgs) : '',
            files: rowData.files ? JSON.stringify(rowData.files) : '',
            description: $.trim($('#description').val()),
            sonGoods: a.length > 0 ? JSON.stringify(a) : '',
            produceClass1: $('#produceClass1').val() || 0,
            produceClass2: $('#produceClass2').val() || 0,
            produceQty1: $('#produceQty1').val() || 0,
            produceQty2: $('#produceQty2').val() || 0,
            producePrice1: $('#producePrice1').val() || 0,
            producePrice2: $('#producePrice2').val() || 0,
            producePrice3: $('#producePrice3').val() || 0,
        };
        return b;
    },
    getGridData: function() {
        var sonGoods = [];
        var sonIds = $('#grid').jqGrid('getDataIDs');
        var sonLen = sonIds.length;
        for (var i = 0; sonLen > i; i++) {
            var sonData = {};
            var gid = sonIds[i];
            var sonRow = $('#grid').jqGrid('getRowData', gid);
            if (!sonRow.gid) {
                continue;
            }
            sonData = {
                gid: Number(sonRow.gid),
                no: sonRow.no,
                name: sonRow.name,
                spec: sonRow.spec || '',
                sku: sonRow.sku || '',
                brand: sonRow.brand || '',
                qty1: Number(sonRow.qty1),
                unit1: sonRow.unit1,
                qty2: Number(sonRow.qty2),
                unit2: sonRow.unit2
            };
            sonGoods.push(sonData);
        }
        return sonGoods;
    },
    cancleGridEdit: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
    },
    addBrand: function(a) {
        $.dialog({
            title: '新增产品品牌',
            content: 'url:/Uri/category_manage',
            data: {
                oper: 'add',
                typeNumber: 'brandtype',
                callback: function(a, b, c) {
                    Public.ajaxPost('/Base/Category/list?typeNumber=brandtype', {}, function(d) {
                        if (d && 200 == d.status) {
                            var e = d.data.rows;
                        } else {
                            var e = [];
                            $.dialog.tips('获取产品品牌失败！', 2);
                        }
                        brandCombo.loadData(e, '-1', !1);
                        brandCombo.selectByValue(a.id);
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
    },
    addUnit: function() {
        $.dialog({
            title: '新增计量单位',
            content: 'url:/Uri/category_manage',
            data: {
                oper: 'add',
                typeNumber: 'unittype',
                callback: function(a, b, c) {
                    Public.ajaxPost('/Base/Category/list?typeNumber=unittype', {}, function(d) {
                        if (d && 200 == d.status) {
                            var e = d.data.rows;
                        } else {
                            var e = [];
                            $.dialog.tips('获取计量单位失败！', 2);
                        }
                        unitCombo.loadData(e, '-1', !1);
                        unitCombo.selectByValue(a.id);
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
    },
    resetForm: function() {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑', '新增'));
        }
        oper = 'add';
        cRowId = void 0;
        rowData = {};
        var data = [{}];
        categoryTree.selectByValue('');
        $('#no').val('');
        $('#barCode').val('');
        $('#name').val('');
        $('#spec').val('');
        $('#sku').val('');
        brandCombo._setDefaultSelected(-1);
        unitCombo._setDefaultSelected(-1);
        storageCombo._setDefaultSelected(-1);
        locationCombo._setDefaultSelected(-1);
        disableCombo._setDefaultSelected(-1);
        proofreadCombo._setDefaultSelected(-1);
        serNameCombo._setDefaultSelected(-1);
        $('#clearAllBtn').trigger('click');
        $('#weight').val('');
        $('#minInventory').val('');
        $('#maxInventory').val('');
        $('#purchasePrice').val('');
        $('#salePrice').val('');
        $('#createTime').val('');
        $('#modifyTime').val('');
        $('#imgs').html('');
        $('#files').html('');
        $('#description').val('');
        $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
            data: data
        }).trigger('reloadGrid');
        $('#produceClass1').val(0);
        $('#produceClass2').val(0);
        $('#produceQty1').val(0);
        $('#produceQty2').val(0);
        $('#producePrice1').val(0);
        $('#producePrice2').val('');
        $('#producePrice3').val(0);
    },
    copyGoodsInfo: function(type) {
        $.dialog({
            width: 1250,
            height: 500,
            title: '选择目标产品',
            content: 'url:/Uri/goods_batch',
            data: {
                isNoAdd: !0,
                isSingle: !0
            },
            max: !1,
            min: !1,
            lock: !0,
            ok: function() {
                var goodsId = 0;
                var addList = this.content.addList;
                for (rowid in addList) {
                    goodsId = rowid;
                }
                goodsId > 0 && Public.ajaxPost('/Base/Goods/query', {
                    id: goodsId
                }, function(b) {
                    if (200 == b.status) {
                        rowData = b.data;
                        THISPAGE.cancleGridEdit();
                        if ('all' == type) {
                            categoryTree.selectByValue(rowData.categoryId);
                            $('#no').val(rowData.no);
                            $('#barCode').val(rowData.barCode);
                            $('#name').val(rowData.name);
                            $('#spec').val(rowData.spec);
                            $('#sku').val(rowData.sku);
                            brandCombo.selectByValue(rowData.brandId);
                            unitCombo.selectByValue(rowData.unitId);
                            storageCombo.selectByValue(rowData.storageId);
                            locationCombo.selectByValue(rowData.locationId);
                            $('#minInventory').val(rowData.lowQty);
                            $('#maxInventory').val(rowData.highQty);
                            disableCombo.selectByValue(rowData.isDisable);
                            proofreadCombo.selectByValue(rowData.isProofread);
                            serNameCombo.selectByValue(rowData.serName);
                            $('#weight').val(rowData.weight, pricePlaces);
                            $('#purchasePrice').val(rowData.purPrice, pricePlaces);
                            $('#salePrice').val(rowData.salePrice, pricePlaces);
                            $('#modName').val(defaultPage.SYSTEM.uName);
                            $('#description').val(rowData.description);
                            if (rowData.imgs) {
                                THISPAGE.addImgs(rowData.imgs);
                            }
                            if (rowData.files) {
                                THISPAGE.addFiles(rowData.files);
                            }
                            $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
                                data: rowData.sonGoods
                            }).trigger('reloadGrid');
                            THISPAGE.newId = rowData.sonGoods.length + 1;
                            $('#produceClass1').val(rowData.produceClass1);
                            $('#produceClass2').val(rowData.produceClass2);
                            $('#produceQty1').val(rowData.produceQty1);
                            $('#produceQty2').val(rowData.produceQty2);
                            $('#producePrice1').val(rowData.producePrice1);
                            $('#producePrice2').val(0 == rowData.producePrice2 ? '' : rowData.producePrice2);
                            $('#producePrice3').val(rowData.producePrice3);
                        } else if ('son' == type) {
                            $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
                                data: rowData.sonGoods
                            }).trigger('reloadGrid');
                            THISPAGE.newId = rowData.sonGoods.length + 1;
                            $('#produceClass1').val(rowData.produceClass1);
                            $('#produceClass2').val(rowData.produceClass2);
                            $('#produceQty1').val(rowData.produceQty1);
                            $('#produceQty2').val(rowData.produceQty2);
                            $('#producePrice1').val(rowData.producePrice1);
                            $('#producePrice2').val(0 == rowData.producePrice2 ? '' : rowData.producePrice2);
                            $('#producePrice3').val(rowData.producePrice3);
                        } else if ('pullson' == type) {
                            var sonGoods = [];
                            var sonIds = $('#grid').jqGrid('getDataIDs');
                            var sonLen = sonIds.length;
                            for (var i = 0; sonLen > i; i++) {
                                var sonData = {};
                                var gid = sonIds[i];
                                var sonRow = $('#grid').jqGrid('getRowData', gid);
                                if (!sonRow.gid) {
                                    continue;
                                }
                                sonData = {
                                    gid: Number(sonRow.gid),
                                    no: sonRow.no,
                                    name: sonRow.name,
                                    spec: sonRow.spec || '',
                                    sku: sonRow.sku || '',
                                    brand: sonRow.brand || '',
                                    qty1: Number(sonRow.qty1),
                                    unit1: sonRow.unit1,
                                    qty2: Number(sonRow.qty2),
                                    unit2: sonRow.unit2
                                };
                                sonGoods.push(sonData);
                            }
                            $.merge(sonGoods, rowData.sonGoods);
                            $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
                                data: sonGoods
                            }).trigger('reloadGrid');
                            THISPAGE.newId = sonGoods.length + 1;
                            THISPAGE.calTotal();
                        }
                        $.dialog.tips('从已有产品快速复制成功！请按需检查编辑！', 1);
                    } else {
                        $.dialog.tips('获取产品信息失败！', 2);
                    }
                });
            },
            cancel: function() {
                return !0;
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
