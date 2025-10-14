import { RecaptchaVerifier } from "firebase/auth";
import { auth } from "./index";

export const setupRecaptcha = () => {
  try {
    console.log("🟢 Inicializando reCAPTCHA...");

    // Limpiar si ya existe
    if (window.recaptchaVerifier) {
      window.recaptchaVerifier.clear();
      delete window.recaptchaVerifier;
    }

    // Crear nuevo verificador
    window.recaptchaVerifier = new RecaptchaVerifier(
      auth,
      "recaptcha-container",
      {
        size: "normal",
        callback: (response) => {
          console.log("✅ reCAPTCHA verificado:", response);
        },
        "expired-callback": () => {
          console.log("⚠️ reCAPTCHA expirado");
        },
      }
    );
    // Renderizar
    window.recaptchaVerifier.render().then((widgetId) => {
      console.log("📦 reCAPTCHA renderizado con ID:", widgetId);
    });

    return window.recaptchaVerifier;
  } catch (error) {
    console.error("❌ Error en setupRecaptcha:", error);
    throw error;
  }
};
