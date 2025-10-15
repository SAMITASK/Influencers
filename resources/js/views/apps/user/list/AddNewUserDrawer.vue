<script setup>
import { PerfectScrollbar } from "vue3-perfect-scrollbar"

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  editingUser: {
    type: Object,
    default: null,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  isLoading: Boolean,
})

const emit = defineEmits(["update:isDrawerOpen", "userData", "userUpdated"])

// ✅ Errores locales actualizados
const localErrors = reactive({
  name: [],
  phone_number: [],
  email: [],
  social_handle: [],
  code: [], // 🆕 Solo un código
  code_description: [], // 🆕 Descripción del código
})

// ✅ Watch para actualizar errores desde el padre
watch(
  () => props.errors,
  newErrors => {
    // Limpiar errores anteriores
    Object.keys(localErrors).forEach(key => {
      localErrors[key] = []
    })
    
    // Asignar nuevos errores
    if (newErrors && typeof newErrors === 'object') {
      Object.keys(newErrors).forEach(key => {
        if (newErrors[key]) {
          localErrors[key] = Array.isArray(newErrors[key]) 
            ? newErrors[key] 
            : [newErrors[key]]
        }
      })
    }
  },
  { deep: true, immediate: true },
)

const isFormValid = ref(false)
const refForm = ref()
const fullName = ref("")
const phoneNumber = ref("")
const email = ref("")
const socialHandle = ref("")
const code = ref("") 
const codeDescription = ref("")
const status = ref("active")
const isEditing = ref(false)

// ✅ Función para resetear formulario
const resetForm = () => {
  refForm.value?.reset()
  refForm.value?.resetValidation()
  fullName.value = ""
  phoneNumber.value = ""
  email.value = ""
  socialHandle.value = ""
  code.value = ""
  codeDescription.value = ""
  status.value = "active"
  isEditing.value = false
  clearErrors()
}

// ✅ Función para limpiar errores
const clearErrors = () => {
  Object.keys(localErrors).forEach(key => {
    localErrors[key] = []
  })
}

watch(
  () => props.editingUser,
  newInfluencer => {
    if (newInfluencer) {
      isEditing.value = true
      fullName.value = newInfluencer.name || ""
      phoneNumber.value = newInfluencer.phone || ""
      email.value = newInfluencer.email || ""
      socialHandle.value = newInfluencer.social || ""
      code.value = newInfluencer.code || "" // 🆕
      codeDescription.value = newInfluencer.code_description || "" // 🆕
      status.value = newInfluencer.status || "active"
    } else {
      resetForm()
    }
  },
  { immediate: true },
)

const closeNavigationDrawer = () => {
  emit("update:isDrawerOpen", false)
  nextTick(() => {
    resetForm()
  })
}

const onSubmit = () => {
  clearErrors() // Limpiar errores anteriores antes de validar
  
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const influencerData = {
        name: fullName.value,
        phone_number: phoneNumber.value,
        email: email.value,
        social_handle: socialHandle.value,
        code: code.value, // 🆕 Un solo código
        code_description: codeDescription.value, // 🆕
        status: status.value,
      }

      if (isEditing.value && props.editingUser) {
        influencerData.id = props.editingUser.id
        emit("userUpdated", influencerData)
      } else {
        emit("userData", influencerData)
      }
    }
  })
}

const handleDrawerModelValueUpdate = val => {
  emit("update:isDrawerOpen", val)
}

// ✅ Exponer resetForm para que el padre pueda usarlo
defineExpose({
  resetForm,
})
</script>

<template>
  <VNavigationDrawer
    data-allow-mismatch
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <AppDrawerHeaderSection
      :title="isEditing ? 'Editar Influencer' : 'Agregar Influencer'"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm 
            ref="refForm" 
            v-model="isFormValid" 
            @submit.prevent="onSubmit"
          >
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="fullName"
                  label="Nombre completo"
                  placeholder="John Doe"
                  :rules="[requiredValidator]"
                  :error-messages="localErrors.name"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="phoneNumber"
                  label="Número de celular"
                  placeholder="+51 999 999 999"
                  :rules="[requiredValidator]"
                  :error-messages="localErrors.phone_number"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="email"
                  label="Email (opcional)"
                  placeholder="correo@ejemplo.com"
                  :error-messages="localErrors.email"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="socialHandle"
                  label="Red social"
                  placeholder="@usuario"
                  :error-messages="localErrors.social_handle"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="code"
                  label="Código del Influencer"
                  placeholder="CODIGO2024"
                  :rules="[requiredValidator]"
                  :error-messages="localErrors.code"
                  hint="Código único que se usará en las entradas"
                  persistent-hint
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="codeDescription"
                  label="Descripción del Código (opcional)"
                  placeholder="Ej: Promo Navidad 2024"
                  :error-messages="localErrors.code_description"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="status"
                  label="Estado"
                  :items="[
                    { title: 'Activo', value: 'active' },
                    { title: 'Inactivo', value: 'inactive' },
                  ]"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <VBtn 
                  type="submit" 
                  class="me-4"     
                  :loading="isLoading"
                  :disabled="isLoading"
                >
                  {{ isEditing ? "Actualizar" : "Guardar" }}
                </VBtn>
                <VBtn
                  type="reset"
                  variant="outlined"
                  color="error"
                  @click="closeNavigationDrawer"
                  :disabled="isLoading"
                >
                  Cancelar
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
