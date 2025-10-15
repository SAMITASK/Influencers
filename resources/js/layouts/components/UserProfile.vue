<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import avatar1 from '@images/avatars/avatar-1.png'

const router = useRouter()
const loading = ref(false)

// Obtener datos del usuario de la cookie
const userData = useCookie('userData')
const user = computed(() => userData.value || {})

const userProfileList = [
  { type: 'divider' },
]

const handleLogout = async () => {
  loading.value = true
  
  try {
    // Llamar al endpoint de logout
    await $api('/auth/logout', {
      method: 'POST',
      onResponseError({ response }) {
        console.error('Error en logout:', response._data?.message)
      },
    })
    
    console.log('✅ Sesión cerrada en el servidor')
    
  } catch (error) {
    console.error('❌ Error al cerrar sesión:', error)
  } finally {
    // Siempre limpiar cookies (incluso si falla el endpoint)
    useCookie('accessToken').value = null
    useCookie('userData').value = null
    
    // Redirigir al login
    router.replace('/login')
    
    loading.value = false
  }
}
</script>

<template>
  <VBadge
    dot
    bordered
    location="bottom right"
    offset-x="2"
    offset-y="2"
    color="success"
    class="user-profile-badge"
  >
    <VAvatar
      class="cursor-pointer"
      size="38"
    >
      <VImg :src="avatar1" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="15px"
      >
        <VList>
          <VListItem class="px-4">
            <div class="d-flex gap-x-2 align-center">
              <VAvatar>
                <VImg :src="avatar1" />
              </VAvatar>

              <div>
                <div class="text-body-2 font-weight-medium text-high-emphasis">
                  {{ user.name || 'Usuario' }}
                </div>
                <div class="text-capitalize text-caption text-disabled">
                  {{ user.social_handle || user.code || 'Sin identificador' }}
                </div>
              </div>
            </div>
          </VListItem>

          <PerfectScrollbar :options="{ wheelPropagation: false }">
            <template
              v-for="item in userProfileList"
              :key="item.title"
            >
              <VListItem
                v-if="item.type === 'navItem'"
                :href="item.href"
                class="px-4"
              >
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="22"
                  />
                </template>

                <VListItemTitle>{{ item.title }}</VListItemTitle>

                <template
                  v-if="item.chipsProps"
                  #append
                >
                  <VChip
                    v-bind="item.chipsProps"
                    variant="elevated"
                  />
                </template>
              </VListItem>

              <VDivider
                v-else
                class="my-1"
              />
            </template>

            <VListItem class="px-4">
              <VBtn
                block
                color="error"
                size="small"
                append-icon="ri-logout-box-r-line"
                :loading="loading"
                :disabled="loading"
                @click="handleLogout"
              >
                Cerrar Sesión
              </VBtn>
            </VListItem>
          </PerfectScrollbar>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>

<style lang="scss">
.user-profile-badge {
  &.v-badge--bordered.v-badge--dot .v-badge__badge::after {
    color: rgb(var(--v-theme-background));
  }
}
</style>
