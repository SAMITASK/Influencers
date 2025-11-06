<div align="center">
  <br />
  <a href="https://i.postimg.cc/pLG6FzV6/influencers.png" target="_blank">
    <img src="https://i.postimg.cc/pLG6FzV6/influencers.png" alt="Portal de Códigos Banner" width="600"/>
  </a>
  <br />
  <div>
    <img src="https://img.shields.io/badge/-Vue.js-black?style=for-the-badge&logo=vue.js&logoColor=4FC08D" alt="Vue.js" />
    <img src="https://img.shields.io/badge/-Vuetify-black?style=for-the-badge&logo=vuetify&logoColor=1867C0" alt="Vuetify" />
    <img src="https://img.shields.io/badge/-Vite-black?style=for-the-badge&logo=vite&logoColor=white&color=646CFF" alt="Vite" />
    <img src="https://img.shields.io/badge/-PHP-black?style=for-the-badge&logo=php&logoColor=777BB4" alt="PHP" />
    <img src="https://img.shields.io/badge/-MySQL-black?style=for-the-badge&logo=mysql&logoColor=4479A1" alt="MySQL" />
    <img src="https://img.shields.io/badge/-Firebase-black?style=for-the-badge&logo=firebase&logoColor=FFCA28" alt="Firebase" />
  </div>
  <h2 align="center">Portal de Códigos Promocionales 🎟️</h2>
  <p align="center">
    Sistema completo para la <b>gestión de códigos promocionales de influencers</b><br>
    Administra códigos, visualiza reportes de ventas en tiempo real y rankings de rendimiento.<br>
    Portal dual: panel administrativo y acceso para influencers.
  </p>
</div>

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Tecnologías](#tecnologías)
3. [Características](#características)
4. [Instalación Rápida](#instalación-rápida)
5. [Uso](#uso)
6. [Estructura del Sistema](#estructura-del-sistema)
7. [Contribuciones](#contribuciones)
8. [Licencia](#licencia)

---

## <a name="introducción">🤖 Introducción</a>

**Portal de Códigos** es un sistema integral para la gestión y seguimiento de códigos promocionales otorgados a influencers en campañas de venta de entradas. 

Cuando los usuarios utilizan códigos de influencers al comprar entradas, el sistema registra y analiza automáticamente las ventas, generando reportes detallados y rankings en tiempo real. Los administradores pueden gestionar códigos y monitorear el rendimiento, mientras que los influencers tienen acceso a sus propias estadísticas de ventas.

---

## <a name="tecnologías">⚙️ Tecnologías</a>

- **Vue 3** (framework frontend)
- **Vuetify** (biblioteca de componentes Material Design)
- **Vite** (herramienta de construcción)
- **PHP** (backend/API)
- **MySQL** (base de datos propia)
- **Firebase Authentication** (autenticación por número de celular)
- **Composer** (gestión de dependencias PHP)
- **pnpm** (gestor de paquetes frontend)

---

## <a name="características">🔋 Características</a>

- 📱 **Autenticación por celular**: Login seguro mediante Firebase Authentication con número de teléfono.
- 🎫 **Gestión de códigos**: Creación, edición y administración de códigos promocionales por influencer.
- 📊 **Reportes en tiempo real**: Seguimiento detallado de ventas por código y por tipo de entrada.
- 🏆 **Top 10 Influencers**: Ranking dinámico de los influencers con mejor rendimiento.
- 👥 **Portal dual**: Panel administrativo completo y acceso individual para influencers.
- 📈 **Analytics por influencer**: Cada influencer puede visualizar sus propias estadísticas de ventas.
- 💰 **Tracking por entrada**: Monitoreo de ventas diferenciado por tipo de entrada.
- 🔐 **Gestión de accesos**: Control de permisos entre administradores e influencers.
- ⚡ **Interfaz moderna**: Desarrollada con Vue 3 para una experiencia fluida y responsive.

---

## <a name="instalación-rápida">🤸 Instalación Rápida</a>

**Requisitos**

- [Git](https://git-scm.com/)
- [Node.js & pnpm](https://pnpm.io/)
- [PHP](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- MySQL

**Instalación**

```bash
git clone https://github.com/SAMITASK/Influencers.git
cd Influencers

# Frontend
pnpm install
pnpm run dev

# Backend
composer install
# Configurar .env con la conexión a MySQL y credenciales de Firebase
# Importar la estructura de base de datos desde /sql
# Iniciar el servidor PHP
```

Abre [http://localhost:5173](http://localhost:5173) en tu navegador.

---

## <a name="uso">🕸️ Uso</a>

### Autenticación:
1. Ingresa tu número de celular
2. Recibe el código de verificación por SMS (Firebase)
3. Valida tu identidad y accede al portal

### Para Administradores:
1. Accede con credenciales de administrador
2. Crea y asigna códigos promocionales a influencers
3. Visualiza el dashboard con reportes generales
4. Consulta el Top 10 de influencers con mejor rendimiento
5. Analiza ventas por tipo de entrada y por período

### Para Influencers:
1. Accede con tu número de celular registrado
2. Visualiza tus códigos promocionales activos
3. Consulta tus estadísticas de ventas en tiempo real
4. Revisa el detalle de ventas por tipo de entrada
5. Monitorea tu posición en el ranking

---

## <a name="estructura-del-sistema">🗂️ Estructura del Sistema</a>

```
📦 Portal de Códigos
├── 👨‍💼 Panel Administrativo
│   ├── Gestión de influencers
│   ├── Administración de códigos
│   ├── Reportes generales
│   └── Top 10 ranking
│
└── 🎯 Portal de Influencers
    ├── Dashboard personal
    ├── Códigos asignados
    ├── Estadísticas de ventas
    └── Detalle por entrada
```

---

## <a name="contribuciones">🤝 Contribuciones</a>

Este es un proyecto privado de uso interno. No se aceptan contribuciones externas en este momento.

---

## <a name="licencia">📝 Licencia</a>

Código propietario de uso interno. Todos los derechos reservados.

---

> Desarrollado e implementado por [EmersonValenzuela](https://github.com/EmersonValenzuela)
