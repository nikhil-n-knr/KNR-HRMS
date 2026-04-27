// vite.config.js
import { defineConfig } from "file:///D:/KNR/KNR-Hrms/node_modules/vite/dist/node/index.js";
import laravel from "file:///D:/KNR/KNR-Hrms/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///D:/KNR/KNR-Hrms/node_modules/@vitejs/plugin-vue/dist/index.mjs";
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true
    }),
    vue()
  ],
  server: {
    host: "0.0.0.0",
    // Listen on all interfaces
    hmr: {
      host: "localhost"
      // Or 10.0.2.2 if you want to be specific, but localhost usually works if proxied correctly
    },
    cors: true
  },
  resolve: {
    alias: {
      "@": "/resources/js"
    }
  },
  build: {
    chunkSizeWarningLimit: 1e3
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJEOlxcXFxLTlJcXFxcS05SLUhybXNcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZmlsZW5hbWUgPSBcIkQ6XFxcXEtOUlxcXFxLTlItSHJtc1xcXFx2aXRlLmNvbmZpZy5qc1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9pbXBvcnRfbWV0YV91cmwgPSBcImZpbGU6Ly8vRDovS05SL0tOUi1Icm1zL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XHJcbmltcG9ydCBsYXJhdmVsIGZyb20gJ2xhcmF2ZWwtdml0ZS1wbHVnaW4nO1xyXG5pbXBvcnQgdnVlIGZyb20gJ0B2aXRlanMvcGx1Z2luLXZ1ZSc7XHJcblxyXG5leHBvcnQgZGVmYXVsdCBkZWZpbmVDb25maWcoe1xyXG4gICAgcGx1Z2luczogW1xyXG4gICAgICAgIGxhcmF2ZWwoe1xyXG4gICAgICAgICAgICBpbnB1dDogWydyZXNvdXJjZXMvY3NzL2FwcC5jc3MnLCAncmVzb3VyY2VzL2pzL2FwcC5qcyddLFxyXG4gICAgICAgICAgICByZWZyZXNoOiB0cnVlLFxyXG4gICAgICAgIH0pLFxyXG4gICAgICAgIHZ1ZSgpLFxyXG4gICAgXSxcclxuICAgIHNlcnZlcjoge1xyXG4gICAgICAgIGhvc3Q6ICcwLjAuMC4wJywgLy8gTGlzdGVuIG9uIGFsbCBpbnRlcmZhY2VzXHJcbiAgICAgICAgaG1yOiB7XHJcbiAgICAgICAgICAgIGhvc3Q6ICdsb2NhbGhvc3QnLCAvLyBPciAxMC4wLjIuMiBpZiB5b3Ugd2FudCB0byBiZSBzcGVjaWZpYywgYnV0IGxvY2FsaG9zdCB1c3VhbGx5IHdvcmtzIGlmIHByb3hpZWQgY29ycmVjdGx5XHJcbiAgICAgICAgfSxcclxuICAgICAgICBjb3JzOiB0cnVlLFxyXG4gICAgfSxcclxuICAgIHJlc29sdmU6IHtcclxuICAgICAgICBhbGlhczoge1xyXG4gICAgICAgICAgICAnQCc6ICcvcmVzb3VyY2VzL2pzJyxcclxuICAgICAgICB9LFxyXG4gICAgfSxcclxuICAgIGJ1aWxkOiB7XHJcbiAgICAgICAgY2h1bmtTaXplV2FybmluZ0xpbWl0OiAxMDAwLFxyXG4gICAgfSxcclxufSk7XHJcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBcU8sU0FBUyxvQkFBb0I7QUFDbFEsT0FBTyxhQUFhO0FBQ3BCLE9BQU8sU0FBUztBQUVoQixJQUFPLHNCQUFRLGFBQWE7QUFBQSxFQUN4QixTQUFTO0FBQUEsSUFDTCxRQUFRO0FBQUEsTUFDSixPQUFPLENBQUMseUJBQXlCLHFCQUFxQjtBQUFBLE1BQ3RELFNBQVM7QUFBQSxJQUNiLENBQUM7QUFBQSxJQUNELElBQUk7QUFBQSxFQUNSO0FBQUEsRUFDQSxRQUFRO0FBQUEsSUFDSixNQUFNO0FBQUE7QUFBQSxJQUNOLEtBQUs7QUFBQSxNQUNELE1BQU07QUFBQTtBQUFBLElBQ1Y7QUFBQSxJQUNBLE1BQU07QUFBQSxFQUNWO0FBQUEsRUFDQSxTQUFTO0FBQUEsSUFDTCxPQUFPO0FBQUEsTUFDSCxLQUFLO0FBQUEsSUFDVDtBQUFBLEVBQ0o7QUFBQSxFQUNBLE9BQU87QUFBQSxJQUNILHVCQUF1QjtBQUFBLEVBQzNCO0FBQ0osQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
