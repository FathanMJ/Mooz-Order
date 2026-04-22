import { initializeApp } from 'firebase/app';
import { getFirestore } from 'firebase/firestore';

const firebaseConfig = {
  apiKey: "AIzaSyAujc1BK3OIEcKsRN3g0I6JFcd38dJsiy8",
  authDomain: "mooz-order.firebaseapp.com",
  projectId: "mooz-order",
  storageBucket: "mooz-order.firebasestorage.app",
  messagingSenderId: "447771458322",
  appId: "1:447771458322:android:cd5c13863bf81955a051cb"
};

const app = initializeApp(firebaseConfig);
const db = getFirestore(app);

export { db };
