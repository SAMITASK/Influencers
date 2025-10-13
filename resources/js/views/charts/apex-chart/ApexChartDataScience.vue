<script setup>
import { useTheme } from "vuetify";
import { getColumnChartConfig } from "@core/libs/apex-chart/apexCharConfig";

const props = defineProps({
  dateRange: {
    type: String, // ✅ Es un string, no array
    default: "",
  },
  coupon: {
    type: String,
    default: "CAMILA2025",
  },
});

const vuetifyTheme = useTheme();

// 📊 Obtener datos del chart desde la API
const { data: chartData, isFetching } = await useApi(
  createUrl("cart-details/chart", {
    query: {
      date: computed(() => props.dateRange), 
      coupon: computed(() => props.coupon),
    },
  })
);

// 📈 Series y categorías reactivas
const series = computed(() => chartData.value?.series || []);
const categories = computed(() => chartData.value?.categories || []);

// 🎨 Configuración del chart con categorías dinámicas
const chartConfig = computed(() => {
  const config = getColumnChartConfig(vuetifyTheme.current.value);

  return {
    ...config,
    xaxis: {
      ...config.xaxis,
      categories: categories.value,
    },
  };
});
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>Ventas por Tipo de Entrada</VCardTitle>
      <VCardSubtitle>Cupón: {{ coupon }}</VCardSubtitle>
    </VCardItem>

    <VCardText>
      <div
        v-if="isFetching"
        class="d-flex justify-center align-center"
        style="min-block-size: 400px;"
      >
        <VProgressCircular indeterminate color="primary" />
      </div>

      <VueApexCharts
        v-else
        type="bar"
        height="400"
        :options="chartConfig"
        :series="series"
      />
    </VCardText>
  </VCard>
</template>
