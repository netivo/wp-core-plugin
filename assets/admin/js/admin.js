(function ($) {
    $.fn.wpUpload = function () {
        var $el = null;
        var $input =  null;
        var $uploadButton =  null;
        var $image =  null;
        var inputName = null;
        var inputId = null;
        var imageUrl = null;
        var inputPlaceholder = null;

        var init = function () {
            inputName = $el.data("name");
            if(typeof inputName === 'undefined') inputName = null;
            inputId = $el.data("id");
            if(typeof inputId === 'undefined') inputId = null;
            imageUrl = $el.data("value");
            if(typeof imageUrl === 'undefined') imageUrl = null;
            inputPlaceholder = $el.data('placeholder');
            if(typeof inputPlaceholder === 'undefined') inputPlaceholder = null;
            prepareHtml();
            prepareActions();
        };

        var prepareHtml = function () {
            $input = $('<input/>').attr('type','text').attr('class','input').addClass('input wp-upload__input');
            if(inputName !== null){
                $input.attr('name',inputName);
            }
            if(inputId !== null){
                $input.attr('id',inputId);
            }
            if(inputPlaceholder !== null){
                $input.attr('placeholder', inputPlaceholder);
            }
            if(imageUrl !== null){
                $input.val(imageUrl);
                if(imageUrl.toString().toLowerCase().match(/\.(jpg|jpeg|png|gif|bmp)/g)){
                    $image = $('<img/>').attr('src', imageUrl).addClass('wp-upload__image');
                }
            }
            $uploadButton = $('<button>Załaduj</button>').addClass('btn wp-upload__btn');
            $el.append($input).append($uploadButton);
            if(imageUrl !== null){
                $el.append($image);
            }
        };

        var prepareActions = function (){
            $input.on('change, keyup', inputChange);
            $uploadButton.on('click', buttonClick);
        };
        var inputChange = function (e) {
            if($(this).val().toLowerCase().match(/\.(jpg|jpeg|png|gif|bmp)/g)){
                imageUrl = $(this).val();
                if($image === null){
                    $image = $('<img/>').addClass('wp-upload__image');
                    $el.append($image);

                }
                $image.attr('src', imageUrl);

            }
        };

        var buttonClick = function (e) {
            e.preventDefault();
            var image = wp.media({
                title: 'Upload Image',
                multiple: false
            }).open()
                .on('select', function(e){
                    var uploaded_image = image.state().get('selection').first();
                    imageUrl = uploaded_image.toJSON().url;
                    $input.val(imageUrl);
                    if(imageUrl.toString().toLowerCase().match(/\.(jpg|jpeg|png|gif|bmp)/g)){
                        if($image === null){
                            $image = $('<img/>').addClass('wp-upload__image');
                            $el.append($image);
                        }
                        $image.attr('src', imageUrl);

                    }
                });
        };

        if(this.length > 0){
            if(this.length > 1){
                this.each(function(){
                    $(this).wpUpload();
                })
            } else {
                $el = this;
                init();
            }
        }
    }
})(jQuery);
(function ($) {
    $.fn.dynamicTabs = function () {
        var $el = null;
        var $nav = null;
        var $content = null;
        var $navElements = null;
        var $contentElements = null;

        var init = function () {
            $nav = $el.find('[data-element="nav"]');
            $content = $el.find('[data-element="content"]');

            $navElements = $nav.find('[data-toggle]');
            $contentElements = $content.find('[data-target]');

            prepareActions();
        };

        var prepareActions = function () {
            $nav.on('click', '[data-toggle]', tabClick);
        };

        var tabClick = function (e) {
            e.preventDefault();
            $navElements.each(function () {
                if($(this).hasClass('navbar__tabs-element')) $(this).removeClass('navbar__tabs-element--active');
                else $(this).closest('.navbar__tabs-element').removeClass('navbar__tabs-element--active');
            });
            $contentElements.removeClass('tabs__content--active');

            var tglElement = $(this).data('toggle');
            var cntElement = $content.find('[data-target="' + tglElement + '"]');
            cntElement.addClass('tabs__content--active');
            if($(this).hasClass('navbar__tabs-element')) $(this).addClass('navbar__tabs-element--active');
            else $(this).closest('.navbar__tabs-element').addClass('navbar__tabs-element--active');
        };

        if (this.length > 0) {
            if (this.length > 1) {
                this.each(function () {
                    $(this).dynamicTabs();
                });
            } else {
                $el = this;
                init();
            }
        }
    };
}(jQuery));
(function ($) {
    $.fn.dynamicList = function () {
        var $el = null;
        var $select = null;
        var $button = null;
        var $container = null;
        var count = 0;
        var inputName = null;


        var init = function () {
            $select = $el.find('[data-element="select"]');
            $button = $el.find('[data-element="button"]');
            $container = $el.find('[data-element="container"]');

            inputName = $el.data("name");
            if (typeof inputName === 'undefined') inputName = null;

            fillSelected();
            $button.on('click', buttonClick);
            $container.on('click', '.delete', buttonDelete);
            $container.on('click', '.move-up', boxOrder);
            $container.on('click', '.move-down', boxOrder);
        };

        var fillSelected = function () {
           var $selected = $select.find('[data-selected]');
           $selected.each(function () {
               var order = $(this).data('selected');
               var value = $(this).val();
               var text = $(this).text();
               order = order.toString().split(',');

               $.each(order, function () {
                   var $div = $('<div></div>').attr('class', 'drag-el').attr('data-id', value).data('order', this );
                   $div.append($('<span></span>').html(text));
                   if (inputName !== null) {
                       $div.append($('<input/>').attr('type', 'hidden').attr('name', inputName + '[' + this + ']').val(value));
                   }
                   $div.append($('<a></a>').attr('class', 'delete').html('usuń'));
                   $div.append($('<a></a>').attr('class', 'move-up').html('w górę'));
                   $div.append($('<a></a>').attr('class', 'move-down').html('w dół'));
                   $container.append($div);
                   count++;
               });

           });
           boxReorder();
        };


        var buttonClick = function (e) {
            e.preventDefault();

            var $selectEl = $select.find('option:selected');
            if ($selectEl.length > 0) {
                var $div = $('<div></div>').attr('class', 'drag-el').attr('data-id', $selectEl.val()).data('order', count);
                $div.append($('<span></span>').html($selectEl.text()));
                if (inputName !== null) {
                    $div.append($('<input/>').attr('type', 'hidden').attr('name', inputName + '[' + count + ']').val($selectEl.val()));
                }
                $div.append($('<a></a>').attr('class', 'delete').html('usuń'));
                $div.append($('<a></a>').attr('class', 'move-up').html('w górę'));
                $div.append($('<a></a>').attr('class', 'move-down').html('w dół'));
                $container.append($div);

                count++;
            }
            else {
                alert('Musisz wybrać element z listy!')
            }
        };

        var buttonDelete = function (e) {
            e.preventDefault();
            var $buttonBoxParent = $(this).closest('.drag-el');
            var deleteId = $buttonBoxParent.data('order');
            if (deleteId < count - 1) {
                var containerEl = $container.find('.drag-el');
                containerEl.each(function () {
                    var order = $(this).data('order');
                    if (order > deleteId) {
                        order--;
                        $(this).data('order', order);
                        var $input = $(this).find('input');
                        if ($input.length > 0) {
                            $input.attr('name', inputName + '[' + order + ']');
                        }
                    }
                });
            }
            $buttonBoxParent.remove();
            count--;
        };

        var boxOrder = function (e) {
            e.preventDefault();
            var $buttonBoxParent = $(this).closest('.drag-el');
            var elementId = $buttonBoxParent.data('order');
            var containerEl = $container.find('.drag-el');
            if($(this).attr('class') === 'move-up'){
                if(elementId > 0 ){
                    elementId--;
                    containerEl.each(function () {
                        var order = $(this).data('order');
                        if (order === elementId) {
                            order++;
                            $(this).data('order', order);
                            var $input = $(this).find('input');
                            if ($input.length > 0) {
                                $input.attr('name', inputName + '[' + order + ']');
                            }
                        }
                    });
                }
            }
            else if($(this).attr('class') === 'move-down') {
                if (elementId < count-1){
                    elementId++;
                    containerEl.each(function () {
                        var order = $(this).data('order');
                        if (order === elementId) {
                            order--;
                            $(this).data('order', order);
                            var $input = $(this).find('input');
                            if ($input.length > 0) {
                                $input.attr('name', inputName + '[' + order + ']');
                            }
                        }
                    });
                }
            }
            $buttonBoxParent.data('order', elementId);
            var $input = $buttonBoxParent.find('input');
            if ($input.length > 0) {
                $input.attr('name', inputName + '[' + elementId + ']');
            }
            boxReorder();
        };

        var boxReorder = function () {
            var containerEl = $container.find('.drag-el').toArray();
            containerEl.sort(sortSections);

            $container.html('');
            for (var i = 0; i<containerEl.length; i++){
                var $el = $(containerEl[i]);
                $el.data('order', i);
                $container.append($el);
            }


            function sortSections(a, b){
                var no1 = $(a).data('order');
                var no2 = $(b).data('order');
                return (no1 === no2) ? 0 : (no1 > no2) ? 1 : -1;
            }
        };

        if (this.length > 0) {
            if (this.length > 1) {
                this.each(function () {
                    $(this).dynamicList();
                });
            } else {
                $el = this;
                init();
            }
        }
    };
}(jQuery));
(function ($) {
    $.fn.dynamicSelect = function () {
        var $el = null;
        var $select = null;
        var $container = null;
        var $containerElements = null;

        var init = function () {
            $select = $el.find('[data-element="select"]');
            $container = $el.find('[data-element="container"]');
            $containerElements = $container.find('[data-element-select]');
            $containerElements.addClass('display-none');
            prepare();
            selectChange();
        };
        var prepare = function () {
            $select.on('change', selectChange);
        };
        var selectChange = function () {
            $containerElements.addClass('display-none');
            var $selectData = $select.find('option:selected').data('select');
            $containerElements.each(function () {
                var dataElSelect = $(this).data('element-select');
                if ($selectData === dataElSelect) {
                    $(this).removeClass('display-none');
                }
            })
        };
        if (this.length > 0) {
            if (this.length > 1) {
                this.each(function () {
                    $(this).dynamicSelect();
                });
            } else {
                $el = this;
                init();
            }
        }
    }
}(jQuery));
(function ($) {
    var defaults = {
        fields: null,
        name: 'some-name',
        values: []
    };

    $.fn.dynamicBox = function (options) {
        var $el = null;
        var $container = null;
        var $btn = null;
        var $boxContainer = null;
        var globalOptions;
        var count = 0;
        var $deleteBtn = null;

        var init = function () {
            $container = $el.find('[data-element="container"]');
            $btn = $el.find('[data-element="button"]');
            $boxContainer = $('<div></div>');
            $container.append($boxContainer);
            prepare();
            createBoxes();
        };
        var initOptions = function (options) {
            globalOptions = $.extend({}, defaults, options);

            if (typeof(globalOptions.fields) === 'object') {
                if ($.type(globalOptions.fields) === 'array') {
                    var tmp = {};
                    $.each(globalOptions.fields, function () {
                        if (this.hasOwnProperty('name')) {
                            tmp[this.name] = this;
                        }
                    });
                    globalOptions.fields = tmp;

                } else {
                    if (globalOptions.fields.hasOwnProperty('name')) {
                        var tmp = globalOptions.fields;
                        globalOptions.fields = {};
                        globalOptions.fields[tmp.name] = tmp;
                    } else {
                        $.each(globalOptions.fields, function (key, value) {
                            if (!value.hasOwnProperty('name')) {
                                value.name = key;
                            }
                        });
                    }
                }
            }
        };
        var prepare = function () {
            $btn.on('click', clickButton);
            $container.on('click', '.box__delete', deleteBox);
        };

        var createBoxes = function () {
            $.each(globalOptions.values, function () {
                var $gridRow = $boxContainer.find('.js-row:last-of-type');
                var $gridCol = $('<div></div>').addClass('grid__col grid__col--50');
                if ($gridRow.length === 0) {
                    $gridRow = $('<div></div>').addClass('grid__row js-row');
                    $boxContainer.append($gridRow);
                }
                if (count % 2 === 0 && count > 0) {
                    $gridRow = $('<div></div>').addClass('grid__row js-row');
                    $boxContainer.append($gridRow);
                }
                var $box = createBox(count, this);
                $gridCol.append($box);
                $gridRow.append($gridCol);
                count++;
            });
        };
        var clickButton = function (e) {
            e.preventDefault();
            var $gridRow = $boxContainer.find('.js-row:last-of-type');
            var $gridCol = $('<div></div>').addClass('grid__col grid__col--50');
            if ($gridRow.length === 0) {
                $gridRow = $('<div></div>').addClass('grid__row js-row');
                $boxContainer.append($gridRow);
            }
            if (count % 2 === 0 && count > 0) {
                $gridRow = $('<div></div>').addClass('grid__row js-row');
                $boxContainer.append($gridRow);
            }
            var $box = createBox(count);
            $gridCol.append($box);
            $gridRow.append($gridCol);
            count++;
        };

        var createBox = function (order, values) {
            var $box = $('<div></div>').addClass('box').attr('data-order', order);
            var $deletButton = $('<button></button>').addClass('box__delete').append('<span class="dashicons dashicons-no-alt"></span>');
            $box.append($deletButton);
            $boxContainer.addClass('box-section');
            if (globalOptions.fields !== null) {
                $.each(globalOptions.fields, function (key, options) {
                    var name = 'render' + capitalized(options.type) + 'Element';
                    var value = null;
                    if (typeof values !== 'undefined') {
                        $.each(values, function () {
                            if (this.hasOwnProperty('name')) {
                                if (this.name === options.name) {
                                    if (this.hasOwnProperty('value')) {
                                        if (options.type !== 'checkbox' && options.type !== 'radio') {
                                            value = this.value;
                                        } else if (options.type === 'radio') {
                                            if (options.hasOwnProperty('value')) {
                                                if (options.value === this.value) {
                                                    value = true;
                                                }
                                            } else {
                                                value = true;
                                            }
                                        } else if (options.type === 'checkbox') {
                                            if (options.hasOwnProperty('value')) {
                                                if ($.type(this.value) === 'array') {
                                                    if ($.inArray(options.value, this.value)) {
                                                        value = true;
                                                    }
                                                } else {
                                                    if (options.value === this.value) {
                                                        value = true;
                                                    }
                                                }
                                            } else {
                                                value = true;
                                            }
                                        }
                                    }
                                }
                            }
                        })
                    }
                    var $field = $el[name](options.name, order, options, value);
                    $box.append($field);
                })
            }
            // $deleteBtn = $box.find('.box__delete');
            // $deleteBtn.on('click', deleteBox);
            return $box;
        };

        var deleteBox = function (e) {
            e.preventDefault();
            var $box = $(this).closest('.box');
            var deleteId = $box.attr('data-order');
            if (deleteId < count - 1) {
                var containerEl = $container.find('.box');
                containerEl.each(function () {
                    var order = $(this).attr('data-order');
                    if (order > deleteId) {
                        order--;
                        $(this).attr('data-order', order);
                        var $fields = $(this).find('[name]');
                        if ($fields.length > 0) {
                            $fields.each(function () {
                                var name = $(this).attr('name');
                                name = name.replace(globalOptions.name, '');
                                var matches = name.match(/\[([^\]]+)\]/g);
                                matches[0] = '[' + order + ']';
                                name = globalOptions.name + matches.join('');
                                $(this).attr('name', name);
                            })
                        }
                    }
                });
            }
            $box.parent().remove();
            count--;
            if (count === 0) {
                $boxContainer.removeClass('box-section');
            }
            var allBoxes = $container.find('.box');
            redraw(allBoxes);
        };

        var redraw = function (boxes) {
            var values = [];
            $.each(boxes, function () {
                var tmp = [];
                var order = $(this).attr('data-order');
                var $box = $(this);
                $.each(globalOptions.fields, function (key, options) {
                    var name = options.name;
                    var arrName = name.match(/\[([^\]]+)\]/g);
                    $.each(arrName, function () {
                        name = name.replace(this, '');
                    });
                    var addName = '';
                    if (arrName !== null && typeof arrName !== 'undefined') {
                        if (arrName.length > 0) {
                            addName = arrName.join('');
                        }
                    }
                    var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
                    var $input = $box.find('[name="' + inputName + '"]');
                    tmp.push({
                        name: options.name,
                        value: $input.val()
                    });
                });
                values[order] = tmp;
            });
            console.log(values);
            $boxContainer.html('');
            $.each(values, function (key, value) {
                var $gridRow = $boxContainer.find('.js-row:last-of-type');
                var $gridCol = $('<div></div>').addClass('grid__col grid__col--50');
                if ($gridRow.length === 0) {
                    $gridRow = $('<div></div>').addClass('grid__row js-row');
                    $boxContainer.append($gridRow);
                }
                if (key % 2 === 0 && key > 0) {
                    $gridRow = $('<div></div>').addClass('grid__row js-row');
                    $boxContainer.append($gridRow);
                }
                var $box = createBox(key, value);
                $gridCol.append($box);
                $gridRow.append($gridCol);
            });
            // $boxContainer = $('<div></div>');
            // $boxContainer.addClass('box-section');
            // $container.append($boxContainer);
            // boxes.each(function () {
            //     var $gridRow = $boxContainer.find('.js-row:last-of-type');
            //     var $gridCol = $('<div></div>').addClass('grid__col grid__col--50');
            //     if ($gridRow.length === 0) {
            //         $gridRow = $('<div></div>').addClass('grid__row js-row');
            //         $boxContainer.append($gridRow);
            //     }
            //     if ($(this).data('order')%2 === 0 && $(this).data('order')>0 ){
            //         $gridRow = $('<div></div>').addClass('grid__row js-row');
            //         $boxContainer.append($gridRow);
            //     }
            //     $gridCol.append($(this));
            //     $gridRow.append($gridCol);
            // });
        };

        var capitalized = function (str) {
            if (str !== undefined && str.length > 1) {
                return str[0].toUpperCase() + str.slice(1);
            }
            else {
                return "";
            }
        };
        this.renderTextElement = function (name, order, args, value) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--30');
                var $label = $('<label></label>').html(args.label).addClass('label');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                if (args.hasOwnProperty('id')) {
                    $label.attr('for', args.id);
                }
                $labelContainer.append($label);
                $element.append($labelContainer);
                $inputContainer.addClass('grid__col grid__col--70');
            } else {
                $inputContainer.addClass('grid__col grid__col--100');
            }
            var $input = $('<input/>').addClass('input');
            if (args.hasOwnProperty('class')) {
                $input.addClass(args.class);
            }
            if (args.hasOwnProperty('id')) {
                $input.attr('id', args.id);
            }
            if (args.hasOwnProperty('placeholder')) {
                $input.attr('placeholder', args.placeholder);
            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
            $input.attr('name', inputName);
            if (value !== null) {
                $input.val(value);
            }
            $inputContainer.append($input);

            $element.append($inputContainer);

            return $element;
        };
        this.renderTextareaElement = function (name, order, args, value) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--30');
                var $label = $('<label></label>').html(args.label).addClass('label');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                if (args.hasOwnProperty('id')) {
                    $label.attr('for', args.id);
                }
                $labelContainer.append($label);
                $element.append($labelContainer);
                $inputContainer.addClass('grid__col grid__col--70');
            } else {
                $inputContainer.addClass('grid__col grid__col--100');
            }
            var $textarea = $('<textarea></textarea>').addClass('textarea');
            if (args.hasOwnProperty('class')) {
                $textarea.addClass(args.class);
            }
            if (args.hasOwnProperty('rows')) {
                $textarea.attr('rows', args.rows);
            }
            if (args.hasOwnProperty('id')) {
                $textarea.attr('id', args.id);
            }
            if (args.hasOwnProperty('placeholder')) {
                $textarea.attr('placeholder', args.placeholder);
            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
            $textarea.attr('name', inputName);
            if (value !== null) {
                $textarea.val(value);
            }
            $inputContainer.append($textarea);

            $element.append($inputContainer);

            return $element;

        };
        this.renderRadioElement = function (name, order, args, checked) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            var $label = $('<label></label>').addClass('label label--radio');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--100');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                var $radio = $('<input/>').attr('type', 'radio');
                if (args.hasOwnProperty('class')) {
                    $radio.addClass(args.class);
                }
                if (args.hasOwnProperty('value')) {
                    $radio.attr('value', args.value);
                }
                if (args.hasOwnProperty('id')) {
                    $radio.attr('id', args.id);
                }
                $labelContainer.append($label);
                $label.append($radio);
                $label.append(args.label);

            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;

            $radio.attr('name', inputName);
            if (checked !== null) {
                if (checked === true) {
                    $radio.attr('checked', '');
                }
            }
            $inputContainer.append($labelContainer);

            $element.append($inputContainer);

            return $element;
        };
        this.renderCheckboxElement = function (name, order, args, checked) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            var $label = $('<label></label>').addClass('label label--checkbox');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--100');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                var $checkbox = $('<input/>').attr('type', 'checkbox');
                if (args.hasOwnProperty('class')) {
                    $checkbox.addClass(args.class);
                }
                if (args.hasOwnProperty('value')) {
                    $checkbox.attr('value', args.value);
                }
                if (args.hasOwnProperty('id')) {
                    $checkbox.attr('id', args.id);
                }
                $labelContainer.append($label);
                $label.append($checkbox);
                $label.append(args.label);

            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
            if (args.hasOwnProperty('group')) {
                if (args.group) {
                    inputName += '[]';
                }
            }
            $checkbox.attr('name', inputName);
            if (checked !== null) {
                if (checked === true) {
                    $checkbox.attr('checked', '');
                }
            }
            $inputContainer.append($labelContainer);

            $element.append($inputContainer);

            return $element;
        };
        this.renderSelectElement = function (name, order, args, value) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--30');
                var $label = $('<label></label>').html(args.label).addClass('label');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                if (args.hasOwnProperty('id')) {
                    $label.attr('for', args.id);
                }
                $labelContainer.append($label);
                $element.append($labelContainer);
                $inputContainer.addClass('grid__col grid__col--70');
            } else {
                $inputContainer.addClass('grid__col grid__col--100');
            }
            var $select = $('<select></select>').addClass('select');
            if (args.hasOwnProperty('class')) {
                $select.addClass(args.class);
            }
            if (args.hasOwnProperty('id')) {
                $select.attr('id', args.id);
            }
            if (args.hasOwnProperty('size')) {
                $select.attr('size', args.size);
            }
            if (args.hasOwnProperty('options')) {
                var options = args.options;
                $.each(options, function (key, val) {
                    var $option = $('<option></option>');
                    if ($.type(args.options) === 'array') {
                        $option.html(val);
                        if (value !== null) {
                            if ($.type(value) === 'array') {
                                if ($.inArray(val, value)) {
                                    $option.attr('selected', '');
                                }
                            } else {
                                if (val === value) {
                                    $option.attr('selected', '');
                                }
                            }
                        }
                    }
                    else {
                        $option.html(val).attr('value', key);
                        if (value !== null) {
                            if ($.type(value) === 'array') {
                                if ($.inArray(key, value)) {
                                    $option.attr('selected', '');
                                }
                            } else {
                                if (key === value) {
                                    $option.attr('selected', '');
                                }
                            }
                        }
                    }
                    $select.append($option);
                });
            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
            if (args.hasOwnProperty('multiple') && args.multiple === true) {
                $select.attr('multiple', '');
                inputName += '[]';
            }

            $select.attr('name', inputName);

            $inputContainer.append($select);

            $element.append($inputContainer);

            return $element;
        };
        this.renderFileuploadElement = function (name, order, args, value) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--30');
                var $label = $('<label></label>').html(args.label).addClass('label');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                if (args.hasOwnProperty('id')) {
                    $label.attr('for', args.id);
                }
                $labelContainer.append($label);
                $element.append($labelContainer);
                $inputContainer.addClass('grid__col grid__col--70');
            } else {
                $inputContainer.addClass('grid__col grid__col--100');
            }
            var $fileUpload = $('<div></div>').addClass('jsupload wp-upload');
            if (args.hasOwnProperty('id')) {
                $fileUpload.attr('data-id', args.id);
            }
            if (args.hasOwnProperty('class')) {
                $fileUpload.addClass(args.class);
            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
            $fileUpload.attr('data-name', inputName);
            if (value !== null) {
                $fileUpload.attr('data-value', value);
            }
            $inputContainer.append($fileUpload);

            $element.append($inputContainer);

            $fileUpload.wpUpload();

            return $element;

        };
        this.renderDatepickerElement = function (name, order, args, value) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--30');
                var $label = $('<label></label>').html(args.label).addClass('label');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                if (args.hasOwnProperty('id')) {
                    $label.attr('for', args.id);
                }
                $labelContainer.append($label);
                $element.append($labelContainer);
                $inputContainer.addClass('grid__col grid__col--70');
            } else {
                $inputContainer.addClass('grid__col grid__col--100');
            }
            var $input = $('<input/>').addClass('input');
            if (args.hasOwnProperty('class')) {
                $input.addClass(args.class);
            }
            if (args.hasOwnProperty('id')) {
                $input.attr('id', args.id);
            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
            $input.attr('name', inputName);
            if (value !== null) {
                $input.val(value);
            }
            $inputContainer.append($input);

            $element.append($inputContainer);
            if (args.hasOwnProperty('datepicker')) {
                $input.datepicker(args.datepicker);
            } else {
                $input.datepicker();
            }
            return $element;

        };
        this.renderColorpickerElement = function (name, order, args, value) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--30');
                var $label = $('<label></label>').html(args.label).addClass('label');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                if (args.hasOwnProperty('id')) {
                    $label.attr('for', args.id);
                }
                $labelContainer.append($label);
                $element.append($labelContainer);
                $inputContainer.addClass('grid__col grid__col--70');
            } else {
                $inputContainer.addClass('grid__col grid__col--100');
            }
            var $input = $('<input/>').addClass('input');
            if (args.hasOwnProperty('class')) {
                $input.addClass(args.class);
            }
            if (args.hasOwnProperty('id')) {
                $input.attr('id', args.id);
            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
            $input.attr('name', inputName);
            if (value !== null) {
                $input.val(value);
            }
            $inputContainer.append($input);

            $element.append($inputContainer);
            if (args.hasOwnProperty('colorpicker')) {
                $input.wpColorPicker(args.colorpicker);
            } else {
                $input.wpColorPicker();
            }
            return $element;

        };
        this.renderWpeditorElement = function (name, order, args, value) {
            var $element = $('<div></div>').addClass('grid__row');
            var $inputContainer = $('<div></div>');
            if (args.hasOwnProperty('label')) {
                var $labelContainer = $('<div></div>').addClass('grid__col grid__col--100');
                var $label = $('<label></label>').html(args.label).addClass('label');
                if (args.hasOwnProperty('labelClass')) {
                    $label.addClass(args.labelClass);
                }
                if (args.hasOwnProperty('id')) {
                    $label.attr('for', args.id);
                }
                $labelContainer.append($label);
                $element.append($labelContainer);
                $inputContainer.addClass('grid__col grid__col--100');
            } else {
                $inputContainer.addClass('grid__col grid__col--100');
            }
            var $textarea = $('<textarea></textarea>').addClass('textarea');
            if (args.hasOwnProperty('class')) {
                $textarea.addClass(args.class);
            }
            if (args.hasOwnProperty('rows')) {
                $textarea.attr('rows', args.rows);
            }
            if (args.hasOwnProperty('id')) {
                $textarea.attr('id', args.id);
            }
            else {
                $textarea.attr('id', makeid());
            }
            if (args.hasOwnProperty('placeholder')) {
                $textarea.attr('placeholder', args.placeholder);
            }
            var arrName = name.match(/\[([^\]]+)\]/g);
            $.each(arrName, function () {
                name = name.replace(this, '');
            });
            var addName = '';
            if (arrName !== null && typeof arrName !== 'undefined') {
                if (arrName.length > 0) {
                    addName = arrName.join('');
                }
            }
            var inputName = globalOptions.name + '[' + order + '][' + name + ']' + addName;
            $textarea.attr('name', inputName);
            if (value !== null) {
                $textarea.val(value);
            }
            $inputContainer.append($textarea);

            $element.append($inputContainer);

            wp.editor.initialize($textarea.attr('id'), {tinymce: true, quicktags: true});

            return $element;

        };

        function makeid() {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            for (var i = 0; i < 10; i++)
                text += possible.charAt(Math.floor(Math.random() * possible.length));

            return text;
        }


        if (this.length > 0) {
            if (this.length > 1) {
                this.each(function () {
                    $(this).dynamicBox(options);
                });
            } else {
                initOptions(options);
                $el = this;
                init();
            }
        }
    }
}(jQuery));
jQuery(document).ready(function ($){
    $('.jsupload').wpUpload();

    $('[data-confirm]').on('click',function (e) {
        if (!confirm($(this).data('confirm'))){
            e.preventDefault();
        }
    });
    $('.js-dynamic-tabs').dynamicTabs();

    $('.js-dynamic-list').dynamicList();

    $('.js-dynamic-select').dynamicSelect();

    $('.js-colorpicker').wpColorPicker();
});

