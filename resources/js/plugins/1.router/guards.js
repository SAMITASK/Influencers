// src/plugins/router/guards.js
export function setupGuards(router) {
  // Configuración de rutas protegidas
  const protectedRoutes = {
    'users': ['ADMIN'],
    // Agrega más rutas aquí
    // 'reports': ['ADMIN', 'MANAGER'],
  }

  router.beforeEach((to, from, next) => {
    console.log('🔍 Navegando a:', {
      path: to.path,
      name: to.name,
      meta: to.meta
    })

    // 1. Rutas públicas explícitas
    if (to.meta.public) {
      return next()
    }

    // 2. Verificar autenticación
    const userDataCookie = document.cookie
      .split('; ')
      .find(row => row.startsWith('userData='))
    
    const accessTokenCookie = document.cookie
      .split('; ')
      .find(row => row.startsWith('accessToken='))

    let userData = null
    if (userDataCookie) {
      try {
        userData = JSON.parse(decodeURIComponent(userDataCookie.split('=')[1]))
      } catch (e) {
        console.error('Error parsing userData cookie:', e)
      }
    }

    const isLoggedIn = !!(userData && accessTokenCookie)

    console.log('👤 Usuario:', {
      isLoggedIn,
      role: userData?.role
    })

    // 3. Rutas solo para no autenticados (login)
    if (to.meta.unauthenticatedOnly) {
      if (isLoggedIn) {
        return next({ name: 'root' })
      }
      return next()
    }

    // 4. Redirigir a login si no está autenticado
    if (!isLoggedIn && to.name !== 'login') {
      return next({
        name: 'login',
        query: {
          to: to.fullPath !== '/' ? to.fullPath : undefined,
        },
      })
    }

    // 5. ✅ VALIDAR ROLES
    const requiredRoles = protectedRoutes[to.name]
    
    if (requiredRoles && Array.isArray(requiredRoles)) {
      const userRole = userData?.role?.toUpperCase()
      const allowedRoles = requiredRoles.map(role => role.toUpperCase())
      
      console.log('🔒 Validando roles:', {
        ruta: to.name,
        userRole,
        allowedRoles,
        hasAccess: allowedRoles.includes(userRole)
      })
      
      if (!allowedRoles.includes(userRole)) {
        console.warn(`⛔ Acceso denegado a ${to.path}`)
        
        // Redirigir al dashboard con error
        return next({
          name: 'root',
          query: { 
            error: 'unauthorized',
            attempted: to.path 
          }
        })
      }
    }

    console.log(`✅ Acceso permitido a ${to.path}`)
    next()
  })
}
