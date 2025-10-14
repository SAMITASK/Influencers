<script setup>
import { useGenerateImageVariant } from "@/@core/composable/useGenerateImageVariant";
import authV1LoginMaskDark from "@images/pages/auth-v1-login-mask-dark.png";
import authV1LoginMaskLight from "@images/pages/auth-v1-login-mask-light.png";
import { VNodeRenderer } from "@layouts/components/VNodeRenderer";
import { themeConfig } from "@themeConfig";

import { setupRecaptcha } from "@/firebase/auth";
import { auth } from "@/firebase/index";
import { signInWithPhoneNumber } from "firebase/auth";

definePage({
  meta: {
    layout: "blank",
    public: true,
  },
});
console.log("auth index: ",auth);

const form = ref({ phone_number: "" });
const loading = ref(false);
const errorMessage = ref("");
const cooldownTime = ref(0);
const cooldownTimer = ref(null);

onMounted(() => {
  try {
    setupRecaptcha(); // SIN await
  } catch (error) {
    console.error("Error al inicializar reCAPTCHA:", error);
    errorMessage.value = "Error al inicializar el sistema de verificación";
  }
});


onUnmounted(() => {
  if (window.recaptchaVerifier) {
    window.recaptchaVerifier.clear();
    delete window.recaptchaVerifier;
  }
  // Limpiar el temporizador si existe
  if (cooldownTimer.value) {
    clearInterval(cooldownTimer.value);
  }
});

const startCooldown = () => {
  cooldownTime.value = 60; // 60 segundos de espera
  cooldownTimer.value = setInterval(() => {
    cooldownTime.value--;
    if (cooldownTime.value <= 0) {
      clearInterval(cooldownTimer.value);
      cooldownTimer.value = null;
    }
  }, 1000);
};

const handleSubmit = async () => {
  try {
    // Verificar si está en tiempo de espera
    if (cooldownTime.value > 0) {
      errorMessage.value = `Por favor, espera ${cooldownTime.value} segundos antes de intentar nuevamente`;
      return;
    }

    loading.value = true;
    errorMessage.value = "";

    // Formatear número de teléfono
    let phoneNumber = form.value.phone_number;
    if (!phoneNumber.startsWith("+")) {
      phoneNumber = "+51" + phoneNumber.replace(/\D/g, "");
    }

    // Validar formato del número
    const phoneRegex = /^\+51\d{9}$/;
    if (!phoneRegex.test(phoneNumber)) {
      throw new Error("Formato de número inválido");
    }

    const confirmationResult = await signInWithPhoneNumber(
      auth,
      phoneNumber,
      window.recaptchaVerifier
    );

    window.confirmationResult = confirmationResult;
    console.log("SMS enviado con éxito");
  } catch (error) {
    console.error("Error:", error);

    if (error.message === "Formato de número inválido") {
      errorMessage.value = "El número debe tener 9 dígitos y comenzar con +51";
    } else
      switch (error.code) {
        case "auth/invalid-phone-number":
          errorMessage.value = "Número de teléfono inválido";
          break;
        case "auth/too-many-requests":
          errorMessage.value =
            "Demasiados intentos. Por favor, espera un momento.";
          startCooldown(); // Iniciar tiempo de espera
          // Reiniciar reCAPTCHA
          if (window.recaptchaVerifier) {
            window.recaptchaVerifier.clear();
            delete window.recaptchaVerifier;
            await setupRecaptcha();
          }
          break;
        default:
          errorMessage.value =
            "Error al enviar el código. Por favor, intenta de nuevo";
      }
  } finally {
    loading.value = false;
  }
};

const authV1ThemeLoginMask = useGenerateImageVariant(authV1LoginMaskLight, authV1LoginMaskDark)

</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard class="auth-card pa-1 pa-sm-7" max-width="448">
      <VCardItem class="justify-center pb-6">
        <VCardTitle>
          <RouterLink to="/">
            <div class="app-logo">
              <VNodeRenderer :nodes="themeConfig.app.logo" />
              <h1 class="app-logo-title">
                {{ themeConfig.app.title }}
              </h1>
            </div>
          </RouterLink>
        </VCardTitle>
      </VCardItem>

      <VCardText>
        <p class="mb-0">
          Please sign-in to your account and start the adventure
        </p>
      </VCardText>
      <VCardText>
        <VForm @submit.prevent="handleSubmit">
          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="form.phone_number"
                label="Número de celular"
                type="tel"
                placeholder="+51 999 999 999"
                :error="!!errorMessage"
                :error-messages="errorMessage"
                required
              />
            </VCol>

            <VCol cols="12">
              <div
                id="recaptcha-container"
                class="d-flex justify-center"
                style="min-height: 78px"
              ></div>
            </VCol>

            <VCol cols="12">
              <VBtn
                block
                type="submit"
                color="primary"
                :loading="loading"
                :disabled="!form.phone_number || cooldownTime > 0"
              >
                {{
                  cooldownTime > 0 ? `Espera ${cooldownTime}s` : "Enviar código"
                }}
              </VBtn>

              <p v-if="errorMessage" class="text-error mt-2 text-center">
                {{ errorMessage }}
              </p>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
    <VImg
      :src="authV1ThemeLoginMask"
      class="d-none d-md-block auth-footer-mask flip-in-rtl"
    />
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
