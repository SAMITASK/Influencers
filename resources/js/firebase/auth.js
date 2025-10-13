// resources/js/firebase/auth.js
import { RecaptchaVerifier } from "firebase/auth";
import { auth } from "./index";

/**
 * Inicializa el reCAPTCHA.
 * Llama a esta función en onMounted() de tu login.
 */
export const setupRecaptcha = () => {
  if (!window.recaptchaVerifier) {
    window.recaptchaVerifier = new RecaptchaVerifier(
      "recaptcha-container", // ID del div en tu template
      {
        size: "normal", // "normal" para que se vea, invisible si quieres invisible
        callback: (response) => {
          console.log("reCAPTCHA verificado", response);
        },
      },
      auth
    );

    window.recaptchaVerifier.render().then((widgetId) => {
      window.recaptchaWidgetId = widgetId;
    });
  }
};
