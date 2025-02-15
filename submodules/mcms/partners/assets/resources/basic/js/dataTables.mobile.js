/*!
 Responsive 2.1.0
 2014-2016 SpryMedia Ltd - datatables.net/license
*/
(function(c) {
    "function" === typeof define && define.amd ? define(["jquery", "datatables.net"], function(l) {
        return c(l, window, document)
    }) : "object" === typeof exports ? module.exports = function(l, k) {
        l || (l = window);
        if (!k || !k.fn.dataTable)
            k = require("datatables.net")(l, k).$;
        return c(k, l, l.document)
    }
    : c(jQuery, window, document)
})(function(c, l, k, p) {
    var m = c.fn.dataTable
      , j = function(a, b) {
        if (!m.versionCheck || !m.versionCheck("1.10.3"))
            throw "DataTables Responsive requires DataTables 1.10.3 or newer";
        this.s = {
            dt: new m.Api(a),
            columns: [],
            current: []
        };
        this.s.dt.settings()[0].responsive || (b && "string" === typeof b.details ? b.details = {
            type: b.details
        } : b && !1 === b.details ? b.details = {
            type: !1
        } : b && !0 === b.details && (b.details = {
            type: "inline"
        }),
        this.c = c.extend(!0, {}, j.defaults, m.defaults.responsive, b),
        a.responsive = this,
        this._constructor())
    }
    ;
    c.extend(j.prototype, {
        _constructor: function() {
            var a = this
              , b = this.s.dt
              , d = b.settings()[0]
              , e = c(l).width();
            b.settings()[0]._responsive = this;
            c(l).on("resize.dtr orientationchange.dtr", m.util.throttle(function() {
                var b = c(l).width();
                b !== e && (a._resize(),
                e = b)
            }));
            d.oApi._fnCallbackReg(d, "aoRowCreatedCallback", function(e) {
                -1 !== c.inArray(!1, a.s.current) && c("td, th", e).each(function(e) {
                    e = b.column.index("toData", e);
                    !1 === a.s.current[e] && c(this).css("display", "none")
                })
            });
            b.on("destroy.dtr", function() {
                b.off(".dtr");
                c(b.table().body()).off(".dtr");
                c(l).off("resize.dtr orientationchange.dtr");
                c.each(a.s.current, function(b, e) {
                    !1 === e && a._setColumnVis(b, !0)
                })
            });
            this.c.breakpoints.sort(function(a, b) {
                return a.width < b.width ? 1 : a.width > b.width ? -1 : 0
            });
            this._classLogic();
            this._resizeAuto();
            d = this.c.details;
            !1 !== d.type && (a._detailsInit(),
            b.on("column-visibility.dtr", function() {
                a._classLogic();
                a._resizeAuto();
                a._resize()
            }),
            b.on("draw.dtr", function() {
                a._redrawChildren()
            }),
            c(b.table().node()).addClass("dtr-" + d.type));
            b.on("column-reorder.dtr", function() {
                a._classLogic();
                a._resizeAuto();
                a._resize()
            });
            b.on("column-sizing.dtr", function() {
                a._resizeAuto();
                a._resize()
            });
            b.on("init.dtr", function() {
                a._resizeAuto();
                a._resize();
                c.inArray(false, a.s.current) && b.columns.adjust()
            });
            this._resize()
        },
        _columnsVisiblity: function(a) {
            var b = this.s.dt, d = this.s.columns, e, f, g = d.map(function(a, b) {
                return {
                    columnIdx: b,
                    priority: a.priority
                }
            }).sort(function(a, b) {
                return a.priority !== b.priority ? a.priority - b.priority : a.columnIdx - b.columnIdx
            }), h = c.map(d, function(b) {
                return b.auto && null === b.minWidth ? !1 : !0 === b.auto ? "-" : -1 !== c.inArray(a, b.includeIn)
            }), n = 0;
            e = 0;
            for (f = h.length; e < f; e++)
                !0 === h[e] && (n += d[e].minWidth);
            e = b.settings()[0].oScroll;
            e = e.sY || e.sX ? e.iBarWidth : 0;
            b = b.table().container().offsetWidth - e - n;
            e = 0;
            for (f = h.length; e < f; e++)
                d[e].control && (b -= d[e].minWidth);
            n = !1;
            e = 0;
            for (f = g.length; e < f; e++) {
                var i = g[e].columnIdx;
                "-" === h[i] && (!d[i].control && d[i].minWidth) && (n || 0 > b - d[i].minWidth ? (n = !0,
                h[i] = !1) : h[i] = !0,
                b -= d[i].minWidth)
            }
            g = !1;
            e = 0;
            for (f = d.length; e < f; e++)
                if (!d[e].control && !d[e].never && !h[e]) {
                    g = !0;
                    break
                }
            e = 0;
            for (f = d.length; e < f; e++)
                d[e].control && (h[e] = g);
            -1 === c.inArray(!0, h) && (h[0] = !0);
            return h
        },
        _classLogic: function() {
            var a = this
              , b = this.c.breakpoints
              , d = this.s.dt
              , e = d.columns().eq(0).map(function(a) {
                var b = this.column(a)
                  , e = b.header().className
                  , a = d.settings()[0].aoColumns[a].responsivePriority;
                a === p && (b = c(b.header()).data("priority"),
                a = b !== p ? 1 * b : 1E4);
                return {
                    className: e,
                    includeIn: [],
                    auto: !1,
                    control: !1,
                    never: e.match(/\bnever\b/) ? !0 : !1,
                    priority: a
                }
            })
              , f = function(a, b) {
                var d = e[a].includeIn;
                -1 === c.inArray(b, d) && d.push(b)
            }
              , g = function(c, d, i, g) {
                if (i)
                    if ("max-" === i) {
                        g = a._find(d).width;
                        d = 0;
                        for (i = b.length; d < i; d++)
                            b[d].width <= g && f(c, b[d].name)
                    } else if ("min-" === i) {
                        g = a._find(d).width;
                        d = 0;
                        for (i = b.length; d < i; d++)
                            b[d].width >= g && f(c, b[d].name)
                    } else {
                        if ("not-" === i) {
                            d = 0;
                            for (i = b.length; d < i; d++)
                                -1 === b[d].name.indexOf(g) && f(c, b[d].name)
                        }
                    }
                else
                    e[c].includeIn.push(d)
            }
            ;
            e.each(function(a, e) {
                for (var d = a.className.split(" "), f = !1, j = 0, l = d.length; j < l; j++) {
                    var k = c.trim(d[j]);
                    if ("all" === k) {
                        f = !0;
                        a.includeIn = c.map(b, function(a) {
                            return a.name
                        });
                        return
                    }
                    if ("none" === k || a.never) {
                        f = !0;
                        return
                    }
                    if ("control" === k) {
                        f = !0;
                        a.control = !0;
                        return
                    }
                    c.each(b, function(a, b) {
                        var d = b.name.split("-")
                          , c = k.match(RegExp("(min\\-|max\\-|not\\-)?(" + d[0] + ")(\\-[_a-zA-Z0-9])?"));
                        c && (f = !0,
                        c[2] === d[0] && c[3] === "-" + d[1] ? g(e, b.name, c[1], c[2] + c[3]) : c[2] === d[0] && !c[3] && g(e, b.name, c[1], c[2]))
                    })
                }
                f || (a.auto = !0)
            });
            this.s.columns = e
        },
        _detailsDisplay: function(a, b) {
            var d = this
              , e = this.s.dt
              , f = this.c.details;
            if (f && !1 !== f.type) {
                var g = f.display(a, b, function() {
                    return f.renderer(e, a[0], d._detailsObj(a[0]))
                });
                (!0 === g || !1 === g) && c(e.table().node()).triggerHandler("responsive-display.dt", [e, a, g, b])
            }
        },
        _detailsInit: function() {
            var a = this
              , b = this.s.dt
              , d = this.c.details;
            "inline" === d.type && (d.target = "td:first-child, th:first-child");
            b.on("draw.dtr", function() {
                a._tabIndexes()
            });
            a._tabIndexes();
            c(b.table().body()).on("keyup.dtr", "td, th", function(a) {
                a.keyCode === 13 && c(this).data("dtr-keyboard") && c(this).click()
            });
            var e = d.target;
            c(b.table().body()).on("click.dtr mousedown.dtr mouseup.dtr", "string" === typeof e ? e : "td, th", function(d) {
                if (c(b.table().node()).hasClass("collapsed") && b.row(c(this).closest("tr")).length) {
                    if (typeof e === "number") {
                        var g = e < 0 ? b.columns().eq(0).length + e : e;
                        if (b.cell(this).index().column !== g)
                            return
                    }
                    g = b.row(c(this).closest("tr"));
                    d.type === "click" ? '' : d.type === "mousedown" ? c(this).css("outline", "none") : d.type === "mouseup" && c(this).blur().css("outline", "")
                }
            })
        },
        _detailsObj: function(a) {
            var b = this
              , d = this.s.dt;
            return c.map(this.s.columns, function(e, c) {
                if (!e.never && !e.control)
                    return {
                        title: d.settings()[0].aoColumns[c].sTitle,
                        data: d.cell(a, c).render(b.c.orthogonal),
                        hidden: d.column(c).visible() && !b.s.current[c],
                        columnIndex: c,
                        rowIndex: a
                    }
            })
        },
        _find: function(a) {
            for (var b = this.c.breakpoints, d = 0, c = b.length; d < c; d++)
                if (b[d].name === a)
                    return b[d]
        },
        _redrawChildren: function() {
            var a = this
              , b = this.s.dt;
            b.rows({
                page: "current"
            }).iterator("row", function(c, e) {
                b.row(e);
                a._detailsDisplay(b.row(e), !0)
            })
        },
        _resize: function() {
            var a = this, b = this.s.dt, d = c(l).width(), e = this.c.breakpoints, f = e[0].name, g = this.s.columns, h, j = this.s.current.slice();
            for (h = e.length - 1; 0 <= h; h--)
                if (d <= e[h].width) {
                    f = e[h].name;
                    break
                }
            var i = this._columnsVisiblity(f);
            this.s.current = i;
            e = !1;
            h = 0;
            for (d = g.length; h < d; h++)
                if (!1 === i[h] && !g[h].never && !g[h].control) {
                    e = !0;
                    break
                }
            c(b.table().node()).toggleClass("collapsed", e);
            var k = !1;
            b.columns().eq(0).each(function(b, c) {
                i[c] !== j[c] && (k = !0,
                a._setColumnVis(b, i[c]))
            });
            k && (this._redrawChildren(),
            c(b.table().node()).trigger("responsive-resize.dt", [b, this.s.current]))
        },
        _resizeAuto: function() {
            var a = this.s.dt
              , b = this.s.columns;
            if (this.c.auto && -1 !== c.inArray(!0, c.map(b, function(a) {
                return a.auto
            }))) {
                a.table().node();
                var d = a.table().node().cloneNode(!1)
                  , e = c(a.table().header().cloneNode(!1)).appendTo(d)
                  , f = c(a.table().body()).clone(!1, !1).empty().appendTo(d)
                  , g = a.columns().header().filter(function(b) {
                    return a.column(b).visible()
                }).to$().clone(!1).css("display", "table-cell");
                c(f).append(c(a.rows({
                    page: "current"
                }).nodes()).clone(!1)).find("th, td").css("display", "");
                if (f = a.table().footer()) {
                    var f = c(f.cloneNode(!1)).appendTo(d)
                      , h = a.columns().footer().filter(function(b) {
                        return a.column(b).visible()
                    }).to$().clone(!1).css("display", "table-cell");
                    c("<tr/>").append(h).appendTo(f)
                }
                c("<tr/>").append(g).appendTo(e);
                "inline" === this.c.details.type && c(d).addClass("dtr-inline collapsed");
                c(d).find("[name]").removeAttr("name");
                d = c("<div/>").css({
                    width: 1,
                    height: 1,
                    overflow: "hidden"
                }).append(d);
                d.insertBefore(a.table().node());
                g.each(function(c) {
                    c = a.column.index("fromVisible", c);
                    b[c].minWidth = this.offsetWidth || 0
                });
                d.remove()
            }
        },
        _setColumnVis: function(a, b) {
            var d = this.s.dt
              , e = b ? "" : "none";
            c(d.column(a).header()).css("display", e);
            c(d.column(a).footer()).css("display", e);
            d.column(a).nodes().to$().css("display", e)
        },
        _tabIndexes: function() {
            var a = this.s.dt
              , b = a.cells({
                page: "current"
            }).nodes().to$()
              , d = a.settings()[0]
              , e = this.c.details.target;
            b.filter("[data-dtr-keyboard]").removeData("[data-dtr-keyboard]");
            c("number" === typeof e ? ":eq(" + e + ")" : e, a.rows({
                page: "current"
            }).nodes()).attr("tabIndex", d.iTabIndex).data("dtr-keyboard", 1)
        }
    });
    j.breakpoints = [{
        name: "desktop",
        width: Infinity
    }, {
        name: "tablet-l",
        width: 1024
    }, {
        name: "tablet-p",
        width: 768
    }, {
        name: "mobile-l",
        width: 480
    }, {
        name: "mobile-p",
        width: 320
    }];
    j.display = {
        childRow: function(a, b, d) {
            if (b) {
                if (c(a.node()).hasClass("parent"))
                    return a.child(d(), "child").show(),
                    !0
            } else {
                if (a.child.isShown())
                    return a.child(!1),
                    c(a.node()).removeClass("parent"),
                    !1;
                a.child(d(), "child").show();
                c(a.node()).addClass("parent");
                return !0
            }
        },
        childRowImmediate: function(a, b, d) {
            if (!b && a.child.isShown() || !a.responsive.hasHidden())
                return a.child(!1),
                c(a.node()).removeClass("parent"),
                !1;
            a.child(d(), "child").show();
            c(a.node()).addClass("parent");
            return !0
        },
        modal: function(a) {
            return function(b, d, e) {
                if (d)
                    c("div.dtr-modal-content").empty().append(e());
                else {
                    var f = function() {
                        g.remove();
                        c(k).off("keypress.dtr")
                    }
                      , g = c('<div class="dtr-modal"/>').append(c('<div class="dtr-modal-display"/>').append(c('<div class="dtr-modal-content"/>').append(e())).append(c('<div class="dtr-modal-close">&times;</div>').click(function() {
                        f()
                    }))).append(c('<div class="dtr-modal-background"/>').click(function() {
                        f()
                    })).appendTo("body");
                    c(k).on("keyup.dtr", function(a) {
                        27 === a.keyCode && (a.stopPropagation(),
                        f())
                    })
                }
                a && a.header && c("div.dtr-modal-content").prepend("<h2>" + a.header(b) + "</h2>")
            }
        }
    };
    j.renderer = {
        listHidden: function() {
            return function(a, b, d) {
                return (a = c.map(d, function(a) {
                    return a.hidden ? '<li data-dtr-index="' + a.columnIndex + '" data-dt-row="' + a.rowIndex + '" data-dt-column="' + a.columnIndex + '"><span class="dtr-title">' + a.title + '</span> <span class="dtr-data">' + a.data + "</span></li>" : ""
                }).join("")) ? c('<ul data-dtr-index="' + b + '"/>').append(a) : !1
            }
        },
        tableAll: function(a) {
            a = c.extend({
                tableClass: ""
            }, a);
            return function(b, d, e) {
                b = c.map(e, function(a) {
                    return '<tr data-dt-row="' + a.rowIndex + '" data-dt-column="' + a.columnIndex + '"><td>' + a.title + ":</td> <td>" + a.data + "</td></tr>"
                }).join("");
                return c('<table class="' + a.tableClass + '" width="100%"/>').append(b)
            }
        }
    };
    j.defaults = {
        breakpoints: j.breakpoints,
        auto: !0,
        details: {
            display: j.display.childRow,
            renderer: j.renderer.listHidden(),
            target: 0,
            type: "inline"
        },
        orthogonal: "display"
    };
    var o = c.fn.dataTable.Api;
    o.register("responsive()", function() {
        return this
    });
    o.register("responsive.index()", function(a) {
        a = c(a);
        return {
            column: a.data("dtr-index"),
            row: a.parent().data("dtr-index")
        }
    });
    o.register("responsive.rebuild()", function() {
        return this.iterator("table", function(a) {
            a._responsive && a._responsive._classLogic()
        })
    });
    o.register("responsive.recalc()", function() {
        return this.iterator("table", function(a) {
            a._responsive && (a._responsive._resizeAuto(),
            a._responsive._resize())
        })
    });
    o.register("responsive.hasHidden()", function() {
        var a = this.context[0];
        return a._responsive ? -1 !== c.inArray(!1, a._responsive.s.current) : !1
    });
    j.version = "2.1.0";
    c.fn.dataTable.Responsive = j;
    c.fn.DataTable.Responsive = j;
    c(k).on("preInit.dt.dtr", function(a, b) {
        if ("dt" === a.namespace && (c(b.nTable).hasClass("responsive") || c(b.nTable).hasClass("dt-responsive") || b.oInit.responsive || m.defaults.responsive)) {
            var d = b.oInit.responsive;
            !1 !== d && new j(b,c.isPlainObject(d) ? d : {})
        }
    });
    return j
});
