<script setup>
import { Spanish } from "flatpickr/dist/l10n/es";
import ApexChartDataScience from "@/views/charts/apex-chart/ApexChartDataScience.vue";

definePage({
  meta: {
    requiresAuth: true,
  },
});

const searchQuery = ref("");
const selectType = ref("ALL");
const selectedInfluencer = ref("ALL");

// 👤 Obtener datos del usuario desde la cookie
const userData = useCookie("userData");
const isAdmin = computed(() => userData.value?.role === "admin");

const influencersData = ref([]);

// 📋 Cargar lista de influencers (solo para admins)
watchEffect(async () => {
  if (isAdmin.value) {
    const { data } = await useApi(createUrl("influencers"));
    influencersData.value = data.value;
  }
});

const influencers = computed(() => {
  if (!influencersData.value) return [];

  return [
    { title: "Todos los influencers", value: "ALL" },
    ...influencersData.value.map((inf) => ({
      title: `${inf.name} (${inf.code})`,
      value: inf.code,
    })),
  ];
});

const today = new Date();
const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

function formatDate(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

const dateRange = ref(`${formatDate(firstDayOfMonth)} a ${formatDate(today)}`);

const headers = [
  {
    title: "🎟️ Tipo de Entrada",
    key: "type",
    align: "start",
    sortable: true,
  },
  {
    title: "🎫 Código",
    key: "code",
    align: "start",
    sortable: true,
  },
  {
    title: "📅 Fecha de Compra",
    key: "date_shop",
    align: "start",
    sortable: true,
  },
  {
    title: "S/. Precio",
    key: "price",
    align: "start",
    sortable: true,
  },
];

// 🎁 Agregar columna de influencer si es admin
if (isAdmin.value) {
  headers.splice(3, 0, {
    title: "👤 Influencer",
    key: "coupon",
    align: "start",
    sortable: true,
  });
}

const itemsPerPage = ref(30);
const page = ref(1);
const sortBy = ref();
const orderBy = ref();

const {
  data: cartData,
  execute: fetchCart,
  isFetching,
} = await useApi(
  createUrl("cart-details", {
    query: {
      q: searchQuery,
      type: selectType,
      date: dateRange,
      influencer: selectedInfluencer,
      itemsPerPage,
      page,
      sortBy,
      orderBy,
    },
  })
);

const updateOptions = (options) => {
  page.value = options.page;
  sortBy.value = options.sortBy[0]?.key;
  orderBy.value = options.sortBy[0]?.order;
};

// 📦 Datos de tabla
const cart = computed(() => cartData.value?.data || []);
const total = computed(() => cartData.value?.total || 0);

const statisticsWithIcon = computed(() => {
  const stats = cartData.value?.stats || {
    total: 0,
    totalFDT: 0,
    totalLight: 0,
  };

  return [
    {
      title: "Total Entradas",
      value: stats.total.toLocaleString(),
      desc: "Festival del Terror",
      icon: "ri-ticket-line",
      iconColor: "primary",
    },
    {
      title: "Entradas General",
      value: stats.totalFDT.toLocaleString(),
      desc: "Festival del Terror",
      icon: "ri-ticket-2-line",
      iconColor: "success",
    },
    {
      title: "Entradas Light",
      value: stats.totalLight.toLocaleString(),
      desc: "Festival del Terror",
      icon: "ri-lightbulb-line",
      iconColor: "warning",
    },
  ];
});
</script>

<template>
  <div>
    <VCard class="mb-6" title="Filtros">
      <VCardText>
        <VRow>
          <!-- 👑 Select de Influencer (solo para admin) -->
          <VCol v-if="isAdmin" cols="12" sm="4">
            <VSelect
              v-model="selectedInfluencer"
              label="Seleccionar Influencer"
              placeholder="Todos los influencers"
              :items="influencers"
              clearable
              clear-icon="ri-close-line"
            >
              <template #prepend-inner>
                <VIcon icon="ri-user-star-line" />
              </template>
            </VSelect>
          </VCol>

          <VCol cols="12" sm="4">
            <VSelect
              v-model="selectType"
              label="Seleccionar Tipo Entrada"
              placeholder="Seleccionar Tipo Entrada"
              :items="[
                { title: 'Entrada General FDT', value: 11 },
                { title: 'Entrada Light FDT', value: 17 },
                { title: 'Todos', value: 'ALL' },
              ]"
              clearable
              clear-icon="ri-close-line"
            />
          </VCol>

          <VCol cols="12" sm="4">
            <AppDateTimePicker
              v-model="dateRange"
              label="Rango de fechas"
              placeholder="Selecciona el rango"
              :config="{
                mode: 'range',
                dateFormat: 'Y-m-d',
                locale: Spanish,
                maxDate: new Date(),
              }"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VRow id="apex-chart-wrapper" class="d-flex flex-wrap align-center">
      <VCol cols="12" md="12" class="mb-6">
        <VRow>
          <template v-if="isFetching">
            <VCol cols="12" md="4" sm="6" v-for="n in 3" :key="n">
              <VSkeletonLoader type="card" height="100%" />
            </VCol>
          </template>

          <template v-else>
            <VCol
              cols="12"
              md="4"
              sm="6"
              v-for="stat in statisticsWithIcon"
              :key="stat.title"
            >
              <CardStatisticsWithIcon v-bind="stat" />
            </VCol>
          </template>
        </VRow>
      </VCol>

      <!-- Gráfico -->
      <VCol cols="12" md="12" class="mb-4 mb-md-0">
        <VCard class="mb-6">
          <ApexChartDataScience
            :date-range="dateRange"
            :type="selectType"
            :influencer="selectedInfluencer"
          />
        </VCard>
      </VCol>
    </VRow>

    <VCard title="Detalle Entradas">
      <VCardText class="d-flex flex-wrap gap-4 align-center">
        <VSpacer />
        <div class="d-flex align-center gap-4 flex-wrap">
          <div class="app-user-search-filter">
            <VTextField
              v-model="searchQuery"
              placeholder="Buscar OC"
              density="compact"
            />
          </div>
        </div>
      </VCardText>
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :headers="headers"
        :items="cart"
        :items-length="total"
        class="text-no-wrap rounded-0"
        @update:options="updateOptions"
        :loading="isFetching"
        hover
      >
        <!-- 👤 Columna de influencer (solo visible para admin) -->
        <template v-if="isAdmin" #item.coupon="{ item }">
          <VChip
            v-if="item.influencer"
            size="small"
            color="primary"
            variant="tonal"
          >
            {{ item.influencer }}
          </VChip>
          <span v-else class="text-disabled">Sin influencer</span>
        </template>

        <template #item.price="{ item }">
          <div class="d-flex align-center gap-x-3">
            <div class="d-flex flex-column">
              <span class="text-base">{{
                Number(item.price).toLocaleString("es-PE", {
                  style: "currency",
                  currency: "PEN",
                })
              }}</span>
            </div>
          </div>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <VDivider />

          <div class="d-flex justify-end flex-wrap gap-x-6 px-2 py-1">
            <div
              class="d-flex align-center gap-x-2 text-medium-emphasis text-base"
            >
              Registros por página:
              <VSelect
                v-model="itemsPerPage"
                class="per-page-select"
                variant="plain"
                :items="[10, 20, 25, 50, 100]"
              />
            </div>

            <p
              class="d-flex align-center text-base text-high-emphasis me-2 mb-0"
            >
              {{ paginationMeta({ page, itemsPerPage }, total) }}
            </p>

            <div class="d-flex gap-x-2 align-center me-2">
              <VBtn
                class="flip-in-rtl"
                icon="ri-arrow-left-s-line"
                variant="text"
                density="comfortable"
                color="high-emphasis"
                :disabled="page <= 1"
                @click="page <= 1 ? (page = 1) : page--"
              />

              <VBtn
                class="flip-in-rtl"
                icon="ri-arrow-right-s-line"
                density="comfortable"
                variant="text"
                color="high-emphasis"
                :disabled="page >= Math.ceil(total / itemsPerPage)"
                @click="
                  page >= Math.ceil(total / itemsPerPage)
                    ? (page = Math.ceil(total / itemsPerPage))
                    : page++
                "
              />
            </div>
          </div>
        </template>
      </VDataTableServer>
    </VCard>
  </div>
</template>
