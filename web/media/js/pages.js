!function ($) {
    "use strict";
    var Pages = function () {
        this.VERSION = "2.0.0", this.AUTHOR = "Revox", this.SUPPORT = "support@revox.io", this.pageScrollElement = "html, body", this.$body = $("body"), this.setUserOS(), this.setUserAgent()
    };
    Pages.prototype.setUserOS = function () {
        var OSName = "";
        -1 != navigator.appVersion.indexOf("Win") && (OSName = "windows"), -1 != navigator.appVersion.indexOf("Mac") && (OSName = "mac"), -1 != navigator.appVersion.indexOf("X11") && (OSName = "unix"), -1 != navigator.appVersion.indexOf("Linux") && (OSName = "linux"), this.$body.addClass(OSName)
    }, Pages.prototype.setUserAgent = function () {
        navigator.userAgent.match(/Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i) ? this.$body.addClass("mobile") : (this.$body.addClass("desktop"), navigator.userAgent.match(/MSIE 9.0/) && this.$body.addClass("ie9"))
    }, Pages.prototype.isVisibleXs = function () {
        return !$("#pg-visible-xs").length && this.$body.append('<div id="pg-visible-xs" class="visible-xs" />'), $("#pg-visible-xs").is(":visible")
    }, Pages.prototype.isVisibleSm = function () {
        return !$("#pg-visible-sm").length && this.$body.append('<div id="pg-visible-sm" class="visible-sm" />'), $("#pg-visible-sm").is(":visible")
    }, Pages.prototype.isVisibleMd = function () {
        return !$("#pg-visible-md").length && this.$body.append('<div id="pg-visible-md" class="visible-md" />'), $("#pg-visible-md").is(":visible")
    }, Pages.prototype.isVisibleLg = function () {
        return !$("#pg-visible-lg").length && this.$body.append('<div id="pg-visible-lg" class="visible-lg" />'), $("#pg-visible-lg").is(":visible")
    }, Pages.prototype.getUserAgent = function () {
        return $("body").hasClass("mobile") ? "mobile" : "desktop"
    }, Pages.prototype.setFullScreen = function (element) {
        var requestMethod = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullscreen;
        if (requestMethod)
            requestMethod.call(element);
        else if ("undefined" != typeof window.ActiveXObject) {
            var wscript = new ActiveXObject("WScript.Shell");
            null !== wscript && wscript.SendKeys("{F11}")
        }
    }, Pages.prototype.getColor = function (color, opacity) {
        opacity = parseFloat(opacity) || 1;
        var elem = $(".pg-colors").length ? $(".pg-colors") : $('<div class="pg-colors"></div>').appendTo("body"),
                colorElem = elem.find('[data-color="' + color + '"]').length ? elem.find('[data-color="' + color + '"]') : $('<div class="bg-' + color + '" data-color="' + color + '"></div>').appendTo(elem),
                color = colorElem.css("background-color"),
                rgb = color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/),
                rgba = "rgba(" + rgb[1] + ", " + rgb[2] + ", " + rgb[3] + ", " + opacity + ")";
        return rgba
    }, Pages.prototype.initSidebar = function () {
        $('[data-pages="sidebar"]').each(function () {
            var $sidebar = $(this);
            $sidebar.sidebar($sidebar.data())
        })
    }, Pages.prototype.initDropDown = function () {
        $(".dropdown-default").each(function () {
            var btn = $(this).find(".dropdown-menu").siblings(".dropdown-toggle"),
                    offset = 0,
                    menuWidth = (btn.actual("innerWidth") - btn.actual("width"), $(this).find(".dropdown-menu").actual("outerWidth"));
            btn.actual("outerWidth") < menuWidth ? (btn.width(menuWidth - offset), $(this).find(".dropdown-menu").width(btn.actual("outerWidth"))) : $(this).find(".dropdown-menu").width(btn.actual("outerWidth"))
        })
    }, Pages.prototype.initFormGroupDefault = function () {
        $(".form-group.form-group-default").click(function () {
            $(this).find("input").focus()
        }), $("body").on("focus", ".form-group.form-group-default :input", function () {
            $(".form-group.form-group-default").removeClass("focused"), $(this).parents(".form-group").addClass("focused")
        }), $("body").on("blur", ".form-group.form-group-default :input", function () {
            $(this).parents(".form-group").removeClass("focused"), $(this).val() ? $(this).closest(".form-group").find("label").addClass("fade") : $(this).closest(".form-group").find("label").removeClass("fade")
        }), $(".form-group.form-group-default .checkbox, .form-group.form-group-default .radio").hover(function () {
            $(this).parents(".form-group").addClass("focused")
        }, function () {
            $(this).parents(".form-group").removeClass("focused")
        })
    }, Pages.prototype.initSlidingTabs = function () {
        $('a[data-toggle="tab"]').on("show.bs.tab", function (e) {
            e = $(e.target).parent().find("a[data-toggle=tab]");
            var hrefCurrent = (e.attr("href"), e.attr("href"));
            $(hrefCurrent).is(".slide-left, .slide-right") && ($(hrefCurrent).addClass("sliding"), setTimeout(function () {
                $(hrefCurrent).removeClass("sliding")
            }, 100))
        })
    }, Pages.prototype.initNotificationCenter = function () {
        $(".notification-list .dropdown-menu").on("click", function (event) {
            event.stopPropagation()
        }), $(".toggle-more-details").on("click", function (event) {
            var p = $(this).closest(".heading");
            p.closest(".heading").children(".more-details").stop().slideToggle("fast", function () {
                p.toggleClass("open")
            })
        })
    }, Pages.prototype.initProgressBars = function () {
        $(window).on("load", function () {
            $(".progress-bar-indeterminate, .progress-circle-indeterminate, .mapplic-pin").hide().show(0)
        })
    }, Pages.prototype.initView = function () {
        $('[data-navigate="view"]').on("click", function (e) {
            e.preventDefault();
            var el = $(this).attr("data-view-port");
            return null != $(this).attr("data-toggle-view") && ($(el).children().last().children(".view").hide(), $($(this).attr("data-toggle-view")).show()), $(el).toggleClass($(this).attr("data-view-animation")), !1
        })
    }, Pages.prototype.initInputFile = function () {
        $(document).on("change", ".btn-file :file", function () {
            var input = $(this),
                    numFiles = input.get(0).files ? input.get(0).files.length : 1,
                    label = input.val().replace(/\\/g, "/").replace(/.*\//, "");
            input.trigger("fileselect", [numFiles, label])
        }), $(".btn-file :file").on("fileselect", function (event, numFiles, label) {
            var input = $(this).parents(".input-group").find(":text"),
                    log = numFiles > 1 ? numFiles + " files selected" : label;
            input.length ? input.val(log) : $(this).parent().html(log)
        })
    }, Pages.prototype.initTooltipPlugin = function () {
        $.fn.tooltip && $('[data-toggle="tooltip"]').tooltip()
    }, Pages.prototype.initSelect2Plugin = function () {
        $.fn.select2 && $('[data-init-plugin="select2"]').each(function () {
            $(this).select2({
                minimumResultsForSearch: "true" == $(this).attr("data-disable-search") ? -1 : 1
            }).on("select2-opening", function () {
                $.fn.scrollbar && $(".select2-results").scrollbar({
                    ignoreMobile: !1
                })
            })
        })
    }, Pages.prototype.initScrollBarPlugin = function () {
        $.fn.scrollbar && $(".scrollable").scrollbar({
            ignoreOverlay: !1
        })
    }, Pages.prototype.initListView = function () {
        $.fn.ioslist && $('[data-init-list-view="ioslist"]').ioslist(), $.fn.scrollbar && $(".list-view-wrapper").scrollbar({
            ignoreOverlay: !1
        })
    }, Pages.prototype.initSwitcheryPlugin = function () {
        window.Switchery && $('[data-init-plugin="switchery"]').each(function () {
            new Switchery($(this).get(0), {
                color: $.Pages.getColor("success")
            })
        })
    }, Pages.prototype.initSelectFxPlugin = function () {
        window.SelectFx && $('select[data-init-plugin="cs-select"]').each(function () {
            var el = $(this).get(0);
            $(el).wrap('<div class="cs-wrapper"></div>'), new SelectFx(el)
        })
    }, Pages.prototype.initUnveilPlugin = function () {
        $.fn.unveil && $("img").unveil()
    }, Pages.prototype.initValidatorPlugin = function () {
        $.validator && $.validator.setDefaults({
            ignore: "",
            showErrors: function (errorMap, errorList) {
                var $this = this;
                return $.each(this.successList, function (index, value) {
                    var parent = $(this).closest(".form-group-attached");
                    return parent.length ? $(value).popover("hide") : void 0
                }), $.each(errorList, function (index, value) {
                    var parent = $(value.element).closest(".form-group-attached");
                    if (!parent.length)
                        return $this.defaultShowErrors();
                    var _popover;
                    _popover = $(value.element).popover({
                        trigger: "manual",
                        placement: "top",
                        html: !0,
                        container: parent.closest("form"),
                        content: value.message
                    }), _popover.data("bs.popover").options.content = value.message;
                    var parent = $(value.element).closest(".form-group");
                    parent.addClass("has-error"), $(value.element).popover("show")
                })
            },
            onfocusout: function (element) {
                var parent = $(element).closest(".form-group");
                $(element).valid() && (parent.removeClass("has-error"), parent.next(".error").remove())
            },
            onkeyup: function (element) {
                var parent = $(element).closest(".form-group");
                $(element).valid() ? ($(element).removeClass("error"), parent.removeClass("has-error"), parent.next("label.error").remove(), parent.find("label.error").remove()) : parent.addClass("has-error")
            },
            errorPlacement: function (error, element) {
                var parent = $(element).closest(".form-group");
                parent.hasClass("form-group-default") ? (parent.addClass("has-error"), error.insertAfter(parent)) : error.insertAfter(element)
            }
        })
    }, Pages.prototype.init = function () {
        this.initSidebar(), this.initDropDown(), this.initFormGroupDefault(), this.initSlidingTabs(), this.initNotificationCenter(), this.initProgressBars(), this.initTooltipPlugin(), this.initSelect2Plugin(), this.initScrollBarPlugin(), this.initSwitcheryPlugin(), this.initSelectFxPlugin(), this.initUnveilPlugin(), this.initValidatorPlugin(), this.initView(), this.initListView(), this.initInputFile()
    }, $.Pages = new Pages, $.Pages.Constructor = Pages
}(window.jQuery),
        function (window) {
            "use strict";

            function hasParent(e, p) {
                if (!e)
                    return !1;
                for (var el = e.target || e.srcElement || e || !1; el && el != p; )
                    el = el.parentNode || !1;
                return el !== !1
            }

            function extend(a, b) {
                for (var key in b)
                    b.hasOwnProperty(key) && (a[key] = b[key]);
                return a
            }

            function SelectFx(el, options) {
                this.el = el, this.options = extend({}, this.options), extend(this.options, options), this._init()
            }

            function closest(elem, selector) {
                for (var matchesSelector = elem.matches || elem.webkitMatchesSelector || elem.mozMatchesSelector || elem.msMatchesSelector; elem; ) {
                    if (matchesSelector.bind(elem)(selector))
                        return elem;
                    elem = elem.parentElement
                }
                return !1
            }

            function offset(el) {
                return {
                    left: el.getBoundingClientRect().left + window.pageXOffset - el.ownerDocument.documentElement.clientLeft,
                    top: el.getBoundingClientRect().top + window.pageYOffset - el.ownerDocument.documentElement.clientTop
                }
            }

            function insertAfter(newNode, referenceNode) {
                referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling)
            }
            SelectFx.prototype.options = {
                newTab: !0,
                stickyPlaceholder: !0,
                container: "body",
                onChange: function (el) {
                    var event = document.createEvent("HTMLEvents");
                    event.initEvent("change", !0, !1), el.dispatchEvent(event)
                }
            }, SelectFx.prototype._init = function () {
                var selectedOpt = this.el.querySelector("option[selected]");
                this.hasDefaultPlaceholder = selectedOpt && selectedOpt.disabled, this.selectedOpt = selectedOpt || this.el.querySelector("option"), this._createSelectEl(), this.selOpts = [].slice.call(this.selEl.querySelectorAll("li[data-option]")), this.selOptsCount = this.selOpts.length, this.current = this.selOpts.indexOf(this.selEl.querySelector("li.cs-selected")) || -1, this.selPlaceholder = this.selEl.querySelector("span.cs-placeholder"), this._initEvents(), this.el.onchange = function () {
                    var index = this.selectedIndex,
                            inputText = this.children[index].innerHTML.trim();
                }
            }, SelectFx.prototype._createSelectEl = function () {
                var options = "",
                        createOptionHTML = function (el) {
                            var optclass = "",
                                    classes = "",
                                    link = "";
                            return !el.selectedOpt || this.foundSelected || this.hasDefaultPlaceholder || (classes += "cs-selected ", this.foundSelected = !0), el.getAttribute("data-class") && (classes += el.getAttribute("data-class")), el.getAttribute("data-link") && (link = "data-link=" + el.getAttribute("data-link")), "" !== classes && (optclass = 'class="' + classes + '" '), "<li " + optclass + link + ' data-option data-value="' + el.value + '"><span>' + el.textContent + "</span></li>"
                        };
                [].slice.call(this.el.children).forEach(function (el) {
                    if (!el.disabled) {
                        var tag = el.tagName.toLowerCase();
                        "option" === tag ? options += createOptionHTML(el) : "optgroup" === tag && (options += '<li class="cs-optgroup"><span>' + el.label + "</span><ul>", [].slice.call(el.children).forEach(function (opt) {
                            options += createOptionHTML(opt)
                        }), options += "</ul></li>")
                    }
                });
                var opts_el = '<div class="cs-options"><ul>' + options + "</ul></div>";
                this.selEl = document.createElement("div"), this.selEl.className = this.el.className, this.selEl.tabIndex = this.el.tabIndex, this.selEl.innerHTML = '<span class="cs-placeholder">' + this.selectedOpt.textContent + "</span>" + opts_el, this.el.parentNode.appendChild(this.selEl), this.selEl.appendChild(this.el);
                var backdrop = document.createElement("div");
                backdrop.className = "cs-backdrop", this.selEl.appendChild(backdrop)
            }, SelectFx.prototype._initEvents = function () {
                var self = this;
                this.selPlaceholder.addEventListener("click", function () {
                    self._toggleSelect()
                }), this.selOpts.forEach(function (opt, idx) {
                    opt.addEventListener("click", function () {
                        self.current = idx, self._changeOption(), self._toggleSelect()
                    })
                }), document.addEventListener("click", function (ev) {
                    var target = ev.target;
                    self._isOpen() && target !== self.selEl && !hasParent(target, self.selEl) && self._toggleSelect()
                }), this.selEl.addEventListener("keydown", function (ev) {
                    var keyCode = ev.keyCode || ev.which;
                    switch (keyCode) {
                        case 38:
                            ev.preventDefault(), self._navigateOpts("prev");
                            break;
                        case 40:
                            ev.preventDefault(), self._navigateOpts("next");
                            break;
                        case 32:
                            ev.preventDefault(), self._isOpen() && "undefined" != typeof self.preSelCurrent && -1 !== self.preSelCurrent && self._changeOption(), self._toggleSelect();
                            break;
                        case 13:
                            ev.preventDefault(), self._isOpen() && "undefined" != typeof self.preSelCurrent && -1 !== self.preSelCurrent && (self._changeOption(), self._toggleSelect());
                            break;
                        case 27:
                            ev.preventDefault(), self._isOpen() && self._toggleSelect()
                    }
                })
            }, SelectFx.prototype._navigateOpts = function (dir) {
                this._isOpen() || this._toggleSelect();
                var tmpcurrent = "undefined" != typeof this.preSelCurrent && -1 !== this.preSelCurrent ? this.preSelCurrent : this.current;
                ("prev" === dir && tmpcurrent > 0 || "next" === dir && tmpcurrent < this.selOptsCount - 1) && (this.preSelCurrent = "next" === dir ? tmpcurrent + 1 : tmpcurrent - 1, this._removeFocus(), classie.add(this.selOpts[this.preSelCurrent], "cs-focus"))
            }, SelectFx.prototype._toggleSelect = function () {
                var backdrop = this.selEl.querySelector(".cs-backdrop"),
                        container = document.querySelector(this.options.container),
                        mask = container.querySelector(".dropdown-mask"),
                        csOptions = this.selEl.querySelector(".cs-options"),
                        csPlaceholder = this.selEl.querySelector(".cs-placeholder"),
                        csPlaceholderWidth = csPlaceholder.offsetWidth,
                        csPlaceholderHeight = csPlaceholder.offsetHeight,
                        csOptionsWidth = csOptions.scrollWidth;
                if (this._isOpen()) {
                    -1 !== this.current && (this.selPlaceholder.textContent = this.selOpts[this.current].textContent);
                    var dummy = this.selEl.data,
                            parent = dummy.parentNode;
                    insertAfter(this.selEl, dummy), this.selEl.removeAttribute("style"), parent.removeChild(dummy);
                    this.selEl.clientHeight;
                    backdrop.style.transform = backdrop.style.webkitTransform = backdrop.style.MozTransform = backdrop.style.msTransform = backdrop.style.OTransform = "scale3d(1,1,1)", classie.remove(this.selEl, "cs-active"), mask.style.display = "none", csOptions.style.overflowY = "hidden", csOptions.style.width = "auto";
                    var parentFormGroup = closest(this.selEl, ".form-group");
                    parentFormGroup && classie.removeClass(parentFormGroup, "focused")
                } else {
                    this.hasDefaultPlaceholder && this.options.stickyPlaceholder && (this.selPlaceholder.textContent = this.selectedOpt.textContent);
                    var dummy;
                    this.selEl.parentNode.querySelector(".dropdown-placeholder") ? dummy = this.selEl.parentNode.querySelector(".dropdown-placeholder") : (dummy = document.createElement("div"), classie.add(dummy, "dropdown-placeholder"), insertAfter(dummy, this.selEl)), dummy.style.height = csPlaceholderHeight + "px", dummy.style.width = this.selEl.offsetWidth + "px", this.selEl.data = dummy, this.selEl.style.position = "absolute";
                    var offsetselEl = offset(this.selEl);
                    this.selEl.style.left = offsetselEl.left + "px", container.appendChild(this.selEl);
                    var contentHeight = csOptions.offsetHeight,
                            originalHeight = csPlaceholder.offsetHeight,
                            scaleV = (csOptions.offsetWidth, csPlaceholder.offsetWidth, contentHeight / originalHeight);
                    //this.selEl.style.top = offsetselEl.top + "px";
                    this.selEl.style.top = ((csPlaceholder.offsetHeight * scaleV / 2) + offsetselEl.top - csPlaceholder.offsetHeight) + "px";
                    
                    
                    backdrop.style.transform = backdrop.style.webkitTransform = backdrop.style.MozTransform = backdrop.style.msTransform = backdrop.style.OTransform = "scale3d(1, " + scaleV + ", 1)", mask || (mask = document.createElement("div"), classie.add(mask, "dropdown-mask"), container.appendChild(mask)), mask.style.display = "block", classie.add(this.selEl, "cs-active");
                    var resizedWidth = csOptionsWidth > csPlaceholderWidth ? csOptionsWidth : csPlaceholderWidth;
                    this.selEl.style.width = resizedWidth + "px", this.selEl.style.height = originalHeight + "px", csOptions.style.width = "100%", setTimeout(function () {
                        csOptions.style.overflowY = "auto"
                    }, 300)
                }
            }, SelectFx.prototype._changeOption = function () {
                "undefined" != typeof this.preSelCurrent && -1 !== this.preSelCurrent && (this.current = this.preSelCurrent, this.preSelCurrent = -1);
                var opt = this.selOpts[this.current];
                this.selPlaceholder.textContent = opt.textContent, this.el.value = opt.getAttribute("data-value");
                var oldOpt = this.selEl.querySelector("li.cs-selected");
                oldOpt && classie.remove(oldOpt, "cs-selected"), classie.add(opt, "cs-selected"), opt.getAttribute("data-link") && (this.options.newTab ? window.open(opt.getAttribute("data-link"), "_blank") : window.location = opt.getAttribute("data-link")), this.options.onChange(this.el)
            }, SelectFx.prototype._isOpen = function (opt) {
                return classie.has(this.selEl, "cs-active")
            }, SelectFx.prototype._removeFocus = function (opt) {
                var focusEl = this.selEl.querySelector("li.cs-focus");
                focusEl && classie.remove(focusEl, "cs-focus")
            }, window.SelectFx = SelectFx
        }(window),
        function ($) {
            "use strict";
            $("[data-chat-input]").on("keypress", function (e) {
                if (13 == e.which) {
                    var el = $(this).attr("data-chat-conversation");
                    $(el).append('<div class="message clearfix"><div class="chat-bubble from-me">' + $(this).val() + "</div></div>"), $(this).val("")
                }
            })
        }(window.jQuery),
        function ($) {
            "use strict";

            function Plugin(option) {
                return this.filter(":input").each(function () {
                    var $this = $(this),
                            data = $this.data("pg.circularProgress"),
                            options = "object" == typeof option && option;
                    data || $this.data("pg.circularProgress", data = new Progress(this, options)), "string" == typeof option ? data[option]() : options.hasOwnProperty("value") && data.value(options.value)
                })
            }

            function perc2deg(p) {
                return parseInt(p / 100 * 360)
            }
            var Progress = function (element, options) {
                this.$element = $(element), this.options = $.extend(!0, {}, $.fn.circularProgress.defaults, options), this.$container = $('<div class="progress-circle"></div>'), this.$element.attr("data-color") && this.$container.addClass("progress-circle-" + this.$element.attr("data-color")), this.$element.attr("data-thick") && this.$container.addClass("progress-circle-thick"), this.$pie = $('<div class="pie"></div>'), this.$pie.$left = $('<div class="left-side half-circle"></div>'), this.$pie.$right = $('<div class="right-side half-circle"></div>'), this.$pie.append(this.$pie.$left).append(this.$pie.$right), this.$container.append(this.$pie).append('<div class="shadow"></div>'), this.$element.after(this.$container), this.val = this.$element.val();
                var deg = perc2deg(this.val);
                this.val <= 50 ? this.$pie.$right.css("transform", "rotate(" + deg + "deg)") : (this.$pie.css("clip", "rect(auto, auto, auto, auto)"), this.$pie.$right.css("transform", "rotate(180deg)"), this.$pie.$left.css("transform", "rotate(" + deg + "deg)"))
            };
            Progress.VERSION = "1.0.0", Progress.prototype.value = function (val) {
                if ("undefined" != typeof val) {
                    var deg = perc2deg(val);
                    this.$pie.removeAttr("style"), this.$pie.$right.removeAttr("style"), this.$pie.$left.removeAttr("style"), 50 >= val ? this.$pie.$right.css("transform", "rotate(" + deg + "deg)") : (this.$pie.css("clip", "rect(auto, auto, auto, auto)"), this.$pie.$right.css("transform", "rotate(180deg)"), this.$pie.$left.css("transform", "rotate(" + deg + "deg)"))
                }
            };
            var old = $.fn.circularProgress;
            $.fn.circularProgress = Plugin, $.fn.circularProgress.Constructor = Progress, $.fn.circularProgress.defaults = {
                value: 0
            }, $.fn.circularProgress.noConflict = function () {
                return $.fn.circularProgress = old, this
            }, $(window).on("load", function () {
                $('[data-pages-progress="circle"]').each(function () {
                    var $progress = $(this);
                    $progress.circularProgress($progress.data())
                })
            })
        }(window.jQuery),
        function ($) {
            "use strict";
            var Notification = function (container, options) {
                function SimpleNotification() {
                    if (self.notification.addClass("pgn-simple"), self.alert.append(self.options.message), self.options.showClose) {
                        var close = $('<button type="button" class="close" data-dismiss="alert"></button>').append('<span aria-hidden="true">&times;</span>').append('<span class="sr-only">Close</span>');
                        self.alert.prepend(close)
                    }
                }

                function BarNotification() {
                    if (self.notification.addClass("pgn-bar"), self.alert.append("<span>" + self.options.message + "</span>"), self.alert.addClass("alert-" + self.options.type), self.options.showClose) {
                        var close = $('<button type="button" class="close" data-dismiss="alert"></button>').append('<span aria-hidden="true">&times;</span>').append('<span class="sr-only">Close</span>');
                        self.alert.prepend(close)
                    }
                }

                function CircleNotification() {
                    self.notification.addClass("pgn-circle");
                    var table = "<div>";
                    self.options.thumbnail && (table += '<div class="pgn-thumbnail"><div>' + self.options.thumbnail + "</div></div>"), table += '<div class="pgn-message"><div>', self.options.title && (table += '<p class="bold">' + self.options.title + "</p>"), table += "<p>" + self.options.message + "</p></div></div>", table += "</div>", self.options.showClose && (table += '<button type="button" class="close" data-dismiss="alert">', table += '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>', table += "</button>"), self.alert.append(table), self.alert.after('<div class="clearfix"></div>')
                }

                function FlipNotification() {
                    if (self.notification.addClass("pgn-flip"), self.alert.append("<span>" + self.options.message + "</span>"), self.options.showClose) {
                        var close = $('<button type="button" class="close" data-dismiss="alert"></button>').append('<span aria-hidden="true">&times;</span>').append('<span class="sr-only">Close</span>');
                        self.alert.prepend(close)
                    }
                }
                var self = this;
                return self.container = $(container), self.notification = $('<div class="pgn"></div>'), self.options = $.extend(!0, {}, $.fn.pgNotification.defaults, options), self.container.find(".pgn-wrapper[data-position=" + this.options.position + "]").length ? self.wrapper = $(".pgn-wrapper[data-position=" + this.options.position + "]") : (self.wrapper = $('<div class="pgn-wrapper" data-position="' + this.options.position + '"></div>'), self.container.append(self.wrapper)), self.alert = $('<div class="alert"></div>'), self.alert.addClass("alert-" + self.options.type), "bar" == self.options.style ? new BarNotification : "flip" == self.options.style ? new FlipNotification : "circle" == self.options.style ? new CircleNotification : ("simple" == self.options.style, new SimpleNotification), self.notification.append(self.alert), self.alert.on("closed.bs.alert", function () {
                    self.notification.remove(), self.options.onClosed()
                }), this
            };
            Notification.VERSION = "1.0.0", Notification.prototype.show = function () {
                if (this.wrapper.prepend(this.notification), this.options.onShown(), 0 != this.options.timeout) {
                    var _this = this;
                    setTimeout(function () {
                        this.notification.fadeOut("slow", function () {
                            $(this).remove(), _this.options.onClosed()
                        })
                    }.bind(this), this.options.timeout)
                }
            }, $.fn.pgNotification = function (options) {
                return new Notification(this, options)
            }, $.fn.pgNotification.defaults = {
                style: "simple",
                message: null,
                position: "top-right",
                type: "info",
                showClose: !0,
                timeout: 4e3,
                onShown: function () {
                },
                onClosed: function () {
                }
            }
        }(window.jQuery),
        function ($) {
            "use strict";

            function Plugin(option) {
                return this.each(function () {
                    var $this = $(this),
                            data = $this.data("pg.portlet"),
                            options = "object" == typeof option && option;
                    data || $this.data("pg.portlet", data = new Portlet(this, options)), "string" == typeof option ? data[option]() : options.hasOwnProperty("refresh") ? data.refresh(options.refresh) : options.hasOwnProperty("error") && data.error(options.error)
                })
            }
            var Portlet = function (element, options) {
                this.$element = $(element), this.options = $.extend(!0, {}, $.fn.portlet.defaults, options), this.$loader = null, this.$body = this.$element.find(".panel-body")
            };
            Portlet.VERSION = "1.0.0", Portlet.prototype.collapse = function () {
                var icon = this.$element.find(this.options.collapseButton + " > i");
                this.$element.find(".panel-heading");
                return this.$body.stop().slideToggle("fast"), this.$element.hasClass("panel-collapsed") ? (this.$element.removeClass("panel-collapsed"), icon.removeClass().addClass("pg-arrow_maximize"), void($.isFunction(this.options.onExpand) && this.options.onExpand())) : (this.$element.addClass("panel-collapsed"), icon.removeClass().addClass("pg-arrow_minimize"), void($.isFunction(this.options.onCollapse) && this.options.onCollapse()))
            }, Portlet.prototype.close = function () {
                this.$element.remove(), $.isFunction(this.options.onClose) && this.options.onClose()
            }, Portlet.prototype.maximize = function () {
                var icon = this.$element.find(this.options.maximizeButton + " > i");
                this.$element.hasClass("panel-maximized") ? (this.$element.removeClass("panel-maximized"), icon.removeClass("pg-fullscreen_restore").addClass("pg-fullscreen"), $.isFunction(this.options.onRestore) && this.options.onRestore()) : (this.$element.addClass("panel-maximized"), icon.removeClass("pg-fullscreen").addClass("pg-fullscreen_restore"), $.isFunction(this.options.onMaximize) && this.options.onMaximize())
            }, Portlet.prototype.refresh = function (refresh) {
                var toggle = this.$element.find(this.options.refreshButton);
                if (refresh) {
                    if (this.$loader && this.$loader.is(":visible"))
                        return;
                    if (!$.isFunction(this.options.onRefresh))
                        return;
                    this.$loader = $('<div class="portlet-progress"></div>'), this.$loader.css({
                        "background-color": "rgba(" + this.options.overlayColor + "," + this.options.overlayOpacity + ")"
                    });
                    var elem = "";
                    if ("circle" == this.options.progress)
                        elem += '<div class="progress-circle-indeterminate progress-circle-' + this.options.progressColor + '"></div>';
                    else if ("bar" == this.options.progress)
                        elem += '<div class="progress progress-small">', elem += '    <div class="progress-bar-indeterminate progress-bar-' + this.options.progressColor + '"></div>', elem += "</div>";
                    else if ("circle-lg" == this.options.progress) {
                        toggle.addClass("refreshing");
                        var iconNew, iconOld = toggle.find("> i").first();
                        toggle.find('[class$="-animated"]').length ? iconNew = toggle.find('[class$="-animated"]') : (iconNew = $("<i/>"), iconNew.css({
                            position: "absolute",
                            top: iconOld.position().top,
                            left: iconOld.position().left
                        }), iconNew.addClass("portlet-icon-refresh-lg-" + this.options.progressColor + "-animated"), toggle.append(iconNew)), iconOld.addClass("fade"), iconNew.addClass("active")
                    } else
                        elem += '<div class="progress progress-small">', elem += '    <div class="progress-bar-indeterminate progress-bar-' + this.options.progressColor + '"></div>', elem += "</div>";
                    this.$loader.append(elem), this.$element.append(this.$loader);
                    var _loader = this.$loader;
                    setTimeout(function () {
                        this.$loader.remove(), this.$element.append(_loader)
                    }.bind(this), 300), this.$loader.fadeIn(), $.isFunction(this.options.onRefresh) && this.options.onRefresh()
                } else {
                    var _this = this;
                    this.$loader.fadeOut(function () {
                        if ($(this).remove(), "circle-lg" == _this.options.progress) {
                            var iconNew = toggle.find(".active"),
                                    iconOld = toggle.find(".fade");
                            iconNew.removeClass("active"), iconOld.removeClass("fade"), toggle.removeClass("refreshing")
                        }
                        _this.options.refresh = !1
                    })
                }
            }, Portlet.prototype.error = function (error) {
                if (error) {
                    var _this = this;
                    this.$element.pgNotification({
                        style: "bar",
                        message: error,
                        position: "top",
                        timeout: 0,
                        type: "danger",
                        onShown: function () {
                            _this.$loader.find("> div").fadeOut()
                        },
                        onClosed: function () {
                            _this.refresh(!1)
                        }
                    }).show()
                }
            };
            var old = $.fn.portlet;
            $.fn.portlet = Plugin, $.fn.portlet.Constructor = Portlet, $.fn.portlet.defaults = {
                progress: "circle",
                progressColor: "master",
                refresh: !1,
                error: null,
                overlayColor: "255,255,255",
                overlayOpacity: .8,
                refreshButton: '[data-toggle="refresh"]',
                maximizeButton: '[data-toggle="maximize"]',
                collapseButton: '[data-toggle="collapse"]',
                closeButton: '[data-toggle="close"]'
            }, $.fn.portlet.noConflict = function () {
                return $.fn.portlet = old, this
            }, $(document).on("click.pg.portlet.data-api", '[data-toggle="collapse"]', function (e) {
                var $this = $(this),
                        $target = $this.closest(".panel");
                $this.is("a") && e.preventDefault(), $target.data("pg.portlet") && $target.portlet("collapse")
            }), $(document).on("click.pg.portlet.data-api", '[data-toggle="close"]', function (e) {
                var $this = $(this),
                        $target = $this.closest(".panel");
                $this.is("a") && e.preventDefault(), $target.data("pg.portlet") && $target.portlet("close")
            }), $(document).on("click.pg.portlet.data-api", '[data-toggle="refresh"]', function (e) {
                var $this = $(this),
                        $target = $this.closest(".panel");
                $this.is("a") && e.preventDefault(), $target.data("pg.portlet") && $target.portlet({
                    refresh: !0
                })
            }), $(document).on("click.pg.portlet.data-api", '[data-toggle="maximize"]', function (e) {
                var $this = $(this),
                        $target = $this.closest(".panel");
                $this.is("a") && e.preventDefault(), $target.data("pg.portlet") && $target.portlet("maximize")
            }), $(window).on("load", function () {
                $('[data-pages="portlet"]').each(function () {
                    var $portlet = $(this);
                    $portlet.portlet($portlet.data())
                })
            })
        }(window.jQuery),
        function ($) {
            "use strict";

            function Plugin(option) {
                return this.each(function () {
                    var $this = $(this),
                            data = $this.data("pg.quickview"),
                            options = "object" == typeof option && option;
                    data || $this.data("pg.quickview", data = new Quickview(this, options)), "string" == typeof option && data[option]()
                })
            }
            var Quickview = function (element, options) {
                this.$element = $(element), this.options = $.extend(!0, {}, $.fn.quickview.defaults, options), this.bezierEasing = [.05, .74, .27, .99];
                var _this = this;
                $(this.options.notes).on("click", ".list > ul > li", function (e) {
                    var note = $(this).find(".note-preview"),
                            note = $(this).find(".note-preview");
                    $(_this.options.noteEditor).html(note.html()), $(_this.options.notes).toggleClass("push")
                }), $(this.options.notes).on("click", ".list > ul > li .checkbox", function (e) {
                    e.stopPropagation()
                }), $(this.options.notes).on("click", _this.options.backButton, function (e) {
                    $(_this.options.notes).find(".toolbar > li > a").removeClass("active"), $(_this.options.notes).toggleClass("push")
                }), $(this.options.deleteNoteButton).click(function (e) {
                    e.preventDefault(), $(this).toggleClass("selected"), $(_this.options.notes).find(".list > ul > li .checkbox").fadeToggle("fast"), $(_this.options.deleteNoteConfirmButton).fadeToggle("fast").removeClass("hide")
                }), $(this.options.newNoteButton).click(function (e) {
                    e.preventDefault(), $(_this.options.noteEditor).html("")
                }), $(this.options.deleteNoteConfirmButton).click(function () {
                    var checked = $(_this.options.notes).find("input[type=checkbox]:checked");
                    checked.each(function () {
                        $(this).parents("li").remove()
                    })
                }), $(this.options.notes).on("click", ".toolbar > li > a", function (e) {
                    var command = $(this).attr("data-action");
                    document.execCommand(command, !1, null), $(this).toggleClass("active")
                })
            };
            Quickview.VERSION = "1.0.0";
            var old = $.fn.quickview;
            $.fn.quickview = Plugin, $.fn.quickview.Constructor = Quickview, $.fn.quickview.defaults = {
                notes: "#note-views",
                alerts: "#alerts",
                chat: "#chat",
                notesList: ".list",
                noteEditor: ".quick-note-editor",
                deleteNoteButton: ".delete-note-link",
                deleteNoteConfirmButton: ".btn-remove-notes",
                newNoteButton: ".new-note-link",
                backButton: ".close-note-link"
            }, $.fn.quickview.noConflict = function () {
                return $.fn.quickview = old, this
            }, $(window).on("load", function () {
                $('[data-pages="quickview"]').each(function () {
                    var $quickview = $(this);
                    $quickview.quickview($quickview.data())
                })
            }), $(document).on("click.pg.quickview.data-api touchstart", '[data-toggle="quickview"]', function (e) {
                var elem = $(this).attr("data-toggle-element");
                if (Modernizr.csstransitions)
                    $(elem).toggleClass("open");
                else {
                    var width = $(elem).width();
                    $(elem).hasClass("open-ie") ? $(elem).stop().animate({
                        right: 0
                    }, 400, $.bez([.05, .74, .27, .99]), function () {
                        $(elem).removeClass("open-ie")
                    }) : $(elem).stop().animate({
                        right: -1 * width
                    }, 400, $.bez([.05, .74, .27, .99]), function () {
                        $(elem).addClass("open-ie")
                    })
                }
                e.preventDefault()
            })
        }(window.jQuery),
        function ($) {
            "use strict";

            function Plugin(option) {
                return this.each(function () {
                    var $this = $(this),
                            data = $this.data("pg.parallax"),
                            options = "object" == typeof option && option;
                    data || $this.data("pg.parallax", data = new Parallax(this, options)), "string" == typeof option && data[option]()
                })
            }
            var Parallax = function (element, options) {
                if (this.$element = $(element), this.options = $.extend(!0, {}, $.fn.parallax.defaults, options), this.$coverPhoto = this.$element.find(".cover-photo"), this.$content = this.$element.find(".inner"), this.$coverPhoto.find("> img").length) {
                    var img = this.$coverPhoto.find("> img");
                    this.$coverPhoto.css("background-image", "url(" + img.attr("src") + ")"), img.remove()
                }
            };
            Parallax.VERSION = "1.0.0", Parallax.prototype.animate = function () {
                var scrollPos, pagecoverWidth = this.$element.height(),
                        opacityKeyFrame = 50 * pagecoverWidth / 100,
                        direction = "translateX";
                scrollPos = $(window).scrollTop(), direction = "translateY", this.$coverPhoto.css({
                    transform: direction + "(" + scrollPos * this.options.speed.coverPhoto + "px)"
                }), this.$content.css({
                    transform: direction + "(" + scrollPos * this.options.speed.content + "px)"
                }), this.$content.css(scrollPos > opacityKeyFrame ? {
                    opacity: 1 - scrollPos / 1200
                } : {
                    opacity: 1
                })
            };
            var old = $.fn.parallax;
            $.fn.parallax = Plugin, $.fn.parallax.Constructor = Parallax, $.fn.parallax.defaults = {
                speed: {
                    coverPhoto: .3,
                    content: .17
                }
            }, $.fn.parallax.noConflict = function () {
                return $.fn.parallax = old, this
            }, $(window).on("load", function () {
                $('[data-pages="parallax"]').each(function () {
                    var $parallax = $(this);
                    $parallax.parallax($parallax.data())
                })
            }), $(window).on("scroll", function () {
                Modernizr.touch || $('[data-pages="parallax"]').parallax("animate")
            })
        }(window.jQuery),
        function ($) {
            "use strict";

            function Plugin(option) {
                return this.each(function () {
                    var $this = $(this),
                            data = $this.data("pg.sidebar"),
                            options = "object" == typeof option && option;
                    data || $this.data("pg.sidebar", data = new Sidebar(this, options)), "string" == typeof option && data[option]()
                })
            }
            var Sidebar = function (element, options) {
                function sidebarMouseEnter(e) {
                    return $.Pages.isVisibleSm() || $.Pages.isVisibleXs() ? !1 : void($(".close-sidebar").data("clicked") || _this.$body.hasClass("menu-pin") || (_this.cssAnimation ? (_this.$element.css({
                        transform: _this.menuOpenCSS
                    }), _this.$body.addClass("sidebar-visible")) : _this.$element.stop().animate({
                        left: "0px"
                    }, 400, $.bez(_this.bezierEasing), function () {
                        _this.$body.addClass("sidebar-visible")
                    })))
                }

                function sidebarMouseLeave(e) {
                    if ($.Pages.isVisibleSm() || $.Pages.isVisibleXs())
                        return !1;
                    if ("undefined" != typeof e) {
                        var target = $(e.target);
                        if (target.parent(".page-sidebar").length)
                            return
                    }
                    _this.$body.hasClass("menu-pin") || ($(".sidebar-overlay-slide").hasClass("show") && ($(".sidebar-overlay-slide").removeClass("show"), $("[data-pages-toggle']").removeClass("active")), _this.cssAnimation ? (_this.$element.css({
                        transform: _this.menuClosedCSS
                    }), _this.$body.removeClass("sidebar-visible")) : _this.$element.stop().animate({
                        left: "-" + _this.sideBarWidthCondensed + "px"
                    }, 400, $.bez(_this.bezierEasing), function () {
                        _this.$body.removeClass("sidebar-visible"), setTimeout(function () {
                            $(".close-sidebar").data({
                                clicked: !1
                            })
                        }, 100)
                    }))
                }
                if (this.$element = $(element), this.options = $.extend(!0, {}, $.fn.sidebar.defaults, options), this.bezierEasing = [.05, .74, .27, .99], this.cssAnimation = !0, this.menuClosedCSS, this.menuOpenCSS, this.css3d = !0, this.sideBarWidth = 280, this.sideBarWidthCondensed = 210, this.$sidebarMenu = this.$element.find(".sidebar-menu > ul"), this.$pageContainer = $(this.options.pageContainer), this.$body = $("body"), this.$sidebarMenu.length) {
                    "desktop" == $.Pages.getUserAgent() && this.$sidebarMenu.scrollbar({
                        ignoreOverlay: !1
                    }), Modernizr.csstransitions || (this.cssAnimation = !1), Modernizr.csstransforms3d || (this.css3d = !1), this.menuOpenCSS = 1 == this.css3d ? "translate3d(" + this.sideBarWidthCondensed + "px, 0,0)" : "translate(" + this.sideBarWidthCondensed + "px, 0)", this.menuClosedCSS = 1 == this.css3d ? "translate3d(0, 0,0)" : "translate(0, 0)", $("body").on("click", ".sidebar-menu a", function (e) {
                        if ($(this).parent().children(".sub-menu") !== !1) {
                            var parent = $(this).parent().parent();
                            $(this).parent();
                            parent.children("li.open").children("a").children(".arrow").removeClass("open"), parent.children("li.open").children("a").children(".arrow").removeClass("active"), parent.children("li.open").children(".sub-menu").slideUp(200, function () {
                            }), parent.children("li").removeClass("open");
                            var sub = $(this).parent().children(".sub-menu");
                            sub.is(":visible") ? ($(".arrow", $(this)).removeClass("open"), sub.slideUp(200, function () {
                                $(this).parent().removeClass("active")
                            })) : ($(".arrow", $(this)).addClass("open"), $(this).parent().addClass("open"), sub.slideDown(200, function () {
                            }))
                        }
                    }), $(".sidebar-slide-toggle").on("click touchend", function (e) {
                        e.preventDefault(), $(this).toggleClass("active");
                        var el = $(this).attr("data-pages-toggle");
                        null != el && $(el).toggleClass("show")
                    });
                    var _this = this;
                    this.$element.bind("mouseenter mouseleave", sidebarMouseEnter), this.$pageContainer.bind("mouseover", sidebarMouseLeave)
                }
            };
            Sidebar.prototype.toggleSidebar = function (toggle) {
                var timer, bodyColor = $("body").css("background-color");
                $(".page-container").css("background-color", bodyColor), this.$body.hasClass("sidebar-open") ? (this.$body.removeClass("sidebar-open"), timer = setTimeout(function () {
                    this.$element.removeClass("visible")
                }.bind(this), 400)) : (clearTimeout(timer), this.$element.addClass("visible"), setTimeout(function () {
                    this.$body.addClass("sidebar-open")
                }.bind(this), 10), setTimeout(function () {
                    $(".page-container").css({
                        "background-color": ""
                    })
                }, 1e3))
            }, Sidebar.prototype.togglePinSidebar = function (toggle) {
                "hide" == toggle ? this.$body.removeClass("menu-pin") : "show" == toggle ? this.$body.addClass("menu-pin") : this.$body.toggleClass("menu-pin")
            };
            var old = $.fn.sidebar;
            $.fn.sidebar = Plugin, $.fn.sidebar.Constructor = Sidebar, $.fn.sidebar.defaults = {
                pageContainer: ".page-container"
            }, $.fn.sidebar.noConflict = function () {
                return $.fn.sidebar = old, this
            }, $(document).on("click.pg.sidebar.data-api", '[data-toggle-pin="sidebar"]', function (e) {
                e.preventDefault();
                var $target = ($(this), $('[data-pages="sidebar"]'));
                return $target.data("pg.sidebar").togglePinSidebar(), !1
            }), $(document).on("click.pg.sidebar.data-api touchstart", '[data-toggle="sidebar"]', function (e) {
                e.preventDefault();
                var $target = ($(this), $('[data-pages="sidebar"]'));
                return $target.data("pg.sidebar").toggleSidebar(), !1
            })
        }(window.jQuery),
        function ($) {
            "use strict";

            function Plugin(option) {
                return this.each(function () {
                    var $this = $(this),
                            data = $this.data("pg.search"),
                            options = "object" == typeof option && option;
                    data || $this.data("pg.search", data = new Search(this, options)), "string" == typeof option && data[option]()
                })
            }
            var Search = function (element, options) {
                this.$element = $(element), this.options = $.extend(!0, {}, $.fn.search.defaults, options), this.init()
            };
            Search.VERSION = "1.0.0", Search.prototype.init = function () {
                var _this = this;
                this.pressedKeys = [], this.ignoredKeys = [], this.$searchField = this.$element.find(this.options.searchField), this.$closeButton = this.$element.find(this.options.closeButton), this.$suggestions = this.$element.find(this.options.suggestions), this.$brand = this.$element.find(this.options.brand), this.$searchField.on("keyup", function (e) {
                    _this.$suggestions && _this.$suggestions.html($(this).val())
                }), this.$searchField.on("keyup", function (e) {
                    return _this.options.onKeyEnter && _this.options.onKeyEnter(_this.$searchField.val()), 13 == e.keyCode && (e.preventDefault(), _this.options.onSearchSubmit && _this.options.onSearchSubmit(_this.$searchField.val())), $("body").hasClass("overlay-disabled") ? 0 : void 0
                }), this.$closeButton.on("click", function () {
                    _this.toggleOverlay("hide")
                }), this.$element.on("click", function (e) {
                    "search" == $(e.target).data("pages") && _this.toggleOverlay("hide")
                }), $(document).on("keypress.pg.search", function (e) {
                    _this.keypress(e)
                }), $(document).on("keyup", function (e) {
                    _this.$element.is(":visible") && 27 == e.keyCode && _this.toggleOverlay("hide")
                })
            }, Search.prototype.keypress = function (e) {
                e = e || event;
                var nodeName = e.target.nodeName;
                $("body").hasClass("overlay-disabled") || $(e.target).hasClass("js-input") || "INPUT" == nodeName || "TEXTAREA" == nodeName || 0 === e.which || 0 === e.charCode || e.ctrlKey || e.metaKey || e.altKey || 27 == e.keyCode || this.toggleOverlay("show", String.fromCharCode(e.keyCode | e.charCode))
            }, Search.prototype.toggleOverlay = function (action, key) {
                var _this = this;
                "show" == action ? (this.$element.removeClass("hide"), this.$element.fadeIn("fast"), this.$searchField.is(":focus") || (this.$searchField.val(key), setTimeout(function () {
                    this.$searchField.focus();
                    var tmpStr = this.$searchField.val();
                    this.$searchField.val(""), this.$searchField.val(tmpStr)
                }.bind(this), 100)), this.$element.removeClass("closed"), this.$brand.toggleClass("invisible"), $(document).off("keypress.pg.search")) : (this.$element.fadeOut("fast").addClass("closed"), this.$searchField.val("").blur(), setTimeout(function () {
                    this.$element.is(":visible") && this.$brand.toggleClass("invisible"), $(document).on("keypress.pg.search", function (e) {
                        _this.keypress(e)
                    })
                }.bind(this), 100))
            };
            var old = $.fn.search;
            $.fn.search = Plugin, $.fn.search.Constructor = Search, $.fn.search.defaults = {
                searchField: '[data-search="searchField"]',
                closeButton: '[data-search="closeButton"]',
                suggestions: '[data-search="suggestions"]',
                brand: '[data-search="brand"]'
            }, $.fn.search.noConflict = function () {
                return $.fn.search = old, this
            }, $(document).on("click.pg.search.data-api", '[data-toggle="search"]', function (e) {
                var $this = $(this),
                        $target = $('[data-pages="search"]');
                $this.is("a") && e.preventDefault(), $target.data("pg.search").toggleOverlay("show")
            })
        }(window.jQuery),
        function ($) {
            "use strict";
            "undefined" == typeof angular && $.Pages.init()
        }(window.jQuery);
