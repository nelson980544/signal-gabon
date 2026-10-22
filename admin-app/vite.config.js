import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// APP_BASE permet de servir l'app sous un sous-chemin (déploiement unifié
// à la racine du monorepo). Par défaut l'app est servie à la racine.
export default defineConfig({
  base: process.env.APP_BASE || '/',
  plugins: [react()],
})
