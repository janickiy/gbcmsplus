var firebaseSubscribe = function(localStorageToken) {
  this.bt_register = $('#register-push');
  this.bt_delete = $('#delete-push');
  this.error_block = $('#push-error');
  this.messaging = null;
  this.localStorageToken = localStorageToken;

  this.bt_register.on('click', function() {
    firebaseSubscribe.disableActionButtons();
    firebaseSubscribe.getToken(true);
  });

  this.bt_delete.on('click', function() {
    firebaseSubscribe.disableActionButtons();
    // Delete Instance ID token.
    firebaseSubscribe.messaging.getToken()
      .then(function (currentToken) {
        $.post(firebaseSubscribe.bt_delete.data('url'), {token: currentToken}, function (result) {
          if (result) {
            firebaseSubscribe.messaging.deleteToken(currentToken);
          }
        }).always(function () {
          firebaseSubscribe.setTokenSentToServer(false);
          firebaseSubscribe.enableActionButtons();
        });
      })
      .catch(function(error) {
        firebaseSubscribe.enableActionButtons();
        firebaseSubscribe.showError('Error retrieving Instance ID token.', error);
      });
  });

  this.init();
};

firebaseSubscribe.prototype.isReady = function () {
  if (window.location.protocol === 'https:' &&
    'Notification' in window &&
    'serviceWorker' in navigator &&
    'localStorage' in window &&
    'postMessage' in window
  ) {
    return true;
  }

  this.error_block.show();
  this.disableActionButtons();

  if (window.location.protocol !== 'https:') {
    this.showError('Is not from HTTPS');
  } else if (!('Notification' in window)) {
    this.showError('Notification not supported');
  } else if (!('serviceWorker' in navigator)) {
    this.showError('ServiceWorker not supported');
  } else if (!('localStorage' in window)) {
    this.showError('LocalStorage not supported');
  } else if (!('fetch' in window)) {
    this.showError('fetch not supported');
  } else if (!('postMessage' in window)) {
    this.showError('postMessage not supported');
  }

  return false;
};

firebaseSubscribe.prototype.showError = function(error, error_data) {
  // Для отладки можно раскомментировать
  if (typeof error_data !== "undefined") {
    // console.error(error + ' ', error_data);
  } else {
    // console.error(error);
  }
};

firebaseSubscribe.prototype.showRegister = function() {
  this.bt_register.show();
  this.bt_delete.hide();
};
firebaseSubscribe.prototype.showDelete = function() {
  this.bt_register.hide();
  this.bt_delete.show();
};

firebaseSubscribe.prototype.disableActionButtons = function() {
  this.bt_register.attr('disabled', true);
  this.bt_delete.attr('disabled', true);
};
firebaseSubscribe.prototype.enableActionButtons = function() {
  this.bt_register.attr('disabled', false);
  this.bt_delete.attr('disabled', false);
};

firebaseSubscribe.prototype.getToken = function(send) {
  send = send || false;
  this.messaging.requestPermission()
    .then(function() {
      // Get Instance ID token. Initially this makes a network call, once retrieved
      // subsequent calls to getToken will return from cache.
      firebaseSubscribe.messaging.getToken()
        .then(function(currentToken) {
          firebaseSubscribe.showRegister();
          if (firebaseSubscribe.isTokenSentToServer(currentToken)) {
            firebaseSubscribe.showDelete();
          }

          if (currentToken) {
            send && firebaseSubscribe.sendTokenToServer(currentToken);
          } else {
            firebaseSubscribe.showError('No Instance ID token available. Request permission to generate one.');
            firebaseSubscribe.setTokenSentToServer(false);
          }
        })
        .catch(function(error) {
          firebaseSubscribe.showError('An error occurred while retrieving token.', error);
          firebaseSubscribe.setTokenSentToServer(false);
        });
    });
};

// Send the Instance ID token your application server, so that it can:
// - send messages back to this app
// - subscribe/unsubscribe the token from topics
firebaseSubscribe.prototype.sendTokenToServer = function(currentToken) {
  if (!this.isTokenSentToServer(currentToken)) {
    this.setTokenSentToServer(currentToken);
  }
};

firebaseSubscribe.prototype.isTokenSentToServer = function(currentToken) {
  return this.getSentFirebaseMessagingToken() == currentToken;
};

firebaseSubscribe.prototype.getSentFirebaseMessagingToken = function() {
  return window.localStorage.getItem(this.localStorageToken);
};

firebaseSubscribe.prototype.setTokenSentToServer = function(currentToken) {
  this.showRegister();
  var bt_register = this.bt_register;
  var localStorageToken = this.localStorageToken;
  if (currentToken) {
    // send current token to server
    $.post(bt_register.data('url'), {token: currentToken}, function(result){
      window.localStorage.setItem(localStorageToken, currentToken);
      firebaseSubscribe.showDelete();
    }).always(function() {
      firebaseSubscribe.enableActionButtons();
    });
  } else {
    this.enableActionButtons();
    window.localStorage.removeItem(localStorageToken);
  }
};

firebaseSubscribe.prototype.init = function () {
  if (!this.isReady()) return false;

  this.messaging = firebase.messaging();
  if (Notification.permission === 'granted') {
    this.getToken();
  } else {
    this.showRegister();
  }
};