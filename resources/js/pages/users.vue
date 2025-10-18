<script setup>
import AddNewUserDrawer from "@/views/apps/user/list/AddNewUserDrawer.vue";

definePage({
  meta: {
    title: 'Usuarios',
    roles: ['ADMIN'],
  }
})

const isLoading = ref(false);
const searchQuery = ref("");

// Data table options
const itemsPerPage = ref(10);
const page = ref(1);
const sortBy = ref();
const orderBy = ref();
const selectedRows = ref([]);

const updateOptions = (options) => {
  page.value = options.page;
  sortBy.value = options.sortBy[0]?.key;
  orderBy.value = options.sortBy[0]?.order;
};

// Headers
const headers = [
  {
    title: "👤 Nombre Completo",
    key: "name",
    align: "start",
    sortable: true,
    width: "220px",
  },
  {
    title: "📱 Usuario / Red Social",
    key: "social",
    align: "center",
    sortable: true,
  },
  {
    title: "✉️ Correo Electrónico",
    key: "email",
    align: "center",
    sortable: true,
    width: "200px",
  },
  {
    title: "📞 Teléfono",
    key: "phone",
    align: "center",
    sortable: false,
    width: "140px",
  },
  {
    title: "🏷️ Código",
    key: "code",
    align: "center",
    sortable: true,
    width: "180px",
  },
  {
    title: "👔 Rol", // 🆕 Nueva columna
    key: "role",
    align: "center",
    sortable: true,
    width: "120px",
  },
  {
    title: "🔖 Estado",
    key: "status",
    align: "center",
    sortable: true,
    width: "120px",
  },
  {
    title: "⚙️ Acciones",
    key: "actions",
    align: "center",
    sortable: false,
    width: "120px",
  },
];

const {
  data: usersData,
  execute: fetchUsers,
  isFetching,
} = await useApi(
  createUrl("influencers/list", {
    query: {
      q: searchQuery,
      itemsPerPage,
      page,
      sortBy,
      orderBy,
    },
  })
);

const users = computed(() => usersData.value.data);
const totalUsers = computed(() => usersData.value.totalUsers);

const resolveUserStatusVariant = (stat) => {
  const statLowerCase = stat.toLowerCase();
  if (statLowerCase === "pending") return "warning";
  if (statLowerCase === "active") return "success";
  if (statLowerCase === "inactive") return "secondary";

  return "primary";
};

const translateStatus = (status) => {
  if (!status) return "";
  const s = status.toLowerCase();
  if (s === "active") return "Activo";
  if (s === "inactive") return "Inactivo";
  if (s === "pending") return "Pendiente";
  return status;
};

const isAddNewUserDrawerVisible = ref(false);
const editingUser = ref(null);
const errors = ref({});

// ✅ Corrección: Referencia al drawer para resetear
const drawerRef = ref(null);

// ✅ Mejora: Función para crear usuario con mejor manejo de errores
const addNewUser = async (userData) => {
  errors.value = {}; // Limpiar errores previos
  isLoading.value = true;

  try {
    await $api("influencers", {
      method: "POST",
      body: userData,
    });

    // Si llegamos aquí, la petición fue exitosa
    await fetchUsers();
    isAddNewUserDrawerVisible.value = false;
    drawerRef.value?.resetForm(); // Resetear usando el método expuesto

    showSnackbar({
      message: "Influencer creado correctamente",
      color: "primary",
    });
  } catch (error) {
    // Verificar si hay errores de validación del servidor
    if (error.response?._data?.errors) {
      errors.value = error.response._data.errors;
      showSnackbar({
        message: "Por favor corrige los errores del formulario",
        color: "warning",
      });
    } else {
      // Error general
      showSnackbar({
        message:
          error.response?._data?.message ||
          "Error al crear usuario. Contactar con sistemas.",
        color: "error",
      });
    }
  } finally {
    isLoading.value = false;
  }
};

const openCreateModal = () => {
  editingUser.value = null;
  errors.value = {}; // Limpiar errores
  isAddNewUserDrawerVisible.value = true;
};

const editUser = (user) => {
  editingUser.value = user;
  errors.value = {}; // Limpiar errores
  isAddNewUserDrawerVisible.value = true;
};

// ✅ Mejora: Función para actualizar usuario con manejo de errores
const updateUser = async (userData) => {
  errors.value = {};
  isLoading.value = true;

  try {
    const response = await $api(`influencers/${userData.id}`, {
      method: "PUT",
      body: userData,
    });

    await fetchUsers();
    isAddNewUserDrawerVisible.value = false;
    drawerRef.value?.resetForm();

    showSnackbar({
      message: response.message, // <-- viene de Laravel
      color: "primary",
    });
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;

      showSnackbar({
        message: "Por favor corrige los errores del formulario",
        color: "warning",
      });
    } else {
      showSnackbar({
        message:
          error.response?.message ||
          "Error al actualizar usuario. Contactar con sistemas.",
        color: "error",
      });
    }
  } finally {
    isLoading.value = false;
  }
};

const handleUserData = (userData) => {
  if (userData.id) {
    updateUser(userData);
  } else {
    addNewUser(userData);
  }
};

const isSnackbarVisible = ref(false);
const colorSnackbar = ref("primary");
const messageSnackbar = ref("");

const showSnackbar = ({ message, color = "success" }) => {
  messageSnackbar.value = message;
  colorSnackbar.value = color;
  isSnackbarVisible.value = true;
};
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText class="d-flex flex-wrap gap-4 align-center">
        <VSpacer />
        <div class="d-flex align-center gap-4 flex-wrap">
          <div class="app-user-search-filter">
            <VTextField
              v-model="searchQuery"
              placeholder="Buscar Usuario"
              density="compact"
            />
          </div>
          <VBtn @click="openCreateModal"> Nuevo Usuario </VBtn>
        </div>
      </VCardText>

      <VDataTableServer
        v-model:model-value="selectedRows"
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="users"
        item-value="id"
        :items-length="totalUsers"
        :headers="headers"
        class="text-no-wrap rounded-0"
        @update:options="updateOptions"
        :loading="isFetching"
      >
        <!-- 🆕 Mostrar código único -->
        <template #item.code="{ item }">
          <VChip
            v-if="item.code"
            color="primary"
            variant="tonal"
            size="small"
            class="font-weight-medium"
          >
            {{ item.code }}
          </VChip>
          <span v-else class="text-disabled"> Sin código </span>
        </template>

        <template #item.role="{ item }">
          <VChip
            :color="item.role === 'admin' ? 'error' : 'primary'"
            size="small"
            class="text-capitalize"
          >
            {{ item.role === "admin" ? "Administrador" : "Influencer" }}
          </VChip>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="resolveUserStatusVariant(item.status)"
            size="small"
            class="text-capitalize"
          >
            {{ translateStatus(item.status) }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn size="small" @click="editUser(item)">
            <VIcon icon="ri-edit-box-line" />
          </IconBtn>
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
              {{ paginationMeta({ page, itemsPerPage }, totalUsers) }}
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
                :disabled="page >= Math.ceil(totalUsers / itemsPerPage)"
                @click="
                  page >= Math.ceil(totalUsers / itemsPerPage)
                    ? (page = Math.ceil(totalUsers / itemsPerPage))
                    : page++
                "
              />
            </div>
          </div>
        </template>
      </VDataTableServer>
    </VCard>

    <!-- ✅ Corrección: Agregar ref al drawer -->
    <AddNewUserDrawer
      ref="drawerRef"
      v-model:is-drawer-open="isAddNewUserDrawerVisible"
      :errors="errors"
      :isLoading="isLoading"
      :editing-user="editingUser"
      @user-data="handleUserData"
      @user-updated="handleUserData"
    />

    <VSnackbar
      v-model="isSnackbarVisible"
      :timeout="3000"
      location="top"
      :color="colorSnackbar"
    >
      {{ messageSnackbar }}
    </VSnackbar>
  </section>
</template>

<style lang="scss" scoped>
.app-user-search-filter {
  inline-size: 15.625rem;
}
</style>
