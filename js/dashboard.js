(function() {
  'use strict';
  
  document.addEventListener('DOMContentLoaded', function() {
      initializeDashboard();
      loadStatistics();
      loadRecentActivity();
      
      setInterval(loadStatistics, 30000);
      setInterval(loadRecentActivity, 60000);
  });
  
  async function loadStatistics() {
      try {
          const response = await CookieManager.fetchWithAuth(
              `${window.APP_BASE_URL}/api/v1/dashboard/stats/`
          );
          const data = await response.json();
          
          if (data.success) {
              updateStatsDisplay(data.stats);
          }
      } catch (error) {
          console.error('Error al cargar estadísticas:', error);
      }
  }
  
  function updateStatsDisplay(stats) {
      // 1. Llamadas Hoy
      const llamadasHoy = stats.llamadas_hoy || 0;
      const llamadasAyer = stats.llamadas_ayer || 0;
      animateValue('total-llamadas', 0, llamadasHoy, 1000);
      updateComparison('llamadas-comparison', llamadasHoy, llamadasAyer);
      
      // 2. Ventas Hoy (número de ventas)
      const ventasHoy = stats.ventas_hoy || 0;
      const ventasAyer = stats.ventas_ayer || 0;
      animateValue('total-ventas', 0, ventasHoy, 1000);
      updateComparison('ventas-comparison', ventasHoy, ventasAyer);
      
      // 3. Tasa de Conversión (porcentaje)
      const tasaHoy = stats.tasa_conversion_hoy || 0;
      const tasaAyer = stats.tasa_conversion_ayer || 0;
      animateValue('tasa-conversion', 0, tasaHoy, 1000, '%');
      updateComparison('tasa-comparison', tasaHoy, tasaAyer, true);
      
      // 4. Ingreso Total Hoy (moneda)
      const ingresoHoy = stats.ingreso_total_hoy || 0;
      const ingresoAyer = stats.ingreso_total_ayer || 0;
      animateValueCurrency('ingreso-total', 0, ingresoHoy, 1000);
      updateComparison('ingreso-comparison', ingresoHoy, ingresoAyer, false, true);
  }
  
  function updateComparison(elementId, hoy, ayer, isPercentage = false, isCurrency = false) {
      const element = document.getElementById(elementId);
      if (!element) return;
      
      // Si ayer fue 0
      if (ayer === 0) {
          if (hoy === 0) {
              element.innerHTML = '<span style="color: var(--text-secondary);">Sin cambios</span>';
          } else {
              element.innerHTML = '<span style="color: var(--text-secondary);">Primer día</span>';
          }
          return;
      }
      
      const diferencia = hoy - ayer;
      const porcentajeCambio = ((diferencia / ayer) * 100).toFixed(1);
      
      // Determinar color y flecha
      const isPositive = diferencia > 0;
      const color = isPositive ? '#10b981' : '#ef4444'; // verde o rojo
      const arrow = isPositive ? '↑' : '↓';
      
      let diferenciaTexto;
      if (isCurrency) {
          diferenciaTexto = Math.abs(diferencia).toFixed(2);
      } else if (isPercentage) {
          diferenciaTexto = Math.abs(diferencia).toFixed(1);
      } else {
          diferenciaTexto = Math.abs(diferencia);
      }
      
      if (isPercentage) {
          element.innerHTML = `
              <span style="color: ${color}; font-weight: 500;">
                  ${arrow} ${diferenciaTexto}% vs ayer
              </span>
          `;
      } else {
          element.innerHTML = `
              <span style="color: ${color}; font-weight: 500;">
                  ${arrow} ${diferenciaTexto}${isCurrency ? '' : ''} (${Math.abs(porcentajeCambio)}%) vs ayer
              </span>
          `;
      }
  }
  
  async function loadRecentActivity() {
      try {
          const response = await CookieManager.fetchWithAuth(
              `${window.APP_BASE_URL}/api/v1/dashboard/recent_activity/`
          );
          const data = await response.json();
          
          if (data.success && data.activities) {
              displayRecentActivity(data.activities);
          }
      } catch (error) {
          console.error('Error al cargar actividad reciente:', error);
      }
  }
  
  function displayRecentActivity(activities) {
      const tbody = document.getElementById('actividad-reciente-body');
      tbody.innerHTML = '';
      
      if (activities.length === 0) {
          tbody.innerHTML = `
              <tr>
                  <td colspan="5" style="text-align: center; color: var(--text-secondary);">
                      No hay actividad reciente
                  </td>
              </tr>
          `;
          return;
      }
      
      activities.forEach(activity => {
          const row = document.createElement('tr');
          
          // Crear celda de cliente con nombre e ID
          const clienteHtml = activity.cliente_id 
              ? `<div style="font-weight: 500;">${activity.cliente}</div><div style="font-size: 0.85rem; color: var(--text-secondary);">${activity.cliente_id}</div>`
              : `<div style="font-weight: 500;">${activity.cliente}</div><div style="font-size: 0.85rem; color: var(--text-secondary);">Sin ID</div>`;
          
          row.innerHTML = `
              <td>${formatTime(activity.hora)}</td>
              <td>${activity.operador}</td>
              <td>${activity.actividad}</td>
              <td>${clienteHtml}</td>
              <td><span class="badge badge-${activity.estado_class}">${activity.estado}</span></td>
          `;
          tbody.appendChild(row);
      });
  }
  
  function formatTime(timestamp) {
      if (!timestamp) return '-';
      const date = new Date(timestamp);
      return date.toLocaleTimeString('es-ES', { 
          hour: '2-digit', 
          minute: '2-digit' 
      });
  }
  
  function animateValue(elementId, start, end, duration, suffix = '') {
      const element = document.getElementById(elementId);
      if (!element) return;
      
      const range = end - start;
      const increment = range / (duration / 16);
      let current = start;
      
      const timer = setInterval(() => {
          current += increment;
          if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
              element.textContent = Math.round(end) + suffix;
              clearInterval(timer);
          } else {
              element.textContent = Math.round(current) + suffix;
          }
      }, 16);
  }
  
  function animateValueCurrency(elementId, start, end, duration) {
      const element = document.getElementById(elementId);
      if (!element) return;
      
      const range = end - start;
      const increment = range / (duration / 16);
      let current = start;
      
      const timer = setInterval(() => {
          current += increment;
          if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
              element.textContent = `$${end.toFixed(2)}`;
              clearInterval(timer);
          } else {
              element.textContent = `$${current.toFixed(2)}`;
          }
      }, 16);
  }
  
  function initializeDashboard() {
      console.log('Dashboard inicializado correctamente');
  }
  
})();