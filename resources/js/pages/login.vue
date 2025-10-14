<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGenerateImageVariant } from '@/@core/composable/useGenerateImageVariant'
import authV1LoginMaskDark from '@images/pages/auth-v1-login-mask-dark.png'
import authV1LoginMaskLight from '@images/pages/auth-v1-login-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { setupRecaptcha } from '@/firebase/auth'
import { auth } from '@/firebase'
import { signInWithPhoneNumber } from 'firebase/auth'

definePage({
  meta: { layout: 'blank', unauthenticatedOnly: true }
})

const route = useRoute()
const router = useRouter()

const step = ref('phone')
const form = ref({ phone_number: '' })
const otp = ref('')
const loading = ref(false)
const err = ref('')
const cooldown = ref(0)
let timer = null
const phoneInput = ref(null)

const authMask = useGenerateImageVariant(authV1LoginMaskLight, authV1LoginMaskDark)

onMounted(() => {
  phoneInput.value?.focus()
  try { setupRecaptcha() }
  catch (error) { err.value = 'Error al inicializar el sistema de verificación' }
})

onUnmounted(() => {
  if (window.recaptchaVerifier) { window.recaptchaVerifier.clear(); delete window.recaptchaVerifier }
  if (timer) clearInterval(timer)
})

const masked = computed(() => `******${form.value.phone_number.replace(/\D/g, '').slice(-4)}`)

const handleSubmit = async () => {
  if (!form.value.phone_number) { err.value = 'Ingresa tu número de celular'; return }
  if (cooldown.value > 0) return

  loading.value = true
  err.value = ''
  try {
    let phone = form.value.phone_number
    if (!phone.startsWith('+')) phone = '+51' + phone.replace(/\D/g, '')

    const res = await $api('/auth/check-phone', {
      method: 'POST',
      body: { phone_number: phone },
      onResponseError({ response }) { throw new Error(response._data?.message || 'Error verificando número') }
    })

    if (!res?.exists) { err.value = res?.message || 'Número no registrado'; return }

    const confirmation = await signInWithPhoneNumber(auth, phone, window.recaptchaVerifier)
    window.confirmationResult = confirmation
    step.value = 'verify'
    cooldown.value = 60
    timer = setInterval(() => { cooldown.value--; if (cooldown.value <= 0) clearInterval(timer) }, 1000)

  } catch (error) {
    console.error('❌ Error:', error)
    if (error.code === 'auth/too-many-requests') err.value = 'Demasiados intentos. Intenta más tarde.'
    else if (error.code === 'auth/invalid-phone-number') err.value = 'Número inválido.'
    else err.value = error.message || 'Error al enviar el SMS.'
  } finally { loading.value = false }
}

const onFinish = async () => {
  if (!window.confirmationResult) { err.value = 'No se encontró una sesión de verificación activa.'; return }
  loading.value = true; err.value = ''

  try {
    const result = await window.confirmationResult.confirm(otp.value)
    const token = await result.user.getIdToken()
    const res = await $api('/auth/verify-token', {
      method: 'POST',
      body: { token },
      onResponseError({ response }) { err.value = response._data?.message || 'Error en autenticación' }
    })

    const { accessToken, userData } = res
    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken

    await nextTick(() => { router.replace(route.query.to ? String(route.query.to) : '/') })

  } catch (error) {
    if (error.code === 'auth/invalid-verification-code') err.value = 'Código inválido.'
    else if (error.code === 'auth/code-expired') err.value = 'El código ha expirado.'
    else err.value = error.message || 'Error al verificar el código.'
    otp.value = ''
  } finally { loading.value = false }
}

const handleResend = () => { step.value = 'phone'; otp.value = ''; err.value = '' }
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard class="auth-card pa-1 pa-sm-7" max-width="448">
      <!-- Logo -->
      <VCardItem class="justify-center pb-6">
        <VCardTitle>
          <RouterLink to="/">
            <div class="app-logo">
              <VNodeRenderer :nodes="themeConfig.app.logo" />
              <h1 class="app-logo-title">{{ themeConfig.app.title }}</h1>
            </div>
          </RouterLink>
        </VCardTitle>
      </VCardItem>

      <!-- Título dinámico -->
      <VCardText>
        <p class="mb-0">{{ step === 'phone' ? 'Por favor, inicia sesión con tu número de celular' : 'Ingresa el código enviado a tu celular' }}</p>
        <p v-if="step === 'verify'" class="text-h6 mt-2">{{ masked }}</p>
      </VCardText>

      <div class="transition-wrapper">
        <Transition name="slide" mode="out-in">
          <VCardText v-if="step === 'phone'" key="phone">
            <VForm @submit.prevent="handleSubmit">
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="form.phone_number"
                    ref="phoneInput"
                    label="Número de celular"
                    type="tel"
                    placeholder="999 999 999"
                    prepend-inner-icon="ri-smartphone-line"
                    :error="err !== ''"
                    :error-messages="err"
                    maxlength="9"
                    required
                    @input="form.phone_number = form.phone_number.replace(/\D/g, '')"
                  />
                </VCol>

                <VCol cols="12">
                  <div id="recaptcha-container" class="d-flex justify-center" style="min-block-size: 78px;" />
                </VCol>

                <VCol cols="12">
                  <VBtn block type="submit" color="primary" :loading="loading">
                    {{ cooldown > 0 ? `Espera ${cooldown}s` : 'Enviar código' }}
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>

          <VCardText v-else key="verify">
            <VForm @submit.prevent="onFinish">
              <VRow>
                <VCol cols="12">
                  <VOtpInput v-model="otp" :disabled="loading" type="number" autofocus class="pa-0" @finish="onFinish" />
                </VCol>

                <VCol v-if="err" cols="12">
                  <VAlert type="error" variant="tonal" closable @click:close="err = ''">{{ err }}</VAlert>
                </VCol>

                <VCol cols="12">
                  <VBtn :loading="loading" :disabled="loading || otp.length !== 6" block type="submit" class="mt-3 mb-5">Verificar mi cuenta</VBtn>
                </VCol>

                <VCol cols="12">
                  <div class="d-flex justify-center align-center flex-wrap">
                    <span class="me-1">¿No recibiste el código?</span>
                    <a href="#" @click.prevent="handleResend">Reenviar</a>
                  </div>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </Transition>
      </div>
    </VCard>

    <VImg :src="authMask" class="d-none d-md-block auth-footer-mask flip-in-rtl" />
  </div>
</template>

<style lang="scss" scoped>
.transition-wrapper { position: relative; min-block-size: 320px; }
.slide-enter-active, .slide-leave-active { transition: all 0.3s ease-out; }
.slide-enter-from { opacity: 0; transform: translateX(30px); }
.slide-leave-to { opacity: 0; transform: translateX(-30px); }
</style>

<style lang="scss">
@use '@core-scss/template/pages/page-auth';
</style>
