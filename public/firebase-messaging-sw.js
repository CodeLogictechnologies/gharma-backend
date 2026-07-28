// firebase-messaging-sw.js
importScripts(
    "https://www.gstatic.com/firebasejs/12.16.0/firebase-app-compat.js",
);
importScripts(
    "https://www.gstatic.com/firebasejs/12.16.0/firebase-messaging-compat.js",
);

firebase.initializeApp({
    apiKey: "AIzaSyBfcmxIs9yhpsGMysUUlOtJLODiAb8VtpI",
    authDomain: "my-project-12888-1734931293849.firebaseapp.com",
    projectId: "my-project-12888-1734931293849",
    storageBucket: "my-project-12888-1734931293849.firebasestorage.app",
    messagingSenderId: "337435954580",
    appId: "1:337435954580:web:1b52914bdbccc87a705381",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    console.log(
        "[firebase-messaging-sw.js] Received background message ",
        payload,
    );

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: "/favicon.ico",
        badge: "/favicon.ico",
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
