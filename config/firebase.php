<?php
declare(strict_types=1);

function firebase_config(): array
{
    return [
        'apiKey' => getenv('FIREBASE_API_KEY') ?: 'AIzaSyDQKnGz5FkcVP-W2IjliCWxoofKuRRgSvU',
        'authDomain' => getenv('FIREBASE_AUTH_DOMAIN') ?: 'alwayskhubsoorat-75d0e.firebaseapp.com',
        'projectId' => getenv('FIREBASE_PROJECT_ID') ?: 'alwayskhubsoorat-75d0e',
        'storageBucket' => getenv('FIREBASE_STORAGE_BUCKET') ?: 'alwayskhubsoorat-75d0e.firebasestorage.app',
        'messagingSenderId' => getenv('FIREBASE_MESSAGING_SENDER_ID') ?: '170676428420',
        'appId' => getenv('FIREBASE_APP_ID') ?: '1:170676428420:web:d01b15a3f404af3d652cd1',
        'measurementId' => getenv('FIREBASE_MEASUREMENT_ID') ?: 'G-06F14FSZLR',
    ];
}
