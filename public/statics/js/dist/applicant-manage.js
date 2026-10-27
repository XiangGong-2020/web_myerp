var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var channelCombo, departmentCombo, positionCombo, identityCombo, sexCombo, nationCombo, politicalCombo, criminalCombo, healthyCombo, marriageCombo, educationCombo, entranceCombo, graduationCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Applicant/query', {
                id: cRowId
            }, function(res) {
                if (200 === res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initCombo();
                    THISPAGE.initEvent();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！', 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initCombo();
            THISPAGE.initEvent();
            THISPAGE.initValidator();
        }
    },
    initBtns: function() {
        api.button({
            id: 'saveAndAdd',
            name: '保存并新增',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(0), !1;
            }
        }, {
            id: 'confirm',
            name: '保存',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(1), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        if (rowData.id) {
            $('#name').val(rowData.name),
            $('#joinDate').val(rowData.joinDate),
            $('#birthday').val(rowData.birthday),
            $('#nativePlace').val(rowData.nativePlace),
            $('#school').val(rowData.school),
            $('#major').val(rowData.major),
            $('#sfzNo').val(rowData.sfzNo),
            $('#nowAddress').val(rowData.nowAddress),
            $('#homeAddress').val(rowData.homeAddress),
            $('#wechat').val(rowData.wechat),
            $('#mobile').val(rowData.mobile),
            $('#salary').val(rowData.salary),
            $('#workOneTime').val(rowData.workOneTime),
            $('#workOneCompany').val(rowData.workOneCompany),
            $('#workOnePosition').val(rowData.workOnePosition),
            $('#workOneEndReason').val(rowData.workOneEndReason),
            $('#workOneWitness').val(rowData.workOneWitness),
            $('#workTwoTime').val(rowData.workTwoTime),
            $('#workTwoCompany').val(rowData.workTwoCompany),
            $('#workTwoPosition').val(rowData.workTwoPosition),
            $('#workTwoEndReason').val(rowData.workTwoEndReason),
            $('#workTwoWitness').val(rowData.workTwoWitness),
            $('#familyOneRelationship').val(rowData.familyOneRelationship),
            $('#familyOneName').val(rowData.familyOneName),
            $('#familyOneWorkPlace').val(rowData.familyOneWorkPlace),
            $('#familyOnePosition').val(rowData.familyOnePosition),
            $('#familyOnePhone').val(rowData.familyOnePhone),
            $('#familyTwoRelationship').val(rowData.familyTwoRelationship),
            $('#familyTwoName').val(rowData.familyTwoName),
            $('#familyTwoWorkPlace').val(rowData.familyTwoWorkPlace),
            $('#familyTwoPosition').val(rowData.familyTwoPosition),
            $('#familyTwoPhone').val(rowData.familyTwoPhone),
            $('#urgentName').val(rowData.urgentName),
            $('#urgentPhone').val(rowData.urgentPhone),
            $('#urgentRelationship').val(rowData.urgentRelationship),
            $('#selfEvaluationOne').val(rowData.selfEvaluationOne),
            $('#selfEvaluationTwo').val(rowData.selfEvaluationTwo);
        }
    },
    initCombo: function() {
        channelCombo = $('#channel').combo({
            data: [{id: 0, name: '网上招聘'}, {id: 1, name: '现场招聘'}, {id: 2, name: '亲朋介绍'}, {id: 3, name: '自询自荐'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.channel ? ['name', rowData.channel] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        departmentCombo = $('#department').combo({
            data: '/Base/Category/list?typeNumber=departmenttype',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.department ? ['name', rowData.department] : -1,
            width: 310,
            listHeight: 200,
            callback: {
                onChange: function() {
                    if (this._selectedIndex > -1) { // 如果选择了具体部门，才动态加载对应部门的职位
                        Public.ajaxGet('/Base/Category/list?typeNumber=positiontype&parentId=' + this.formattedData[this._selectedIndex].value, {}, function(res) {
                            if (res && 200 == res.status) {
                                positionCombo.loadData(res.data.rows, rowData.position ? ['name', rowData.position] : -1, false);
                            }
                        });
                    }
                }
            }
        }).getCombo();
        positionCombo = $('#position').combo({
            data: [],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.position ? ['name', rowData.position] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        identityCombo = $('#identity').combo({
            data: [{id: 0, name: '实习生'}, {id: 1, name: '社会人士'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.identity ? ['name', rowData.identity] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        sexCombo = $('#sex').combo({
            data: [{id: 0, name: '男'}, {id: 1, name: '女'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.sex ? ['name', rowData.sex] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        nationCombo = $('#nation').combo({
            data: [{id: 1, name: '汉族'}, {id: 2, name: '壮族'}, {id: 3, name: '满族'}, {id: 4, name: '回族'}, {id: 5, name: '苗族'}, {id: 6, name: '维吾尔族'}, {id: 7, name: '土家族'}, {id: 8, name: '彝族'}, {id: 9, name: '蒙古族'}, {id: 10, name: '藏族'}, {id: 11, name: '布依族'}, {id: 12, name: '侗族'}, {id: 13, name: '瑶族'}, {id: 14, name: '朝鲜族'}, {id: 15, name: '白族'}, {id: 16, name: '哈尼族'}, {id: 17, name: '哈萨克族'}, {id: 18, name: '黎族'}, {id: 19, name: '傣族'}, {id: 20, name: '畲族'}, {id: 21, name: '傈僳族'}, {id: 22, name: '仡佬族'}, {id: 23, name: '东乡族'}, {id: 24, name: '高山族'}, {id: 25, name: '拉祜族'}, {id: 26, name: '水族'}, {id: 27, name: '佤族'}, {id: 28, name: '纳西族'}, {id: 29, name: '羌族'}, {id: 30, name: '土族'}, {id: 31, name: '仫佬族'}, {id: 32, name: '锡伯族'}, {id: 33, name: '柯尔克孜族'}, {id: 34, name: '达斡尔族'}, {id: 35, name: '景颇族'}, {id: 36, name: '毛南族'}, {id: 37, name: '撒拉族'}, {id: 38, name: '布朗族'}, {id: 39, name: '塔吉克族'}, {id: 40, name: '阿昌族'}, {id: 41, name: '普米族'}, {id: 42, name: '鄂温克族'}, {id: 43, name: '怒族'}, {id: 44, name: '京族'}, {id: 45, name: '基诺族'}, {id: 46, name: '德昂族'}, {id: 47, name: '保安族'}, {id: 48, name: '俄罗斯族'}, {id: 49, name: '裕固族'}, {id: 50, name: '乌孜别克族'}, {id: 51, name: '门巴族'}, {id: 52, name: '鄂伦春族'}, {id: 53, name: '独龙族'}, {id: 54, name: '塔塔尔族'}, {id: 55, name: '赫哲族'}, {id: 56, name: '珞巴族'}, {id: 57, name: '其他'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.nation ? ['name', rowData.nation] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        politicalCombo = $('#political').combo({
            data: [{id: 0, name: '群众'}, {id: 1, name: '共青团员'}, {id: 2, name: '共产党员'}, {id: 3, name: '其他政党'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.political ? ['name', rowData.political] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        criminalCombo = $('#criminal').combo({
            data: [{id: 0, name: '无'}, {id: 1, name: '有'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.criminal ? ['name', rowData.criminal] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        healthyCombo = $('#healthy').combo({
            data: [{id: 0, name: '很健康'}, {id: 1, name: '亚健康'}, {id: 2, name: '治疗中'}, {id: 3, name: '已康复'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.healthy ? ['name', rowData.healthy] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        marriageCombo = $('#marriage').combo({
            data: [{id: 0, name: '未婚'}, {id: 1, name: '已婚'}, {id: 2, name: '离异'}, {id: 3, name: '丧偶'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.marriage ? ['name', rowData.marriage] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        educationCombo = $('#education').combo({
            data: [{id: 0, name: '无'}, {id: 1, name: '小学'}, {id: 2, name: '初中'}, {id: 3, name: '高中（职高、中专）'}, {id: 4, name: '专科（高职、高专、高技）'}, {id: 5, name: '本科'}, {id: 6, name: '硕士研究生'}, {id: 7, name: '博士研究生'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.education ? ['name', rowData.education] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        entranceCombo = $('#entrance').combo({
            data: [{id: 0, name: '统招'}, {id: 1, name: '保送'}, {id: 2, name: '自考'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.entrance ? ['name', rowData.entrance] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        graduationCombo = $('#graduation').combo({
            data: [{id: 0, name: '已拿到毕业证'}, {id: 1, name: '已毕业未拿到毕业证'}, {id: 2, name: '应届毕业生学信网可查'}, {id: 3, name: '未毕业'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.graduation ? ['name', rowData.graduation] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        $('#channel').find('input').attr('name', 'channelInput');
        $('#department').find('input').attr('name', 'departmentInput');
        $('#position').find('input').attr('name', 'positionInput');
        $('#identity').find('input').attr('name', 'identityInput');
        $('#sex').find('input').attr('name', 'sexInput');
        $('#nation').find('input').attr('name', 'nationInput');
        $('#political').find('input').attr('name', 'politicalInput');
        $('#criminal').find('input').attr('name', 'criminalInput');
        $('#healthy').find('input').attr('name', 'healthyInput');
        $('#marriage').find('input').attr('name', 'marriageInput');
        $('#education').find('input').attr('name', 'educationInput');
        $('#entrance').find('input').attr('name', 'entranceInput');
        $('#graduation').find('input').attr('name', 'graduationInput');
    },
    initEvent: function() {
        laydate.render({
            elem: "#joinDate, #birthday"
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                channelInput: {required: !0},
                departmentInput: {required: !0},
                positionInput: {required: !0},
                joinDate: {required: !0},
                identityInput: {required: !0},
                sexInput: {required: !0},
                birthday: {required: !0},
                nativePlace: {required: !0},
                nationInput: {required: !0},
                politicalInput: {required: !0},
                criminalInput: {required: !0},
                healthyInput: {required: !0},
                marriageInput: {required: !0},
                educationInput: {required: !0},
                entranceInput: {required: !0},
                graduationInput: {required: !0},
                school: {required: !0},
                major: {required: !0},
                sfzNo: {required: !0},
                nowAddress: {required: !0},
                homeAddress: {required: !0},
                wechat: {required: !0},
                mobile: {required: !0},
                salary: {required: !0},
                urgentName: {required: !0},
                urgentPhone: {required: !0},
                urgentRelationship: {required: !0},
                selfEvaluationOne: {required: !0},
                selfEvaluationTwo: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('APPLICANT')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增应聘档案' : '编辑应聘档案';
            Public.ajaxPost('/Base/Applicant/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
        var data = {
            id: cRowId || 0,
            name: $.trim($('#name').val()),
            channel: channelCombo.getText(),
            department: departmentCombo.getText(),
            position: positionCombo.getText(),
            joinDate: $.trim($('#joinDate').val()),
            identity: identityCombo.getText(),
            sex: sexCombo.getText(),
            birthday: $.trim($('#birthday').val()),
            nativePlace: $.trim($('#nativePlace').val()),
            nation: nationCombo.getText(),
            political: politicalCombo.getText(),
            criminal: criminalCombo.getText(),
            healthy: healthyCombo.getText(),
            marriage: marriageCombo.getText(),
            education: educationCombo.getText(),
            entrance: entranceCombo.getText(),
            graduation: graduationCombo.getText(),
            school: $.trim($('#school').val()),
            major: $.trim($('#major').val()),
            sfzNo: $.trim($('#sfzNo').val()),
            nowAddress: $.trim($('#nowAddress').val()),
            homeAddress: $.trim($('#homeAddress').val()),
            wechat: $.trim($('#wechat').val()),
            mobile: $.trim($('#mobile').val()),
            salary: $.trim($('#salary').val()),
            urgentName: $.trim($('#urgentName').val()),
            urgentPhone: $.trim($('#urgentPhone').val()),
            urgentRelationship: $.trim($('#urgentRelationship').val()),
            selfEvaluationOne: $.trim($('#selfEvaluationOne').val()),
            selfEvaluationTwo: $.trim($('#selfEvaluationTwo').val()),
            workOneTime: $.trim($('#workOneTime').val()),
            workOneCompany: $.trim($('#workOneCompany').val()),
            workOnePosition: $.trim($('#workOnePosition').val()),
            workOneEndReason: $.trim($('#workOneEndReason').val()),
            workOneWitness: $.trim($('#workOneWitness').val()),
            workTwoTime: $.trim($('#workTwoTime').val()),
            workTwoCompany: $.trim($('#workTwoCompany').val()),
            workTwoPosition: $.trim($('#workTwoPosition').val()),
            workTwoEndReason: $.trim($('#workTwoEndReason').val()),
            workTwoWitness: $.trim($('#workTwoWitness').val()),
            familyOneRelationship: $.trim($('#familyOneRelationship').val()),
            familyOneName: $.trim($('#familyOneName').val()),
            familyOneWorkPlace: $.trim($('#familyOneWorkPlace').val()),
            familyOnePosition: $.trim($('#familyOnePosition').val()),
            familyOnePhone: $.trim($('#familyOnePhone').val()),
            familyTwoRelationship: $.trim($('#familyTwoRelationship').val()),
            familyTwoName: $.trim($('#familyTwoName').val()),
            familyTwoWorkPlace: $.trim($('#familyTwoWorkPlace').val()),
            familyTwoPosition: $.trim($('#familyTwoPosition').val()),
            familyTwoPhone: $.trim($('#familyTwoPhone').val())
        };
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑','新增'));
        }
        oper = 'add';
        cRowId = void 0;
        $('#name').val('');
        channelCombo._setDefaultSelected(-1);
        departmentCombo._setDefaultSelected(-1);
        positionCombo._setDefaultSelected(-1);
        $('#joinDate').val('');
        identityCombo._setDefaultSelected(-1);
        sexCombo._setDefaultSelected(-1);
        $('#birthday').val('');
        $('#nativePlace').val('');
        nationCombo._setDefaultSelected(-1);
        politicalCombo._setDefaultSelected(-1);
        criminalCombo._setDefaultSelected(-1);
        healthyCombo._setDefaultSelected(-1);
        marriageCombo._setDefaultSelected(-1);
        educationCombo._setDefaultSelected(-1);
        entranceCombo._setDefaultSelected(-1);
        graduationCombo._setDefaultSelected(-1);
        $('#school').val('');
        $('#major').val('');
        $('#sfzNo').val('');
        $('#nowAddress').val('');
        $('#homeAddress').val('');
        $('#wechat').val('');
        $('#mobile').val('');
        $('#salary').val('');
        $('#urgentName').val('');
        $('#urgentPhone').val('');
        $('#urgentRelationship').val('');
        $('#selfEvaluationOne').val('');
        $('#selfEvaluationTwo').val('');
        $('#workOneTime').val('');
        $('#workOneCompany').val('');
        $('#workOnePosition').val('');
        $('#workOneEndReason').val('');
        $('#workOneWitness').val('');
        $('#workTwoTime').val('');
        $('#workTwoCompany').val('');
        $('#workTwoPosition').val('');
        $('#workTwoEndReason').val('');
        $('#workTwoWitness').val('');
        $('#familyOneRelationship').val('');
        $('#familyOneName').val('');
        $('#familyOneWorkPlace').val('');
        $('#familyOnePosition').val('');
        $('#familyOnePhone').val('');
        $('#familyTwoRelationship').val('');
        $('#familyTwoName').val('');
        $('#familyTwoWorkPlace').val('');
        $('#familyTwoPosition').val('');
        $('#familyTwoPhone').val('');
    }
};
$(function() {
    THISPAGE.init();
});
