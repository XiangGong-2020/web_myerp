(function($) {
    $.fn.fSelect = function(options) {
        if (typeof options == 'string') {
            var settings = options;
        } else {
            var settings = $.extend({
                id: 'fselect',
                placeholder: '请选择', // 占位符，默认显示什么
                numDisplayed: 99, // 设置页面上可以显示几个选项（超出这个数量将会显示选择了n项）
                overflowText: '选择了{n}项', // 超出这个数量将会显示"选择了n项"
                searchText: '搜索', // 搜索框中占位符显示什么字
                showSearch: !0, // 是否显示搜索框
                width: 150, // 下拉框最大宽度
                listHeight: 125, // 下拉框最大高度
                disabled: false
            }, options);
        }
        function fSelect(select, settings) {
            this.$select = $(select);
            this.settings = settings;
            this.create();
        }
        fSelect.prototype = {
            create: function() {
                var multiple = this.$select.is('[multiple]') ? ' multiple' : '';
                this.$select.wrap('<div id="' + this.settings.id + '" class="fs-wrap' + multiple + '" style="width:' + this.settings.width + 'px"></div>');
                this.$select.before('<div class="fs-label-wrap"><input type="text" class="fs-input" name="fsInput" placeholder="' + this.settings.placeholder + '" readonly /><span class="fs-arrow iconfont icon-xiajiantou"></span></div>');
                this.$select.before('<div class="fs-dropdown hidden"><div class="fs-options" style="max-height:' + this.settings.listHeight + 'px"></div></div>');
                this.$select.addClass('visibility-hidden');
                this.$wrap = this.$select.closest('.fs-wrap');
                /* 添加全选和清空按钮 */
                var BtnFixed = '<div class="btnFixed"><button class="btn " id="selectAllBtn" type="button">全选</button><button class="btn btn-red" id="clearAllBtn" type="button">清空</button><button class="btn btn-gray" id="closeBtn" type="button">关闭</button></div>';
                this.$wrap.find('.fs-dropdown').append(BtnFixed);
                this.reload();
            },
            reload: function() {
                /* 如果设置显示搜索框，执行下面方法 */
                if (this.settings.showSearch) {
                    var search = '<div class="fs-search"><input type="search" class="fs-input-search" placeholder="' + this.settings.searchText + '" /></div>';
                    this.$wrap.find('.fs-dropdown').prepend(search);
                }
                if (this.settings.disabled) {
                    this.$wrap.find('.fs-input').attr('disabled', true).addClass('ui-input-dis');
                    this.$wrap.find('.fs-arrow').addClass('fs-arrow-dis');
                }
                var choices = this.buildOptions(this.$select);
                /* 获取到所有option选项 */
                this.$wrap.find('.fs-options').append(choices);
                /* 选中当前已选的值 */
                var valArr = this.$select.val();
                if (valArr) {
                    var $fsOption = this.$wrap.find('.fs-option');
                    valArr.forEach(function(currentValue) {
                        $fsOption.each(function() {
                            if (currentValue == $(this).attr('data-value')) {
                                $(this).addClass("selected");
                            }
                        });
                    });
                }
                /* 获取到选中状态的选项 */
                this.reloadDropdownLabel();
            },
            destroy: function() {
                this.$wrap.find('.fs-label-wrap').remove();
                this.$wrap.find('.fs-dropdown').remove();
                this.$select.unwrap().removeClass('visibility-hidden');
            },
            /* 获取select的option选项，并赋值到插件自定义的每一选项div里 */
            buildOptions: function($element) {
                var $this = this;
                var choices = '';
                $element.children().each(function(i, el) {
                    var $el = $(el);
                    /* 支持 optiongroup */
                    if ('optgroup' == $el.prop('nodeName').toLowerCase()) {
                        choices += '<div class="fs-optgroup">';
                        choices += '<div class="fs-optgroup-label">' + $el.prop('label') + '</div>';
                        choices += $this.buildOptions($el);
                        choices += '</div>';
                    } else {
                        var selected = $el.is('[selected]') ? ' selected' : '';
                        choices += '<div class="fs-option' + selected + '" data-value="' + $el.prop('value') + '"><span class="fs-checkbox"><i></i></span><div class="fs-option-label">' + $el.html() + '</div></div>';
                    }
                });
                return choices;
            },
            /* 每次加载时判断一下插件自定义的每一选项div哪些是选中状态，将选中状态对应的文字，push到一个数组labelText里，页面上显示出来的文字就是数组中获取到的文字 */
            reloadDropdownLabel: function() {
                var settings = this.settings;
                var labelText = [];
                this.$wrap.find('.fs-option.selected').each(function(i, el) {
                    labelText.push($(el).find('.fs-option-label').text());
                });
                if (labelText.length < 1) {
                    labelText = '';
                } else if (labelText.length > settings.numDisplayed) {
                    labelText = settings.overflowText.replace('{n}', labelText.length);
                } else {
                    labelText = labelText.join(',');
                }
                this.$wrap.find('.fs-input').val(labelText);
                this.$wrap.find('.fs-input').attr("title", labelText);
                this.$select.change();
            }
        };
        return this.each(function() {
            var data = $(this).data('fSelect');
            if (!data) {
                data = new fSelect(this, settings);
                $(this).data('fSelect', data);
            }
            if (typeof settings == 'string') {
                data[settings]();
            }
        });
    };
    window.fSelect = {
        'active': null,
        'idx': -1
    };
    function setIndexes($wrap) {
        $wrap.find('.fs-option:not(.hidden)').each(function(i, el) {
            $(el).attr('data-index', i);
            $wrap.find('.fs-option').removeClass('hl');
        });
        $wrap.find('.fs-search input').focus();
        window.fSelect.idx = -1;
    }
    /**滚轮滑动事件 */
    function setScroll($wrap) {
        var $container = $wrap.find('.fs-options');
        var $selected = $wrap.find('.fs-option.hl');
        var itemMin = $selected.offset().top + $container.scrollTop();
        var itemMax = itemMin + $selected.outerHeight();
        var containerMin = $container.offset().top + $container.scrollTop();
        var containerMax = containerMin + $container.outerHeight();
        if (itemMax > containerMax) { // scroll down
            var to = $container.scrollTop() + itemMax - containerMax;
            $container.scrollTop(to);
        } else if (itemMin < containerMin) { // scroll up
            var to = $container.scrollTop() - containerMin - itemMin;
            $container.scrollTop(to);
        }
    }
    /* 插件选项的点击事件 */
    $(document).on('click', '.fs-option', function() {
        var $wrap = $(this).closest('.fs-wrap');
        if ($wrap.hasClass('multiple')) {
            var selected = [];
            $(this).toggleClass('selected');
            $wrap.find('.fs-option.selected').each(function(i, el) {
                selected.push($(el).attr('data-value'));
            });
        } else {
            var selected = $(this).attr('data-value');
            $wrap.find('.fs-option').removeClass('selected');
            $(this).addClass('selected');
            $wrap.removeClass('fs-wrap-active').find('.fs-dropdown').hide();
        }
        $wrap.find('select').val(selected);
        $wrap.find('select').fSelect('reloadDropdownLabel');
    });
    /**搜索框输入事件 */
    $(document).on('keyup', '.fs-search input', function(e) {
        if (40 == e.which) {
            $(this).blur();
            return;
        }
        var $wrap = $(this).closest('.fs-wrap');
        var keywords = $(this).val();
        $wrap.find('.fs-option, .fs-optgroup-label').removeClass('hidden');
        if ('' != keywords) {
            $wrap.find('.fs-option').each(function() {
                var regex = new RegExp(keywords, 'gi');
                if (null === $(this).find('.fs-option-label').text().match(regex)) {
                    $(this).addClass('hidden');
                }
            });
            $wrap.find('.fs-optgroup-label').each(function() {
                var num_visible = $(this).closest('.fs-optgroup').find('.fs-option:not(.hidden)').length;
                if (num_visible < 1) {
                    $(this).addClass('hidden');
                }
            });
        }
        setIndexes($wrap);
    });
    /* 显示 | 隐藏下拉选项 */
    $(document).on('click', function(e) {
        var el = e.target || e.srcElement;
        if (0 == $(el).closest('.fs-wrap').length) { //点击目标是fs-wrap外区域，自动隐藏下拉框、去掉active样式
            $('.fs-dropdown').addClass('hidden');
            $('.fs-wrap').removeClass('active');
        } else { //点击目标是fs-wrap内区域，且是fs-label-wrap区域，且没有禁用状态
            if ($(el).closest('.fs-label-wrap').length > 0 && $(el).closest('.fs-label-wrap').find('.fs-arrow-dis').length == 0) {
                var $wrap = $(el).closest('.fs-wrap');
                if (0 < $wrap.length) {
                    window.fSelect.active = $wrap;
                    var is_hidden = $wrap.find('.fs-dropdown').hasClass('hidden');
                    if (is_hidden) {
                        $wrap.addClass('active').find('.fs-dropdown').removeClass('hidden');
                    } else {
                        $wrap.removeClass('active').find('.fs-dropdown').addClass('hidden');
                    }
                    setIndexes($wrap);
                } else {
                    $('.fs-dropdown').addClass('hidden');
                    $('.fs-dropdown').closest('.fs-wrap').removeClass('active');
                    window.fSelect.active = null;
                }
            }
        }
    });
    /* 上箭头、下箭头、空格、回车按键事件 */
    $(document).on('keydown', function(e) {
        var $wrap = window.fSelect.active;
        if (null === $wrap) {
            return;
        } else if (38 == e.which) { // up
            e.preventDefault();
            $wrap.find('.fs-option').removeClass('hl');
            if (window.fSelect.idx > 0) {
                window.fSelect.idx--;
                $wrap.find('.fs-option[data-index=' + window.fSelect.idx + ']').addClass('hl');
                setScroll($wrap);
            } else {
                window.fSelect.idx = -1;
                $wrap.find('.fs-search input').focus();
            }
        } else if (40 == e.which) { // down
            e.preventDefault();
            var last_index = $wrap.find('.fs-option:last').attr('data-index');
            if (window.fSelect.idx < parseInt(last_index)) {
                window.fSelect.idx++;
                $wrap.find('.fs-option').removeClass('hl');
                $wrap.find('.fs-option[data-index=' + window.fSelect.idx + ']').addClass('hl');
                setScroll($wrap);
            }
        } else if (32 == e.which || 13 == e.which) { // space, enter
            $wrap.find('.fs-option.hl').click();
        } else if (27 == e.which) { // esc
            $('.fs-dropdown').addClass('hidden');
            $('.fs-dropdown').closest('.fs-wrap').removeClass('active');
            window.fSelect.active = null;
        }
    });
    /* 全选按钮 */
    $(document).on('click', '#selectAllBtn', function() {
        var $wrap = $(this).closest('.fs-wrap');
        if ($wrap.hasClass('multiple')) {
            var selected = [];
            $wrap.find(".fs-option").addClass('selected');
            $wrap.find('.fs-option.selected').each(function(i, el) {
                selected.push($(el).attr('data-value'));
            });
        }
        $wrap.find('select').val(selected);
        $wrap.find('select').fSelect('reloadDropdownLabel');
    });
    /* 清空按钮 */
    $(document).on('click', '#clearAllBtn', function() {
        var $wrap = $(this).closest('.fs-wrap');
        if ($wrap.hasClass('multiple')) {
            var selected = [];
            $wrap.find(".fs-option").removeClass('selected');
            $wrap.find('.fs-option.selected').each(function(i, el) {
                selected.push($(el).attr('data-value'));
            });
        }
        $wrap.find('select').val(selected);
        $wrap.find('select').fSelect('reloadDropdownLabel');
    });
    /* 关闭按钮 */
    $(document).on('click', '#closeBtn', function() {
        $(this).closest('.fs-dropdown').addClass('hidden');
        $(this).closest('.fs-wrap').removeClass('active');
    });
})(jQuery);
