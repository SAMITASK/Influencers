<script setup>
const props = defineProps({
  supplierData: {
    type: Object,
    required: true,
  },
})

</script>

<template>
  <VRow>
    <!-- SECTION User Details -->
    <VCol cols="12">
      <VCard v-if="props.supplierData">
        <VCardText class="text-center pt-12 pb-6">
          <!-- 👉 Avatar -->
          <VAvatar
            rounded="lg"
            :size="120"
            :color="!props.supplierData.avatar ? 'primary' : undefined"
            :variant="!props.supplierData.avatar ? 'tonal' : undefined"
          >
            <VImg
              v-if="props.supplierData.avatar"
              :src="props.supplierData.avatar"
            />
            <span
              v-else
              class="text-5xl font-weight-medium"
            >
              {{ avatarText(props.supplierData.reason ) }}
            </span>
          </VAvatar>

          <!-- 👉 User fullName -->
          <h5 class="text-h5 mt-4">
            {{ props.supplierData.reason }}
          </h5>
        </VCardText>

        <VCardText class="d-flex justify-center flex-wrap gap-6 pb-6">
          <!-- 👉 Done task -->
          <div class="d-flex align-center me-8">
            <VAvatar
              :size="40"
              rounded
              color="primary"
              variant="tonal"
              class="me-4"
            >
              <VIcon
                size="24"
                icon="ri-check-line"
              />
            </VAvatar>

            <div>
              <h5 class="text-h5">
                {{ kFormatter(props.supplierData.purchase_orders_count) }}
              </h5>
              <span>Ord. Compra</span>
            </div>
          </div>

          <!-- 👉 Done Project -->
          <div class="d-flex align-center me-4">
            <VAvatar
              :size="44"
              rounded
              color="primary"
              variant="tonal"
              class="me-4"
            >
              <VIcon
                size="24"
                icon="ri-briefcase-line"
              />
            </VAvatar>

            <div>
              <h5 class="text-h5">
                {{ kFormatter(props.supplierData.service_orders_count) }}
              </h5>
              <span>Ord. Servicio</span>
            </div>
          </div>
        </VCardText>

        <!-- 👉 Details -->
        <VCardText class="pb-6">
          <h5 class="text-h5">
            Detalles
          </h5>

          <VDivider class="my-4" />

          <!-- 👉 User Details list -->
          <VList class="card-list">
            <VListItem>
              <VListItemTitle class="text-sm">
                <span class="font-weight-medium">
                  Dirección:
                </span>
              </VListItemTitle>
                  <span class="text-body-1">
                  {{ props.supplierData.address }}
                </span>
            </VListItem>

            <VListItem>
              <VListItemTitle class="text-sm">
                <span class="font-weight-medium">
                  Usuario:
                </span>
                <span class="text-body-1">{{ props.supplierData.user }}</span>
              </VListItemTitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
    <!-- !SECTION -->
  </VRow>

</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.5rem;
}

.current-plan {
  border: 2px solid rgb(var(--v-theme-primary));
}

.text-capitalize {
  text-transform: capitalize !important;
}
</style>
