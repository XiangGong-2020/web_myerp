var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var sexCombo, companyCombo, departmentCombo, positionCombo, superiorsCombo, educationCombo, animalCombo, astroCombo, nationCombo, hukouCombo, isDisableCombo, isBuySocialSecurityCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/User/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initCombo();
                    THISPAGE.initEvent();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
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
        rowData.id && (
            $('#name').val(rowData.name),
            $('#joinDate').val('0000-00-00' == rowData.joinDate ? '' : rowData.joinDate),
            $('#sfzNo').val(rowData.sfzNo),
            $('#bankNo').val(rowData.bankNo),
            $('#zfbNo').val(rowData.zfbNo),
            $('#wechat').val(rowData.wechat),
            $('#mobile').val(rowData.mobile),
            $('#birthdaySolar').val(rowData.birthdaySolar),
            $('#birthdayLunar').val(rowData.birthdayLunar),
            $('#homeAddress').val(rowData.homeAddress),
            $('#school').val(rowData.school),
            $('#major').val(rowData.major),
            $('#fixedSalary').val(rowData.fixedSalary > 0 ? rowData.fixedSalary : ''),
            $('#bonusPerson').val(rowData.bonusPerson > 0 ? rowData.bonusPerson : ''),
            $('#leaveDate').val('0000-00-00' == rowData.leaveDate ? '' : rowData.leaveDate),
            $('#buySocialSecurityDate').val('0000-00-00' == rowData.buySocialSecurityDate ? '' : rowData.buySocialSecurityDate),
            $('#stopSocialSecurityDate').val('0000-00-00' == rowData.stopSocialSecurityDate ? '' : rowData.stopSocialSecurityDate),
            $('#duty').val(rowData.duty),
            $('#description').val(rowData.description)
        );
    },
    initCombo: function() {
        sexCombo = $('#sex').combo({
            data: [{id: 0, name: '男'}, {id: 1, name: '女'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.sex ? ['name', rowData.sex] : -1
        }).getCombo();
        companyCombo = $('#company').combo({
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.company ? ['name', rowData.company] : -1
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
            defaultSelected: rowData.position ? ['name', rowData.position] : -1
        }).getCombo();
        superiorsCombo = $('#superiors').combo({
            data: '/Base/User/queryAll?isDisable=0&normal=yes',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows.unshift({
                        id: -1,
                        name: '无'
                    }), a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.superiorsName ? ['name', rowData.superiorsName] : 0
        }).getCombo();
        nationCombo = $('#nation').combo({
            data: [{id: 1, name: '汉族'}, {id: 2, name: '壮族'}, {id: 3, name: '满族'}, {id: 4, name: '回族'}, {id: 5, name: '苗族'}, {id: 6, name: '维吾尔族'}, {id: 7, name: '土家族'}, {id: 8, name: '彝族'}, {id: 9, name: '蒙古族'}, {id: 10, name: '藏族'}, {id: 11, name: '布依族'}, {id: 12, name: '侗族'}, {id: 13, name: '瑶族'}, {id: 14, name: '朝鲜族'}, {id: 15, name: '白族'}, {id: 16, name: '哈尼族'}, {id: 17, name: '哈萨克族'}, {id: 18, name: '黎族'}, {id: 19, name: '傣族'}, {id: 20, name: '畲族'}, {id: 21, name: '傈僳族'}, {id: 22, name: '仡佬族'}, {id: 23, name: '东乡族'}, {id: 24, name: '高山族'}, {id: 25, name: '拉祜族'}, {id: 26, name: '水族'}, {id: 27, name: '佤族'}, {id: 28, name: '纳西族'}, {id: 29, name: '羌族'}, {id: 30, name: '土族'}, {id: 31, name: '仫佬族'}, {id: 32, name: '锡伯族'}, {id: 33, name: '柯尔克孜族'}, {id: 34, name: '达斡尔族'}, {id: 35, name: '景颇族'}, {id: 36, name: '毛南族'}, {id: 37, name: '撒拉族'}, {id: 38, name: '布朗族'}, {id: 39, name: '塔吉克族'}, {id: 40, name: '阿昌族'}, {id: 41, name: '普米族'}, {id: 42, name: '鄂温克族'}, {id: 43, name: '怒族'}, {id: 44, name: '京族'}, {id: 45, name: '基诺族'}, {id: 46, name: '德昂族'}, {id: 47, name: '保安族'}, {id: 48, name: '俄罗斯族'}, {id: 49, name: '裕固族'}, {id: 50, name: '乌孜别克族'}, {id: 51, name: '门巴族'}, {id: 52, name: '鄂伦春族'}, {id: 53, name: '独龙族'}, {id: 54, name: '塔塔尔族'}, {id: 55, name: '赫哲族'}, {id: 56, name: '珞巴族'}, {id: 57, name: '其他'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.nation ? ['name', rowData.nation] : -1
        }).getCombo();
        hukouCombo = $('#hukou').combo({
            data: [{id: 0, name: '本市城镇'}, {id: 1, name: '本市农村'}, {id: 2, name: '外市城镇'}, {id: 3, name: '外市农村'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.hukou ? ['name', rowData.hukou] : -1
        }).getCombo();
        animalCombo = $('#animal').combo({
            data: [{id: 0, name: '鼠'}, {id: 1, name: '牛'}, {id: 2, name: '虎'}, {id: 3, name: '兔'}, {id: 4, name: '龙'}, {id: 5, name: '蛇'}, {id: 6, name: '马'}, {id: 7, name: '羊'}, {id: 8, name: '猴'}, {id: 9, name: '鸡'}, {id: 10, name: '狗'}, {id: 11, name: '猪'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.animal ? ['name', rowData.animal] : -1
        }).getCombo();
        astroCombo = $('#astro').combo({
            data: [{id: 0, name: '水瓶座'}, {id: 1, name: '双鱼座'}, {id: 2, name: '白羊座'}, {id: 3, name: '金牛座'}, {id: 4, name: '双子座'}, {id: 5, name: '巨蟹座'}, {id: 6, name: '狮子座'}, {id: 7, name: '处女座'}, {id: 8, name: '天秤座'}, {id: 9, name: '天蝎座'}, {id: 10, name: '射手座'}, {id: 11, name: '摩羯座'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.astro ? ['name', rowData.astro] : -1
        }).getCombo();
        educationCombo = $('#education').combo({
            data: [{id: 0, name: '无'}, {id: 1, name: '小学'}, {id: 2, name: '初中'}, {id: 3, name: '高中（职高、中专）'}, {id: 4, name: '专科（高职、高专、高技）'}, {id: 5, name: '本科'}, {id: 6, name: '硕士研究生'}, {id: 7, name: '博士研究生'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.education ? ['name', rowData.education] : -1,
            width: 310,
            listHeight: 200
        }).getCombo();
        isDisableCombo = $('#isDisable').combo({
            data: [{id: 0, name: '在职'}, {id: 1, name: '已离职'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.isDisable >= 0 ? ['id', parseInt(rowData.isDisable)] : -1
        }).getCombo();
        isBuySocialSecurityCombo = $('#isBuySocialSecurity').combo({
            data: [{id: 0, name: '未参保'}, {id: 1, name: '已参保'}, {id: 2, name: '已停保'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.isBuySocialSecurity >= 0 ? ['id', parseInt(rowData.isBuySocialSecurity)] : -1
        }).getCombo();
        $('#sex').find('input').attr('name', 'sexInput');
        $('#company').find('input').attr('name', 'companyInput');
        $('#department').find('input').attr('name', 'departmentInput');
        $('#position').find('input').attr('name', 'positionInput');
        $('#superiors').find('input').attr('name', 'superiorsInput');
        $('#nation').find('input').attr('name', 'nationInput');
        $('#hukou').find('input').attr('name', 'hukouInput');
        $('#isDisable').find('input').attr('name', 'isDisableInput');
        $('#isBuySocialSecurity').find('input').attr('name', 'isBuySocialSecurityInput');
    },
    initEvent: function() {
        laydate.render({
            elem: "#joinDate, #leaveDate, #buySocialSecurityDate, #stopSocialSecurityDate"
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                sexInput: {required: !0},
                joinDate: {required: !0},
                companyInput: {required: !0},
                departmentInput: {required: !0},
                positionInput: {required: !0},
                superiorsInput: {required: !0},
                sfzNo: {required: !0},
                mobile: {required: !0},
                isDisableInput: {required: !0},
                isBuySocialSecurityInput: {required: !0},
                duty: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('USER_STAFF_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增员工档案' : '编辑员工档案';
            var data = THISPAGE.getData();
            Public.ajaxPost('/Base/User/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (res && 200 == res.status) {
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
        var sfzNo = $.trim($('#sfzNo').val());
        var birthday_sfz_lunar = {};
        if (sfzNo.length > 15) {
            var birthday_sfz = sfzNo.substring(6, 14);
            birthday_sfz_lunar = calendar.solar2lunar(birthday_sfz.substring(0, 4), birthday_sfz.substring(4, 6), birthday_sfz.substring(6, 8));
        }
        var animal = Object.keys(birthday_sfz_lunar).length > 0 ? birthday_sfz_lunar.animal : '';
        var astro = Object.keys(birthday_sfz_lunar).length > 0 ? birthday_sfz_lunar.astro : '';
        var birthdaySolar = Object.keys(birthday_sfz_lunar).length > 0 ? birthday_sfz_lunar.sMonth + '月' + birthday_sfz_lunar.sDay + '日' : '';
        var birthdayLunar = Object.keys(birthday_sfz_lunar).length > 0 ? birthday_sfz_lunar.lMonthCn + birthday_sfz_lunar.lDayCn : '';
        var data = {
            id: cRowId || 0,
            name: $.trim($('#name').val()),
            sex: sexCombo.getText() || '',
            joinDate: $.trim($('#joinDate').val()),
            company: companyCombo.getText() || '',
            department: departmentCombo.getText() || '',
            position: positionCombo.getText() || '',
            superiorsId: superiorsCombo.getValue() == -1 ? 0 : superiorsCombo.getValue(),
            superiorsName: superiorsCombo.getText() == '无' ? '' : superiorsCombo.getText(),
            nation: nationCombo.getText() || '',
            hukou: hukouCombo.getText() || '',
            sfzNo: sfzNo,
            bankNo: $.trim($('#bankNo').val()),
            zfbNo: $.trim($('#zfbNo').val()),
            mobile: $.trim($('#mobile').val()),
            wechat: $.trim($('#wechat').val()),
            animal: animalCombo.getText() || animal,
            astro: astroCombo.getText() || astro,
            birthdaySolar: $.trim($('#birthdaySolar').val()) || birthdaySolar,
            birthdayLunar: $.trim($('#birthdayLunar').val()) || birthdayLunar,
            education: educationCombo.getText() || '',
            school: $.trim($('#school').val()),
            major: $.trim($('#major').val()),
            homeAddress: $.trim($('#homeAddress').val()),
            fixedSalary: parseInt($.trim($('#fixedSalary').val())),
            bonusPerson: parseInt($.trim($('#bonusPerson').val())),
            isDisable: isDisableCombo.getValue() || 0,
            leaveDate: $.trim($('#leaveDate').val()),
            isBuySocialSecurity: isBuySocialSecurityCombo.getValue() || 0,
            buySocialSecurityDate: $.trim($('#buySocialSecurityDate').val()),
            stopSocialSecurityDate: $.trim($('#stopSocialSecurityDate').val()),
            duty: $.trim($('#duty').val()),
            description: $.trim($('#description').val())
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
        sexCombo._setDefaultSelected(-1);
        $('#joinDate').val('');
        companyCombo._setDefaultSelected(-1);
        departmentCombo._setDefaultSelected(-1);
        positionCombo._setDefaultSelected(-1);
        superiorsCombo._setDefaultSelected(-1);
        nationCombo._setDefaultSelected(-1);
        hukouCombo._setDefaultSelected(-1);
        $('#sfzNo').val('');
        $('#bankNo').val('');
        $('#zfbNo').val('');
        $('#mobile').val('');
        $('#wechat').val('');
        animalCombo._setDefaultSelected(-1);
        astroCombo._setDefaultSelected(-1);
        $('#birthdaySolar').val('');
        $('#birthdayLunar').val('');
        educationCombo._setDefaultSelected(-1);
        $('#school').val('');
        $('#major').val('');
        $('#homeAddress').val('');
        $('#fixedSalary').val('');
        $('#bonusPerson').val('');
        isDisableCombo._setDefaultSelected(-1);
        $('#leaveDate').val('');
        isBuySocialSecurityCombo._setDefaultSelected(-1);
        $('#buySocialSecurityDate').val('');
        $('#stopSocialSecurityDate').val('');
        $('#duty').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
