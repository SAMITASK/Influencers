import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

const firebaseConfig = {
  apiKey: "AIzaSyDbsLrRf0aohJp-sgV1IwXllWnhIUd10XE",
  authDomain: "lagranjavilla-a0fa8.firebaseapp.com",
  projectId: "lagranjavilla-a0fa8",
  storageBucket: "lagranjavilla-a0fa8.firebasestorage.app",
  messagingSenderId: "1099393441183",
  appId: "1:1099393441183:web:01ec8915f716f57de6b1aa",
  measurementId: "G-RN5QP6L4SF"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

export { auth, app };
