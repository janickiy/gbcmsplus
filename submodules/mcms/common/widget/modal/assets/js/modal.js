(function ($) {
  var ModalWidget = function (params) {
    var modal,
      api = {
        init: function () {
          modal = modal || new _modal();
        },
        open: function (id) {
          return modal.open(id);
        },
        close: function (id) {
          return modal.close(id);
        },
        empty: function (id) {
          return modal.empty(id);
        },
        reload: function () {
          return modal.reload();
        },
        getId: function () {
          return modal.getId();
        }
      };

    var _modal = function (params) {
      this._modalId = null;
      this._modalParams = {};
      this._modals = {};
      this._openedModals = [];
      this.params = {
        buttonClass: '[data-toggle="modal"]',
        modalClass: '.modal',
        wrapperClass: '#modals-wrapper'
      };

      $.extend(this.params, params);

      return this.init();
    };

    _modal.prototype.open = function (id) {
      if (id && !$(id).length) {
        return;
      }

      if (!id && this._openedModals.length) {
        return this.open(this._openedModals.pop());
      } else if (!id) {
        return;
      }

      if (this._modalId && this._modalId != id) {
        var self = this;
        this._openedModals.push(this._modalId);
        this.close();
        this._modalId = null;
        setTimeout(function () {
          return self.open(id)
        }, 400);
        return;
      }

      if (id && !this._modalId) {
        this._modalId = id;
      }

      $(id).modal('show');
    };

    _modal.prototype.close = function (id) {
      if (id) {
        $(id).modal('hide');
        return;
      }

      if (this._modalId && this._modals.hasOwnProperty(this._modalId)) {
        $(this._modalId).modal('hide');
      }
    };

    _modal.prototype.empty = function (id) {
      this.runAction('empty', null, id);
    };

    _modal.prototype.reload = function () {
      if (!this._modalId || !this._modals.hasOwnProperty(this._modalId)) {
        return;
      }
      this.getModal.apply(this, [this._modals[this._modalId]['url'], this._modals[this._modalId], this.renderModal, this._modals[this._modalId]['method']]);
    };

    _modal.prototype.runAction = function (action, data, id) {
      var modalId = id ? id : this._modalId;

      switch (action) {
        case 'refresh':
          var $content = $(modalId).find('.modal-content');

          if (this._modals.hasOwnProperty(modalId)) {
            $(modalId).animate({
              scrollTop: 30
            }, 200);
          } else {
            this._modals[modalId] = this._modalParams;
          }

          $content.html(data);
          var $modals = $content.find(this.params.modalClass).detach();

          if ($modals.length) {
            $(this.params.wrapperClass).append($modals);
          }

          break;

        case 'empty':
          $(modalId).find('.modal-content').html('<div class="modal-body text-center"><i class="fa fa-spin fa-refresh fa-4x"></i></div>');
          this.close();
          delete this._modals[modalId];

          break;
      }
    };

    _modal.prototype.renderModal = function (data) {
      this.runAction('refresh', data);
    };

    _modal.prototype.getModal = function (url, data, callback, requestMethod) {
      var self = this;

      var requestMethod = requestMethod || 'post';
      if (requestMethod === 'get') {
        delete data['url'];
        delete data['method'];
      }

      $.ajaxSetup({'cache': true});
      $.ajax({
        url: url,
        method: requestMethod,
        data: data,
        success: function () {
          callback.apply(self, arguments)
        }
      });
    };

    _modal.prototype.prepareModal = function (e) {
      var $this = $(e.target);
      this._modalId = '#' + $this.attr('id');

      if (this._modals.hasOwnProperty(this._modalId)) {
        return;
      }

      this._modalParams = {
        url: $this.attr('data-url'),
        method: $this.data('modal-method')
      };

      if (this._modalParams.url) {
        this.getModal.apply(this, [this._modalParams.url, this._modalParams, this.renderModal, this._modalParams.method]);
      }
    };

    _modal.prototype.getId = function () {
      if (this._modalId && this._modals.hasOwnProperty(this._modalId)) {
        return this._modalId;
      }

      return null;
    };

    _modal.prototype.renderWrapper = function (id, url, size, method) {
      url = url || '';
      size = size || '';
      var $wrapper = $(
        '<div id="' + id.substr(1) + '" class="fade modal" role="dialog">' +
        '<div class="modal-dialog ' + size + '">' +
        '<div class="modal-content">' +
        '<div class="modal-body text-center"><i class="fa fa-spin fa-refresh fa-4x"></i></div>' +
        '</div>' +
        '</div>' +
        '</div>');

      $wrapper.attr('data-url', url);
      $wrapper.attr('data-modal-method', method);

      return $wrapper;
    };

    _modal.prototype.init = function () {
      var self = this;
        $('body')
        .on('click', this.params.buttonClass, function (e) {
          e.preventDefault();
          var $this = $(this),
            id = $this.attr('data-target'),
            url = $this.attr('data-url'),
            method = $this.attr('data-modal-method'),
            size = $this.attr('data-size');

          if (url && (!$(id).length || $(id).attr('data-url') != url)) {
            ($(id).length && $(id).attr('data-url') != url) &&
            $(id).remove() && delete self._modals[id];

            var $wrapper = self.renderWrapper(id, url, size, method);
            $(self.params.wrapperClass).append($wrapper);
            $wrapper.modal('show');
            return false;
          }
        });
      $(document)
        .on('show.bs.modal', this.params.modalClass, function () {
          var $this = $(this),
            id = '#' + $this.attr('id'),
            args = arguments;

          // если не наша модалка (класс не равен modalClass), то не обрабатываем её.
          // была ошибка когда открыт DatePicker переоткрывалась модалка при попытке её закрыть
          if (args.hasOwnProperty("0") && args[0].hasOwnProperty("target")) {
            var $target = $(args[0].target);
            if (!$target.is(self.params.modalClass)) {
              return false;
            }
          }

          if (self._modalId && self._modalId != id) {
            self.open(id);
            return false;
          }
          self.prepareModal.apply(self, args);
        })
        .on('hidden.bs.modal', this.params.modalClass, function () {
          var $this = $(this),
            id = '#' + $this.attr('id'),
            args = arguments;
          self._modalId = self._modalId == id ? null : self._modalId;
          self._modalParams = self._modalId ? null : self._modalParams;
          if (!self._openedModals.length) {
            return;
          }
          if (self._openedModals[self._openedModals.length - 1] == id) {
            return;
          }
          if (self._openedModals && self._openedModals.length) {
            self.open();
          }
        });
    };

    return api;
  };

  this.ModalWidget = this.ModalWidget || new ModalWidget();
})(jQuery);