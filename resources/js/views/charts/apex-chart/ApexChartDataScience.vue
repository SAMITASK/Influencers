<script setup>
import { useTheme } from "vuetify"
import { getColumnChartConfig } from "@core/libs/apex-chart/apexCharConfig"
import { computed } from "vue"

const props = defineProps({
  dateRange: {
    type: String,
    default: "",
  },
  type: { type: [String, Number] },
  influencer: {
    type: String,
    default: "ALL",
  },
})

const vuetifyTheme = useTheme()
const userData = useCookie('userData')
const isAdmin = computed(() => userData.value?.role === 'admin')

// 📊 Obtener datos del chart desde la API
const { data: chartData, isFetching } = await useApi(
  createUrl("cart-details/chart", {
    query: {
      date: computed(() => props.dateRange),
      type: computed(() => props.type),
      influencer: computed(() => props.influencer),
    },
  })
)

// 📈 Series y categorías reactivas
const series = computed(() => chartData.value?.series || [])
const categories = computed(() => chartData.value?.categories || [])
const rankingData = computed(() => chartData.value?.ranking || [])

console.log(chartData.value);


// 🎨 Configuración del chart según el rol
const chartConfig = computed(() => {
  const config = getColumnChartConfig(vuetifyTheme.current.value)
  
  // Si es admin y está viendo todos los influencers
  if (isAdmin.value && props.influencer === 'ALL') {
    return {
      ...config,
      chart: {
        ...config.chart,
        type: 'bar',
        horizontal: true,
        toolbar: {
          show: true,
        },
      },
      xaxis: {
        ...config.xaxis,
        categories: categories.value,
      },
      plotOptions: {
        bar: {
          horizontal: true,
          dataLabels: {
            position: 'top',
          },
          barHeight: '30%',
        },
      },
      dataLabels: {
        enabled: true,
        offsetX: -6,
        style: {
          fontSize: '11px',
          colors: ['#fff']
        }
      },
      legend: {
        show: true,
        position: 'top',
      },
    }
  }
  
  // Config normal para influencer específico
  return {
    ...config,
    xaxis: {
      ...config.xaxis,
      categories: categories.value,
    },
  }
})

const chartTitle = computed(() => {
  if (isAdmin.value && props.influencer === 'ALL') {
    return 'Top 10 Influencers'
  }
  return 'Ventas por Tipo de Entrada'
})

const chartSubtitle = computed(() => {
  if (isAdmin.value && props.influencer === 'ALL') {
    return 'Mejores vendedores del período'
  }
  if (props.influencer && props.influencer !== 'ALL') {
    return `Influencer: ${props.influencer}`
  }
  return `Cupón: ${userData.value?.code || ''}`
})

const showRanking = computed(() => {
  return isAdmin.value && props.influencer === 'ALL' && rankingData.value.length > 0
})

const rankingHeaders = [
  { title: '#', key: 'position', align: 'center', width: 60 },
  { title: 'Influencer', key: 'name', align: 'start' },
  { title: 'General', key: 'general', align: 'center' },
  { title: 'Light', key: 'light', align: 'center' },
  { title: 'Total', key: 'total', align: 'center' },
]
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>{{ chartTitle }}</VCardTitle>
      <VCardSubtitle>{{ chartSubtitle }}</VCardSubtitle>
    </VCardItem>
    
    <VCardText>
      <div
        v-if="isFetching"
        class="d-flex justify-center align-center"
        style="min-block-size: 400px;"
      >
        <VProgressCircular indeterminate color="primary" />
      </div>
      
      <template v-else>
        <!-- 📊 Gráfico -->
        <VueApexCharts
          :type="isAdmin && influencer === 'ALL' ? 'bar' : 'bar'"
          :height="isAdmin && influencer === 'ALL' ? 400 : 400"
          :options="chartConfig"
          :series="series"
        />

        <!-- 📋 Ranking completo (solo para admin con ALL) -->
        <VDivider v-if="showRanking" class="my-6" />
        
        <div v-if="showRanking">
          <div class="d-flex align-center mb-4">
            <VIcon icon="ri-trophy-line" color="warning" class="me-2" />
            <h3 class="text-h6">Ranking Completo de Influencers</h3>
          </div>

          <VDataTable
            :headers="rankingHeaders"
            :items="rankingData"
            :items-per-page="-1"
            density="comfortable"
            hide-default-footer
            class="text-no-wrap"
          >
            <!-- Posición con medalla -->
            <template #item.position="{ item }">
              <VChip
                :color="item.position === 1 ? 'warning' : item.position === 2 ? 'grey' : item.position === 3 ? 'orange' : 'default'"
                size="small"
                variant="tonal"
              >
                <VIcon
                  v-if="item.position <= 3"
                  :icon="item.position === 1 ? 'ri-trophy-fill' : item.position === 2 ? 'ri-medal-2-fill' : 'ri-medal-fill'"
                  size="16"
                  class="me-1"
                />
                {{ item.position }}
              </VChip>
            </template>

            <!-- Nombre del influencer -->
            <template #item.name="{ item }">
              <div class="d-flex align-center">
                <VAvatar
                  size="32"
                  :color="item.position <= 10 ? 'primary' : 'grey'"
                  variant="tonal"
                  class="me-2"
                >
                  <span class="text-xs">{{ item.name.charAt(0) }}</span>
                </VAvatar>
                <div>
                  <div class="font-weight-medium">{{ item.name }}</div>
                  <div class="text-caption text-disabled">{{ item.code }}</div>
                </div>
              </div>
            </template>

            <!-- Columnas numéricas con badges -->
            <template #item.general="{ item }">
              <VChip size="small" color="success" variant="tonal">
                {{ item.general }}
              </VChip>
            </template>

            <template #item.light="{ item }">
              <VChip size="small" color="warning" variant="tonal">
                {{ item.light }}
              </VChip>
            </template>

            <template #item.total="{ item }">
              <VChip size="small" color="primary" variant="flat">
                {{ item.total }}
              </VChip>
            </template>
          </VDataTable>
        </div>
      </template>
    </VCardText>
  </VCard>
</template>
