import { RecaptchaVerifier } from "firebase/auth";
import { auth } from "./index";



export const setupRecaptcha = () => {
  try {
    console.log('🔍 Iniciando setup reCAPTCHA...');
    
    console.log("auth object:", auth);
console.log("auth.app.name:", auth.app?.name);

    // Limpiar el recaptcha existente si existe
    if (window.recaptchaVerifier) {
      console.log('🧹 Limpiando verificador anterior...');
      window.recaptchaVerifier.clear();
      delete window.recaptchaVerifier;
    }

    console.log('🔨 Creando RecaptchaVerifier...');
    
    window.recaptchaVerifier = new RecaptchaVerifier(
      auth,
      'recaptcha-container',
      {
        size: "normal",
        callback: (response) => {
          console.log("✅ reCAPTCHA verificado:", response);
        },
        'expired-callback': () => {
          console.log("⚠️ reCAPTCHA expirado");
        }
      }
    );
    console.log("Tipo de RecaptchaVerifier:", RecaptchaVerifier.length);

    console.log('🎨 Renderizando reCAPTCHA...');
    // NO usar await - render() puede no ser una promesa en tu versión
    window.recaptchaVerifier.render();
    console.log('✅ reCAPTCHA configurado');
    
    return window.recaptchaVerifier;
  } catch (error) {
    console.error("❌ Error en setupRecaptcha:", error);
    throw error;
  }
};
