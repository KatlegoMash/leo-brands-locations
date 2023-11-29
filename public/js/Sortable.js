!function (t) { "use strict"; "function" == typeof define && define.amd ? define(t) : "undefined" != typeof module && void 0 !== module.exports ? module.exports = t() : window.Sortable = t() }(function () { "use strict"; if ("undefined" == typeof window || !window.document) return function () { throw new Error("Sortable.js requires a window with a document") }; var t, e, n, i, o, a, r, s, l, c, d, h, u, f, p, g, v, m, b, _, D, y = {}, w = /\s+/g, T = /left|right|inline/, C = "Sortable" + (new Date).getTime(), S = window, E = S.document, x = S.parseInt, N = S.jQuery || S.Zepto, k = S.Polymer, B = !1, Y = !!("draggable" in E.createElement("div")), O = !navigator.userAgent.match(/Trident.*rv[ :]?11\./) && ((D = E.createElement("x")).style.cssText = "pointer-events:auto", "auto" === D.style.pointerEvents), X = !1, A = Math.abs, M = Math.min, P = [], R = [], I = et(function (t, e, n) { if (n && e.scroll) { var i, o, a, r, d, h, u = n[C], f = e.scrollSensitivity, p = e.scrollSpeed, g = t.clientX, v = t.clientY, m = window.innerWidth, b = window.innerHeight; if (l !== n && (s = e.scroll, l = n, c = e.scrollFn, !0 === s)) { s = n; do { if (s.offsetWidth < s.scrollWidth || s.offsetHeight < s.scrollHeight) break } while (s = s.parentNode) } s && (i = s, o = s.getBoundingClientRect(), a = (A(o.right - g) <= f) - (A(o.left - g) <= f), r = (A(o.bottom - v) <= f) - (A(o.top - v) <= f)), a || r || (r = (b - v <= f) - (v <= f), ((a = (m - g <= f) - (g <= f)) || r) && (i = S)), y.vx === a && y.vy === r && y.el === i || (y.el = i, y.vx = a, y.vy = r, clearInterval(y.pid), i && (y.pid = setInterval(function () { if (h = r ? r * p : 0, d = a ? a * p : 0, "function" == typeof c) return c.call(u, d, h, t); i === S ? S.scrollTo(S.pageXOffset + d, S.pageYOffset + h) : (i.scrollTop += h, i.scrollLeft += d) }, 24))) } }, 30), L = function (t) { function e(t, e) { return void 0 !== t && !0 !== t || (t = n.name), "function" == typeof t ? t : function (n, i) { var o = i.options.group.name; return e ? t : t && (t.join ? t.indexOf(o) > -1 : o == t) } } var n = {}, i = t.group; i && "object" == typeof i || (i = { name: i }), n.name = i.name, n.checkPull = e(i.pull, !0), n.checkPut = e(i.put), n.revertClone = i.revertClone, t.group = n }; function F(t, e) { if (!t || !t.nodeType || 1 !== t.nodeType) throw "Sortable: `el` must be HTMLElement, and not " + {}.toString.call(t); this.el = t, this.options = e = nt({}, e), t[C] = this; var n = { group: Math.random(), sort: !0, disabled: !1, store: null, handle: null, scroll: !0, scrollSensitivity: 30, scrollSpeed: 10, draggable: /[uo]l/i.test(t.nodeName) ? "li" : ">*", ghostClass: "sortable-ghost", chosenClass: "sortable-chosen", dragClass: "sortable-drag", ignore: "a, img", filter: null, preventOnFilter: !0, animation: 0, setData: function (t, e) { t.setData("Text", e.textContent) }, dropBubble: !1, dragoverBubble: !1, dataIdAttr: "data-id", delay: 0, forceFallback: !1, fallbackClass: "sortable-fallback", fallbackOnBody: !1, fallbackTolerance: 0, fallbackOffset: { x: 0, y: 0 } }; for (var i in n) !(i in e) && (e[i] = n[i]); L(e); for (var o in this) "_" === o.charAt(0) && "function" == typeof this[o] && (this[o] = this[o].bind(this)); this.nativeDraggable = !e.forceFallback && Y, H(t, "mousedown", this._onTapStart), H(t, "touchstart", this._onTapStart), H(t, "pointerdown", this._onTapStart), this.nativeDraggable && (H(t, "dragover", this), H(t, "dragenter", this)), R.push(this._onDragOver), e.store && this.sort(e.store.get(this)) } function U(e, n) { "clone" !== e.lastPullMode && (n = !0), i && i.state !== n && (q(i, "display", n ? "none" : ""), n || i.state && (e.options.group.revertClone ? (o.insertBefore(i, a), e._animate(t, i)) : o.insertBefore(i, t)), i.state = n) } function j(t, e, n) { if (t) { n = n || E; do { if (">*" === e && t.parentNode === n || tt(t, e)) return t } while (void 0, t = (o = (i = t).host) && o.nodeType ? o : i.parentNode) } var i, o; return null } function H(t, e, n) { t.addEventListener(e, n, B) } function W(t, e, n) { t.removeEventListener(e, n, B) } function V(t, e, n) { if (t) if (t.classList) t.classList[n ? "add" : "remove"](e); else { var i = (" " + t.className + " ").replace(w, " ").replace(" " + e + " ", " "); t.className = (i + (n ? " " + e : "")).replace(w, " ") } } function q(t, e, n) { var i = t && t.style; if (i) { if (void 0 === n) return E.defaultView && E.defaultView.getComputedStyle ? n = E.defaultView.getComputedStyle(t, "") : t.currentStyle && (n = t.currentStyle), void 0 === e ? n : n[e]; e in i || (e = "-webkit-" + e), i[e] = n + ("string" == typeof n ? "" : "px") } } function z(t, e, n) { if (t) { var i = t.getElementsByTagName(e), o = 0, a = i.length; if (n) for (; o < a; o++)n(i[o], o); return i } return [] } function G(t, e, n, o, a, r, s) { t = t || e[C]; var l = E.createEvent("Event"), c = t.options, d = "on" + n.charAt(0).toUpperCase() + n.substr(1); l.initEvent(n, !0, !0), l.to = e, l.from = a || e, l.item = o || e, l.clone = i, l.oldIndex = r, l.newIndex = s, e.dispatchEvent(l), c[d] && c[d].call(t, l) } function Q(t, e, n, i, o, a, r, s) { var l, c, d = t[C], h = d.options.onMove; return (l = E.createEvent("Event")).initEvent("move", !0, !0), l.to = e, l.from = t, l.dragged = n, l.draggedRect = i, l.related = o || e, l.relatedRect = a || e.getBoundingClientRect(), l.willInsertAfter = s, t.dispatchEvent(l), h && (c = h.call(d, l, r)), c } function Z(t) { t.draggable = !1 } function J() { X = !1 } function K(t) { for (var e = t.tagName + t.className + t.src + t.href + t.textContent, n = e.length, i = 0; n--;)i += e.charCodeAt(n); return i.toString(36) } function $(t, e) { var n = 0; if (!t || !t.parentNode) return -1; for (; t && (t = t.previousElementSibling);)"TEMPLATE" === t.nodeName.toUpperCase() || ">*" !== e && !tt(t, e) || n++; return n } function tt(t, e) { if (t) { var n = (e = e.split(".")).shift().toUpperCase(), i = new RegExp("\\s(" + e.join("|") + ")(?=\\s)", "g"); return !("" !== n && t.nodeName.toUpperCase() != n || e.length && ((" " + t.className + " ").match(i) || []).length != e.length) } return !1 } function et(t, e) { var n, i; return function () { void 0 === n && (n = arguments, i = this, setTimeout(function () { 1 === n.length ? t.call(i, n[0]) : t.apply(i, n), n = void 0 }, e)) } } function nt(t, e) { if (t && e) for (var n in e) e.hasOwnProperty(n) && (t[n] = e[n]); return t } function it(t) { return N ? N(t).clone(!0)[0] : k && k.dom ? k.dom(t).cloneNode(!0) : t.cloneNode(!0) } F.prototype = { constructor: F, _onTapStart: function (e) { var n, i = this, o = this.el, a = this.options, s = a.preventOnFilter, l = e.type, c = e.touches && e.touches[0], d = (c || e).target, h = e.target.shadowRoot && e.path && e.path[0] || d, u = a.filter; if (function (t) { var e = t.getElementsByTagName("input"), n = e.length; for (; n--;) { var i = e[n]; i.checked && P.push(i) } }(o), !t && !(/mousedown|pointerdown/.test(l) && 0 !== e.button || a.disabled) && (d = j(d, a.draggable, o)) && r !== d) { if (n = $(d, a.draggable), "function" == typeof u) { if (u.call(this, e, d, this)) return G(i, h, "filter", d, o, n), void (s && e.preventDefault()) } else if (u && (u = u.split(",").some(function (t) { if (t = j(h, t.trim(), o)) return G(i, t, "filter", d, o, n), !0 }))) return void (s && e.preventDefault()); a.handle && !j(h, a.handle, o) || this._prepareDragStart(e, c, d, n) } }, _prepareDragStart: function (n, i, s, l) { var c, d = this, h = d.el, u = d.options, p = h.ownerDocument; s && !t && s.parentNode === h && (m = n, o = h, e = (t = s).parentNode, a = t.nextSibling, r = s, g = u.group, f = l, this._lastX = (i || n).clientX, this._lastY = (i || n).clientY, t.style["will-change"] = "transform", c = function () { d._disableDelayedDrag(), t.draggable = d.nativeDraggable, V(t, u.chosenClass, !0), d._triggerDragStart(n, i), G(d, o, "choose", t, o, f) }, u.ignore.split(",").forEach(function (e) { z(t, e.trim(), Z) }), H(p, "mouseup", d._onDrop), H(p, "touchend", d._onDrop), H(p, "touchcancel", d._onDrop), H(p, "pointercancel", d._onDrop), H(p, "selectstart", d), u.delay ? (H(p, "mouseup", d._disableDelayedDrag), H(p, "touchend", d._disableDelayedDrag), H(p, "touchcancel", d._disableDelayedDrag), H(p, "mousemove", d._disableDelayedDrag), H(p, "touchmove", d._disableDelayedDrag), H(p, "pointermove", d._disableDelayedDrag), d._dragStartTimer = setTimeout(c, u.delay)) : c()) }, _disableDelayedDrag: function () { var t = this.el.ownerDocument; clearTimeout(this._dragStartTimer), W(t, "mouseup", this._disableDelayedDrag), W(t, "touchend", this._disableDelayedDrag), W(t, "touchcancel", this._disableDelayedDrag), W(t, "mousemove", this._disableDelayedDrag), W(t, "touchmove", this._disableDelayedDrag), W(t, "pointermove", this._disableDelayedDrag) }, _triggerDragStart: function (e, n) { (n = n || ("touch" == e.pointerType ? e : null)) ? (m = { target: t, clientX: n.clientX, clientY: n.clientY }, this._onDragStart(m, "touch")) : this.nativeDraggable ? (H(t, "dragend", this), H(o, "dragstart", this._onDragStart)) : this._onDragStart(m, !0); try { E.selection ? setTimeout(function () { E.selection.empty() }) : window.getSelection().removeAllRanges() } catch (t) { } }, _dragStarted: function () { if (o && t) { var e = this.options; V(t, e.ghostClass, !0), V(t, e.dragClass, !1), F.active = this, G(this, o, "start", t, o, f) } else this._nulling() }, _emulateDragOver: function () { if (b) { if (this._lastX === b.clientX && this._lastY === b.clientY) return; this._lastX = b.clientX, this._lastY = b.clientY, O || q(n, "display", "none"); var t = E.elementFromPoint(b.clientX, b.clientY), e = t, i = R.length; if (e) do { if (e[C]) { for (; i--;)R[i]({ clientX: b.clientX, clientY: b.clientY, target: t, rootEl: e }); break } t = e } while (e = e.parentNode); O || q(n, "display", "") } }, _onTouchMove: function (t) { if (m) { var e = this.options, i = e.fallbackTolerance, o = e.fallbackOffset, a = t.touches ? t.touches[0] : t, r = a.clientX - m.clientX + o.x, s = a.clientY - m.clientY + o.y, l = t.touches ? "translate3d(" + r + "px," + s + "px,0)" : "translate(" + r + "px," + s + "px)"; if (!F.active) { if (i && M(A(a.clientX - this._lastX), A(a.clientY - this._lastY)) < i) return; this._dragStarted() } this._appendGhost(), _ = !0, b = a, q(n, "webkitTransform", l), q(n, "mozTransform", l), q(n, "msTransform", l), q(n, "transform", l), t.preventDefault() } }, _appendGhost: function () { if (!n) { var e, i = t.getBoundingClientRect(), a = q(t), r = this.options; V(n = t.cloneNode(!0), r.ghostClass, !1), V(n, r.fallbackClass, !0), V(n, r.dragClass, !0), q(n, "top", i.top - x(a.marginTop, 10)), q(n, "left", i.left - x(a.marginLeft, 10)), q(n, "width", i.width), q(n, "height", i.height), q(n, "opacity", "0.8"), q(n, "position", "fixed"), q(n, "zIndex", "100000"), q(n, "pointerEvents", "none"), r.fallbackOnBody && E.body.appendChild(n) || o.appendChild(n), e = n.getBoundingClientRect(), q(n, "width", 2 * i.width - e.width), q(n, "height", 2 * i.height - e.height) } }, _onDragStart: function (e, n) { var a = e.dataTransfer, r = this.options; this._offUpEvents(), g.checkPull(this, this, t, e) && ((i = it(t)).draggable = !1, i.style["will-change"] = "", q(i, "display", "none"), V(i, this.options.chosenClass, !1), o.insertBefore(i, t), G(this, o, "clone", t)), V(t, r.dragClass, !0), n ? ("touch" === n ? (H(E, "touchmove", this._onTouchMove), H(E, "touchend", this._onDrop), H(E, "touchcancel", this._onDrop), H(E, "pointermove", this._onTouchMove), H(E, "pointerup", this._onDrop)) : (H(E, "mousemove", this._onTouchMove), H(E, "mouseup", this._onDrop)), this._loopId = setInterval(this._emulateDragOver, 50)) : (a && (a.effectAllowed = "move", r.setData && r.setData.call(this, a, t)), H(E, "drop", this), setTimeout(this._dragStarted, 0)) }, _onDragOver: function (r) { var s, l, c, f, p, m, b = this.el, D = this.options, y = D.group, w = F.active, S = g === y, E = !1, x = D.sort; if ((void 0 !== r.preventDefault && (r.preventDefault(), !D.dragoverBubble && r.stopPropagation()), !t.animated) && (_ = !0, w && !D.disabled && (S ? x || (f = !o.contains(t)) : v === this || (w.lastPullMode = g.checkPull(this, w, t, r)) && y.checkPut(this, w, t, r)) && (void 0 === r.rootEl || r.rootEl === this.el))) { if (I(r, D, this.el), X) return; if (s = j(r.target, D.draggable, b), l = t.getBoundingClientRect(), v !== this && (v = this, E = !0), f) return U(w, !0), e = o, void (i || a ? o.insertBefore(t, i || a) : x || o.appendChild(t)); if (0 === b.children.length || b.children[0] === n || b === r.target && (p = r, m = b.lastElementChild.getBoundingClientRect(), p.clientY - (m.top + m.height) > 5 || p.clientX - (m.left + m.width) > 5)) { if (0 !== b.children.length && b.children[0] !== n && b === r.target && (s = b.lastElementChild), s) { if (s.animated) return; c = s.getBoundingClientRect() } U(w, S), !1 !== Q(o, b, t, l, s, c, r) && (t.contains(b) || (b.appendChild(t), e = b), this._animate(l, t), s && this._animate(c, s)) } else if (s && !s.animated && s !== t && void 0 !== s.parentNode[C]) { d !== s && (d = s, h = q(s), u = q(s.parentNode)); var N = (c = s.getBoundingClientRect()).right - c.left, k = c.bottom - c.top, B = T.test(h.cssFloat + h.display) || "flex" == u.display && 0 === u["flex-direction"].indexOf("row"), Y = s.offsetWidth > t.offsetWidth, O = s.offsetHeight > t.offsetHeight, A = (B ? (r.clientX - c.left) / N : (r.clientY - c.top) / k) > .5, M = s.nextElementSibling, P = !1; if (B) { var R = t.offsetTop, L = s.offsetTop; P = R === L ? s.previousElementSibling === t && !Y || A && Y : s.previousElementSibling === t || t.previousElementSibling === s ? (r.clientY - c.top) / k > .5 : L > R } else E || (P = M !== t && !O || A && O); var H = Q(o, b, t, l, s, c, r, P); !1 !== H && (1 !== H && -1 !== H || (P = 1 === H), X = !0, setTimeout(J, 30), U(w, S), t.contains(b) || (P && !M ? b.appendChild(t) : s.parentNode.insertBefore(t, P ? M : s)), e = t.parentNode, this._animate(l, t), this._animate(c, s)) } } }, _animate: function (t, e) { var n = this.options.animation; if (n) { var i = e.getBoundingClientRect(); 1 === t.nodeType && (t = t.getBoundingClientRect()), q(e, "transition", "none"), q(e, "transform", "translate3d(" + (t.left - i.left) + "px," + (t.top - i.top) + "px,0)"), e.offsetWidth, q(e, "transition", "all " + n + "ms"), q(e, "transform", "translate3d(0,0,0)"), clearTimeout(e.animated), e.animated = setTimeout(function () { q(e, "transition", ""), q(e, "transform", ""), e.animated = !1 }, n) } }, _offUpEvents: function () { var t = this.el.ownerDocument; W(E, "touchmove", this._onTouchMove), W(E, "pointermove", this._onTouchMove), W(t, "mouseup", this._onDrop), W(t, "touchend", this._onDrop), W(t, "pointerup", this._onDrop), W(t, "touchcancel", this._onDrop), W(t, "pointercancel", this._onDrop), W(t, "selectstart", this) }, _onDrop: function (r) { var s = this.el, l = this.options; clearInterval(this._loopId), clearInterval(y.pid), clearTimeout(this._dragStartTimer), W(E, "mousemove", this._onTouchMove), this.nativeDraggable && (W(E, "drop", this), W(s, "dragstart", this._onDragStart)), this._offUpEvents(), r && (_ && (r.preventDefault(), !l.dropBubble && r.stopPropagation()), n && n.parentNode && n.parentNode.removeChild(n), o !== e && "clone" === F.active.lastPullMode || i && i.parentNode && i.parentNode.removeChild(i), t && (this.nativeDraggable && W(t, "dragend", this), Z(t), t.style["will-change"] = "", V(t, this.options.ghostClass, !1), V(t, this.options.chosenClass, !1), G(this, o, "unchoose", t, o, f), o !== e ? (p = $(t, l.draggable)) >= 0 && (G(null, e, "add", t, o, f, p), G(this, o, "remove", t, o, f, p), G(null, e, "sort", t, o, f, p), G(this, o, "sort", t, o, f, p)) : t.nextSibling !== a && (p = $(t, l.draggable)) >= 0 && (G(this, o, "update", t, o, f, p), G(this, o, "sort", t, o, f, p)), F.active && (null != p && -1 !== p || (p = f), G(this, o, "end", t, o, f, p), this.save()))), this._nulling() }, _nulling: function () { o = t = e = n = a = i = r = s = l = m = b = _ = p = d = h = v = g = F.active = null, P.forEach(function (t) { t.checked = !0 }), P.length = 0 }, handleEvent: function (e) { switch (e.type) { case "drop": case "dragend": this._onDrop(e); break; case "dragover": case "dragenter": t && (this._onDragOver(e), function (t) { t.dataTransfer && (t.dataTransfer.dropEffect = "move"); t.preventDefault() }(e)); break; case "selectstart": e.preventDefault() } }, toArray: function () { for (var t, e = [], n = this.el.children, i = 0, o = n.length, a = this.options; i < o; i++)j(t = n[i], a.draggable, this.el) && e.push(t.getAttribute(a.dataIdAttr) || K(t)); return e }, sort: function (t) { var e = {}, n = this.el; this.toArray().forEach(function (t, i) { var o = n.children[i]; j(o, this.options.draggable, n) && (e[t] = o) }, this), t.forEach(function (t) { e[t] && (n.removeChild(e[t]), n.appendChild(e[t])) }) }, save: function () { var t = this.options.store; t && t.set(this) }, closest: function (t, e) { return j(t, e || this.options.draggable, this.el) }, option: function (t, e) { var n = this.options; if (void 0 === e) return n[t]; n[t] = e, "group" === t && L(n) }, destroy: function () { var t = this.el; t[C] = null, W(t, "mousedown", this._onTapStart), W(t, "touchstart", this._onTapStart), W(t, "pointerdown", this._onTapStart), this.nativeDraggable && (W(t, "dragover", this), W(t, "dragenter", this)), Array.prototype.forEach.call(t.querySelectorAll("[draggable]"), function (t) { t.removeAttribute("draggable") }), R.splice(R.indexOf(this._onDragOver), 1), this._onDrop(), this.el = t = null } }, H(E, "touchmove", function (t) { F.active && t.preventDefault() }); try { window.addEventListener("test", null, Object.defineProperty({}, "passive", { get: function () { B = { capture: !1, passive: !1 } } })) } catch (t) { } return F.utils = { on: H, off: W, css: q, find: z, is: function (t, e) { return !!j(t, e, t) }, extend: nt, throttle: et, closest: j, toggleClass: V, clone: it, index: $ }, F.create = function (t, e) { return new F(t, e) }, F.version = "1.6.1", F });