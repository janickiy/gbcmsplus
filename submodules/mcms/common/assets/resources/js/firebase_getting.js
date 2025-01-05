firebase.initializeApp({
  messagingSenderId: '695597360069'
});
(function () {
  if (window.location.protocol === 'https:' &&
    'Notification' in window &&
    'serviceWorker' in navigator &&
    'localStorage' in window &&
    'postMessage' in window
  ) {
    var messaging = firebase.messaging();
    messaging.onMessage(function (payload) {
      setTimeout(function() {
        var messageId = parseInt(payload.data.messageId);
        var lastNotificationId = localStorage.getItem('lastNotificationId');
        // проверка, что пользователь не получал это сообщение
        if (lastNotificationId !== messageId) {
          localStorage.setItem('lastNotificationId', messageId);

          navigator.serviceWorker.getRegistration('/firebase-cloud-messaging-push-scope').then(function (registration) {
            payload.notification.data = payload.notification; // параметры уведомления
            registration.showNotification(payload.notification.title, payload.notification);
          });

          var notification = new Notification(payload.notification.title, payload.notification);
          notification.onclick = function () {
            window.open(payload.notification.click_action);
            notification.close();
          };
        }
      }, Math.random() * 1000);

    });
  }
})();