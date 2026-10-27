var defaultPage = Public.getDefaultPage();
var queryConditions = {
    skey: ''
};
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Applicant/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '姓名',
                width: 42,
                fixed: !0,
                sortable: !0
            }, {
                name: 'position',
                label: '应聘职位',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'joinDate',
                label: '应聘日期',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'salary',
                label: '期薪',
                width: 42,
                fixed: !0,
                sortable: !0
            }, {
                name: 'mobile',
                label: '手机号',
                width: 80,
                fixed: !0,
                sortable: !0
            }, {
                name: 'sex',
                label: '性别',
                width: 42,
                fixed: !0,
                sortable: !0
            }, {
                name: 'birthday',
                label: '出生日期',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'identity',
                label: '社会身份',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'nativePlace',
                label: '籍贯',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'nation',
                label: '民族',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'political',
                label: '政貌',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'criminal',
                label: '犯罪',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'healthy',
                label: '健康',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'marriage',
                label: '婚姻',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'education',
                label: '学历',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'graduation',
                label: '证书',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'school',
                label: '院校',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'major',
                label: '专业',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'des',
                label: '自我评价',
                width: 100,
                title: !0,
                sortable: !1,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    var d = (
                        '优势(' + c.selfEvaluationOne + ') 劣势(' + c.selfEvaluationTwo + ')'
                    );
                    return d;
                }
            }, {
                name: 'createdUser',
                label: '入职转化',
                width: 66,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    return a == 1 ? '<span class="db h28 lh28 hover-green b-r-3">已入职</span>' : '<span class="user-btn db h28 lh28 hover-red b-r-3" data-id="' + c.id + '" data-sfzno="' + c.sfzNo + '" title="点击入职转化为正式员工">未入职</span>';
                }
            }, {
                name: 'print',
                label: '打印',
                width: 34,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    return '<i class="print-btn iconfont icon-dayin f16 lh32 poi" title="打印" data-id="' + c.id + '"></i>';
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(c) {
                $('#' + c).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            $.dialog({
                title: '新增应聘档案',
                content: 'url:/Uri/applicant_manage',
                data: {
                    oper: 'add',
                    callback: THISPAGE.callback
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
        });
        $('.grid-wrap').on('click', '.user-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var sfzno = $(this).data('sfzno');
            var birthday_sfz_lunar = {};
            if (sfzno.length > 15) {
                var birthday_sfz = sfzno.substring(6, 14);
                birthday_sfz_lunar = calendar.solar2lunar(birthday_sfz.substring(0, 4), birthday_sfz.substring(4, 6), birthday_sfz.substring(6, 8));
            }
            $.dialog.confirm('请确认是否将该人员入职转化为正式员工？', function() {
                Public.ajaxPost('/Base/Applicant/regUser', {
                    id: id,
                    animal: Object.keys(birthday_sfz_lunar).length > 0 ? birthday_sfz_lunar.animal : '',
                    astro: Object.keys(birthday_sfz_lunar).length > 0 ? birthday_sfz_lunar.astro : '',
                    birthdaySolar: Object.keys(birthday_sfz_lunar).length > 0 ? birthday_sfz_lunar.sMonth + '月' + birthday_sfz_lunar.sDay + '日' : '',
                    birthdayLunar: Object.keys(birthday_sfz_lunar).length > 0 ? birthday_sfz_lunar.lMonthCn + birthday_sfz_lunar.lDayCn : ''
                }, function(res) {
                    if (res && 200 == res.status) {
                        $('#search').trigger('click');
                        $.dialog.confirm('入职成功（员工账号名：员工手机号，初始密码：员工身份证后6位）。<br>账号权限未分配，确定立即前往分配权限？', function() {
                            parent.parent.tab.addTabItem({
                                tabid: 'setting-user',
                                text: '<i class="iconfont icon-yonghuzu f14 mr5"></i>员工账号',
                                url: '/Uri/user_list'
                            });
                        });
                    } else {
                        $.dialog.tips('生成失败！' + res.msg, 2);
                    }
                });
            });
        });
        $('.grid-wrap').on('click', '.print-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            Public.ajaxPost('/Base/Applicant/query', {
                id: id
            }, function(res) {
                if (200 === res.status) {
                    var prnhtml = '<div class="mb50 f20 fb tc">应聘档案登记表</div>'
                        + '<div class="bd0 ovh" style="width:808px;">'
                        + '<div class="fl" style="width:606px;height:248px;">'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">姓名</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.name + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">了解渠道</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.channel + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">应聘部门</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.department + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">应聘职位</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.position + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">应聘日期</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.joinDate + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">社会身份</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.identity + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">性别</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.sex + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">出生日期</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.birthday + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">籍贯</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.nativePlace + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">民族</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.nation + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">政治面貌</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.political + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">犯罪记录</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.criminal + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">健康状况</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.healthy + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">婚姻状况</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.marriage + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">入学渠道</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0">' + res.data.entrance + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">最高学历</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0" style="width:201px">' + res.data.education + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">毕业证书</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0" style="width:201px">' + res.data.graduation + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">毕业院校</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0 es" style="width:201px">' + res.data.school + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">主修专业</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0 es" style="width:201px">' + res.data.major + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">期望薪资</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0" style="width:50px">' + res.data.salary + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">身份证号</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0" style="width:150px">' + res.data.sfzNo + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">手机号</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0" style="width:100px">' + res.data.mobile + '</div>'
                        + '</div>'
                        + '<div class="fl f18 fb tc bb0" style="width:201px;height:247px;line-height:247px;">照片区</div>'

                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">家庭住址</div>'
                        + '<div class="fl h30 lh30 tl ti10 bb0 es" style="width:707px">' + res.data.homeAddress + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">现居住地</div>'
                        + '<div class="fl h30 lh30  tl ti10 bb0 es" style="width:707px">' + res.data.nowAddress + '</div>'

                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">紧急联系人</div>'
                        + '<div class="fl w100 h30 lh30 fb tc bb0">姓名:</div>'
                        + '<div class="fl w100 h30 lh30 ti10 bb0">' + res.data.urgentName + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc bb0">与您的关系:</div>'
                        + '<div class="fl w100 h30 lh30 ti10 bb0">' + res.data.urgentRelationship + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc bb0">联系电话:</div>'
                        + '<div class="fl h30 lh30 ti10 bb0" style="width:206px;">' + res.data.urgentPhone + '</div>'

                        + '<div class="fl w100 fb tc br0 bb0" style="height:61px;line-height:61px;">自我评价</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">优势</div>'
                        + '<div class="fl h30 lh30 tl ti10 bb0 es" style="width:606px">' + res.data.selfEvaluationOne + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">劣势</div>'
                        + '<div class="fl h30 lh30 tl ti10 bb0 es" style="width:606px">' + res.data.selfEvaluationTwo + '</div>'

                        + '<div class="fl w100 fb tc br0 bb0" style="height:185px;line-height:185px;">工作经历</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">起止时间</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0 es" style="width:150px;">' + res.data.workOneTime + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">单位</div>'
                        + '<div class="fl h30 lh30 tc bb0 es" style="width:354px;">' + res.data.workOneCompany + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">职位</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0 es" style="width:150px;">' + res.data.workOnePosition + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">离职原因</div>'
                        + '<div class="fl h30 lh30 tc bb0 es" style="width:354px;">' + res.data.workOneEndReason + '</div>'
                        + '<div class="fl w150 h30 lh30 fb tc br0 bb0">证明人及联系方式</div>'
                        + '<div class="fl h30 lh30 tc bb0 es" style="width:556px">' + res.data.workOneWitness + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">起止时间</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0 es" style="width:150px;">' + res.data.workTwoTime + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">单位</div>'
                        + '<div class="fl h30 lh30 tc bb0 es" style="width:354px;">' + res.data.workTwoCompany + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">职位</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0 es" style="width:150px;">' + res.data.workTwoPosition + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">离职原因</div>'
                        + '<div class="fl h30 lh30 tc bb0 es" style="width:354px;">' + res.data.workTwoEndReason + '</div>'
                        + '<div class="fl w150 h30 lh30 fb tc br0 bb0">证明人及联系方式</div>'
                        + '<div class="fl h30 lh30 tc bb0 es" style="width:556px">' + res.data.workTwoWitness + '</div>'

                        + '<div class="fl w100 fb tc br0 bb0" style="height:123px;line-height:123px;">家庭成员</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">姓名</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0 es">' + res.data.familyOneName + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">与您的关系</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0 es">' + res.data.familyOneRelationship + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">联系电话</div>'
                        + '<div class="fl w200 h30 lh30 tc bb0 es">' + res.data.familyOnePhone + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">工作单位</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0 es" style="width:302px;">' + res.data.familyOneWorkPlace + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">工作职位</div>'
                        + '<div class="fl w250 h30 lh30 tc bb0 es" style="width:202px;">' + res.data.familyOnePosition + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">姓名</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0 es">' + res.data.familyTwoName + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">与您的关系</div>'
                        + '<div class="fl w100 h30 lh30 tc br0 bb0 es">' + res.data.familyTwoRelationship + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">联系电话</div>'
                        + '<div class="fl w200 h30 lh30 tc bb0 es">' + res.data.familyTwoPhone + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">工作单位</div>'
                        + '<div class="fl h30 lh30 tc br0 bb0 es" style="width:302px;">' + res.data.familyTwoWorkPlace + '</div>'
                        + '<div class="fl w100 h30 lh30 fb tc br0 bb0">工作职位</div>'
                        + '<div class="fl w250 h30 lh30 tc bb0 es" style="width:202px;">' + res.data.familyTwoPosition + '</div>'
                        + '<div class="pl20 pr20 fb lh50">应聘人承诺声明：本人保证以上所填内容完全属实，且为本人亲自提供，如有虚假，本人自愿承担所有相应后果与损失。</div>'

                        + '</div>'
                        + '<div class="fr mt50 h50 lh50 fb" style="margin-right: 150px;"><span class="mr50">日期：' + new Date().format('yyyy-MM-dd') + '</span>应聘人签字：</div>';
                    window.document.body.innerHTML = prnhtml;
                    window.print();
                    window.location.reload();
                }
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑应聘档案',
                content: 'url:/Uri/applicant_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
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
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                Public.ajaxPost('/Base/Applicant/delete', {
                    id: id
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('删除成功！');
                        $('#grid').jqGrid('delRowData', id);
                    } else {
                        $.dialog.tips('删除失败！' + res.msg, 2);
                    }
                });
            });
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#search').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function(a, b, c, d) {
        var e = $('#grid').data('gridData');
        e || (e = {}, $('#grid').data('gridData', e));
        e[a.id] = a;
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : ($('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
