<script setup>
import { useGenerateImageVariant } from "@/@core/composable/useGenerateImageVariant";
import authV1LoginMaskDark from "@images/pages/auth-v1-login-mask-dark.png";
import authV1LoginMaskLight from "@images/pages/auth-v1-login-mask-light.png";
import { VNodeRenderer } from "@layouts/components/VNodeRenderer";
import { themeConfig } from "@themeConfig";

import { setupRecaptcha } from "@/firebase/auth";
import { auth } from "@/firebase/index";

definePage({
  meta: {
    layout: "blank",
    public: true,
  },
});

const authV1ThemeLoginMask = useGenerateImageVariant(
  authV1LoginMaskLight,
  authV1LoginMaskDark
);

const form = ref({ phone_number: "" });
const loading = ref(false);
const errorMessage = ref("");

onMounted(() => {
  setupRecaptcha(); // Inicializa reCAPTCHA al montar el componente
});
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
        <h4 class="text-h4 mb-1">
          Welcome to
          <span class="text-capitalize">{{ themeConfig.app.title }}! 👋🏻</span>
        </h4>

        <p class="mb-0">
          Ingresa tu número de celular para acceder a tu panel de ventas.
        </p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="">
          <VRow>
            <!-- Phone -->
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
              <div id="recaptcha-container"></div>
            </VCol>

            <VCol cols="12">
              <VBtn block type="submit" color="primary" :loading="loading">
                Enviar código
              </VBtn>

              <!-- Mensaje de error -->
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
